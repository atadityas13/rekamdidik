<?php
/**
 * API: Get List Pengajuan Pembatalan (Admin)
 * GET /api/admin/get-pengajuan-pembatalan.php
 * 
 * Mengambil daftar pengajuan pembatalan verval
 */

session_start();

header('Content-Type: application/json');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    // Check if admin is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized: Admin login required');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed');
    }

    $status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all'; // all, menunggu, disetujui, ditolak

    $db = new Database();
    $conn = $db->connect();
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Build query based on filter
    $sql = "SELECT p.*, s.nisn, s.nama_kk, s.nama_ijazah, a.username as admin_username 
            FROM pengajuan_pembatalan p 
            JOIN siswa s ON p.siswa_id = s.id 
            LEFT JOIN admin_users a ON p.admin_id = a.id";
    
    if ($status_filter !== 'all') {
        $sql .= " WHERE p.status = ?";
    }
    
    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    
    if ($status_filter !== 'all') {
        $stmt->bind_param('s', $status_filter);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pengajuan_list = [];
    while ($row = $result->fetch_assoc()) {
        $pengajuan_list[] = $row;
    }
    
    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['data'] = $pengajuan_list;
    $response['count'] = count($pengajuan_list);

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
