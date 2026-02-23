<?php
/**
 * API: Count Pengajuan Pembatalan yang Menunggu
 * GET /api/admin/count-pengajuan-menunggu.php
 * 
 * Menghitung jumlah pengajuan pembatalan yang statusnya "menunggu"
 */

session_start();

header('Content-Type: application/json');

require_once '../../config/Database.php';

$response = ['success' => false, 'count' => 0];

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        throw new Exception('Unauthorized');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed');
    }

    $db = new Database();
    $conn = $db->connect();
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Count pengajuan dengan status menunggu
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pengajuan_pembatalan WHERE status = 'menunggu'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['count'] = intval($row['total']);

} catch (Exception $e) {
    $response['success'] = false;
    $response['count'] = 0;
}

echo json_encode($response);
?>
