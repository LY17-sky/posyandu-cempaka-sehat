<?php
$isUserView = isUserView();
$message = flash('message');

if ($isUserView) {
    $balitas = getMotherBalitas();
    ?>
    <div class="space-y-8 animate-fade-in">
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Jadwal Imunisasi Anak
            </h3>
            <p class="text-sm text-indigo-500/70 font-medium">Pantau jadwal dan status imunisasi setiap anak Anda</p>
        </div>

        <?php if (empty($balitas)): ?>
            <div class="card p-12 text-center bg-white/50 backdrop-blur-md rounded-2xl border-dashed border-2 border-indigo-100">
                <p class="text-indigo-400 font-bold">Belum ada data balita terdaftar.</p>
            </div>
        <?php else: ?>
            <?php foreach ($balitas as $balita): ?>
                <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-3xl relative overflow-hidden mb-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-pink-500 flex items-center justify-center text-white text-2xl font-black shadow-lg">
                            <?php echo strtoupper(substr($balita['nama'], 0, 1)); ?>
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-indigo-950"><?php echo sanitize($balita['nama']); ?></h4>
                            <p class="text-sm font-bold text-indigo-400">Tgl Lahir: <?php echo date('d M Y', strtotime($balita['tgl_lahir'])); ?></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-indigo-50 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead class="bg-indigo-50/80 text-indigo-900 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-6 py-4 text-center">Usia Target</th>
                                    <th class="px-6 py-4 text-left">Jenis Vaksin</th>
                                    <th class="px-6 py-4 text-left">Keterangan</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-indigo-50 bg-white">
                                <?php 
                                $vaksinMaster = getVaksinMaster();
                                foreach ($vaksinMaster as $v): 
                                    $status = getVaksinStatus($balita['id'], $balita['tgl_lahir'], $v['jenis'], $v['usia_bulan']);
                                    
                                    $statusClass = '';
                                    $statusText = '';
                                    if ($status === 'sudah') {
                                        $statusClass = 'bg-emerald-100 text-emerald-600';
                                        $statusText = 'Selesai';
                                    } elseif ($status === 'segera') {
                                        $statusClass = 'bg-amber-100 text-amber-600';
                                        $statusText = 'Segera';
                                    } elseif ($status === 'terlambat') {
                                        $statusClass = 'bg-rose-100 text-rose-600';
                                        $statusText = 'Terlambat';
                                    } else {
                                        $statusClass = 'bg-slate-100 text-slate-500';
                                        $statusText = 'Belum Waktunya';
                                    }
                                ?>
                                <tr class="hover:bg-indigo-50/30 transition-all">
                                    <td class="px-6 py-4 text-center font-bold text-indigo-400"><?php echo $v['usia_bulan']; ?> Bln</td>
                                    <td class="px-6 py-4 font-black text-indigo-950"><?php echo sanitize($v['jenis']); ?></td>
                                    <td class="px-6 py-4 text-indigo-500 text-xs font-medium"><?php echo sanitize($v['keterangan']); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
} else {
    // Admin View
    $jadwal = fetch_all("SELECT i.id, i.balita_id, b.nama AS balita, i.jenis_imunisasi, i.tgl_imunisasi, i.status FROM imunisasi i JOIN balita b ON i.balita_id = b.id WHERE 1=1 " . getBalitaFilter('b.id_pos') . " ORDER BY i.tgl_imunisasi DESC");
    ?>
    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <!-- Decorative Glow -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Jadwal & Riwayat Imunisasi
                </h3>
                <p class="text-sm text-indigo-500/70 font-medium">Data pemantauan imunisasi lengkap balita</p>
            </div>

            <?php if ($message): ?>
                <div class="mb-6 rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        <span class="font-medium"><?php echo $message; ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
                <table class="min-w-full divide-y divide-indigo-50 text-sm">
                    <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Nama Balita</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Jenis Imunisasi</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50">
                        <?php if (count($jadwal) === 0): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-20 text-center text-indigo-300 font-medium">Belum ada data imunisasi.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($jadwal as $item): ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                            <?php echo strtoupper(substr($item['balita'], 0, 1)); ?>
                                        </div>
                                        <a href="?module=balita&page=detail&id=<?php echo $item['balita_id']; ?>" class="text-indigo-950 font-bold group-hover:text-indigo-600 transition-colors cursor-pointer"><?php echo sanitize($item['balita']); ?></a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-indigo-900 font-medium"><?php echo sanitize($item['jenis_imunisasi']); ?></td>
                                <td class="px-6 py-4 text-indigo-500 font-medium">
                                    <?php echo date('d M Y', strtotime($item['tgl_imunisasi'])); ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($item['status'] === 'sudah'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-600">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                            Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}
?>
