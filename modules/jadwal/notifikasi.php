<?php
require_once __DIR__ . '/../../helpers/notifikasi.php';
$balitas = fetch_all("SELECT id, nama, no_telp, nama_ibu FROM balita WHERE is_active = 1" . getPosFilter() . " ORDER BY nama");
$message = flash('message');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Token CSRF tidak valid.');
        redirect('index.php?module=jadwal&page=notifikasi');
    }

    $balita_id = intval($_POST['balita_id'] ?? 0);
    $pesan = trim($_POST['pesan'] ?? '');

    if ($balita_id > 0 && $pesan !== '') {
        $balita = fetch_one("SELECT no_telp, nama, nama_ibu FROM balita WHERE id = ?", [$balita_id]);
        if ($balita && !empty($balita['no_telp'])) {
            $result = sendWA($balita['no_telp'], $pesan);
            if ($result['success']) {
                flash('message', 'Notifikasi WA berhasil dikirim ke ' . sanitize($balita['nama_ibu']));
            } else {
                flash('message', 'Gagal mengirim WA: ' . ($result['error'] ?? 'unknown error'));
            }
        } else {
            flash('message', 'Nomor telepon balita tidak ditemukan.');
        }
    } else {
        flash('message', 'Pilih balita dan tulis pesan terlebih dahulu.');
    }
    redirect('index.php?module=jadwal&page=notifikasi');
}
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-teal-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-emerald-950 flex items-center gap-2">
                <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                Kirim Notifikasi WA
            </h3>
            <p class="text-sm text-emerald-600/70 font-medium">Kirimkan pesan pengingat jadwal langsung ke WhatsApp orang tua.</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium"><?php echo $message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="rounded-3xl border border-emerald-100 bg-white/40 p-6 sm:p-8 backdrop-blur-md">
            <form method="post" class="grid gap-6 md:grid-cols-2">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <label class="block md:col-span-2">
                    <span class="text-sm font-black text-emerald-900 ml-1 uppercase tracking-wider">Kepada (Ibu / Ayah)</span>
                    <select name="balita_id" required class="mt-2 block w-full rounded-2xl border-emerald-100 bg-white px-5 py-4 text-emerald-950 focus:border-teal-400 focus:ring-teal-400/20 transition-all outline-none border-2">
                        <option value="">-- Pilih Penerima --</option>
                        <?php foreach ($balitas as $balita): ?>
                            <option value="<?php echo $balita['id']; ?>"><?php echo sanitize($balita['nama']); ?> — Ibu: <?php echo sanitize($balita['nama_ibu']); ?> (<?php echo sanitize($balita['no_telp'] ?: '-'); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                
                <label class="block md:col-span-2">
                    <span class="text-sm font-black text-emerald-900 ml-1 uppercase tracking-wider">Pesan WhatsApp</span>
                    <textarea name="pesan" rows="4" required placeholder="Tuliskan pesan notifikasi atau pengingat di sini..." class="mt-2 block w-full rounded-2xl border-emerald-100 bg-white px-5 py-4 text-emerald-950 focus:border-teal-400 focus:ring-teal-400/20 transition-all outline-none border-2"></textarea>
                </label>
                
                <div class="md:col-span-2 mt-2">
                    <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-emerald-200 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Kirim Pesan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>