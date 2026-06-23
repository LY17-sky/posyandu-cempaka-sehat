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
$bbInput = trim($input['bb'] ?? '');
$tbInput = trim($input['tb'] ?? '');
$lkInput = trim($input['lk'] ?? '');
$lilaInput = trim($input['lila'] ?? '');
$tglTimbang = trim($input['tgl_timbang'] ?? date('Y-m-d'));

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

$bb = floatval(str_replace(',', '.', $bbInput));
$tb = floatval(str_replace(',', '.', $tbInput));
$lk = floatval(str_replace(',', '.', $lkInput));
$lila = floatval(str_replace(',', '.', $lilaInput));

$errors = [];
if ($bb <= 0 || $bb > 50) $errors[] = 'Berat badan harus antara 0.1 - 50 kg';
if ($tb <= 0 || $tb > 200) $errors[] = 'Tinggi badan harus antara 0.1 - 200 cm';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => $errors[0]]);
    exit;
}

try {
    $record = db()->selectOne("SELECT balita_id FROM timbang WHERE id = ?", [$id]);
    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        exit;
    }
    
    if (!checkBalitaAccess($record['balita_id'])) {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses']);
        exit;
    }
    
    $success = db()->update('timbang', [
        'bb' => $bb,
        'tb' => $tb,
        'lk' => $lk ?: null,
        'lila' => $lila ?: null,
        'tgl_timbang' => $tglTimbang
    ], 'id = ?', [$id]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan database']);
}
?>