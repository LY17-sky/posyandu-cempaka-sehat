<?php
$backupDir = __DIR__ . '/../../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}
$files = array_filter(scandir($backupDir), function ($name) {
    return preg_match('/\.(sql|sqlite)$/i', $name);
});
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    Manajemen Backup Data
                </h3>
                <p class="text-sm text-indigo-500/70 font-medium">Cadangkan dan pulihkan basis data sistem</p>
            </div>
            <a href="index.php?module=backup&page=auto_backup" 
               class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-500 to-pink-500 px-5 py-2.5 text-white font-bold shadow-lg shadow-indigo-200 hover:scale-[1.02] active:scale-95 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Backup Baru
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Nama File Backup</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Ukuran</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    <?php if (count($files) === 0): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-20 text-center text-indigo-300 font-medium">Belum ada file backup tersedia.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($files as $file): 
                        $size = filesize($backupDir . '/' . $file);
                        $sizeStr = $size > 1024*1024 ? round($size/(1024*1024), 2) . ' MB' : round($size/1024, 2) . ' KB';
                    ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>
                                    </div>
                                    <span class="text-indigo-950 font-bold"><?php echo sanitize($file); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-indigo-500 font-medium"><?php echo $sizeStr; ?></td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="backups/<?php echo urlencode($file); ?>" 
                                       class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg font-bold hover:bg-indigo-600 hover:text-white transition-all text-xs" download>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Unduh
                                    </a>
                                    <form method="post" action="index.php?module=backup&page=restore" class="inline" id="restoreForm_<?php echo urlencode($file); ?>">
                                        <input type="hidden" name="file" value="<?php echo urlencode($file); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <button type="button" onclick="confirmRestore('<?php echo urlencode($file); ?>')" 
                                                class="inline-flex items-center gap-2 bg-amber-50 text-amber-600 px-4 py-2 rounded-lg font-bold hover:bg-amber-600 hover:text-white transition-all text-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Restore
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmRestore(file) {
    Swal.fire({
        title: 'Restore Database?',
        html: 'Data saat ini akan ditimpa dengan cadangan dari <b>' + decodeURIComponent(file) + '</b>. Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Pulihkan Sekarang',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('restoreForm_' + file).submit();
        }
    });
}
</script>
