CREATE DATABASE IF NOT EXISTS `posyandu`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE `posyandu`;

CREATE TABLE IF NOT EXISTS balita (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100),
    nik VARCHAR(16) UNIQUE,
    tgl_lahir DATE,
    nama_ayah VARCHAR(100),
    nama_ibu VARCHAR(100),
    no_telp VARCHAR(15),
    alamat TEXT,
    foto VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    jenis_kelamin ENUM('L','P') DEFAULT 'L',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS timbang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    balita_id INT,
    bb DECIMAL(5,2),
    tb DECIMAL(5,2),
    lk DECIMAL(5,2),
    lila DECIMAL(5,2),
    tgl_timbang DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (balita_id) REFERENCES balita(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    balita_id INT NULL,
    FOREIGN KEY (balita_id) REFERENCES balita(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS imunisasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    balita_id INT,
    jenis_imunisasi VARCHAR(100),
    tgl_imunisasi DATE,
    status ENUM('belum', 'sudah') DEFAULT 'belum',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (balita_id) REFERENCES balita(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jadwal_posyandu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE,
    lokasi VARCHAR(255),
    waktu TIME,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS konsultasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    balita_id INT,
    nama_pengirim VARCHAR(100),
    pertanyaan TEXT,
    jawaban TEXT,
    bidan_id INT,
    tgl_konsultasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (balita_id) REFERENCES balita(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS backup_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tujuan VARCHAR(255) NOT NULL,
    pesan TEXT NOT NULL,
    status ENUM('pending','sent','failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO balita (nama, nik, tgl_lahir, nama_ayah, nama_ibu, no_telp, alamat) VALUES
('Ahmad Rahman', '1234567890123456', '2020-01-15', 'Rahman', 'Siti', '081234567890', 'Jl. Sudirman No. 1'),
('Fatimah Sari', '1234567890123457', '2019-05-20', 'Sari', 'Maya', '081234567891', 'Jl. Thamrin No. 2'),
('Budi Santoso', '1234567890123458', '2021-03-10', 'Santoso', 'Ani', '081234567892', 'Jl. Gajah Mada No. 3');

INSERT INTO timbang (balita_id, bb, tb, lk, lila, tgl_timbang) VALUES
(1, 8.5, 70.0, 42.0, 12.5, '2023-01-15'),
(1, 9.2, 72.5, 43.2, 13.0, '2023-02-15'),
(1, 9.8, 75.0, 44.0, 13.5, '2023-03-15'),
(2, 7.8, 68.0, 41.5, 12.0, '2023-01-20'),
(2, 8.3, 70.5, 42.5, 12.8, '2023-02-20'),
(3, 9.0, 71.0, 42.8, 13.2, '2023-03-10');

INSERT INTO users (username, password, role, balita_id) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL),
('1234567890123456 Siti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1),
('1234567890123457 Maya', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 2),
('1234567890123458 Ani', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 3);
