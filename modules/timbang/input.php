<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/notifikasi.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        error_log('CSRF Token gagal');
        echo "<script>
        Swal.fire({icon:'error',title:'Gagal',text:'Token keamanan tidak valid.'});
        </script>";
        echo "<div class='card p-8'><p class='text-red-500'>Token keamanan tidak valid.</p></div>";
        exit;
    }
    
    $balita_id = intval($_POST['balita_id'] ?? 0);
    $bbInput   = trim($_POST['bb'] ?? '');
    $tbInput   = trim($_POST['tb'] ?? '');
    $lkInput   = trim($_POST['lk'] ?? '');
    $lilaInput = trim($_POST['lila'] ?? '');
    $tgl_timbang = trim($_POST['tgl_timbang'] ?? date('Y-m-d'));
    
    // Server-side validation
    $bb  = floatval(str_replace(',', '.', $bbInput));
    $tb  = floatval(str_replace(',', '.', $tbInput));

    $errors = [];
    if ($balita_id <= 0)   $errors[] = 'Silakan pilih balita terlebih dahulu.';
    if ($bb <= 0 || $bb > 50)  $errors[] = 'Berat badan harus antara 0.1 - 50 kg.';
    if ($tb <= 0 || $tb > 200) $errors[] = 'Tinggi badan harus antara 0.1 - 200 cm.';

    if (!empty($errors)) {
        error_log('Input validation error: ' . implode(', ', $errors));
        echo "<script>
        Swal.fire({icon:'error',title:'Data Tidak Valid',text:'" . addslashes($errors[0]) . "'});
        </script>";
        echo "<div class='card p-8'><p class='text-red-500'>" . htmlspecialchars($errors[0]) . "</p></div>";
        exit;
    }
    
    $lk   = floatval(str_replace(',', '.', $lkInput));
    $lila = floatval(str_replace(',', '.', $lilaInput));
    
    if ($balita_id > 0 && $bb > 0 && $tb > 0) {
        $success = db()->insert('timbang', [
            'balita_id'   => $balita_id,
            'bb'          => $bb,
            'tb'          => $tb,
            'lk'          => $lk,
            'lila'        => $lila,
            'tgl_timbang' => $tgl_timbang
        ]);
        
        if (!$success) {
            error_log('DB insert failed for balita_id=' . $balita_id);
            echo "<script>
            Swal.fire({icon:'error',title:'Gagal',text:'Gagal menyimpan data penimbangan. Silakan coba lagi.'});
            </script>";
            echo "<div class='card p-8'><p class='text-red-500'>Gagal menyimpan data.</p></div>";
            exit;
        }
        
        $balita = fetch_one("SELECT tgl_lahir FROM balita WHERE id = ?", [$balita_id]);
        $whoColor     = 'Biru';
        $whoStatus    = 'Normal';
        $whoRekomendasi = 'Pertumbuhan dalam batas normal, lanjutkan pola asuh yang baik';
        
        if ($balita && $balita['tgl_lahir']) {
            $birthDate = new DateTime($balita['tgl_lahir']);
            $now       = new DateTime($tgl_timbang);
            $ageMonths = (int)$birthDate->diff($now)->format('%y') * 12 + (int)$birthDate->diff($now)->format('%m');
            
            require_once __DIR__ . '/../api/who_status.php';
            $whoResult = getWHOStatus($bb, $tb, $lk, $lila, $ageMonths, $balita['jenis_kelamin'] ?? 'L');
            $whoColor     = $whoResult['color'];
            $whoStatus    = $whoResult['status'];
            $whoRekomendasi = $whoResult['rekomendasi'];
        }
        
        error_log('WHO result: color=' . $whoColor . ' status=' . $whoStatus);
        
        // Kirim WA untuk semua penimbangan
        waTimbangSelesai($balita_id, $bb, $tb, $tgl_timbang);
        
        // Kirim WA tambahan jika status gizi abnormal
        if ($whoColor !== 'Biru') {
            waWHOAbnormal($balita_id, $whoStatus, $whoRekomendasi, $bb, $tb);
        }
        
        flash('message', 'Data penimbangan berhasil disimpan.');
        header('Location: index.php?module=timbang&page=riwayat&balita_id=' . $balita_id);
        exit;
    }
    
    echo "<script>
    Swal.fire({icon:'error',title:'Error',text:'Data tidak valid'});
    </script>";
    echo "<div class='card p-8'><p class='text-red-500'>Data tidak valid.</p></div>";
    exit;
}

if (isUserView()) {
    $balitas = getMotherBalitas();
    if (empty($balitas)) {
        echo "<div class='card p-12 text-center bg-white/80 backdrop-blur-md rounded-2xl'><p class='text-indigo-300 font-bold'>Data balita tidak ditemukan.</p></div>";
        return;
    }
} else {
    $balitas = fetch_all("SELECT id, nama, nama_ibu, tgl_lahir FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
}
?>

<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden animate-fade-in">
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-400 flex items-center justify-center shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-indigo-950">Input Penimbangan</h3>
                <p class="text-sm text-indigo-500/70 font-medium">Catat perkembangan fisik balita secara akurat</p>
            </div>
        </div>

        <form id="timbangForm" method="post" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="md:col-span-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-pink-500 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                            Identitas Balita
                        </h4>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-indigo-900 ml-1 mb-2">Pilih Balita</label>
                        <select name="balita_id" id="balitaSelect" required class="w-full"></select>
                    </div>

                    <div id="balitaInfo" class="hidden animate-fade-in">
                        <div class="p-5 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl border border-indigo-100 flex items-center gap-4">
                            <div id="balitaAvatar" class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-indigo-500 shadow-sm font-bold text-lg"></div>
                            <div>
                                <h3 id="balitaName" class="font-bold text-indigo-950"></h3>
                                <p id="balitaDetails" class="text-xs text-indigo-400"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-indigo-900 ml-1 mb-2">Tanggal Penimbangan</label>
                        <input type="date" name="tgl_timbang" id="tglTimbang" value="<?php echo date('Y-m-d'); ?>" required 
                               class="block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-3 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="md:col-span-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-blue-500 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            Pengukuran Fisik
                        </h4>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-indigo-900 ml-1">Berat Badan (kg)</span>
                            <input type="number" name="bb" id="bb" step="0.1" min="0.1" max="50" required placeholder="0.0"
                                   class="mt-2 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-3 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold text-lg">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-indigo-900 ml-1">Tinggi Badan (cm)</span>
                            <input type="number" name="tb" id="tb" step="0.1" min="0.1" max="200" required placeholder="0.0"
                                   class="mt-2 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-3 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold text-lg">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-indigo-900 ml-1">Lingkar Kepala (cm)</span>
                            <input type="number" name="lk" id="lk" step="0.1" min="0" placeholder="0.0"
                                   class="mt-2 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-3 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-indigo-900 ml-1">Lingkar Lengan (cm)</span>
                            <input type="number" name="lila" id="lila" step="0.1" min="0" placeholder="0.0"
                                   class="mt-2 block w-full rounded-xl border-indigo-100 bg-indigo-50/30 px-4 py-3 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        </label>
                    </div>

                    <div id="whoResult" class="hidden animate-fade-in">
                        <div class="p-6 rounded-2xl border-2 border-dashed border-indigo-100 bg-white/50">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-black text-indigo-900 uppercase tracking-widest">Prediksi Status Gizi (WHO)</span>
                                <span id="statusBadge" class="px-3 py-1 rounded-lg text-xs font-black uppercase"></span>
                            </div>
                            <h5 id="statusText" class="text-xl font-black text-indigo-950 mb-2"></h5>
                            <p id="rekomendasiText" class="text-xs text-indigo-400 leading-relaxed font-medium"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4 pt-6">
                <button type="submit" id="submitBtn" 
                        class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-500 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest flex items-center justify-center gap-3">
                    <span id="submitText">Simpan Data Penimbangan</span>
                    <div id="loadingSpinner" class="hidden w-5 h-5 border-3 border-white/30 border-t-white rounded-full animate-spin"></div>
                </button>
                <button type="button" onclick="window.history.back()" 
                        class="bg-white border-2 border-indigo-100 text-indigo-400 font-bold py-4 px-8 rounded-2xl hover:bg-indigo-50 transition-all uppercase tracking-widest text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedBalitaId = 0;
    
    $('#balitaSelect').select2({
        placeholder: 'Ketik nama balita atau NIK...',
        ajax: {
            url: 'modules/api/get_balita.php',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                if (!Array.isArray(data)) {
                    console.warn('get_balita.php tidak mengembalikan array:', data);
                    return { results: [] };
                }
                return {
                    results: data.map(function(item) {
                        return {
                            id:    item.id,
                            text:  (item.nama || '') + ' (' + (item.nama_ibu || '-') + ')',
                            data:  item
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });
    
    $('#balitaSelect').on('select2:select', function (e) {
        const data = e.params.data.data;
        selectedBalitaId = data.id;
        $('#balitaName').text(data.nama || '');
        $('#balitaAvatar').text((data.nama || '').charAt(0).toUpperCase());
        $('#balitaDetails').text('NIK: ' + (data.nik || '-') + ' | Lahir: ' + new Date(data.tgl_lahir).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}));
        $('#balitaInfo').removeClass('hidden');
        checkWHOStatus();
    });

    $('#balitaSelect').on('select2:unselecting', function () {
        selectedBalitaId = 0;
        $('#balitaInfo').addClass('hidden');
        $('#whoResult').addClass('hidden');
        $('#bb').val('');
        $('#tb').val('');
        $('#lk').val('');
        $('#lila').val('');
    });
    
    function checkWHOStatus() {
        const bb   = parseFloat($('#bb').val());
        const tb   = parseFloat($('#tb').val());
        const lk   = parseFloat($('#lk').val()) || 0;
        const lila = parseFloat($('#lila').val()) || 0;
        
        if (selectedBalitaId > 0 && bb > 0 && tb > 0 && $('#bb').val() && $('#tb').val()) {
            $.get('modules/api/who_status.php', {
                balita_id: selectedBalitaId,
                bb: bb,
                tb: tb,
                lk: lk,
                lila: lila
            }, function(data) {
                if (!data || data.error) {
                    $('#whoResult').addClass('hidden');
                    return;
                }
                $('#whoResult').removeClass('hidden');
                $('#statusText').text(data.status || '');
                $('#rekomendasiText').text(data.rekomendasi || '');
                
                const badge = $('#statusBadge');
                if (data.color === 'Merah') {
                     badge.removeClass('bg-yellow-100 text-yellow-600 bg-blue-100 text-blue-600').addClass('bg-red-100 text-red-600').text('Risiko Tinggi');
                     $('#statusText').addClass('text-red-600').removeClass('text-indigo-950');
                 } else if (data.color === 'Kuning') {
                     badge.removeClass('bg-red-100 text-red-600 bg-blue-100 text-blue-600').addClass('bg-yellow-100 text-yellow-600').text('Waspada');
                     $('#statusText').removeClass('text-red-600 text-indigo-950').addClass('text-yellow-600');
                 } else {
                     badge.removeClass('bg-red-100 text-red-600 bg-yellow-100 text-yellow-600').addClass('bg-blue-100 text-blue-600').text('Normal');
                     $('#statusText').removeClass('text-red-600 text-yellow-600').addClass('text-indigo-950');
                 }
            }).fail(function(xhr, status, error) {
                console.warn('WHO status request failed:', status, error);
                $('#whoResult').addClass('hidden');
            });
        } else {
            $('#whoResult').addClass('hidden');
        }
    }
    
    $('#bb, #tb, #lk, #lila').on('input', function() {
        checkWHOStatus();
    });
    
    document.getElementById('timbangForm').addEventListener('submit', function(e) {
        const bb = parseFloat(document.querySelector('[name="bb"]').value);
        const tb = parseFloat(document.querySelector('[name="tb"]').value);
        
        if (bb <= 0 || bb > 50) {
            e.preventDefault();
            Swal.fire({icon:'error',title:'Data Tidak Valid',text:'Berat badan harus antara 0.1 - 50 kg'});
            document.querySelector('[name="bb"]').focus();
            return;
        }
        
        if (tb <= 0 || tb > 200) {
            e.preventDefault();
            Swal.fire({icon:'error',title:'Data Tidak Valid',text:'Tinggi badan harus antara 0.1 - 200 cm'});
            document.querySelector('[name="tb"]').focus();
            return;
        }

        if (selectedBalitaId === 0 || !$('#balitaSelect').val()) {
            e.preventDefault();
            Swal.fire({icon:'warning',title:'Belum Memilih Balita',text:'Silakan pilih balita terlebih dahulu sebelum menyimpan.'});
            $('#balitaSelect').select2('open');
            return;
        }

        document.getElementById('submitText').classList.add('opacity-0');
        document.getElementById('loadingSpinner').classList.remove('hidden');
    });
});
</script>
