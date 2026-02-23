<?php
/**
 * Admin Dashboard - Memerlukan login
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Verval Rekam Didik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css">
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h2 {
            margin: 0;
            color: #333;
        }
        
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: #f0f0f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .user-info strong {
            color: #667eea;
        }

        /* Animation for badge notification */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(244, 67, 54, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(244, 67, 54, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(244, 67, 54, 0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔐 Admin Dashboard</h1>
            <p>Verval Rekam Didik MTsN 11 Majalengka</p>
        </header>

        <div class="card">
            <div class="admin-header">
                <h2 style="color: #333;">Daftar Data Siswa</h2>
                <div class="header-right">
                    <button id="btnChangePassword" class="user-info" style="border: none; background: none; cursor: pointer; padding: 0;">
                        👤 <strong><?php echo htmlspecialchars($_SESSION['admin_nama'] ?? $_SESSION['admin_username']); ?></strong>
                    </button>
                    <a href="../index.php" class="button button-secondary" style="font-size: 13px; padding: 8px 15px;">← Verval</a>
                    <button id="btnLogout" class="button" style="font-size: 13px; padding: 8px 15px; background: #d32f2f; color: white; border: none; border-radius: 4px; cursor: pointer;">Logout</button>
                </div>
            </div>

            <div id="alertContainer"></div>

            <!-- Toolbar -->
            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <button id="btnOpenImport" class="button button-success">📥 Impor Data Siswa</button>
                <button id="btnPengajuanPembatalan" class="button" style="background: #ff9800; color: white; position: relative;">
                    📋 Pengajuan Pembatalan
                    <span id="badgePengajuanCount" style="display: none; position: absolute; top: -8px; right: -8px; background: #f44336; color: white; border-radius: 50%; width: 24px; height: 24px; font-size: 12px; font-weight: bold; line-height: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); animation: pulse 2s infinite;">0</span>
                </button>
                <button id="btnDeleteAll" class="button button-danger">🗑️ Hapus Semua Data</button>
            </div>

            <!-- Search & Filter -->
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" id="searchInput" placeholder="Cari NISN atau Nama..." style="padding: 10px;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <select id="statusFilter" style="padding: 10px;">
                        <option value="">Semua Status</option>
                        <option value="belum">Belum Verval</option>
                        <option value="sudah">Sudah Verval</option>
                    </select>
                </div>
                <button id="btnSearch" class="button button-primary" style="padding: 10px 20px;">Cari</button>
            </div>

            <div id="loadingContainer" style="display: none;">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Sedang memproses data...</p>
                </div>
            </div>

            <div class="table-container">
                <table id="siswaTabel">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Perubahan</th>
                            <th>Scan Ijazah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <p style="color: #999;">Memuat data siswa...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="paginationContainer" style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;"></div>
        </div>
    </div>

    <!-- Modal Import Siswa -->
    <div id="importModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📥 Impor Data Siswa (Excel)</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                    Upload file .xlsx. Pastikan kolom NISN bertipe teks agar 10 digit tidak terpotong. Jika ada NISN duplikat, proses akan gagal dan ditampilkan daftar duplikat.
                </p>
                <form id="importForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Pilih File Excel (.xlsx)</label>
                        <input type="file" id="importFile" name="file" accept=".xlsx" required>
                        <small style="color: #999; display: block; margin-top: 5px;">Maksimal 2MB, format .xlsx</small>
                    </div>
                    <div id="importResult" style="margin-top: 15px;"></div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" class="button button-secondary close-modal">Batal</button>
                        <button type="submit" class="button button-success">Impor Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Siswa -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Siswa</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div id="detailContent" style="max-height: 70vh; overflow-y: auto;"></div>
        </div>
    </div>

    <!-- Modal Change Password -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>🔐 Ubah Username & Password</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="changePasswordForm" style="padding: 0 20px;">
                <div class="form-group">
                    <label>Username Baru</label>
                    <input type="text" id="newUsername" placeholder="Masukkan username baru" required>
                </div>
                <div class="form-group">
                    <label>Password Saat Ini</label>
                    <input type="password" id="currentPassword" placeholder="Masukkan password saat ini" required>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" id="newPassword" placeholder="Biarkan kosong jika tidak ingin ubah">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" id="confirmPassword" placeholder="Konfirmasi password baru">
                    <small style="color: #666; display: block; margin-top: 5px;">Password harus minimal 6 karakter</small>
                </div>
                <div id="changePassResult" style="margin: 15px 0;"></div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="button button-secondary close-modal">Batal</button>
                    <button type="submit" class="button button-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pengajuan Pembatalan -->
    <div id="pengajuanPembatalanModal" class="modal">
        <div class="modal-content" style="max-width: 1000px;">
            <div class="modal-header">
                <h2>📋 Pengajuan Pembatalan Verval</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div style="padding: 20px;">
                <!-- Filter Status Pengajuan -->
                <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                    <label style="font-weight: 600;">Filter Status:</label>
                    <select id="filterStatusPengajuan" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="all">Semua</option>
                        <option value="menunggu" selected>Menunggu</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                    <button onclick="loadPengajuanPembatalan()" class="button button-primary" style="padding: 8px 15px;">Refresh</button>
                </div>

                <div id="pengajuanListContainer">
                    <p style="text-align: center; padding: 20px; color: #999;">Memuat data pengajuan...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script>
        let currentPage = 1;
        let currentSearch = '';
        let currentStatus = '';

        // Logout handler
        document.getElementById('btnLogout').addEventListener('click', async function() {
            Swal.fire({
                title: '🚪 Logout',
                text: 'Apakah Anda yakin ingin logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d32f2f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        await fetch('../api/auth/logout.php', { method: 'POST' });
                        window.location.href = '../auth/login.php';
                    } catch (error) {
                        showAlert('Terjadi kesalahan: ' + error.message, 'error');
                    }
                }
            });
        });

        // Change password handler
        document.getElementById('btnChangePassword').addEventListener('click', function() {
            openModal('changePasswordModal');
        });

        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const newUsername = document.getElementById('newUsername').value.trim();
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const resultBox = document.getElementById('changePassResult');

            resultBox.innerHTML = '';

            // Validasi
            if (!newUsername) {
                resultBox.innerHTML = '<div class="alert alert-error">Username tidak boleh kosong</div>';
                return;
            }

            if (!currentPassword) {
                resultBox.innerHTML = '<div class="alert alert-error">Password saat ini harus diisi</div>';
                return;
            }

            // Jika ada password baru, harus dikonfirmasi
            if (newPassword) {
                if (newPassword.length < 6) {
                    resultBox.innerHTML = '<div class="alert alert-error">Password baru minimal 6 karakter</div>';
                    return;
                }

                if (newPassword !== confirmPassword) {
                    resultBox.innerHTML = '<div class="alert alert-error">Konfirmasi password tidak cocok</div>';
                    return;
                }
            }

            try {
                const response = await fetch('../api/admin/change-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        new_username: newUsername,
                        current_password: currentPassword,
                        new_password: newPassword || null
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Perubahan berhasil disimpan. Silakan login kembali.', 'success');
                    setTimeout(() => {
                        closeModal('changePasswordModal');
                        window.location.href = '../auth/login.php';
                    }, 1500);
                } else {
                    resultBox.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (error) {
                resultBox.innerHTML = `<div class="alert alert-error">Terjadi kesalahan: ${error.message}</div>`;
            }
        });

        // Modal controls
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            modal.classList.add('active');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
            modal.classList.remove('active');
        }

        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });

        // Open import modal
        document.getElementById('btnOpenImport').addEventListener('click', function() {
            openModal('importModal');
        });

        // Open pengajuan pembatalan modal
        document.getElementById('btnPengajuanPembatalan').addEventListener('click', function() {
            openModal('pengajuanPembatalanModal');
            loadPengajuanPembatalan();
            loadPengajuanCount(); // Refresh counter saat modal dibuka
        });

        // Import form handler
        document.getElementById('importForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('importFile');
            const resultBox = document.getElementById('importResult');
            resultBox.innerHTML = '';

            if (!fileInput.files || fileInput.files.length === 0) {
                showAlert('File Excel wajib dipilih', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const response = await fetch('../api/admin/import-siswa.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Import berhasil', 'success');
                    const inserted = result.details && result.details.inserted ? result.details.inserted : 0;
                    resultBox.innerHTML = `<div class="alert alert-success">Berhasil mengimpor ${inserted} data siswa.</div>`;
                    fileInput.value = '';
                    
                    // Close modal dan reload data
                    setTimeout(() => {
                        closeModal('importModal');
                        currentPage = 1;
                        loadData();
                    }, 1500);
                } else {
                    showAlert(result.message || 'Import gagal', 'error');
                    resultBox.innerHTML = buildImportErrorHTML(result.details || {});
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
        });

        // Delete all handler
        document.getElementById('btnDeleteAll').addEventListener('click', async function() {
            Swal.fire({
                title: '🗑️ Hapus Semua Data',
                text: 'Apakah Anda yakin ingin menghapus SEMUA data siswa? Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('../api/admin/delete-all-siswa.php', {
                            method: 'DELETE'
                        });

                        const data = await response.json();
                        if (data.success) {
                            showAlert('Semua data berhasil dihapus', 'success');
                            currentPage = 1;
                            loadData();
                        } else {
                            showAlert(data.message || 'Gagal menghapus data', 'error');
                        }
                    } catch (error) {
                        showAlert('Terjadi kesalahan: ' + error.message, 'error');
                    }
                }
            });
        });

        // Search handler
        document.getElementById('btnSearch').addEventListener('click', function() {
            currentPage = 1;
            currentSearch = document.getElementById('searchInput').value;
            currentStatus = document.getElementById('statusFilter').value;
            loadData();
        });

        // Enter key on search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('btnSearch').click();
            }
        });

        // Initial load on page load (load all data)
        document.addEventListener('DOMContentLoaded', function() {
            loadData();
            loadPengajuanCount(); // Load notification count
            
            // Auto-refresh pengajuan count every 30 seconds
            setInterval(loadPengajuanCount, 30000);
        });

        async function loadData() {
            const loadingContainer = document.getElementById('loadingContainer');
            const tableBody = document.getElementById('tableBody');
            
            if (loadingContainer) loadingContainer.style.display = 'block';
            tableBody.innerHTML = '';

            try {
                const params = new URLSearchParams({
                    page: currentPage,
                    limit: 10,
                    search: currentSearch,
                    status: currentStatus
                });

                const response = await fetch(`../api/admin/list-siswa.php?${params}`);
                const result = await response.json();

                if (loadingContainer) loadingContainer.style.display = 'none';

                if (result.success) {
                    if (result.data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data yang ditemukan</td></tr>';
                    } else {
                        let html = '';
                        result.data.forEach((siswa, index) => {
                            const no = (currentPage - 1) * 10 + index + 1;
                            const statusBadge = siswa.verval_status === 'sudah' 
                                ? '<span class="status-badge status-sudah">Sudah Verval</span>'
                                : '<span class="status-badge status-belum">Belum Verval</span>';
                            
                            const nama = siswa.nama_kk || siswa.nama_ijazah || '-';
                            const ijazahLink = siswa.dokumen_ijazah 
                                ? `<a href="../uploads/ijazah/${siswa.dokumen_ijazah}" target="_blank" style="color: #667eea; margin-right: 10px;">👁 Lihat</a><a href="../uploads/ijazah/${siswa.dokumen_ijazah}" download style="color: #667eea;">⬇️ Download</a>`
                                : '<span style="color: #999;">-</span>';

                            const batalkanBtn = siswa.verval_status === 'sudah' 
                                ? `<button class="btn-small btn-danger" onclick="cancelVerval(${siswa.id})" style="font-size: 11px; padding: 5px 8px; background: #d32f2f; color: white;">✕ Batalkan</button>`
                                : '';

                            html += `
                                <tr>
                                    <td>${no}</td>
                                    <td><strong>${siswa.nisn}</strong></td>
                                    <td>${nama}</td>
                                    <td>${statusBadge}</td>
                                    <td id="changes-${siswa.id}"><span style="color: #999;">-</span></td>
                                    <td>${ijazahLink}</td>
                                    <td>
                                        <div class="table-actions" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <button class="btn-small btn-view" onclick="viewDetail(${siswa.id})" style="font-size: 11px; padding: 5px 8px;">👁 Lihat Data</button>
                                            ${batalkanBtn}
                                        </div>
                                    </td>
                                </tr>
                            `;

                        });

                        tableBody.innerHTML = html;
                        
                        // Load changes for each row
                        result.data.forEach(siswa => {
                            loadChanges(siswa.id);
                        });
                    }

                    // Setup pagination
                    setupPagination(result.pagination);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                if (loadingContainer) loadingContainer.style.display = 'none';
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
        }

        async function loadChanges(siswaId) {
            try {
                const response = await fetch(`../api/admin/get-changes.php?id=${siswaId}`);
                const result = await response.json();

                if (result.success && result.changes.length > 0) {
                    const changesCell = document.getElementById(`changes-${siswaId}`);
                    changesCell.innerHTML = result.changes.join(', ');
                }
            } catch (error) {
                console.error('Error loading changes:', error);
            }
        }

        async function cancelVerval(siswaId) {
            Swal.fire({
                title: '⚠️ Batalkan Verval',
                text: 'Apakah Anda yakin ingin membatalkan verifikasi siswa ini? Status akan kembali ke "Belum Verval".',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d32f2f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('../api/admin/cancel-verval.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id: siswaId })
                        });

                        const data = await response.json();
                        if (data.success) {
                            showAlert('Verval berhasil dibatalkan', 'success');
                            loadData();
                        } else {
                            showAlert(data.message || 'Gagal membatalkan verval', 'error');
                        }
                    } catch (error) {
                        showAlert('Terjadi kesalahan: ' + error.message, 'error');
                    }
                }
            });
        }

        function setupPagination(pagination) {
            const container = document.getElementById('paginationContainer');
            container.innerHTML = '';

            if (pagination.total_pages <= 1) return;

            // Previous button
            if (pagination.page > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'button button-secondary';
                prevBtn.textContent = '← Sebelumnya';
                prevBtn.onclick = () => {
                    currentPage--;
                    loadData();
                    window.scrollTo(0, 0);
                };
                container.appendChild(prevBtn);
            }

            // Page info
            const info = document.createElement('span');
            info.style.padding = '10px';
            info.style.alignSelf = 'center';
            info.textContent = `Halaman ${pagination.page} dari ${pagination.total_pages}`;
            container.appendChild(info);

            // Next button
            if (pagination.page < pagination.total_pages) {
                const nextBtn = document.createElement('button');
                nextBtn.className = 'button button-secondary';
                nextBtn.textContent = 'Selanjutnya →';
                nextBtn.onclick = () => {
                    currentPage++;
                    loadData();
                    window.scrollTo(0, 0);
                };
                container.appendChild(nextBtn);
            }
        }

        async function viewDetail(siswaId) {
            try {
                const response = await fetch(`../api/admin/detail-siswa.php?id=${siswaId}`);
                const result = await response.json();

                if (result.success) {
                    const siswa = result.data;
                    let html = buildDetailHTML(siswa);
                    document.getElementById('detailContent').innerHTML = html;
                    openModal('detailModal');
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
        }

        function buildImportErrorHTML(details) {
            let html = '<div class="alert alert-error">Detail error import:</div>';

            if (details.missing_headers && details.missing_headers.length > 0) {
                html += `<div class="alert alert-warning">Kolom header kurang: ${details.missing_headers.join(', ')}</div>`;
            }

            if (details.duplicate_in_file && details.duplicate_in_file.length > 0) {
                html += `<div class="alert alert-warning">Duplikat NISN di file: ${details.duplicate_in_file.join(', ')}</div>`;
            }

            if (details.duplicate_in_db && details.duplicate_in_db.length > 0) {
                html += `<div class="alert alert-warning">Duplikat NISN di database: ${details.duplicate_in_db.join(', ')}</div>`;
            }

            if (details.invalid_rows && details.invalid_rows.length > 0) {
                const items = details.invalid_rows
                    .map(item => `Baris ${item.row}: ${item.reason}`)
                    .join('<br>');
                html += `<div class="alert alert-warning">Baris tidak valid:<br>${items}</div>`;
            }

            return html;
        }

        function buildDetailHTML(siswa) {
            let html = `
                <div class="data-section">
                    <h4 style="color: #667eea; margin-bottom: 15px;">📋 Data Dasar Siswa</h4>
                    <div class="data-row">
                        <div class="data-field">
                            <label>NISN</label>
                            <p>${siswa.nisn}</p>
                        </div>
                        <div class="data-field">
                            <label>Status Verval</label>
                            <p>${siswa.verval_status === 'sudah' 
                                ? '<span class="status-badge status-sudah">Sudah Verval</span>'
                                : '<span class="status-badge status-belum">Belum Verval</span>'}</p>
                        </div>
                        <div class="data-field">
                            <label>Tanggal Dibuat</label>
                            <p>${formatTime(siswa.created_at)}</p>
                        </div>
                        <div class="data-field">
                            <label>Terakhir Diupdate</label>
                            <p>${formatTime(siswa.updated_at)}</p>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <h4 style="color: #667eea; margin-bottom: 15px;">📋 Data dari Kartu Keluarga (KK)</h4>
                    <div class="data-row">
                        <div class="data-field">
                            <label>NIK (KK)</label>
                            <p>${siswa.nik_kk || '-'}</p>
                            <small style="color: ${siswa.nik_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nik_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Nama (KK)</label>
                            <p>${siswa.nama_kk || '-'}</p>
                            <small style="color: ${siswa.nama_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Tempat Lahir (KK)</label>
                            <p>${siswa.tempat_lahir_kk || '-'}</p>
                            <small style="color: ${siswa.tempat_lahir_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.tempat_lahir_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Tanggal Lahir (KK)</label>
                            <p>${formatDate(siswa.tanggal_lahir_kk) || '-'}</p>
                            <small style="color: ${siswa.tanggal_lahir_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.tanggal_lahir_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Jenis Kelamin (KK)</label>
                            <p>${siswa.jenis_kelamin_kk === 'L' ? 'Laki-laki' : siswa.jenis_kelamin_kk === 'P' ? 'Perempuan' : '-'}</p>
                            <small style="color: ${siswa.jenis_kelamin_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.jenis_kelamin_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Nama Ibu Kandung (KK)</label>
                            <p>${siswa.nama_ibu_kk || '-'}</p>
                            <small style="color: ${siswa.nama_ibu_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ibu_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Nama Ayah Kandung (KK)</label>
                            <p>${siswa.nama_ayah_kk || '-'}</p>
                            <small style="color: ${siswa.nama_ayah_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ayah_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <h4 style="color: #667eea; margin-bottom: 15px;">📋 Data dari Ijazah</h4>
                    <div class="data-row">
                        <div class="data-field">
                            <label>NISN (Ijazah)</label>
                            <p>${siswa.nisn || '-'}</p>
                            <small style="color: ${siswa.nisn_verified ? '#388e3c' : '#999'};">
                                ${siswa.nisn_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Nama (Ijazah)</label>
                            <p>${siswa.nama_ijazah || '-'}</p>
                            <small style="color: ${siswa.nama_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Tempat Lahir (Ijazah)</label>
                            <p>${siswa.tempat_lahir_ijazah || '-'}</p>
                            <small style="color: ${siswa.tempat_lahir_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.tempat_lahir_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Tanggal Lahir (Ijazah)</label>
                            <p>${formatDate(siswa.tanggal_lahir_ijazah) || '-'}</p>
                            <small style="color: ${siswa.tanggal_lahir_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.tanggal_lahir_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Jenis Kelamin (Ijazah)</label>
                            <p>${siswa.jenis_kelamin_ijazah === 'L' ? 'Laki-laki' : siswa.jenis_kelamin_ijazah === 'P' ? 'Perempuan' : '-'}</p>
                            <small style="color: ${siswa.jenis_kelamin_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.jenis_kelamin_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label>Nama Ayah Kandung (Ijazah)</label>
                            <p>${siswa.nama_ayah_ijazah || '-'}</p>
                            <small style="color: ${siswa.nama_ayah_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ayah_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                    </div>
                </div>
            `;

            if (siswa.verval_data) {
                html += `
                    <div class="data-section">
                        <h4 style="color: #667eea; margin-bottom: 15px;">🏫 Data Verval Jenjang Sebelumnya</h4>
                        <div class="data-row">
                            <div class="data-field">
                                <label>Nama Sekolah Dasar (SD)</label>
                                <p>${siswa.verval_data.nama_sd || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label>Tahun Ajaran Kelulusan</label>
                                <p>${siswa.verval_data.tahun_ajaran_kelulusan || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label>NIP Kepala Sekolah pada Ijazah</label>
                                <p>${siswa.verval_data.nip_kepala_sekolah || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label>Nama Kepala Sekolah pada Ijazah</label>
                                <p>${siswa.verval_data.nama_kepala_sekolah || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label>Nomor Seri Ijazah</label>
                                <p>${siswa.verval_data.nomor_seri_ijazah || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label>Tanggal Terbit Ijazah</label>
                                <p>${formatDate(siswa.verval_data.tanggal_terbit_ijazah) || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label>Dokumen Ijazah</label>
                                <p>${siswa.verval_data.dokumen_ijazah 
                                    ? `<a href="../uploads/ijazah/${siswa.verval_data.dokumen_ijazah}" target="_blank" style="color: #667eea;">📄 ${siswa.verval_data.dokumen_ijazah}</a>`
                                    : '-'}</p>
                            </div>
                        </div>
                    </div>
                `;
            }

            if (siswa.history_perbaikan && siswa.history_perbaikan.length > 0) {
                html += `
                    <div class="data-section">
                        <h4 style="color: #667eea; margin-bottom: 15px;">📝 Riwayat Perbaikan Data</h4>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Nilai Sebelum</th>
                                        <th>Nilai Sesudah</th>
                                        <th>Tanggal Perbaikan</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                siswa.history_perbaikan.forEach(item => {
                    html += `
                                    <tr>
                                        <td><strong>${item.field_name}</strong></td>
                                        <td>${item.nilai_sebelum || '-'}</td>
                                        <td>${item.nilai_sesudah || '-'}</td>
                                        <td>${formatTime(item.tanggal_perbaikan)}</td>
                                    </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }

            return html;
        }

        // ========================================
        // PENGAJUAN PEMBATALAN FUNCTIONS
        // ========================================
        
        // Load count pengajuan yang menunggu untuk badge notification
        async function loadPengajuanCount() {
            try {
                const response = await fetch('../api/admin/count-pengajuan-menunggu.php');
                const result = await response.json();

                if (result.success) {
                    updatePengajuanBadge(result.count);
                }
            } catch (error) {
                console.error('Error loading pengajuan count:', error);
            }
        }

        // Update badge counter
        function updatePengajuanBadge(count) {
            const badge = document.getElementById('badgePengajuanCount');
            
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }

        async function loadPengajuanPembatalan() {
            try {
                const statusFilter = document.getElementById('filterStatusPengajuan').value;
                const container = document.getElementById('pengajuanListContainer');
                
                container.innerHTML = '<p style="text-align: center; padding: 20px; color: #999;">Memuat data pengajuan...</p>';

                const response = await fetch(`../api/admin/get-pengajuan-pembatalan.php?status=${statusFilter}`);
                const result = await response.json();

                if (result.success) {
                    if (result.data.length === 0) {
                        container.innerHTML = `
                            <div style="text-align: center; padding: 40px; color: #999;">
                                <p style="font-size: 48px; margin: 0;">📭</p>
                                <p>Tidak ada pengajuan pembatalan</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '<div style="display: flex; flex-direction: column; gap: 15px;">';
                    
                    result.data.forEach(pengajuan => {
                        const statusBadge = getStatusBadge(pengajuan.status);
                        const statusColor = pengajuan.status === 'menunggu' ? '#ff9800' : 
                                          pengajuan.status === 'disetujui' ? '#4caf50' : '#f44336';
                        
                        html += `
                            <div style="border: 1px solid #ddd; border-left: 4px solid ${statusColor}; border-radius: 4px; padding: 15px; background: white;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div>
                                        <h4 style="margin: 0 0 5px 0; color: #333;">
                                            📌 NISN: ${pengajuan.nisn} 
                                            <span style="color: #666; font-weight: normal;">- ${pengajuan.nama_kk || pengajuan.nama_ijazah || '-'}</span>
                                        </h4>
                                        <p style="margin: 0; font-size: 13px; color: #999;">
                                            Diajukan: ${new Date(pengajuan.created_at).toLocaleString('id-ID')}
                                        </p>
                                    </div>
                                    <div>
                                        ${statusBadge}
                                    </div>
                                </div>
                                
                                <div style="background: #f5f7fa; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
                                    <strong style="color: #666; font-size: 13px;">Alasan Pembatalan:</strong>
                                    <p style="margin: 5px 0 0 0; color: #333;">${pengajuan.alasan}</p>
                                </div>

                                ${pengajuan.status !== 'menunggu' ? `
                                    <div style="background: #e8f5e9; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
                                        <strong style="color: #666; font-size: 13px;">Diproses oleh: ${pengajuan.admin_username || 'Admin'}</strong>
                                        <p style="margin: 5px 0 0 0; font-size: 13px;">Tanggal: ${new Date(pengajuan.tanggal_diproses).toLocaleString('id-ID')}</p>
                                        ${pengajuan.catatan_admin ? `<p style="margin: 5px 0 0 0; color: #333;"><strong>Catatan:</strong> ${pengajuan.catatan_admin}</p>` : ''}
                                    </div>
                                ` : ''}

                                ${pengajuan.status === 'menunggu' ? `
                                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                        <button onclick="prosesPengajuan(${pengajuan.id}, 'setujui')" class="button button-success" style="padding: 8px 15px; font-size: 13px;">
                                            ✅ Setujui
                                        </button>
                                        <button onclick="prosesPengajuan(${pengajuan.id}, 'tolak')" class="button button-danger" style="padding: 8px 15px; font-size: 13px;">
                                            ❌ Tolak
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `<p style="text-align: center; padding: 20px; color: #f44336;">${result.message}</p>`;
                }
            } catch (error) {
                console.error('Error loading pengajuan:', error);
                document.getElementById('pengajuanListContainer').innerHTML = 
                    `<p style="text-align: center; padding: 20px; color: #f44336;">Terjadi kesalahan: ${error.message}</p>`;
            }
        }

        function getStatusBadge(status) {
            const badges = {
                'menunggu': '<span style="background: #ff9800; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">⏳ Menunggu</span>',
                'disetujui': '<span style="background: #4caf50; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">✅ Disetujui</span>',
                'ditolak': '<span style="background: #f44336; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">❌ Ditolak</span>'
            };
            return badges[status] || status;
        }

        async function prosesPengajuan(pengajuanId, action) {
            const actionText = action === 'setujui' ? 'menyetujui' : 'menolak';
            const actionTextCap = action === 'setujui' ? 'Setujui' : 'Tolak';
            
            // Prompt untuk catatan admin
            let catatan_admin = '';
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: `${actionTextCap} Pengajuan?`,
                    html: `
                        <p>Anda yakin ingin ${actionText} pengajuan ini?</p>
                        ${action === 'setujui' ? '<p style="color: #f44336; font-weight: bold;">Status verval siswa akan direset ke "belum"</p>' : ''}
                        <textarea id="catatanAdmin" class="swal2-textarea" placeholder="Catatan (opsional)"></textarea>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: actionTextCap,
                    confirmButtonColor: action === 'setujui' ? '#4caf50' : '#f44336',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        return document.getElementById('catatanAdmin').value;
                    }
                });
                
                if (!result.isConfirmed) return;
                catatan_admin = result.value || '';
            } else {
                if (!confirm(`Anda yakin ingin ${actionText} pengajuan ini?`)) return;
                catatan_admin = prompt('Catatan (opsional):') || '';
            }

            try {
                const response = await fetch('../api/admin/proses-pembatalan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pengajuan_id: pengajuanId,
                        action: action,
                        catatan_admin: catatan_admin
                    })
                });

                const result = await response.json();

                if (result.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            loadPengajuanPembatalan();
                            loadData(); // Refresh data siswa
                            loadPengajuanCount(); // Refresh badge counter
                        });
                    } else {
                        showAlert(result.message, 'success');
                        setTimeout(() => {
                            loadPengajuanPembatalan();
                            loadData();
                            loadPengajuanCount(); // Refresh badge counter
                        }, 1500);
                    }
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
                console.error('Error processing pengajuan:', error);
            }
        }

        // Filter change listener
        document.getElementById('filterStatusPengajuan').addEventListener('change', function() {
            loadPengajuanPembatalan();
        });
    </script>
</body>
</html>
