<?php
/**
 * API: Save Bagian B (Jenjang Sebelumnya + Upload)
 * POST /api/save-bagian-b.php
 * 
 * Simpan data jenjang sebelumnya dan upload dokumen
 * Update status jika semua field Bagian A sudah verified
 */

header('Content-Type: application/json');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $siswa_id = isset($_POST['siswa_id']) ? intval($_POST['siswa_id']) : 0;
    
    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    $conn->begin_transaction();

    try {
        // 1. Handle dokumen ijazah upload
        $dokumen_ijazah = null;
        if (isset($_FILES['dokumen_ijazah']) && $_FILES['dokumen_ijazah']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['dokumen_ijazah'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 1 * 1024 * 1024; // 1MB (diubah dari 2MB)

            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Format file harus JPG, JPEG, atau PNG');
            }

            if ($file['size'] > $max_size) {
                $file_size_mb = round($file['size'] / (1024 * 1024), 2);
                throw new Exception('Ukuran file terlalu besar (' . $file_size_mb . 'MB). Maksimal 1MB');
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $dokumen_ijazah = 'ijazah_' . $siswa_id . '_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/ijazah/';
            
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception('Gagal membuat folder upload');
                }
            }
            
            $upload_path = $upload_dir . $dokumen_ijazah;

            // Hapus file lama jika ada (saat update)
            $stmt_check = $conn->prepare('SELECT dokumen_ijazah FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
            $stmt_check->bind_param('i', $siswa_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($row_check = $result_check->fetch_assoc()) {
                $old_file = $row_check['dokumen_ijazah'];
                if (!empty($old_file)) {
                    $old_path = $upload_dir . $old_file;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            }
            $stmt_check->close();

            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Gagal upload file dokumen ijazah');
            }
        }

        // 2. Save verval jenjang sebelumnya
        $verval_fields = [
            'nama_sd' => $_POST['nama_sd'] ?? '',
            'tahun_ajaran_kelulusan' => $_POST['tahun_ajaran_kelulusan'] ?? '',
            'nip_kepala_sekolah' => $_POST['nip_kepala_sekolah'] ?? '',
            'nama_kepala_sekolah' => $_POST['nama_kepala_sekolah'] ?? '',
            'nomor_seri_ijazah' => $_POST['nomor_seri_ijazah'] ?? '',
            'tanggal_terbit_ijazah' => $_POST['tanggal_terbit_ijazah'] ?? ''
        ];

        // Check if exists
        $stmt = $conn->prepare('SELECT id FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            // Update
            $set_clause = [];
            $values = [];
            $types = '';
            
            foreach ($verval_fields as $field => $value) {
                $set_clause[] = "`$field` = ?";
                $values[] = trim($value);
                $types .= 's';
            }
            
            if ($dokumen_ijazah) {
                $set_clause[] = "`dokumen_ijazah` = ?";
                $values[] = $dokumen_ijazah;
                $types .= 's';
            }
            
            $values[] = $siswa_id;
            $types .= 'i';
            
            $update_query = "UPDATE verval_jenjang_sebelumnya SET " . implode(', ', $set_clause) . " WHERE siswa_id = ?";
            $stmt = $conn->prepare($update_query);
            
            if (!$stmt) {
                throw new Exception('Prepare UPDATE failed: ' . $conn->error);
            }
            
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new Exception('Execute UPDATE failed: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            // Insert
            $cols = array_keys($verval_fields);
            $cols[] = 'siswa_id';
            if ($dokumen_ijazah) {
                $cols[] = 'dokumen_ijazah';
            }
            
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            
            $values = array_values($verval_fields);
            $values[] = $siswa_id;
            if ($dokumen_ijazah) {
                $values[] = $dokumen_ijazah;
            }
            
            $types = str_repeat('s', count($verval_fields)) . 'i' . ($dokumen_ijazah ? 's' : '');
            
            $insert_query = "INSERT INTO verval_jenjang_sebelumnya (`" . implode('`, `', $cols) . "`) VALUES ($placeholders)";
            $stmt = $conn->prepare($insert_query);
            
            if (!$stmt) {
                throw new Exception('Prepare INSERT failed: ' . $conn->error);
            }
            
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new Exception('Execute INSERT failed: ' . $stmt->error);
            }
            $stmt->close();
        }

        // 3. Check if ALL Bagian A fields are verified
        $verified_fields = [
            'nik_kk_verified', 'nama_kk_verified', 'tempat_lahir_kk_verified',
            'tanggal_lahir_kk_verified', 'jenis_kelamin_kk_verified', 'nama_ibu_kk_verified',
            'nama_ayah_kk_verified', 'nisn_verified', 'nama_ijazah_verified',
            'tempat_lahir_ijazah_verified', 'tanggal_lahir_ijazah_verified',
            'jenis_kelamin_ijazah_verified', 'nama_ayah_ijazah_verified'
        ];
        
        $check_sql = "SELECT " . implode(', ', $verified_fields) . " FROM siswa WHERE id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $all_verified = true;
        foreach ($verified_fields as $vf) {
            if (empty($row[$vf])) {
                $all_verified = false;
                break;
            }
        }

        // 4. Update status HANYA jika semua Bagian A sudah verified
        if ($all_verified) {
            $status = 'sudah';
            $stmt = $conn->prepare('UPDATE siswa SET verval_status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('si', $status, $siswa_id);
            $stmt->execute();
            $stmt->close();
            
            $response['status_updated'] = 'sudah';
            $response['message'] = 'Data Bagian B berhasil disimpan. Status verval diupdate ke "Sudah" ✓';
        } else {
            $response['message'] = 'Data Bagian B berhasil disimpan. Silakan lengkapi verifikasi Bagian A untuk menyelesaikan verval.';
            $response['status_updated'] = 'belum';
        }

        $conn->commit();
        
        $response['success'] = true;
        $response['all_verified'] = $all_verified;
        $response['dokumen_uploaded'] = !empty($dokumen_ijazah);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
