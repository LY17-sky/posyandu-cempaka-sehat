<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

try {
    $record = db()->selectOne("SELECT * FROM timbang WHERE id = ?", [$id]);
    
    if (!$record) {
        echo json_encode(['error' => 'Data tidak ditemukan']);
        exit;
    }
    
    if (!checkBalitaAccess($record['balita_id'])) {
        echo json_encode(['error' => 'Anda tidak memiliki akses']);
        exit;
    }
    
    echo json_encode([
        'id' => $record['id'],
        'balita_id' => $record['balita_id'],
        'bb' => $record['bb'],
        'tb' => $record['tb'],
        'lk' => $record['lk'],
        'lila' => $record['lila'],
        'tgl_timbang' => $record['tgl_timbang']
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Terjadi kesalahan database']);
}
?>