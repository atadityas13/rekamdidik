<?php
/**
 * API: Get Verval Deadline Setting (Public)
 * GET /api/get-verval-deadline.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/Database.php';

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
    $db = new Database();
    $conn = $db->connect();

    $conn->query("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $default_deadline = '2026-03-13 22:00:00';

    $stmt = $conn->prepare('SELECT setting_value FROM app_settings WHERE setting_key = ?');
    $setting_key = 'verval_deadline';
    $stmt->bind_param('s', $setting_key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $insert_stmt = $conn->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?)');
        $insert_stmt->bind_param('ss', $setting_key, $default_deadline);
        $insert_stmt->execute();
        $insert_stmt->close();
        $deadline_db = $default_deadline;
    } else {
        $row = $result->fetch_assoc();
        $deadline_db = trim((string) $row['setting_value']);
    }
    $stmt->close();

    $tz = new DateTimeZone('Asia/Jakarta');
    $deadline_dt = DateTime::createFromFormat('Y-m-d H:i:s', $deadline_db, $tz);

    if (!$deadline_dt) {
        $deadline_dt = DateTime::createFromFormat('Y-m-d H:i:s', $default_deadline, $tz);
        $deadline_db = $default_deadline;
    }

    $now = new DateTime('now', $tz);

    $response['success'] = true;
    $response['message'] = 'Setting deadline berhasil diambil';
    $response['data'] = [
        'deadline_db' => $deadline_dt->format('Y-m-d H:i:s'),
        'deadline_iso' => $deadline_dt->format('Y-m-d\\TH:i:sP'),
        'deadline_display' => formatDeadlineDisplay($deadline_dt),
        'is_closed' => ($now >= $deadline_dt)
    ];

    $conn->close();
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>