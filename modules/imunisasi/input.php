<?php
$balitas = fetch_all("SELECT id, nama FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Token CSRF tidak valid.');
        redirect('index.php?module=imunisasi&page=jadwal');
    }
    $balita_id = intval($_POST['balita_id'] ?? 0);
    $jenis_imunisasi = escape($_POST['nama_imunisasi'] ?? '');
    $tanggal = escape($_POST['tanggal'] ?? date('Y-m-d'));
    $status = escape($_POST['status'] ?? 'sudah');

    if ($balita_id > 0 && $jenis_imunisasi !== '') {
        db()->insert('imunisasi', [
            'balita_id' => $balita_id,
            'jenis_imunisasi' => $jenis_imunisasi,
            'tgl_imunisasi' => $tanggal,
            'status' => $status
        ]);
        flash('message', 'Data imunisasi berhasil disimpan.');
        redirect('index.php?module=imunisasi&page=jadwal');
    }
}
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden animate-fade-in">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-orange-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-400 to-pink-400 flex items-center justify-center shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-indigo-950">Input Imunisasi</h3>
                <p class="text-sm text-indigo-500/70 font-medium">Catat pemberian vaksin dan imunisasi balita</p>
            </div>
        </div>

        <form method="post" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-indigo-900 ml-1 uppercase tracking-widest">Pilih Balita</label>
                    <select name="balita_id" required class="block w-full rounded-2xl border-indigo-100 bg-indigo-50/30 px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold">
                        <option value="">-- Pilih Balita --</option>
                        <?php foreach ($balitas as $balita): ?>
                            <option value="<?php echo $balita['id']; ?>"><?php echo sanitize($balita['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-indigo-900 ml-1 uppercase tracking-widest">Nama Imunisasi</label>
                    <select name="nama_imunisasi" required class="block w-full rounded-2xl border-indigo-100 bg-indigo-50/30 px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold">
                        <option value="">-- Pilih Imunisasi --</option>
                        <?php foreach (getVaksinMaster() as $v): ?>
                            <option value="<?php echo $v['jenis']; ?>"><?php echo $v['jenis']; ?> (Usia <?php echo $v['usia_bulan']; ?> bln) - <?php echo $v['keterangan']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-indigo-900 ml-1 uppercase tracking-widest">Tanggal Pemberian</label>
                    <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>"
                           class="block w-full rounded-2xl border-indigo-100 bg-indigo-50/30 px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-indigo-900 ml-1 uppercase tracking-widest">Status</label>
                    <select name="status" class="block w-full rounded-2xl border-indigo-100 bg-indigo-50/30 px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold">
                        <option value="sudah">Selesai (Sudah Diberikan)</option>
                        <option value="belum">Terjadwal (Belum Diberikan)</option>
                    </select>
                </div>
            </div>

            <div class="pt-6 flex gap-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-orange-500 to-pink-500 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-orange-100 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Data Imunisasi
                </button>
                <button type="button" onclick="window.history.back()" class="bg-white border-2 border-indigo-100 text-indigo-400 font-bold py-4 px-8 rounded-2xl hover:bg-indigo-50 transition-all uppercase tracking-widest text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
