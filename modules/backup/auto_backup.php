<?php
$backupDir = __DIR__ . '/../../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
$filename = 'backup_' . date('Ymd_His') . '.sql';
$filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

$sourceFile = DB_NAME;
$success = false;

if (file_exists($sourceFile)) {
    // Save as .sqlite since it's an SQLite file copy
    $filename = 'backup_' . date('Ymd_His') . '.sqlite';
    $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;
    
    $success = copy($sourceFile, $filepath);
}

if (!$success) {
    // If copying failed or source doesn't exist
    $filename = 'backup_' . date('Ymd_His') . '_failed.txt';
    $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($filepath, "Gagal membuat backup otomatis.\n");
}
flash('message', 'Backup otomatis disimpan ke ' . $filename);
redirect('index.php?module=backup&page=list');
