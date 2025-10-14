<html>
<head><title>Mini Stamp</title></head>
<body>
<center>
<h1>Time Stamp Service</h1>
<?php
require_once __DIR__ . '/../bootstrap.php';

function generateRandomHash(int $length = 20): string {
    $chars = '0123456789ABCDEF';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return '0x' . $out;
}

try {
    $pdo  = timestamp_pdo();
    $uid  = generateRandomHash();
    $data = $_POST['tsdata'] ?? '';

    $stmt = $pdo->prepare('INSERT INTO timestampdata (uid, datahash) VALUES (?, ?)');
    $stmt->execute([$uid, $data]);

    echo 'Data added to DB uid:' . htmlspecialchars($uid) . ' data:' . htmlspecialchars($data);
} catch (Throwable $e) {
    echo '<div style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<br><br>
Write down your UID!<br><br>
<a href="index.php">Back Home</a>
</center>
</body>
</html>
