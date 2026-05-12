<?php
require 'config/database.php';
requireLogin();

$user = getCurrentUser();
$isAdmin = isAdmin();
$isAdminPos = isAdminPos();
$isUserView = isUserView();
$userPosId = getUserPosId();

if (($isAdminPos || $isUserView) && $userPosId == 0) {
    header('Location: choose_pos.php');
    exit;
}

if ($isAdminPos || $isUserView) {
    $_SESSION['pos_aktif'] = $userPosId;
}

if ($isAdmin && !isset($_SESSION['pos_aktif'])) {
    header('Location: choose_pos.php');
    exit;
}

$pos_aktif = $_SESSION['pos_aktif'] ?? 0;
$posNama = $pos_aktif > 0 ? 'Cempaka ' . $pos_aktif : 'Semua Pos';

$module = $_GET['module'] ?? 'dashboard';
$page = $_GET['page'] ?? 'home';

$adminMenus = [
    'dashboard' => ['home'],
    'balita' => ['daftar', 'tambah', 'detail'],
    'timbang' => ['input', 'riwayat', 'grafik', 'deteksi_who'],
    'imunisasi' => ['input', 'jadwal', 'jadwal_default', 'status'],
    'jadwal' => ['posyandu', 'notifikasi', 'reminder'],
    'laporan' => ['bulanan', 'export_excel', 'export_pdf'],
    'kartu' => ['cetak_kms'],
    'konsultasi' => ['riwayat', 'bidan'],
    'backup' => ['auto_backup', 'restore', 'list']
];

$userMenus = [
    'dashboard' => ['home'],
    'timbang' => ['riwayat', 'grafik'],
    'imunisasi' => ['jadwal'],
    'konsultasi' => ['form', 'riwayat'],
    'kartu' => ['cetak_kms']
];

$userViewMenus = [
    'dashboard' => ['home'],
    'balita' => ['detail'],
    'timbang' => ['riwayat', 'grafik'],
    'imunisasi' => ['jadwal'],
    'jadwal' => ['posyandu'],
    'kartu' => ['cetak_kms'],
    'konsultasi' => ['form', 'riwayat']
];

$adminPosMenus = [
    'dashboard' => ['home'],
    'balita' => ['daftar', 'tambah', 'detail'],
    'timbang' => ['input', 'riwayat', 'grafik', 'deteksi_who'],
    'imunisasi' => ['input', 'jadwal', 'jadwal_default', 'status'],
    'jadwal' => ['posyandu', 'notifikasi', 'reminder'],
    'kartu' => ['cetak_kms'],
    'konsultasi' => ['form', 'riwayat', 'bidan'],
];

$menus = $isAdmin ? $adminMenus : ($isUserView ? $userViewMenus : $adminPosMenus);

if (!isset($menus[$module]) || !in_array($page, $menus[$module])) {
    $module = 'dashboard';
    $page = 'home';
}

$directOutputs = ($module === 'api' || ($module === 'laporan' && in_array($page, ['export_excel', 'export_pdf'])) || ($module === 'backup' && in_array($page, ['auto_backup', 'restore'])));
if ($directOutputs) {
    include "modules/{$module}/{$page}.php";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Posyandu - <?php echo ucfirst($module); ?></title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <link rel="stylesheet" href="assets/css/sidebar.css?v=2">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <!-- ===== SIDEBAR ===== -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">

        <!-- Ambient glows -->
        <div class="sb-glow-top"></div>
        <div class="sb-glow-bottom"></div>

        <!-- Header -->
        <div class="sidebar-header">
            <div class="sidebar-brand-wrap">
                <div class="sidebar-logo-box">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="sidebar-brand-name">CempakaSehat</div>
                    <div class="sidebar-brand-tagline">Sistem Posyandu</div>
                </div>
                <button id="sidebarCloseBtn" class="lg:hidden flex items-center justify-center" style="background:rgba(255,255,255,0.08);border:none;color:rgba(255,255,255,0.6);padding:0.375rem;border-radius:8px;cursor:pointer;transition:all .2s">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- User Card -->
        <div class="sidebar-user-section">
            <div class="sidebar-user-card">
                <div class="sidebar-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                 <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="sidebar-user-role"><?php echo isAdmin() ? 'Super Admin' : (isAdminPos() ? 'Admin Pos' : 'User View'); ?></div>
                </div>
                <div class="sidebar-user-dot"></div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <?php
            $menuIcons = [
                'dashboard'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
                'balita'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
                'timbang'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
                'imunisasi'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                'jadwal'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
                'laporan'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                'kartu'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>',
                'konsultasi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>',
                'backup'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>',
            ];

            $menuLabels = [
                'dashboard'  => 'Beranda',
                'balita'     => 'Data Balita',
                'timbang'    => 'Timbang',
                'imunisasi'  => 'Imunisasi',
                'jadwal'     => 'Jadwal',
                'laporan'    => 'Laporan',
                'kartu'      => 'Kartu KMS',
                'konsultasi' => 'Konsultasi',
                'backup'     => 'Backup',
            ];

            $mainMenuKeys = ['dashboard', 'balita', 'laporan', 'kartu'];
            $opsMenuKeys  = ['timbang', 'imunisasi', 'jadwal', 'konsultasi', 'backup'];

            if (!$isAdmin) {
                $mainMenuKeys = ['dashboard', 'balita', 'kartu'];
                $opsMenuKeys  = ['timbang', 'imunisasi', 'jadwal', 'konsultasi'];
            }

            $orderedMenus = [];
            foreach ($mainMenuKeys as $k) { if (isset($menus[$k])) $orderedMenus[$k] = $menus[$k]; }
            foreach ($opsMenuKeys as $k) { if (isset($menus[$k])) $orderedMenus[$k] = $menus[$k]; }

            $printedMainLabel = false;
            $printedOpsLabel  = false;

            foreach ($orderedMenus as $menuModule => $pages):
                $isMain   = in_array($menuModule, $mainMenuKeys);
                $isActive = ($module === $menuModule);
                $label    = $menuLabels[$menuModule] ?? ucfirst($menuModule);
                $icon     = $menuIcons[$menuModule] ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>';

                if ($isMain && !$printedMainLabel): $printedMainLabel = true; ?>
                    <div class="sb-section-label">Menu Utama</div>
                <?php endif;
                if (!$isMain && !$printedOpsLabel): $printedOpsLabel = true; ?>
                    <div class="sb-section-label">Operasional</div>
                <?php endif;

                if (count($pages) === 1):
                    $menuPage = reset($pages); ?>
                    <a href="?module=<?php echo $menuModule; ?>&page=<?php echo $menuPage; ?>"
                        class="nav-link <?php echo $isActive ? 'active' : ''; ?>">
                        <svg class="nav-link-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <?php echo $icon; ?>
                        </svg>
                        <span class="nav-link-text"><?php echo $label; ?></span>
                    </a>

                <?php else: ?>
                    <button type="button"
                        class="nav-link menu-toggle <?php echo $isActive ? 'active dropdown-open' : ''; ?>"
                        data-module="<?php echo $menuModule; ?>">
                        <svg class="nav-link-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <?php echo $icon; ?>
                        </svg>
                        <span class="nav-link-text"><?php echo $label; ?></span>
                        <svg class="nav-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="nav-dropdown menu-content <?php echo $isActive ? 'open' : ''; ?>"
                        data-module="<?php echo $menuModule; ?>">
                        <?php foreach ($pages as $menuPage):
                            $subActive = ($module === $menuModule && $page === $menuPage); ?>
                            <a href="?module=<?php echo $menuModule; ?>&page=<?php echo $menuPage; ?>"
                                class="nav-dropdown-link <?php echo $subActive ? 'active' : ''; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $menuPage)); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer-area">
            <a href="logout.php" class="sb-logout-link">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                </svg>
                <span>Keluar Akun</span>
            </a>
        </div>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div id="main-wrapper">
        <!-- Top Navigation -->
        <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-200 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Mobile Menu Toggle -->
                    <div class="flex items-center space-x-4">
                        <button id="sidebarToggle" class="lg:hidden text-gray-600 hover:text-gray-900 transition p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <!-- Page Title -->
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-semibold text-gray-900">
                                <?php echo ucfirst(str_replace('_', ' ', $page)); ?>
                            </h1>
                            <p class="text-xs text-gray-500"><?php echo ucfirst($module); ?></p>
                        </div>
                    </div>

                    <!-- Right Actions -->
                    <div class="flex items-center space-x-3">
                        <!-- Pos Aktif Badge -->
                        <?php if ($isAdmin): ?>
                        <a href="choose_pos.php" class="hidden sm:inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium transition" title="Ganti Pos">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <?php echo $posNama; ?>
                        </a>
                        <?php endif; ?>

                        <!-- Dark Mode Toggle -->
                        <button id="darkModeToggle" class="text-gray-500 hover:text-gray-700 transition p-2 hover:bg-gray-100 rounded-lg" title="Dark Mode">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </button>

                        <!-- User Menu Dropdown -->
                        <div class="relative">
                            <button id="userMenuBtn" class="flex items-center space-x-2 p-1.5 hover:bg-gray-100 rounded-lg transition">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-300 to-pink-300 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <svg class="w-4 h-4 text-gray-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white/95 backdrop-blur-md rounded-xl shadow-2xl border border-gray-200 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-pink-50">
                                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                                </div>
                                <div class="py-1">
                                    <a href="logout.php" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="p-4 sm:p-6 lg:p-8 min-h-screen main-content-bg">
            <div class="animate-fade-in">
                <?php
                $file = "modules/{$module}/{$page}.php";
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo "<div class='text-center py-12'>";
                    echo "<svg class='w-16 h-16 mx-auto text-gray-400 mb-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
                    echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>";
                    echo "</svg>";
                    echo "<p class='text-gray-500 text-lg'>Halaman tidak ditemukan</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </main>

        <footer class="bg-white border-t border-gray-200 py-4 px-6 text-center text-gray-600 text-sm">
            <p>© 2026 Sistem Informasi Posyandu. Semua hak dilindungi.</p>
        </footer>
    </div>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
