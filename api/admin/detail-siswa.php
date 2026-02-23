<?php
/**
 * API: Get Detail Siswa dan History Perbaikan
 * GET /api/admin/detail-siswa.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $siswa_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (empty($siswa_id)) {
        throw new Exception('ID Siswa harus diisi');
    }

    $db = new Database();
    $conn = $db->connect();

    // Get siswa data
    $stmt = $conn->prepare('SELECT * FROM siswa WHERE id = ?');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Data siswa tidak ditemukan');
    }

    $data = $result->fetch_assoc();
    $stmt->close();

    // Get verval data
    $stmt = $conn->prepare('SELECT * FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $verval_result = $stmt->get_result();
    $data['verval_data'] = $verval_result->num_rows > 0 ? $verval_result->fetch_assoc() : null;
    $stmt->close();

    // Get history perbaikan
    $stmt = $conn->prepare('SELECT * FROM history_perbaikan WHERE siswa_id = ? ORDER BY tanggal_perbaikan DESC');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $history_result = $stmt->get_result();
    $data['history_perbaikan'] = [];

    while ($row = $history_result->fetch_assoc()) {
        $data['history_perbaikan'][] = $row;
    }

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Data detail siswa berhasil diambil';
    $response['data'] = $data;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
