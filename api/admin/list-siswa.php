<?php
/**
 * API: Get Daftar Siswa untuk Admin
 * GET /api/admin/list-siswa.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    $db = new Database();
    $conn = $db->connect();

    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit_param = isset($_GET['limit']) ? trim((string) $_GET['limit']) : '20';
    $is_all = ($limit_param === 'all');
    $limit = $is_all ? 0 : intval($limit_param);
    if (!$is_all && $limit <= 0) {
        $limit = 20;
    }
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    $where = ['1=1'];
    $params = [];

    if (!empty($search)) {
        $where[] = "(nisn LIKE ? OR nama_kk LIKE ? OR nama_ijazah LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    if (!empty($status)) {
        $where[] = "verval_status = ?";
        $params[] = $status;
    }

    $where_clause = implode(' AND ', $where);

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM siswa WHERE $where_clause";
    if (!empty($params)) {
        $stmt = $conn->prepare($count_query);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare($count_query);
        $stmt->execute();
    }

    $count_result = $stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $stmt->close();

    // Get data
    $query = "SELECT 
                id, nisn, nama_kk, nama_ijazah, nik_kk,
                tempat_lahir_kk, tanggal_lahir_kk, jenis_kelamin_kk,
                nama_ibu_kk, nama_ayah_kk,
                nama_ijazah, tempat_lahir_ijazah, tanggal_lahir_ijazah,
                jenis_kelamin_ijazah, nama_ayah_ijazah,
                verval_status, verval_approval_status, catatan_konfirmasi,
                created_at, updated_at
              FROM siswa
              WHERE $where_clause
              ORDER BY created_at DESC";

    if (!$is_all) {
        $query .= " LIMIT $limit OFFSET $offset";
    }

    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare($query);
        $stmt->execute();
    }

    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        // Get verval data
        $verval_stmt = $conn->prepare('SELECT dokumen_ijazah FROM verval_jenjang_sebelumnya WHERE siswa_id = ?');
        $verval_stmt->bind_param('i', $row['id']);
        $verval_stmt->execute();
        $verval_result = $verval_stmt->get_result();
        $row['dokumen_ijazah'] = $verval_result->num_rows > 0 ? $verval_result->fetch_assoc()['dokumen_ijazah'] : null;
        $verval_stmt->close();

        $data[] = $row;
    }

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Data siswa berhasil diambil';
    $response['data'] = $data;
    $effective_limit = $is_all ? ($total > 0 ? $total : 1) : $limit;
    $response['pagination'] = [
        'page' => $page,
        'limit' => $is_all ? 'all' : $limit,
        'total' => $total,
        'total_pages' => $is_all ? 1 : ceil($total / $effective_limit)
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
