<?php
$id = isset($_GET['id']) ? intval($_GET['id'] ?? 0) : 0;
if ($id > 0) {
    db()->update('balita', ['is_active' => 0], 'id = ?', [$id]);
    flash('message', 'Balita berhasil dihapus.');
}
redirect('index.php?module=balita&page=daftar');
