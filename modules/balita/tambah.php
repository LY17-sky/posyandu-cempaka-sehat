<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Token CSRF tidak valid.');
        redirect('index.php?module=balita&page=tambah');
    }
    $nama = escape($_POST['nama'] ?? '');
    $nik = escape($_POST['nik'] ?? '');
    $tanggal_lahir = escape($_POST['tanggal_lahir'] ?? '');
    $jenis = escape($_POST['jenis_kelamin'] ?? 'L');
    $bb_lahir = floatval($_POST['bb_lahir'] ?? 0);
    $tb_lahir = floatval($_POST['tb_lahir'] ?? 0);
    $alamat = escape($_POST['alamat'] ?? '');
    $ibu = escape($_POST['ibu'] ?? '');
    $nik_ibu = escape($_POST['nik_ibu'] ?? '');
    $ayah = escape($_POST['ayah'] ?? '');
    $no_telp = escape($_POST['no_telp'] ?? '');
    $id_pos = intval($_POST['id_pos'] ?? $_SESSION['pos_aktif'] ?? 1);
    if (isAdminPos() && $id_pos !== getUserPosId()) {
        $id_pos = getUserPosId();
    }

    db()->insert('balita', [
        'nama' => $nama,
        'nik' => $nik,
        'tgl_lahir' => $tanggal_lahir,
        'jenis_kelamin' => $jenis,
        'bb_lahir' => $bb_lahir > 0 ? $bb_lahir : null,
        'tb_lahir' => $tb_lahir > 0 ? $tb_lahir : null,
        'alamat' => $alamat,
        'nama_ibu' => $ibu,
        'nik_ibu' => $nik_ibu,
        'nama_ayah' => $ayah,
        'no_telp' => $no_telp,
        'id_pos' => $id_pos
    ]);
    flash('message', 'Balita berhasil ditambahkan.');
    redirect('index.php?module=balita&page=daftar');
}
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-200/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-400 to-pink-400 flex items-center justify-center shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-indigo-950">Tambah Data Balita</h3>
                <p class="text-sm text-indigo-500/70 font-medium">Lengkapi formulir pendaftaran balita baru</p>
            </div>
        </div>

        <form method="post" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Data Balita -->
                <div class="md:col-span-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-pink-500 mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                        Informasi Dasar Balita
                    </h4>
                </div>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Nama Lengkap</span>
                    <input type="text" name="nama" required placeholder="Nama lengkap balita" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">NIK Balita</span>
                    <input type="text" name="nik" placeholder="16 digit NIK" maxlength="16"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Tanggal Lahir</span>
                    <input type="date" name="tanggal_lahir" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Jenis Kelamin</span>
                    <select name="jenis_kelamin" 
                            class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Berat Badan Lahir (kg)</span>
                    <input type="number" step="0.01" name="bb_lahir" placeholder="Contoh: 3.2" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Tinggi Badan Lahir (cm)</span>
                    <input type="number" step="0.1" name="tb_lahir" placeholder="Contoh: 50" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Alamat Lengkap</span>
                    <textarea name="alamat" rows="2" placeholder="Alamat domisili saat ini"
                              class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2"></textarea>
                </label>

                <!-- Data Orang Tua -->
                <div class="md:col-span-2 mt-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-500 mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        Informasi Orang Tua
                    </h4>
                </div>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Nama Ibu</span>
                    <input type="text" name="ibu" placeholder="Nama lengkap ibu" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">NIK Ibu</span>
                    <input type="text" name="nik_ibu" placeholder="16 digit NIK Ibu" maxlength="16"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Nama Ayah</span>
                    <input type="text" name="ayah" placeholder="Nama lengkap ayah" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Nomor WhatsApp</span>
                    <input type="text" name="no_telp" placeholder="08xxxxxxxxxxx" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-indigo-900 ml-1">Posyandu</span>
                    <select name="id_pos" 
                            class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        <?php 
                        $posList = [1 => 'Cempaka I', 2 => 'Cempaka II', 3 => 'Cempaka III', 4 => 'Cempaka IV', 5 => 'Cempaka V'];
                        foreach($posList as $id => $nama): 
                        ?>
                        <option value="<?php echo $id; ?>" <?php echo ($_SESSION['pos_aktif'] ?? 1) == $id ? 'selected' : ''; ?>><?php echo $nama; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-200 hover:shadow-indigo-300 hover:scale-[1.02] active:scale-95 transition-all">
                    Simpan Data Balita
                </button>
                <a href="index.php?module=balita&page=daftar" 
                   class="bg-white border-2 border-indigo-100 text-indigo-900 font-bold py-3 px-6 rounded-xl hover:bg-indigo-50 transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
