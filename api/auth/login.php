<?php
/**
 * API: Admin Login
 * POST /api/auth/login.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/Database.php';

session_start();

$response = ['success' => false, 'message' => ''];

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username']) || !isset($input['password'])) {
        throw new Exception('Username dan password harus diisi');
    }

    $username = trim($input['username']);
    $password = $input['password'];

    // Validasi input
    if (empty($username) || empty($password)) {
        throw new Exception('Username dan password tidak boleh kosong');
    }

    $db = new Database();
    $conn = $db->connect();

    // Cari user berdasarkan username
    $query = "SELECT id, username, password_hash, nama_lengkap FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Username atau password tidak valid');
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Verifikasi password
    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('Username atau password tidak valid');
    }

    // Set session
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_nama'] = $user['nama_lengkap'];
    $_SESSION['logged_in'] = true;

    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Login berhasil';
    $response['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'nama_lengkap' => $user['nama_lengkap']
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
