<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('message', 'Metode tidak diizinkan.');
    redirect('index.php?module=balita&page=daftar');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    flash('message', 'Token CSRF tidak valid.');
    redirect('index.php?module=balita&page=daftar');
}

$id = intval($_POST['id'] ?? 0);
if ($id > 0) {
    $balita = db()->selectOne("SELECT * FROM balita WHERE id = ?" . getPosFilter(), [$id]);
    if (!$balita) {
        flash('message', 'Balita tidak ditemukan atau Anda tidak memiliki akses.');
        redirect('index.php?module=balita&page=daftar');
    }
    db()->update('balita', ['is_active' => 0], 'id = ?', [$id]);
    flash('message', 'Balita berhasil dihapus.');
}
redirect('index.php?module=balita&page=daftar');
