<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
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
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>