<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();

    echo "✅ Koneksi database berhasil!\n\n";

    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tabel yang ada:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "\n";

    $balita = fetch_all("SELECT COUNT(*) as total FROM balita");
    echo "👶 Total balita: " . $balita[0]['total'] . "\n";

    $timbang = fetch_all("SELECT COUNT(*) as total FROM timbang");
    echo "⚖️ Total data timbang: " . $timbang[0]['total'] . "\n";

    $users = fetch_all("SELECT COUNT(*) as total FROM users");
    echo "👥 Total users: " . $users[0]['total'] . "\n";

    echo "\n🎉 Database SQLite siap digunakan!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
