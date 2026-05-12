<?php
$id = intval($_GET['id'] ?? 0);
$balita = db()->selectOne("SELECT * FROM balita WHERE id = ?", [$id]);

if (!$balita || !checkBalitaAccess($id)) {
    echo "<div class='card p-12 text-center'><p class='text-indigo-300 font-bold'>Data balita tidak ditemukan atau Anda tidak memiliki akses.</p></div>";
    return;
}

// Fetch weighing history
$timbang = db()->select("SELECT * FROM timbang WHERE balita_id = ? ORDER BY tgl_timbang DESC", [$id]);

// Fetch immunization history
$imunisasi = db()->select("SELECT * FROM imunisasi WHERE balita_id = ? ORDER BY tgl_imunisasi DESC", [$id]);

// Prepare chart data (chronological)
$chartData = db()->select("SELECT tgl_timbang, bb, tb FROM timbang WHERE balita_id = ? ORDER BY tgl_timbang ASC", [$id]);
$labels = [];
$bbData = [];
$tbData = [];
foreach ($chartData as $row) {
    $labels[] = date('d/m/y', strtotime($row['tgl_timbang']));
    $bbData[] = $row['bb'];
    $tbData[] = $row['tb'];
}

// Age calculation
$birthDate = new DateTime($balita['tgl_lahir']);
$now = new DateTime();
$diff = $birthDate->diff($now);
$months = ($diff->y * 12) + $diff->m;

// Status Gizi (Simplistic)
$lastTimbang = $timbang[0] ?? null;
$statusGizi = "Normal";
$statusColor = "emerald";
if ($lastTimbang) {
    if ($lastTimbang['bb'] < 8.5) {
        $statusGizi = "Kurang Gizi";
        $statusColor = "red";
    } elseif ($lastTimbang['bb'] > 12) {
        $statusGizi = "Berlebih Gizi";
        $statusColor = "amber";
    }
}
?>

<div class="space-y-8 animate-fade-in">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <?php $backUrl = isUserView() ? 'index.php?module=dashboard&page=home' : 'index.php?module=balita&page=daftar'; ?>
            <a href="<?php echo $backUrl; ?>" class="p-2 rounded-xl bg-white border border-indigo-100 text-indigo-500 hover:bg-indigo-50 transition-all shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-indigo-950">Detail Balita</h1>
                <p class="text-sm text-indigo-400 font-medium">Informasi lengkap kesehatan anak</p>
            </div>
        </div>
        <div class="flex gap-3">
             <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-white border border-indigo-100 text-indigo-600 font-bold hover:bg-indigo-50 transition-all shadow-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="lg:col-span-1 space-y-8">
            <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-24 h-24 bg-indigo-100/30 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="relative z-10 text-center mb-8">
                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-500 to-pink-500 mx-auto mb-4 flex items-center justify-center text-white text-3xl font-black shadow-xl">
                        <?php echo strtoupper(substr($balita['nama'], 0, 1)); ?>
                    </div>
                    <h2 class="text-xl font-black text-indigo-950"><?php echo sanitize($balita['nama']); ?></h2>
                    <p class="text-xs font-bold text-indigo-300 uppercase tracking-widest mt-1">ID: <?php echo str_pad($balita['id'], 5, '0', STR_PAD_LEFT); ?></p>
                </div>

                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-50">
                        <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest block mb-1">Status Gizi Terakhir</span>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-black text-indigo-950"><?php echo $statusGizi; ?></span>
                            <span class="w-3 h-3 rounded-full bg-<?php echo $statusColor; ?>-500 shadow-lg shadow-<?php echo $statusColor; ?>-200"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-50">
                            <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest block mb-1">Usia</span>
                            <span class="text-lg font-black text-indigo-950"><?php echo $months; ?></span>
                            <span class="text-[10px] font-bold text-indigo-400">BLN</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-50">
                            <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest block mb-1">Jenis Kelamin</span>
                            <span class="text-sm font-black text-indigo-950"><?php echo $balita['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-indigo-100">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-indigo-300">NIK</span>
                            <span class="font-black text-indigo-950 font-mono"><?php echo $balita['nik'] ?: '-'; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-indigo-300">Tgl Lahir</span>
                            <span class="font-black text-indigo-950"><?php echo date('d M Y', strtotime($balita['tgl_lahir'])); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-indigo-300">Nama Ibu</span>
                            <span class="font-black text-indigo-950"><?php echo sanitize($balita['nama_ibu']); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-indigo-300">Nama Ayah</span>
                            <span class="font-black text-indigo-950"><?php echo sanitize($balita['nama_ayah'] ?: '-'); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-indigo-300">No. WhatsApp</span>
                            <span class="font-black text-indigo-950"><?php echo $balita['no_telp'] ?: '-'; ?></span>
                        </div>
                        <div class="pt-2">
                            <span class="font-bold text-indigo-300 text-xs uppercase block mb-1">Alamat</span>
                            <p class="text-sm text-indigo-950 font-bold"><?php echo sanitize($balita['alamat']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Stats & Charts -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Chart Section -->
            <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl relative overflow-hidden">
                <h3 class="text-lg font-black text-indigo-950 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Grafik Pertumbuhan
                </h3>
                <div class="h-[300px]">
                    <canvas id="detailGrowthChart"></canvas>
                </div>
            </div>

            <!-- History Tabs -->
            <div class="card bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl overflow-hidden">
                <div class="flex border-b border-indigo-50">
                    <button id="tabTimbang" onclick="switchTab('timbang')" class="flex-1 px-6 py-4 text-sm font-black text-indigo-500 border-b-2 border-indigo-500 bg-indigo-50/30 transition-all">Riwayat Penimbangan</button>
                    <button id="tabImunisasi" onclick="switchTab('imunisasi')" class="flex-1 px-6 py-4 text-sm font-bold text-indigo-300 hover:text-indigo-500 transition-all">Riwayat Imunisasi</button>
                </div>

                <!-- Timbang Table -->
                <div id="contentTimbang" class="p-0 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-indigo-50/50 text-indigo-900 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-6 py-4 text-left">Tanggal</th>
                                <th class="px-6 py-4 text-center">BB (kg)</th>
                                <th class="px-6 py-4 text-center">TB (cm)</th>
                                <th class="px-6 py-4 text-center">LK (cm)</th>
                                <th class="px-6 py-4 text-center">LILA (cm)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-50">
                            <?php if (empty($timbang)): ?>
                            <tr><td colspan="5" class="px-6 py-12 text-center text-indigo-300">Belum ada data penimbangan.</td></tr>
                            <?php else: ?>
                            <?php foreach ($timbang as $row): ?>
                            <tr class="hover:bg-indigo-50/30 transition-all">
                                <td class="px-6 py-4 font-bold text-indigo-600"><?php echo date('d M Y', strtotime($row['tgl_timbang'])); ?></td>
                                <td class="px-6 py-4 text-center font-black text-indigo-950"><?php echo $row['bb']; ?></td>
                                <td class="px-6 py-4 text-center font-bold text-indigo-900"><?php echo $row['tb']; ?></td>
                                <td class="px-6 py-4 text-center text-indigo-400"><?php echo $row['lk'] ?: '-'; ?></td>
                                <td class="px-6 py-4 text-center text-indigo-400"><?php echo $row['lila'] ?: '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Imunisasi Table -->
                <div id="contentImunisasi" class="hidden p-0 overflow-x-auto flex flex-col">
                    <?php 
                    $vaksinMaster = getVaksinMaster();
                    $rekomendasi = [];
                    foreach ($vaksinMaster as $v) {
                        $st = getVaksinStatus($balita['id'], $balita['tgl_lahir'], $v['jenis'], $v['usia_bulan']);
                        if ($st === 'segera' || $st === 'terlambat') {
                            $rekomendasi[] = $v;
                        }
                    }
                    if (!empty($rekomendasi)): 
                    ?>
                    <div class="m-6 p-4 rounded-2xl bg-amber-50 border border-amber-200">
                        <h4 class="text-sm font-black text-amber-900 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Rekomendasi Jadwal Terdekat
                        </h4>
                        <ul class="list-disc list-inside text-sm text-amber-800 font-medium">
                            <?php foreach (array_slice($rekomendasi, 0, 3) as $r): ?>
                                <li><strong><?php echo sanitize($r['jenis']); ?></strong> - <?php echo sanitize($r['keterangan']); ?> (Target: <?php echo $r['usia_bulan']; ?> bln)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <table class="min-w-full text-sm">
                        <thead class="bg-indigo-50/50 text-indigo-900 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-6 py-4 text-center">Usia Target</th>
                                <th class="px-6 py-4 text-left">Jenis Vaksin</th>
                                <th class="px-6 py-4 text-left">Keterangan</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-50">
                            <?php 
                            $vaksinMaster = getVaksinMaster();
                            foreach ($vaksinMaster as $v): 
                                $status = getVaksinStatus($balita['id'], $balita['tgl_lahir'], $v['jenis'], $v['usia_bulan']);
                                
                                $statusClass = '';
                                $statusText = '';
                                if ($status === 'sudah') {
                                    $statusClass = 'bg-emerald-100 text-emerald-600';
                                    $statusText = 'Selesai';
                                } elseif ($status === 'segera') {
                                    $statusClass = 'bg-amber-100 text-amber-600';
                                    $statusText = 'Segera';
                                } elseif ($status === 'terlambat') {
                                    $statusClass = 'bg-rose-100 text-rose-600';
                                    $statusText = 'Terlambat';
                                } else {
                                    $statusClass = 'bg-slate-100 text-slate-500';
                                    $statusText = 'Belum Waktunya';
                                }
                            ?>
                            <tr class="hover:bg-indigo-50/30 transition-all">
                                <td class="px-6 py-4 text-center font-bold text-indigo-400"><?php echo $v['usia_bulan']; ?> Bln</td>
                                <td class="px-6 py-4 font-black text-indigo-950"><?php echo sanitize($v['jenis']); ?></td>
                                <td class="px-6 py-4 text-indigo-500 text-xs font-medium"><?php echo sanitize($v['keterangan']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    const timbangBtn = document.getElementById('tabTimbang');
    const imunisasiBtn = document.getElementById('tabImunisasi');
    const timbangContent = document.getElementById('contentTimbang');
    const imunisasiContent = document.getElementById('contentImunisasi');

    if (tab === 'timbang') {
        timbangBtn.classList.add('border-indigo-500', 'text-indigo-500', 'bg-indigo-50/30', 'font-black');
        timbangBtn.classList.remove('text-indigo-300', 'font-bold');
        imunisasiBtn.classList.remove('border-indigo-500', 'text-indigo-500', 'bg-indigo-50/30', 'font-black');
        imunisasiBtn.classList.add('text-indigo-300', 'font-bold');
        timbangContent.classList.remove('hidden');
        imunisasiContent.classList.add('hidden');
    } else {
        imunisasiBtn.classList.add('border-indigo-500', 'text-indigo-500', 'bg-indigo-50/30', 'font-black');
        imunisasiBtn.classList.remove('text-indigo-300', 'font-bold');
        timbangBtn.classList.remove('border-indigo-500', 'text-indigo-500', 'bg-indigo-50/30', 'font-black');
        timbangBtn.classList.add('text-indigo-300', 'font-bold');
        imunisasiContent.classList.remove('hidden');
        timbangContent.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('detailGrowthChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [
                {
                    label: 'Berat Badan (kg)',
                    data: <?php echo json_encode($bbData); ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Tinggi Badan (cm)',
                    data: <?php echo json_encode($tbData); ?>,
                    borderColor: '#ec4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { weight: 'bold' }, usePointStyle: true }
                }
            },
            scales: {
                y: { beginAtZero: false, grid: { color: 'rgba(99, 102, 241, 0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
