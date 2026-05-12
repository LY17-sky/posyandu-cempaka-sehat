<?php
require_once __DIR__ . '/../../config/database.php';
$tujuan = escape($_POST['tujuan'] ?? $_GET['tujuan'] ?? '');
$pesan = escape($_POST['pesan'] ?? $_GET['pesan'] ?? '');
$status = 'failed';
if ($tujuan !== '' && $pesan !== '') {
    db()->insert('notifications', [
        'tujuan' => $tujuan,
        'pesan' => $pesan,
        'status' => 'sent'
    ]);
    $status = 'sent';
}
header('Content-Type: application/json');
echo json_encode(['status' => $status, 'tujuan' => $tujuan, 'pesan' => $pesan]);
