<?php
/**
 * API: Check Status Re-upload Ijazah (Siswa)
 * GET /api/check-reupload-ijazah.php?siswa_id=X
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if (!isset($_GET['siswa_id'])) {
        throw new Exception('Siswa ID tidak ditemukan');
    }

    $siswa_id = intval($_GET['siswa_id']);
    
    $db = new Database();
    $conn = $db->connect();

    // Get pending request
    $stmt = $conn->prepare('SELECT id, catatan_admin, created_at 
                           FROM ijazah_reupload_request 
                           WHERE siswa_id = ? AND status = "pending"
                           ORDER BY created_at DESC LIMIT 1');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $request = $result->fetch_assoc();
        $response['success'] = true;
        $response['has_request'] = true;
        $response['data'] = $request;
    } else {
        $response['success'] = true;
        $response['has_request'] = false;
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
