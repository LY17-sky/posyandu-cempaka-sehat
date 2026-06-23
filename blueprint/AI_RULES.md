# Aturan Main Pengembangan — AI Agent

**Proyek:** Sistem Informasi Posyandu "Cempaka Sehat"
**Stack:** PHP Native + SQLite + Tailwind CSS (CDN)

---

## 1. Siklus Wajib (Plan → Build → Test → Commit → Push → Verify)

| Langkah | Wajib? | Detail |
|---------|--------|--------|
| **Plan** | ✅ WAJIB | Baca kode terkait, pahami struktur, tentukan pendekatan SEBELUM menulis kode. Jangan asal edit. |
| **Build** | ✅ WAJIB | Implementasi sesuai konvensi proyek. Ikuti pola kode yang sudah ada. |
| **Test Code** | ✅ WAJIB | Jalankan di local, cek error log PHP, pastikan tidak ada syntax error. Test semua role yg relevan. |
| **Git Commit** | ✅ WAJIB | Commit dengan pesan jelas: `[module] aksi: deskripsi singkat` |
| **Git Push** | ✅ WAJIB | Push ke remote. Jangan force push. |
| **Test on GitHub** | ✅ WAJIB | Setelah push, pastikan kode berfungsi. Jika ada CI, pastikan passing. |

> **Catatan:** Langkah boleh diulang (looping) jika test gagal. Jangan skip.

---

## 2. Arsitektur Aplikasi

### 2.1 Routing
- **SPA Shell:** `index.php` dengan parameter `?module=` & `?page=`
- Contoh: `index.php?module=balita&page=daftar`
- API endpoint langsung di `modules/api/` (tidak lewat index.php)
- File export langsung (PDF/Excel) bypass index.php

### 2.2 Hak Akses (3 Role)
| Role | Akses |
|------|-------|
| **super_admin** | Full akses semua pos, bisa switch pos |
| **admin_pos** | CRUD terbatas ke pos masing-masing |
| **user_view** | Read-only, hanya lihat data anak sendiri |

### 2.3 Database
- **SQLite** via PDO (`config/database.php`)
- Auto-inisialisasi schema via `initializeSchema()`
- Gunakan helper: `db()`, `query_db()`, `fetch_all()`, `fetch_one()`, `sanitize()`

### 2.4 Struktur Direktori
```
PROJEK/
├── index.php              # ⚠️ JANGAN diubah tanpa persetujuan
├── login.php              # Halaman login (AJAX)
├── config/database.php    # Koneksi DB singleton
├── modules/
│   ├── api/               # JSON API endpoints
│   ├── balita/            # CRUD balita
│   ├── timbang/           # Penimbangan + WHO Z-score
│   ├── imunisasi/         # Imunisasi + jadwal
│   ├── jadwal/            # Jadwal posyandu
│   ├── laporan/           # Laporan bulanan (Excel/PDF)
│   ├── dashboard/         # Dashboard home
│   ├── konsultasi/        # Konsultasi dengan bidan
│   ├── backup/            # Backup & restore database
│   ├── kartu/             # Cetak KMS
│   ├── settings/          # Profil user
│   └── admin/             # Manajemen pos (super_admin)
├── assets/
│   ├── css/style.css      # Tailwind + custom styling
│   ├── css/sidebar.css    # Sidebar styles
│   └── js/dashboard.js    # UI interaksi
├── blueprint/             # Dokumentasi blueprint
│   ├── blueprint.md
│   ├── laporan_audit.md
│   └── AI_RULES.md        # ⬅️ FILE INI
└── backups/               # Backup SQLite
```

---

## 3. Konvensi Kode

### 3.1 PHP
| Aturan | Keterangan |
|--------|------------|
| **Prepared Statements** | WAJIB untuk semua query database. Jangan pernah concatenate nilai ke SQL. |
| **Output Escaping** | WAJIB `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` untuk semua output |
| **CSRF Protection** | WAJIB di semua form POST. Gunakan token + `hash_equals()` |
| **Error Handling** | try-catch untuk operasi database. Gunakan SweetAlert2 untuk feedback user. |
| **Session** | `session_regenerate_id()` setelah login. `requireLogin()` di setiap halaman. |
| **Password** | `password_hash()` + `password_verify()` (bcrypt) |
| **Indentasi** | 4 spasi. Konsisten. |
| **Short Tags** | Gunakan `<?php`, jangan `<?` |

### 3.2 Frontend
| Aturan | Keterangan |
|--------|------------|
| **CSS** | Tailwind utility classes (CDN). Tambah custom CSS di `style.css` jika perlu. |
| **Dark Mode** | Support dark mode via class + localStorage |
| **Alerts** | SweetAlert2 untuk konfirmasi/alert. Toast untuk notifikasi ringan. |
| **Forms** | Loading state selama submit. CSRF token wajib. |
| **Responsive** | Mobile-first. Test di berbagai ukuran layar. |

### 3.3 API (JSON)
- Format respon: `{"status": "success/error", "message": "...", "data": {...}}`
- Method: GET untuk read, POST untuk write
- Selalu sertakan CSRF token untuk endpoint write
- Gunakan `header('Content-Type: application/json')`

---

## 4. Aturan Ketat (Hard Rules — JANGAN DILANGGAR)

### ❌ DILARANG
1. **DILARANG** edit `database.sqlite` langsung — gunakan migration atau schema di `database.php`
2. **DILARANG** hapus/modifikasi CSRF protection
3. **DILARANG** gunakan `mysql_*`, `mysqli_*` — hanya PDO
4. **DILARANG** push tanpa testing local
5. **DILARANG** force push ke main/master
6. **DILARANG** ubah routing di `index.php` tanpa pemahaman penuh
7. **DILARANG** simpan credential asli di kode (API key, dll)
8. **DILARANG** hapus `.htaccess` yang sudah ada
9. **DILARANG** tambah dependency baru (CDN/library) tanpa konfirmasi
10. **DILARANG** ubah struktur database tanpa update `blueprint.md`

### ✅ WAJIB
1. **WAJIB** test dengan minimal 2 role berbeda
2. **WAJIB** cek error log PHP (`php -l file.php`) sebelum commit
3. **WAJIB** pastikan dark mode tidak rusak
4. **WAJIB** update `blueprint/` jika ada perubahan signifikan
5. **WAJIB** gunakan prepared statements untuk query SQL
6. **WAJIB** escape output dengan `htmlspecialchars()`
7. **WAJIB** beri `requireLogin()` di awal setiap halaman module

---

## 5. Prosedur Git

### 5.1 Commit Message Format
```
[module] aksi: deskripsi singkat

Contoh:
[balita] fix: perbaiki format tanggal lahir di detail balita
[timbang] add: tambah filter tanggal di riwayat timbang
[imunisasi] refactor: pisahkan logika jadwal dari controller
```

### 5.2 Prosedur Commit
```bash
git status                                # Cek file yang berubah
git diff                                  # Review perubahan
git add <file>                            # Stage file satu per satu
git commit -m "[module] aksi: deskripsi"  # Commit dengan pesan jelas
git push origin <branch>                  # Push
```

### 5.3 Branching
- `main` — production-ready. Hanya merge dari branch fitur.
- `feat/<nama-fitur>` — fitur baru
- `fix/<nama-bug>` — perbaikan bug
- `refactor/<deskripsi>` — refactoring

---

## 6. Testing Guidelines

### 6.1 Sebelum Commit
```bash
# Cek syntax error semua file PHP
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }

# Cek apakah SQLite masih jalan
php -r "require 'config/database.php'; echo 'DB OK';"
```

### 6.2 Test Checklist
- [ ] Syntax check: `php -l` tidak ada error
- [ ] Database: koneksi SQLite berhasil
- [ ] Login: semua role bisa login
- [ ] Fitur: sesuai spesifikasi task
- [ ] CRUD: create, read, update, delete berfungsi
- [ ] Validasi: input invalid ditangani dengan baik
- [ ] CSRF: token ada dan valid
- [ ] XSS: output tidak mentah (htmlspecialchars)
- [ ] Dark mode: tampilan tetap rapi
- [ ] Responsive: di layar mobile & desktop

---

## 7. Catatan Penting

- Proyek ini **tidak menggunakan Composer/npm** — semua dependency via CDN
- **SQLite** auto-initialize — tidak perlu setup manual
- WHO Z-score calculator ada di modul `timbang/`
- Notifikasi WhatsApp via **Fonnte API** (placeholder — jangan aktifkan tanpa API key real)
- Jika ragu dengan struktur kode, lihat blueprint di `blueprint/blueprint.md`

---

## 8. Perintah Cepat untuk AI Agent

| Situasi | Perintah |
|---------|----------|
| Memulai task baru | Baca dulu `blueprint/AI_RULES.md` dan `blueprint/blueprint.md` |
| Ingin pahami modul | Baca semua file di `modules/<nama>/` |
| Ingin test | `php -l file.php` lalu buka di browser |
| Commit | Gunakan format `[module] aksi: deskripsi` |
| Ragu dengan konvensi | Lihat kode yang sudah ada di modul serupa |
