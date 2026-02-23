<?php
/**
 * Setup Admin Users Table
 * Jalankan script ini SEKALI untuk membuat tabel admin_users
 */

require_once 'config/Database.php';

$db = new Database();
$conn = $db->connect();

// SQL untuk membuat tabel admin_users
$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabel admin_users berhasil dibuat!<br><br>";
    
    // Cek apakah sudah ada admin user
    $check_query = "SELECT COUNT(*) as count FROM admin_users";
    $result = $conn->query($check_query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert default admin user
        $username = 'admin';
        $password = 'admin123'; // GANTI PASSWORD INI SETELAH LOGIN
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $nama_lengkap = 'Administrator';
        
        $insert_query = "INSERT INTO admin_users (username, password_hash, nama_lengkap) 
                        VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('sss', $username, $password_hash, $nama_lengkap);
        
        if ($stmt->execute()) {
            echo "✅ Admin user default berhasil dibuat!<br>";
            echo "📝 Username: <strong>admin</strong><br>";
            echo "🔑 Password: <strong>admin123</strong><br>";
            echo "⚠️  HARAP GANTI PASSWORD SETELAH LOGIN PERTAMA!<br>";
        } else {
            echo "❌ Gagal membuat admin user: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "ℹ️ Admin user sudah ada di database.<br>";
    }
} else {
    echo "❌ Gagal membuat tabel: " . $conn->error;
}

$conn->close();
?>
