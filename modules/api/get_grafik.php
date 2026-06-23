<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

header('Content-Type: application/json');

$balitaId = intval($_GET['balita_id'] ?? 0);
$period = $_GET['period'] ?? 'all';

if ($balitaId <= 0 || !checkBalitaAccess($balitaId)) {
    echo json_encode(['error' => 'Unauthorized or invalid Balita ID']);
    exit;
}

// Build date filter
$dateFilter = '';
$params = [$balitaId];
switch ($period) {
    case '3m':
        $threeMonthsAgo = date('Y-m-d', strtotime('-3 months'));
        $dateFilter = "AND t.tgl_timbang >= ?";
        $params[] = $threeMonthsAgo;
        break;
    case '6m':
        $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));
        $dateFilter = "AND t.tgl_timbang >= ?";
        $params[] = $sixMonthsAgo;
        break;
    case '1year':
        $oneYearAgo = date('Y-m-d', strtotime('-1 year'));
        $dateFilter = "AND t.tgl_timbang >= ?";
        $params[] = $oneYearAgo;
        break;
    default:
        // 'all' - no filter
        break;
}

try {
    $records = db()->select("
        SELECT t.*, b.nama as balita_nama
        FROM timbang t
        JOIN balita b ON t.balita_id = b.id
        WHERE t.balita_id = ? {$dateFilter}
        ORDER BY t.tgl_timbang ASC
    ", $params);
    
    if (empty($records)) {
        echo json_encode([
            'labels' => [],
            'bb' => [],
            'tb' => [],
            'lk' => [],
            'lila' => [],
            'status' => []
        ]);
        exit;
    }
    
    $labels = [];
    $bb = [];
    $tb = [];
    $lk = [];
    $lila = [];
    $status = [];
    
    foreach ($records as $record) {
        $labels[] = date('d/m/Y', strtotime($record['tgl_timbang']));
        $bb[] = $record['bb'] ? floatval($record['bb']) : null;
        $tb[] = $record['tb'] ? floatval($record['tb']) : null;
        $lk[] = $record['lk'] ? floatval($record['lk']) : null;
        $lila[] = $record['lila'] ? floatval($record['lila']) : null;
        
        $statusText = 'Normal';
        if ($record['bb'] && $record['tb']) {
            $balitaData = db()->selectOne("SELECT tgl_lahir, jenis_kelamin FROM balita WHERE id = ?", [$record['balita_id']]);
            if ($balitaData) {
                $ageMonths = getBalitaAgeInMonths($balitaData['tgl_lahir']);
                $result = getStatusGiziByAge($record['bb'], $record['tb'], $record['lk'] ?? 0, $record['lila'] ?? 0, $ageMonths, $balitaData['jenis_kelamin'] ?? 'L');
                $statusText = $result['color'] === 'Biru' ? 'Normal' : $result['status'];
            }
        }
        $status[] = $statusText;
    }
    
    echo json_encode([
        'labels' => $labels,
        'bb' => $bb,
        'tb' => $tb,
        'lk' => $lk,
        'lila' => $lila,
        'status' => $status,
        'balita' => $records[0]['balita_nama'] ?? 'Unknown'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Terjadi kesalahan database. Silakan coba lagi.']);
}
?>
