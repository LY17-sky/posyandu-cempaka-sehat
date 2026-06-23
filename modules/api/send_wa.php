<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'failed', 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$tujuan = trim($input['tujuan'] ?? '');
$pesan = trim($input['pesan'] ?? '');
$csrf_token = $input['csrf_token'] ?? '';

if (!verifyCSRFToken($csrf_token)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'failed', 'error' => 'Invalid CSRF token']);
    exit;
}

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
