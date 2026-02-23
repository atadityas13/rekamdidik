<?php
/**
 * API: Update Verval Jenjang Sebelumnya
 * POST /api/update-verval.php
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

    $siswa_id = isset($_POST['siswa_id']) ? intval($_POST['siswa_id']) : 0;
    $nama_sd = isset($_POST['nama_sd']) ? trim($_POST['nama_sd']) : '';
    $tahun_ajaran = isset($_POST['tahun_ajaran_kelulusan']) ? trim($_POST['tahun_ajaran_kelulusan']) : '';
    $nip_kepala = isset($_POST['nip_kepala_sekolah']) ? trim($_POST['nip_kepala_sekolah']) : '';
    $nama_kepala = isset($_POST['nama_kepala_sekolah']) ? trim($_POST['nama_kepala_sekolah']) : '';
    $nomor_seri = isset($_POST['nomor_seri_ijazah']) ? trim($_POST['nomor_seri_ijazah']) : '';
    $tanggal_terbit = isset($_POST['tanggal_terbit_ijazah']) ? trim($_POST['tanggal_terbit_ijazah']) : '';

    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();

    // Check if siswa exists
    $stmt = $conn->prepare('SELECT id FROM siswa WHERE id = ?');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Data siswa tidak ditemukan');
    }
    $stmt->close();

    // Handle file upload
    $dokumen_ijazah = null;
    if (isset($_FILES['dokumen_ijazah']) && $_FILES['dokumen_ijazah']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['dokumen_ijazah'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 1 * 1024 * 1024; // 1MB

        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Format file harus JPG, JPEG, atau PNG');
        }

        if ($file['size'] > $max_size) {
            throw new Exception('Ukuran file maksimal 1MB');
        }

        // Create unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $dokumen_ijazah = 'ijazah_' . $siswa_id . '_' . time() . '.' . $ext;
        $upload_path = '../uploads/ijazah/' . $dokumen_ijazah;

        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Gagal upload file');
        }
    }

    // Check if verval data exists
    $stmt = $conn->prepare('SELECT id FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($exists) {
        // Update existing
        if ($dokumen_ijazah) {
            $stmt = $conn->prepare('UPDATE verval_jenjang_sebelumnya SET nama_sd = ?, tahun_ajaran_kelulusan = ?, nip_kepala_sekolah = ?, nama_kepala_sekolah = ?, nomor_seri_ijazah = ?, tanggal_terbit_ijazah = ?, dokumen_ijazah = ? WHERE siswa_id = ?');
            $stmt->bind_param('sssssssi', $nama_sd, $tahun_ajaran, $nip_kepala, $nama_kepala, $nomor_seri, $tanggal_terbit, $dokumen_ijazah, $siswa_id);
        } else {
            $stmt = $conn->prepare('UPDATE verval_jenjang_sebelumnya SET nama_sd = ?, tahun_ajaran_kelulusan = ?, nip_kepala_sekolah = ?, nama_kepala_sekolah = ?, nomor_seri_ijazah = ?, tanggal_terbit_ijazah = ? WHERE siswa_id = ?');
            $stmt->bind_param('ssssssi', $nama_sd, $tahun_ajaran, $nip_kepala, $nama_kepala, $nomor_seri, $tanggal_terbit, $siswa_id);
        }
    } else {
        // Create new
        $stmt = $conn->prepare('INSERT INTO verval_jenjang_sebelumnya (siswa_id, nama_sd, tahun_ajaran_kelulusan, nip_kepala_sekolah, nama_kepala_sekolah, nomor_seri_ijazah, tanggal_terbit_ijazah, dokumen_ijazah) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssss', $siswa_id, $nama_sd, $tahun_ajaran, $nip_kepala, $nama_kepala, $nomor_seri, $tanggal_terbit, $dokumen_ijazah);
    }

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan data verval: ' . $stmt->error);
    }

    $stmt->close();

    // Update verval status
    $new_status = 'sudah';
    $stmt = $conn->prepare('UPDATE siswa SET verval_status = ? WHERE id = ?');
    $stmt->bind_param('si', $new_status, $siswa_id);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Data verval berhasil disimpan';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
