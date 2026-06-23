<?php
if ($isAdmin || $isAdminPos) {
    $currentMonth = date('Y-m');
    $posFilter = getBalitaFilter('b.id_pos');
    $allTimbang = db()->select("
        SELECT t.bb, t.tb, t.lk, t.lila, t.tgl_timbang, b.tgl_lahir, b.jenis_kelamin, b.id, b.nama, b.nama_ibu
        FROM timbang t
        JOIN balita b ON t.balita_id = b.id
        WHERE strftime('%Y-%m', t.tgl_timbang) = ? $posFilter
        ORDER BY t.tgl_timbang DESC
    ", [$currentMonth]);
    
    $statusData = ['Normal' => 0, 'Kurang' => 0, 'Berlebih' => 0];
    $burukBalita = [];
    foreach ($allTimbang as $row) {
        $ageMonths = getBalitaAgeInMonths($row['tgl_lahir']);
        $result = getStatusGiziByAge($row['bb'], $row['tb'], $row['lk'] ?? 0, $row['lila'] ?? 0, $ageMonths, $row['jenis_kelamin'] ?? 'L');
        $category = 'Normal';
        if ($result['color'] === 'Merah' || $result['color'] === 'Kuning') {
            $category = 'Kurang';
            if (count($burukBalita) < 5) {
                $burukBalita[] = ['id' => $row['id'], 'nama' => $row['nama'], 'nama_ibu' => $row['nama_ibu'], 'bb' => $row['bb'], 'tb' => $row['tb'], 'tgl_timbang' => $row['tgl_timbang'], 'status' => $result['status']];
            }
        } elseif (str_contains($result['status'], 'Overweight')) {
            $category = 'Berlebih';
        }
        $statusData[$category] = ($statusData[$category] ?? 0) + 1;
    }
    
    $totalBalita = db()->selectOne("SELECT COUNT(*) as count FROM balita WHERE is_active = 1" . getPosFilter())['count'] ?? 0;
    $totalTimbang = db()->selectOne("SELECT COUNT(*) as count FROM timbang t JOIN balita b ON t.balita_id = b.id WHERE 1=1" . getBalitaFilter('b.id_pos'))['count'] ?? 0;
    ?>
     <!-- Welcome Banner -->
     <div class="mb-8 bg-gradient-to-r from-blue-400 via-pink-300 to-blue-500 rounded-2xl p-8 text-white shadow-lg">
         <div class="flex items-center justify-between">
             <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Selamat datang, <?php echo $isAdmin ? 'Super Admin' : 'Admin Pos ' . $posNama; ?>!</h1>
                <p class="text-blue-100">
                    <?php 
                    if ($isAdmin) {
                        echo $pos_aktif > 0 ? "Pantau data kesehatan balita di $posNama" : "Pantau data kesehatan balita seluruh wilayah";
                    } else {
                        echo "Kelola data kesehatan balita di $posNama";
                    }
                    ?>
                </p>
            </div>
            <svg class="w-20 h-20 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20 13H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM20 3H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zm-3 6h-6c-.55 0-1-.45-1-1s.45-1 1-1h6c.55 0 1 .45 1 1s-.45 1-1 1z"/>
            </svg>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Balita Card -->
        <div class="card group">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Balita</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $totalBalita; ?></p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500">Balita aktif terdaftar</p>
            </div>
        </div>

        <!-- Total Penimbangan Card -->
        <div class="card group">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Penimbangan</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $totalTimbang; ?></p>
                    </div>
                     <div class="p-3 bg-blue-100 rounded-lg group-hover:scale-110 transition">
                         <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500">Jumlah penimbangan</p>
            </div>
        </div>

        <!-- Status Kurang Card -->
        <div class="card group">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Status Kurang Gizi</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $statusData['Kurang']; ?></p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-lg group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 9v2m0 4v2m0 0H9m3 0h3m0 0V9m0 8v2m0 0H9m-6-8c0 5.523 4.477 10 10 10s10-4.477 10-10S17.523 2 12 2 2 6.477 2 12z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500">Membutuhkan perhatian</p>
            </div>
        </div>

        <!-- Status Normal Card -->
        <div class="card group">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Status Normal</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $statusData['Normal']; ?></p>
                    </div>
                    <div class="p-3 bg-emerald-100 rounded-lg group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500">Status gizi baik</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Status Chart -->
        <div class="lg:col-span-1 card">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Gizi Bulan Ini</h3>
                <div class="relative h-64">
                    <canvas id="statusChart" width="200" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Balita Kondisi Buruk -->
        <div class="lg:col-span-2 card p-6 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
            <!-- Decorative Glow -->
            <div class="absolute -top-12 -right-12 w-24 h-24 bg-red-100/30 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <h3 class="text-lg font-bold text-indigo-950 mb-6 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0H9m3 0h3m0 0V9m0 8v2m0 0H9"></path></svg>
                    </div>
                    Balita Membutuhkan Perhatian
                </h3>
                <div class="grid gap-4">
                    <?php if (empty($burukBalita)): ?>
                        <div class="text-center py-12 text-indigo-300">
                            <svg class="w-16 h-16 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="font-bold">Semua balita sehat walafiat!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($burukBalita as $balita): ?>
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-red-50 to-transparent rounded-xl border border-red-100 hover:shadow-md transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 font-bold">
                                        <?php echo strtoupper(substr($balita['nama'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-indigo-950 group-hover:text-red-600 transition-colors"><?php echo htmlspecialchars($balita['nama']); ?></div>
                                        <div class="text-xs text-indigo-400">Ibu: <?php echo htmlspecialchars($balita['nama_ibu']); ?></div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 bg-white rounded-lg text-xs font-bold text-red-600 border border-red-50">BB: <?php echo $balita['bb']; ?> kg</span>
                                    <span class="px-3 py-1 bg-white rounded-lg text-xs font-bold text-red-600 border border-red-50">TB: <?php echo $balita['tb']; ?> cm</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="index.php?module=laporan&page=bulanan" class="text-center py-2 text-indigo-400 text-xs font-bold hover:text-indigo-600 transition-colors">Lihat Laporan Lengkap →</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="?module=balita&page=tambah" class="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition text-center">
                    <svg class="w-6 h-6 mx-auto mb-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 4a8 8 0 100 16 8 8 0 000-16zM12 2a10 10 0 110 20 10 10 0 010-20zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4v-2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900">Tambah Balita</p>
                </a>
                     <a href="?module=timbang&page=input" class="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition text-center">
                         <svg class="w-6 h-6 mx-auto mb-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 2a8 8 0 100 16 8 8 0 000-16zm1 5h2v2h-2v6h-2V9h2V7z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900">Input Penimbangan</p>
                </a>
                <a href="?module=laporan&page=bulanan" class="p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition text-center">
                    <svg class="w-6 h-6 mx-auto mb-2 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-2h2v16h-2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900">Laporan</p>
                </a>
                <a href="?module=backup&page=auto_backup" class="p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition text-center">
                    <svg class="w-6 h-6 mx-auto mb-2 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-900">Backup</p>
                </a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('statusChart')) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Normal', 'Kurang Gizi', 'Berlebih Gizi'],
                    datasets: [{
                        data: [<?php echo implode(',', array_values($statusData)); ?>],
                         backgroundColor: ['#60A5FA', '#F472B6', '#EF4444'],
                         borderColor: ['#3B82F6', '#EC4899', '#DC2626'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12, weight: 'bold' },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });
        }
    });
    </script>

    <?php
} else {
    $balitas = getMotherBalitas();
    $namaIbu = !empty($balitas) ? $balitas[0]['nama_ibu'] : $user['username'];
    ?>
    <div class="space-y-8 animate-fade-in">
        <!-- Compact Premium Welcome Banner (Soft Pastel) -->
        <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-indigo-50 via-white to-indigo-50/30 p-6 md:p-10 text-indigo-950 shadow-xl border border-white">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-200/20 rounded-full blur-3xl -mr-10 -mt-10"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-pink-200/10 rounded-full blur-3xl -ml-10 -mb-10"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                <div class="w-24 h-24 md:w-28 md:h-28 rounded-full border-4 border-white p-1.5 bg-white shadow-xl shadow-indigo-100/50">
                    <div class="w-full h-full rounded-full bg-gradient-to-br from-indigo-400 to-pink-400 flex items-center justify-center text-3xl font-black text-white shadow-inner">
                        <?php echo strtoupper(substr($namaIbu, 0, 1)); ?>
                    </div>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-2xl md:text-4xl font-black mb-2 tracking-tight">Halo, Bunda <span class="text-indigo-600"><?php echo htmlspecialchars($namaIbu); ?></span>! ✨</h1>
                    <p class="text-indigo-500/70 text-base font-medium max-w-xl leading-relaxed">
                        Pantau tumbuh kembang si kecil dengan mudah. Anda memiliki <span class="text-indigo-600 font-black"><?php echo count($balitas); ?> Balita</span> terdaftar.
                    </p>
                </div>
            </div>
        </div>

        <?php if (count($balitas) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <?php foreach ($balitas as $balita): 
                    $lastTimbang = db()->selectOne("SELECT * FROM timbang WHERE balita_id = ? ORDER BY tgl_timbang DESC LIMIT 1", [$balita['id']]);
                    $birthDate = new DateTime($balita['tgl_lahir']);
                    $now = new DateTime();
                    $diff = $birthDate->diff($now);
                    $usiaBulan = ($diff->y * 12) + $diff->m;
                    
                    // Simple Gizi Status
                    $statusGizi = "Optimal";
                    $statusColor = "emerald";
                    if ($lastTimbang && $lastTimbang['bb'] < 8.5) { $statusGizi = "Perlu Atensi"; $statusColor = "amber"; }
                ?>
                <div class="group relative">
                    <!-- Glow Effect -->
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-pink-500 rounded-[2.5rem] blur-2xl opacity-0 group-hover:opacity-10 transition-all duration-500"></div>
                    
                    <div class="relative card bg-white border border-indigo-50 shadow-xl shadow-indigo-100/30 rounded-[2.5rem] overflow-hidden transition-all duration-500 hover:-translate-y-2">
                        <!-- Card Header -->
                        <div class="h-28 bg-gradient-to-r from-indigo-50/50 to-pink-50/50 relative">
                            <div class="absolute -bottom-8 left-8">
                                <div class="w-20 h-20 rounded-3xl bg-white shadow-xl p-1.5">
                                    <div class="w-full h-full rounded-2xl bg-gradient-to-br from-indigo-500 to-pink-500 flex items-center justify-center text-white text-3xl font-black">
                                        <?php echo strtoupper(substr($balita['nama'], 0, 1)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-6 right-8">
                                <div class="px-4 py-1.5 rounded-full text-[10px] font-black bg-white shadow-sm flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-<?php echo $statusColor; ?>-500 animate-pulse"></span>
                                    <span class="text-indigo-950 uppercase tracking-widest"><?php echo $statusGizi; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 pt-12">
                            <div class="mb-8">
                                <h3 class="text-2xl font-black text-indigo-950 mb-1 group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($balita['nama']); ?></h3>
                                <div class="flex items-center gap-3 text-sm font-bold text-indigo-400">
                                    <span class="flex items-center gap-1">
                                        <?php if ($balita['jenis_kelamin'] === 'P'): ?>
                                            <svg class="w-4 h-4 text-pink-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l.395.072 4 4a1 1 0 010 1.414l-4 4-.395.072V17a1 1 0 11-2 0v-4.586l-.707.707a1 1 0 01-1.414-1.414l4-4V3a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                                            Perempuan
                                        <?php else: ?>
                                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                            Laki-laki
                                        <?php endif; ?>
                                    </span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-100"></span>
                                    <span><?php echo date('d M Y', strtotime($balita['tgl_lahir'])); ?></span>
                                </div>
                            </div>

                            <!-- Growth Stats -->
                            <div class="grid grid-cols-3 gap-4 mb-8">
                                <div class="bg-indigo-50/50 rounded-3xl p-4 text-center border border-indigo-50 group-hover:bg-indigo-50 transition-colors">
                                    <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-1">Usia</p>
                                    <p class="text-xl font-black text-indigo-950"><?php echo $usiaBulan; ?></p>
                                    <p class="text-[10px] font-bold text-indigo-400">Bulan</p>
                                </div>
                                <div class="bg-pink-50/50 rounded-3xl p-4 text-center border border-pink-50 group-hover:bg-pink-50 transition-colors">
                                    <p class="text-[10px] font-black text-pink-300 uppercase tracking-widest mb-1">BB</p>
                                    <p class="text-xl font-black text-pink-600"><?php echo $lastTimbang ? number_format($lastTimbang['bb'], 1) : '-'; ?></p>
                                    <p class="text-[10px] font-bold text-pink-400">Kg</p>
                                </div>
                                <div class="bg-emerald-50/50 rounded-3xl p-4 text-center border border-emerald-50 group-hover:bg-emerald-50 transition-colors">
                                    <p class="text-[10px] font-black text-emerald-300 uppercase tracking-widest mb-1">TB</p>
                                    <p class="text-xl font-black text-emerald-600"><?php echo $lastTimbang ? number_format($lastTimbang['tb'], 1) : '-'; ?></p>
                                    <p class="text-[10px] font-bold text-emerald-400">Cm</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-4">
                                <a href="?module=balita&page=detail&id=<?php echo $balita['id']; ?>" 
                                   class="flex-1 bg-indigo-950 text-white text-center py-4 rounded-2xl font-black text-sm hover:shadow-lg hover:shadow-indigo-200 transition-all active:scale-95">
                                    Detail Profil
                                </a>
                                <a href="?module=timbang&page=grafik&balita_id=<?php echo $balita['id']; ?>" 
                                   class="w-14 h-14 bg-white border-2 border-indigo-50 flex items-center justify-center rounded-2xl text-indigo-600 hover:border-indigo-500 hover:text-indigo-500 transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card p-20 text-center bg-white/50 backdrop-blur-md rounded-[3rem] border-dashed border-2 border-indigo-100 flex flex-col items-center">
                <div class="w-24 h-24 bg-indigo-50 rounded-3xl flex items-center justify-center mb-8 text-indigo-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-indigo-950 mb-3">Wah, Belum Ada Data Nih!</h3>
                <p class="text-indigo-400 font-medium max-w-sm leading-relaxed">
                    Silakan hubungi petugas Posyandu terdekat untuk mendaftarkan data balita Anda menggunakan <span class="font-bold text-indigo-600">NIK Ibu</span> Anda.
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
