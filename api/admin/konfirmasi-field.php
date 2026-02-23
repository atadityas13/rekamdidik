<?php
/**
 * API: Konfirmasi Field Verval (Admin)
 * POST /api/admin/konfirmasi-field.php
 * 
 * Body: {
 *   siswa_id: int,
 *   field_name: string,
 *   action: 'approve' | 'need_document' | 'need_confirmation' | 'need_edit',
 *   catatan: string (optional)
 * }
 * 
 * Actions:
 * - approve: Setujui field
 * - need_document: Minta berkas pendukung (upload file wajib)
 * - need_confirmation: Minta konfirmasi/penjelasan saja (tanpa file)
 * - need_edit: Minta edit data + berkas pendukung
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check admin login
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized: Admin login required');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['siswa_id']) || !isset($input['field_name']) || !isset($input['action'])) {
        throw new Exception('Parameter tidak lengkap');
    }

    $siswa_id = intval($input['siswa_id']);
    $field_name = $input['field_name'];
    $action = $input['action']; // 'approve', 'need_document', 'need_confirmation', 'need_edit'
    $catatan = isset($input['catatan']) ? trim($input['catatan']) : null;
    $admin_id = $_SESSION['admin_id'] ?? null;

    if (!in_array($action, ['approve', 'need_document', 'need_confirmation', 'need_edit'])) {
        throw new Exception('Action tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();

    $conn->begin_transaction();

    // Set status dan tipe_konfirmasi berdasarkan action
    if ($action === 'approve') {
        $status = 'approved';
        $tipe_konfirmasi = null;
    } else {
        $status = $action; // need_document, need_confirmation, need_edit
        $tipe_konfirmasi = $action;
    }

    // Check apakah sudah ada record konfirmasi untuk field ini
    $stmt = $conn->prepare('SELECT id FROM verval_konfirmasi WHERE siswa_id = ? AND field_name = ?');
    $stmt->bind_param('is', $siswa_id, $field_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // Update existing record
        $stmt = $conn->prepare('UPDATE verval_konfirmasi 
                                SET status = ?, tipe_konfirmasi = ?, catatan_admin = ?, admin_id = ?, 
                                    pesan_siswa = NULL, nilai_baru_siswa = NULL, berkas_pendukung = NULL,
                                    updated_at = NOW()
                                WHERE id = ?');
        $stmt->bind_param('sssii', $status, $tipe_konfirmasi, $catatan, $admin_id, $existing['id']);
    } else {
        // Insert new record
        $stmt = $conn->prepare('INSERT INTO verval_konfirmasi 
                                (siswa_id, field_name, status, tipe_konfirmasi, catatan_admin, admin_id) 
                                VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssi', $siswa_id, $field_name, $status, $tipe_konfirmasi, $catatan, $admin_id);
    }

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan konfirmasi: ' . $stmt->error);
    }
    $stmt->close();

    // Update verval_approval_status siswa
    // Cek apakah ada field yang masih perlu tindakan siswa atau admin
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM verval_konfirmasi 
                           WHERE siswa_id = ? 
                           AND status IN ("need_document", "need_confirmation", "need_edit")');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $need_action_count = $row['count'];
    $stmt->close();

    // Cek apakah ada yang sudah direspon siswa tapi belum direview
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM verval_konfirmasi 
                           WHERE siswa_id = ? AND status = "student_responded"');
    $stmt->bind_param('i', $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $responded_count = $row['count'];
    $stmt->close();

    // Set approval status
    $approval_status = 'pending';
    if ($need_action_count > 0) {
        $approval_status = 'need_confirmation';
    } else if ($responded_count > 0) {
        $approval_status = 'pending'; // Masih ada yang perlu direview admin
    } else {
        // Cek apakah semua field sudah approved
        $stmt = $conn->prepare('SELECT COUNT(*) as count FROM verval_konfirmasi 
                               WHERE siswa_id = ? AND status != "approved"');
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $not_approved_count = $row['count'];
        $stmt->close();

        if ($not_approved_count === 0) {
            // Cek jumlah total field yang harus dikonfirmasi
            $stmt = $conn->prepare('SELECT COUNT(*) as count FROM verval_konfirmasi WHERE siswa_id = ?');
            $stmt->bind_param('i', $siswa_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $total_konfirmasi = $row['count'];
            $stmt->close();

            // Minimal 15 field harus dikonfirmasi (sesuai jumlah field penting)
            if ($total_konfirmasi >= 15) {
                $approval_status = 'approved';
            }
        }
    }

    $stmt = $conn->prepare('UPDATE siswa SET verval_approval_status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->bind_param('si', $approval_status, $siswa_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $conn->close();

    $response['success'] = true;
    $response['message'] = $action === 'approve' 
        ? 'Field berhasil disetujui' 
        : 'Permintaan berkas pendukung berhasil dikirim';
    $response['approval_status'] = $approval_status;

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
