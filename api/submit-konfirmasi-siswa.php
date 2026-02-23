<?php
/**
 * API: Submit Respon Konfirmasi (Siswa)
 * POST /api/submit-konfirmasi-siswa.php
 * 
 * Form Data:
 * - siswa_id: int
 * - field_name: string 
 * - pesan_siswa: text (wajib)
 * - nilai_baru: string (optional - untuk edit data)
 * - berkas: file (optional - untuk upload berkas pendukung)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_POST['siswa_id']) || !isset($_POST['field_name']) || !isset($_POST['pesan_siswa'])) {
        throw new Exception('Parameter tidak lengkap');
    }

    $siswa_id = intval($_POST['siswa_id']);
    $field_name = $_POST['field_name'];
    $pesan_siswa = trim($_POST['pesan_siswa']);
    $nilai_baru = isset($_POST['nilai_baru']) ? trim($_POST['nilai_baru']) : null;

    if (strlen($pesan_siswa) < 10) {
        throw new Exception('Pesan konfirmasi minimal 10 karakter');
    }

    $db = new Database();
    $conn = $db->connect();

    $conn->begin_transaction();

    // Get konfirmasi record
    $stmt = $conn->prepare('SELECT id, status, tipe_konfirmasi, berkas_pendukung 
                           FROM verval_konfirmasi 
                           WHERE siswa_id = ? AND field_name = ?');
    $stmt->bind_param('is', $siswa_id, $field_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Konfirmasi untuk field ini tidak ditemukan');
    }
    
    $konfirmasi = $result->fetch_assoc();
    $stmt->close();

    // Validasi tipe konfirmasi
    $valid_statuses = ['need_document', 'need_confirmation', 'need_edit', 'student_responded'];
    if (!in_array($konfirmasi['status'], $valid_statuses)) {
        throw new Exception('Status konfirmasi tidak memerlukan respon');
    }

    $tipe = $konfirmasi['tipe_konfirmasi'];
    $berkas_filename = $konfirmasi['berkas_pendukung']; // Keep old file initially

    // Handle edit data (untuk need_edit)
    if ($tipe === 'need_edit') {
        if (empty($nilai_baru)) {
            throw new Exception('Nilai data baru harus diisi');
        }
        
        // Update field value di tabel siswa (atau verval_jenjang_sebelumnya)
        // List field yang ada di tabel siswa
        $siswa_fields = [
            'nik_kk', 'nama_kk', 'tempat_lahir_kk', 'tanggal_lahir_kk',
            'jenis_kelamin_kk', 'nama_ibu_kk', 'nama_ayah_kk',
            'nisn', 'nama_ijazah', 'tempat_lahir_ijazah', 'tanggal_lahir_ijazah',
            'jenis_kelamin_ijazah', 'nama_ayah_ijazah'
        ];

        if (in_array($field_name, $siswa_fields)) {
            // Update di tabel siswa
            $update_query = "UPDATE siswa SET $field_name = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('si', $nilai_baru, $siswa_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal mengupdate data: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            // Update di tabel verval_jenjang_sebelumnya
            $update_query = "UPDATE verval_jenjang_sebelumnya SET $field_name = ?, updated_at = NOW() WHERE siswa_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('si', $nilai_baru, $siswa_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal mengupdate data jenjang: ' . $stmt->error);
            }
            $stmt->close();
        }

        // Save to history
        $stmt = $conn->prepare('INSERT INTO history_perbaikan_data 
                               (siswa_id, field_name, nilai_sebelum, nilai_sesudah, tanggal_perbaikan)
                               SELECT ?, ?, 
                                      (SELECT ' . $field_name . ' FROM siswa WHERE id = ? LIMIT 1),
                                      ?,
                                      NOW()');
        $stmt->bind_param('isss', $siswa_id, $field_name, $siswa_id, $nilai_baru);
        $stmt->execute();
        $stmt->close();
    }

    // Handle file upload (wajib untuk need_document dan need_edit)
    if (($tipe === 'need_document' || $tipe === 'need_edit') && isset($_FILES['berkas'])) {
        $file = $_FILES['berkas'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error upload file');
        }

        $max_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

        // Validasi
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Tipe file tidak diizinkan. Hanya JPG, PNG, dan PDF');
        }

        if ($file['size'] > $max_size) {
            $size_mb = round($file['size'] / (1024 * 1024), 2);
            throw new Exception("Ukuran file terlalu besar ({$size_mb}MB). Maksimal 2MB");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed_extensions)) {
            throw new Exception('Ekstensi file tidak valid');
        }

        // Delete old file if exists
        if (!empty($konfirmasi['berkas_pendukung'])) {
            $old_file = __DIR__ . '/../uploads/berkas_pendukung/' . $konfirmasi['berkas_pendukung'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Upload new file
        $upload_dir = __DIR__ . '/../uploads/berkas_pendukung/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $berkas_filename = 'berkas_' . $siswa_id . '_' . $field_name . '_' . time() . '.' . $extension;
        $upload_path = $upload_dir . $berkas_filename;

        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Gagal mengupload file');
        }
    }

    // Validasi: need_document dan need_edit WAJIB upload berkas
    if (($tipe === 'need_document' || $tipe === 'need_edit') && empty($berkas_filename)) {
        throw new Exception('Upload berkas pendukung wajib untuk tipe konfirmasi ini');
    }

    // Update verval_konfirmasi
    $stmt = $conn->prepare('UPDATE verval_konfirmasi 
                           SET status = "student_responded", 
                               pesan_siswa = ?,
                               nilai_baru_siswa = ?,
                               berkas_pendukung = ?,
                               updated_at = NOW()
                           WHERE id = ?');
    $stmt->bind_param('sssi', $pesan_siswa, $nilai_baru, $berkas_filename, $konfirmasi['id']);
    
    if (!$stmt->execute()) {
        // Cleanup uploaded file on error
        if (isset($upload_path) && file_exists($upload_path)) {
            unlink($upload_path);
        }
        throw new Exception('Gagal menyimpan respon: ' . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Respon konfirmasi berhasil dikirim. Menunggu review admin.';

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
