# 📚 Dokumentasi Aplikasi "Verval Rekam Didik Jenjang Sebelum MTsN 11 Majalengka"

## 🎯 Daftar Isi
1. [Pengenalan](#pengenalan)
2. [Persyaratan Sistem](#persyaratan-sistem)
3. [Instalasi](#instalasi)
4. [Konfigurasi Database](#konfigurasi-database)
5. [Struktur Folder](#struktur-folder)
6. [Penggunaan Aplikasi](#penggunaan-aplikasi)
7. [API Documentation](#api-documentation)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Pengenalan

Aplikasi "Verval Rekam Didik Jenjang Sebelum MTsN 11 Majalengka" adalah sistem informasi yang dirancang untuk memverifikasi data pendidikan sebelumnya (jenjang SD) siswa yang masuk ke MTsN 11 Majalengka.

### Fitur Utama:
- ✅ Form input NISN untuk siswa
- ✅ Tampilan status verval (belum/sudah)
- ✅ Kelola data siswa dari KK dan Ijazah
- ✅ Upload dokumen ijazah (JPG, JPEG, PNG)
- ✅ Riwayat perbaikan data
- ✅ Dashboard admin untuk monitoring
- ✅ Sistem verifikasi field per field

---

## 🔧 Persyaratan Sistem

### Minimum Requirements:
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi (atau MariaDB 10.2+)
- Web Server (Apache atau Nginx)
- Browser modern (Chrome, Firefox, Edge, Safari)

### Recommended:
- PHP 8.0+
- MySQL 8.0+
- Apache 2.4+ dengan modul rewrite enabled
- 100 MB storage untuk upload file

---

## 📦 Instalasi

### Step 1: Download/Extract File
```bash
# Copy semua file ke direktori web server
# Contoh: D:\BACKUP HOSTING\redik.mtsn11majalengka.sch.id\rekamdidik
```

### Step 2: Buat Database
```sql
-- Buka MySQL/MariaDB console dan jalankan command di bawah:

-- 1. Menggunakan SQL file yang sudah disiapkan:
-- Buka file: config/schema.sql dan copy-paste ke MySQL console

-- Atau secara manual:

-- Buat database baru
CREATE DATABASE IF NOT EXISTS rekamdidik_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rekamdidik_db;

-- Jalankan schema dari file config/schema.sql
```

### Step 3: Konfigurasi Database
Edit file `config/Database.php` dan sesuaikan kredensial:

```php
private $host = 'localhost';      // Host database
private $db_name = 'rekamdidik_db'; // Nama database
private $user = 'root';           // Username database
private $password = '';           // Password database (kosong jika default)
```

### Step 4: Pastikan Folder Upload
```bash
# Folder uploads/ijazah harus memiliki permission untuk write
# Verifikasi permission:
# Linux/Mac: chmod 755 uploads/ijazah/
# Windows: Folder harus bisa ditulis oleh application user
```

### Step 5: Akses Aplikasi
```
# Siswa (Verval):
http://localhost/rekamdidik/

# Admin Dashboard:
http://localhost/rekamdidik/admin/
```

---

## 🗄️ Konfigurasi Database

### Tabel Utama:

#### 1. Tabel `siswa`
Menyimpan data siswa dengan verifikasi field

```
Columns:
- id (INT) - Primary Key
- nisn (VARCHAR) - NISN siswa (UNIQUE)
- nik_kk, nama_kk, tempat_lahir_kk, tanggal_lahir_kk, jenis_kelamin_kk, nama_ibu_kk, nama_ayah_kk - Data dari KK
- nama_ijazah, tempat_lahir_ijazah, tanggal_lahir_ijazah, jenis_kelamin_ijazah, nama_ayah_ijazah - Data dari Ijazah
- verval_status (ENUM: belum/sudah) - Status verval
- [field]_verified (BOOLEAN) - Checkbox verifikasi setiap field
- created_at, updated_at (TIMESTAMP)
```

#### 2. Tabel `verval_jenjang_sebelumnya`
Menyimpan data verval jenjang sebelumnya (SD)

```
Columns:
- id (INT) - Primary Key
- siswa_id (INT) - Reference ke tabel siswa
- nama_sd (VARCHAR) - Nama SD
- tahun_ajaran_kelulusan (VARCHAR)
- nip_kepala_sekolah, nama_kepala_sekolah (VARCHAR)
- nomor_seri_ijazah (VARCHAR)
- tanggal_terbit_ijazah (DATE)
- dokumen_ijazah (VARCHAR) - Nama file upload
- created_at, updated_at (TIMESTAMP)
```

#### 3. Tabel `history_perbaikan`
Menyimpan riwayat perbaikan data

```
Columns:
- id (INT) - Primary Key
- siswa_id (INT) - Reference ke tabel siswa
- field_name (VARCHAR) - Nama field yang diperbaiki
- nilai_sebelum (TEXT) - Nilai sebelum perbaikan
- nilai_sesudah (TEXT) - Nilai setelah perbaikan
- tanggal_perbaikan (TIMESTAMP)
```

#### 4. Tabel `admin_users`
Menyimpan data user admin

```
Columns:
- id (INT) - Primary Key
- username (VARCHAR) - Login username (UNIQUE)
- password (VARCHAR) - Password (MD5 hashed)
- email (VARCHAR)
- nama_lengkap (VARCHAR)
- role (ENUM: admin/operator)
- status (ENUM: aktif/nonaktif)
- created_at, updated_at (TIMESTAMP)

Default Admin:
Username: admin
Password: admin123
```

---

## 📁 Struktur Folder

```
rekamdidik/
├── index.php                 # Halaman utama (Verval)
├── config/
│   ├── Database.php         # Class koneksi database
│   └── schema.sql           # SQL schema database
├── api/
│   ├── check-nisn.php       # API cek NISN & ambil data
│   ├── update-siswa.php     # API update data siswa
│   ├── update-verval.php    # API simpan verval
│   └── admin/
│       ├── list-siswa.php   # API list data siswa
│       └── detail-siswa.php # API detail siswa + history
├── admin/
│   └── index.php            # Dashboard admin
├── assets/
│   ├── css/
│   │   └── style.css        # Stylesheet
│   └── js/
│       └── utils.js         # Utility functions JavaScript
├── uploads/
│   └── ijazah/              # Folder upload dokumen ijazah
│       └── [file uploads]
└── README.md                # Dokumentasi ini
```

---

## 🎮 Penggunaan Aplikasi

### A. Untuk Siswa

#### 1. Halaman Verval (index.php)
```
URL: http://[domain]/rekamdidik/

Step:
1. Masukkan NISN (10 digit)
2. Klik "Periksa Status"
3. Sistem akan menampilkan:
   - Status verval (belum/sudah)
   - Data siswa dari KK
   - Data siswa dari Ijazah
   - Form verval jenjang sebelumnya
   - Riwayat perbaikan (jika ada)
```

#### 2. Bagian A: Data Siswa
```
- Field dengan checkbox "Sesuai KK" atau "Sesuai Ijazah"
- Jika belum diceklis: field bisa diedit
- Jika sudah diceklis: field disabled (tidak bisa diedit)
- Setiap edit akan disimpan otomatis dan tercatat di history
```

#### 3. Bagian B: Verval Jenjang Sebelumnya
```
Input yang harus diisi:
- Nama Sekolah Dasar (SD)
- Tahun Ajaran Kelulusan (format: 2020/2021)
- NIP Kepala Sekolah pada Ijazah
- Nama Kepala Sekolah pada Ijazah
- Nomor Seri Ijazah
- Tanggal Terbit Ijazah
- Upload Dokumen Ijazah (JPG, JPEG, PNG, max 1MB)

Setelah submit:
- Data disimpan ke tabel verval_jenjang_sebelumnya
- Status siswa berubah menjadi "Sudah Verval"
- File ijazah disimpan di folder uploads/ijazah/
```

### B. Untuk Admin

#### 1. Halaman Admin Dashboard (admin/index.php)
```
URL: http://[domain]/rekamdidik/admin/

Fitur:
- Tabel daftar seluruh siswa
- Filter berdasarkan nama/NISN
- Filter berdasarkan status verval
- Lihat detail siswa lengkap
- Tampilkan file ijazah
- Lihat riwayat perbaikan
```

#### 2. Fungsi Search & Filter
```
- Search box: Cari berdasarkan NISN, nama KK, nama Ijazah
- Status filter: Belum Verval / Sudah Verval / Semua
- Pagination: 10 data per halaman
```

#### 3. View Detail Siswa
```
Menampilkan:
- Data dasar siswa
- Semua data dari KK dengan status verifikasi
- Semua data dari Ijazah dengan status verifikasi
- Data verval jenjang sebelumnya (jika ada)
- Riwayat perbaikan lengkap (sebelum & sesudah)
- Link download dokumen ijazah
```

---

## 📡 API Documentation

### 1. Check NISN
```
Endpoint: POST /api/check-nisn.php
Input: { "nisn": "0123456789" }

Response Success:
{
    "success": true,
    "message": "Data siswa ditemukan",
    "data": {
        "id": 1,
        "nisn": "0123456789",
        "nik_kk": "3273050101010001",
        "nama_kk": "BUDI SANTOSO",
        ...
        "verval_status": "belum",
        "verval_data": {...} atau null,
        "history_perbaikan": [...]
    }
}

Response Error:
{
    "success": false,
    "message": "NISN tidak ditemukan dalam database"
}
```

### 2. Update Data Siswa
```
Endpoint: POST /api/update-siswa.php
Input: {
    "siswa_id": 1,
    "field_name": "nik_kk",
    "nilai_baru": "3273050101010001"
}

Response Success:
{
    "success": true,
    "message": "Data berhasil diupdate"
}

Response Error:
{
    "success": false,
    "message": "Field ini sudah diceklis dan tidak bisa diubah"
}
```

### 3. Update Verval Data
```
Endpoint: POST /api/update-verval.php
Method: POST (multipart/form-data)

Input:
- siswa_id (hidden)
- nama_sd (text)
- tahun_ajaran_kelulusan (text)
- nip_kepala_sekolah (text)
- nama_kepala_sekolah (text)
- nomor_seri_ijazah (text)
- tanggal_terbit_ijazah (date)
- dokumen_ijazah (file)

Response Success:
{
    "success": true,
    "message": "Data verval berhasil disimpan"
}
```

### 4. List Siswa (Admin)
```
Endpoint: GET /api/admin/list-siswa.php
Parameters:
- page (int) = 1
- limit (int) = 10
- search (string) = ""
- status (string) = ""

Response:
{
    "success": true,
    "message": "Data siswa berhasil diambil",
    "data": [...],
    "pagination": {
        "page": 1,
        "limit": 10,
        "total": 100,
        "total_pages": 10
    }
}
```

### 5. Detail Siswa (Admin)
```
Endpoint: GET /api/admin/detail-siswa.php?id=1

Response:
{
    "success": true,
    "message": "Data detail siswa berhasil diambil",
    "data": {
        ...data lengkap siswa...,
        "verval_data": {...},
        "history_perbaikan": [...]
    }
}
```

---

## 🐛 Troubleshooting

### Problem: "Connection Error" saat cek NISN
**Solution:**
1. Pastikan MySQL/MariaDB sudah running
2. Check kredensial di config/Database.php
3. Verifikasi database sudah dibuat

### Problem: File upload gagal dengan pesan "Gagal upload file"
**Solution:**
1. Check folder `uploads/ijazah/` memiliki write permission
2. Verifikasi file size < 1MB
3. Format file harus JPG, JPEG, atau PNG
4. Check disk space

### Problem: Data tidak muncul di admin dashboard
**Solution:**
1. Klik tombol "Cari" untuk load data
2. Pastikan ada data siswa di database
3. Check browser console untuk error messages

### Problem: Field tidak bisa diedit
**Solution:**
- Jika ada checkbox "Sesuai KK/Ijazah" yang sudah dicentang, field tersebut disabled
- Admin harus unchecklist di database jika perlu edit ulang
- Query: `UPDATE siswa SET [field]_verified = 0 WHERE id = [id];`

### Problem: White screen atau error 500
**Solution:**
1. Check file php.ini apakah display_errors = On
2. Check error logs di PHP/Apache
3. Pastikan extension mysqli di-enable di PHP
4. Verifikasi syntax PHP tidak ada error (gunakan PHP linter)

### Problem: Ijazah tidak bisa didownload dari admin
**Solution:**
1. Verifikasi nama file di database matches dengan file di folder
2. Check permission folder uploads/ijazah/
3. Pastikan file path benar di HTML link

---

## 🔐 Security Notes

### Password Hashing
- Default admin password di database menggunakan MD5 hashing
- **Recommendation:** Change password ke yang lebih aman dengan bcrypt
- **Change password query:**
```sql
UPDATE admin_users SET password = MD5('password_baru') WHERE username = 'admin';
```

### File Upload Security
- File upload hanya menerima JPG, JPEG, PNG
- File size maksimal 1MB
- File disimpan dengan nama random (ijazah_[siswa_id]_[timestamp].[ext])
- Pastikan folder uploads tidak accessible langsung dari web (setup via .htaccess atau web server config)

### Input Validation
- Semua input dari user divalidasi dan di-escape
- NISN harus 10 digit angka
- Prepared statements digunakan untuk prevent SQL injection

---

## 📞 Support & Maintenance

Untuk masalah atau feature request, hubungi:
- **Email:** admin@mtsn11.sch.id
- **Phone:** [Nomor Support]

---

## 📝 Changelog

### v1.0.0 - Initial Release
- ✅ Form input NISN
- ✅ Display status verval
- ✅ Kelola data siswa KK & Ijazah
- ✅ Upload dokumen ijazah
- ✅ Admin dashboard
- ✅ Riwayat perbaikan

---

## 📄 License
Aplikasi ini dibuat untuk MTsN 11 Majalengka. Penggunaan, modifikasi, dan distribusi harus dengan izin dari sekolah.

---

**Last Updated:** February 23, 2024
**Version:** 1.0.0
