<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

header('Content-Type: application/json');

function getWHOStatus($bb, $tb, $lk, $lila, $ageMonths, $gender = 'L') {
    $result = getStatusGiziByAge($bb, $tb, $lk, $lila, $ageMonths, $gender);
    return [
        'status' => $result['status'],
        'rekomendasi' => $result['rekomendasi'],
        'color' => $result['color'],
        'z_scores' => $result['z_scores']
    ];
}

$balita_id = intval($_GET['balita_id'] ?? 0);
$bb = floatval($_GET['bb'] ?? 0);
$tb = floatval($_GET['tb'] ?? 0);
$lk = floatval($_GET['lk'] ?? 0);
$lila = floatval($_GET['lila'] ?? 0);

if ($balita_id > 0 && $bb > 0 && $tb > 0) {
    $balita = db()->selectOne("SELECT tgl_lahir, jenis_kelamin FROM balita WHERE id = ?", [$balita_id]);
    
    if ($balita && $balita['tgl_lahir']) {
        $birthDate = new DateTime($balita['tgl_lahir']);
        $now = new DateTime();
        $ageMonths = (int)$birthDate->diff($now)->format('%m') + ($birthDate->diff($now)->format('%y') * 12);
        
        $result = getWHOStatus($bb, $tb, $lk, $lila, $ageMonths, $balita['jenis_kelamin'] ?? 'L');
        echo json_encode($result);
    } else {
        echo json_encode(['error' => 'Data balita tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'Parameter tidak lengkap']);
}