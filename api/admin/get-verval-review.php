<?php
/**
 * API: Get Data Verval untuk Review Admin
 * GET /api/admin/get-verval-review.php?siswa_id=X
 * 
 * Return: Data siswa lengkap + status konfirmasi per field
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Check admin login
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized: Admin login required');
    }

    if (!isset($_GET['siswa_id'])) {
        throw new Exception('Siswa ID tidak ditemukan');
    }

    $siswa_id = intval($_GET['siswa_id']);
    
    $db = new Database();
    $conn = $db->connect();

    // 1. Get data siswa
    $query = "SELECT s.* 
              FROM siswa s
              WHERE s.id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Siswa tidak ditemukan');
    }
    
    $siswa = $result->fetch_assoc();
    $stmt->close();
    
    // Get data verval jenjang sebelumnya (jika ada)
    $stmt = $conn->prepare("SELECT * FROM verval_jenjang_sebelumnya WHERE siswa_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $result_verval = $stmt->get_result();
        if ($result_verval->num_rows > 0) {
            $verval_data = $result_verval->fetch_assoc();
            // Merge ke siswa data
            $siswa = array_merge($siswa, $verval_data);
        }
        $stmt->close();
    }

    // 2. Get status konfirmasi untuk semua field yang perlu dikonfirmasi
    $fields_to_confirm = [
        'nik_kk', 'nama_kk', 'tempat_lahir_kk', 'tanggal_lahir_kk',
        'jenis_kelamin_kk', 'nama_ibu_kk', 'nama_ayah_kk',
        'nisn', 'nama_ijazah', 'tempat_lahir_ijazah', 'tanggal_lahir_ijazah',
        'jenis_kelamin_ijazah', 'nama_ayah_ijazah',
        'jenjang_sebelumnya', 'nama_sekolah_asal', 'npsn_sekolah_asal',
        'nomor_peserta_un', 'nomor_seri_ijazah', 'tahun_lulus'
    ];

    $konfirmasi_status = [];
    foreach ($fields_to_confirm as $field) {
        $stmt = $conn->prepare('SELECT status, tipe_konfirmasi, catatan_admin, 
                                       berkas_pendukung, pesan_siswa, nilai_baru_siswa, updated_at 
                                FROM verval_konfirmasi 
                                WHERE siswa_id = ? AND field_name = ?
                                ORDER BY updated_at DESC LIMIT 1');
        
        if (!$stmt) {
            // Jika tabel belum ada, set semua ke pending
            $konfirmasi_status[$field] = [
                'status' => 'pending',
                'tipe_konfirmasi' => null,
                'catatan_admin' => null,
                'berkas_pendukung' => null,
                'pesan_siswa' => null,
                'nilai_baru_siswa' => null,
                'updated_at' => null
            ];
            continue;
        }
        
        $stmt->bind_param('is', $siswa_id, $field);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $konfirmasi_status[$field] = $row;
        } else {
            // Default status pending jika belum ada record
            $konfirmasi_status[$field] = [
                'status' => 'pending',
                'tipe_konfirmasi' => null,
                'catatan_admin' => null,
                'berkas_pendukung' => null,
                'pesan_siswa' => null,
                'nilai_baru_siswa' => null,
                'updated_at' => null
            ];
        }
        $stmt->close();
    }

    // 3. Hitung statistik konfirmasi
    $stats = [
        'pending' => 0,
        'approved' => 0,
        'need_document' => 0,
        'need_confirmation' => 0,
        'need_edit' => 0,
        'document_uploaded' => 0,
        'student_responded' => 0
    ];

    foreach ($konfirmasi_status as $status_data) {
        $status = $status_data['status'];
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }

    $conn->close();

    $response['success'] = true;
    $response['data'] = [
        'siswa' => $siswa,
        'konfirmasi' => $konfirmasi_status,
        'stats' => $stats
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log error untuk debugging (optional - hapus di production)
    error_log('Error in get-verval-review.php: ' . $e->getMessage());
    
    // Jika database connection error, tutup koneksi
    if (isset($conn) && $conn) {
        $conn->close();
    }
}

echo json_encode($response);
?>
