# Dokumentasi Fix: Verified Checkbox Data tidak Terkirim ke Backend

## Problem

User melaporkan: **"Data yang diinput dan diupload tidak terkirim"** - Status verval tetap "belum verval" meskipun sudah disimpan.

### Root Cause

Checkbox untuk verified flags (✅ di bagian data siswa) tidak memiliki `name` attribute, sehingga tidak disertakan dalam FormData ketika form disubmit.

**HTML Sebelumnya (SALAH):**
```html
<input type="checkbox" class="verify-checkbox" data-verify-field="nik_kk_verified" ...>
```
- `data-verify-field` hanya digunakan oleh JavaScript, bukan untuk form submission
- FormData API hanya mengirim element yang memiliki `name` attribute
- Hasilnya: verified flags tidak pernah terkirim ke backend

## Solution Implemented

### 1. Helper Function di index.php (Line 596-605)

Menambahkan fungsi `ensureCheckboxNames()` yang:
- Meloop melalui semua `.verify-checkbox` element
- Mengambil nilai dari `data-verify-field` attribute
- Menambahkan `name` dan `value="1"` ke masing-masing checkbox

```javascript
function ensureCheckboxNames() {
    document.querySelectorAll('.verify-checkbox').forEach(checkbox => {
        const verifyField = checkbox.getAttribute('data-verify-field');
        if (verifyField && !checkbox.getAttribute('name')) {
            checkbox.setAttribute('name', verifyField);
            checkbox.setAttribute('value', '1');
        }
    });
}
```

### 2. Call Function Sebelum Form Submit (Line 644)

Fungsi dipanggil dalam form submission handler, sebelum FormData dibuat:

```javascript
if (!allowSubmit) {
    return;
}

// Ensure all checkboxes have name attribute before collecting FormData
ensureCheckboxNames();

const formData = new FormData(this);
```

### 3. Tambah `name` Attribute ke Hidden Input (Line 384)

Hidden input siswa_id juga perlu `name` attribute:

```html
<!-- SEBELUM -->
<input type="hidden" id="siswa_id" value="${siswa.id}">

<!-- SESUDAH -->
<input type="hidden" id="siswa_id" name="siswa_id" value="${siswa.id}">
```

## Alur Data Setelah Fix

### Student Flow:

1. **Student mengisi form:**
   - Input text: nama_kk, nik_kk, dll
   - Checkbox untuk verifikasi: ✅ jika data sesuai
   - File upload: dokumen ijazah

2. **Klik "Submit Data Verval":**
   - JavaScript confirm dialog muncul
   - User menyetujui pernyataan tanggung jawab

3. **Form disubmit:**
   - `ensureCheckboxNames()` menambahkan `name` attribute ke semua checkbox yang belum punya
   - FormData dikumpulkan dari form element
   - Include: siswa_id, text inputs, **checkbox values**, file upload

4. **POST ke `/api/save-verval-complete.php`:**
   - Backend menerima semua data termasuk verified flags
   - File upload diproses dan disimpan
   - History perbaikan dicatat untuk field yang diedit
   - Verified flags diupdate di database
   - Status verval diubah dari 'belum' menjadi 'sudah'
   - Response success: true, message success

5. **Page reload:**
   - Student melihat status "Sudah Diverifikasi ✓"
   - Data ditampilkan dengan updated verified flags

### Backend Processing:

**File:** `api/save-verval-complete.php`

Steps:
1. Get siswa_id dari hidden input
2. Get current siswa data untuk history tracking
3. Update editable fields jika ada perubahan
4. Catat history_perbaikan untuk field yang berubah
5. **Update verified flags** dari POST parameters:
   - Cek `$_POST['nik_kk_verified']`, `$_POST['nisn_verified']`, dll
   - Set ke 1 jika ada dan bernilai '1', set ke 0 jika tidak ada
6. Upload dokumen ijazah
7. Simpan/update verval_jenjang_sebelumnya
8. **Update status verval ke 'sudah'**
9. Commit transaction, return success

## Verified Fields (13 Total)

Data Siswa (Bagian A):
- `nik_kk_verified`
- `nisn_verified`
- `nama_kk_verified`
- `tempat_lahir_kk_verified`
- `tanggal_lahir_kk_verified`
- `jenis_kelamin_kk_verified`
- `nama_ibu_kk_verified`
- `nama_ayah_kk_verified`

Jenjang Sebelumnya (Bagian B):
- `nama_ijazah_verified`
- `tempat_lahir_ijazah_verified`
- `tanggal_lahir_ijazah_verified`
- `jenis_kelamin_ijazah_verified`
- `nama_ayah_ijazah_verified`

## Testing Checklist

- [ ] Buka form verval sebagai student
- [ ] Isikan beberapa field text (nama, nik, dll)
- [ ] Centang beberapa checkbox ✅
- [ ] Upload dokumen ijazah
- [ ] Klik "Submit Data Verval"
- [ ] Setujui dialog konfirmasi
- [ ] Tunggu loading selesai
- [ ] Verifikasi status berubah menjadi "Sudah Diverifikasi ✓"

## Admin Verification

Login sebagai admin:
- Lihat siswa di list "Sudah Diverifikasi"
- Klik detail untuk lihat data
- Verifikasi:
  - Editable fields terupdate ✓
  - Verified flags terisi ✓
  - Document file tersimpan ✓
  - History perbaikan tercatat ✓

## Debugging

Jika masih ada masalah:

1. **Open Browser DevTools → Network Tab**
   - Check POST request ke `/api/save-verval-complete.php`
   - Lihat FormData - pastikan checkbox values ada
   - Contoh: `nik_kk_verified: 1`, `nisn_verified: 1`, dll

2. **Check Browser Console**
   - Lihat response API - apakah success: true?
   - Lihat saved_data counts - pastikan tidak 0

3. **Check Database**
   - Query: `SELECT verval_status, nik_kk_verified, nisn_verified FROM siswa WHERE id = X`
   - Pastikan status = 'sudah' dan verified flags = 1

4. **Check File Upload**
   - Verifikasi dokumen_ijazah tersimpan di `/uploads/dokumen_ijazah/`

## Files Modified

1. **index.php**
   - Added: `ensureCheckboxNames()` function (line 596-605)
   - Modified: Form submission handler di-update dengan call function (line 644)
   - Modified: Hidden input siswa_id ditambah `name` attribute (line 384)

2. **api/save-verval-complete.php**
   - Already correctly implemented ✓
   - No changes needed

## Implementation Date

Implemented: 26 Desember 2024

## Status

✅ **READY FOR TESTING**
