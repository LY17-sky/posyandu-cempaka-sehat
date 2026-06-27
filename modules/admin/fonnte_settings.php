<?php
if (!isAdmin()) {
    echo "<div class='card p-12 text-center'><p class='text-indigo-300 font-bold'>Anda tidak memiliki akses ke halaman ini.</p></div>";
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_fonnte'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid.');
        redirect('index.php?module=admin&page=fonnte_settings');
    }
    $apiKey = trim($_POST['fonnte_api_key'] ?? '');
    db()->query("INSERT OR REPLACE INTO config (key_name, value) VALUES ('fonnte_api_key', ?)", [$apiKey]);
    flash('message', 'API Key Fonnte berhasil disimpan.');
    redirect('index.php?module=admin&page=fonnte_settings');
}

$currentKey = '';
$row = db()->selectOne("SELECT value FROM config WHERE key_name = 'fonnte_api_key'");
if ($row) {
    $currentKey = $row['value'];
}

$message = flash('message');
$error = flash('error');
?>

<div class="space-y-8">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-indigo-950">Konfigurasi Fonnte</h1>
            <p class="text-sm text-indigo-400 font-medium mt-2">Atur API Key untuk notifikasi WhatsApp</p>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span class="font-medium"><?php echo $message; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="rounded-xl border-l-4 border-red-400 bg-red-50 p-4 text-red-800 shadow-sm">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            <span class="font-medium"><?php echo $error; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6">API Key Fonnte</h2>

            <div class="mb-6 p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                <p class="text-sm text-indigo-600 font-medium leading-relaxed">
                    Fonnte digunakan untuk mengirim notifikasi WhatsApp otomatis ke orang tua balita,
                    termasuk notifikasi penimbangan, peringatan gizi abnormal, dan reminder jadwal posyandu.
                    <br><br>
                    Daftar akun Fonnte di <a href="https://fonnte.com" target="_blank" class="text-pink-500 underline">https://fonnte.com</a>
                    lalu masukkan API Key di bawah ini.
                </p>
            </div>

            <form method="post" class="max-w-xl space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="simpan_fonnte" value="1">

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Fonnte API Key</span>
                    <input type="text" name="fonnte_api_key"
                           value="<?php echo sanitize($currentKey); ?>"
                           placeholder="Masukkan API Key dari Fonnte"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                    <p class="text-xs text-indigo-500 mt-1">Kosongkan untuk menonaktifkan notifikasi WhatsApp</p>
                </label>

                <div class="flex gap-3">
                    <button type="submit" class="bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-100 hover:scale-[1.01] active:scale-95 transition-all">
                        Simpan API Key
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6">Status Notifikasi</h2>
            <?php if (!empty($currentKey)): ?>
                <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                    <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-emerald-800 font-semibold">API Key terkonfigurasi — notifikasi WhatsApp aktif</span>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200">
                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                    <span class="text-amber-800 font-semibold">API Key belum dikonfigurasi — notifikasi WhatsApp tidak akan terkirim</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
