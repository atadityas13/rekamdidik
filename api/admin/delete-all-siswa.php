<?php
/**
 * API: Delete All Siswa Data
 * DELETE /api/admin/delete-all-siswa.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    $db = new Database();
    $conn = $db->connect();

    // Delete all data from related tables first (foreign key constraints)
    $tables = [
        'history_perbaikan',
        'verval_jenjang_sebelumnya',
        'siswa'
    ];

    foreach ($tables as $table) {
        $del_query = "TRUNCATE TABLE $table";
        if (!$conn->query($del_query)) {
            throw new Exception("Gagal menghapus data dari tabel $table: " . $conn->error);
        }
    }

    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Semua data siswa berhasil dihapus';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
