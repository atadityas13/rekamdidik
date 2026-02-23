<?php
/**
 * API: Get Perubahan Data untuk Kolom Tabel Admin
 * GET /api/admin/get-changes.php?id=SISWA_ID
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => '', 'changes' => []];

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID siswa tidak ditemukan');
    }

    $siswa_id = intval($_GET['id']);
    
    $db = new Database();
    $conn = $db->connect();

    // Get latest changes (max 3)
    $query = "SELECT field_name FROM history_perbaikan 
              WHERE siswa_id = ? 
              ORDER BY tanggal_perbaikan DESC 
              LIMIT 3";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $changes = [];
    while ($row = $result->fetch_assoc()) {
        // Translate field names to Indonesian
        $field_map = [
            'nama_kk' => 'Nama',
            'nik_kk' => 'NIK',
            'tanggal_lahir_kk' => 'Tanggal Lahir',
            'tempat_lahir_kk' => 'Tempat Lahir',
            'jenis_kelamin_kk' => 'Jenis Kelamin',
            'nama_ibu_kk' => 'Nama Ibu',
            'nama_ayah_kk' => 'Nama Ayah',
            'nama_ijazah' => 'Nama Ijazah',
            'tanggal_lahir_ijazah' => 'Tanggal Lahir Ijazah',
            'tempat_lahir_ijazah' => 'Tempat Lahir Ijazah'
        ];
        
        $field_display = isset($field_map[$row['field_name']]) 
            ? $field_map[$row['field_name']] 
            : $row['field_name'];
        
        $changes[] = $field_display;
    }

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['changes'] = $changes;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
