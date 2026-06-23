<?php
require_once __DIR__ . '/config/database.php';

if (!defined('DB_NAME') || !str_contains(DB_NAME, '.sqlite')) {
    die('Aplikasi ini menggunakan SQLite. File setup.php untuk MySQL tidak diperlukan.' . "\n");
}

try {
    echo "Database SQLite sudah terinitializeasi oleh config/database.php.\n";
    echo "Tidak perlu setup terpisah untuk SQLite.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>