<?php
define('FONNTE_API_KEY', 'your-api-key-here');
define('FONNTE_URL', 'https://api.fonnte.com/send');

function sendWA($target, $message) {
    if (empty($target) || empty($message)) {
        return ['success' => false, 'error' => 'Nomor atau pesan kosong'];
    }
    
    $target = formatNumber($target);
    if (!$target) {
        return ['success' => false, 'error' => 'Format nomor tidak valid'];
    }
    
    $data = [
        'target' => $target,
        'message' => $message,
        'delay' => '2'
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => FONNTE_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . FONNTE_API_KEY,
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['status']) && $result['status'] === true) {
        return ['success' => true, 'response' => $result];
    }
    
    return ['success' => false, 'error' => $result['message'] ?? 'Gagal mengirim WA'];
}

function formatNumber($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (strlen($number) < 10) return false;
    if (substr($number, 0, 2) === '62') {
        return $number;
    }
    if (substr($number, 0, 1) === '0') {
        return '62' . substr($number, 1);
    }
    if (substr($number, 0, 1) === '+') {
        return str_replace('+', '', $number);
    }
    return '62' . $number;
}

function waTimbangSelesai($balita_id, $bb, $tb, $tgl_timbang) {
    $balita = fetch_one("SELECT nama, no_telp, nama_ibu FROM balita WHERE id = ?", [$balita_id]);
    if (!$balita || empty($balita['no_telp'])) {
        return ['success' => false, 'error' => 'Nomor tidak ditemukan'];
    }
    
    $message = "Haloibu {$balita['nama_ibu']}! 🍀\n\n";
    $message .= "Data penimbangan {$balita['nama']} sudah dicatat:\n";
    $message .= "📅 Tanggal: {$tgl_timbang}\n";
    $message .= "⚖️ Berat: {$bb} kg\n";
    $message .= "📏 Tinggi: {$tb} cm\n\n";
    $message .= "Terima kasih telah mengikuti program posyandu!";
    
    return sendWA($balita['no_telp'], $message);
}

function waWHOAbnormal($balita_id, $status, $rekomendasi, $bb, $tb) {
    $balita = fetch_one("SELECT nama, no_telp, nama_ibu FROM balita WHERE id = ?", [$balita_id]);
    if (!$balita || empty($balita['no_telp'])) {
        return ['success' => false, 'error' => 'Nomor tidak ditemukan'];
    }
    
    $icon = $status === 'Merah' ? '🚨' : '⚠️';
    $level = $status === 'Merah' ? 'PERLU PERHATIAN KHUSUS' : 'WASPADA';
    
    $message = "{$icon} PERINGATAN GIZI {$level}\n\n";
    $message .= "Ibu {$balita['nama_ibu']}, hasil pemeriksaan {$balita['nama']}:\n\n";
    $message .= "⚖️ BB: {$bb} kg | 📏 TB: {$tb} cm\n";
    $message .= "📊 Status: {$status}\n";
    $message .= "💡 Saran: {$rekomendasi}\n\n";
    $message .= "Silakan datang ke posyandu untuk konsultasi lebih lanjut. Sehat selalu! 🍀";
    
    return sendWA($balita['no_telp'], $message);
}

function waReminderPosyandu($jadwal_id) {
    $jadwal = fetch_one("
        SELECT j.tanggal, j.lokasi, j.waktu, j.catatan 
        FROM jadwal_posyandu j 
        WHERE j.id = ?
    ", [$jadwal_id]);
    
    if (!$jadwal) {
        return ['success' => false, 'error' => 'Jadwal tidak ditemukan'];
    }
    
    $tgl = date('d/m/Y', strtotime($jadwal['tanggal']));
    $waktu = $jadwal['waktu'] ? date('H:i', strtotime($jadwal['waktu'])) : '07:00-11:00';
    
    $balitas = fetch_all("SELECT nama, no_telp, nama_ibu FROM balita WHERE is_active = 1 AND no_telp IS NOT NULL AND no_telp != ''");
    
    $results = [];
    foreach ($balitas as $b) {
        if (empty($b['no_telp'])) continue;
        
        $message = "📅 *REMINDER POSYANDU*\n\n";
        $message .= "Ibu {$b['nama_ibu']}, besok akan ada kegiatan posyandu:\n\n";
        $message .= "📆 Tanggal: {$tgl}\n";
        $message .= "⏰ Waktu: {$waktu}\n";
        $message .= "📍 Lokasi: {$jadwal['lokasi']}\n";
        if (!empty($jadwal['catatan'])) {
            $message .= "📝 Catatan: {$jadwal['catatan']}\n";
        }
        $message .= "\nAyo datang tepat waktu untuk pemeriksaan tumbuh kembang buah hati! 🍀";
        
        $results[] = sendWA($b['no_telp'], $message);
    }
    
    return ['success' => count($results) > 0, 'total' => count($results)];
}

function cekDanKirimReminder() {
    $besok = date('Y-m-d', strtotime('+1 day'));
    $jadwals = fetch_all("SELECT id FROM jadwal_posyandu WHERE tanggal = ?", [$besok]);
    
    foreach ($jadwals as $j) {
        waReminderPosyandu($j['id']);
    }
}