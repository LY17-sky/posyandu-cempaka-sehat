<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();
$totalBalita = fetch_one('SELECT COUNT(*) AS value FROM balita WHERE is_active = 1');
$totalTimbang = fetch_one('SELECT COUNT(*) AS value FROM timbang');
$totalKonsultasi = fetch_one('SELECT COUNT(*) AS value FROM konsultasi');
$graph = fetch_all("SELECT strftime('%m', tgl_timbang) AS month, COUNT(*) AS value FROM timbang GROUP BY strftime('%m', tgl_timbang) ORDER BY month");
$monthNames = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
$graph = array_map(function($r) use ($monthNames) { $r['label'] = $monthNames[$r['month']] ?? $r['month']; return $r; }, $graph);
$result = [
    'total_balita' => intval($totalBalita['value'] ?? 0),
    'total_timbang' => intval($totalTimbang['value'] ?? 0),
    'total_konsultasi' => intval($totalKonsultasi['value'] ?? 0),
    'labels' => array_column($graph, 'label'),
    'graph' => array_map('intval', array_column($graph, 'value'))
];
header('Content-Type: application/json');
echo json_encode($result);
