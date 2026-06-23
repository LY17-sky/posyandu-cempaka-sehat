<?php
$year = intval($_GET['year'] ?? date('Y'));
$rows = fetch_all("SELECT strftime('%m', tgl_timbang) AS month, COUNT(*) AS count FROM timbang WHERE strftime('%Y', tgl_timbang) = ? GROUP BY strftime('%m', tgl_timbang) ORDER BY month", [(string)$year]);
$labels = [];
$values = [];
for ($m = 1; $m <= 12; $m++) {
    $monthKey = str_pad($m, 2, '0', STR_PAD_LEFT);
    $labels[] = $monthKey;
    $values[$monthKey] = 0;
}
foreach ($rows as $row) {
    $values[$row['month']] = intval($row['count']);
}
$result = ['labels' => array_keys($values), 'data' => array_values($values)];
header('Content-Type: application/json');
echo json_encode($result);
