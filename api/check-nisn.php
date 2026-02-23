<?php
/**
 * API: Cek NISN dan Ambil Data Siswa
 * POST /api/check-nisn.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $nisn = isset($input['nisn']) ? trim($input['nisn']) : '';

    if (empty($nisn)) {
        throw new Exception('NISN harus diisi');
    }

    if (strlen($nisn) !== 10) {
        throw new Exception('NISN harus 10 digit');
    }

    if (!ctype_digit($nisn)) {
        throw new Exception('NISN hanya boleh angka');
    }

    // Connect to database
    $db = new Database();
    $conn = $db->connect();

    // Check if siswa exists
    $stmt = $conn->prepare('SELECT * FROM siswa WHERE nisn = ?');
    $stmt->bind_param('s', $nisn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('NISN tidak ditemukan dalam database');
    }

    $siswa = $result->fetch_assoc();
    $stmt->close();

    // Get verval data if exists
    $stmt = $conn->prepare('SELECT * FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
    $stmt->bind_param('i', $siswa['id']);
    $stmt->execute();
    $verval_result = $stmt->get_result();
    $siswa['verval_data'] = $verval_result->num_rows > 0 ? $verval_result->fetch_assoc() : null;
    $stmt->close();

    // Get history perbaikan
    $stmt = $conn->prepare('SELECT * FROM history_perbaikan WHERE siswa_id = ? ORDER BY tanggal_perbaikan DESC LIMIT 10');
    $stmt->bind_param('i', $siswa['id']);
    $stmt->execute();
    $history_result = $stmt->get_result();
    $siswa['history_perbaikan'] = [];
    while ($row = $history_result->fetch_assoc()) {
        $siswa['history_perbaikan'][] = $row;
    }
    $stmt->close();

    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Data siswa ditemukan';
    $response['data'] = $siswa;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
