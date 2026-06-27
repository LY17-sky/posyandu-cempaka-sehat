<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
$balita = db()->selectOne("SELECT * FROM balita WHERE id = ?", [$id]);

if (!$balita) {
    echo "<div class='card p-12 text-center'><p class='text-indigo-300 font-bold'>Data balita tidak ditemukan.</p></div>";
    return;
}

if (!checkBalitaAccess($id)) {
    echo "<div class='card p-12 text-center'><p class='text-indigo-300 font-bold'>Anda tidak memiliki akses ke data balita ini.</p></div>";
    return;
}

// Fetch weighing history
$timbang = db()->select("SELECT * FROM timbang WHERE balita_id = ? ORDER BY tgl_timbang DESC", [$id]);

// Fetch immunization history
$imunisasi = db()->select("SELECT * FROM imunisasi WHERE balita_id = ? ORDER BY tgl_imunisasi DESC", [$id]);

// Age calculation (moved up for WHO chart data use)
$birthDate = new DateTime($balita['tgl_lahir']);
$now = new DateTime();
$diff = $birthDate->diff($now);
$months = ($diff->y * 12) + $diff->m;

// Prepare chart data — chronological, with decimal age for WHO KMS chart
$chartData = db()->select("SELECT tgl_timbang, bb, tb FROM timbang WHERE balita_id = ? ORDER BY tgl_timbang ASC", [$id]);
$labels    = [];
$bbData    = [];
$tbData    = [];
$bbWhoData = []; // {x: age_months_decimal, y: bb}
$tbWhoData = []; // {x: age_months_decimal, y: tb}
foreach ($chartData as $row) {
    $labels[] = date('d/m/y', strtotime($row['tgl_timbang']));
    $bbData[] = $row['bb'];
    $tbData[] = $row['tb'];
    $tDate    = new DateTime($row['tgl_timbang']);
    $dAge     = $birthDate->diff($tDate);
    $ageM     = ($dAge->y * 12) + $dAge->m + ($dAge->d / 30.44);
    $bbWhoData[] = ['x' => round($ageM, 2), 'y' => floatval($row['bb']), 'tgl' => date('d M Y', strtotime($row['tgl_timbang']))];
    $tbWhoData[] = ['x' => round($ageM, 2), 'y' => floatval($row['tb']), 'tgl' => date('d M Y', strtotime($row['tgl_timbang']))];
}

$lastTimbang = $timbang[0] ?? null;
$statusGizi  = "Normal";
$statusColor = "emerald";
if ($lastTimbang) {
    $result = getStatusGiziByAge(
        $lastTimbang['bb'], $lastTimbang['tb'],
        $lastTimbang['lk'] ?? 0, $lastTimbang['lila'] ?? 0,
        $months, $balita['jenis_kelamin'] ?? 'L'
    );
    if ($result['color'] === 'Merah') {
        $statusGizi  = $result['status'];
        $statusColor = "red";
    } elseif ($result['color'] === 'Kuning') {
        $statusGizi  = $result['status'];
        $statusColor = "amber";
    }
}

// WHO reference data for Z-Score analysis
$jk = $balita['jenis_kelamin'] ?? 'L';
$whoRefM     = [0,   6,    12,   24,   36,   48,    60   ];
$medBB_L     = [3.3, 7.9,  9.6,  12.2, 14.3, 16.3,  18.3 ];
$medBB_P     = [3.2, 7.3,  8.9,  11.5, 13.9, 16.1,  18.2 ];
$sdBB_L      = [0.4, 0.8,  1.0,  1.2,  1.4,  1.7,   2.0  ];
$sdBB_P      = [0.4, 0.8,  1.0,  1.3,  1.5,  1.8,   2.1  ];
$medTB_L     = [49.9,67.6, 75.7, 87.8, 96.1, 103.3, 110.0];
$medTB_P     = [49.1,65.7, 74.0, 86.4, 95.1, 102.7, 109.4];
$sdTB_L      = [1.9, 2.4,  2.6,  2.8,  3.0,  3.2,   3.4  ];
$sdTB_P      = [1.9, 2.4,  2.6,  2.8,  3.0,  3.2,   3.4  ];
$whoMedBB    = $jk === 'L' ? $medBB_L : $medBB_P;
$whoSdBB     = $jk === 'L' ? $sdBB_L  : $sdBB_P;
$whoMedTB    = $jk === 'L' ? $medTB_L : $medTB_P;
$whoSdTB     = $jk === 'L' ? $sdTB_L  : $sdTB_P;

function whoInterp($x, $xPts, $yPts) {
    $n = count($xPts);
    if ($x <= $xPts[0]) return $yPts[0];
    if ($x >= $xPts[$n-1]) return $yPts[$n-1];
    for ($i = 0; $i < $n-1; $i++) {
        if ($x >= $xPts[$i] && $x <= $xPts[$i+1]) {
            $r = ($x - $xPts[$i]) / ($xPts[$i+1] - $xPts[$i]);
            return $yPts[$i] + $r * ($yPts[$i+1] - $yPts[$i]);
        }
    }
    return $yPts[$n-1];
}

$lastBbZScore = null;
$lastTbZScore = null;
$bbWhoStatus  = 'Normal';
$tbWhoStatus  = 'Normal';
if ($lastTimbang) {
    $lDate  = new DateTime($lastTimbang['tgl_timbang']);
    $lDiff  = $birthDate->diff($lDate);
    $lAgeM  = ($lDiff->y * 12) + $lDiff->m + ($lDiff->d / 30.44);
    if (!empty($lastTimbang['bb'])) {
        $mBB = whoInterp($lAgeM, $whoRefM, $whoMedBB);
        $sBB = whoInterp($lAgeM, $whoRefM, $whoSdBB);
        if ($sBB > 0) {
            $lastBbZScore = round(($lastTimbang['bb'] - $mBB) / $sBB, 2);
            if      ($lastBbZScore < -3) $bbWhoStatus = 'Gizi Buruk';
            elseif  ($lastBbZScore < -2) $bbWhoStatus = 'Gizi Kurang';
            elseif  ($lastBbZScore > 3)  $bbWhoStatus = 'Obesitas';
            elseif  ($lastBbZScore > 2)  $bbWhoStatus = 'Risiko Gizi Lebih';
            else                         $bbWhoStatus = 'Gizi Baik';
        }
    }
    if (!empty($lastTimbang['tb'])) {
        $mTB = whoInterp($lAgeM, $whoRefM, $whoMedTB);
        $sTB = whoInterp($lAgeM, $whoRefM, $whoSdTB);
        if ($sTB > 0) {
            $lastTbZScore = round(($lastTimbang['tb'] - $mTB) / $sTB, 2);
            if      ($lastTbZScore < -3) $tbWhoStatus = 'Sangat Pendek';
            elseif  ($lastTbZScore < -2) $tbWhoStatus = 'Pendek (Stunting)';
            elseif  ($lastTbZScore > 2)  $tbWhoStatus = 'Tinggi';
            else                         $tbWhoStatus = 'Normal';
        }
    }
}

// Growth trend analysis
$growthAnalysis = [
    'bbTrend'      => 'neutral',
    'tbTrend'      => 'neutral',
    'bbStatus'     => '',
    'tbStatus'     => '',
    'bbWhoStatus'  => $bbWhoStatus,
    'tbWhoStatus'  => $tbWhoStatus,
    'bbZScore'     => $lastBbZScore,
    'tbZScore'     => $lastTbZScore,
    'dataCount'    => count($chartData),
    'hasEnoughData'=> count($chartData) >= 2
];
if ($growthAnalysis['hasEnoughData']) {
    $recentBB   = $bbData[count($bbData)-1] ?? 0;
    $previousBB = $bbData[count($bbData)-2] ?? 0;
    $bbChange   = $recentBB - $previousBB;
    if ($bbChange > 0.2)     { $growthAnalysis['bbTrend'] = 'increase'; $growthAnalysis['bbStatus'] = 'Berat badan meningkat dengan baik'; }
    elseif ($bbChange >= 0)  { $growthAnalysis['bbTrend'] = 'stable';   $growthAnalysis['bbStatus'] = 'Berat badan stabil/sedikit meningkat'; }
    else                     { $growthAnalysis['bbTrend'] = 'decrease'; $growthAnalysis['bbStatus'] = 'Berat badan cenderung menurun — perlu perhatian'; }
    $recentTB   = $tbData[count($tbData)-1] ?? 0;
    $previousTB = $tbData[count($tbData)-2] ?? 0;
    $tbChange   = $recentTB - $previousTB;
    if ($tbChange > 0.5)     { $growthAnalysis['tbTrend'] = 'increase'; $growthAnalysis['tbStatus'] = 'Tinggi badan meningkat dengan baik'; }
    elseif ($tbChange >= 0)  { $growthAnalysis['tbTrend'] = 'stable';   $growthAnalysis['tbStatus'] = 'Tinggi badan stabil/sedikit meningkat'; }
    else                     { $growthAnalysis['tbTrend'] = 'decrease'; $growthAnalysis['tbStatus'] = 'Tinggi badan cenderung menurun — kemungkinan kesalahan data'; }
} else {
    $growthAnalysis['bbStatus'] = 'Data belum cukup untuk analisis tren (minimal 2 data penimbangan)';
    $growthAnalysis['tbStatus'] = 'Data belum cukup untuk analisis tren (minimal 2 data penimbangan)';
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

            <!-- WHO Z-Score Status Row -->
            <?php
            $bbZColor = 'emerald'; $bbZText = 'text-emerald-950';
            if (in_array($bbWhoStatus, ['Gizi Buruk','Gizi Kurang'])) { $bbZColor='rose'; $bbZText='text-rose-950'; }
            elseif (in_array($bbWhoStatus, ['Risiko Gizi Lebih','Obesitas'])) { $bbZColor='amber'; $bbZText='text-amber-950'; }
            $tbZColor = 'emerald'; $tbZText = 'text-emerald-950';
            if (in_array($tbWhoStatus, ['Sangat Pendek','Pendek (Stunting)'])) { $tbZColor='rose'; $tbZText='text-rose-950'; }
            elseif ($tbWhoStatus === 'Tinggi') { $tbZColor='blue'; $tbZText = 'text-blue-950'; }
            ?>
            <div class="grid grid-cols-3 gap-4">
                <div class="card p-5 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl text-center">
                    <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-1">Status Gizi Terakhir</p>
                    <div class="flex items-center justify-center gap-2">
                        <p class="text-lg font-black text-indigo-950"><?php echo $statusGizi; ?></p>
                        <span class="w-3 h-3 rounded-full bg-<?php echo $statusColor; ?>-500 shadow-lg shadow-<?php echo $statusColor; ?>-200"></span>
                    </div>
                </div>
                <div class="card p-5 bg-<?php echo $bbZColor; ?>-50/50 border border-<?php echo $bbZColor; ?>-100 shadow-lg shadow-<?php echo $bbZColor; ?>-100/50 rounded-3xl text-center">
                    <p class="text-[10px] font-black text-<?php echo $bbZColor; ?>-400 uppercase tracking-widest mb-1">Status BB/U (WHO)</p>
                    <p class="font-black <?php echo $bbZText; ?>"><?php echo $bbWhoStatus; ?></p>
                    <?php if ($lastBbZScore !== null): ?>
                    <p class="text-[10px] text-<?php echo $bbZColor; ?>-500 mt-1 font-medium">Z-Score: <?php echo $lastBbZScore; ?></p>
                    <?php endif; ?>
                </div>
                <div class="card p-5 bg-<?php echo $tbZColor; ?>-50/50 border border-<?php echo $tbZColor; ?>-100 shadow-lg shadow-<?php echo $tbZColor; ?>-100/50 rounded-3xl text-center">
                    <p class="text-[10px] font-black text-<?php echo $tbZColor; ?>-400 uppercase tracking-widest mb-1">Status TB/U (WHO)</p>
                    <p class="font-black <?php echo $tbZText; ?>"><?php echo $tbWhoStatus; ?></p>
                    <?php if ($lastTbZScore !== null): ?>
                    <p class="text-[10px] text-<?php echo $tbZColor; ?>-500 mt-1 font-medium">Z-Score: <?php echo $lastTbZScore; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grafik KMS BB/U Z-Score -->
            <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl relative overflow-hidden">
                <h3 class="text-lg font-black text-indigo-950 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Grafik KMS Berat Badan (BB/U) — Z-Score WHO
                </h3>
                <?php if (empty($bbWhoData)): ?>
                <div class="h-[300px] flex items-center justify-center">
                    <p class="text-sm text-indigo-300 font-medium">Belum ada data penimbangan berat badan.</p>
                </div>
                <?php else: ?>
                <div class="h-[320px]">
                    <canvas id="kmsBbChart"></canvas>
                </div>
                <?php endif; ?>
            </div>

            <!-- Grafik KMS TB/U Z-Score -->
            <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl relative overflow-hidden">
                <h3 class="text-lg font-black text-indigo-950 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Grafik KMS Tinggi Badan (TB/U) — Z-Score WHO
                </h3>
                <?php if (empty($tbWhoData)): ?>
                <div class="h-[300px] flex items-center justify-center">
                    <p class="text-sm text-indigo-300 font-medium">Belum ada data penimbangan tinggi badan.</p>
                </div>
                <?php else: ?>
                <div class="h-[320px]">
                    <canvas id="kmsTbChart"></canvas>
                </div>
                <?php endif; ?>
            </div>

            <!-- Chart Explanation - KMS Z-Score -->
            <div class="card p-6 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl">
                <h3 class="text-lg font-black text-indigo-950 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z" clip-rule="evenodd"></path></svg>
                    Interpretasi Grafik KMS
                </h3>

                <?php if (empty($bbWhoData)): ?>
                <div class="p-4 rounded-2xl bg-slate-50/50 border border-slate-100">
                    <p class="text-xs text-slate-600 font-medium">Belum ada data penimbangan. Setelah melakukan penimbangan setidaknya satu kali, grafik dan interpretasi KMS akan muncul di sini.</p>
                </div>
                <?php else: ?>
                <div class="space-y-6">
                    <div>
                        <h4 class="text-sm font-black text-indigo-950 mb-2">Kurva Referensi WHO (Garis Berwarna)</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-0.5 bg-red-400"></span>
                                <span class="text-xs font-bold text-rose-700">+3 SD (Sangat Tinggi)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-0.5 bg-amber-400"></span>
                                <span class="text-xs font-bold text-amber-700">+2 SD (Risiko Gizi Lebih)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-0.5 bg-emerald-500"></span>
                                <span class="text-xs font-bold text-emerald-700">Median 0 SD (Normal)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-0.5 bg-blue-400"></span>
                                <span class="text-xs font-bold text-blue-700">-2 SD (Pendek / Kekurangan)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-0.5 bg-red-500"></span>
                                <span class="text-xs font-bold text-rose-700">-3 SD (Sangat Kurang)</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl <?php echo in_array($bbWhoStatus, ['Gizi Buruk','Gizi Kurang']) ? 'bg-rose-50/50 border border-rose-100' : (in_array($bbWhoStatus, ['Risiko Gizi Lebih','Obesitas']) ? 'bg-amber-50/50 border border-amber-100' : 'bg-emerald-50/50 border border-emerald-100'); ?>">
                            <h4 class="text-sm font-black <?php
                                if (in_array($bbWhoStatus, ['Gizi Buruk','Gizi Kurang'])) echo 'text-rose-950';
                                elseif (in_array($bbWhoStatus, ['Risiko Gizi Lebih','Obesitas'])) echo 'text-amber-950';
                                else echo 'text-emerald-950';
                            ?> mb-2">Berat Badan (BB/U)</h4>
                            <p class="text-xs <?php
                                if (in_array($bbWhoStatus, ['Gizi Buruk','Gizi Kurang'])) echo 'text-rose-700';
                                elseif (in_array($bbWhoStatus, ['Risiko Gizi Lebih','Obesitas'])) echo 'text-amber-700';
                                else echo 'text-emerald-700';
                            ?> font-medium">
                                <?php echo $bbWhoStatus; ?> — Z-Score terakhir: <?php echo $lastBbZScore !== null ? $lastBbZScore : 'N/A'; ?>
                            </p>
                        </div>
                        <div class="p-4 rounded-2xl <?php echo in_array($tbWhoStatus, ['Sangat Pendek','Pendek (Stunting)']) ? 'bg-rose-50/50 border border-rose-100' : ($tbWhoStatus === 'Tinggi' ? 'bg-blue-50/50 border border-blue-100' : 'bg-emerald-50/50 border border-emerald-100'); ?>">
                            <h4 class="text-sm font-black <?php
                                if (in_array($tbWhoStatus, ['Sangat Pendek','Pendek (Stunting)'])) echo 'text-rose-950';
                                elseif ($tbWhoStatus === 'Tinggi') echo 'text-blue-950';
                                else echo 'text-emerald-950';
                            ?> mb-2">Tinggi Badan (TB/U)</h4>
                            <p class="text-xs <?php
                                if (in_array($tbWhoStatus, ['Sangat Pendek','Pendek (Stunting)'])) echo 'text-rose-700';
                                elseif ($tbWhoStatus === 'Tinggi') echo 'text-blue-700';
                                else echo 'text-emerald-700';
                            ?> font-medium">
                                <?php echo $tbWhoStatus; ?> — Z-Score terakhir: <?php echo $lastTbZScore !== null ? $lastTbZScore : 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="p-4 rounded-2xl mt-6 <?php
                    if ($statusColor === 'red') echo 'bg-rose-50/50 border border-rose-100';
                    elseif ($statusColor === 'amber') echo 'bg-amber-50/50 border border-amber-100';
                    else echo 'bg-emerald-50/50 border border-emerald-100';
                ?>">
                    <h4 class="text-sm font-black <?php
                        if ($statusColor === 'red') echo 'text-rose-950';
                        elseif ($statusColor === 'amber') echo 'text-amber-950';
                        else echo 'text-emerald-950';
                    ?> mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 <?php
                            if ($statusColor === 'red') echo 'text-rose-500';
                            elseif ($statusColor === 'amber') echo 'text-amber-500';
                            else echo 'text-emerald-500';
                        ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        Rekomendasi Kesehatan
                    </h4>
                    <ul class="text-xs <?php
                        if ($statusColor === 'red') echo 'text-rose-700';
                        elseif ($statusColor === 'amber') echo 'text-amber-700';
                        else echo 'text-emerald-700';
                    ?> space-y-1 list-disc list-inside font-medium">
                        <li>Status gizi: <strong><?php echo $statusGizi; ?></strong> (BB/U: <?php echo $bbWhoStatus; ?><?php echo $lastBbZScore !== null ? " Z:{$lastBbZScore}" : ''; ?>, TB/U: <?php echo $tbWhoStatus; ?><?php echo $lastTbZScore !== null ? " Z:{$lastTbZScore}" : ''; ?>)</li>
                        <?php if ($bbWhoStatus === 'Gizi Baik' && $tbWhoStatus === 'Normal'): ?>
                        <li>Pertumbuhan sangat baik sesuai standar WHO — lanjutkan pola asupan nutrisi yang sekarang</li>
                        <?php elseif ($bbWhoStatus === 'Obesitas' || $bbWhoStatus === 'Risiko Gizi Lebih'): ?>
                        <li>Perlu pembatasan asupan gizi berlebih dan perbanyak aktivitas fisik anak</li>
                        <?php elseif (in_array($bbWhoStatus, ['Gizi Buruk','Gizi Kurang'])): ?>
                        <li>Perlu peningkatan asupan gizi segera — hubungi bidan atau dokter anak</li>
                        <?php else: ?>
                        <li>Pertumbuhan stabil — pantau terus dengan penimbangan rutin setiap bulan untuk monitoring optimal</li>
                        <?php endif; ?>
                        <?php if (in_array($tbWhoStatus, ['Pendek (Stunting)','Sangat Pendek'])): ?>
                        <li>Terdeteksi risiko stunting pada TB/U — segera konsultasi ke tenaga kesehatan untuk penanganan lebih lanjut</li>
                        <?php endif; ?>
                        <?php if ($statusColor === 'red' || $statusColor === 'amber'): ?>
                        <li>Konsultasikan dengan bidan atau tenaga kesehatan untuk penanganan lebih lanjut</li>
                        <?php endif; ?>
                        <li>Lakukan penimbangan rutin setiap bulan untuk monitoring optimal perkembangan balita</li>
                    </ul>
                </div>

                <?php if ($growthAnalysis['hasEnoughData']): ?>
                <div class="p-4 rounded-2xl rounded-t-none -mt-2 border border-indigo-100 bg-indigo-50/30">
                    <h4 class="text-sm font-black text-indigo-950 mb-3">Analisis Tren (Data Historis)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3 rounded-xl <?php
                            if ($growthAnalysis['bbTrend'] === 'increase') echo 'bg-emerald-50/50';
                            elseif ($growthAnalysis['bbTrend'] === 'stable') echo 'bg-blue-50/50';
                            else echo 'bg-amber-50/50';
                        ?>">
                            <span class="text-xs font-bold <?php
                                if ($growthAnalysis['bbTrend'] === 'increase') echo 'text-emerald-600';
                                elseif ($growthAnalysis['bbTrend'] === 'stable') echo 'text-blue-600';
                                else echo 'text-amber-600';
                            ?> uppercase tracking-wider">Berat Badan</span>
                            <p class="text-sm font-black text-indigo-950 mt-1"><?php echo $growthAnalysis['bbStatus']; ?></p>
                        </div>
                        <div class="p-3 rounded-xl <?php
                            if ($growthAnalysis['tbTrend'] === 'increase') echo 'bg-emerald-50/50';
                            elseif ($growthAnalysis['tbTrend'] === 'stable') echo 'bg-blue-50/50';
                            else echo 'bg-amber-50/50';
                        ?>">
                            <span class="text-xs font-bold <?php
                                if ($growthAnalysis['tbTrend'] === 'increase') echo 'text-emerald-600';
                                elseif ($growthAnalysis['tbTrend'] === 'stable') echo 'text-blue-600';
                                else echo 'text-amber-600';
                            ?> uppercase tracking-wider">Tinggi Badan</span>
                            <p class="text-sm font-black text-indigo-950 mt-1"><?php echo $growthAnalysis['tbStatus']; ?></p>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-4 rounded-2xl bg-slate-50/50 border border-slate-100 mt-6">
                    <p class="text-xs text-slate-600 font-medium">Lakukan penimbangan rutin setiap bulan untuk mendapatkan analisis tren dan interpretsi yang lebih akurat.</p>
                </div>
                <?php endif; ?>
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
    const bbData = <?php echo json_encode($bbWhoData); ?>;
    const tbData = <?php echo json_encode($tbWhoData); ?>;
    const isLaki = <?php echo json_encode(($balita['jenis_kelamin'] ?? 'L') === 'L'); ?>;

    const refMonths = [0, 6, 12, 24, 36, 48, 60];
    const medianL = [3.3, 7.9, 9.6, 12.2, 14.3, 16.3, 18.3];
    const medianP = [3.2, 7.3, 8.9, 11.5, 13.9, 16.1, 18.2];
    const sdL     = [0.4, 0.8, 1.0, 1.2, 1.4, 1.7, 2.0];
    const sdP     = [0.4, 0.8, 1.0, 1.3, 1.5, 1.8, 2.1];
    const median  = isLaki ? medianL : medianP;
    const sd      = isLaki ? sdL     : sdP;

    function interpolate(x, xP, yP) {
        for (let i = 0; i < xP.length - 1; i++) {
            if (x >= xP[i] && x <= xP[i+1]) {
                return yP[i] + ((x - xP[i]) / (xP[i+1] - xP[i])) * (yP[i+1] - yP[i]);
            }
        }
        return yP[yP.length - 1];
    }

    function buildWhoLines(dataArr) {
        const labels = [];
        const sd3Pos = [], sd2Pos = [], sd0 = [], sd2Neg = [], sd3Neg = [];
        const maxDataX = dataArr.length > 0 ? Math.ceil(Math.max(...dataArr.map(d => d.x)) / 6) * 6 : 24;
        const chartMaxX = Math.max(24, Math.min(60, maxDataX + 6));
        for (let m = 0; m <= chartMaxX; m++) {
            labels.push(m);
            const mV = interpolate(m, refMonths, median);
            const sV = interpolate(m, refMonths, sd);
            sd3Pos.push({ x: m, y: +(mV + (3 * sV)).toFixed(2) });
            sd2Pos.push({ x: m, y: +(mV + (2 * sV)).toFixed(2) });
            sd0.push({ x: m, y: +(mV).toFixed(2) });
            sd2Neg.push({ x: m, y: +(mV - (2 * sV)).toFixed(2) });
            sd3Neg.push({ x: m, y: +(mV - (3 * sV)).toFixed(2) });
        }
        return { labels, sd3Pos, sd2Pos, sd0, sd2Neg, sd3Neg, chartMaxX };
    }

    // BB/U Chart
    const bbCanvas = document.getElementById('kmsBbChart');
    if (bbCanvas && bbData.length > 0) {
        const bbLines = buildWhoLines(bbData);
        new Chart(bbCanvas.getContext('2d'), {
            type: 'scatter',
            data: {
                datasets: [
                    {
                        label: '+3 SD (Sangat Tinggi/Gejolak)', data: bbLines.sd3Pos,
                        borderColor: 'rgba(239, 68, 68, 0.6)', borderWidth: 1, pointRadius: 0, fill: false, tension: 0.4, type: 'line'
                    },
                    {
                        label: '+2 SD (Risiko Gizi Lebih)', data: bbLines.sd2Pos,
                        borderColor: 'rgba(245, 158, 11, 0.7)', backgroundColor: 'rgba(239, 68, 68, 0.06)', borderWidth: 1, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: 'Median / 0 SD (Normal)', data: bbLines.sd0,
                        borderColor: 'rgba(16, 185, 129, 0.85)', backgroundColor: 'rgba(16, 185, 129, 0.12)', borderWidth: 2, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: '-2 SD (Kurang)', data: bbLines.sd2Neg,
                        borderColor: 'rgba(59, 130, 246, 0.7)', backgroundColor: 'rgba(16, 185, 129, 0.12)', borderWidth: 1, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: '-3 SD (Gizi Buruk)', data: bbLines.sd3Neg,
                        borderColor: 'rgba(239, 68, 68, 0.7)', backgroundColor: 'rgba(245, 158, 11, 0.08)', borderWidth: 1, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: 'BB Anak (kg)', data: bbData,
                        borderColor: '#3B82F6', backgroundColor: '#3B82F6', borderWidth: 3,
                        pointRadius: 7, pointHoverRadius: 9, pointBackgroundColor: '#fff', pointBorderWidth: 2,
                        fill: false, tension: 0, showLine: true
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { type: 'linear', min: 0, max: bbLines.chartMaxX, ticks: { stepSize: 1, callback: v => v + ' bln', font: { size: 10 } }, grid: { display: false }, title: { display: true, text: 'Usia (Bulan)', font: { weight: 'bold' } } },
                    y: { title: { display: true, text: 'Berat Badan (kg)', font: { weight: 'bold' } }, grid: { color: 'rgba(99,102,241,0.07)' } }
                },
                plugins: {
                    legend: { labels: { usePointStyle: true, boxWidth: 8, font: { weight: 'bold' } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                if (ctx.datasetIndex === 5) { const d = ctx.raw; return 'BB: ' + d.y + ' kg (' + d.tgl + ')'; }
                                return ctx.dataset.label + ': ' + ctx.raw.y.toFixed(2) + ' kg';
                            }
                        }
                    }
                }
            }
        });
    }

    // TB/U Chart
    const tbCanvas = document.getElementById('kmsTbChart');
    if (tbCanvas && tbData.length > 0) {
        const tbLines = buildWhoLines(tbData);
        new Chart(tbCanvas.getContext('2d'), {
            type: 'scatter',
            data: {
                datasets: [
                    {
                        label: '+3 SD (Sangat Tinggi)', data: tbLines.sd3Pos,
                        borderColor: 'rgba(239, 68, 68, 0.6)', borderWidth: 1, pointRadius: 0, fill: false, tension: 0.4, type: 'line'
                    },
                    {
                        label: '+2 SD (Tinggi)', data: tbLines.sd2Pos,
                        borderColor: 'rgba(59, 130, 246, 0.7)', backgroundColor: 'rgba(59,130,246,0.06)', borderWidth: 1, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: 'Median / 0 SD (Normal)', data: tbLines.sd0,
                        borderColor: 'rgba(16, 185, 129, 0.85)', backgroundColor: 'rgba(16,185,129,0.12)', borderWidth: 2, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: '-2 SD (Pendek)', data: tbLines.sd2Neg,
                        borderColor: 'rgba(59, 130, 246, 0.8)', backgroundColor: 'rgba(59, 130, 246, 0.12)', borderWidth: 1, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: '-3 SD (Sangat Pendek)', data: tbLines.sd3Neg,
                        borderColor: 'rgba(239, 68, 68, 0.7)', backgroundColor: 'rgba(245,158,11,0.08)', borderWidth: 1, pointRadius: 0, fill: '-1', tension: 0.4, type: 'line'
                    },
                    {
                        label: 'TB Anak (cm)', data: tbData,
                        borderColor: '#EC4899', backgroundColor: '#EC4899', borderWidth: 3,
                        pointRadius: 7, pointHoverRadius: 9, pointBackgroundColor: '#fff', pointBorderWidth: 2,
                        fill: false, tension: 0, showLine: true
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { type: 'linear', min: 0, max: tbLines.chartMaxX, ticks: { stepSize: 1, callback: v => v + ' bln', font: { size: 10 } }, grid: { display: false }, title: { display: true, text: 'Usia (Bulan)', font: { weight: 'bold' } } },
                    y: { title: { display: true, text: 'Tinggi Badan (cm)', font: { weight: 'bold' } }, grid: { color: 'rgba(236,72,153,0.07)' } }
                },
                plugins: {
                    legend: { labels: { usePointStyle: true, boxWidth: 8, font: { weight: 'bold' } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                if (ctx.datasetIndex === 5) { const d = ctx.raw; return 'TB: ' + d.y + ' cm (' + d.tgl + ')'; }
                                return ctx.dataset.label + ': ' + ctx.raw.y.toFixed(1) + ' cm';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
