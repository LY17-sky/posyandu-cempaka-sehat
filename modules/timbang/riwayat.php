<?php
if ($isUserView) {
    $balitas = getMotherBalitas();
    $balitaId = intval($_GET['balita_id'] ?? ($balitas[0]['id'] ?? 0));
    $balita = null;
    foreach ($balitas as $b) {
        if ($b['id'] == $balitaId) { $balita = $b; break; }
    }
    if (!$balita) $balita = $balitas[0] ?? null;
    
    if (!$balita || !checkBalitaAccess($balitaId)) {
        echo "<div class='card p-12 text-center bg-white/80 backdrop-blur-md rounded-2xl'><p class='text-indigo-300 font-bold'>Data balita tidak ditemukan atau Anda tidak memiliki akses.</p></div>";
        return;
    }
    $balitaId = $balita['id'];
} else {
    $balitaId = intval($_GET['balita_id'] ?? 0);
    if ($balitaId === 0) {
        $firstBalita = db()->selectOne("SELECT id FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama LIMIT 1");
        if ($firstBalita) {
            $balitaId = $firstBalita['id'];
        }
    }
    $balita = db()->selectOne("SELECT * FROM balita WHERE id = ?" . getPosFilter(), [$balitaId]);
    if (!$balita) {
        echo "<div class='card p-12 text-center bg-white/80 backdrop-blur-md rounded-2xl'><p class='text-indigo-300 font-bold'>Balita tidak ditemukan</p></div>";
        return;
    }
}

$records = db()->select("SELECT * FROM timbang WHERE balita_id = ? ORDER BY tgl_timbang DESC", [$balitaId]);

$birthDate = new DateTime($balita['tgl_lahir']);
$balitaGender = $balita['jenis_kelamin'] ?? 'L';
function getStatusGizi($bb, $tb, $lk, $lila, $tglTimbang) {
    global $birthDate, $balitaGender;
    $timbangDate = new DateTime($tglTimbang);
    $diff = $birthDate->diff($timbangDate);
    $ageMonths = ($diff->y * 12) + $diff->m;
    $result = getStatusGiziByAge($bb, $tb, $lk, $lila, $ageMonths, $balitaGender);
    return [$result['status'], $result['color'], $result['rekomendasi']];
}
?>

<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden animate-fade-in">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
<div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between mb-8">
                <div>
                    <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                        <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Riwayat Penimbangan
                    </h3>
                    <p class="text-sm text-indigo-500/70 font-medium">
                        Balita: <span class="text-indigo-900 font-bold" id="balitaNama"><?php echo htmlspecialchars($balita['nama']); ?></span>
                        <span class="mx-2 text-indigo-200">|</span>
                        Ibu: <span class="text-indigo-900 font-bold" id="balitaIbu"><?php echo htmlspecialchars($balita['nama_ibu']); ?></span>
                    </p>
                </div>
                
                <?php if (!$isUserView): ?>
                <div class="sm:flex sm:items-center">
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="module" value="timbang">
                        <input type="hidden" name="page" value="riwayat">
                        <select name="balita_id" onchange="this.form.submit()" class="rounded-xl border-indigo-100 bg-white px-4 py-2 text-sm font-bold text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                            <option value="">Pilih balita...</option>
                            <?php 
                            $allBalitas = db()->select("SELECT id, nama, nama_ibu FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
                            foreach ($allBalitas as $b): ?>
                            <option value="<?php echo $b['id']; ?>" <?php echo $b['id'] == $balitaId ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if ($isUserView && count($balitas) > 1): ?>
                <div class="sm:flex sm:items-center">
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="module" value="timbang">
                        <input type="hidden" name="page" value="riwayat">
                        <select name="balita_id" onchange="this.form.submit()" class="rounded-xl border-indigo-100 bg-white px-4 py-2 text-sm font-bold text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                            <?php foreach ($balitas as $b): ?>
                            <option value="<?php echo $b['id']; ?>" <?php echo $b['id'] == $balitaId ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <?php endif; ?>

            <?php if ($isAdmin || isAdminPos()): ?>
            <a href="index.php?module=timbang&page=input&balita_id=<?php echo $balitaId; ?>" 
               class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-500 to-pink-500 px-5 py-2.5 text-white font-bold shadow-lg shadow-indigo-200 hover:shadow-indigo-300 hover:scale-[1.02] active:scale-95 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Penimbangan
            </a>
            <?php endif; ?>
        </div>

        <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">BB (kg)</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">TB (cm)</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">LK (cm)</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">LILA (cm)</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Status Gizi</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Rekomendasi</th>
                        <?php if ($isAdmin || isAdminPos()): ?>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="<?php echo ($isAdmin || isAdminPos()) ? 8 : 7; ?>" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-2 opacity-30">
                                <svg class="w-16 h-16 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span class="text-indigo-900 font-bold">Belum ada data penimbangan</span>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($records as $record): ?>
                    <?php list($status, $color, $rekomendasi) = getStatusGizi($record['bb'], $record['tb'], $record['lk'], $record['lila'], $record['tgl_timbang']); ?>
                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap text-indigo-500 font-medium">
                            <?php echo date('d M Y', strtotime($record['tgl_timbang'])); ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 text-blue-600 font-bold"><?php echo number_format($record['bb'], 1); ?></span>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-indigo-900">
                            <?php echo number_format($record['tb'], 1); ?>
                        </td>
                        <td class="px-6 py-4 text-center text-indigo-500">
                            <?php echo $record['lk'] ? number_format($record['lk'], 1) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 text-center text-indigo-500">
                            <?php echo $record['lila'] ? number_format($record['lila'], 1) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($color === 'Biru'): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-600">Normal</span>
                            <?php elseif ($color === 'Kuning'): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-600"><?php echo $status; ?></span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600"><?php echo $status; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-xs text-indigo-900 font-medium max-w-xs">
                            <?php echo htmlspecialchars($rekomendasi); ?>
                        </td>
                        <?php if ($isAdmin || isAdminPos()): ?>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-3">
                                <button onclick="editRecord(<?php echo $record['id']; ?>)" 
                                        class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button onclick="deleteRecord(<?php echo $record['id']; ?>)" 
                                        class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editRecord(id) {
    fetch('modules/api/get_timbang.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }
            
            Swal.fire({
                title: 'Edit Data Penimbangan',
                html: `
                    <input type="hidden" id="edit-id" value="${id}">
                    <div class="mb-3">
                        <label class="block text-sm font-bold text-left mb-1">Tanggal</label>
                        <input type="date" id="edit-tgl" value="${data.tgl_timbang}" class="swal2-input" style="width: 100%;">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-bold text-left mb-1">Berat Badan (kg)</label>
                        <input type="number" id="edit-bb" value="${data.bb}" step="0.1" min="0.1" max="50" class="swal2-input" style="width: 100%;">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-bold text-left mb-1">Tinggi Badan (cm)</label>
                        <input type="number" id="edit-tb" value="${data.tb}" step="0.1" min="0.1" max="200" class="swal2-input" style="width: 100%;">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-bold text-left mb-1">Lingkar Kepala (cm)</label>
                        <input type="number" id="edit-lk" value="${data.lk || ''}" step="0.1" min="0" class="swal2-input" style="width: 100%;">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-bold text-left mb-1">Lingkar Lengan (cm)</label>
                        <input type="number" id="edit-lila" value="${data.lila || ''}" step="0.1" min="0" class="swal2-input" style="width: 100%;">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                focusConfirm: false,
                preConfirm: () => {
                    const bb = parseFloat(document.getElementById('edit-bb').value);
                    const tb = parseFloat(document.getElementById('edit-tb').value);
                    
                    if (!bb || bb <= 0 || bb > 50) {
                        Swal.showValidationMessage('Berat badan harus antara 0.1 - 50 kg');
                        return false;
                    }
                    if (!tb || tb <= 0 || tb > 200) {
                        Swal.showValidationMessage('Tinggi badan harus antara 0.1 - 200 cm');
                        return false;
                    }
                    
                    return {
                        id: id,
                        tgl_timbang: document.getElementById('edit-tgl').value,
                        bb: bb,
                        tb: tb,
                        lk: parseFloat(document.getElementById('edit-lk').value) || 0,
                        lila: parseFloat(document.getElementById('edit-lila').value) || 0,
                        csrf_token: '<?php echo generateCSRFToken(); ?>'
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('modules/api/edit_timbang.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(result.value)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    });
                }
            });
        });
}

function deleteRecord(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: 'Data penimbangan akan dihapus permanen',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('modules/api/delete_timbang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id, csrf_token: '<?php echo generateCSRFToken(); ?>' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success')
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}
</script>
