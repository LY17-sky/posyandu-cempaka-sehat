<?php
require_once __DIR__ . '/../../config/database.php';

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

$bulanLabel = date('F Y', strtotime($bulan . '-01'));

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan_Timbang_".str_replace(' ', '_', $bulanLabel).".xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);

?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        .title { font-size: 16pt; font-weight: bold; color: #4f46e5; }
        .subtitle { font-size: 12pt; color: #64748b; }
        .header { background-color: #f1f5f9; color: #1e293b; font-weight: bold; border: 1px solid #e2e8f0; }
        .data { border: 1px solid #e2e8f0; }
        .number { text-align: right; }
        .text-center { text-align: center; }
        .badge-l { color: #1e40af; }
        .badge-p { color: #9d174d; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="6" class="title">LAPORAN DATA PENIMBANGAN BALITA</td>
        </tr>
        <tr>
            <td colspan="6" class="subtitle"><?php echo $posNama; ?> - Periode <?php echo $bulanLabel; ?></td>
        </tr>
        <tr>
            <td colspan="6" style="color: #94a3b8; font-size: 9pt;">Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></td>
        </tr>
        <tr><td></td></tr>
        <thead>
            <tr>
                <th class="header">No</th>
                <th class="header">Tanggal Timbang</th>
                <th class="header">Nama Balita</th>
                <th class="header">Jenis Kelamin</th>
                <th class="header">Berat Badan (kg)</th>
                <th class="header">Tinggi Badan (cm)</th>
                <th class="header">Umur (bulan)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($records as $row): ?>
            <tr>
                <td class="data text-center"><?php echo $no++; ?></td>
                <td class="data"><?php echo date('d/m/Y', strtotime($row['tgl_timbang'])); ?></td>
                <td class="data" style="font-weight: bold;"><?php echo sanitize($row['nama']); ?></td>
                <td class="data text-center <?php echo $row['jenis_kelamin'] == 'L' ? 'badge-l' : 'badge-p'; ?>">
                    <?php echo $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                </td>
                <td class="data number" style="color: #4f46e5; font-weight: bold;"><?php echo number_format($row['bb'], 1); ?></td>
                <td class="data number" style="color: #ec4899; font-weight: bold;"><?php echo number_format($row['tb'], 1); ?></td>
                <td class="data text-center"><?php echo $row['umur']; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($records) === 0): ?>
            <tr>
                <td colspan="7" class="data text-center" style="padding: 20px; color: #94a3b8;">Tidak ada data pada periode ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <?php if (count($records) > 0): ?>
        <tfoot>
            <tr><td></td></tr>
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: right;">RATA-RATA:</td>
                <td class="number" style="font-weight: bold;"><?php echo number_format(array_sum(array_column($records, 'bb')) / count($records), 1); ?></td>
                <td class="number" style="font-weight: bold;"><?php echo number_format(array_sum(array_column($records, 'tb')) / count($records), 1); ?></td>
                <td></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</body>
</html>
<?php
exit;
