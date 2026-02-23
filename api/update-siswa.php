<?php
/**
 * API: Update Data Siswa
 * POST /api/update-siswa.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $siswa_id = isset($input['siswa_id']) ? intval($input['siswa_id']) : 0;
    $field_name = isset($input['field_name']) ? trim($input['field_name']) : '';
    $nilai_baru = isset($input['nilai_baru']) ? $input['nilai_baru'] : '';

    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }

    if (empty($field_name)) {
        throw new Exception('Nama field harus diisi');
    }

    // List field yang bisa diupdate
    $fields_allowed = ['nik_kk', 'nama_kk', 'tempat_lahir_kk', 'tanggal_lahir_kk', 
                       'jenis_kelamin_kk', 'nama_ibu_kk', 'nama_ayah_kk',
                       'nama_ijazah', 'tempat_lahir_ijazah', 'tanggal_lahir_ijazah',
                       'jenis_kelamin_ijazah', 'nama_ayah_ijazah'];

    if (!in_array($field_name, $fields_allowed)) {
        throw new Exception('Field tidak valid');
    }

    // Check if field is verified
    $verified_field = $field_name . '_verified';
    if (!in_array($verified_field, $fields_allowed)) {
        $verified_field = null;
    }

    $db = new Database();
    $conn = $db->connect();

    // Get current value
    $stmt = $conn->prepare("SELECT $field_name, $verified_field FROM siswa WHERE id = ?");
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Data siswa tidak ditemukan');
    }

    $row = $result->fetch_assoc();
    $nilai_lama = $row[$field_name];
    $is_verified = $row[$verified_field] ?? false;

    $stmt->close();

    // Check if field is verified
    if ($is_verified) {
        throw new Exception('Field ini sudah diceklis dan tidak bisa diubah');
    }

    // Update field
    $stmt = $conn->prepare("UPDATE siswa SET $field_name = ? WHERE id = ?");
    $stmt->bind_param('si', $nilai_baru, $siswa_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal update data: ' . $stmt->error);
    }

    $stmt->close();

    // Save to history
    $stmt = $conn->prepare('INSERT INTO history_perbaikan (siswa_id, field_name, nilai_sebelum, nilai_sesudah) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $siswa_id, $field_name, $nilai_lama, $nilai_baru);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Data berhasil diupdate';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
