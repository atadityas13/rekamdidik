<?php
/**
 * API: Save Single Field Data + Verified Flag (Bagian A)
 * POST /api/save-field-verified.php
 * 
 * Dipanggil ketika user centang checkbox verified
 * Simpan data field + set verified flag = 1
 */

header('Content-Type: application/json');

require_once '../config/Database.php';

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $siswa_id = isset($_POST['siswa_id']) ? intval($_POST['siswa_id']) : 0;
    $field_name = isset($_POST['field_name']) ? trim($_POST['field_name']) : '';
    $field_value = isset($_POST['field_value']) ? trim($_POST['field_value']) : '';
    $verified_flag = isset($_POST['verified_flag']) ? trim($_POST['verified_flag']) : '';
    $is_verified = isset($_POST['is_verified']) ? intval($_POST['is_verified']) : 0;
    
    if (empty($siswa_id)) {
        throw new Exception('ID Siswa tidak valid');
    }
    
    if (empty($verified_flag)) {
        throw new Exception('Verified flag tidak valid');
    }

    $db = new Database();
    $conn = $db->connect();
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    $conn->begin_transaction();

    try {
        // Validate field names to prevent SQL injection
        $valid_field_names = [
            'nik_kk', 'nama_kk', 'tempat_lahir_kk', 'tanggal_lahir_kk', 'jenis_kelamin_kk',
            'nama_ibu_kk', 'nama_ayah_kk',
            'nama_ijazah', 'tempat_lahir_ijazah', 'tanggal_lahir_ijazah', 'jenis_kelamin_ijazah',
            'nama_ayah_ijazah'
        ];
        
        $valid_verified_flags = [
            'nik_kk_verified', 'nama_kk_verified', 'tempat_lahir_kk_verified',
            'tanggal_lahir_kk_verified', 'jenis_kelamin_kk_verified', 'nama_ibu_kk_verified',
            'nama_ayah_kk_verified', 'nisn_verified', 'nama_ijazah_verified',
            'tempat_lahir_ijazah_verified', 'tanggal_lahir_ijazah_verified',
            'jenis_kelamin_ijazah_verified', 'nama_ayah_ijazah_verified'
        ];
        
        // Validate verified flag
        if (!in_array($verified_flag, $valid_verified_flags)) {
            throw new Exception('Invalid verified flag: ' . $verified_flag);
        }
        
        // Check if field_name is provided and valid
        $has_editable_field = !empty($field_name) && in_array($field_name, $valid_field_names);

        // 1. Get old value for history (only if has editable field)
        $old_value = null;
        if ($is_verified && $has_editable_field) {
            $stmt = $conn->prepare("SELECT `$field_name` FROM siswa WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $siswa_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $old_value = $row[$field_name];
                }
                $stmt->close();
            }
        }

        // 2. Update data field + verified flag (or just verified flag)
        if ($is_verified && $has_editable_field) {
            // Save field data + verified flag
            $stmt = $conn->prepare("UPDATE siswa SET `$field_name` = ?, `$verified_flag` = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('sii', $field_value, $is_verified, $siswa_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            $stmt->close();
            
            // 3. Save history if value changed
            if ($old_value !== $field_value && !empty($field_value)) {
                $stmt = $conn->prepare('INSERT INTO history_perbaikan (siswa_id, field_name, nilai_sebelum, nilai_sesudah, created_at) VALUES (?, ?, ?, ?, NOW())');
                if ($stmt) {
                    $stmt->bind_param('isss', $siswa_id, $field_name, $old_value, $field_value);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } else {
            // Just update verified flag (either checking field without edit, or unchecking)
            $stmt = $conn->prepare("UPDATE siswa SET `$verified_flag` = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('ii', $is_verified, $siswa_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            $stmt->close();
        }

        // 4. Check if ALL fields are verified
        $verified_fields = [
            'nik_kk_verified', 'nama_kk_verified', 'tempat_lahir_kk_verified',
            'tanggal_lahir_kk_verified', 'jenis_kelamin_kk_verified', 'nama_ibu_kk_verified',
            'nama_ayah_kk_verified', 'nisn_verified', 'nama_ijazah_verified',
            'tempat_lahir_ijazah_verified', 'tanggal_lahir_ijazah_verified',
            'jenis_kelamin_ijazah_verified', 'nama_ayah_ijazah_verified'
        ];
        
        $check_sql = "SELECT " . implode(', ', $verified_fields) . " FROM siswa WHERE id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $all_verified = true;
        foreach ($verified_fields as $vf) {
            if (empty($row[$vf])) {
                $all_verified = false;
                break;
            }
        }
        
        // 5. Check if Bagian B sudah ada data
        $stmt = $conn->prepare('SELECT id FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $bagian_b_exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        
        // 6. Update status jika semua verified DAN Bagian B sudah diisi
        if ($all_verified && $bagian_b_exists) {
            $status = 'sudah';
            $stmt = $conn->prepare('UPDATE siswa SET verval_status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('si', $status, $siswa_id);
            $stmt->execute();
            $stmt->close();
            
            $response['status_updated'] = 'sudah';
        }

        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = $is_verified ? 'Data berhasil disimpan dan diverifikasi' : 'Verifikasi dibatalkan';
        $response['all_verified'] = $all_verified;
        $response['bagian_b_complete'] = $bagian_b_exists;
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
