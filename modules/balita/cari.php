<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$keyword = trim($_GET['q'] ?? '');
if ($keyword === '') {
    echo json_encode(['error' => 'Keyword required']);
    exit;
}

$list = fetch_all("SELECT id, nama, tgl_lahir, jenis_kelamin FROM balita WHERE is_active = 1" . getPosFilter() . " AND nama LIKE ? ORDER BY nama LIMIT 20", ['%' . $keyword . '%']);
echo json_encode($list);