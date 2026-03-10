<?php
/**
 * API: Update Verval Deadline (Admin)
 * POST /api/admin/update-verval-deadline.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/Database.php';

session_start();

$response = ['success' => false, 'message' => '', 'data' => null];

function formatDeadlineDisplay(DateTime $dt) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $day = (int) $dt->format('j');
    $monthNum = (int) $dt->format('n');
    $year = $dt->format('Y');
    $time = $dt->format('H:i');

    return $day . ' ' . $months[$monthNum] . ' ' . $year . ' Pukul ' . $time . ' WIB';
}

try {
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('User tidak terautentikasi');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $deadline_input = isset($input['deadline']) ? trim((string) $input['deadline']) : '';

    if ($deadline_input === '') {
        throw new Exception('Deadline wajib diisi');
    }

    $tz = new DateTimeZone('Asia/Jakarta');

    // Mendukung format dari input datetime-local: YYYY-MM-DDTHH:MM
    $deadline_dt = DateTime::createFromFormat('Y-m-d\\TH:i', $deadline_input, $tz);
    if (!$deadline_dt) {
        // Fallback jika dikirim format lengkap
        $deadline_dt = DateTime::createFromFormat('Y-m-d H:i:s', $deadline_input, $tz);
    }

    if (!$deadline_dt) {
        throw new Exception('Format deadline tidak valid');
    }

    $deadline_db = $deadline_dt->format('Y-m-d H:i:s');

    $db = new Database();
    $conn = $db->connect();

    $conn->query("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $setting_key = 'verval_deadline';
    $stmt = $conn->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->bind_param('ss', $setting_key, $deadline_db);

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan pengaturan deadline');
    }

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Deadline verval berhasil diperbarui';
    $response['data'] = [
        'deadline_db' => $deadline_db,
        'deadline_iso' => $deadline_dt->format('Y-m-d\\TH:i:sP'),
        'deadline_display' => formatDeadlineDisplay($deadline_dt)
    ];
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>