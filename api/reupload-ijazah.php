<?php
/**
 * API: Re-upload Ijazah (Siswa)
 * POST /api/reupload-ijazah.php
 * 
 * Form Data:
 * - siswa_id: int
 * - ijazah: file (JPG, PNG - Max 1MB)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_POST['siswa_id'])) {
        throw new Exception('Siswa ID tidak ditemukan');
    }

    $siswa_id = intval($_POST['siswa_id']);

    if (!isset($_FILES['ijazah']) || $_FILES['ijazah']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File ijazah harus diupload');
    }

    $file = $_FILES['ijazah'];
    $max_size = 1 * 1024 * 1024; // 1MB
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    // Validasi tipe file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Tipe file tidak diizinkan. Hanya JPG dan PNG');
    }

    // Validasi ukuran
    if ($file['size'] > $max_size) {
        $size_mb = round($file['size'] / (1024 * 1024), 2);
        throw new Exception("Ukuran file terlalu besar ({$size_mb}MB). Maksimal 1MB");
    }

    // Validasi ekstensi
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        throw new Exception('Ekstensi file tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();

    $conn->begin_transaction();

    // Check apakah ada request re-upload pending
    $stmt = $conn->prepare('SELECT id, ijazah_lama FROM ijazah_reupload_request 
                           WHERE siswa_id = ? AND status = "pending"');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Tidak ada permintaan re-upload ijazah dari admin');
    }
    
    $request = $result->fetch_assoc();
    $stmt->close();

    // Get old file
    $stmt = $conn->prepare('SELECT dokumen_ijazah FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_data = $result->fetch_assoc();
    $old_filename = $old_data ? $old_data['dokumen_ijazah'] : null;
    $stmt->close();

    // Delete old file if exists
    if (!empty($old_filename)) {
        $old_file = __DIR__ . '/../uploads/ijazah/' . $old_filename;
        if (file_exists($old_file)) {
            unlink($old_file);
        }
    }

    // Upload new file
    $upload_dir = __DIR__ . '/../uploads/ijazah/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = 'ijazah_' . $siswa_id . '_' . time() . '.' . $extension;
    $upload_path = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Gagal mengupload file');
    }

    // Update database
    $stmt = $conn->prepare('UPDATE verval_jenjang_sebelumnya 
                           SET dokumen_ijazah = ?, updated_at = NOW()
                           WHERE siswa_id = ?');
    $stmt->bind_param('si', $filename, $siswa_id);
    
    if (!$stmt->execute()) {
        // Cleanup uploaded file on error
        unlink($upload_path);
        throw new Exception('Gagal menyimpan informasi ijazah: ' . $stmt->error);
    }
    $stmt->close();

    // Update request status
    $stmt = $conn->prepare('UPDATE ijazah_reupload_request 
                           SET status = "completed", 
                               ijazah_baru = ?,
                               responded_at = NOW(),
                               updated_at = NOW()
                           WHERE id = ?');
    $stmt->bind_param('si', $filename, $request['id']);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Ijazah berhasil diupload ulang. Silakan tunggu review admin.';
    $response['filename'] = $filename;

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
