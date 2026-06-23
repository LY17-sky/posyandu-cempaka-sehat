<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <div class="max-w-lg mx-auto mt-12 text-center">
        <div class="card p-10 bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-white/20">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-50 flex items-center justify-center">
                <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-indigo-950 mb-2">Buat Backup Database?</h3>
            <p class="text-slate-500 mb-6">File backup akan disimpan di folder <strong>backups/</strong></p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <button type="submit" class="bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:scale-[1.02] active:scale-95 transition-all">
                    Ya, Backup Sekarang
                </button>
                <a href="index.php?module=backup&page=list" class="inline-block ml-3 text-slate-500 hover:text-slate-700 font-medium py-3 px-6">Batal</a>
            </form>
        </div>
    </div>
    <?php
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    flash('message', 'Token CSRF tidak valid.');
    redirect('index.php?module=backup&page=list');
}

$backupDir = __DIR__ . '/../../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$sourceFile = DB_NAME;
$filename = 'backup_' . date('Ymd_His') . '.sqlite';
$filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

$success = file_exists($sourceFile) ? copy($sourceFile, $filepath) : false;

if (!$success) {
    $filename = 'backup_' . date('Ymd_His') . '_failed.txt';
    $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($filepath, "Gagal membuat backup otomatis.\n");
}
flash('message', 'Backup otomatis disimpan ke ' . $filename);
redirect('index.php?module=backup&page=list');
