<?php
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ubah_password'])) {
        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';
        $passwordKonfirm = $_POST['password_konfirm'] ?? '';

        if (empty($passwordLama) || empty($passwordBaru) || empty($passwordKonfirm)) {
            flash('error', 'Semua field harus diisi.');
        } elseif (strlen($passwordBaru) < 6) {
            flash('error', 'Password baru minimal 6 karakter.');
        } elseif ($passwordBaru !== $passwordKonfirm) {
            flash('error', 'Password baru dan konfirmasi tidak cocok.');
        } else {
            // Verify password lama
            if (!password_verify($passwordLama, $user['password'])) {
                flash('error', 'Password lama tidak sesuai.');
            } else {
                // Update password
                $hashedPassword = password_hash($passwordBaru, PASSWORD_BCRYPT);
                db()->update('users', [
                    'password' => $hashedPassword
                ], 'id = ?', [$user['id']]);
                flash('success', 'Password berhasil diubah.');
                redirect('index.php?module=settings&page=profile');
            }
        }
    }
}

$error = flash('error');
$success = flash('success');
?>

<div class="max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-indigo-950">Pengaturan Akun</h1>
        <p class="text-sm text-indigo-400 font-medium mt-2">Kelola informasi akun dan keamanan Anda</p>
    </div>

    <!-- Profile Card -->
    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl mb-8 relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                Informasi Profil
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                    <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest block mb-2">Username</span>
                    <p class="text-lg font-black text-indigo-950"><?php echo sanitize($user['username']); ?></p>
                </div>

                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                    <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest block mb-2">Role</span>
                    <p class="text-lg font-black text-indigo-950"><?php echo sanitize(ucfirst($user['role'])); ?></p>
                </div>

                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 md:col-span-2">
                    <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest block mb-2">Tergabung Sejak</span>
                    <p class="text-lg font-black text-indigo-950"><?php echo date('d F Y H:i', strtotime($user['created_at'] ?? date('Y-m-d H:i:s'))); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Ubah Password
            </h2>

            <?php if ($error): ?>
            <div class="mb-6 rounded-xl border-l-4 border-red-400 bg-red-50 p-4 text-red-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium"><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-6 rounded-xl border-l-4 border-emerald-400 bg-emerald-50 p-4 text-emerald-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium"><?php echo $success; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <input type="hidden" name="ubah_password" value="1">

                <div>
                    <label class="block text-sm font-bold text-indigo-900 mb-2">
                        Password Lama
                    </label>
                    <input type="password" name="password_lama" required
                           class="w-full px-4 py-3 rounded-xl border-2 border-indigo-100 focus:border-pink-400 focus:ring-pink-400/20 outline-none transition-all bg-white">
                </div>

                <div>
                    <label class="block text-sm font-bold text-indigo-900 mb-2">
                        Password Baru
                    </label>
                    <input type="password" name="password_baru" required
                           class="w-full px-4 py-3 rounded-xl border-2 border-indigo-100 focus:border-pink-400 focus:ring-pink-400/20 outline-none transition-all bg-white">
                    <p class="text-xs text-indigo-500 mt-1">Minimal 6 karakter</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-indigo-900 mb-2">
                        Konfirmasi Password Baru
                    </label>
                    <input type="password" name="password_konfirm" required
                           class="w-full px-4 py-3 rounded-xl border-2 border-indigo-100 focus:border-pink-400 focus:ring-pink-400/20 outline-none transition-all bg-white">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-pink-500 to-indigo-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-pink-100 hover:scale-[1.01] active:scale-95 transition-all">
                        Simpan Password Baru
                    </button>
                    <a href="index.php?module=dashboard&page=home" class="flex items-center justify-center px-6 py-3 rounded-xl border-2 border-indigo-100 text-indigo-600 font-bold hover:bg-indigo-50 transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success message setelah 5 detik
    const successMsg = document.querySelector('.animate-fade-in');
    if (successMsg && successMsg.classList.contains('bg-emerald-50')) {
        setTimeout(() => {
            successMsg.style.opacity = '0';
            successMsg.style.transition = 'opacity 0.3s';
        }, 5000);
    }
});
</script>
