<?php
/**
 * API: Admin Logout
 * POST /api/auth/logout.php
 */

header('Content-Type: application/json');

session_start();

// Destroy session
session_destroy();

$response = [
    'success' => true,
    'message' => 'Logout berhasil'
];

echo json_encode($response);
?>
