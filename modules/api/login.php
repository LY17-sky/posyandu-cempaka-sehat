<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
    exit;
}

try {
    // 1. Cek login berdasarkan username (untuk Super Admin dan Admin Pos)
    $user = db()->selectOne('SELECT * FROM users WHERE username = ?', [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['id_pos'] = $user['id_pos'] ?? 0;
        
        // Super Admin perlu pilih POS (atau bisa lihat semua), 
        // Admin Pos langsung masuk ke POS yang ditugaskan
        $needPos = ($user['role'] === 'super_admin' || $user['id_pos'] == 0);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login berhasil', 
            'role' => $user['role'], 
            'need_pos' => $needPos
        ]);
        exit;
    }

    // 2. Cek login berdasarkan NIK Ibu (untuk User View / Ibu)
    // Mencari balita yang memiliki nik_ibu sesuai input
    $balitaSample = db()->selectOne('SELECT * FROM balita WHERE nik_ibu = ?', [$username]);
    if ($balitaSample) {
        $user = db()->selectOne('SELECT * FROM users WHERE username = ? AND role = ?', [$username, 'user_view']);
        if (!$user) {
            // Auto-create user for Mother if not exists
            $userId = db()->insert('users', [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user_view',
                'id_pos' => $balitaSample['id_pos']
            ]);
            $user = db()->selectOne('SELECT * FROM users WHERE id = ?', [$userId]);
        }

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['id_pos'] = $user['id_pos'] ?? 0;
            echo json_encode(['success' => true, 'message' => 'Login berhasil', 'role' => $user['role']]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>