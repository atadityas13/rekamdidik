<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verval Rekam Didik - MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🎓 Verval Rekam Didik</h1>
            <p>Jenjang Sebelum MTsN 11 Majalengka</p>
        </header>

        <div class="card">
            <h2 style="margin-bottom: 30px; color: #333;">Periksa Status Verval Anda</h2>
            
            <div id="alertContainer"></div>

            <form id="nisnForm">
                <div class="form-group">
                    <label for="nisn">Masukkan NISN Anda</label>
                    <input 
                        type="text" 
                        id="nisn" 
                        name="nisn" 
                        placeholder="Contoh: 0123456789"
                        maxlength="10"
                        inputmode="numeric"
                        required
                    >
                    <small style="color: #999; margin-top: 5px; display: block;">NISN harus 10 digit</small>
                </div>

                <button type="submit" class="button button-primary button-large" style="width: 100%; padding: 15px;">
                    Periksa Status
                </button>
            </form>

            <div id="loadingContainer" style="display: none; margin-top: 30px;">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Sedang mencari data Anda...</p>
                </div>
            </div>

            <div id="resultContainer" style="display: none; margin-top: 30px;"></div>
        </div>

        <div style="text-align: center; margin-top: 40px; color: white; font-size: 12px;">
            <p>© 2024 MTsN 11 Majalengka | Verval Rekam Didik Jenjang Sebelumnya</p>
        </div>
    </div>

    <script src="assets/js/utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('nisnForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const nisn = document.getElementById('nisn').value.trim();
            const validation = validateNISN(nisn);
            
            if (!validation.valid) {
                showAlert(validation.message, 'error');
                return;
            }

            const loadingContainer = document.getElementById('loadingContainer');
            const resultContainer = document.getElementById('resultContainer');
            
            loadingContainer.style.display = 'block';
            resultContainer.style.display = 'none';

            try {
                const response = await apiCall('/api/check-nisn.php', 'POST', { nisn: nisn });

                loadingContainer.style.display = 'none';

                if (response.success) {
                    const siswa = response.data;
                    resultContainer.innerHTML = buildResultHTML(siswa);
                    resultContainer.style.display = 'block';
                    
                    // Setup event listeners for result
                    setupResultEventListeners(siswa.id, siswa.verval_status);
                } else {
                    showAlert(response.message, 'error');
                }
            } catch (error) {
                loadingContainer.style.display = 'none';
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
        });

        function buildResultHTML(siswa) {
            const statusClass = siswa.verval_status === 'sudah' ? 'status-sudah' : 'status-belum';
            const statusText = siswa.verval_status === 'sudah' ? 'Sudah Melakukan Verval' : 'Belum Melakukan Verval';
            const statusMessage = siswa.verval_status === 'sudah' 
                ? 'Data Anda telah berhasil diverifikasi sesuai dengan dokumen pendukung.' 
                : 'Data Anda belum diverifikasi. Silakan lengkapi data dan upload dokumen pendukung di bawah.';
            const disableAttr = (verified) => verified ? 'disabled' : '';
            const checkedAttr = (verified) => verified ? 'checked' : '';

            let html = `
                <div class="alert alert-info">
                    <div style="margin-bottom: 10px;">
                        <strong>Status Verval:</strong>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <p>${statusMessage}</p>
                </div>

                <div class="data-section">
                    <h3>📋 Bagian A: Data Siswa</h3>
                    <p class="helper-text">Bandingkan data KK dan Ijazah. Jika sudah sesuai, silakan centang agar terkunci.</p>
                    <div class="compare-wrap">
                        <table class="compare-table">
                            <thead>
                                <tr>
                                    <th>Data Verifikasi</th>
                                    <th>Data pada Kartu Keluarga</th>
                                    <th>Data pada Ijazah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="compare-label" data-label="">NIK</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="text" id="nik_kk" value="${siswa.nik_kk || ''}" class="data-input" data-field="nik_kk" ${disableAttr(siswa.nik_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nik_kk_verified" data-target-input="nik_kk" ${checkedAttr(siswa.nik_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell muted" data-label="Data pada Ijazah">-</td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">NISN</td>
                                    <td class="compare-cell muted" data-label="Data pada Kartu Keluarga">-</td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="text" id="nisn_ijazah" value="${siswa.nisn}" disabled>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nisn_verified" data-target-input="nisn_ijazah" ${checkedAttr(siswa.nisn_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Nama</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="text" id="nama_kk" value="${siswa.nama_kk || ''}" class="data-input" data-field="nama_kk" ${disableAttr(siswa.nama_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_kk_verified" data-target-input="nama_kk" ${checkedAttr(siswa.nama_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="text" id="nama_ijazah" value="${siswa.nama_ijazah || ''}" class="data-input" data-field="nama_ijazah" ${disableAttr(siswa.nama_ijazah_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_ijazah_verified" data-target-input="nama_ijazah" ${checkedAttr(siswa.nama_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Tempat Lahir</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="text" id="tempat_lahir_kk" value="${siswa.tempat_lahir_kk || ''}" class="data-input" data-field="tempat_lahir_kk" ${disableAttr(siswa.tempat_lahir_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tempat_lahir_kk_verified" data-target-input="tempat_lahir_kk" ${checkedAttr(siswa.tempat_lahir_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="text" id="tempat_lahir_ijazah" value="${siswa.tempat_lahir_ijazah || ''}" class="data-input" data-field="tempat_lahir_ijazah" ${disableAttr(siswa.tempat_lahir_ijazah_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tempat_lahir_ijazah_verified" data-target-input="tempat_lahir_ijazah" ${checkedAttr(siswa.tempat_lahir_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Tanggal Lahir</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="date" id="tanggal_lahir_kk" value="${siswa.tanggal_lahir_kk || ''}" class="data-input" data-field="tanggal_lahir_kk" ${disableAttr(siswa.tanggal_lahir_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tanggal_lahir_kk_verified" data-target-input="tanggal_lahir_kk" ${checkedAttr(siswa.tanggal_lahir_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="date" id="tanggal_lahir_ijazah" value="${siswa.tanggal_lahir_ijazah || ''}" class="data-input" data-field="tanggal_lahir_ijazah" ${disableAttr(siswa.tanggal_lahir_ijazah_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tanggal_lahir_ijazah_verified" data-target-input="tanggal_lahir_ijazah" ${checkedAttr(siswa.tanggal_lahir_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Jenis Kelamin</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <select id="jenis_kelamin_kk" class="data-input" data-field="jenis_kelamin_kk" ${disableAttr(siswa.jenis_kelamin_kk_verified)}>
                                            <option value="">-- Pilih --</option>
                                            <option value="L" ${siswa.jenis_kelamin_kk === 'L' ? 'selected' : ''}>Laki-laki</option>
                                            <option value="P" ${siswa.jenis_kelamin_kk === 'P' ? 'selected' : ''}>Perempuan</option>
                                        </select>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="jenis_kelamin_kk_verified" data-target-input="jenis_kelamin_kk" ${checkedAttr(siswa.jenis_kelamin_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <select id="jenis_kelamin_ijazah" class="data-input" data-field="jenis_kelamin_ijazah" ${disableAttr(siswa.jenis_kelamin_ijazah_verified)}>
                                            <option value="">-- Pilih --</option>
                                            <option value="L" ${siswa.jenis_kelamin_ijazah === 'L' ? 'selected' : ''}>Laki-laki</option>
                                            <option value="P" ${siswa.jenis_kelamin_ijazah === 'P' ? 'selected' : ''}>Perempuan</option>
                                        </select>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="jenis_kelamin_ijazah_verified" data-target-input="jenis_kelamin_ijazah" ${checkedAttr(siswa.jenis_kelamin_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Nama Ibu Kandung</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="text" id="nama_ibu_kk" value="${siswa.nama_ibu_kk || ''}" class="data-input" data-field="nama_ibu_kk" ${disableAttr(siswa.nama_ibu_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_ibu_kk_verified" data-target-input="nama_ibu_kk" ${checkedAttr(siswa.nama_ibu_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell muted" data-label="Data pada Ijazah">-</td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Nama Ayah Kandung</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="text" id="nama_ayah_kk" value="${siswa.nama_ayah_kk || ''}" class="data-input" data-field="nama_ayah_kk" ${disableAttr(siswa.nama_ayah_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_ayah_kk_verified" data-target-input="nama_ayah_kk" ${checkedAttr(siswa.nama_ayah_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="text" id="nama_ayah_ijazah" value="${siswa.nama_ayah_ijazah || ''}" class="data-input" data-field="nama_ayah_ijazah" ${disableAttr(siswa.nama_ayah_ijazah_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_ayah_ijazah_verified" data-target-input="nama_ayah_ijazah" ${checkedAttr(siswa.nama_ayah_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="data-section">
                    <h3>🏫 Bagian B: Verval Rekam Didik Jenjang Sebelumnya</h3>
                    
                    <form id="vervalForm">
                        <input type="hidden" id="siswa_id" value="${siswa.id}">
                        
                        <div class="data-row">
                            <div class="form-group">
                                <label for="nama_sd">Nama Sekolah Dasar (SD)</label>
                                <input type="text" id="nama_sd" name="nama_sd" placeholder="Nama SD" required value="${siswa.verval_data?.nama_sd || ''}">
                            </div>

                            <div class="form-group">
                                <label for="tahun_ajaran_kelulusan">Tahun Ajaran Kelulusan</label>
                                <input type="text" id="tahun_ajaran_kelulusan" name="tahun_ajaran_kelulusan" placeholder="Contoh: 2020/2021" required value="${siswa.verval_data?.tahun_ajaran_kelulusan || ''}">
                            </div>

                            <div class="form-group">
                                <label for="nip_kepala_sekolah">NIP Kepala Sekolah pada Ijazah</label>
                                <input type="text" id="nip_kepala_sekolah" name="nip_kepala_sekolah" placeholder="NIP Kepala Sekolah" required value="${siswa.verval_data?.nip_kepala_sekolah || ''}">
                            </div>

                            <div class="form-group">
                                <label for="nama_kepala_sekolah">Nama Kepala Sekolah pada Ijazah</label>
                                <input type="text" id="nama_kepala_sekolah" name="nama_kepala_sekolah" placeholder="Nama Kepala Sekolah" required value="${siswa.verval_data?.nama_kepala_sekolah || ''}">
                            </div>

                            <div class="form-group">
                                <label for="nomor_seri_ijazah">Nomor Seri Ijazah</label>
                                <input type="text" id="nomor_seri_ijazah" name="nomor_seri_ijazah" placeholder="Nomor Seri Ijazah" required value="${siswa.verval_data?.nomor_seri_ijazah || ''}">
                            </div>

                            <div class="form-group">
                                <label for="tanggal_terbit_ijazah">Tanggal Terbit Ijazah</label>
                                <input type="date" id="tanggal_terbit_ijazah" name="tanggal_terbit_ijazah" required value="${siswa.verval_data?.tanggal_terbit_ijazah || ''}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dokumen_ijazah">Upload Dokumen Ijazah (JPG, JPEG, PNG - Max 1MB)</label>
                            <input type="file" id="dokumen_ijazah" name="dokumen_ijazah" accept=".jpg,.jpeg,.png" required>
                            ${siswa.verval_data?.dokumen_ijazah ? `<small style="color: #666; margin-top: 5px; display: block;">📎 File saat ini: ${siswa.verval_data.dokumen_ijazah}</small>` : ''}
                        </div>

                        <div class="button-group">
                            <button type="submit" class="button button-success">💾 Simpan Data Verval</button>
                            <button type="reset" class="button button-secondary">↺ Reset Form</button>
                        </div>
                    </form>
                </div>

                ${siswa.history_perbaikan && siswa.history_perbaikan.length > 0 ? buildHistoryHTML(siswa.history_perbaikan) : ''}
            `;

            return html;
        }

        function buildHistoryHTML(history) {
            if (history.length === 0) return '';

            let html = `
                <div class="data-section">
                    <h3>📝 Riwayat Perbaikan Data</h3>
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

            history.forEach(item => {
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

            return html;
        }

        function setupResultEventListeners(siswaId, vervalStatus) {
            // Handle data input changes
            document.querySelectorAll('.data-input').forEach(input => {
                input.addEventListener('change', async function() {
                    const fieldName = this.getAttribute('data-field');
                    const nilai = this.value;

                    try {
                        const response = await apiCall('/api/update-siswa.php', 'POST', {
                            siswa_id: siswaId,
                            field_name: fieldName,
                            nilai_baru: nilai
                        });

                        if (response.success) {
                            showAlert('Data berhasil diupdate', 'success');
                        } else {
                            showAlert(response.message, 'error');
                            // Reload page to reset
                            setTimeout(() => location.reload(), 2000);
                        }
                    } catch (error) {
                        showAlert('Terjadi kesalahan: ' + error.message, 'error');
                    }
                });
            });

            document.querySelectorAll('.verify-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', async function() {
                    const fieldName = this.getAttribute('data-verify-field');
                    const targetInputId = this.getAttribute('data-target-input');

                    const labels = {
                        nik_kk_verified: 'NIK',
                        nisn_verified: 'NISN',
                        nama_kk_verified: 'Nama',
                        nama_ijazah_verified: 'Nama',
                        tempat_lahir_kk_verified: 'Tempat Lahir',
                        tempat_lahir_ijazah_verified: 'Tempat Lahir',
                        tanggal_lahir_kk_verified: 'Tanggal Lahir',
                        tanggal_lahir_ijazah_verified: 'Tanggal Lahir',
                        jenis_kelamin_kk_verified: 'Jenis Kelamin',
                        jenis_kelamin_ijazah_verified: 'Jenis Kelamin',
                        nama_ibu_kk_verified: 'Nama Ibu Kandung',
                        nama_ayah_kk_verified: 'Nama Ayah Kandung',
                        nama_ayah_ijazah_verified: 'Nama Ayah Kandung'
                    };

                    const docLabel = fieldName.indexOf('_kk_') !== -1 || fieldName.endsWith('_kk_verified')
                        ? 'KK'
                        : 'Ijazah';

                    let valueText = '';
                    if (targetInputId) {
                        const target = document.getElementById(targetInputId);
                        if (target) {
                            if (target.tagName === 'SELECT') {
                                valueText = target.options[target.selectedIndex] ? target.options[target.selectedIndex].text : '';
                            } else {
                                valueText = target.value;
                            }
                        }
                    }

                    const labelText = labels[fieldName] || 'Data';
                    const isChecking = this.checked;
                    const valueSuffix = valueText ? ` "${valueText}"` : '';
                    const confirmText = isChecking
                        ? `Anda yakin data ${labelText}${valueSuffix} sudah sesuai dengan data sebenarnya pada ${docLabel}?`
                        : `Apakah anda yakin membatalkan hasil periksa ${labelText}${valueSuffix} pada ${docLabel}?`;

                    let confirmed = false;
                    if (typeof Swal !== 'undefined') {
                        const result = await Swal.fire({
                            title: isChecking ? 'Konfirmasi Verifikasi' : 'Konfirmasi Pembatalan',
                            text: confirmText,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: isChecking ? 'Ya, sesuai' : 'Ya, batalkan',
                            cancelButtonText: 'Batal'
                        });
                        confirmed = result.isConfirmed;
                    } else {
                        confirmed = window.confirm(confirmText);
                    }

                    if (!confirmed) {
                        this.checked = !isChecking;
                        return;
                    }

                    try {
                        const response = await apiCall('/api/update-verified.php', 'POST', {
                            siswa_id: siswaId,
                            field_name: fieldName,
                            value: isChecking ? 1 : 0
                        });

                        if (response.success) {
                            if (targetInputId) {
                                const target = document.getElementById(targetInputId);
                                if (target) {
                                    target.disabled = isChecking;
                                }
                            }
                            showAlert(response.message, 'success');
                        } else {
                            this.checked = !isChecking;
                            showAlert(response.message, 'error');
                        }
                    } catch (error) {
                        this.checked = !isChecking;
                        showAlert('Terjadi kesalahan: ' + error.message, 'error');
                    }
                });
            });

            // Handle verval form submission
            document.getElementById('vervalForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                let allowSubmit = false;
                if (typeof Swal !== 'undefined') {
                    const result = await Swal.fire({
                        title: 'Konfirmasi Verval',
                        html: 'Saya menyatakan bahwa semua data telah diperiksa dengan sungguh-sungguh dan perbaikan telah sesuai dengan data sebenarnya. Saya bertanggung jawab atas semua data yang diverifikasi.',
                        input: 'checkbox',
                        inputValue: 0,
                        inputPlaceholder: 'Saya setuju dan bertanggung jawab',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, simpan',
                        cancelButtonText: 'Batal',
                        preConfirm: (value) => {
                            if (!value) {
                                Swal.showValidationMessage('Anda harus menyetujui pernyataan ini');
                            }
                            return value;
                        }
                    });
                    allowSubmit = result.isConfirmed && result.value;
                } else {
                    allowSubmit = window.confirm('Saya menyatakan bahwa semua data telah diperiksa dengan sungguh-sungguh dan perbaikan telah sesuai dengan data sebenarnya. Saya bertanggung jawab atas semua data yang diverifikasi.');
                }

                if (!allowSubmit) {
                    return;
                }

                const formData = new FormData(this);
                
                try {
                    const response = await fetch('/api/update-verval.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        showAlert('Data verval berhasil disimpan', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert(result.message, 'error');
                    }
                } catch (error) {
                    showAlert('Terjadi kesalahan: ' + error.message, 'error');
                }
            });
        }
    </script>
</body>
</html>
