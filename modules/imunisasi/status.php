<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$balitas = fetch_all("SELECT id, nama FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Status Imunisasi
            </h3>
            <p class="text-sm text-indigo-500/70 font-medium">Status kelengkapan imunisasi per balita</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Nama Balita</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Total Target</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Selesai</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Belum</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Progres</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    <?php if (count($balitas) === 0): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-indigo-300 font-medium">Belum ada balita.</td>
                        </tr>
                    <?php endif; ?>
                    <?php 
                        $master = getVaksinMaster();
                        $total = count($master);
                        foreach ($balitas as $balita):
                        
                        $summary = fetch_one("SELECT COUNT(DISTINCT jenis_imunisasi) AS selesai FROM imunisasi WHERE balita_id = ? AND status = 'sudah'", [$balita['id']]);
                        $selesai = $summary['selesai'] ?? 0;
                        $belum = $total - $selesai;
                        $persen = $total > 0 ? round(($selesai / $total) * 100) : 0;
                    ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        <?php echo strtoupper(substr($balita['nama'], 0, 1)); ?>
                                    </div>
                                    <span class="text-indigo-950 font-bold group-hover:text-indigo-600 transition-colors"><?php echo sanitize($balita['nama']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-indigo-400"><?php echo $total; ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg font-bold"><?php echo $selesai; ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-lg font-bold"><?php echo $belum; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 bg-indigo-50 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-400 to-pink-400 rounded-full transition-all duration-500" style="width: <?php echo $persen; ?>%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-indigo-900"><?php echo $persen; ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
