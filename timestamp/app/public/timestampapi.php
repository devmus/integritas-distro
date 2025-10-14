<?php 

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Support GET or POST
$data = $_REQUEST['tsdata'] ?? '';
if ($data === '') {
    echo json_encode(['status' => false, 'error' => 'Missing tsdata']);
    exit;
}
if (strlen($data) > 66) {
    echo json_encode(['status' => false, 'error' => 'Hash data wrong length']);
    exit;
}

function generateRandomHash(int $length = 20): string {
    $chars = '0123456789ABCDEF';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return '0x' . $out;
}

try {
    $pdo = timestamp_pdo();

    $uid = generateRandomHash();

    $stmt = $pdo->prepare('INSERT INTO timestampdata (uid, datahash) VALUES (?, ?)');
    $stmt->execute([$uid, $data]);

    echo json_encode(['status' => true, 'data' => $data, 'uid' => $uid]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'error' => 'DB error', 'details' => $e->getMessage()]);
}
