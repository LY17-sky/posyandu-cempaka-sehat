<?php
if (!isAdmin()) {
    echo "<div class='card p-12 text-center'><p class='text-indigo-300 font-bold'>Anda tidak memiliki akses ke halaman ini.</p></div>";
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect('index.php?module=admin&page=users');
    }
    if (isset($_POST['tambah_user'])) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user_view';
        $no_telp = trim($_POST['no_telp'] ?? '');
        $id_pos = intval($_POST['id_pos'] ?? 0);
        $balita_id = !empty($_POST['balita_id']) ? intval($_POST['balita_id']) : null;

        if (empty($username) || empty($password)) {
            flash('error', 'Username dan password harus diisi.');
        } elseif (strlen($password) < 6) {
            flash('error', 'Password minimal 6 karakter.');
        } else {
            $existing = db()->selectOne("SELECT id FROM users WHERE username = ?", [$username]);
            if ($existing) {
                flash('error', 'Username sudah digunakan.');
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                db()->insert('users', [
                    'username' => $username,
                    'password' => $hashed,
                    'role' => $role,
                    'no_telp' => $no_telp,
                    'id_pos' => $id_pos,
                    'balita_id' => $balita_id
                ]);
                flash('message', 'User "' . $username . '" berhasil ditambahkan.');
                redirect('index.php?module=admin&page=users');
            }
        }
    }

    if (isset($_POST['edit_user'])) {
        $id = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user_view';
        $no_telp = trim($_POST['no_telp'] ?? '');
        $id_pos = intval($_POST['id_pos'] ?? 0);
        $balita_id = !empty($_POST['balita_id']) ? intval($_POST['balita_id']) : null;

        if ($id > 0 && !empty($username)) {
            $data = [
                'username' => $username,
                'role' => $role,
                'no_telp' => $no_telp,
                'id_pos' => $id_pos,
                'balita_id' => $balita_id
            ];
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    flash('error', 'Password minimal 6 karakter.');
                    redirect('index.php?module=admin&page=users&edit=' . $id);
                }
                $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            }
            $existing = db()->selectOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
            if ($existing) {
                flash('error', 'Username sudah digunakan oleh user lain.');
            } else {
                db()->update('users', $data, 'id = ?', [$id]);
                flash('message', 'User berhasil diperbarui.');
                redirect('index.php?module=admin&page=users');
            }
        }
    }

    if (isset($_POST['hapus_user'])) {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $user = db()->selectOne("SELECT username FROM users WHERE id = ?", [$id]);
            if ($user && $user['username'] === 'admin') {
                flash('error', 'Tidak dapat menghapus akun Super Admin utama.');
            } else {
                db()->delete('users', 'id = ?', [$id]);
                flash('message', 'User berhasil dihapus.');
            }
            redirect('index.php?module=admin&page=users');
        }
    }
}

$users = db()->select("
    SELECT u.*, p.nama as pos_nama, b.nama as balita_nama
    FROM users u
    LEFT JOIN pos_cempaka p ON u.id_pos = p.id
    LEFT JOIN balita b ON u.balita_id = b.id
    ORDER BY u.role, u.username ASC
");

$posList = db()->select("SELECT * FROM pos_cempaka ORDER BY nama ASC");
$balitaList = db()->select("SELECT id, nama, nik_ibu FROM balita ORDER BY nama ASC");

$message = flash('message');
$error = flash('error');

$editData = null;
$editId = intval($_GET['edit'] ?? 0);
if ($editId > 0) {
    $editData = db()->selectOne("SELECT * FROM users WHERE id = ?", [$editId]);
}

$roleLabels = [
    'super_admin' => 'Super Admin',
    'admin_pos' => 'Admin Pos',
    'user_view' => 'User View'
];
?>

<div class="space-y-8">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-indigo-950">Kelola User</h1>
            <p class="text-sm text-indigo-400 font-medium mt-2">Tambah dan kelola akun pengguna sistem</p>
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

    <!-- Form Card -->
    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6">
                <?php echo $editData ? 'Edit User' : 'Tambah User Baru'; ?>
            </h2>

            <form method="post" class="grid gap-6 md:grid-cols-3">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="tambah_user" value="1">
                <?php endif; ?>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Username</span>
                    <input type="text" name="username" required placeholder="Contoh: cempaka1"
                           value="<?php echo $editData ? sanitize($editData['username']) : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">
                        Password <?php echo $editData ? '(kosongkan jika tidak diubah)' : ''; ?>
                    </span>
                    <input type="password" name="password" <?php echo $editData ? '' : 'required'; ?>
                           placeholder="<?php echo $editData ? 'Biarkan kosong' : 'Minimal 6 karakter'; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Role</span>
                    <select name="role" required
                            class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        <option value="">-- Pilih Role --</option>
                        <?php foreach ($roleLabels as $val => $label): ?>
                        <option value="<?php echo $val; ?>" <?php echo $editData && $editData['role'] === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">No. Telp</span>
                    <input type="text" name="no_telp" placeholder="Contoh: 081234567890"
                           value="<?php echo $editData ? sanitize($editData['no_telp'] ?? '') : ''; ?>"
                           class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2" />
                </label>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Pos</span>
                    <select name="id_pos"
                            class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        <option value="0">-- Semua Pos --</option>
                        <?php foreach ($posList as $pos): ?>
                        <option value="<?php echo $pos['id']; ?>" <?php echo $editData && intval($editData['id_pos'] ?? 0) === $pos['id'] ? 'selected' : ''; ?>><?php echo sanitize($pos['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-bold text-indigo-900 ml-1 uppercase">Tautan Balita (User View)</span>
                    <select name="balita_id"
                            class="mt-1.5 block w-full rounded-xl border-indigo-100 bg-white px-4 py-2.5 text-indigo-900 focus:border-pink-400 focus:ring-pink-400/20 transition-all outline-none border-2">
                        <option value="">-- Tidak ada --</option>
                        <?php foreach ($balitaList as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $editData && intval($editData['balita_id'] ?? 0) === $b['id'] ? 'selected' : ''; ?>><?php echo sanitize($b['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="md:col-span-3 flex gap-3">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-100 hover:scale-[1.01] active:scale-95 transition-all">
                        <?php echo $editData ? 'Perbarui User' : 'Simpan User Baru'; ?>
                    </button>
                    <?php if ($editData): ?>
                    <a href="index.php?module=admin&page=users" class="flex items-center justify-center px-6 py-3 rounded-xl border-2 border-indigo-100 text-indigo-600 font-bold hover:bg-indigo-50 transition-all">
                        Batal
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Users -->
    <div class="card p-8 bg-white/80 backdrop-blur-md border-white/20 shadow-xl rounded-2xl relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-pink-200/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <h2 class="text-lg font-bold text-indigo-950 mb-6">Daftar User</h2>

            <div class="overflow-hidden rounded-xl border border-indigo-50 shadow-sm bg-white/50 backdrop-blur-sm">
                <table class="min-w-full divide-y divide-indigo-50 text-sm">
                    <thead class="bg-gradient-to-r from-indigo-50 to-pink-50 text-indigo-900">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">No.</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Username</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Pos</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Tautan Balita</th>
                            <th class="px-6 py-4 text-center font-bold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50">
                        <?php if (count($users) === 0): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-indigo-300 font-medium">Belum ada user.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($users as $idx => $u): ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 font-bold text-indigo-600"><?php echo $idx + 1; ?></td>
                                <td class="px-6 py-4 font-black text-indigo-950"><?php echo sanitize($u['username']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php
                                        echo $u['role'] === 'super_admin' ? 'bg-purple-100 text-purple-700' : ($u['role'] === 'admin_pos' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700');
                                    ?>"><?php echo $roleLabels[$u['role']] ?? $u['role']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-indigo-600 font-medium"><?php echo $u['pos_nama'] ? sanitize($u['pos_nama']) : '<span class="text-indigo-300">-</span>'; ?></td>
                                <td class="px-6 py-4 text-indigo-500"><?php echo $u['balita_nama'] ? sanitize($u['balita_nama']) : '<span class="text-indigo-300">-</span>'; ?></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="index.php?module=admin&page=users&edit=<?php echo $u['id']; ?>"
                                           class="inline-flex items-center gap-1 bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-blue-600 hover:text-white transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </a>
                                        <?php if ($u['username'] !== 'admin'): ?>
                                        <form method="post" onsubmit="return confirm('Hapus user &quot;<?php echo sanitize($u['username']); ?>&quot;?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="hapus_user" value="1">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 bg-red-100 text-red-600 px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-red-600 hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
