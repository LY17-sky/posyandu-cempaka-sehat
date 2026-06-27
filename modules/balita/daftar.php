<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Token CSRF tidak valid.');
        redirect('index.php?module=balita&page=daftar');
    }
    $id = intval($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $nik = trim($_POST['nik'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $jenis = trim($_POST['jenis_kelamin'] ?? 'L');
    $alamat = trim($_POST['alamat'] ?? '');
    $ibu = trim($_POST['ibu'] ?? '');
    $nik_ibu = trim($_POST['nik_ibu'] ?? '');
    $ayah = trim($_POST['ayah'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');
    $id_pos = intval($_POST['id_pos'] ?? 1);

    if ($id > 0) {
        db()->update('balita', [
            'nama' => $nama,
            'nik' => $nik,
            'tgl_lahir' => $tanggal_lahir,
            'jenis_kelamin' => $jenis,
            'alamat' => $alamat,
            'nama_ibu' => $ibu,
            'nik_ibu' => $nik_ibu,
            'nama_ayah' => $ayah,
            'no_telp' => $no_telp,
            'id_pos' => $id_pos
        ], 'id = ?', [$id]);
        flash('message', 'Data balita ' . $nama . ' berhasil diperbarui.');
    }
    redirect('index.php?module=balita&page=daftar');
}

$balitas = fetch_all("SELECT * FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
$message = flash('message');
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Daftar Balita
                </h3>
                <p class="text-sm text-indigo-500/70 font-medium">Manajemen data kesehatan balita aktif</p>
            </div>
            <?php if (!isset($no_add_btn) && (isAdmin() || isAdminPos())): ?>
            <a href="index.php?module=balita&page=tambah" 
               class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-500 to-pink-500 px-5 py-2.5 text-white font-bold shadow-lg shadow-indigo-200 hover:shadow-indigo-300 hover:scale-[1.02] active:scale-95 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Balita
            </a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 rounded-xl border-l-4 border-indigo-400 bg-indigo-50 p-4 text-indigo-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium"><?php echo sanitize($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="mb-6 relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-indigo-300 group-focus-within:text-indigo-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="searchBalita" placeholder="Cari berdasarkan nama, NIK, atau orang tua..." 
                   class="w-full pl-11 pr-4 py-3 border-2 border-indigo-50 bg-indigo-50/20 rounded-xl focus:outline-none focus:border-pink-300 focus:bg-white focus:ring-4 focus:ring-pink-100 transition-all outline-none text-indigo-900 placeholder-indigo-300">
        </div>

        <div class="overflow-x-auto rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">NIK</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Tgl Lahir</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Usia</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Jenis Kelamin</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Orang Tua (Ibu/Ayah)</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-indigo-50">
                    <?php if (count($balitas) === 0): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-indigo-300 font-medium">Belum ada balita terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($balitas as $balita): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        <?php echo strtoupper(substr($balita['nama'], 0, 1)); ?>
                                    </div>
                                    <a href="?module=balita&page=detail&id=<?php echo $balita['id']; ?>" class="text-indigo-950 font-bold group-hover:text-indigo-600 transition-colors cursor-pointer"><?php echo sanitize($balita['nama']); ?></a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-indigo-500 font-mono"><?php echo sanitize($balita['nik'] ?: '-'); ?></td>
                            <td class="px-6 py-4 text-indigo-500"><?php echo date('d M Y', strtotime($balita['tgl_lahir'])); ?></td>
                            <td class="px-6 py-4">
                                <?php 
                                $birthDate = new DateTime($balita['tgl_lahir']);
                                $now = new DateTime();
                                $diff = $birthDate->diff($now);
                                $months = ($diff->y * 12) + $diff->m;
                                ?>
                                <span class="font-black text-indigo-950"><?php echo $months; ?></span>
                                <span class="text-[10px] font-bold text-indigo-300 uppercase">Bulan</span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($balita['jenis_kelamin'] === 'P'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-600">Perempuan</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-600">Laki-laki</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-indigo-900 font-medium"><?php echo sanitize($balita['nama_ibu']); ?></div>
                                <div class="text-xs text-indigo-300"><?php echo sanitize($balita['nama_ayah'] ?: '-'); ?></div>
                            </td>
                             <td class="px-6 py-4 text-center whitespace-nowrap">
                                <?php if (isAdmin() || isAdminPos()): ?>
                                <div class="flex items-center justify-center gap-3">
                                    <button onclick='editBalita(<?php echo json_encode($balita); ?>)' 
                                       class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button onclick="deleteBalita(<?php echo $balita['id']; ?>, '<?php echo addslashes($balita['nama']); ?>')" 
                                            class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                <?php endif; ?>
                             </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-indigo-950/20 backdrop-blur-sm p-4 animate-fade-in">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white p-8 text-left align-middle shadow-2xl transition-all">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-black text-indigo-950">Edit Data Balita</h3>
                <button onclick="closeModal()" class="text-indigo-300 hover:text-pink-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="post" id="editForm" class="space-y-5">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">Nama Lengkap</span>
                        <input type="text" name="nama" id="edit_nama" required class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                    </label>
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">NIK Balita</span>
                        <input type="text" name="nik" id="edit_nik" maxlength="16" class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                    </label>
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">Tanggal Lahir</span>
                        <input type="date" name="tanggal_lahir" id="edit_tgl_lahir" required class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                    </label>
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">Jenis Kelamin</span>
                        <select name="jenis_kelamin" id="edit_jenis" class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">Nama Ibu</span>
                        <input type="text" name="ibu" id="edit_ibu" required class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                    </label>
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">NIK Ibu</span>
                        <input type="text" name="nik_ibu" id="edit_nik_ibu" maxlength="16" class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                    </label>
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">Nama Ayah</span>
                        <input type="text" name="ayah" id="edit_ayah" class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                    </label>
                    <label class="block">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">No. WhatsApp</span>
                        <input type="text" name="no_telp" id="edit_telp" class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">Posyandu</span>
                        <select name="id_pos" id="edit_pos" class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2">
                            <?php 
                            $posList = [1 => 'Cempaka I', 2 => 'Cempaka II', 3 => 'Cempaka III', 4 => 'Cempaka IV', 5 => 'Cempaka V'];
                            foreach($posList as $id_p => $nama): 
                            ?>
                            <option value="<?php echo $id_p; ?>"><?php echo $nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-xs font-black text-indigo-900 ml-1 uppercase">Alamat Lengkap</span>
                        <textarea name="alamat" id="edit_alamat" rows="2" class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-indigo-950 font-bold focus:border-pink-400 focus:ring-pink-400/20 transition-all border-2"></textarea>
                    </label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-black py-4 rounded-2xl shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="closeModal()" class="px-8 bg-indigo-50 text-indigo-400 font-bold rounded-2xl hover:bg-indigo-100 transition-all uppercase">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBalita(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_nik').value = data.nik || '';
    document.getElementById('edit_tgl_lahir').value = data.tgl_lahir;
    document.getElementById('edit_jenis').value = data.jenis_kelamin;
    document.getElementById('edit_ibu').value = data.nama_ibu;
    document.getElementById('edit_nik_ibu').value = data.nik_ibu || '';
    document.getElementById('edit_ayah').value = data.nama_ayah || '';
    document.getElementById('edit_telp').value = data.no_telp || '';
    document.getElementById('edit_pos').value = data.id_pos || 1;
    document.getElementById('edit_alamat').value = data.alamat || '';
    
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBalita');
    const tableBody = document.getElementById('tableBody');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(keyword)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (visibleCount === 0 && keyword !== '') {
                const emptyMsg = document.getElementById('emptySearchRow');
                if (emptyMsg) emptyMsg.remove();
                
                const tr = document.createElement('tr');
                tr.id = 'emptySearchRow';
                tr.innerHTML = '<td colspan="7" class="px-6 py-10 text-center text-indigo-300 font-medium">Tidak ada data yang cocok dengan pencarian Anda.</td>';
                tableBody.appendChild(tr);
            } else {
                const emptyMsg = document.getElementById('emptySearchRow');
                if (emptyMsg) emptyMsg.remove();
            }
        });
    }
});

function deleteBalita(id, nama) {
    Swal.fire({
        title: 'Hapus Balita?',
        html: 'Anda akan menghapus data <b>' + nama + '</b> secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        background: '#ffffff',
        borderRadius: '1.5rem',
        customClass: {
            confirmButton: 'font-bold rounded-xl px-6 py-3',
            cancelButton: 'font-bold rounded-xl px-6 py-3'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?module=balita&page=hapus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id + '&csrf_token=<?php echo generateCSRFToken(); ?>'
            }).then(() => {
                location.reload();
            });
        }
    });
}
</script>
