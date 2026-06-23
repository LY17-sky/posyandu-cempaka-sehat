<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$bulan = $_GET['bulan'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
    $bulan = date('Y-m');
}

$pos_aktif = $_SESSION['pos_aktif'] ?? 0;
$posNama = $pos_aktif > 0 ? 'Posyandu Cempaka ' . $pos_aktif : 'Semua Posyandu Cempaka';

$posFilter = getBalitaFilter('b.id_pos');
$records = fetch_all("
    SELECT 
        t.tgl_timbang, 
        b.nama, 
        b.jenis_kelamin,
        t.bb, 
        t.tb, 
        CAST((julianday('now') - julianday(b.tgl_lahir)) / 30.44 AS INTEGER) AS umur 
    FROM timbang t 
    LEFT JOIN balita b ON t.balita_id = b.id 
    WHERE strftime('%Y-%m', t.tgl_timbang) = ? $posFilter 
    ORDER BY t.tgl_timbang DESC
", [$bulan]);

// Statistik
$total = count($records);
$avg_bb = $total > 0 ? array_sum(array_column($records, 'bb')) / $total : 0;
$avg_tb = $total > 0 ? array_sum(array_column($records, 'tb')) / $total : 0;

$bulanLabel = date('F Y', strtotime($bulan . '-01'));

if (isset($_GET['download'])) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="laporan_timbang_'.str_replace('-', '_', $bulan).'.html"');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Bulanan - <?php echo $bulanLabel; ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary: #4f46e5;
                --secondary: #ec4899;
                --text-main: #1e293b;
                --text-muted: #64748b;
                --border: #e2e8f0;
            }
            body { 
                font-family: 'Inter', sans-serif; 
                color: var(--text-main);
                line-height: 1.5;
                margin: 0;
                padding: 40px;
                background: #fff;
            }
            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 2px solid var(--primary);
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .logo-section {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .logo {
                width: 60px;
                height: 60px;
                object-fit: contain;
            }
            .title-section h1 {
                margin: 0;
                font-size: 24px;
                color: var(--primary);
                text-transform: uppercase;
            }
            .title-section p {
                margin: 5px 0 0;
                color: var(--text-muted);
                font-weight: 500;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
            .stat-card {
                background: #f8fafc;
                border: 1px solid var(--border);
                padding: 15px;
                border-radius: 12px;
                text-align: center;
            }
            .stat-value {
                display: block;
                font-size: 20px;
                font-weight: 700;
                color: var(--primary);
            }
            .stat-label {
                font-size: 12px;
                color: var(--text-muted);
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            table { 
                border-collapse: collapse; 
                width: 100%; 
                margin-top: 20px;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid var(--border);
            }
            th { 
                background-color: #f1f5f9; 
                color: var(--text-main);
                font-weight: 700;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.05em;
                padding: 12px 15px;
                text-align: left;
                border-bottom: 2px solid var(--border);
            }
            td { 
                padding: 12px 15px; 
                border-bottom: 1px solid var(--border);
                font-size: 13px;
            }
            tr:nth-child(even) { background-color: #fafafa; }
            .badge {
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
            }
            .badge-l { background: #dbeafe; color: #1e40af; }
            .badge-p { background: #fce7f3; color: #9d174d; }
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid var(--border);
                display: flex;
                justify-content: space-between;
                font-size: 11px;
                color: var(--text-muted);
            }
            @media print {
                body { padding: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo-section">
                <img src="assets/img/logo_anak.png" alt="Logo" class="logo">
                <div class="title-section">
                    <h1>LAPORAN PENIMBANGAN</h1>
                    <p><?php echo $posNama; ?></p>
                </div>
            </div>
            <div style="text-align: right;">
                <p style="font-weight: 700; margin: 0; color: var(--primary);"><?php echo $bulanLabel; ?></p>
                <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Dicetak: <?php echo date('d/m/Y H:i'); ?></p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value"><?php echo $total; ?></span>
                <span class="stat-label">Total Balita</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo number_format($avg_bb, 1); ?> kg</span>
                <span class="stat-label">Rata-rata Berat</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo number_format($avg_tb, 1); ?> cm</span>
                <span class="stat-label">Rata-rata Tinggi</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Balita</th>
                    <th>JK</th>
                    <th>BB (kg)</th>
                    <th>TB (cm)</th>
                    <th>Umur</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($row['tgl_timbang'])); ?></td>
                    <td style="font-weight: 600;"><?php echo sanitize($row['nama']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($row['jenis_kelamin']); ?>">
                            <?php echo $row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                        </span>
                    </td>
                    <td style="font-weight: 700; color: var(--primary);"><?php echo number_format($row['bb'], 1); ?></td>
                    <td style="font-weight: 700; color: var(--secondary);"><?php echo number_format($row['tb'], 1); ?></td>
                    <td><?php echo $row['umur']; ?> bln</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="footer">
            <div>Sistem Informasi Posyandu Cempaka Sehat</div>
            <div>Halaman 1 dari 1</div>
        </div>

        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()" style="background: var(--primary); color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Cetak ke PDF</button>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-bold text-indigo-950">Export PDF Laporan</h3>
            <p class="text-slate-500">Generate laporan penimbangan bulanan siap cetak.</p>
        </div>
        <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center text-red-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Periode</p>
            <p class="text-lg font-bold text-indigo-900"><?php echo $bulanLabel; ?></p>
        </div>
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Total Data</p>
            <p class="text-lg font-bold text-indigo-900"><?php echo $total; ?> Penimbangan</p>
        </div>
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Unit</p>
            <p class="text-lg font-bold text-indigo-900"><?php echo $posNama; ?></p>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <a href="index.php?module=laporan&page=export_pdf&bulan=<?php echo urlencode($bulan); ?>&download=1" 
           class="flex items-center justify-center gap-3 bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-xl font-bold transition-all shadow-lg shadow-red-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Preview & Download PDF
        </a>
        <p class="text-center text-sm text-slate-400">File akan terbuka di tab baru. Gunakan fitur print (Ctrl+P) untuk menyimpan sebagai PDF.</p>
    </div>

    <div class="mt-12 border-t border-slate-100 pt-8">
        <h4 class="text-sm font-bold text-slate-900 mb-4">Preview Data:</h4>
        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-slate-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600">Balita</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-600">BB</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-600">TB</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-600">Umur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if ($total === 0): ?>
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400 italic">Data tidak ditemukan untuk periode ini.</td></tr>
                    <?php endif; ?>
                    <?php foreach (array_slice($records, 0, 10) as $row): ?>
                        <tr>
                            <td class="px-4 py-3 text-slate-500"><?php echo sanitize($row['tgl_timbang']); ?></td>
                            <td class="px-4 py-3 font-bold text-indigo-950"><?php echo sanitize($row['nama']); ?></td>
                            <td class="px-4 py-3 text-center font-bold text-blue-600"><?php echo $row['bb']; ?></td>
                            <td class="px-4 py-3 text-center font-bold text-pink-600"><?php echo $row['tb']; ?></td>
                            <td class="px-4 py-3 text-center text-slate-600"><?php echo $row['umur']; ?> bln</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($total > 10): ?>
                <div class="p-3 bg-slate-50 text-center text-xs text-slate-400 font-medium border-t border-slate-100">
                    Menampilkan 10 dari <?php echo $total; ?> data
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>