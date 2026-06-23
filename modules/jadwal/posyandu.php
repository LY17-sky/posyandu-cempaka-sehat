<?php
require_once __DIR__ . '/../../helpers/notifikasi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['kirim_reminder'])) {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flash('message', 'Token CSRF tidak valid.');
            redirect('index.php?module=jadwal&page=posyandu');
        }
        if (!isAdmin() && !isAdminPos()) {
            flash('message', 'Anda tidak memiliki akses.');
            redirect('index.php?module=jadwal&page=posyandu');
        }
        cekDanKirimReminder();
        flash('message', 'Reminder WhatsApp berhasil dikirim ke orang tua.');
        redirect('index.php?module=jadwal&page=posyandu');
    }
    
    if (isset($_POST['edit_jadwal'])) {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flash('message', 'Token CSRF tidak valid.');
            redirect('index.php?module=jadwal&page=posyandu');
        }
        if (!isAdmin() && !isAdminPos()) {
            flash('message', 'Anda tidak memiliki akses.');
            redirect('index.php?module=jadwal&page=posyandu');
        }
        $id = intval($_POST['id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $lokasi = trim($_POST['lokasi'] ?? '');
        $waktu = trim($_POST['waktu'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');
        
        if ($id > 0 && $lokasi !== '' && $waktu !== '') {
            db()->update('jadwal_posyandu', [
                'tanggal' => $tanggal,
                'lokasi' => $lokasi,
                'waktu' => $waktu,
                'catatan' => $catatan
            ], 'id = ?', [$id]);
            flash('message', 'Jadwal berhasil diperbarui.');
            redirect('index.php?module=jadwal&page=posyandu');
        }
    }
    
    if (!isset($_POST['kirim_reminder']) && !isset($_POST['edit_jadwal'])) {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flash('message', 'Token CSRF tidak valid.');
            redirect('index.php?module=jadwal&page=posyandu');
        }
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $lokasi = trim($_POST['lokasi'] ?? '');
        $waktu = trim($_POST['waktu'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');
        if ($lokasi !== '' && $waktu !== '') {
            db()->insert('jadwal_posyandu', [
                'tanggal' => $tanggal,
                'lokasi' => $lokasi,
                'waktu' => $waktu,
                'catatan' => $catatan
            ]);
            flash('message', 'Jadwal posyandu baru berhasil ditambahkan.');
            redirect('index.php?module=jadwal&page=posyandu');
        }
    }
}

$jadwal = fetch_all('SELECT * FROM jadwal_posyandu ORDER BY tanggal DESC');
$message = flash('message');

// Get edit data if exists
$editData = null;
$editId = intval($_GET['edit'] ?? 0);
if ($editId > 0 && (isAdmin() || isAdminPos())) {
    $editData = db()->selectOne('SELECT * FROM jadwal_posyandu WHERE id = ?', [$editId]);
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
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Jadwal Posyandu
                </h3>
                <p class="text-sm text-indigo-500/70 font-medium">Kelola jadwal kegiatan dan pengiriman reminder</p>
            </div>
            <?php if (isAdmin() || isAdminPos()): ?>
            <div class="flex gap-3">
                <a href="#" onclick="kirimReminderWA()" 
                   class="inline-flex items-center rounded-xl bg-emerald-50 text-emerald-600 px-5 py-2.5 font-bold border border-emerald-100 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Kirim Reminder WhatsApp
                </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium"><?php echo $message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isAdmin() || isAdminPos()): ?>
        <div class="bg-white/40 p-6 rounded-2xl border border-indigo-50 mb-8 backdrop-blur-sm">
            <h4 class="text-sm font-bold text-indigo-950 mb-4 uppercase tracking-wider">
                <?php echo $editData ? 'Edit Jadwal' : 'Tambah Jadwal Baru'; ?>
            </h4>
            <form method="post" class="grid gap-6 md:grid-cols-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="edit_jadwal" value="1">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>
                
                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Tanggal</span>
                    <input type="date" name="tanggal" value="<?php echo $editData ? $editData['tanggal'] : date('Y-m-d'); ?>" 
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>
                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Lokasi</span>
                    <input type="text" name="lokasi" required placeholder="Contoh: Balai Desa"
                           value="<?php echo $editData ? sanitize($editData['lokasi']) : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>
                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Waktu</span>
                    <input type="text" name="waktu" required placeholder="Contoh: 08:00 atau 08:00-11:00"
                           value="<?php echo $editData ? sanitize($editData['waktu']) : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>
                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Catatan</span>
                    <input type="text" name="catatan" placeholder="Opsional"
                           value="<?php echo $editData ? sanitize($editData['catatan']) : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>
                <div class="md:col-span-4 flex gap-3">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-100 hover:scale-[1.01] active:scale-95 transition-all">
                        <?php echo $editData ? 'Perbarui Jadwal' : 'Simpan Jadwal Kegiatan'; ?>
                    </button>
                    <?php if ($editData): ?>
                    <a href="index.php?module=jadwal&page=posyandu" class="flex items-center justify-center bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl hover:bg-slate-300 transition-all">
                        Batal
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Catatan</th>
                        <?php if (isAdmin() || isAdminPos()): ?>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    <?php if (count($jadwal) === 0): ?>
                        <tr>
                            <td colspan="<?php echo (isAdmin() || isAdminPos()) ? '5' : '4'; ?>" class="px-6 py-20 text-center text-indigo-300 font-medium">Belum ada jadwal posyandu.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($jadwal as $row): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex flex-col items-center justify-center text-indigo-600">
                                        <span class="text-[10px] font-bold uppercase leading-none"><?php echo date('M', strtotime($row['tanggal'])); ?></span>
                                        <span class="text-sm font-black leading-none mt-1"><?php echo date('d', strtotime($row['tanggal'])); ?></span>
                                    </div>
                                    <span class="text-indigo-950 font-bold"><?php echo date('D, d M Y', strtotime($row['tanggal'])); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-indigo-900 font-medium"><?php echo sanitize($row['lokasi']); ?></td>
                            <td class="px-6 py-4">
                                <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg font-bold text-xs"><?php echo sanitize($row['waktu']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-indigo-500 italic"><?php echo sanitize($row['catatan'] ?: '-'); ?></td>
                            <?php if (isAdmin() || isAdminPos()): ?>
                            <td class="px-6 py-4 text-center">
                                <a href="index.php?module=jadwal&page=posyandu&edit=<?php echo $row['id']; ?>" 
                                   class="inline-flex items-center gap-1 bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-blue-600 hover:text-white transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function kirimReminderWA() {
    Swal.fire({
        title: 'Kirim Reminder WhatsApp?',
        text: 'Reminder akan dikirim ke semua ibu balita untuk jadwal besok.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Kirim',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?module=jadwal&page=posyandu', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'kirim_reminder=1&csrf_token=<?php echo generateCSRFToken(); ?>'
            }).then(() => location.reload());
        }
    });
}
</script>