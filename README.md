# Sistem Informasi Posyandu — Cempaka Sehat

Aplikasi web modern untuk manajemen data posyandu dengan fitur lengkap untuk kader, admin pos, dan orang tua balita.

## ✅ Status: SELESAI (100% Fungsional)

Seluruh fitur telah selesai diimplementasikan dan sistem siap digunakan. Semua bug kritis telah diperbaiki.

### 🔧 Fitur yang Ditambahkan
- ✅ Migrasi database MySQL → **SQLite** (tanpa perlu XAMPP/MySQL)
- ✅ Database schema diperbarui dengan kolom yang hilang
- ✅ Semua query menggunakan prepared statements
- ✅ Path backup OS-agnostic
- ✅ Export PDF diganti dengan HTML printable
- ✅ Variable typo & column mismatch diperbaiki
- ✅ PDO compatibility issues diselesaikan
- ✅ 3-level access control (super_admin, admin_pos, user_view)
- ✅ Multi-posyandu support (Cempaka I–V)
- ✅ **User Management** — CRUD user via UI (Super Admin)
- ✅ **Konfigurasi Fonnte API** — Atur API key WhatsApp dari halaman admin
- ✅ **Ganti Password** — Fitur ubah password untuk semua role
- ✅ **Scripts Automation** — Cron job untuk backup & reminder WhatsApp

## 🚀 Fitur Utama

### 👥 Triple Role System
- **Super Admin** — Akses penuh ke semua modul & semua pos
- **Admin Pos** — Akses terbatas ke data pos masing-masing
- **User View (Orang Tua)** — Akses baca-saja ke data anak sendiri

### 📊 Dashboard Interaktif
- Statistik real-time balita dan penimbangan
- Grafik tren pertumbuhan bulanan
- Status gizi dengan indikator warna (Merah/Kuning/Biru)
- Notifikasi prioritas untuk balita bermasalah

### 👶 Modul Balita
- Manajemen data lengkap (NIK, nama, orang tua, alamat)
- Pencarian cepat
- Validasi data otomatis

### ⚖️ Modul Penimbangan
- Input: BB, TB, LK (Lingkar Kepala), LILA (Lingkar Lengan Atas)
- Riwayat dengan status gizi WHO (Z-score)
- Grafik multi-parameter
- Deteksi stunting, wasting, underweight, overweight

### 💉 Modul Imunisasi
- Jadwal imunisasi otomatis berdasarkan usia
- Status vaksinasi real-time (sudah/belum/segera/terlambat)
- 21 jenis vaksin sesuai jadwal IDAI

### 📅 Modul Jadwal Posyandu
- Jadwal kegiatan posyandu
- Multi-pos support

### 📋 Modul Laporan
- Laporan bulanan penimbangan
- Export Excel
- Cetak HTML printable
- Statistik tren gizi

### 🏥 Modul Konsultasi
- Form konsultasi ke bidan
- Riwayat percakapan
- Status pending/dijawab

### 💾 Modul Backup
- Auto backup database SQLite
- Restore dari file backup
- Riwayat backup tersimpan

### 🖨️ Cetak KMS
- Kartu Menuju Sehat
- Template siap cetak (HTML printable)

### ⚙️ Pengaturan
- Kelola Pos Cempaka (super_admin)
- Ubah password profil (semua role)

## 🎨 Desain UI/UX
- **Modern & Responsive**: Mobile-first dengan Tailwind CSS
- **Dark Mode**: Toggle dengan localStorage
- **Chart.js**: Grafik interaktif
- **SweetAlert2**: Modal & konfirmasi
- **Fetch API**: AJAX tanpa jQuery

## 🗄️ Database Schema (SQLite)

- **balita** — Data balita (`id`, `nama`, `nik`, `tgl_lahir`, `nama_ayah`, `nama_ibu`, `nik_ibu`, `no_telp`, `alamat`, `jenis_kelamin`, `bb_lahir`, `tb_lahir`, `id_pos`, `is_active`)
- **timbang** — Riwayat penimbangan (`id`, `balita_id`, `bb`, `tb`, `lk`, `lila`, `tgl_timbang`)
- **users** — Akun pengguna (`id`, `username`, `password`, `role`, `no_telp`, `id_pos`, `balita_id`)
- **imunisasi** — Riwayat imunisasi (`id`, `balita_id`, `jenis_imunisasi`, `tgl_imunisasi`, `status`)
- **jadwal_posyandu** — Jadwal kegiatan (`id`, `tanggal`, `lokasi`, `waktu`, `catatan`)
- **konsultasi** — Konsultasi bidan (`id`, `balita_id`, `pertanyaan`, `jawaban`, `bidan_id`, `status`)
- **pos_cempaka** — Data posyandu (`id`, `nama`, `lokasi`, `kontak`)
- **notifications** — Log notifikasi (`id`, `tujuan`, `pesan`, `status`)
- **backup_log** — Riwayat backup (`id`, `file_name`, `created_at`)

## 🛠️ Teknologi

- **Backend**: PHP Native 8+ dengan PDO
- **Database**: SQLite
- **Frontend**: HTML5, Tailwind CSS, JavaScript ES6
- **Charts**: Chart.js
- **Icons**: Google Material Icons
- **Modals**: SweetAlert2
- **AJAX**: Fetch API
- **CI/CD**: GitHub Actions (PHP lint + smoke test DB)

## 📦 Instalasi

### Persyaratan
- PHP 8.0 atau lebih baru (dengan ekstensi `pdo_sqlite` dan `sqlite3`)
- Web server (Apache / Nginx / PHP built-in server)

### Langkah Instalasi
1. **Clone/download** ke direktori web server
2. **Konfigurasi database** — Tidak perlu! SQLite otomatis terinisialisasi saat pertama kali diakses oleh `config/database.php`
3. **Akses aplikasi** — Buka `login.php` di browser

### PHP Built-in Server (untuk development)
```bash
cd posyandu
php -S localhost:8000
```
Buka `http://localhost:8000/login.php`

## 🔐 Akun Demo

### Super Admin
| Username | Password | Role |
|----------|----------|------|
| `admin` | `password` | Akses penuh semua pos |

### Admin Pos
| Username | Password | Pos |
|----------|----------|-----|
| `cempaka1` | `pos123` | Cempaka I |
| `cempaka2` | `pos123` | Cempaka II |
| `cempaka3` | `pos123` | Cempaka III |
| `cempaka4` | `pos123` | Cempaka IV |
| `cempaka5` | `pos123` | Cempaka V |

### User View (Orang Tua)
Login menggunakan NIK Ibu sebagai username:
| NIK Ibu | Password | Data Anak |
|---------|----------|-----------|
| `1234567890123456` | `password` | Ahmad Rahman |
| `1234567890123457` | `password` | Fatimah Sari |
| `1234567890123458` | `password` | Budi Santoso |

## 🔧 API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `modules/api/get_balita.php?q=` | Cari balita |
| `GET` | `modules/api/get_grafik.php?balita_id=&period=` | Data grafik |
| `GET` | `modules/api/get_timbang.php?id=` | Ambil data timbang |
| `POST` | `modules/api/login.php` | Autentikasi |
| `POST` | `modules/api/edit_timbang.php` | Edit data timbang |
| `POST` | `modules/api/delete_timbang.php` | Hapus data timbang |

## 🤖 Automation Scripts

### Backup Otomatis (Cron Job)
```bash
# Backup database setiap jam 2 pagi
0 2 * * * /usr/bin/php /path/to/project/scripts/backup_cron.php
```

### Reminder WhatsApp Otomatis
```bash
# Kirim reminder jadwal posyandu setiap jam
0 * * * * /usr/bin/php /path/to/project/scripts/send_reminders.php
```

> **Catatan:** Pastikan API Key Fonnte sudah dikonfigurasi di halaman **Administrasi → Konfigurasi Fonnte** agar notifikasi WhatsApp berfungsi.

## 📝 Catatan Development
- **CSRF Protection**: Semua form dilengkapi token
- **Prepared Statements**: Aman dari SQL Injection
- **Error Handling**: Try-catch untuk semua operasi database
- **Responsive**: Breakpoints Tailwind (sm/md/lg/xl)
- **GitHub Actions**: CI otomatis (lint PHP + test database)

---
**Dibuat oleh:**

**Nur Istiqlaliyah** — 101230045 — TF23B

**Dengan ❤️ untuk Posyandu Indonesia**
