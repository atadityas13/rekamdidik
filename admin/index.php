<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Verval Rekam Didik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🔐 Admin Dashboard</h1>
            <p>Verval Rekam Didik MTsN 11 Majalengka</p>
        </header>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="color: #333;">Daftar Data Siswa</h2>
                <a href="../index.php" class="button button-secondary">← Kembali ke Verval</a>
            </div>

            <div id="alertContainer"></div>

            <div class="data-section" style="margin-bottom: 20px;">
                <h3>📥 Import Data Siswa (Excel)</h3>
                <p style="color: #666; font-size: 13px; margin-bottom: 10px;">
                    Upload file .xlsx. Pastikan kolom NISN bertipe teks agar 10 digit tidak terpotong. Jika ada NISN duplikat, proses akan gagal dan ditampilkan daftar duplikat.
                </p>
                <form id="importForm" enctype="multipart/form-data">
                    <div class="data-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <input type="file" id="importFile" name="file" accept=".xlsx" required>
                            <small style="color: #999; display: block; margin-top: 5px;">Maksimal 2MB, format .xlsx</small>
                        </div>
                        <div class="form-group" style="margin-bottom: 0; display: flex; align-items: flex-end;">
                            <button type="submit" class="button button-success" style="width: 100%;">Import</button>
                        </div>
                    </div>
                </form>
                <div id="importResult" style="margin-top: 15px;"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" id="searchInput" placeholder="Cari NISN, Nama, atau Email..." style="padding: 10px;">
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
                            <th>Nama (KK)</th>
                            <th>Nama (Ijazah)</th>
                            <th>Status</th>
                            <th>Ijazah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <p style="color: #999;">Tekan tombol "Cari" untuk memuat data siswa</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="paginationContainer" style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;"></div>
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

    <script src="../assets/js/utils.js"></script>
    <script>
        let currentPage = 1;
        let currentSearch = '';
        let currentStatus = '';

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
                } else {
                    showAlert(result.message || 'Import gagal', 'error');
                    resultBox.innerHTML = buildImportErrorHTML(result.details || {});
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
        });

        document.getElementById('btnSearch').addEventListener('click', function() {
            currentPage = 1;
            currentSearch = document.getElementById('searchInput').value;
            currentStatus = document.getElementById('statusFilter').value;
            loadData();
        });

        // Enter key on search input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('btnSearch').click();
            }
        });

        // Initial load on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Don't load data automatically, wait for search
        });

        async function loadData() {
            const loadingContainer = document.getElementById('loadingContainer');
            const tableBody = document.getElementById('tableBody');
            
            loadingContainer.style.display = 'block';
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

                loadingContainer.style.display = 'none';

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
                            const ijazahLink = siswa.dokumen_ijazah 
                                ? `<a href="../uploads/ijazah/${siswa.dokumen_ijazah}" target="_blank" style="color: #667eea;">📄 Lihat</a>`
                                : '<span style="color: #999;">-</span>';

                            html += `
                                <tr>
                                    <td>${no}</td>
                                    <td><strong>${siswa.nisn}</strong></td>
                                    <td>${siswa.nama_kk || '-'}</td>
                                    <td>${siswa.nama_ijazah || '-'}</td>
                                    <td>${statusBadge}</td>
                                    <td>${ijazahLink}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn-small btn-view" onclick="viewDetail(${siswa.id})">👁 Lihat</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        tableBody.innerHTML = html;
                    }

                    // Setup pagination
                    setupPagination(result.pagination);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                loadingContainer.style.display = 'none';
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
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
    </script>
</body>
</html>
