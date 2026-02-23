<?php
/**
 * API: Final Approval Verval (Admin)
 * POST /api/admin/final-approve-verval.php
 * 
 * Body: {
 *   siswa_id: int,
 *   catatan: string (optional)
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

    if (!isset($input['siswa_id'])) {
        throw new Exception('Siswa ID tidak ditemukan');
    }

    $siswa_id = intval($input['siswa_id']);
    $catatan = isset($input['catatan']) ? trim($input['catatan']) : null;
    $admin_id = $_SESSION['admin_id'] ?? null;

    $db = new Database();
    $conn = $db->connect();

    $conn->begin_transaction();

    // Validasi: Cek apakah masih ada field yang belum approved
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM verval_konfirmasi 
                           WHERE siswa_id = ? AND status != "approved"');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $not_approved = $row['count'];
    $stmt->close();

    if ($not_approved > 0) {
        throw new Exception("Masih ada {$not_approved} field yang belum disetujui");
    }

    // Set approval status ke approved
    $stmt = $conn->prepare('UPDATE siswa 
                           SET verval_approval_status = "approved", 
                               catatan_konfirmasi = ?,
                               updated_at = NOW() 
                           WHERE id = ?');
    $stmt->bind_param('si', $catatan, $siswa_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal menyetujui verval: ' . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Verval berhasil disetujui sepenuhnya';

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
