<?php
require 'config/database.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
    $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    $schema = file_get_contents('schema.sql');
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', ltrim($statement))) {
            $pdo->exec($statement);
        }
    }

    echo "Database setup completed successfully!\n";
    echo "Demo data has been inserted.\n";
    echo "Admin login: admin / password\n";
    echo "User login examples:\n";
    echo "- 1234567890123456 Siti (Ahmad Rahman)\n";
    echo "- 1234567890123457 Maya (Fatimah Sari)\n";
    echo "- 1234567890123458 Ani (Budi Santoso)\n";

} catch (Exception $e) {
    echo "Error setting up database: " . $e->getMessage() . "\n";
}
?>