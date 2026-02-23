# 📋 FITUR VERVAL KONFIRMASI - Dokumentasi

> Sistem persetujuan dan konfirmasi data verval per field untuk MTSN 11 Majalengka

---

## 🎯 OVERVIEW

Sistem ini memungkinkan admin untuk **review dan approve setiap field data siswa** (19 fields) dengan 4 opsi tindakan berbeda, serta memungkinkan siswa untuk merespons dengan cara yang sesuai.

### Fitur Utama:
1. ✅ **Admin Review** - Review 19 field data verval per siswa
2. 📄 **Request Berkas** - Admin minta berkas pendukung
3. 💬 **Request Konfirmasi** - Admin minta penjelasan tanpa berkas
4. ✏️ **Request Edit Data** - Admin minta edit data + berkas + penjelasan
5. 📸 **Re-upload Ijazah** - Admin minta upload ulang jika blur/terpotong
6. 📊 **History Tracking** - Track semua perubahan data

---

## 📦 DATABASE

### Tabel Baru (4):
1. **`verval_konfirmasi`** - Tracking konfirmasi per field per siswa
2. **`ijazah_reupload_request`** - Request re-upload ijazah
3. **`verval_history`** - History perubahan data
4. **`siswa`** (ALTER) - Tambah kolom `verval_approval_status` & `catatan_konfirmasi`

### Status Flow:
```
pending → need_document/need_confirmation/need_edit → student_responded → approved
```

### Setup Database:
```sql
-- Di phpMyAdmin, jalankan:
config/verval_konfirmasi.sql
```

---

## 🚀 CARA MENGGUNAKAN

### A. Untuk Admin

#### 1. Review Verval Siswa
1. Login ke `admin/index.php`
2. Klik tombol **"Review Verval"** pada siswa yang `status_verval = "sudah_verval"`
3. Modal review terbuka menampilkan 19 fields dengan status masing-masing

#### 2. Konfirmasi Per Field (4 Opsi)

**Opsi 1: ✓ Setujui**
- Untuk field yang sudah benar
- Tidak perlu action dari siswa
- Status → `approved`

**Opsi 2: 📄 Minta Berkas**
- Untuk field yang perlu bukti dokumen
- Contoh: "Upload KK sebagai bukti NIK"
- Siswa harus upload file (JPG/PNG/PDF, max 2MB)
- Status → `need_document`

**Opsi 3: 💬 Minta Konfirmasi**
- Untuk field yang perlu penjelasan saja (tanpa file)
- Contoh: "Kenapa jenis kelamin berubah?"
- Siswa cukup kasih pesan penjelasan (min 10 karakter)
- Status → `need_confirmation`

**Opsi 4: ✏️ Minta Edit Data**
- Untuk field yang salah dan perlu diperbaiki
- Contoh: "NIK salah, mohon perbaiki sesuai KK"
- Siswa harus: edit data + upload berkas + kasih alasan
- Status → `need_edit`

#### 3. Request Re-upload Ijazah
- Klik tombol **"📸 Minta Upload Ulang Ijazah"** di modal review
- Berikan catatan (contoh: "Foto blur, mohon upload yang jelas")
- Siswa akan melihat permintaan dan bisa upload ijazah baru (max 1MB)

#### 4. Review Respon Siswa
- Buka kembali modal review untuk siswa yang sama
- Field yang sudah direspon siswa akan tampil dengan:
  - Status **"📤 Siswa Sudah Merespon"**
  - Pesan dari siswa (jika ada)
  - Nilai baru (jika edit data)
  - Link berkas pendukung (jika upload file)
- Admin bisa approve atau request lagi jika masih kurang

#### 5. Final Approval
- Setelah semua field `approved`, tombol **"Final Approval"** muncul
- Klik untuk finalisasi → `verval_approval_status = 'approved'`

---

### B. Untuk Siswa

#### 1. Cek Status Konfirmasi
1. Login ke `index.php`
2. Submit verval (Bagian B)
3. Scroll ke section **"Ada Data yang Perlu Konfirmasi!"**
4. Lihat field mana saja yang perlu action

#### 2. Respond Sesuai Tipe

**Tipe 1: 📄 Perlu Berkas**
- Form upload berkas muncul
- Pilih file (JPG/PNG/PDF, max 2MB)
- Klik **"📤 Kirim Berkas"**

**Tipe 2: 💬 Perlu Konfirmasi**
- Form textarea muncul (tanpa upload file)
- Isi penjelasan (min 10 karakter)
- Contoh: "Maaf pak, kemarin salah input. Sudah diperbaiki sesuai KTP."
- Klik **"💬 Kirim Konfirmasi"**

**Tipe 3: ✏️ Perlu Edit**
- Form lengkap muncul:
  - **Nilai Baru**: Masukkan nilai yang benar
  - **Pesan**: Jelaskan alasan perubahan (min 10 karakter)
  - **Berkas**: Upload bukti (JPG/PNG/PDF, max 2MB)
- Klik **"✏️ Kirim Perubahan"**

#### 3. Re-upload Ijazah (Jika Diminta)
- Section **"📸 Permintaan Upload Ulang Ijazah"** muncul
- Baca catatan dari admin
- Pilih ijazah baru (JPG/PNG only, max 1MB)
- Klik **"📤 Upload Ijazah Baru"**

---

## 📊 STATUS REFERENCE

### verval_konfirmasi.status
| Status | Arti | Warna |
|--------|------|-------|
| `pending` | Menunggu review admin | Abu-abu |
| `approved` | Disetujui admin | Hijau ✅ |
| `need_document` | Admin minta berkas | Merah 📄 |
| `need_confirmation` | Admin minta konfirmasi | Orange 💬 |
| `need_edit` | Admin minta edit data | Biru ✏️ |
| `student_responded` | Siswa sudah respond | Ungu 📤 |

### siswa.verval_approval_status
| Status | Arti |
|--------|------|
| `NULL` | Belum submit verval |
| `pending` | Menunggu review admin |
| `need_confirmation` | Ada field yang perlu action siswa |
| `approved` | Final approval, semua OK ✅ |

---

## 🗂️ FILE STRUCTURE

```
rekamdidik/
├── config/
│   └── verval_konfirmasi.sql          → Database migration (JALANKAN INI DULU!)
│
├── api/
│   ├── submit-konfirmasi-siswa.php    → Siswa submit konfirmasi (3 tipe)
│   ├── reupload-ijazah.php            → Siswa re-upload ijazah
│   ├── check-konfirmasi-status.php    → Cek status konfirmasi siswa
│   ├── check-reupload-ijazah.php      → Cek request re-upload
│   └── admin/
│       ├── konfirmasi-field.php       → Admin konfirmasi per field (4 action)
│       ├── get-verval-review.php      → Get data untuk review modal
│       ├── request-reupload-ijazah.php → Admin request re-upload
│       └── final-approve-verval.php   → Final approval
│
├── admin/
│   ├── index.php                      → Dashboard admin (ada button "Review Verval")
│   └── review-verval.js               → JavaScript modal review (670+ lines)
│
├── index.php                          → Dashboard siswa (form konfirmasi)
│
└── uploads/
    ├── berkas_pendukung/              → Upload berkas konfirmasi (2MB max)
    └── ijazah/                        → Re-upload ijazah (1MB max)
```

---

## ⚙️ SETUP

### 1. Database
```sql
-- Di phpMyAdmin:
-- 1. Pilih database "rekamdidik"
-- 2. Tab "SQL"
-- 3. Copy-paste isi file: config/verval_konfirmasi.sql
-- 4. Klik "Go"
```

### 2. Folder Upload
```powershell
# Windows PowerShell:
New-Item -ItemType Directory -Force -Path "uploads\berkas_pendukung"
New-Item -ItemType Directory -Force -Path "uploads\ijazah"

# Pastikan IIS_IUSRS atau IUSR punya akses write
```

```bash
# Linux/Mac:
mkdir -p uploads/berkas_pendukung
mkdir -p uploads/ijazah
chmod 755 uploads/berkas_pendukung
chmod 755 uploads/ijazah
```

### 3. Verifikasi
- ✅ Cek tabel `verval_konfirmasi` exists
- ✅ Cek kolom `siswa.verval_approval_status` exists
- ✅ Folder uploads exists dan writable
- ✅ File `admin/review-verval.js` loaded di admin/index.php

---

## 🧪 TESTING

### Test 1: Admin Review
1. Login admin → Buka dashboard
2. Klik "Review Verval" pada siswa yang sudah verval
3. Modal terbuka → Lihat 19 fields dengan 4 tombol action
4. Klik "📄 Minta Berkas" pada field "NIK"
5. Isi catatan: "Upload KK sebagai bukti"
6. Submit → Field berubah status "📄 Perlu Berkas"

### Test 2: Siswa Upload Berkas
1. Login siswa (yang sama dari Test 1)
2. Buka dashboard → Lihat section "Ada Data yang Perlu Konfirmasi!"
3. Field "NIK" muncul dengan form upload
4. Upload file KK (JPG, < 2MB)
5. Klik "📤 Kirim Berkas"
6. Success → Status "📤 Terkirim"

### Test 3: Admin Review Response
1. Login admin → Review siswa yang sama
2. Field "NIK" sekarang status "📤 Siswa Sudah Merespon"
3. Link berkas muncul → Klik untuk preview
4. Jika OK → Klik "✓ Setujui"
5. Status berubah "✅ Disetujui"

### Test 4: Final Approval
1. Approve semua field hingga 19 fields = approved
2. Tombol "Final Approval" muncul
3. Klik → Konfirmasi
4. Success → `verval_approval_status = 'approved'`

---

## ⚠️ TROUBLESHOOTING

### Problem: Modal tidak muncul
**Solusi:**
```javascript
// Cek di console browser (F12):
console.log(typeof openReviewVerval); // Harus "function"

// Pastikan script loaded di admin/index.php:
<script src="review-verval.js"></script>
```

### Problem: File upload gagal
**Solusi:**
```php
// Cek php.ini:
upload_max_filesize = 10M
post_max_size = 10M

// Cek folder permissions (Windows):
icacls "uploads\berkas_pendukung" /grant IIS_IUSRS:(OI)(CI)M
```

### Problem: Database error
**Solusi:**
```sql
-- Verifikasi tabel exists:
SHOW TABLES LIKE 'verval_konfirmasi';

-- Verifikasi struktur:
DESCRIBE verval_konfirmasi;

-- Cek kolom tipe_konfirmasi ada:
SHOW COLUMNS FROM verval_konfirmasi WHERE Field='tipe_konfirmasi';
```

### Problem: Siswa tidak lihat notifikasi
**Solusi:**
```javascript
// Cek API response:
// Buka: /api/check-konfirmasi-status.php?siswa_id=1
// Harus return: { success: true, data: {...} }

// Cek di console:
console.log('Konfirmasi Status:', data);
```

---

## 📝 WORKFLOW LENGKAP

```
1. Siswa submit verval (Bagian B)
   └─> System: SET verval_approval_status = 'pending'
   
2. Admin buka review modal
   └─> System: Load 19 fields dari database
   
3. Admin pilih action per field:
   
   A. Jika "Minta Berkas":
      └─> status = 'need_document'
      └─> tipe = 'need_document'
      └─> Siswa: Upload file
      └─> status = 'student_responded'
      └─> Admin review → Approve
      
   B. Jika "Minta Konfirmasi":
      └─> status = 'need_confirmation'
      └─> tipe = 'need_confirmation'
      └─> Siswa: Kasih pesan
      └─> status = 'student_responded'
      └─> Admin review → Approve
      
   C. Jika "Minta Edit Data":
      └─> status = 'need_edit'
      └─> tipe = 'need_edit'
      └─> Siswa: Edit + upload + pesan
      └─> System: Update DB + save history
      └─> status = 'student_responded'
      └─> Admin review → Approve
      
4. Semua field approved
   └─> Tombol "Final Approval" muncul
   └─> Klik → verval_approval_status = 'approved'
   └─> SELESAI ✅
```

---

## 🔐 SECURITY

- ✅ SQL Injection: Prepared statements di semua API
- ✅ File Upload: Validasi tipe, size, extension
- ✅ Session Auth: Check session & user role
- ✅ Path Traversal: File naming dengan timestamp
- ⚠️ Recommend: Tambahkan CSRF token untuk POST requests

---

## 📞 SUPPORT

**Files Penting:**
- Database: `config/verval_konfirmasi.sql`
- Admin JS: `admin/review-verval.js`
- API Docs: Lihat comment di masing-masing file PHP

**Logs:**
- PHP errors: Check server error log
- Database: Check MySQL slow query log
- Upload errors: Check uploads folder permissions

---

## ✨ FITUR TAMBAHAN

### 19 Fields yang Dikonfirmasi:
1. nama_lengkap
2. nik
3. nisn
4. jenis_kelamin
5. tempat_lahir
6. tanggal_lahir
7. agama
8. alamat_lengkap
9. rt_rw
10. kelurahan_desa
11. kecamatan
12. kota_kabupaten
13. provinsi
14. kode_pos
15. asal_sekolah
16. npsn_asal
17. tahun_lulus
18. no_ijazah
19. dokumen_ijazah (path file)

### Validasi Upload:
- **Berkas Pendukung**: JPG, PNG, PDF | Max 2MB
- **Ijazah**: JPG, PNG only | Max 1MB

### Auto-Features:
- ✅ Unique constraint: 1 konfirmasi per field per siswa
- ✅ Cascade delete: Hapus siswa → konfirmasi ikut terhapus
- ✅ Timestamp: created_at, updated_at auto
- ✅ History: Track nilai_lama → nilai_baru

---

**Status:** ✅ Production Ready | **Version:** 1.0 | **Date:** Feb 2026
