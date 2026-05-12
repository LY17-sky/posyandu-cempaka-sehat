<?php
$file = basename('backup_20260504_073156.sqlite');
$backupDir = __DIR__ . '/../../backups';
$sourceFile = $backupDir . DIRECTORY_SEPARATOR . $file;
echo "File: " . $file . "\n";
echo "Dir: " . $backupDir . "\n";
echo "Source: " . $sourceFile . "\n";
echo "Exists? " . (file_exists($sourceFile) ? "Yes" : "No") . "\n";
echo "Realpath: " . realpath($sourceFile) . "\n";
