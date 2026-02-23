# Setup Guide untuk Developer

## 📋 Checklist Setup Awal

### 1. Database Setup
- [ ] MySQL/MariaDB sudah installed dan running
- [ ] Import schema dari `config/schema.sql`
- [ ] Database `rekamdidik_db` sudah dibuat
- [ ] User database sudah dibuat dengan privilege penuh

### 2. File Configuration
- [ ] Update `config/Database.php` dengan kredensial database
- [ ] Check folder `uploads/ijazah/` memiliki write permission
- [ ] Check folder permissions:
  ```bash
  chmod 755 uploads/ijazah/
  chmod 644 uploads/.htaccess
  ```

### 3. Web Server Configuration
- [ ] Apache mod_rewrite enabled (untuk URL rewriting)
- [ ] .htaccess files allowed (AllowOverride All)
- [ ] PHP 7.4+ dengan extension mysqli enabled

### 4. Testing
```bash
# 1. Test database connection
# Buka: http://localhost/rekamdidik/api/check-nisn.php
# Kirim POST request dengan NISN yang belum ada
# Seharusnya error: "NISN tidak ditemukan dalam database"

# 2. Test halaman utama
# Buka: http://localhost/rekamdidik/

# 3. Test admin dashboard
# Buka: http://localhost/rekamdidik/admin/
```

### 5. Insert Sample Data (Opsional untuk Testing)

Jalankan query di bawah di MySQL untuk insert data test:

```sql
-- Insert sample student
INSERT INTO siswa (
    nisn, nik_kk, nama_kk, tempat_lahir_kk, tanggal_lahir_kk, 
    jenis_kelamin_kk, nama_ibu_kk, nama_ayah_kk,
    nama_ijazah, tempat_lahir_ijazah, tanggal_lahir_ijazah,
    jenis_kelamin_ijazah, nama_ayah_ijazah, verval_status
) VALUES (
    '0123456789',
    '3273050101010001',
    'BUDI SANTOSO',
    'MAJALENGKA',
    '2010-01-15',
    'L',
    'SITI NURHALIZA',
    'RAHMAT SURYANTO',
    'BUDI SANTOSO',
    'MAJALENGKA',
    '2010-01-15',
    'L',
    'RAHMAT SURYANTO',
    'belum'
);

-- Insert sample verval data
INSERT INTO verval_jenjang_sebelumnya (
    siswa_id, nama_sd, tahun_ajaran_kelulusan,
    nip_kepala_sekolah, nama_kepala_sekolah,
    nomor_seri_ijazah, tanggal_terbit_ijazah
) VALUES (
    1,
    'SD NEGERI MAJALENGKA',
    '2020/2021',
    '197512311999021001',
    'Drs. AHMADI, M.Pd',
    '2021-061234',
    '2021-06-15'
);
```

---

## 🔧 Development Notes

### Code Structure
```
- config/Database.php - Singleton pattern untuk database connection
- api/*.php - REST API endpoints
- admin/index.php - Admin dashboard frontend
- index.php - Siswa verval frontend
- assets/css/style.css - Responsive design dengan CSS Grid
- assets/js/utils.js - Helper functions dan API calls
```

### Adding New API Endpoint

1. Create file: `api/[endpoint].php`
2. Set header: `header('Content-Type: application/json');`
3. Get input dari `file_get_contents('php://input')` atau `$_POST/$_GET`
4. Validate input
5. Call database & process
6. Return JSON response

Example:
```php
<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    // Your logic here
    $response['success'] = true;
    $response['message'] = 'Success';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
```

### Modifying Frontend

1. Edit HTML di halaman (index.php atau admin/index.php)
2. Edit style di `assets/css/style.css`
3. Edit JavaScript di `assets/js/utils.js` atau inline di HTML

### Database Query Tips

```php
// Read
$stmt = $conn->prepare('SELECT * FROM siswa WHERE nisn = ?');
$stmt->bind_param('s', $nisn);
$stmt->execute();
$result = $stmt->get_result();

// Insert
$stmt = $conn->prepare('INSERT INTO siswa (nisn, nama_kk) VALUES (?, ?)');
$stmt->bind_param('ss', $nisn, $nama);
$stmt->execute();

// Update
$stmt = $conn->prepare('UPDATE siswa SET nama_kk = ? WHERE id = ?');
$stmt->bind_param('si', $nama, $id);
$stmt->execute();

// Delete
$stmt = $conn->prepare('DELETE FROM siswa WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
```

---

## 📚 Additional Resources

### Useful SQL Queries

```sql
-- Count semua siswa
SELECT COUNT(*) FROM siswa;

-- Count siswa yang belum verval
SELECT COUNT(*) FROM siswa WHERE verval_status = 'belum';

-- List siswa dengan verval data
SELECT s.*, vjs.nama_sd 
FROM siswa s 
LEFT JOIN verval_jenjang_sebelumnya vjs ON s.id = vjs.siswa_id;

-- Show history perbaikan untuk siswa tertentu
SELECT * FROM history_perbaikan 
WHERE siswa_id = 1 
ORDER BY tanggal_perbaikan DESC;

-- Reset password admin
UPDATE admin_users SET password = MD5('newpassword') 
WHERE username = 'admin';
```

### PHP Debug Tips

```php
// Print debug info
echo '<pre>';
var_dump($data);
echo '</pre>';
die();

// Log to file
error_log(json_encode($data), 3, 'debug.log');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Frontend Debug Tips

```javascript
// Console logging
console.log('Data:', data);

// Inspect network requests
// Open DevTools > Network tab

// Check API response
fetch(url).then(r => r.json()).then(d => console.log(d));
```

---

## 🚀 Deployment Checklist

Sebelum go live ke production:

- [ ] Change admin password dari default value
- [ ] Set PHP error reporting ke E_ALL tapi jangan display errors
- [ ] Setup SSL certificate untuk HTTPS
- [ ] Backup database regularly
- [ ] Setup cron job untuk backup otomatis
- [ ] Monitor disk space
- [ ] Setup email notifications untuk error
- [ ] Test semua fitur di production environment
- [ ] Add backup procedure documentation
- [ ] Setup monitoring & alerting system

---

## 📞 Deployment Support

Jika memerlukan bantuan deployment ke production:
1. Pastikan PHP 7.4+ dan MySQL 5.7+ available
2. Copy semua file ke document root web server
3. Update config/Database.php
4. Import schema database
5. Set proper permissions
6. Test akses aplikasi

---

**Last Updated:** February 23, 2024
**Version:** 1.0.0 Setup Guide
