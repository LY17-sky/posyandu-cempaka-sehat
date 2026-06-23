<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $posFilter = getPosFilter();
    $balitas = db()->select("
        SELECT id, nama, nik, tgl_lahir, nama_ibu, nama_ayah, alamat
        FROM balita 
        WHERE is_active = 1 $posFilter
        AND (nama LIKE ? OR nik LIKE ? OR nama_ibu LIKE ?)
        ORDER BY nama
        LIMIT 20
    ", ["%{$query}%", "%{$query}%", "%{$query}%"]);
    
    $results = [];
    foreach ($balitas as $balita) {
        $results[] = [
            'id' => $balita['id'],
            'nama' => $balita['nama'],
            'nik' => $balita['nik'],
            'tgl_lahir' => $balita['tgl_lahir'],
            'nama_ibu' => $balita['nama_ibu'],
            'nama_ayah' => $balita['nama_ayah'],
            'alamat' => $balita['alamat'],
            'usia' => date_diff(date_create($balita['tgl_lahir']), date_create())->y . ' tahun ' . 
                     date_diff(date_create($balita['tgl_lahir']), date_create())->m . ' bulan'
        ];
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Terjadi kesalahan database. Silakan coba lagi.']);
}
?>
