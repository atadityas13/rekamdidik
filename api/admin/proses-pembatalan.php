<?php
/**
 * API: Proses Pengajuan Pembatalan (Admin)
 * POST /api/admin/proses-pembatalan.php
 * 
 * Admin menyetujui atau menolak pengajuan pembatalan verval
 */

session_start();

header('Content-Type: application/json');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check if admin is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized: Admin login required');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $pengajuan_id = isset($input['pengajuan_id']) ? intval($input['pengajuan_id']) : 0;
    $action = isset($input['action']) ? trim($input['action']) : ''; // 'setujui' atau 'tolak'
    $catatan_admin = isset($input['catatan_admin']) ? trim($input['catatan_admin']) : '';
    $admin_id = isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
    
    if (empty($pengajuan_id)) {
        throw new Exception('ID Pengajuan tidak valid');
    }
    
    if (!in_array($action, ['setujui', 'tolak'])) {
        throw new Exception('Action tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    $conn->begin_transaction();

    try {
        // 1. Cek pengajuan
        $stmt = $conn->prepare("SELECT p.*, s.nisn, s.verval_status FROM pengajuan_pembatalan p JOIN siswa s ON p.siswa_id = s.id WHERE p.id = ?");
        $stmt->bind_param('i', $pengajuan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Pengajuan tidak ditemukan');
        }
        
        $pengajuan = $result->fetch_assoc();
        $stmt->close();
        
        if ($pengajuan['status'] !== 'menunggu') {
            throw new Exception('Pengajuan ini sudah diproses sebelumnya');
        }

        $new_status = $action === 'setujui' ? 'disetujui' : 'ditolak';
        $siswa_id = $pengajuan['siswa_id'];

        // 2. Update status pengajuan
        $stmt = $conn->prepare('UPDATE pengajuan_pembatalan SET status = ?, admin_id = ?, catatan_admin = ?, tanggal_diproses = NOW() WHERE id = ?');
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('sisi', $new_status, $admin_id, $catatan_admin, $pengajuan_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        $stmt->close();

        // 3. Jika disetujui, reset status verval siswa & reset semua verified flags
        if ($action === 'setujui') {
            $stmt = $conn->prepare('UPDATE siswa SET verval_status = "belum", 
                nik_kk_verified = 0, nisn_verified = 0, nama_kk_verified = 0, 
                nama_ijazah_verified = 0, tempat_lahir_kk_verified = 0, 
                tempat_lahir_ijazah_verified = 0, tanggal_lahir_kk_verified = 0, 
                tanggal_lahir_ijazah_verified = 0, jenis_kelamin_kk_verified = 0, 
                jenis_kelamin_ijazah_verified = 0, nama_ibu_kk_verified = 0, 
                nama_ayah_kk_verified = 0, nama_ayah_ijazah_verified = 0, 
                updated_at = NOW() WHERE id = ?');
            
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $siswa_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to reset verval status: ' . $stmt->error);
            }
            $stmt->close();
            
            // Optional: Hapus data verval jenjang sebelumnya
            $stmt = $conn->prepare('DELETE FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
            if ($stmt) {
                $stmt->bind_param('i', $siswa_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = $action === 'setujui' 
            ? 'Pengajuan pembatalan disetujui. Status verval siswa telah direset.'
            : 'Pengajuan pembatalan ditolak.';
        
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
