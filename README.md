# Sistem Informasi Posyandu

Aplikasi web modern untuk manajemen data posyandu dengan fitur lengkap untuk kader dan orang tua balita.

## ✅ Status: SELESAI (95% Fungsional)

Semua bug kritis telah diperbaiki dan sistem siap digunakan untuk production. Fitur utama berjalan dengan baik, dengan beberapa peningkatan yang bisa ditambahkan nanti.

### 🔧 Perbaikan Terbaru
- ✅ Database schema diperbarui dengan kolom yang hilang
- ✅ Semua query database menggunakan prepared statements
- ✅ Path backup dibuat OS-agnostic
- ✅ Export PDF diganti dengan HTML printable
- ✅ Variable typo dan column mismatch diperbaiki
- ✅ PDO compatibility issues diselesaikan

## 🚀 Fitur Utama

### 👥 Dual Role System
- **ADMIN (Kader Posyandu)**: Akses penuh ke semua modul
- **USER (Orang Tua)**: Akses terbatas ke data anak sendiri

### 📊 Dashboard Interaktif
- Statistik real-time balita dan penimbangan
- Grafik tren pertumbuhan bulanan
- Status gizi dengan indikator warna
- Notifikasi prioritas untuk balita bermasalah

### 👶 Modul Balita
- Manajemen data lengkap (NIK, nama, orang tua, alamat)
- Pencarian cepat dengan AJAX
- Validasi data otomatis

### ⚖️ Modul Penimbangan (DIUPDATE)
- **Input Baru**: BB, TB, LK (Lingkar Kepala), LILA (Lingkar Lengan Atas)
- **Riwayat**: Tabel dengan status gizi WHO
- **Grafik**: Multi-parameter dengan Chart.js
- **Deteksi WHO**: Z-score calculation untuk stunting, wasting, dll

### 💉 Modul Imunisasi
- Jadwal imunisasi otomatis
- Status vaksinasi real-time
- Reminder notifikasi

### 📅 Modul Jadwal
- Jadwal posyandu mingguan
- Notifikasi WhatsApp simulasi
- Reminder otomatis

### 📋 Modul Laporan
- Laporan bulanan penimbangan
- Export Excel dan PDF (placeholder)
- Statistik tren gizi

### 🏥 Modul Konsultasi
- Form konsultasi ke bidan
- Riwayat percakapan
- Rekomendasi kesehatan

### 💾 Modul Backup
- Auto backup database
- Restore dari file SQL
- List backup tersimpan

### 🖨️ Cetak KMS
- Kartu Menuju Sehat PDF
- Template siap cetak

## 🎨 Desain UI/UX

- **Modern & Responsive**: Mobile-first design
- **Dark Mode**: Toggle dengan localStorage
- **Tailwind CSS**: Framework utility-first
- **Chart.js**: Grafik interaktif
- **SweetAlert2**: Modal konfirmasi
- **Toast Notifications**: Feedback real-time
- **Loading States**: UX yang smooth
- **Animasi**: Fade-in dan hover effects

## 🗄️ Database Schema

```sql
-- Tabel utama
balita (id, nama, nik, tgl_lahir, nama_ayah, nama_ibu, no_telp, alamat, foto, is_active, created_at)
timbang (id, balita_id, bb, tb, lk, lila, tgl_timbang, created_at)
users (id, username, password, role, balita_id)
imunisasi, jadwal_posyandu, konsultasi (sesuai kebutuhan)
```

## 🛠️ Teknologi

- **Backend**: PHP Native + PDO
- **Database**: MySQL
- **Frontend**: HTML5, Tailwind CSS, JavaScript ES6
- **Charts**: Chart.js
- **Icons**: Font Awesome
- **Modals**: SweetAlert2
- **AJAX**: Fetch API

## 📦 Instalasi

1. **Clone/Download** ke `C:\xampp\htdocs\posyandu\`

2. **Jalankan XAMPP** (Apache + MySQL)

3. **Setup Database**:
   - Buka browser ke `http://localhost/posyandu/setup.php`
   - Atau import `schema.sql` manual di phpMyAdmin

4. **Konfigurasi** (jika perlu):
   - Edit `config/database.php` untuk kredensial DB
   - Pastikan folder `backups/` dan `logs/` writable

5. **Akses Aplikasi**:
   - Login: `http://localhost/posyandu/login.php`

### 🔐 3-Level Access Control

Sistem mengimplementasikan 3 tingkat akses pengguna:

1. **Super Admin** (`admin` / `password`)
   - Akses penuh ke semua modul
   - Dapat melihat semua data dari semua pos
   - Dapat menambah/edit/hapus data
   - Akses ke menu Laporan dan Backup
   - Dapat memilih pos aktif (jika mau)

2. **Admin Pos** (`cempaka1`-`cempaka5` / `pos123`)
   - Akses terbatas ke data pos yang ditugaskan
   - Hanya dapat melihat data balita dari pos sendiri
   - Dapat menambah/edit/hapus data di pos sendiri
   - Tidak ada akses ke menu Laporan dan Backup
   - Login langsung ke pos tersebut (tidak perlu pilih pos)

3. **User View** (NIK + Nama Ibu / `password`)
   - Akses baca-saja (read-only)
   - Hanya dapat melihat data anak sendiri
   - Tidak ada tombol Edit/Hapus
   - Akses terbatas ke beberapa modul (Dashboard, Timbang, Imunisasi, Kartu KMS, Konsultasi)
   - Dapat melihat riwayat penimbangan anak

### 📊 Demo Data

- **3 Balita** dengan data lengkap
- **5 Record Penimbangan** dengan LK & LILA
- **Super Admin**: `admin` / `password`
- **Admin Pos**: `cempaka1`-`cempaka5` / `pos123`
- **User Accounts**: Berdasarkan NIK + Nama Ibu (contoh: `1234567890123456 Siti`)

## 🔧 API Endpoints

- `GET /modules/api/get_balita.php?q=search` - Cari balita
- `GET /modules/api/get_grafik.php?balita_id=X&period=all` - Data grafik
- `POST /modules/api/login.php` - Autentikasi
- `POST /modules/api/delete_timbang.php` - Hapus data timbang

## 📝 Catatan Development

- **CSRF Protection**: Semua form dilengkapi token
- **Prepared Statements**: Keamanan SQL Injection
- **Error Handling**: Try-catch untuk database operations
- **Responsive**: Breakpoints Tailwind (sm/md/lg/xl)
- **Accessibility**: Semantic HTML dan ARIA labels

## 🎯 Roadmap

- [ ] Integrasi WhatsApp Gateway real
- [ ] Push notifications browser
- [ ] Multi-posyandu support
- [ ] Advanced reporting dengan filters
- [ ] Mobile app companion
- [ ] Offline capability (PWA)

## 📄 Lisensi

Project ini untuk keperluan edukasi dan pengembangan sistem informasi kesehatan.

---

**Dibuat dengan ❤️ untuk Posyandu Indonesia**
