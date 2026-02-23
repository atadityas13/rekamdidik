<?php
/**
 * API: Cancel/Batalkan Verval untuk Siswa
 * POST /api/admin/cancel-verval.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id'])) {
        throw new Exception('ID siswa tidak ditemukan');
    }

    $siswa_id = intval($input['id']);
    
    $db = new Database();
    $conn = $db->connect();

    // Start transaction
    $conn->begin_transaction();

    // Reset verval_status ke 'belum'
    $update_query = "UPDATE siswa SET verval_status = 'belum' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('i', $siswa_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengubah status verval');
    }
    $stmt->close();

    // Reset semua verified flags ke 0
    $verified_fields = [
        'nik_kk_verified', 'nama_kk_verified', 'tempat_lahir_kk_verified',
        'tanggal_lahir_kk_verified', 'jenis_kelamin_kk_verified', 'nama_ibu_kk_verified',
        'nama_ayah_kk_verified', 'nisn_verified', 'nama_ijazah_verified',
        'tempat_lahir_ijazah_verified', 'tanggal_lahir_ijazah_verified',
        'jenis_kelamin_ijazah_verified', 'nama_ayah_ijazah_verified'
    ];

    $set_clause = implode(' = 0, ', $verified_fields) . ' = 0';
    $reset_query = "UPDATE siswa SET $set_clause WHERE id = ?";
    $stmt = $conn->prepare($reset_query);
    $stmt->bind_param('i', $siswa_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal mereset verifikasi field');
    }
    $stmt->close();

    $conn->commit();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Verval berhasil dibatalkan';

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
