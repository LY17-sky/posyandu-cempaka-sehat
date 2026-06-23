<?php
require_once __DIR__ . '/config/database.php';
requireLogin();

$user = getCurrentUser();
$id_pos = $user['id_pos'] ?? 0;
$isAdminPos = isAdminPos();
$isUserView = isUserView();

// Admin Pos and User View have fixed pos assignment, redirect to index
if ($isAdminPos || $isUserView) {
    if ($id_pos > 0) {
        $_SESSION['pos_aktif'] = $id_pos;
        header('Location: index.php');
        exit;
    }
}

// Get pos from database or use hardcoded fallback
$posList = [];
try {
    $posData = db()->select('SELECT id, nama FROM pos_cempaka ORDER BY id ASC');
    foreach ($posData as $pos) {
        $posList[$pos['id']] = $pos['nama'];
    }
} catch (Exception $e) {
    // Fallback to hardcoded pos if table doesn't exist yet
    $posList = [
        1 => 'Cempaka I',
        2 => 'Cempaka II',
        3 => 'Cempaka III',
        4 => 'Cempaka IV',
        5 => 'Cempaka V'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid']);
        exit;
    }
    $pos_id = intval($_POST['pos_id'] ?? 0);
    
    if ($id_pos > 0 && $pos_id !== $id_pos) {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses ke pos ini']);
        exit;
    }
    
    $_SESSION['pos_aktif'] = $pos_id;
    echo json_encode(['success' => true, 'redirect' => 'index.php']);
    exit;
}

$accessiblePos = $id_pos === 0 ? $posList : [$id_pos => $posList[$id_pos]];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Posyandu - Sistem Posyandu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
    <body class="bg-gradient-to-br from-blue-100 via-pink-50 to-blue-100 min-h-screen flex items-center justify-center p-4">
     <div class="absolute inset-0 overflow-hidden pointer-events-none">
         <div class="absolute top-0 left-0 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
         <div class="absolute top-0 right-0 w-96 h-96 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
         <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
     </div>
    
     <div class="w-full max-w-4xl relative z-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-blue-400 to-pink-400 bg-clip-text text-transparent">
                <?php echo $isAdminPos ? 'Info Pos' : ($isUserView ? 'Info Akses' : 'Pilih Posyandu'); ?>
            </h1>
            <p class="text-gray-600 mt-2"><?php echo $isAdminPos ? 'Anda terdaftar sebagai Admin Pos' : ($isUserView ? 'Akses data balita Anda' : 'Pilih posyandu yang ingin Anda kelola'); ?></p>
            <p class="text-sm text-gray-500 mt-1">Logged in as: <?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($accessiblePos as $id => $nama): ?>
            <div onclick="pilihPos(<?php echo $id; ?>, '<?php echo $nama; ?>')" 
                  class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer border-2 border-transparent hover:border-blue-400 group">
                <div class="text-center">
                     <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-300 to-pink-300 flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900"><?php echo $nama; ?></h3>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($id_pos === 0): ?>
        <div class="text-center text-gray-500 text-sm mt-6">Klik kartu untuk memilih posyandu</p>
        <?php endif; ?>
        <?php if ($isAdminPos): ?>
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-blue-800 text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                Anda adalah Admin Pos untuk <?php echo $posList[$id_pos]; ?>
            </p>
        </div>
        <?php endif; ?>
        <?php if ($isUserView): ?>
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800 text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                Akses terbatas: Hanya data pos <?php echo $posList[$id_pos]; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    async function pilihPos(posId, posNama) {
        const result = await Swal.fire({
            title: 'Pilih Posyandu?',
            text: 'Anda akan masuk ke ' + posNama,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Pilih',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280'
        });
        
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('pos_id', posId);
            formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
            
            const response = await fetch('choose_pos.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error'
                });
            }
        }
    }
    </script>
</body>
</html>