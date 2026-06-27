<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!verifyCSRFToken($input['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

$record = db()->selectOne('SELECT balita_id FROM timbang WHERE id = ?', [$id]);
if (!$record) {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    exit;
}

if (!checkBalitaAccess($record['balita_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses']);
    exit;
}

try {
    $deleted = db()->delete('timbang', 'id = ?', [$id]);
    
    if ($deleted) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan database. Silakan coba lagi.']);
}
?>