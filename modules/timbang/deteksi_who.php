<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$balitas = fetch_all("SELECT id, nama, nik, tgl_lahir, nama_ibu FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
?>

<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden animate-fade-in">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-400 flex items-center justify-center shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-indigo-950">Deteksi Status Gizi WHO</h3>
                <p class="text-sm text-indigo-500/70 font-medium">Analisis otomatis pertumbuhan balita berdasarkan standar WHO</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div class="p-6 bg-white/40 rounded-3xl border border-indigo-50 backdrop-blur-sm">
                    <h4 class="text-xs font-black text-indigo-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Input Parameter
                    </h4>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-indigo-900 ml-1 mb-2 uppercase">Pilih Balita</label>
                            <select id="balitaSelect" class="block w-full rounded-xl border-indigo-100 bg-white px-4 py-3 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                                <option value="">-- Pilih Balita --</option>
                                <?php foreach ($balitas as $b): ?>
                                <option value="<?php echo $b['id']; ?>" data-tgl="<?php echo $b['tgl_lahir']; ?>">
                                    <?php echo sanitize($b['nama']) . ' (Ibu: ' . sanitize($b['nama_ibu']) . ')'; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-indigo-900 ml-1 mb-2 uppercase">Tanggal Penimbangan</label>
                            <input type="date" id="tglTimbang" value="<?php echo date('Y-m-d'); ?>" 
                                   class="block w-full rounded-xl border-indigo-100 bg-white px-4 py-3 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-indigo-900 ml-1 mb-2 uppercase">BB (kg)</label>
                                <input type="number" id="bb" step="0.1" min="0" placeholder="0.0"
                                       class="block w-full rounded-xl border-indigo-100 bg-white px-4 py-3 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-indigo-900 ml-1 mb-2 uppercase">TB (cm)</label>
                                <input type="number" id="tb" step="0.1" min="0" placeholder="0.0"
                                       class="block w-full rounded-xl border-indigo-100 bg-white px-4 py-3 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-indigo-900 ml-1 mb-2 uppercase">LK (cm)</label>
                                <input type="number" id="lk" step="0.1" min="0" placeholder="Opsional"
                                       class="block w-full rounded-xl border-indigo-100 bg-white px-4 py-3 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-indigo-900 ml-1 mb-2 uppercase">LILA (cm)</label>
                                <input type="number" id="lila" step="0.1" min="0" placeholder="Opsional"
                                       class="block w-full rounded-xl border-indigo-100 bg-white px-4 py-3 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="p-6 bg-gradient-to-br from-indigo-900 to-indigo-800 rounded-3xl text-white shadow-xl shadow-indigo-100 min-h-[400px] flex flex-col relative overflow-hidden">
                     <div class="absolute -top-12 -right-12 w-32 h-32 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
                     <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-pink-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <h4 class="text-xs font-black text-white/50 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-pink-400"></span>
                        Hasil Analisis Otomatis
                    </h4>
                    
                    <div id="resultContainer" class="flex-1 flex flex-col">
                        <div id="noResult" class="flex-1 flex flex-col items-center justify-center text-center p-8 opacity-40">
                            <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <p class="font-bold">Lengkapi data di sebelah kiri untuk melihat hasil deteksi WHO.</p>
                        </div>
                        
                        <div id="loadingResult" class="hidden flex-1 flex flex-col items-center justify-center text-center p-8">
                             <div class="w-12 h-12 border-4 border-pink-400 border-t-transparent rounded-full animate-spin mb-4"></div>
                            <p class="font-bold text-pink-400 uppercase tracking-widest">Menganalisis Data...</p>
                        </div>
                        
                        <div id="hasilData" class="hidden space-y-6 animate-fade-in">
                            <div id="statusCard" class="p-6 rounded-2xl bg-white/10 border border-white/10 backdrop-blur-md">
                                <div class="flex items-center justify-between mb-3">
                                    <span id="statusLabel" class="text-2xl font-black"></span>
                                    <span id="statusBadge" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest"></span>
                                </div>
                                <p id="rekomendasiLabel" class="text-sm text-indigo-100/80 leading-relaxed font-medium italic"></p>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <div class="p-4 bg-white/5 rounded-2xl border border-white/5 text-center">
                                    <p class="text-[10px] font-black text-white/40 uppercase tracking-wider mb-1">BB/U Z-Score</p>
                                    <p id="zBB" class="text-xl font-black text-white">-</p>
                                </div>
                                <div class="p-4 bg-white/5 rounded-2xl border border-white/5 text-center">
                                    <p class="text-[10px] font-black text-white/40 uppercase tracking-wider mb-1">TB/U Z-Score</p>
                                    <p id="zTB" class="text-xl font-black text-white">-</p>
                                </div>
                                <div class="p-4 bg-white/5 rounded-2xl border border-white/5 text-center">
                                    <p class="text-[10px] font-black text-white/40 uppercase tracking-wider mb-1">BB/TB Z-Score</p>
                                    <p id="zBBTB" class="text-xl font-black text-white">-</p>
                                </div>
                                <div class="p-4 bg-white/5 rounded-2xl border border-white/5 text-center">
                                    <p class="text-[10px] font-black text-white/40 uppercase tracking-wider mb-1">LK/U Z-Score</p>
                                    <p id="zLK" class="text-xl font-black text-white">-</p>
                                </div>
                                <div class="p-4 bg-white/5 rounded-2xl border border-white/5 text-center col-span-2">
                                    <p class="text-[10px] font-black text-white/40 uppercase tracking-wider mb-1">LILA (cm)</p>
                                    <p id="zLILA" class="text-xl font-black text-white">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const balitaSelect = document.getElementById('balitaSelect');
    const tglTimbang = document.getElementById('tglTimbang');
    const bb = document.getElementById('bb');
    const tb = document.getElementById('tb');
    const lk = document.getElementById('lk');
    const lila = document.getElementById('lila');
    
    const noResult = document.getElementById('noResult');
    const loadingResult = document.getElementById('loadingResult');
    const hasilData = document.getElementById('hasilData');
    const statusCard = document.getElementById('statusCard');
    
    function hitung() {
        const balitaId = balitaSelect.value;
        const tgl = tglTimbang.value;
        const berat = parseFloat(bb.value) || 0;
        const tinggi = parseFloat(tb.value) || 0;
        const lingkar = parseFloat(lk.value) || 0;
        const lilang = parseFloat(lila.value) || 0;
        
        if (!balitaId || !tgl || berat <= 0 || tinggi <= 0) {
            noResult.classList.remove('hidden');
            loadingResult.classList.add('hidden');
            hasilData.classList.add('hidden');
            return;
        }
        
        noResult.classList.add('hidden');
        loadingResult.classList.remove('hidden');
        hasilData.classList.add('hidden');
        
        fetch('modules/api/who_status.php?balita_id=' + balitaId + '&bb=' + berat + '&tb=' + tinggi + '&lk=' + lingkar + '&lila=' + lilang)
            .then(r => r.json())
            .then(data => {
                loadingResult.classList.add('hidden');
                
                if (data.error) {
                    noResult.classList.remove('hidden');
                    return;
                }
                
                hasilData.classList.remove('hidden');
                document.getElementById('statusLabel').textContent = data.status;
                document.getElementById('rekomendasiLabel').textContent = data.rekomendasi;
                
                const badge = document.getElementById('statusBadge');
                if (data.color === 'Merah') {
                    badge.className = 'px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-red-500 text-white';
                    badge.textContent = 'Risiko Tinggi';
                } else if (data.color === 'Kuning') {
                    badge.className = 'px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-yellow-400 text-indigo-900';
                    badge.textContent = 'Waspada';
                } else {
                    badge.className = 'px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-emerald-500 text-white';
                    badge.textContent = 'Normal';
                }
                
                document.getElementById('zBB').textContent = data.z_scores.bb_u;
                document.getElementById('zTB').textContent = data.z_scores.tb_u;
                document.getElementById('zBBTB').textContent = data.z_scores.bb_tb !== null ? data.z_scores.bb_tb : '-';
                document.getElementById('zLK').textContent = data.z_scores.lk_u !== null ? data.z_scores.lk_u : '-';
                document.getElementById('zLILA').textContent = data.z_scores.lila_u !== null ? data.z_scores.lila_u : '-';
            })
            .catch(err => {
                loadingResult.classList.add('hidden');
                noResult.classList.remove('hidden');
            });
    }
    
    balitaSelect.addEventListener('change', hitung);
    tglTimbang.addEventListener('change', hitung);
    bb.addEventListener('input', hitung);
    tb.addEventListener('input', hitung);
    lk.addEventListener('input', hitung);
    lila.addEventListener('input', hitung);
});
</script>