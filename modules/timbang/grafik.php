<?php
if ($isUserView) {
    $balitas = getMotherBalitas();
    $id = intval($_GET['balita_id'] ?? ($balitas[0]['id'] ?? 0));
    $balita = null;
    foreach ($balitas as $b) {
        if ($b['id'] == $id) { $balita = $b; break; }
    }
    if (!$balita) $balita = $balitas[0] ?? null;
    
    if (!$balita) {
        echo "<div class='card p-12 text-center bg-white/80 backdrop-blur-md rounded-2xl'><p class='text-indigo-300 font-bold'>Data balita tidak ditemukan</p></div>";
        return;
    }
} else {
    $balitas = db()->select("SELECT id, nama, nama_ibu FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
}
?>

<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden animate-fade-in">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-400 to-pink-400 flex items-center justify-center shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-indigo-950">Grafik Pertumbuhan</h3>
                <p class="text-sm text-indigo-500/70 font-medium">Visualisasi perkembangan kesehatan anak secara berkala</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar Controls -->
            <div class="lg:col-span-1 space-y-8">
                <?php if (!$isUserView || count($balitas) > 1): ?>
                <div class="p-6 bg-white/40 rounded-2xl border border-indigo-50">
                    <label class="block text-xs font-black text-indigo-900 uppercase tracking-widest mb-3 ml-1">Pilih Balita</label>
                    <select id="balitaSelect" class="block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-sm text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        <option value="">Pilih balita...</option>
                        <?php foreach ($balitas as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $b['id'] == $balita['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="p-6 bg-white/40 rounded-2xl border border-indigo-50">
                    <label class="block text-xs font-black text-indigo-900 uppercase tracking-widest mb-4 ml-1">Parameter Grafik</label>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/50 transition-all cursor-pointer group">
                            <input type="checkbox" id="bbCheck" checked class="w-5 h-5 rounded border-indigo-200 text-emerald-500 focus:ring-emerald-200">
                            <span class="text-sm font-bold text-indigo-900 group-hover:text-emerald-600">Berat Badan</span>
                        </label>
                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/50 transition-all cursor-pointer group">
                            <input type="checkbox" id="tbCheck" checked class="w-5 h-5 rounded border-indigo-200 text-blue-500 focus:ring-blue-200">
                            <span class="text-sm font-bold text-indigo-900 group-hover:text-blue-600">Tinggi Badan</span>
                        </label>
                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/50 transition-all cursor-pointer group">
                            <input type="checkbox" id="lkCheck" class="w-5 h-5 rounded border-indigo-200 text-amber-500 focus:ring-amber-200">
                            <span class="text-sm font-bold text-indigo-900 group-hover:text-amber-600">Lingkar Kepala</span>
                        </label>
                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/50 transition-all cursor-pointer group">
                            <input type="checkbox" id="lilaCheck" class="w-5 h-5 rounded border-indigo-200 text-pink-500 focus:ring-pink-200">
                            <span class="text-sm font-bold text-indigo-900 group-hover:text-pink-600">Lingkar Lengan</span>
                        </label>
                    </div>
                </div>

                <div class="p-6 bg-white/40 rounded-2xl border border-indigo-50">
                    <label class="block text-xs font-black text-indigo-900 uppercase tracking-widest mb-4 ml-1">Periode Waktu</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button id="btn3m" class="px-3 py-2 text-xs font-black rounded-xl border-2 border-indigo-50 bg-white text-indigo-400 hover:border-indigo-100 transition-all">3 BLN</button>
                        <button id="btn6m" class="px-3 py-2 text-xs font-black rounded-xl border-2 border-indigo-50 bg-white text-indigo-400 hover:border-indigo-100 transition-all">6 BLN</button>
                        <button id="btn1y" class="px-3 py-2 text-xs font-black rounded-xl border-2 border-indigo-50 bg-white text-indigo-400 hover:border-indigo-100 transition-all">1 THN</button>
                        <button id="btnAll" class="px-3 py-2 text-xs font-black rounded-xl border-2 border-indigo-500 bg-indigo-500 text-white shadow-lg shadow-indigo-100 transition-all">SEMUA</button>
                    </div>
                </div>
            </div>

            <!-- Chart Display -->
            <div class="lg:col-span-3">
                <div class="p-6 bg-white/50 rounded-3xl border border-indigo-50 shadow-inner min-h-[500px] flex flex-col relative">
                    <div id="loadingChart" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm rounded-3xl hidden">
                        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-xs font-black text-indigo-950 uppercase tracking-widest">Memproses Grafik...</p>
                    </div>
                    
                    <div class="flex-1 relative">
                        <canvas id="growthChart"></canvas>
                    </div>
                    
                    <?php if (!$isUserView): ?>
                    <div id="noDataMsg" class="absolute inset-0 flex flex-col items-center justify-center text-center p-12 opacity-30">
                        <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        <p class="font-black text-indigo-950">Silakan pilih balita untuk melihat visualisasi data.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('growthChart').getContext('2d');
    let chart = null;
    let currentBalitaId = <?php echo $isUserView ? $balita['id'] : 0; ?>;
    let currentPeriod = 'all';
    
    const colors = {
        bb: { border: '#10B981', background: 'rgba(16, 185, 129, 0.05)' },
        tb: { border: '#3B82F6', background: 'rgba(59, 130, 246, 0.05)' },
        lk: { border: '#F59E0B', background: 'rgba(245, 158, 11, 0.05)' },
        lila: { border: '#EC4899', background: 'rgba(236, 72, 153, 0.05)' }
    };
    
    function loadChart() {
        if (!currentBalitaId) return;
        
        document.getElementById('loadingChart').classList.remove('hidden');
        if (document.getElementById('noDataMsg')) document.getElementById('noDataMsg').classList.add('hidden');
        
        fetch(`modules/api/get_grafik.php?balita_id=${currentBalitaId}&period=${currentPeriod}`)
            .then(response => response.json())
            .then(data => {
                if (chart) chart.destroy();
                
                const datasets = [];
                
                if (document.getElementById('bbCheck').checked && data.bb.length > 0) {
                    datasets.push({
                        label: 'Berat Badan (kg)',
                        data: data.bb,
                        borderColor: colors.bb.border,
                        backgroundColor: colors.bb.background,
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: colors.bb.border
                    });
                }
                
                if (document.getElementById('tbCheck').checked && data.tb.length > 0) {
                    datasets.push({
                        label: 'Tinggi Badan (cm)',
                        data: data.tb,
                        borderColor: colors.tb.border,
                        backgroundColor: colors.tb.background,
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: colors.tb.border
                    });
                }
                
                if (document.getElementById('lkCheck').checked && data.lk.length > 0) {
                    datasets.push({
                        label: 'Lingkar Kepala (cm)',
                        data: data.lk,
                        borderColor: colors.lk.border,
                        backgroundColor: colors.lk.background,
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff'
                    });
                }
                
                if (document.getElementById('lilaCheck').checked && data.lila.length > 0) {
                    datasets.push({
                        label: 'LILA (cm)',
                        data: data.lila,
                        borderColor: colors.lila.border,
                        backgroundColor: colors.lila.background,
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff'
                    });
                }
                
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { size: 11, weight: 'bold', family: "'Inter', sans-serif" }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#1e1b4b',
                                bodyColor: '#1e1b4b',
                                bodyFont: { weight: 'bold' },
                                borderColor: '#e0e7ff',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: true,
                                callbacks: {
                                    afterLabel: function(context) {
                                        const index = context.dataIndex;
                                        const status = data.status[index];
                                        if (status) {
                                            return 'Status: ' + status.status;
                                        }
                                        return '';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10, weight: 'bold' }, color: '#94a3b8' }
                            },
                            y: {
                                grid: { color: 'rgba(226, 232, 240, 0.5)' },
                                ticks: { font: { size: 10, weight: 'bold' }, color: '#94a3b8' }
                            }
                        }
                    }
                });
                
                document.getElementById('loadingChart').classList.add('hidden');
            })
            .catch(error => {
                document.getElementById('loadingChart').classList.add('hidden');
            });
    }
    
    <?php if (!$isUserView || count($balitas) > 1): ?>
    document.getElementById('balitaSelect').addEventListener('change', function() {
        currentBalitaId = this.value;
        loadChart();
    });
    <?php endif; ?>
    
    ['bbCheck', 'tbCheck', 'lkCheck', 'lilaCheck'].forEach(id => {
        document.getElementById(id).addEventListener('change', loadChart);
    });
    
    ['btn3m', 'btn6m', 'btn1y', 'btnAll'].forEach(id => {
        document.getElementById(id).addEventListener('click', function() {
            ['btn3m', 'btn6m', 'btn1y', 'btnAll'].forEach(btnId => {
                 document.getElementById(btnId).className = "px-3 py-2 text-xs font-black rounded-xl border-2 border-indigo-50 bg-white text-indigo-400 hover:border-indigo-100 transition-all";
            });
            this.className = "px-3 py-2 text-xs font-black rounded-xl border-2 border-indigo-500 bg-indigo-500 text-white shadow-lg shadow-indigo-100 transition-all";
            
            currentPeriod = id.replace('btn', '').toLowerCase();
            if (currentPeriod === '1y') currentPeriod = '1year';
            loadChart();
        });
    });
    
    if (currentBalitaId) loadChart();
});
</script>
