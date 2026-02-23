<?php
/**
 * API: Insert Data Siswa Baru
 * POST /api/insert-siswa.php
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

    // Validasi NISN
    $nisn = isset($input['nisn']) ? trim($input['nisn']) : '';
    if (empty($nisn) || !ctype_digit($nisn) || strlen($nisn) !== 10) {
        throw new Exception('NISN harus 10 digit angka');
    }

    // Validasi data KK
    $nama_kk = isset($input['nama_kk']) ? trim($input['nama_kk']) : '';
    $tempat_lahir_kk = isset($input['tempat_lahir_kk']) ? trim($input['tempat_lahir_kk']) : '';
    $tanggal_lahir_kk = isset($input['tanggal_lahir_kk']) ? trim($input['tanggal_lahir_kk']) : '';
    $jenis_kelamin_kk = isset($input['jenis_kelamin_kk']) ? trim($input['jenis_kelamin_kk']) : '';
    $nama_ibu_kk = isset($input['nama_ibu_kk']) ? trim($input['nama_ibu_kk']) : '';
    $nama_ayah_kk = isset($input['nama_ayah_kk']) ? trim($input['nama_ayah_kk']) : '';

    if (empty($nama_kk) || empty($tempat_lahir_kk) || empty($tanggal_lahir_kk) || 
        empty($jenis_kelamin_kk) || empty($nama_ibu_kk) || empty($nama_ayah_kk)) {
        throw new Exception('Data KK harus diisi lengkap');
    }

    // Data optional
    $nik_kk = isset($input['nik_kk']) ? trim($input['nik_kk']) : null;
    $nama_ijazah = isset($input['nama_ijazah']) ? trim($input['nama_ijazah']) : null;
    $tempat_lahir_ijazah = isset($input['tempat_lahir_ijazah']) ? trim($input['tempat_lahir_ijazah']) : null;
    $tanggal_lahir_ijazah = isset($input['tanggal_lahir_ijazah']) ? trim($input['tanggal_lahir_ijazah']) : null;
    $jenis_kelamin_ijazah = isset($input['jenis_kelamin_ijazah']) ? trim($input['jenis_kelamin_ijazah']) : null;
    $nama_ayah_ijazah = isset($input['nama_ayah_ijazah']) ? trim($input['nama_ayah_ijazah']) : null;
    $verval_status = isset($input['verval_status']) ? trim($input['verval_status']) : 'belum';

    $db = new Database();
    $conn = $db->connect();

    // Check if NISN already exists
    $stmt = $conn->prepare('SELECT id FROM siswa WHERE nisn = ?');
    $stmt->bind_param('s', $nisn);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('NISN sudah terdaftar dalam sistem');
    }
    $stmt->close();

    // Insert new siswa
    $stmt = $conn->prepare('INSERT INTO siswa (
        nisn, nik_kk, nama_kk, tempat_lahir_kk, tanggal_lahir_kk, 
        jenis_kelamin_kk, nama_ibu_kk, nama_ayah_kk,
        nama_ijazah, tempat_lahir_ijazah, tanggal_lahir_ijazah,
        jenis_kelamin_ijazah, nama_ayah_ijazah, verval_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

    $stmt->bind_param(
        'ssssssssssssss',
        $nisn, $nik_kk, $nama_kk, $tempat_lahir_kk, $tanggal_lahir_kk,
        $jenis_kelamin_kk, $nama_ibu_kk, $nama_ayah_kk,
        $nama_ijazah, $tempat_lahir_ijazah, $tanggal_lahir_ijazah,
        $jenis_kelamin_ijazah, $nama_ayah_ijazah, $verval_status
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan data: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Data siswa berhasil disimpan';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
