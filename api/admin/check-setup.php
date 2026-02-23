<?php
/**
 * Quick Check: Verifikasi Database Setup
 * Akses: /api/admin/check-setup.php
 * 
 * Untuk cek apakah migration database sudah dijalankan
 */

header('Content-Type: application/json');

require_once '../../config/Database.php';

$response = [
    'success' => true,
    'tables' => [],
    'columns' => [],
    'errors' => []
];

try {
    $db = new Database();
    $conn = $db->connect();
    
    // 1. Cek tabel verval_konfirmasi
    $result = $conn->query("SHOW TABLES LIKE 'verval_konfirmasi'");
    $response['tables']['verval_konfirmasi'] = $result->num_rows > 0 ? 'EXISTS' : 'NOT FOUND';
    
    // 2. Cek tabel ijazah_reupload_request
    $result = $conn->query("SHOW TABLES LIKE 'ijazah_reupload_request'");
    $response['tables']['ijazah_reupload_request'] = $result->num_rows > 0 ? 'EXISTS' : 'NOT FOUND';
    
    // 3. Cek tabel verval_history
    $result = $conn->query("SHOW TABLES LIKE 'verval_history'");
    $response['tables']['verval_history'] = $result->num_rows > 0 ? 'EXISTS' : 'NOT FOUND';
    
    // 4. Cek kolom verval_approval_status di tabel siswa
    $result = $conn->query("SHOW COLUMNS FROM siswa LIKE 'verval_approval_status'");
    $response['columns']['siswa.verval_approval_status'] = $result->num_rows > 0 ? 'EXISTS' : 'NOT FOUND';
    
    // 5. Cek kolom catatan_konfirmasi di tabel siswa
    $result = $conn->query("SHOW COLUMNS FROM siswa LIKE 'catatan_konfirmasi'");
    $response['columns']['siswa.catatan_konfirmasi'] = $result->num_rows > 0 ? 'EXISTS' : 'NOT FOUND';
    
    // 6. Cek struktur verval_konfirmasi jika ada
    if ($response['tables']['verval_konfirmasi'] === 'EXISTS') {
        $result = $conn->query("SHOW COLUMNS FROM verval_konfirmasi");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        $response['verval_konfirmasi_columns'] = $columns;
        
        // Cek kolom yang diperlukan
        $required_columns = ['tipe_konfirmasi', 'pesan_siswa', 'nilai_baru_siswa'];
        foreach ($required_columns as $col) {
            if (!in_array($col, $columns)) {
                $response['errors'][] = "Column '$col' missing in verval_konfirmasi";
            }
        }
    }
    
    // 7. Summary
    $all_exists = true;
    foreach ($response['tables'] as $table => $status) {
        if ($status !== 'EXISTS') {
            $all_exists = false;
            $response['errors'][] = "Table '$table' not found";
        }
    }
    foreach ($response['columns'] as $col => $status) {
        if ($status !== 'EXISTS') {
            $all_exists = false;
            $response['errors'][] = "Column '$col' not found";
        }
    }
    
    if ($all_exists && count($response['errors']) === 0) {
        $response['message'] = '✅ Setup lengkap! Semua tabel dan kolom sudah ada.';
        $response['ready'] = true;
    } else {
        $response['message'] = '❌ Setup belum lengkap. Jalankan migration SQL dulu!';
        $response['ready'] = false;
        $response['action_required'] = 'Jalankan file: config/verval_konfirmasi.sql di phpMyAdmin';
    }
    
    $conn->close();
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['ready'] = false;
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
