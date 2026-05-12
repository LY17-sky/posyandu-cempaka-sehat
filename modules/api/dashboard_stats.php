<?php
require_once __DIR__ . '/../../config/database.php';
$totalBalita = fetch_one('SELECT COUNT(*) AS value FROM balita WHERE is_active = 1');
$totalTimbang = fetch_one('SELECT COUNT(*) AS value FROM timbang');
$totalKonsultasi = fetch_one('SELECT COUNT(*) AS value FROM konsultasi');
$graph = fetch_all('SELECT DATE_FORMAT(tgl_timbang, "%b") AS label, COUNT(*) AS value FROM timbang GROUP BY DATE_FORMAT(tgl_timbang, "%m") ORDER BY DATE_FORMAT(tgl_timbang, "%m")');
$result = [
    'total_balita' => intval($totalBalita['value'] ?? 0),
    'total_timbang' => intval($totalTimbang['value'] ?? 0),
    'total_konsultasi' => intval($totalKonsultasi['value'] ?? 0),
    'labels' => array_column($graph, 'label'),
    'graph' => array_map('intval', array_column($graph, 'value'))
];
header('Content-Type: application/json');
echo json_encode($result);
