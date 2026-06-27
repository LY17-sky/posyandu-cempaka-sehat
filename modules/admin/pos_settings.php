<?php
if (!isAdmin()) {
    echo "<div class='card p-12 text-center'><p class='text-indigo-300 font-bold'>Anda tidak memiliki akses ke halaman ini.</p></div>";
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect('index.php?module=admin&page=pos_settings');
    }
    if (isset($_POST['tambah_pos'])) {
        $nama = trim($_POST['nama'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $kontak = trim($_POST['kontak'] ?? '');
        
        if (!empty($nama) && !empty($lokasi)) {
            db()->insert('pos_cempaka', [
                'nama' => $nama,
                'lokasi' => $lokasi,
                'kontak' => $kontak
            ]);
            flash('message', 'Pos Cempaka baru berhasil ditambahkan.');
            redirect('index.php?module=admin&page=pos_settings');
        } else {
            flash('error', 'Nama dan lokasi harus diisi.');
        }
    }
    
    if (isset($_POST['edit_pos'])) {
        $id = intval($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $kontak = trim($_POST['kontak'] ?? '');
        
        if ($id > 0 && !empty($nama) && !empty($lokasi)) {
            db()->update('pos_cempaka', [
                'nama' => $nama,
                'lokasi' => $lokasi,
                'kontak' => $kontak
            ], 'id = ?', [$id]);
            flash('message', 'Pos Cempaka berhasil diperbarui.');
            redirect('index.php?module=admin&page=pos_settings');
        }
    }
}

$posList = db()->select('SELECT * FROM pos_cempaka ORDER BY nama ASC');
$message = flash('message');
$error = flash('error');

$editData = null;
$editId = intval($_GET['edit'] ?? 0);
if ($editId > 0) {
    $editData = db()->selectOne('SELECT * FROM pos_cempaka WHERE id = ?', [$editId]);
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-indigo-950">Kelola Pos Cempaka</h1>
            <p class="text-sm text-indigo-400 font-medium mt-2">Tambah dan kelola pos cempaka sehat</p>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
    <div class="rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span class="font-medium"><?php echo $message; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="rounded-xl border-l-4 border-red-400 bg-red-50 p-4 text-red-800 shadow-sm">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            <span class="font-medium"><?php echo $error; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6">
                <?php echo $editData ? 'Edit Pos Cempaka' : 'Tambah Pos Cempaka Baru'; ?>
            </h2>

            <form method="post" class="grid gap-6 md:grid-cols-3">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="edit_pos" value="1">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="tambah_pos" value="1">
                <?php endif; ?>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Nama Pos</span>
                    <input type="text" name="nama" required placeholder="Contoh: Pos Wilayah A"
                           value="<?php echo $editData ? sanitize($editData['nama']) : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Lokasi</span>
                    <input type="text" name="lokasi" required placeholder="Contoh: Jalan Merdeka No. 10"
                           value="<?php echo $editData ? sanitize($editData['lokasi']) : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">No. Kontak</span>
                    <input type="text" name="kontak" placeholder="Contoh: 08xx-xxxx-xxxx"
                           value="<?php echo $editData ? sanitize($editData['kontak']) : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <div class="md:col-span-3 flex gap-3">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-100 hover:scale-[1.01] active:scale-95 transition-all">
                        <?php echo $editData ? 'Perbarui Pos' : 'Simpan Pos Baru'; ?>
                    </button>
                    <?php if ($editData): ?>
                    <a href="index.php?module=admin&page=pos_settings" class="flex items-center justify-center px-6 py-3 rounded-xl border-2 border-indigo-100 text-indigo-600 font-bold hover:bg-indigo-50 transition-all">
                        Batal
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Pos -->
    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6">Daftar Pos Cempaka</h2>

            <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
                <table class="min-w-full divide-y divide-indigo-50 text-sm">
                    <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">No.</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Nama Pos</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50">
                        <?php if (count($posList) === 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-indigo-300 font-medium">Belum ada pos yang ditambahkan.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($posList as $idx => $pos): ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 font-bold text-indigo-600"><?php echo $idx + 1; ?></td>
                                <td class="px-6 py-4 font-black text-indigo-950"><?php echo sanitize($pos['nama']); ?></td>
                                <td class="px-6 py-4 text-indigo-600 font-medium"><?php echo sanitize($pos['lokasi']); ?></td>
                                <td class="px-6 py-4 text-indigo-500"><?php echo sanitize($pos['kontak'] ?: '-'); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <a href="index.php?module=admin&page=pos_settings&edit=<?php echo $pos['id']; ?>"
                                       class="inline-flex items-center gap-1 bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-blue-600 hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
