<?php
/**
 * API: Check Status Pengajuan Pembatalan Siswa
 * GET /api/check-pengajuan-pembatalan.php?siswa_id=123
 * 
 * Cek apakah siswa memiliki pengajuan pembatalan yang sedang dalam proses
 */

header('Content-Type: application/json');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed');
    }

    $siswa_id = isset($_GET['siswa_id']) ? intval($_GET['siswa_id']) : 0;
    
    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Get latest pengajuan for this siswa
    $stmt = $conn->prepare("SELECT * FROM pengajuan_pembatalan WHERE siswa_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $pengajuan = $result->fetch_assoc();
        $response['data'] = $pengajuan;
        $response['has_pengajuan'] = true;
    } else {
        $response['has_pengajuan'] = false;
    }
    
    $stmt->close();
    $conn->close();

    $response['success'] = true;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
