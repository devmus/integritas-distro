<html>
<head><title>Mini Stamp</title></head>
<body>
<center>
<h1>Run Time Stamp</h1>
<?php
require_once __DIR__ . '/../bootstrap.php';

function updateDB(PDO $db, string $root, string $proof, string $data): void {
    $upsql = 'UPDATE timestampdata
              SET roottreehash=?, proof=?, hasbeenadded=1, datestamped=CURRENT_TIMESTAMP
              WHERE hasbeenadded=0 AND datahash=?';
    $stmt = $db->prepare($upsql);
    $stmt->execute([$root, $proof, $data]);
}

try {
    $pdo = timestamp_pdo();

    $stmt = $pdo->prepare('SELECT * FROM timestampdata WHERE hasbeenadded=0');
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo 'No Data to time stamp..';
    } else {
        $mmrcreate = 'mmrcreate nodes:[';
        $nodes     = [];
        while ($row = $stmt->fetch()) {
            $nodes[] = '"' . $row['datahash'] . '"';
        }
        $mmrcreate .= implode(',', $nodes) . ']';

        echo htmlspecialchars($mmrcreate) . '<br><br>';

        // Run on Minima (unchanged)
		$minimaUrl = getenv('MINIMA_RPC_URL') ?: 'http://minima-ts:9005/';
        $ch = curl_init($minimaUrl . urlencode($mmrcreate));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);

        $resp = json_decode($output);
        echo '<pre style="text-align:left;max-width:800;">' .
             htmlspecialchars(json_encode($resp, JSON_PRETTY_PRINT)) .
             '</pre>';

        if (!$resp || empty($resp->status)) {
            echo 'Something went wrong..';
        } else {
            $root   = $resp->response->root->data ?? '';
            $txncmd = 'send amount:0.000000001 address:0xFFEEDD state:{"99":"' . $root . '"}';
            echo htmlspecialchars($txncmd) . '<br><br>';

            $ch = curl_init('minima:9005/' . urlencode($txncmd));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            echo htmlspecialchars($output) . '<br><br>';

            foreach (($resp->response->nodes ?? []) as $node) {
                $nodedata  = $node->data ?? '';
                $nodeproof = $node->proof ?? '';
                updateDB($pdo, $root, $nodeproof, $nodedata);
            }
        }
    }
} catch (Throwable $e) {
    echo '<div style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<br><br>
<a href="index.php">Back Home</a>
</center>
</body>
</html>
