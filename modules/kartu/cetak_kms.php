<?php
$balitas = fetch_all("SELECT id, nama FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
$id = intval($_GET['id'] ?? 0);
$balita = ($id && checkBalitaAccess($id)) ? fetch_one("SELECT * FROM balita WHERE id = ? AND is_active = 1", [$id]) : null;

$grafikData = [];
if ($balita) {
    $riwayat = fetch_all("SELECT tgl_timbang, bb FROM timbang WHERE balita_id = ? ORDER BY tgl_timbang ASC", [$balita['id']]);
    $birthDate = new DateTime($balita['tgl_lahir']);
    foreach ($riwayat as $r) {
        if (!$r['bb']) continue;
        $timbangDate = new DateTime($r['tgl_timbang']);
        $ageDecimal = ($birthDate->diff($timbangDate)->y * 12) + $birthDate->diff($timbangDate)->m + ($birthDate->diff($timbangDate)->d / 30.44);
        
        $grafikData[] = [
            'x' => round($ageDecimal, 2),
            'y' => floatval($r['bb']),
            'tgl' => date('d M Y', strtotime($r['tgl_timbang']))
        ];
    }
}
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Cetak KMS Balita
                </h3>
                <p class="text-sm text-indigo-500/70 font-medium">Lihat dan cetak Kartu Menuju Sehat</p>
            </div>
            
            <form method="get" class="flex items-center gap-3 bg-white/50 p-2 rounded-xl border border-indigo-50 shadow-sm">
                <input type="hidden" name="module" value="kartu" />
                <input type="hidden" name="page" value="cetak_kms" />
                <select name="id" class="bg-transparent border-none focus:ring-0 text-sm font-bold text-indigo-900 cursor-pointer min-w-[200px]">
                    <option value="">Pilih Balita...</option>
                    <?php foreach ($balitas as $item): ?>
                        <option value="<?php echo $item['id']; ?>" <?php echo $item['id'] === $id ? 'selected' : ''; ?>><?php echo sanitize($item['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-md shadow-indigo-100">
                    Tampilkan
                </button>
            </form>
        </div>

        <?php if ($balita): ?>
            <div class="rounded-3xl border border-indigo-50 bg-white/40 p-8 backdrop-blur-sm shadow-inner overflow-hidden relative">
                 <div class="absolute top-0 right-0 p-8 opacity-5">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>
                </div>

                <div class="relative z-10">
                    <h4 class="text-xl font-black text-indigo-950 mb-6 flex items-center gap-3">
                        <span class="w-2 h-8 bg-gradient-to-b from-indigo-500 to-pink-500 rounded-full"></span>
                        Kartu Menuju Sehat (KMS)
                    </h4>
                    
                    <div class="grid gap-6 md:grid-cols-2 mb-8">
                        <div class="rounded-2xl bg-white p-6 shadow-sm border border-indigo-50">
                            <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1">Nama Lengkap</p>
                            <p class="text-xl font-bold text-indigo-950"><?php echo sanitize($balita['nama']); ?></p>
                        </div>
                        <div class="rounded-2xl bg-white p-6 shadow-sm border border-indigo-50">
                            <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1">Tanggal Lahir</p>
                            <p class="text-xl font-bold text-indigo-950"><?php echo date('d F Y', strtotime($balita['tgl_lahir'])); ?></p>
                        </div>
                        <div class="rounded-2xl bg-white p-6 shadow-sm border border-indigo-50">
                            <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1">Jenis Kelamin</p>
                            <p class="text-xl font-bold text-indigo-950"><?php echo $balita['jenis_kelamin'] === 'P' ? 'Perempuan' : 'Laki-laki'; ?></p>
                        </div>
                        <div class="rounded-2xl bg-white p-6 shadow-sm border border-indigo-50">
                            <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1">Nomor NIK</p>
                            <p class="text-xl font-bold text-indigo-950 font-mono"><?php echo sanitize($balita['nik'] ?: '-'); ?></p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 p-8 rounded-2xl text-white shadow-lg shadow-indigo-200 mb-8">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="font-black text-lg mb-2">Panduan Pertumbuhan</p>
                                <p class="text-indigo-50 leading-relaxed text-sm">
                                    Gunakan KMS ini untuk memantau tumbuh kembang anak secara rutin. Pastikan balita mendapatkan:
                                    <ul class="list-disc list-inside mt-3 space-y-1 text-indigo-100/80">
                                        <li>Penimbangan berat badan setiap bulan</li>
                                        <li>Imunisasi dasar lengkap tepat waktu</li>
                                        <li>Asupan nutrisi bergizi dan ASI eksklusif</li>
                                        <li>Konsultasi dengan bidan jika ada indikasi medis</li>
                                    </ul>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik KMS -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-indigo-50 relative">
                        <h4 class="text-lg font-bold text-indigo-950 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                            Grafik Pertumbuhan (Z-Score WHO)
                        </h4>
                        <div class="w-full h-[400px]">
                            <canvas id="kmsChart"></canvas>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button onclick="window.print()" class="bg-white border-2 border-indigo-100 text-indigo-600 px-6 py-3 rounded-xl font-bold hover:bg-indigo-50 transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak Kartu
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border-2 border-dashed border-indigo-100 bg-indigo-50/20 p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="text-indigo-400 font-bold">Silakan pilih data balita terlebih dahulu untuk menampilkan KMS.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($balita): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('kmsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const jk = '<?php echo $balita['jenis_kelamin'] ?? 'L'; ?>';
    const isLaki = jk === 'L';
    
    // Titik referensi WHO (Bulan)
    const refMonths = [0, 6, 12, 24, 36, 48, 60];
    
    // Median (0 SD) untuk Laki-laki & Perempuan (Pendekatan)
    const medianL = [3.3, 7.9, 9.6, 12.2, 14.3, 16.3, 18.3];
    const medianP = [3.2, 7.3, 8.9, 11.5, 13.9, 16.1, 18.2];
    
    // Nilai 1 Standar Deviasi (Pendekatan)
    const sdL = [0.4, 0.8, 1.0, 1.2, 1.4, 1.7, 2.0];
    const sdP = [0.4, 0.8, 1.0, 1.3, 1.5, 1.8, 2.1];
    
    const median = isLaki ? medianL : medianP;
    const sd = isLaki ? sdL : sdP;
    
    function interpolate(x, xPoints, yPoints) {
        for (let i = 0; i < xPoints.length - 1; i++) {
            if (x >= xPoints[i] && x <= xPoints[i+1]) {
                const ratio = (x - xPoints[i]) / (xPoints[i+1] - xPoints[i]);
                return yPoints[i] + ratio * (yPoints[i+1] - yPoints[i]);
            }
        }
        return yPoints[yPoints.length - 1];
    }

    const labels = [];
    const sd3Pos = [], sd2Pos = [], sd0 = [], sd2Neg = [], sd3Neg = [];
    
    const maxDataX = <?php echo count($grafikData) > 0 ? ceil(end($grafikData)['x'] / 6) * 6 : 0; ?>;
    const chartMaxX = Math.max(24, Math.min(60, maxDataX + 6));
    
    for (let m = 0; m <= chartMaxX; m++) {
        labels.push(m);
        const mVal = interpolate(m, refMonths, median);
        const sVal = interpolate(m, refMonths, sd);
        
        sd3Pos.push({x: m, y: mVal + (3 * sVal)});
        sd2Pos.push({x: m, y: mVal + (2 * sVal)});
        sd0.push({x: m, y: mVal});
        sd2Neg.push({x: m, y: mVal - (2 * sVal)});
        sd3Neg.push({x: m, y: mVal - (3 * sVal)});
    }
    
    // Data nyata balita
    const actualDataRaw = <?php echo json_encode($grafikData); ?>;
    const actualData = actualDataRaw.map(d => ({x: d.x, y: d.y, tgl: d.tgl}));
    
    const themeColor = isLaki ? '#3B82F6' : '#EC4899';
    
    new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [
                {
                    label: '+3 SD (Batas Atas)',
                    data: sd3Pos,
                    borderColor: 'rgba(239, 68, 68, 0.5)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4,
                    type: 'line'
                },
                {
                    label: '+2 SD (Risiko Lebih)',
                    data: sd2Pos,
                    borderColor: 'rgba(245, 158, 11, 0.5)',
                    backgroundColor: 'rgba(239, 68, 68, 0.05)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: '-1',
                    tension: 0.4,
                    type: 'line'
                },
                {
                    label: 'Median (Normal)',
                    data: sd0,
                    borderColor: 'rgba(16, 185, 129, 0.8)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: '-1',
                    tension: 0.4,
                    type: 'line'
                },
                {
                    label: '-2 SD (Kurang)',
                    data: sd2Neg,
                    borderColor: 'rgba(245, 158, 11, 0.5)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: '-1',
                    tension: 0.4,
                    type: 'line'
                },
                {
                    label: '-3 SD (Buruk)',
                    data: sd3Neg,
                    borderColor: 'rgba(239, 68, 68, 0.5)',
                    backgroundColor: 'rgba(245, 158, 11, 0.05)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: '-1',
                    tension: 0.4,
                    type: 'line'
                },
                {
                    label: 'Berat Badan Anak',
                    data: actualData,
                    borderColor: themeColor,
                    backgroundColor: themeColor,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: false,
                    type: 'line',
                    showLine: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'linear',
                    position: 'bottom',
                    title: { display: true, text: 'Umur (Bulan)', font: { weight: 'bold' } },
                    min: 0,
                    max: chartMaxX,
                    ticks: { stepSize: 6 }
                },
                y: {
                    title: { display: true, text: 'Berat Badan (kg)', font: { weight: 'bold' } },
                    min: 0
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 5) {
                                const data = context.raw;
                                return 'BB: ' + data.y + ' kg (' + data.tgl + ')';
                            }
                            return context.dataset.label + ': ' + context.raw.y.toFixed(1) + ' kg';
                        }
                    }
                },
                legend: {
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>
