<?php
require_once __DIR__ . '/../../config/database.php';
$file = basename($_GET['file'] ?? '');
$backupDir = __DIR__ . '/../../backups';
$message = '';
if ($file && file_exists($backupDir . DIRECTORY_SEPARATOR . $file)) {
    $sourceFile = $backupDir . DIRECTORY_SEPARATOR . $file;
    if (preg_match('/\.sqlite$/i', $file)) {
        // For SQLite db files, we just copy it over the current database
        copy($sourceFile, DB_NAME);
    } else {
        $content = file_get_contents($sourceFile);
        // Remove comments
        $content = preg_replace('/--.*$/m', '', $content);
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        
        $statements = explode(';', $content);
        foreach ($statements as $sql) {
            $trimmed = trim($sql);
            if ($trimmed === '') {
                continue;
            }
            try {
                query_db($trimmed);
            } catch (Exception $e) {
                // Ignore syntax errors from malformed parts of the dump and continue
                error_log("Restore error: " . $e->getMessage() . " in query: " . $trimmed);
            }
        }
    }
    flash('message', 'Restore backup berhasil dijalankan.');
    redirect('index.php?module=backup&page=list');
} else {
    flash('message', 'Silakan pilih file backup dari daftar berikut untuk dipulihkan.');
    redirect('index.php?module=backup&page=list');
}
