<?php
ob_start();
session_start();

define('DB_NAME', __DIR__ . '/../database.sqlite');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO('sqlite:' . DB_NAME, null, null, $options);
            $this->pdo = $pdo;
            $this->initializeSchema();
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function select($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function selectOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $col) {
            $setParts[] = "$col = :$col";
        }
        $set = implode(', ', $setParts);
        
        // Rename where params to avoid collision
        $renamedParams = [];
        $i = 0;
        foreach ($whereParams as $p) {
            $key = '_where_' . $i++;
            $renamedParams[$key] = $p;
        }
        
        // Rebuild where with renamed params
        $whereWithParams = $where;
        $i = 0;
        foreach ($whereParams as $p) {
            $whereWithParams = preg_replace('/\?/', ':_where_' . $i++, $whereWithParams, 1);
        }
        
        $params = array_merge($data, $renamedParams);
        $sql = "UPDATE $table SET $set WHERE $whereWithParams";
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $params)->rowCount();
    }

    private function initializeSchema() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS balita (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama VARCHAR(100),
            nik VARCHAR(16) UNIQUE,
            tgl_lahir DATE,
            nama_ayah VARCHAR(100),
            nama_ibu VARCHAR(100),
            no_telp VARCHAR(15),
            alamat TEXT,
            foto VARCHAR(255) DEFAULT NULL,
            id_pos INTEGER DEFAULT 1,
            is_active INTEGER DEFAULT 1,
            nik_ibu VARCHAR(16),
            jenis_kelamin CHAR(1) DEFAULT 'L',
            bb_lahir DECIMAL(5,2) DEFAULT NULL,
            tb_lahir DECIMAL(5,2) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS timbang (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            balita_id INTEGER,
            bb DECIMAL(5,2),
            tb DECIMAL(5,2),
            lk DECIMAL(5,2),
            lila DECIMAL(5,2),
            tgl_timbang DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (balita_id) REFERENCES balita(id) ON DELETE CASCADE
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE,
            password VARCHAR(255),
            role VARCHAR(20) DEFAULT 'user_view',
            no_telp VARCHAR(15),
            id_pos INTEGER DEFAULT 0,
            balita_id INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (balita_id) REFERENCES balita(id)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS imunisasi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            balita_id INTEGER,
            jenis_imunisasi VARCHAR(100),
            tgl_imunisasi DATE,
            status VARCHAR(20) DEFAULT 'belum',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (balita_id) REFERENCES balita(id) ON DELETE CASCADE
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS jadwal_posyandu (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tanggal DATE,
            lokasi VARCHAR(255),
            waktu TIME,
            catatan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS konsultasi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            balita_id INTEGER,
            nama_pengirim VARCHAR(100),
            pertanyaan TEXT,
            jawaban TEXT,
            bidan_id INTEGER,
            tgl_konsultasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (balita_id) REFERENCES balita(id) ON DELETE CASCADE
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS backup_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            file_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tujuan VARCHAR(255) NOT NULL,
            pesan TEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS pos_cempaka (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama VARCHAR(100) NOT NULL UNIQUE,
            lokasi VARCHAR(255),
            kontak VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS config (
            key_name TEXT PRIMARY KEY,
            value TEXT
        )");

        // Seed default pos data if empty
        $posCount = $this->selectOne('SELECT COUNT(*) as count FROM pos_cempaka');
        if (!$posCount || intval($posCount['count']) === 0) {
            $this->insert('pos_cempaka', ['nama' => 'Cempaka I', 'lokasi' => 'Lokasi I', 'kontak' => '']);
            $this->insert('pos_cempaka', ['nama' => 'Cempaka II', 'lokasi' => 'Lokasi II', 'kontak' => '']);
            $this->insert('pos_cempaka', ['nama' => 'Cempaka III', 'lokasi' => 'Lokasi III', 'kontak' => '']);
            $this->insert('pos_cempaka', ['nama' => 'Cempaka IV', 'lokasi' => 'Lokasi IV', 'kontak' => '']);
            $this->insert('pos_cempaka', ['nama' => 'Cempaka V', 'lokasi' => 'Lokasi V', 'kontak' => '']);
        }

        $row = $this->selectOne('SELECT COUNT(*) as count FROM balita');
        if (!$row || intval($row['count']) === 0) {
            $this->seedDemoData();
        }
        
        // Migration untuk database lama — aman dijalankan meski kolom sudah ada
        foreach (['jenis_kelamin', 'bb_lahir', 'tb_lahir'] as $col) {
            try {
                $this->pdo->exec("ALTER TABLE balita ADD COLUMN $col TEXT");
            } catch (Exception $e) {
            }
        }
    }

    private function seedDemoData() {
        $this->insert('balita', [
            'nama' => 'Ahmad Rahman',
            'nik' => '1234567890123456',
            'tgl_lahir' => '2020-01-15',
            'nama_ayah' => 'Rahman',
            'nama_ibu' => 'Siti',
            'nik_ibu' => '1234567890123456',
            'no_telp' => '081234567890',
            'alamat' => 'Jl. Sudirman No. 1',
            'id_pos' => 1
        ]);
        $this->insert('balita', [
            'nama' => 'Fatimah Sari',
            'nik' => '1234567890123457',
            'tgl_lahir' => '2019-05-20',
            'nama_ayah' => 'Sari',
            'nama_ibu' => 'Maya',
            'nik_ibu' => '1234567890123457',
            'no_telp' => '081234567891',
            'alamat' => 'Jl. Thamrin No. 2',
            'id_pos' => 2
        ]);
        $this->insert('balita', [
            'nama' => 'Budi Santoso',
            'nik' => '1234567890123458',
            'tgl_lahir' => '2021-03-10',
            'nama_ayah' => 'Santoso',
            'nama_ibu' => 'Ani',
            'nik_ibu' => '1234567890123458',
            'no_telp' => '081234567892',
            'alamat' => 'Jl. Gajah Mada No. 3',
            'id_pos' => 3
        ]);

        $this->query("INSERT INTO timbang (balita_id, bb, tb, lk, lila, tgl_timbang) VALUES
            (1, 8.5, 70.0, 42.0, 12.5, '2023-01-15'),
            (1, 9.2, 72.5, 43.2, 13.0, '2023-02-15'),
            (1, 9.8, 75.0, 44.0, 13.5, '2023-03-15'),
            (2, 7.8, 68.0, 41.5, 12.0, '2023-01-20'),
            (2, 8.3, 70.5, 42.5, 12.8, '2023-02-20'),
            (3, 9.0, 71.0, 42.8, 13.2, '2023-03-10')");

        $adminPassword = password_hash('password', PASSWORD_DEFAULT);
        $this->insert('users', [
            'username' => 'admin',
            'password' => $adminPassword,
            'role' => 'super_admin',
            'id_pos' => 0,
            'balita_id' => null
        ]);
        
        // Admin Pos users - one for each pos
        for ($i = 1; $i <= 5; $i++) {
            $adminPosPassword = password_hash('pos123', PASSWORD_DEFAULT);
            $this->insert('users', [
                'username' => 'cempaka' . $i,
                'password' => $adminPosPassword,
                'role' => 'admin_pos',
                'id_pos' => $i,
                'balita_id' => null
            ]);
        }
        
        // User View - linked to their balita (NIK + nama ibu / password)
        $this->insert('users', [
            'username' => '1234567890123456',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'user_view',
            'id_pos' => 1,
            'balita_id' => 1
        ]);
        $this->insert('users', [
            'username' => '1234567890123457',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'user_view',
            'id_pos' => 2,
            'balita_id' => 2
        ]);
        $this->insert('users', [
            'username' => '1234567890123458',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'user_view',
            'id_pos' => 3,
            'balita_id' => 3
        ]);
    }
}

function db() {
    return Database::getInstance();
}

function getUserNik() {
    $user = getCurrentUser();
    if (!$user) return '';
    $username = $user['username'] ?? '';
    $parts = explode(' ', $username);
    return $parts[0] ?: $username;
}

function getPosFilter($column = 'id_pos') {
    $pos_aktif = $_SESSION['pos_aktif'] ?? 0;
    $user = getCurrentUser();
    $userPosId = getUserPosId();
    
    if (isUserView()) {
        return " AND nik_ibu = '" . escape(getUserNik()) . "'";
    }
    
    if (isAdminPos()) {
        if ($userPosId > 0) {
            return " AND $column = " . intval($userPosId);
        }
        return "";
    }
    
    if (isAdmin()) {
        if ($pos_aktif > 0) {
            return " AND $column = " . intval($pos_aktif);
        }
    }
    return "";
}

function getBalitaFilter($column = 'id_pos') {
    $user = getCurrentUser();
    if (isUserView()) {
        return " AND b.nik_ibu = '" . escape(getUserNik()) . "'";
    }
    
    $pos_aktif = $_SESSION['pos_aktif'] ?? 0;
    $userPosId = getUserPosId();
    
    if (isAdminPos()) {
        if ($userPosId > 0) {
            return " AND $column = " . intval($userPosId);
        }
        return "";
    }
    
    if (isAdmin()) {
        if ($pos_aktif > 0) {
            return " AND $column = " . intval($pos_aktif);
        }
    }
    return "";
}

function db_connect() {
    return db()->getConnection();
}

function query_db($sql, $params = []) {
    return db()->query($sql, $params);
}

function fetch_all($sql, $params = []) {
    return db()->select($sql, $params);
}

function fetch_one($sql, $params = []) {
    return db()->selectOne($sql, $params);
}

function escape($value) {
    $quoted = db_connect()->quote(trim($value));
    return $quoted !== false ? substr($quoted, 1, -1) : trim($value);
}

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Location: ' . $url);
    exit;
}

function flash($key, $value = null) {
    if ($value === null) {
        $message = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $message;
    }
    $_SESSION[$key] = $value;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return db()->selectOne('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'super_admin';
}

function isAdminPos() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin_pos';
}

function isUserView() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'user_view';
}

function checkBalitaAccess($balita_id) {
    if (isAdmin() || isAdminPos()) return true;
    if (isUserView()) {
        $balita = db()->selectOne('SELECT * FROM balita WHERE id = ? AND nik_ibu = ?', [$balita_id, getUserNik()]);
        return (bool)$balita;
    }
    return false;
}

function getMotherBalitas() {
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'user_view') return [];
    return db()->select('SELECT * FROM balita WHERE nik_ibu = ? AND is_active = 1', [getUserNik()]);
}

function getUserPosId() {
    $user = getCurrentUser();
    return $user ? ($user['id_pos'] ?? 0) : 0;
}

function getVaksinMaster() {
    return [
        ['id' => 1, 'usia_bulan' => 0, 'jenis' => 'Hepatitis B (HB-0)', 'keterangan' => 'Dosis pertama (0-24 Jam)'],
        ['id' => 2, 'usia_bulan' => 1, 'jenis' => 'BCG', 'keterangan' => 'TBC'],
        ['id' => 3, 'usia_bulan' => 1, 'jenis' => 'Polio 1', 'keterangan' => 'Polio tetes'],
        ['id' => 4, 'usia_bulan' => 2, 'jenis' => 'DPT-HB-Hib 1', 'keterangan' => 'Kombinasi 5 penyakit'],
        ['id' => 5, 'usia_bulan' => 2, 'jenis' => 'Polio 2', 'keterangan' => 'Polio tetes'],
        ['id' => 6, 'usia_bulan' => 2, 'jenis' => 'Rotavirus 1', 'keterangan' => 'Diare berat'],
        ['id' => 7, 'usia_bulan' => 2, 'jenis' => 'PCV 1', 'keterangan' => 'Pneumonia'],
        ['id' => 8, 'usia_bulan' => 3, 'jenis' => 'DPT-HB-Hib 2', 'keterangan' => 'Dosis kedua'],
        ['id' => 9, 'usia_bulan' => 3, 'jenis' => 'Polio 3', 'keterangan' => 'Polio tetes'],
        ['id' => 10, 'usia_bulan' => 4, 'jenis' => 'DPT-HB-Hib 3', 'keterangan' => 'Dosis ketiga'],
        ['id' => 11, 'usia_bulan' => 4, 'jenis' => 'Polio 4', 'keterangan' => 'Polio tetes'],
        ['id' => 12, 'usia_bulan' => 4, 'jenis' => 'IPV', 'keterangan' => 'Polio suntik'],
        ['id' => 13, 'usia_bulan' => 4, 'jenis' => 'Rotavirus 2', 'keterangan' => 'Dosis kedua'],
        ['id' => 14, 'usia_bulan' => 4, 'jenis' => 'PCV 2', 'keterangan' => 'Dosis kedua'],
        ['id' => 15, 'usia_bulan' => 6, 'jenis' => 'PCV 3', 'keterangan' => 'Dosis ketiga'],
        ['id' => 16, 'usia_bulan' => 6, 'jenis' => 'Influenza 1', 'keterangan' => 'Dosis pertama'],
        ['id' => 17, 'usia_bulan' => 7, 'jenis' => 'Influenza 2', 'keterangan' => 'Dosis kedua'],
        ['id' => 18, 'usia_bulan' => 9, 'jenis' => 'MR/MMR', 'keterangan' => 'Campak & Rubella'],
        ['id' => 19, 'usia_bulan' => 12, 'jenis' => 'PCV Booster', 'keterangan' => 'Dosis lanjutan (12-15 Bulan)'],
        ['id' => 20, 'usia_bulan' => 18, 'jenis' => 'DPT-HB-Hib Booster', 'keterangan' => 'Dosis lanjutan'],
        ['id' => 21, 'usia_bulan' => 18, 'jenis' => 'MR/MMR Booster', 'keterangan' => 'Dosis lanjutan']
    ];
}

function getBalitaAgeInMonths($birthDate) {
    if (empty($birthDate)) return 0;
    $birth = new DateTime($birthDate);
    $now = new DateTime();
    $diff = $birth->diff($now);
    return ($diff->y * 12) + $diff->m;
}

function getVaksinStatus($balita_id, $birthDate, $vaksin_jenis, $target_bulan) {
    $record = fetch_one("SELECT * FROM imunisasi WHERE balita_id = ? AND jenis_imunisasi = ? AND status = 'sudah'", [$balita_id, $vaksin_jenis]);
    if ($record) return 'sudah';

    $ageMonths = getBalitaAgeInMonths($birthDate);

    if ($ageMonths < $target_bulan) return 'belum_waktunya';
    if ($ageMonths == $target_bulan) return 'segera';
    return 'terlambat';
}

function interpolateArray($x, $xPoints, $yPoints) {
    $n = count($xPoints);
    for ($i = 0; $i < $n - 1; $i++) {
        if ($x >= $xPoints[$i] && $x <= $xPoints[$i + 1]) {
            $ratio = ($x - $xPoints[$i]) / ($xPoints[$i + 1] - $xPoints[$i]);
            return $yPoints[$i] + $ratio * ($yPoints[$i + 1] - $yPoints[$i]);
        }
    }
    return end($yPoints);
}

function getWHORef($ageMonths, $gender) {
    $agePoints = [0, 3, 6, 9, 12, 15, 18, 24, 36, 48, 60];
    
    if ($gender === 'L') {
        $bbM = [3.3, 6.4, 7.9, 8.9, 9.6, 10.3, 10.9, 12.2, 14.3, 16.3, 18.3];
        $bbS = [0.4, 0.7, 0.8, 0.9, 1.0, 1.0, 1.1, 1.2, 1.4, 1.7, 2.0];
        $tbM = [49.9, 61.4, 67.6, 72.0, 75.7, 79.1, 82.3, 87.1, 96.1, 103.3, 110.0];
        $tbS = [1.9, 2.4, 2.4, 2.5, 2.6, 2.7, 2.8, 3.0, 3.2, 3.6, 4.0];
        $lkM = [34.5, 40.5, 43.3, 45.0, 46.1, 46.9, 47.5, 48.3, 49.5, 50.2, 50.7];
        $lkS = [1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2];
    } else {
        $bbM = [3.2, 5.8, 7.3, 8.2, 8.9, 9.6, 10.2, 11.5, 13.9, 16.1, 18.2];
        $bbS = [0.4, 0.7, 0.8, 0.9, 1.0, 1.0, 1.1, 1.3, 1.5, 1.8, 2.1];
        $tbM = [49.1, 59.5, 65.7, 70.1, 74.0, 77.5, 80.7, 85.7, 95.1, 102.7, 109.4];
        $tbS = [1.9, 2.3, 2.3, 2.4, 2.6, 2.7, 2.8, 3.0, 3.4, 3.8, 4.2];
        $lkM = [33.9, 39.5, 42.2, 43.8, 45.0, 45.9, 46.6, 47.3, 48.5, 49.3, 49.8];
        $lkS = [1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2, 1.2];
    }
    
    $ageClamped = max(0, min(60, $ageMonths));
    
    return [
        'bb_median' => interpolateArray($ageClamped, $agePoints, $bbM),
        'bb_sd' => interpolateArray($ageClamped, $agePoints, $bbS),
        'tb_median' => interpolateArray($ageClamped, $agePoints, $tbM),
        'tb_sd' => interpolateArray($ageClamped, $agePoints, $tbS),
        'lk_median' => interpolateArray($ageClamped, $agePoints, $lkM),
        'lk_sd' => interpolateArray($ageClamped, $agePoints, $lkS),
    ];
}

function calcZScore($value, $median, $sd) {
    if ($sd == 0) return 0;
    return ($value - $median) / $sd;
}

function getStatusGiziByAge($bb, $tb, $lk, $lila, $ageMonths, $gender = 'L') {
    $ref = getWHORef($ageMonths, $gender);
    $status = [];
    $rekomendasi = [];
    $color = 'Biru';
    
    $bbZ = ($bb > 0) ? calcZScore($bb, $ref['bb_median'], $ref['bb_sd']) : 0;
    $tbZ = ($tb > 0) ? calcZScore($tb, $ref['tb_median'], $ref['tb_sd']) : 0;
    $lkZ = ($lk > 0) ? calcZScore($lk, $ref['lk_median'], $ref['lk_sd']) : 0;
    
    $bbStatus = 'Normal';
    $tbStatus = 'Normal';
    
    if ($bbZ < -3) {
        $status[] = 'Severely Underweight';
        $rekomendasi[] = 'Gizi buruk — perlu intervensi medis segera';
        $color = 'Merah';
        $bbStatus = 'Kurang';
    } elseif ($bbZ < -2) {
        $status[] = 'Underweight';
        $rekomendasi[] = 'Berat badan kurang — perlu peningkatan asupan gizi';
        if ($color !== 'Merah') $color = 'Kuning';
        $bbStatus = 'Kurang';
    } elseif ($bbZ > 2) {
        $status[] = 'Overweight';
        $rekomendasi[] = 'Berat badan berlebih — perlu evaluasi pola makan';
        if ($color !== 'Merah') $color = 'Kuning';
        $bbStatus = 'Berlebih';
    }
    
    if ($tbZ < -3) {
        $status[] = 'Severely Stunted';
        $rekomendasi[] = 'Stunting berat — perlu penanganan intensif';
        $color = 'Merah';
        $tbStatus = 'Kurang';
    } elseif ($tbZ < -2) {
        $status[] = 'Stunted';
        $rekomendasi[] = 'Tinggi badan kurang — risiko stunting';
        if ($color !== 'Merah') $color = 'Kuning';
        $tbStatus = 'Kurang';
    } elseif ($tbZ > 2) {
        $tbStatus = 'Tinggi';
    }
    
    if ($lkZ < -2 && $lk > 0) {
        $status[] = 'Microcephaly Risk';
        $rekomendasi[] = 'Lingkar kepala kecil — konsultasi ke dokter';
        if ($color !== 'Merah') $color = 'Kuning';
    }
    
    if ($lila > 0) {
        $lilaAgeRef = ($ageMonths < 6) ? 11.5 : (($ageMonths < 12) ? 13.0 : (($ageMonths < 24) ? 14.0 : 15.0));
        if ($lila < $lilaAgeRef - 1.5) {
            $status[] = 'Wasting Risk';
            $rekomendasi[] = 'Lingkar lengan kecil — risiko malnutrisi akut';
            if ($color !== 'Merah') $color = 'Kuning';
        }
    }
    
    $statusLabel = !empty($status) ? implode(', ', $status) : 'Normal';
    $rekomendasiLabel = !empty($rekomendasi) ? implode('; ', $rekomendasi) : 'Pertumbuhan dalam batas normal, lanjutkan pola asuh yang baik';
    
    return [
        'status' => $statusLabel,
        'rekomendasi' => $rekomendasiLabel,
        'color' => $color,
        'bb_status' => $bbStatus,
        'tb_status' => $tbStatus,
        'z_scores' => [
            'bb_u' => round($bbZ, 2),
            'tb_u' => round($tbZ, 2),
            'bb_tb' => null,
            'lk_u' => $lk > 0 ? round($lkZ, 2) : null,
            'lila_u' => $lila > 0 ? round($lila, 1) : null,
        ]
    ];
}