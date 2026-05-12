<?php
// Ambil dan validasi parameter bulan
$bulan = $_GET['bulan'] ?? date('Y-m');
// Pastikan format Y-m valid
if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
    $bulan = date('Y-m');
}
$bulan = htmlspecialchars($bulan, ENT_QUOTES, 'UTF-8');

$posFilter = getBalitaFilter('b.id_pos');

$stats = fetch_all("
    SELECT
        t.tgl_timbang,
        b.nama,
        t.bb,
        t.tb,
        CAST((julianday('now') - julianday(b.tgl_lahir)) / 30 AS INTEGER) AS umur
    FROM timbang t
    LEFT JOIN balita b ON t.balita_id = b.id
    WHERE strftime('%Y-%m', t.tgl_timbang) = ? $posFilter
    ORDER BY t.tgl_timbang DESC
", [$bulan]);

// Format bulan untuk judul
$bulanLabel = '';
if ($bulan) {
    $ts = strtotime($bulan . '-01');
    $bulanLabel = $ts ? date('F Y', $ts) : $bulan;
}
?>

<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <!-- Header -->
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5m14 0a4 4 0 014 4v2m-3-10a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Laporan Bulanan
                </h3>
                <p class="text-sm text-indigo-500/70 font-medium">
                    Data penimbangan bulan <b><?php echo $bulanLabel ?: $bulan; ?></b>
                    <span class="mx-2 text-indigo-200">|</span>
                    <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md"><?php echo count($stats); ?> data</span>
                </p>
            </div>
            
            <form method="get" class="flex items-center gap-3 bg-white/50 p-2 rounded-xl border border-indigo-50 shadow-sm">
                <input type="hidden" name="module" value="laporan" />
                <input type="hidden" name="page"   value="bulanan" />
                <input
                    type="month"
                    name="bulan"
                    value="<?php echo $bulan; ?>"
                    class="bg-transparent border-none focus:ring-0 text-sm font-bold text-indigo-900 cursor-pointer"
                />
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-md shadow-indigo-100">
                    Filter
                </button>
            </form>
        </div>

        <!-- Tabel -->
        <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm mb-8">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Nama Balita</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Berat (kg)</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Tinggi (cm)</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Umur (bln)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    <?php if (count($stats) === 0): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-2 opacity-30">
                                    <svg class="w-16 h-16 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-indigo-900 font-bold">Tidak ada data untuk bulan ini</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats as $row): ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 text-indigo-500 font-medium"><?php echo sanitize($row['tgl_timbang']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="text-indigo-950 font-bold group-hover:text-indigo-600 transition-colors"><?php echo sanitize($row['nama']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 text-blue-600 font-bold"><?php echo number_format((float)$row['bb'], 1); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg bg-pink-50 text-pink-600 font-bold"><?php echo number_format((float)$row['tb'], 1); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-indigo-900"><?php echo (int)$row['umur']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tombol Export -->
        <div class="flex flex-wrap gap-4">
            <a href="index.php?module=laporan&page=export_excel&bulan=<?php echo urlencode($bulan); ?>"
               class="flex items-center gap-3 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white px-6 py-3 rounded-xl font-bold transition-all border border-emerald-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </a>
            <a href="index.php?module=laporan&page=export_pdf&bulan=<?php echo urlencode($bulan); ?>"
               class="flex items-center gap-3 bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white px-6 py-3 rounded-xl font-bold transition-all border border-rose-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Export PDF
            </a>
        </div>
    </div>
</div>
