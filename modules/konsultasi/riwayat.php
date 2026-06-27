<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$riwayat = fetch_all("SELECT k.id, k.balita_id, b.nama AS balita, k.nama_pengirim, k.pertanyaan, k.jawaban, k.status, k.created_at FROM konsultasi k LEFT JOIN balita b ON k.balita_id = b.id WHERE 1=1" . getBalitaFilter('b.id_pos') . " ORDER BY k.created_at DESC");
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                Riwayat Konsultasi
            </h3>
            <p class="text-sm text-indigo-500/70 font-medium">Histori tanya jawab kesehatan balita</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Balita / Pengirim</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Pertanyaan</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Jawaban Bidan</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    <?php if (count($riwayat) === 0): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-indigo-300 font-medium">Belum ada riwayat konsultasi.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($riwayat as $item): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap text-indigo-500 font-medium text-xs">
                                <?php echo date('d M Y', strtotime($item['created_at'])); ?><br>
                                <?php echo date('H:i', strtotime($item['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($item['balita_id']): ?>
                                    <a href="?module=balita&page=detail&id=<?php echo $item['balita_id']; ?>" class="text-indigo-950 font-bold hover:text-indigo-600 transition-colors"><?php echo sanitize($item['balita']); ?></a>
                                <?php else: ?>
                                    <div class="text-indigo-950 font-bold"><?php echo sanitize($item['balita'] ?? '-'); ?></div>
                                <?php endif; ?>
                                <div class="text-xs text-indigo-400">Oleh: <?php echo sanitize($item['nama_pengirim']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-indigo-900 max-w-xs truncate" title="<?php echo sanitize($item['pertanyaan']); ?>">
                                <?php echo sanitize($item['pertanyaan']); ?>
                            </td>
                            <td class="px-6 py-4 text-indigo-700 italic max-w-xs truncate" title="<?php echo sanitize($item['jawaban'] ?? ''); ?>">
                                <?php echo $item['jawaban'] ? sanitize($item['jawaban']) : '<span class="text-indigo-200">Menunggu jawaban...</span>'; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($item['status'] === 'answered'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600">Dijawab</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-600">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
