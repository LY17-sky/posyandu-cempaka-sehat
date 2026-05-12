<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

function calculateZScore($value, $mean, $sd) {
    if ($sd == 0) return 0;
    return ($value - $mean) / $sd;
}

function getWHOStatus($bb, $tb, $lk, $lila, $ageMonths) {
    $status = [];
    $rekomendasi = [];
     $color = 'Biru';
    
    // BB/U (Weight-for-age)
    $bbMean = 8 + ($ageMonths * 0.2);
    $bbSd = 1.2;
    $bbZ = calculateZScore($bb, $bbMean, $bbSd);
    if ($bbZ < -3) {
        $status[] = 'Severely Underweight';
        $rekomendasi[] = 'Perlu intervensi medis segera';
        $color = 'Merah';
    } elseif ($bbZ < -2) {
        $status[] = 'Underweight';
        $rekomendasi[] = 'Perlu pemberian MP-ASI dan pemantauan';
        if ($color !== 'Merah') $color = 'Kuning';
    }
    
    // TB/U (Height-for-age) - Stunting
    $tbMean = 65 + ($ageMonths * 0.8);
    $tbSd = 3.5;
    $tbZ = calculateZScore($tb, $tbMean, $tbSd);
    if ($tbZ < -3) {
        $status[] = 'Severely Stunted';
        $rekomendasi[] = 'Perlu penanganan stunting intensif';
        $color = 'Merah';
    } elseif ($tbZ < -2) {
        $status[] = 'Stunted';
        $rekomendasi[] = 'Risiko stunting, perlu nutrisi tambahan';
        if ($color !== 'Merah') $color = 'Kuning';
    }
    
    // BB/TB (Weight-for-height) - Wasting
    $whRatio = $bb / ($tb / 100);
    $whMean = 15.5;
    $whSd = 1.8;
    $whZ = calculateZScore($whRatio, $whMean, $whSd);
    if ($whZ < -3) {
        $status[] = 'Severely Wasted';
        $rekomendasi[] = 'Perlu rehabilitasi gizi segera';
        $color = 'Merah';
    } elseif ($whZ < -2) {
        $status[] = 'Wasted';
        $rekomendasi[] = 'Risiko wasting, perlu asupan nutrisi tinggi';
        if ($color !== 'Merah') $color = 'Kuning';
    }
    
    // LK/U (Head circumference-for-age)
    if ($lk > 0) {
        $lkMean = 40 + ($ageMonths * 0.6);
        $lkSd = 1.5;
        $lkZ = calculateZScore($lk, $lkMean, $lkSd);
        if ($lkZ < -2) {
            $status[] = 'Microcephaly Risk';
            $rekomendasi[] = 'Perlu evaluasi neurologis';
            if ($color !== 'Merah') $color = 'Kuning';
        }
    }
    
    // LILA/U (Mid-upper arm circumference)
    if ($lila > 0) {
        $lilaMean = 13.5;
        $lilaSd = 1.2;
        $lilaZ = calculateZScore($lila, $lilaMean, $lilaSd);
        if ($lilaZ < -3) {
            $status[] = 'Severe Acute Malnutrition';
            $rekomendasi[] = 'Perlu penanganan gizi akut';
            $color = 'Merah';
        } elseif ($lilaZ < -2) {
            $status[] = 'Moderate Acute Malnutrition';
            $rekomendasi[] = 'Perlu suplementasi nutrisi';
            if ($color !== 'Merah') $color = 'Kuning';
        }
    }
    
    if (empty($status)) {
        $status[] = 'Normal';
        $rekomendasi[] = 'Pertumbuhan dalam batas normal, lanjutkan pola asuh yang baik';
    }
    
    return [
        'status' => implode(', ', $status),
        'rekomendasi' => implode('; ', $rekomendasi),
        'color' => $color,
        'z_scores' => [
            'bb_u' => round($bbZ, 2),
            'tb_u' => round($tbZ, 2),
            'bb_tb' => round($whZ, 2),
            'lk_u' => $lk > 0 ? round($lkZ, 2) : null,
            'lila_u' => $lila > 0 ? round($lilaZ, 2) : null
        ]
    ];
}

$balita_id = intval($_GET['balita_id'] ?? 0);
$bb = floatval($_GET['bb'] ?? 0);
$tb = floatval($_GET['tb'] ?? 0);
$lk = floatval($_GET['lk'] ?? 0);
$lila = floatval($_GET['lila'] ?? 0);

if ($balita_id > 0 && $bb > 0 && $tb > 0) {
    $balita = db()->selectOne("SELECT tgl_lahir FROM balita WHERE id = ?", [$balita_id]);
    
    if ($balita && $balita['tgl_lahir']) {
        $birthDate = new DateTime($balita['tgl_lahir']);
        $now = new DateTime();
        $ageMonths = (int)$birthDate->diff($now)->format('%m') + ($birthDate->diff($now)->format('%y') * 12);
        
        $result = getWHOStatus($bb, $tb, $lk, $lila, $ageMonths);
        echo json_encode($result);
    } else {
        echo json_encode(['error' => 'Data balita tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'Parameter tidak lengkap']);
}