<?php
require_once __DIR__ . '/../../config/database.php';

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
        
        // Calculate status for tooltip
        $statusText = 'Normal';
        if ($record['bb'] && $record['tb']) {
            if ($record['bb'] < 8.5 || $record['tb'] < 65) {
                $statusText = 'Kurang';
            } elseif ($record['bb'] > 12 || $record['tb'] > 85) {
                $statusText = 'Berlebih';
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
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
