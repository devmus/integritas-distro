<?php

require_once '/app/bootstrap.php';

function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp] $message\n";
    echo $line;
    file_put_contents("/var/log/timestamp-cron.log", $line, FILE_APPEND);
}

function updateDB($db, $root, $proof, $data ){
    $upsql = "UPDATE timestampdata".
             " SET roottreehash=?, proof=?, hasbeenadded=1, datestamped=CURRENT_TIMESTAMP".
             " WHERE hasbeenadded=0 AND datahash=?";
    $stmt = $db->prepare($upsql);
    $stmt->execute([$root,$proof,$data]);

    logMessage("Updated DB row for datahash=$data with root=$root");
}

/**
 * Mark a row as error (do NOT set hasbeenadded=1).
 * We keep roottreehash (if known) and write the error text into `proof`.
 */
// function updateDBError($db, $errorText, $data, $root = null){
//     $upsql = "UPDATE timestampdata".
//              " SET roottreehash=?, proof=?, datestamped=CURRENT_TIMESTAMP".
//              " WHERE hasbeenadded=0 AND datahash=?";
//     $stmt = $db->prepare($upsql);
//     $stmt->execute([$root, "[ERROR] ".$errorText, $data]);
//     logMessage("Marked datahash=$data as error: $errorText");
// }

/**
 * We keep roottreehash (if known) and write the error text into `proof`.
 */
function updateDBError($db, $errorText, $data, $root = null){
    $upsql = "UPDATE timestampdata".
             " SET roottreehash=?, proof=?, hasbeenadded=1, datestamped=CURRENT_TIMESTAMP".
             " WHERE hasbeenadded=0 AND datahash=?";
    $stmt = $db->prepare($upsql);
    $stmt->execute([$root, "[ERROR]", $data]);
    logMessage("Marked datahash=$data as error: $errorText");
}

try {
    $pdo = timestamp_pdo();

    $sql = "SELECT * FROM timestampdata WHERE hasbeenadded=0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        logMessage("No data to timestamp");
    } else {
        logMessage("Found ".$stmt->rowCount()." items to timestamp");

        // Build mmrcreate
        $mmrcreate = "mmrcreate nodes:[";
        $pendingRows = [];
        while ($row = $stmt->fetch()){
            $mmrcreate .= "\"".$row['datahash']."\",";
            $pendingRows[] = $row['datahash'];
        }
        $mmrcreate = rtrim($mmrcreate, ",")."]";

        // ---- Call 1: MMR create
        logMessage("Running command on Minima: $mmrcreate");
        $ch = curl_init("http://minima:9005/".urlencode($mmrcreate));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output === false) {
            $curlErr = curl_error($ch);
            logMessage("CURL error (mmrcreate): " . $curlErr);
            // Without a root or nodes we cannot proceed—mark all as error
            foreach ($pendingRows as $dh) {
                updateDBError($pdo, "mmrcreate curl error: $curlErr", $dh, null);
            }
            curl_close($ch);
            $pdo = null;
            exit;
        }
        $info = curl_getinfo($ch);
        logMessage("MMR create HTTP {$info['http_code']} response: $output");
        curl_close($ch);

        $resp = json_decode($output,false);
        if(!$resp || empty($resp->status) || $resp->status !== true){
            $err = isset($resp->error) ? $resp->error : "mmrcreate failed or invalid JSON";
            foreach ($pendingRows as $dh) {
                updateDBError($pdo, "mmrcreate error: $err", $dh, null);
            }
            $pdo = null;
            exit;
        }

        // Success: we have a root + nodes from mmrcreate
        $root  = $resp->response->root->data ?? null;
        $nodes = $resp->response->nodes ?? [];

        // ---- Call 2: Transaction
        $txncmd = "send amount:0.0000000000000000000000001 address:0xFFEEDD state:{\"99\":\"".$root."\"}";
        // (Use your chosen amount; keeping your working value here.)

        logMessage("Submitting transaction: $txncmd");
        $minimaUrl = getenv('MINIMA_RPC_URL') ?: 'http://minima-ts:9005/';
        $ch = curl_init($minimaUrl . urlencode($txncmd));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $txnOutput = curl_exec($ch);

        $curlTxnErr = null;
        if ($txnOutput === false) {
            $curlTxnErr = curl_error($ch);
            logMessage("CURL error (txn): " . $curlTxnErr);
        } else {
            $txnInfo = curl_getinfo($ch);
            logMessage("Txn HTTP {$txnInfo['http_code']} response: $txnOutput");
        }
        curl_close($ch);

        // Evaluate transaction outcome
        $txnJson = $txnOutput ? json_decode($txnOutput, false) : null;
        $txnStatus = $txnJson && isset($txnJson->status) ? (bool)$txnJson->status : false;
        $txnError  = $curlTxnErr ?: ($txnJson->error ?? null);

        if ($txnStatus === true) {
            // Transaction OK -> process each node with its proof (existing behavior)
            foreach($nodes as $node){
                $nodedata  = $node->data ?? null;
                $nodeproof = $node->proof ?? null;
                if ($nodedata === null) {
                    continue; // defensive
                }
                updateDB($pdo, $root, $nodeproof, $nodedata);
            }
            logMessage("Timestamp process finished for root=$root");
        } else {
            // Transaction failed -> DO NOT mark as added; record error per node
            $reason = $txnError ? ("txn error: ".$txnError) : "txn failed (status=false or bad response)";
            logMessage("Transaction failed; recording error for ".count($nodes)." nodes. Reason: $reason");
            foreach($nodes as $node){
                $nodedata = $node->data ?? null;
                if ($nodedata === null) {
                    continue;
                }
                // Store the root so you can inspect/possibly retry later
                updateDBError($pdo, $reason, $nodedata, $root);
            }
        }
    }
} catch (Exception $e) {
    logMessage("Exception: ".$e->getMessage());
}

$pdo = null;
?>