<?php
/**
 * API: Check Status Konfirmasi Verval (Siswa)
 * GET /api/check-konfirmasi-status.php?siswa_id=X
 * 
 * Return: Status konfirmasi dan field yang perlu berkas pendukung
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if (!isset($_GET['siswa_id'])) {
        throw new Exception('Siswa ID tidak ditemukan');
    }

    $siswa_id = intval($_GET['siswa_id']);
    
    $db = new Database();
    $conn = $db->connect();

    // Get verval approval status
    $stmt = $conn->prepare('SELECT verval_status, verval_approval_status, catatan_konfirmasi 
                           FROM siswa WHERE id = ?');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Siswa tidak ditemukan');
    }
    
    $siswa = $result->fetch_assoc();
    $stmt->close();

    // Get fields yang perlu tindakan siswa
    $stmt = $conn->prepare('SELECT field_name, status, tipe_konfirmasi, catatan_admin, 
                                   berkas_pendukung, pesan_siswa, nilai_baru_siswa, updated_at
                           FROM verval_konfirmasi 
                           WHERE siswa_id = ? 
                           AND status IN ("need_document", "need_confirmation", "need_edit", 
                                         "document_uploaded", "student_responded")
                           ORDER BY field_name');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $fields_need_action = [];
    while ($row = $result->fetch_assoc()) {
        $fields_need_action[] = $row;
    }
    $stmt->close();

    // Get all konfirmasi status
    $stmt = $conn->prepare('SELECT field_name, status, tipe_konfirmasi, catatan_admin, updated_at
                           FROM verval_konfirmasi 
                           WHERE siswa_id = ?
                           ORDER BY field_name');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $all_konfirmasi = [];
    while ($row = $result->fetch_assoc()) {
        $all_konfirmasi[$row['field_name']] = $row;
    }
    $stmt->close();

    $conn->close();

    $response['success'] = true;
    $response['data'] = [
        'verval_status' => $siswa['verval_status'],
        'approval_status' => $siswa['verval_approval_status'],
        'catatan_konfirmasi' => $siswa['catatan_konfirmasi'],
        'fields_need_action' => $fields_need_action,
        'all_konfirmasi' => $all_konfirmasi
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
