<?php
/**
 * API: Comprehensive Save Verval Data
 * POST /api/save-verval-complete.php
 * 
 * Menangani:
 * 1. Update data siswa yang diedit
 * 2. Simpan verified flags
 * 3. Catat history perbaikan
 * 4. Upload dokumen ijazah
 * 5. Simpan data jenjang sebelumnya
 * 6. Update status verval ke 'sudah'
 */

// Enable error logging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => '', 'errors' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $siswa_id = isset($_POST['siswa_id']) ? intval($_POST['siswa_id']) : 0;
    
    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }

    // Initialize database connection
    try {
        $db = new Database();
        $conn = $db->connect();
    } catch (Exception $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
    
    // Check if connection is valid
    if (!$conn) {
        throw new Exception('Failed to create database connection');
    }
    
    $conn->begin_transaction();

    try {
        // 1. Get siswa current data for history tracking
        $stmt = $conn->prepare('SELECT * FROM siswa WHERE id = ?');
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $siswa_result = $stmt->get_result();
        if ($siswa_result->num_rows === 0) {
            throw new Exception('Data siswa tidak ditemukan');
        }
        $siswa_old = $siswa_result->fetch_assoc();
        $stmt->close();

        // 2. Update data siswa yang diedit
        $editable_fields = [
            'nama_kk', 'nik_kk', 'tempat_lahir_kk', 'tanggal_lahir_kk', 'jenis_kelamin_kk',
            'nama_ibu_kk', 'nama_ayah_kk',
            'nama_ijazah', 'tempat_lahir_ijazah', 'tanggal_lahir_ijazah', 'jenis_kelamin_ijazah',
            'nama_ayah_ijazah'
        ];

        $updates = [];
        $history = [];
        
        foreach ($editable_fields as $field) {
            if (isset($_POST[$field])) {
                $new_value = trim($_POST[$field]);
                $old_value = isset($siswa_old[$field]) ? $siswa_old[$field] : '';
                
                // Jika ada perubahan, catat ke history
                if ($new_value !== $old_value && !empty($new_value)) {
                    $history[] = [
                        'field' => $field,
                        'old' => $old_value,
                        'new' => $new_value
                    ];
                    $updates[$field] = $new_value;
                }
            }
        }

        // Save siswa updates jika ada perubahan
        if (!empty($updates)) {
            $set_clause = [];
            $types = '';
            $values = [];
            
            foreach ($updates as $field => $value) {
                $set_clause[] = "$field = ?";
                $types .= is_numeric($value) ? 'i' : 's';
                $values[] = $value;
            }
            
            $types .= 'i'; // for siswa_id
            $values[] = $siswa_id;
            
            $update_query = "UPDATE siswa SET " . implode(', ', $set_clause) . " WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal update data siswa: ' . $stmt->error);
            }
            $stmt->close();
        }

        // 3. Save history perbaikan
        if (!empty($history)) {
            $stmt = $conn->prepare('INSERT INTO history_perbaikan (siswa_id, field_name, nilai_sebelum, nilai_sesudah) VALUES (?, ?, ?, ?)');
            
            foreach ($history as $item) {
                $stmt->bind_param('isss', $siswa_id, $item['field'], $item['old'], $item['new']);
                if (!$stmt->execute()) {
                    throw new Exception('Gagal catat history perbaikan: ' . $stmt->error);
                }
            }
            $stmt->close();
        }

        // 4. Update verified flags
        $verified_fields = [
            'nik_kk_verified', 'nama_kk_verified', 'tempat_lahir_kk_verified',
            'tanggal_lahir_kk_verified', 'jenis_kelamin_kk_verified', 'nama_ibu_kk_verified',
            'nama_ayah_kk_verified', 'nisn_verified', 'nama_ijazah_verified',
            'tempat_lahir_ijazah_verified', 'tanggal_lahir_ijazah_verified',
            'jenis_kelamin_ijazah_verified', 'nama_ayah_ijazah_verified'
        ];

        $verified_updates = [];
        foreach ($verified_fields as $field) {
            $verified_updates[$field] = isset($_POST[$field]) && $_POST[$field] === '1' ? 1 : 0;
        }

        if (!empty($verified_updates)) {
            $set_clause = [];
            $values = [];
            
            foreach ($verified_updates as $field => $value) {
                $set_clause[] = "$field = ?";
                $values[] = $value;
            }
            
            $values[] = $siswa_id;
            
            $update_query = "UPDATE siswa SET " . implode(', ', $set_clause) . " WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            
            $types = str_repeat('i', count($verified_updates)) . 'i';
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal update verified flags: ' . $stmt->error);
            }
            $stmt->close();
        }

        // 5. Handle dokumen ijazah upload
        $dokumen_ijazah = null;
        if (isset($_FILES['dokumen_ijazah']) && $_FILES['dokumen_ijazah']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['dokumen_ijazah'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            $max_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Format file harus JPG, JPEG, PNG, atau PDF');
            }

            if ($file['size'] > $max_size) {
                throw new Exception('Ukuran file maksimal 2MB');
            }

            // Create unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $dokumen_ijazah = 'ijazah_' . $siswa_id . '_' . time() . '.' . $ext;
            $upload_dir = '../uploads/ijazah/';
            
            // Create directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $upload_path = $upload_dir . $dokumen_ijazah;

            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Gagal upload file dokumen ijazah');
            }
        }

        // 6. Save verval jenjang sebelumnya
        $verval_fields = [
            'nama_sd' => $_POST['nama_sd'] ?? '',
            'tahun_ajaran_kelulusan' => $_POST['tahun_ajaran_kelulusan'] ?? '',
            'nip_kepala_sekolah' => $_POST['nip_kepala_sekolah'] ?? '',
            'nama_kepala_sekolah' => $_POST['nama_kepala_sekolah'] ?? '',
            'nomor_seri_ijazah' => $_POST['nomor_seri_ijazah'] ?? '',
            'tanggal_terbit_ijazah' => $_POST['tanggal_terbit_ijazah'] ?? ''
        ];

        // Check if verval data exists
        $stmt = $conn->prepare('SELECT id FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            // Update
            $set_clause = [];
            $values = [];
            
            foreach ($verval_fields as $field => $value) {
                $set_clause[] = "$field = ?";
                $values[] = trim($value);
            }
            
            if ($dokumen_ijazah) {
                $set_clause[] = "dokumen_ijazah = ?";
                $values[] = $dokumen_ijazah;
            }
            
            $values[] = $siswa_id;
            
            $update_query = "UPDATE verval_jenjang_sebelumnya SET " . implode(', ', $set_clause) . " WHERE siswa_id = ?";
            $stmt = $conn->prepare($update_query);
            
            $types = str_repeat('s', count($values) - 1) . 'i';
            $stmt->bind_param($types, ...$values);
        } else {
            // Insert
            $cols = array_keys($verval_fields);
            $cols[] = 'siswa_id';
            $cols[] = 'dokumen_ijazah';
            
            $values = array_values($verval_fields);
            $values[] = $siswa_id;
            $values[] = $dokumen_ijazah;
            
            $placeholders = str_repeat('?, ', count($values));
            $placeholders = rtrim($placeholders, ', ');
            
            $insert_query = "INSERT INTO verval_jenjang_sebelumnya (" . implode(', ', $cols) . ") VALUES ($placeholders)";
            $stmt = $conn->prepare($insert_query);
            
            $types = str_repeat('s', count($verval_fields)) . 'is';
            $stmt->bind_param($types, ...array_values($verval_fields), $siswa_id, $dokumen_ijazah);
        }

        if (!$stmt->execute()) {
            throw new Exception('Gagal simpan verval jenjang sebelumnya: ' . $stmt->error);
        }
        $stmt->close();

        // 7. Update verval_status ke 'sudah'
        $status = 'sudah';
        $stmt = $conn->prepare('UPDATE siswa SET verval_status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('si', $status, $siswa_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal update status verval: ' . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        $conn->close();

        $response['success'] = true;
        $response['message'] = 'Data verval berhasil disimpan lengkap';
        $response['saved_data'] = [
            'edited_fields' => count($updates),
            'history_items' => count($history),
            'verified_count' => array_sum($verified_updates),
            'dokumen_uploaded' => !empty($dokumen_ijazah),
            'status' => 'sudah',
            'debug' => [
                'po_fields_received' => array_keys($_POST),
                'verified_flags_sent' => array_filter($verified_updates) // Only show enabled flags
            ]
        ];

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    if (isset($conn)) {
        try {
            $conn->rollback();
        } catch (Exception $rb_error) {
            // Rollback failed, but still catch original error
        }
    }
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['errors'][] = $e->getMessage();
}

// Ensure JSON output
if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode($response);
?>
