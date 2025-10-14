<html>
<head><title>Mini Stamp</title></head>
<body>
<center>
<h1>Check UID</h1>
<?php
require_once __DIR__ . '/../bootstrap.php';

try {
    $pdo = timestamp_pdo();

    $uid  = $_POST['tsuid'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM timestampdata WHERE uid = ?');
    $stmt->execute([$uid]);

    if ($stmt->rowCount() === 0) {
        echo 'No Data found for UID: ' . htmlspecialchars($uid);
    } else {
        $row = $stmt->fetch();

        echo '<table>';
        echo '<tr><td>Number:</td><td>' . htmlspecialchars($row['idtimestampdata']) . '</td></tr>';
        echo '<tr><td>UID:</td><td>' . htmlspecialchars($row['uid']) . '</td></tr>';
        echo '<tr><td>Request Created:</td><td>' . htmlspecialchars($row['datecreated']) . '</td></tr>';

        if ((int)$row['hasbeenadded'] === 0) {
            echo '<tr><td>On Chain Yet:</td><td>NO</td></tr></table>';
        } else {
            echo '<tr><td>On Chain Yet:</td><td>YES</td></tr>';
            echo '<tr><td>Date Stamped:</td><td>' . htmlspecialchars($row['datestamped']) . '</td></tr>';
            echo '<tr><td>&nbsp;</td></tr>';
            echo '<tr><td>DATA:</td><td>' . htmlspecialchars($row['datahash']) . '</td></tr>';
            echo '<tr><td>ROOT:</td><td>' . htmlspecialchars($row['roottreehash']) . '</td></tr>';
            echo '<tr><td>PROOF:</td><td style="max-width:300;word-wrap:break-word;">' . htmlspecialchars($row['proof']) . '</td></tr>';
            echo '</table><br><br>';

            echo '<div style="text-align:left;max-width:570;word-wrap:break-word;">';
            echo '<b>To check this on your Minima node</b><br><br>';
            echo 'First:<br><code>archive action:addresscheck address:0xFFEEDD statecheck:' . htmlspecialchars($row['roottreehash']) . '</code><br><br>';
            echo 'Then:<br><code>mmrproof data:"' . htmlspecialchars($row['datahash']) .
                 '" root:' . htmlspecialchars($row['roottreehash']) .
                 ' proof:' . htmlspecialchars($row['proof']) . '</code>';
            echo '</div>';
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
