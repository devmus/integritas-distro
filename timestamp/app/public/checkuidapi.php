<?php 

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$uid = $_REQUEST['tsuid'] ?? '';
if ($uid === '') {
    echo json_encode(['status' => false, 'error' => 'Missing tsuid']);
    exit;
}

try {
    $pdo = timestamp_pdo();

    $stmt = $pdo->prepare('SELECT * FROM timestampdata WHERE uid = ?');
    $stmt->execute([$uid]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => false, 'uid' => $uid, 'error' => 'UID not found']);
        exit;
    }

    $row = $stmt->fetch();

    $resp = [
        'status'       => true,
        'uid'          => $uid,
        'data'         => $row['datahash'],
        'number'       => $row['idtimestampdata'],
        'datecreated'  => $row['datecreated'],
        'onchain'      => (int)$row['hasbeenadded'] === 1,
    ];

    if ((int)$row['hasbeenadded'] === 1) {
        if ($row['proof'] === '[ERROR]') {
            $resp['status']      = false;
            $resp['onchain']     = false;
            $resp['datestamped'] = null;
        } else {
            $resp['datestamped'] = $row['datestamped'];
            $resp['root']        = $row['roottreehash'];
            $resp['proof']       = $row['proof'];
            $resp['address']     = '0xFFEEDD';
        }
    }

    echo json_encode($resp);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'error' => 'DB error', 'details' => $e->getMessage()]);
}
