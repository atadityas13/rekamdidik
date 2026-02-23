<?php
/**
 * API: Admin Change Password/Username
 * POST /api/admin/change-password.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/Database.php';

session_start();

$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('User tidak terautentikasi');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['new_username']) || !isset($input['current_password'])) {
        throw new Exception('Data tidak lengkap');
    }

    $admin_id = $_SESSION['admin_id'];
    $new_username = trim($input['new_username']);
    $current_password = $input['current_password'];
    $new_password = isset($input['new_password']) ? $input['new_password'] : null;

    // Validasi input
    if (empty($new_username)) {
        throw new Exception('Username tidak boleh kosong');
    }

    if (strlen($new_username) < 3) {
        throw new Exception('Username minimal 3 karakter');
    }

    if ($new_password && strlen($new_password) < 6) {
        throw new Exception('Password baru minimal 6 karakter');
    }

    $db = new Database();
    $conn = $db->connect();

    // Get current admin user
    $query = "SELECT password_hash FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User tidak ditemukan');
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify current password
    if (!password_verify($current_password, $user['password_hash'])) {
        throw new Exception('Password saat ini tidak valid');
    }

    // Check if new username is already taken (if different from current)
    $check_query = "SELECT id FROM admin_users WHERE username = ? AND id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('si', $new_username, $admin_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Username sudah digunakan oleh user lain');
    }
    $stmt->close();

    // Prepare update query
    if ($new_password) {
        // Update both username and password
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $update_query = "UPDATE admin_users SET username = ?, password_hash = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ssi', $new_username, $new_password_hash, $admin_id);
    } else {
        // Update only username
        $update_query = "UPDATE admin_users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('si', $new_username, $admin_id);
    }

    if (!$stmt->execute()) {
        throw new Exception('Gagal update data: ' . $conn->error);
    }

    // Update session
    $_SESSION['admin_username'] = $new_username;

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['message'] = $new_password 
        ? 'Username dan password berhasil diubah' 
        : 'Username berhasil diubah';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
