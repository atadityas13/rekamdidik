<?php
/**
 * API: Request Re-upload Ijazah (Admin)
 * POST /api/admin/request-reupload-ijazah.php
 * 
 * Body: {
 *   siswa_id: int,
 *   catatan: string (wajib - jelaskan kenapa perlu re-upload)
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check admin login
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized: Admin login required');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['siswa_id']) || !isset($input['catatan'])) {
        throw new Exception('Parameter tidak lengkap');
    }

    $siswa_id = intval($input['siswa_id']);
    $catatan = trim($input['catatan']);
    $admin_id = $_SESSION['admin_id'] ?? null;

    if (strlen($catatan) < 10) {
        throw new Exception('Catatan minimal 10 karakter. Jelaskan kenapa perlu re-upload.');
    }

    $db = new Database();
    $conn = $db->connect();

    $conn->begin_transaction();

    // Get current ijazah filename
    $stmt = $conn->prepare('SELECT dokumen_ijazah FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Data ijazah tidak ditemukan');
    }
    
    $row = $result->fetch_assoc();
    $ijazah_lama = $row['dokumen_ijazah'];
    $stmt->close();

    // Check apakah sudah ada request pending
    $stmt = $conn->prepare('SELECT id FROM ijazah_reupload_request 
                           WHERE siswa_id = ? AND status = "pending"');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing request
        $stmt->close();
        $stmt = $conn->prepare('UPDATE ijazah_reupload_request 
                               SET catatan_admin = ?, admin_id = ?, updated_at = NOW()
                               WHERE siswa_id = ? AND status = "pending"');
        $stmt->bind_param('sii', $catatan, $admin_id, $siswa_id);
    } else {
        // Create new request
        $stmt->close();
        $stmt = $conn->prepare('INSERT INTO ijazah_reupload_request 
                               (siswa_id, catatan_admin, ijazah_lama, admin_id) 
                               VALUES (?, ?, ?, ?)');
        $stmt->bind_param('issi', $siswa_id, $catatan, $ijazah_lama, $admin_id);
    }

    if (!$stmt->execute()) {
        throw new Exception('Gagal membuat request re-upload: ' . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Request re-upload ijazah berhasil dikirim ke siswa';

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
