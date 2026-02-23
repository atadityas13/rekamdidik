<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Verval Rekam Didik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin: 0 0 10px 0;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #aaa;
        }

        .login-button {
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert-info {
            background: #eef;
            border: 1px solid #ccf;
            color: #339;
        }

        .loading {
            display: none;
            text-align: center;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner-text {
            color: #667eea;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Login Admin</h1>
            <p>Verval Rekam Didik MTsN 11 Majalengka</p>
        </div>

        <div id="alertBox"></div>

        <form id="loginForm" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="login-button" id="loginBtn">Login</button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p class="spinner-text">Sedang meng-verifikasi...</p>
            </div>
        </form>

        <div class="back-link">
            <a href="../">← Kembali ke Beranda</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                showAlert('Username dan password harus diisi', 'error');
                return;
            }

            // Show loading
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('loading').style.display = 'block';

            try {
                const response = await fetch('../api/auth/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Login berhasil! Mengalihkan...', 'success');
                    setTimeout(() => {
                        window.location.href = '../admin/index.php';
                    }, 1000);
                } else {
                    showAlert(result.message || 'Login gagal', 'error');
                    document.getElementById('loginForm').style.display = 'flex';
                    document.getElementById('loading').style.display = 'none';
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
                document.getElementById('loginForm').style.display = 'flex';
                document.getElementById('loading').style.display = 'none';
            }
        });

        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            const className = type === 'success' ? 'alert alert-success' : type === 'error' ? 'alert alert-error' : 'alert alert-info';
            alertBox.innerHTML = `<div class="${className}">${message}</div>`;
            
            if (type === 'error') {
                // Scroll ke atas untuk melihat alert
                window.scrollTo(0, 0);
            }
        }

        // Auto-focus ke password jika username sudah terisi
        document.getElementById('username').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>
