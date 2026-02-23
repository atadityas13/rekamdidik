<?php
/**
 * API: Update Verified Checkbox
 * POST /api/update-verified.php
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
    $value = isset($input['value']) ? (int)!!$input['value'] : 1;

    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }

    if (empty($field_name)) {
        throw new Exception('Nama field harus diisi');
    }

    $verified_fields = [
        'nik_kk_verified',
        'nisn_verified',
        'nama_kk_verified',
        'nama_ijazah_verified',
        'tempat_lahir_kk_verified',
        'tempat_lahir_ijazah_verified',
        'tanggal_lahir_kk_verified',
        'tanggal_lahir_ijazah_verified',
        'jenis_kelamin_kk_verified',
        'jenis_kelamin_ijazah_verified',
        'nama_ibu_kk_verified',
        'nama_ayah_kk_verified',
        'nama_ayah_ijazah_verified'
    ];

    if (!in_array($field_name, $verified_fields, true)) {
        throw new Exception('Field verifikasi tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT id FROM siswa WHERE id = ?");
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Data siswa tidak ditemukan');
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE siswa SET $field_name = ? WHERE id = ?");
    $stmt->bind_param('ii', $value, $siswa_id);
    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan verifikasi: ' . $stmt->error);
    }
    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['message'] = $value === 1 ? 'Verifikasi berhasil disimpan' : 'Verifikasi dibatalkan';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
