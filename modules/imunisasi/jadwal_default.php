<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $waktu = trim($_POST['waktu'] ?? '08:00');
    if ($nama !== '') {
        db()->insert('jadwal_posyandu', [
            'tanggal' => $tanggal,
            'lokasi' => $nama,
            'waktu' => $waktu,
            'catatan' => $keterangan
        ]);
        flash('message', 'Jadwal default posyandu disimpan.');
        redirect('index.php?module=imunisasi&page=jadwal_default');
    }
}
$jadwal = fetch_all('SELECT * FROM jadwal_posyandu ORDER BY tanggal DESC');
$message = flash('message');
?>
<div class="card p-6">
    <h3 class="text-xl font-semibold mb-4">Kelola Jadwal Default Imunisasi / Posyandu</h3>
    <?php if ($message): ?>
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" class="grid gap-4 md:grid-cols-3 mb-6">
        <label class="block md:col-span-1">
            <span class="text-sm font-medium text-slate-700">Nama Jadwal</span>
            <input type="text" name="nama" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
        </label>
        <label class="block md:col-span-1">
            <span class="text-sm font-medium text-slate-700">Keterangan</span>
            <input type="text" name="keterangan" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
        </label>
        <div class="flex items-end md:col-span-1">
            <button type="submit" class="rounded-lg bg-cyan-600 px-5 py-2 text-white hover:bg-cyan-700">Simpan Jadwal</button>
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                <?php if (count($jadwal) === 0): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-5 text-center text-slate-500">Belum ada jadwal.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($jadwal as $row): ?>
                    <tr>
                        <td class="px-4 py-3"><?php echo sanitize($row['tanggal']); ?></td>
                        <td class="px-4 py-3"><?php echo sanitize($row['lokasi']); ?></td>
                        <td class="px-4 py-3"><?php echo sanitize($row['waktu']); ?></td>
                        <td class="px-4 py-3"><?php echo sanitize($row['catatan']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
