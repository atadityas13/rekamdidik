<?php
/**
 * API: Submit Pengajuan Pembatalan Verval (Siswa)
 * POST /api/submit-pembatalan.php
 * 
 * Siswa mengajukan pembatalan verval dengan alasan
 */

header('Content-Type: application/json');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $siswa_id = isset($_POST['siswa_id']) ? intval($_POST['siswa_id']) : 0;
    $alasan = isset($_POST['alasan']) ? trim($_POST['alasan']) : '';
    
    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }
    
    if (empty($alasan)) {
        throw new Exception('Alasan pembatalan harus diisi');
    }

    if (strlen($alasan) < 20) {
        throw new Exception('Alasan pembatalan minimal 20 karakter');
    }

    $db = new Database();
    $conn = $db->connect();
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    $conn->begin_transaction();

    try {
        // 1. Cek apakah siswa sudah verval
        $stmt = $conn->prepare("SELECT verval_status FROM siswa WHERE id = ?");
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Data siswa tidak ditemukan');
        }
        
        $siswa = $result->fetch_assoc();
        $stmt->close();
        
        if ($siswa['verval_status'] !== 'sudah') {
            throw new Exception('Hanya siswa yang sudah verval yang dapat mengajukan pembatalan');
        }

        // 2. Cek apakah sudah ada pengajuan yang sedang menunggu
        $stmt = $conn->prepare("SELECT id, status FROM pengajuan_pembatalan WHERE siswa_id = ? AND status = 'menunggu'");
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Anda masih memiliki pengajuan pembatalan yang sedang menunggu persetujuan admin');
        }
        $stmt->close();

        // 3. Insert pengajuan pembatalan
        $stmt = $conn->prepare('INSERT INTO pengajuan_pembatalan (siswa_id, alasan, status, created_at) VALUES (?, ?, "menunggu", NOW())');
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('is', $siswa_id, $alasan);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Pengajuan pembatalan verval berhasil dikirim. Mohon menunggu persetujuan dari admin.';
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
