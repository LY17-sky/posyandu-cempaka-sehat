<?php
/**
 * Script backup otomatis untuk cron job.
 * 
 * Jadwalkan dengan cron:
 *   0 2 * * * /usr/bin/php /path/to/scripts/backup_cron.php
 * 
 * Atau di Windows Task Scheduler:
 *   php C:\path\to\scripts\backup_cron.php
 */

require_once __DIR__ . '/../config/database.php';

$dbPath = realpath(__DIR__ . '/../database.sqlite');
if (!$dbPath || !file_exists($dbPath)) {
    echo "[ERROR] File database tidak ditemukan: " . __DIR__ . '/../database.sqlite' . "\n";
    exit(1);
}

$backupDir = __DIR__ . '/../backups/';
$timestamp = date('Y-m-d_H-i-s');
$filename = "backup_{$timestamp}.sqlite";
$destPath = $backupDir . $filename;

if (copy($dbPath, $destPath)) {
    db()->insert('backup_log', ['file_name' => $filename]);
    echo "[OK] Backup berhasil: {$filename}\n";
    
    // Hapus backup lebih dari 30 hari
    $files = glob($backupDir . 'backup_*.sqlite');
    $threshold = strtotime('-30 days');
    foreach ($files as $f) {
        if (filemtime($f) < $threshold) {
            unlink($f);
            echo "[INFO] Backup lama dihapus: " . basename($f) . "\n";
        }
    }
} else {
    echo "[ERROR] Gagal membuat backup.\n";
    exit(1);
}
