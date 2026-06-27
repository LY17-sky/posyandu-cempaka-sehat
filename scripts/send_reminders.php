<?php
/**
 * Script pengiriman reminder jadwal posyandu otomatis.
 * 
 * Jadwalkan dengan cron (jalan setiap jam):
 *   0 * * * * /usr/bin/php /path/to/scripts/send_reminders.php
 * 
 * Script ini akan mengecek jadwal posyandu untuk besok
 * dan mengirim reminder WhatsApp ke orang tua balita.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/notifikasi.php';

echo "[INFO] Memeriksa jadwal posyandu untuk besok (" . date('Y-m-d', strtotime('+1 day')) . ")\n";

$besok = date('Y-m-d', strtotime('+1 day'));
$jadwals = fetch_all("SELECT id, lokasi FROM jadwal_posyandu WHERE tanggal = ?", [$besok]);

if (count($jadwals) === 0) {
    echo "[INFO] Tidak ada jadwal posyandu untuk besok.\n";
    exit(0);
}

echo "[INFO] Ditemukan " . count($jadwals) . " jadwal. Mengirim reminder...\n";

foreach ($jadwals as $j) {
    $result = waReminderPosyandu($j['id']);
    if ($result['success']) {
        echo "[OK] Reminder terkirim untuk jadwal #{$j['id']} ({$j['lokasi']})\n";
    } else {
        echo "[WARN] Gagal mengirim reminder untuk jadwal #{$j['id']}: {$result['error']}\n";
    }
}

echo "[SELESAI]\n";
