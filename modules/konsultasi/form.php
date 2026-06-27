<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$balitas = fetch_all("SELECT id, nama FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Token CSRF tidak valid.');
        redirect('index.php?module=konsultasi&page=form');
    }
    $balita_id = intval($_POST['balita_id'] ?? 0);
    $nama_pengirim = trim($_POST['nama_pengirim'] ?? '');
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');
    if ($nama_pengirim !== '' && $pertanyaan !== '') {
        db()->insert('konsultasi', [
            'balita_id' => $balita_id ?: null,
            'nama_pengirim' => $nama_pengirim,
            'pertanyaan' => $pertanyaan
        ]);
        flash('message', 'Konsultasi berhasil dikirim.');
        redirect('index.php?module=konsultasi&page=form');
    }
}
$message = flash('message');
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden animate-fade-in">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-purple-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-indigo-400 flex items-center justify-center shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-indigo-950">Konsultasi Bidan</h3>
                <p class="text-sm text-indigo-500/70 font-medium">Tanyakan seputar kesehatan dan pertumbuhan anak</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium"><?php echo sanitize($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-indigo-900 ml-1 uppercase tracking-widest">Balita (Opsional)</label>
                    <select name="balita_id" class="block w-full rounded-2xl border-indigo-100 bg-indigo-50/30 px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold">
                        <option value="0">Tanpa balita (Umum)</option>
                        <?php foreach ($balitas as $balita): ?>
                            <option value="<?php echo $balita['id']; ?>"><?php echo sanitize($balita['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-indigo-900 ml-1 uppercase tracking-widest">Nama Pengirim</label>
                    <input type="text" name="nama_pengirim" required placeholder="Masukkan nama Anda..."
                           value="<?php echo sanitize($user['username'] ?? ''); ?>"
                           class="block w-full rounded-2xl border-indigo-100 bg-indigo-50/30 px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-black text-indigo-900 ml-1 uppercase tracking-widest">Pertanyaan / Keluhan</label>
                <textarea name="pertanyaan" rows="6" required placeholder="Tuliskan pertanyaan atau keluhan Anda secara detail di sini..."
                          class="block w-full rounded-2xl border-indigo-100 bg-indigo-50/30 px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2 font-bold"></textarea>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-purple-100 hover:scale-[1.01] active:scale-95 transition-all uppercase tracking-widest flex items-center justify-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Kirim Konsultasi Sekarang
                </button>
            </div>
        </form>

        <div class="mt-8 p-6 bg-indigo-50/50 rounded-2xl border border-indigo-50">
            <div class="flex items-start gap-4">
                <div class="p-2 bg-white rounded-lg text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-1">Informasi</p>
                    <p class="text-xs text-indigo-400 leading-relaxed font-medium">Bidan akan menjawab pertanyaan Anda maksimal dalam 1x24 jam. Anda dapat memantau jawaban melalui menu <span class="font-bold text-indigo-600">Riwayat Konsultasi</span>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
