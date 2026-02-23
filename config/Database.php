<?php
/**
 * Database Configuration Class
 * Konfigurasi koneksi database MySQL
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'rekamdidik_db';
    private $user = 'root';
    private $password = '';
    private $conn;

    public function __construct() {
        // Load local override if exists (ignored by git)
        $local_path = __DIR__ . '/Database.local.php';
        if (is_file($local_path)) {
            $local = include $local_path;
            if (is_array($local)) {
                $this->host = isset($local['host']) ? $local['host'] : $this->host;
                $this->db_name = isset($local['db_name']) ? $local['db_name'] : $this->db_name;
                $this->user = isset($local['user']) ? $local['user'] : $this->user;
                $this->password = array_key_exists('password', $local) ? $local['password'] : $this->password;
            }
        }

        // Environment variable override (optional)
        $this->host = getenv('DB_HOST') ? getenv('DB_HOST') : $this->host;
        $this->db_name = getenv('DB_NAME') ? getenv('DB_NAME') : $this->db_name;
        $this->user = getenv('DB_USER') ? getenv('DB_USER') : $this->user;
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : $this->password;
    }

    // Connect to database
    public function connect() {
        $this->conn = new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->db_name
        );

        // Check connection
        if ($this->conn->connect_error) {
            die('Connection Error: ' . $this->conn->connect_error);
        }

        // Set charset
        $this->conn->set_charset('utf8mb4');

        return $this->conn;
    }

    // Get connection
    public function getConnection() {
        return $this->conn;
    }
}
?>
