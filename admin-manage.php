<?php
/**
 * ADMIN MANAGEMENT HELPER SCRIPT
 * Gunakan file ini untuk maintenance admin user
 * 
 * CARA PENGGUNAAN:
 * 1. Akses file ini melalui browser: http://domain/rekamdidik/admin-manage.php
 * 2. Masukkan username & password baru
 * 3. Update password akan dienkripsi dengan MD5
 * 
 * ⚠️ SECURITY WARNING:
 * - Gunakan password yang kuat
 * - Hapus file ini setelah mengubah password
 * - Jangan share script ini ke orang lain
 */

require_once 'config/Database.php';

$response = ['success' => false, 'message' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'change_password') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password_baru = isset($_POST['password_baru']) ? trim($_POST['password_baru']) : '';
        
        if (empty($username) || empty($password_baru)) {
            $response['message'] = 'Username dan password tidak boleh kosong';
        } else if (strlen($password_baru) < 6) {
            $response['message'] = 'Password minimal 6 karakter';
        } else {
            $db = new Database();
            $conn = $db->connect();
            
            $password_hash = md5($password_baru);
            $stmt = $conn->prepare('UPDATE admin_users SET password = ? WHERE username = ?');
            $stmt->bind_param('ss', $password_hash, $username);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Password berhasil diubah';
                } else {
                    $response['message'] = 'Username tidak ditemukan';
                }
            } else {
                $response['message'] = 'Error: ' . $stmt->error;
            }
            
            $stmt->close();
            $conn->close();
        }
    } 
    elseif ($action === 'list_users') {
        $db = new Database();
        $conn = $db->connect();
        
        $result = $conn->query('SELECT id, username, email, nama_lengkap, role, status, created_at FROM admin_users');
        $response['success'] = true;
        $response['message'] = 'Data user berhasil diambil';
        $response['users'] = [];
        
        while ($row = $result->fetch_assoc()) {
            $response['users'][] = $row;
        }
        
        $conn->close();
    }
    elseif ($action === 'add_user') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : 'operator';
        
        if (empty($username) || empty($password)) {
            $response['message'] = 'Username dan password harus diisi';
        } else if (strlen($password) < 6) {
            $response['message'] = 'Password minimal 6 karakter';
        } else {
            $db = new Database();
            $conn = $db->connect();
            
            // Check if username exists
            $stmt = $conn->prepare('SELECT id FROM admin_users WHERE username = ?');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $response['message'] = 'Username sudah terdaftar';
                $stmt->close();
            } else {
                $stmt->close();
                
                $password_hash = md5($password);
                $status = 'aktif';
                
                $stmt = $conn->prepare('INSERT INTO admin_users (username, password, email, nama_lengkap, role, status) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssss', $username, $password_hash, $email, $nama_lengkap, $role, $status);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'User berhasil ditambahkan';
                } else {
                    $response['message'] = 'Error: ' . $stmt->error;
                }
                
                $stmt->close();
            }
            
            $conn->close();
        }
    }
    elseif ($action === 'delete_user') {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        if ($user_id <= 0) {
            $response['message'] = 'ID user tidak valid';
        } else if ($user_id === 1) {
            $response['message'] = 'Tidak bisa menghapus admin default';
        } else {
            $db = new Database();
            $conn = $db->connect();
            
            $stmt = $conn->prepare('DELETE FROM admin_users WHERE id = ? AND id != 1');
            $stmt->bind_param('i', $user_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'User berhasil dihapus';
            } else {
                $response['message'] = 'Error: ' . $stmt->error;
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

// JSON response untuk AJAX
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab-button {
            padding: 10px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 600;
            color: #999;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-button.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔐 Admin Management Panel</h1>
            <p>Kelola user admin dan password</p>
        </header>

        <div class="card">
            <div style="margin-bottom: 20px;">
                <a href="admin/index.php" class="button button-secondary">← Kembali ke Admin</a>
            </div>

            <div id="alertContainer"></div>

            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab('change-pwd')">
                    🔑 Ubah Password
                </button>
                <button class="tab-button" onclick="switchTab('list-users')">
                    👥 Daftar User
                </button>
                <button class="tab-button" onclick="switchTab('add-user')">
                    ➕ Tambah User
                </button>
            </div>

            <!-- TAB 1: Ubah Password -->
            <div id="change-pwd" class="tab-content active">
                <h3 style="margin-bottom: 20px;">Ubah Password Admin</h3>
                <form id="changePasswordForm" onsubmit="handleChangePassword(event)">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="admin" required>
                    </div>

                    <div class="form-group">
                        <label for="password_baru">Password Baru (min 6 karakter)</label>
                        <input type="password" id="password_baru" name="password_baru" placeholder="••••••" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Konfirmasi Password</label>
                        <input type="password" id="password_confirm" placeholder="••••••" required>
                    </div>

                    <div class="alert alert-warning" style="margin-bottom: 20px;">
                        <strong>⚠️ Perhatian:</strong> Pastikan password yang baru kuat dan mudah diingat
                    </div>

                    <button type="submit" class="button button-success">✓ Ubah Password</button>
                </form>
            </div>

            <!-- TAB 2: Daftar User -->
            <div id="list-users" class="tab-content">
                <h3 style="margin-bottom: 20px;">Daftar Admin User</h3>
                <button type="button" class="button button-primary" onclick="loadUsers()" style="margin-bottom: 20px;">
                    🔄 Load Users
                </button>
                
                <div id="usersContainer" class="table-container" style="display: none;">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="usersTable">
                        </tbody>
                    </table>
                </div>
                <div id="usersEmpty" style="text-align: center; padding: 30px; display: none;">
                    <p style="color: #999;">Klik tombol "Load Users" untuk menampilkan data</p>
                </div>
            </div>

            <!-- TAB 3: Tambah User -->
            <div id="add-user" class="tab-content">
                <h3 style="margin-bottom: 20px;">Tambah User Admin Baru</h3>
                <form id="addUserForm" onsubmit="handleAddUser(event)">
                    <div class="data-row">
                        <div class="form-group">
                            <label for="new_username">Username *</label>
                            <input type="text" id="new_username" name="username" placeholder="operator1" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Password (min 6 karakter) *</label>
                            <input type="password" id="new_password" name="password" placeholder="••••••" required>
                        </div>

                        <div class="form-group">
                            <label for="new_email">Email</label>
                            <input type="email" id="new_email" name="email" placeholder="operator@example.com">
                        </div>

                        <div class="form-group">
                            <label for="new_nama">Nama Lengkap</label>
                            <input type="text" id="new_nama" name="nama_lengkap" placeholder="Nama Lengkap">
                        </div>

                        <div class="form-group">
                            <label for="new_role">Role *</label>
                            <select id="new_role" name="role" required>
                                <option value="operator">Operator</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="button button-success">➕ Tambah User</button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/utils.js"></script>
    <script>
        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function handleChangePassword(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password_baru = document.getElementById('password_baru').value;
            const password_confirm = document.getElementById('password_confirm').value;

            if (password_baru !== password_confirm) {
                showAlert('Password tidak cocok', 'error');
                return;
            }

            if (password_baru.length < 6) {
                showAlert('Password minimal 6 karakter', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'change_password');
            formData.append('username', username);
            formData.append('password_baru', password_baru);
            formData.append('ajax', '1');

            fetch('admin-manage.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showAlert('Password berhasil diubah! Silakan login ulang.', 'success');
                    document.getElementById('changePasswordForm').reset();
                } else {
                    showAlert(result.message, 'error');
                }
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
        }

        function loadUsers() {
            const formData = new FormData();
            formData.append('action', 'list_users');
            formData.append('ajax', '1');

            fetch('admin-manage.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(result => {
                if (result.success && result.users) {
                    let html = '';
                    result.users.forEach(user => {
                        html += `
                            <tr>
                                <td><strong>${user.username}</strong></td>
                                <td>${user.nama_lengkap || '-'}</td>
                                <td>${user.email || '-'}</td>
                                <td>${user.role}</td>
                                <td><span style="color: ${user.status === 'aktif' ? '#388e3c' : '#ff6b6b'};">${user.status}</span></td>
                                <td>${formatDate(user.created_at)}</td>
                                <td>
                                    ${user.id !== 1 ? `<button class="btn-small btn-delete" onclick="deleteUser(${user.id})">🗑 Hapus</button>` : '<span style="color: #999;">-</span>'}
                                </td>
                            </tr>
                        `;
                    });
                    document.getElementById('usersTable').innerHTML = html;
                    document.getElementById('usersContainer').style.display = 'block';
                    document.getElementById('usersEmpty').style.display = 'none';
                } else {
                    showAlert(result.message, 'error');
                }
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
        }

        function handleAddUser(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('action', 'add_user');
            formData.append('username', document.getElementById('new_username').value);
            formData.append('password', document.getElementById('new_password').value);
            formData.append('email', document.getElementById('new_email').value);
            formData.append('nama_lengkap', document.getElementById('new_nama').value);
            formData.append('role', document.getElementById('new_role').value);
            formData.append('ajax', '1');

            fetch('admin-manage.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showAlert('User berhasil ditambahkan', 'success');
                    document.getElementById('addUserForm').reset();
                } else {
                    showAlert(result.message, 'error');
                }
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
        }

        function deleteUser(userId) {
            if (!confirm('Yakin hapus user ini?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('user_id', userId);
            formData.append('ajax', '1');

            fetch('admin-manage.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showAlert('User berhasil dihapus', 'success');
                    loadUsers();
                } else {
                    showAlert(result.message, 'error');
                }
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
        }
    </script>
</body>
</html>
