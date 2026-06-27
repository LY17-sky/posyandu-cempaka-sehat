<?php
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$konsultasi = $id ? fetch_one("SELECT * FROM konsultasi WHERE id = ?", [$id]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $konsultasi) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Token CSRF tidak valid.');
        redirect('index.php?module=konsultasi&page=bidan');
    }
    $jawaban = trim($_POST['jawaban'] ?? '');
    if ($jawaban !== '') {
        db()->update('konsultasi', [
            'jawaban' => $jawaban,
            'status' => 'answered'
        ], 'id = ?', [$id]);
        flash('message', 'Jawaban berhasil disimpan.');
        redirect('index.php?module=konsultasi&page=bidan');
    }
}
$daftar = fetch_all("SELECT * FROM konsultasi ORDER BY created_at DESC LIMIT 20");
$message = flash('message');
?>
<div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
    <!-- Decorative Glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-indigo-950 flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Panel Bidan (Konsultasi)
            </h3>
            <p class="text-sm text-indigo-500/70 font-medium">Jawab pertanyaan dan berikan edukasi kepada orang tua</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium"><?php echo sanitize($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm mb-8">
            <table class="min-w-full divide-y divide-indigo-50 text-sm">
                <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Pengirim</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    <?php if (count($daftar) === 0): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center text-indigo-300 font-medium">Tidak ada data konsultasi.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($daftar as $item): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group <?php echo $id == $item['id'] ? 'bg-indigo-50' : ''; ?>">
                            <td class="px-6 py-4 text-indigo-500 font-medium"><?php echo date('d M Y H:i', strtotime($item['created_at'])); ?></td>
                            <td class="px-6 py-4 text-indigo-950 font-bold"><?php echo sanitize($item['nama_pengirim']); ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($item['status'] === 'answered'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600">Terjawab</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-600">Menunggu</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="index.php?module=konsultasi&page=bidan&id=<?php echo $item['id']; ?>" 
                                   class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg font-bold hover:bg-indigo-600 hover:text-white transition-all text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                    Jawab
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($id && $konsultasi): ?>
            <div class="rounded-3xl border border-indigo-100 bg-white/40 p-8 backdrop-blur-md animate-fade-in">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-indigo-950 leading-none">Pertanyaan dari <?php echo sanitize($konsultasi['nama_pengirim']); ?></h4>
                        <p class="text-xs text-indigo-400 mt-1"><?php echo date('d F Y, H:i', strtotime($konsultasi['created_at'])); ?></p>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-2xl border border-indigo-50 text-indigo-900 mb-8 italic">
                    "<?php echo nl2br(sanitize($konsultasi['pertanyaan'])); ?>"
                </div>

                <form method="post" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <label class="block">
                        <span class="text-sm font-black text-indigo-900 ml-1 uppercase tracking-wider">Berikan Jawaban Medis</span>
                        <textarea name="jawaban" rows="5" required placeholder="Tuliskan jawaban atau saran medis di sini..."
                                  class="mt-2 block w-full rounded-2xl border-indigo-100 bg-white px-5 py-4 text-indigo-950 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2"><?php echo sanitize($konsultasi['jawaban']); ?></textarea>
                    </label>
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-indigo-100 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest">
                            Simpan & Kirim Jawaban
                        </button>
                        <a href="index.php?module=konsultasi&page=bidan" class="bg-white border-2 border-indigo-100 text-indigo-400 font-bold py-4 px-8 rounded-2xl hover:bg-indigo-50 transition-all">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
