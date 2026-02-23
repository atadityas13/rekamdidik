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

        <!-- Warning Banner & Countdown -->
        <div id="countdownBanner" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; border-radius: 10px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3); border-left: 5px solid #ffd700;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                <span style="font-size: 28px;">⏰</span>
                <div>
                    <h3 style="margin: 0; font-size: 20px;">Batas Waktu Verval</h3>
                    <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.9;">Segera selesaikan verifikasi data Anda sebelum terlambat!</p>
                </div>
            </div>
            
            <div style="background: rgba(0,0,0,0.2); border-radius: 8px; padding: 20px; text-align: center;">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">
                    <div>
                        <div id="countdown-days" style="font-size: 32px; font-weight: bold;">0</div>
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Hari</div>
                    </div>
                    <div>
                        <div id="countdown-hours" style="font-size: 32px; font-weight: bold;">0</div>
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Jam</div>
                    </div>
                    <div>
                        <div id="countdown-minutes" style="font-size: 32px; font-weight: bold;">0</div>
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Menit</div>
                    </div>
                    <div>
                        <div id="countdown-seconds" style="font-size: 32px; font-weight: bold;">0</div>
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Detik</div>
                    </div>
                </div>
                <p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.9;">
                    <strong>Deadline:</strong> 27 Februari 2026 Pukul 22:00 WIB
                </p>
            </div>

            <div id="urgentWarning" style="margin-top: 15px; padding: 12px; background: rgba(0,0,0,0.3); border-radius: 6px; text-align: center; font-size: 13px; display: none;">
                <strong>⚠️ PERHATIAN PENTING!</strong><br>
                Waktu tersisa kurang dari 24 jam. Segera lakukan verifikasi data Anda!
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 30px; color: #333;">Periksa Status Verval Anda</h2>
            
            <div id="alertContainer"></div>

            <!-- Form untuk verval aktif -->
            <div id="vervalFormContainer">
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

            <!-- Pesan ketika verval sudah ditutup -->
            <div id="vervalClosedMessage" style="display: none;">
                <div style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">🔒</div>
                    <h3 style="color: #333; font-size: 22px; margin-bottom: 15px;">Periode Verval Telah Berakhir</h3>
                    <p style="color: #666; font-size: 15px; margin-bottom: 20px;">
                        Maaf, waktu untuk melakukan verifikasi data sudah ditutup pada 27 Februari 2026 pukul 22:00 WIB.
                    </p>
                    <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="color: #666; margin: 0; font-size: 14px;">
                            <strong>Jika ada pertanyaan atau kendala, silahkan hubungi:</strong><br>
                            <span style="color: #667eea; font-size: 16px; font-weight: bold;">📞 Admin Sekolah</span>
                        </p>
                    </div>
                    <p style="color: #999; font-size: 13px; margin: 0;">
                        © 2026 MTsN 11 Majalengka | Verval Rekam Didik Jenjang Sebelumnya
                    </p>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; color: white; font-size: 12px;">
            <p>© 2026 MTsN 11 Majalengka | Verval Rekam Didik Jenjang Sebelumnya</p>
        </div>
    </div>

    <script src="assets/js/utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Countdown Timer untuk Verval Deadline
        const VERVAL_DEADLINE = new Date('2026-02-27T22:00:00+07:00');

        function initCountdownTimer() {
            function updateCountdown() {
                const now = new Date();
                const diff = VERVAL_DEADLINE - now;

                if (diff <= 0) {
                    // Verval sudah ditutup
                    document.getElementById('countdown-days').textContent = '0';
                    document.getElementById('countdown-hours').textContent = '0';
                    document.getElementById('countdown-minutes').textContent = '0';
                    document.getElementById('countdown-seconds').textContent = '0';
                    document.getElementById('countdownBanner').style.background = 'linear-gradient(135deg, #8B0000 0%, #4a0000 100%)';
                    document.getElementById('countdownBanner').innerHTML = `
                        <div style="text-align: center; padding: 20px;">
                            <h3 style="margin: 0; font-size: 24px; color: white;">⏹️ VERVAL SUDAH DITUTUP</h3>
                            <p style="margin: 10px 0 0 0; font-size: 14px; color: #ffcccc;">Periode verifikasi data telah berakhir pada 27 Februari 2026 pukul 22:00 WIB</p>
                        </div>
                    `;
                    
                    // Sembunyikan form dan tampilkan pesan closed
                    document.getElementById('vervalFormContainer').style.display = 'none';
                    document.getElementById('vervalClosedMessage').style.display = 'block';
                    return;
                }

                // Verval masih aktif - tampilkan form
                document.getElementById('vervalFormContainer').style.display = 'block';
                document.getElementById('vervalClosedMessage').style.display = 'none';

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                document.getElementById('countdown-days').textContent = String(days).padStart(2, '0');
                document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('countdown-minutes').textContent = String(minutes).padStart(2, '0');
                document.getElementById('countdown-seconds').textContent = String(seconds).padStart(2, '0');

                // Show urgent warning jika kurang dari 24 jam
                const hoursLeft = (diff / (1000 * 60 * 60));
                if (hoursLeft < 24 && hoursLeft > 0) {
                    document.getElementById('urgentWarning').style.display = 'block';
                } else {
                    document.getElementById('urgentWarning').style.display = 'none';
                }
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        // Initialize countdown saat page load
        document.addEventListener('DOMContentLoaded', initCountdownTimer);

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
                    
                    // Setup event listeners hanya jika belum verval (ada form)
                    if (siswa.verval_status !== 'sudah') {
                        setupResultEventListeners(siswa.id, siswa.verval_status);
                    }
                } else {
                    showAlert(response.message, 'error');
                }
            } catch (error) {
                loadingContainer.style.display = 'none';
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
        });

        // Helper functions - Global untuk digunakan di berbagai tempat
        function formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        }

        function formatTime(datetime) {
            if (!datetime) return '-';
            const d = new Date(datetime);
            return d.toLocaleString('id-ID', { 
                day: '2-digit', 
                month: 'long', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getFieldLabel(fieldName) {
            const fieldLabels = {
                'nik_kk': 'NIK pada KK',
                'nama_kk': 'Nama pada KK',
                'tempat_lahir_kk': 'Tempat Lahir pada KK',
                'tanggal_lahir_kk': 'Tanggal Lahir pada KK',
                'jenis_kelamin_kk': 'Jenis Kelamin pada KK',
                'nama_ibu_kk': 'Nama Ibu Kandung pada KK',
                'nama_ayah_kk': 'Nama Ayah Kandung pada KK',
                'nisn': 'NISN',
                'nama_ijazah': 'Nama pada Ijazah',
                'tempat_lahir_ijazah': 'Tempat Lahir pada Ijazah',
                'tanggal_lahir_ijazah': 'Tanggal Lahir pada Ijazah',
                'jenis_kelamin_ijazah': 'Jenis Kelamin pada Ijazah',
                'nama_ayah_ijazah': 'Nama Ayah Kandung pada Ijazah',
                // Bagian B: Verval Jenjang Sebelumnya
                'nama_sd': 'Nama Sekolah Dasar (SD)',
                'tahun_ajaran_kelulusan': 'Tahun Ajaran Kelulusan',
                'nip_kepala_sekolah': 'NIP Kepala Sekolah',
                'nama_kepala_sekolah': 'Nama Kepala Sekolah',
                'nomor_seri_ijazah': 'Nomor Seri Ijazah',
                'tanggal_terbit_ijazah': 'Tanggal Terbit Ijazah',
                'dokumen_ijazah': 'Dokumen Ijazah'
            };
            return fieldLabels[fieldName] || fieldName;
        }

        function buildDataViewHTML(siswa) {
            // Halaman read-only untuk siswa yang sudah verval

            let html = `
                <div class="alert alert-success" style="margin-bottom: 30px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 48px;">✅</div>
                        <div>
                            <h3 style="margin: 0 0 5px 0; color: #388e3c;">Data Anda Sudah Terverifikasi</h3>
                            <p style="margin: 0;">Terima kasih telah melakukan verifikasi dan rekam data. Silakan simpan halaman ini sebagai bukti.</p>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <h3 style="color: #667eea; margin-bottom: 15px;">📋 Data Dasar Siswa</h3>
                    <div class="data-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">NISN</label>
                            <p style="margin: 0; font-size: 16px;">${siswa.nisn}</p>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Status Verval</label>
                            <p style="margin: 0;"><span class="status-badge status-sudah">Sudah Verval</span></p>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Terakhir Diupdate</label>
                            <p style="margin: 0; font-size: 14px;">${formatTime(siswa.updated_at)}</p>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <h3 style="color: #667eea; margin-bottom: 15px;">📄 Data Pada Kartu Keluarga (KK)</h3>
                    <div class="data-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">NIK</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.nik_kk || '-'}</p>
                            <small style="color: ${siswa.nik_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nik_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nama</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.nama_kk || '-'}</p>
                            <small style="color: ${siswa.nama_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Tempat Lahir</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.tempat_lahir_kk || '-'}</p>
                            <small style="color: ${siswa.tempat_lahir_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.tempat_lahir_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Tanggal Lahir</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${formatDate(siswa.tanggal_lahir_kk)}</p>
                            <small style="color: ${siswa.tanggal_lahir_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.tanggal_lahir_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Jenis Kelamin</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.jenis_kelamin_kk === 'L' ? 'Laki-laki' : siswa.jenis_kelamin_kk === 'P' ? 'Perempuan' : '-'}</p>
                            <small style="color: ${siswa.jenis_kelamin_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.jenis_kelamin_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nama Ibu Kandung</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.nama_ibu_kk || '-'}</p>
                            <small style="color: ${siswa.nama_ibu_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ibu_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nama Ayah Kandung</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.nama_ayah_kk || '-'}</p>
                            <small style="color: ${siswa.nama_ayah_kk_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ayah_kk_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <h3 style="color: #667eea; margin-bottom: 15px;">📜 Data Pada Ijazah</h3>
                    <div class="data-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">NISN</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.nisn || '-'}</p>
                            <small style="color: ${siswa.nisn_verified ? '#388e3c' : '#999'};">
                                ${siswa.nisn_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nama</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.nama_ijazah || '-'}</p>
                            <small style="color: ${siswa.nama_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Tempat Lahir</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.tempat_lahir_ijazah || '-'}</p>
                            <small style="color: ${siswa.tempat_lahir_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.tempat_lahir_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Tanggal Lahir</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${formatDate(siswa.tanggal_lahir_ijazah)}</p>
                            <small style="color: ${siswa.tanggal_lahir_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.tanggal_lahir_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Jenis Kelamin</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.jenis_kelamin_ijazah === 'L' ? 'Laki-laki' : siswa.jenis_kelamin_ijazah === 'P' ? 'Perempuan' : '-'}</p>
                            <small style="color: ${siswa.jenis_kelamin_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.jenis_kelamin_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                        <div class="data-field">
                            <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nama Ayah Kandung</label>
                            <p style="margin: 0 0 3px 0; font-size: 16px;">${siswa.nama_ayah_ijazah || '-'}</p>
                            <small style="color: ${siswa.nama_ayah_ijazah_verified ? '#388e3c' : '#999'};">
                                ${siswa.nama_ayah_ijazah_verified ? '✓ Terverifikasi' : '○ Belum terverifikasi'}
                            </small>
                        </div>
                    </div>
                </div>

                ${siswa.verval_data ? `
                    <div class="data-section">
                        <h3 style="color: #667eea; margin-bottom: 15px;">🏫 Data Verval Jenjang Sebelumnya</h3>
                        <div class="data-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="data-field">
                                <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nama Sekolah Dasar (SD)</label>
                                <p style="margin: 0; font-size: 16px;">${siswa.verval_data.nama_sd || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Tahun Ajaran Kelulusan</label>
                                <p style="margin: 0; font-size: 16px;">${siswa.verval_data.tahun_ajaran_kelulusan || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">NIP Kepala Sekolah</label>
                                <p style="margin: 0; font-size: 16px;">${siswa.verval_data.nip_kepala_sekolah || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nama Kepala Sekolah</label>
                                <p style="margin: 0; font-size: 16px;">${siswa.verval_data.nama_kepala_sekolah || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nomor Seri Ijazah</label>
                                <p style="margin: 0; font-size: 16px;">${siswa.verval_data.nomor_seri_ijazah || '-'}</p>
                            </div>
                            <div class="data-field">
                                <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Tanggal Terbit Ijazah</label>
                                <p style="margin: 0; font-size: 16px;">${formatDate(siswa.verval_data.tanggal_terbit_ijazah)}</p>
                            </div>
                            <div class="data-field">
                                <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Dokumen Ijazah</label>
                                <p style="margin: 0;">${siswa.verval_data.dokumen_ijazah 
                                    ? '<a href="/uploads/ijazah/' + siswa.verval_data.dokumen_ijazah + '" target="_blank" style="color: #667eea; text-decoration: underline;">📄 Lihat Dokumen</a>'
                                    : '-'}</p>
                            </div>
                        </div>
                    </div>
                ` : ''}

                ${siswa.history_perbaikan && siswa.history_perbaikan.length > 0 ? `
                    <div class="data-section">
                        <h3 style="color: #667eea; margin-bottom: 15px;">📝 Riwayat Perbaikan Data</h3>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f5f7fa;">
                                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Data Perbaikan</th>
                                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Sebelumnya</th>
                                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Menjadi</th>
                                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Tanggal Perbaikan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${siswa.history_perbaikan.map(item => `
                                        <tr>
                                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>${getFieldLabel(item.field_name)}</strong></td>
                                            <td style="padding: 10px; border-bottom: 1px solid #eee;">${item.nilai_sebelum || '-'}</td>
                                            <td style="padding: 10px; border-bottom: 1px solid #eee;">${item.nilai_sesudah || '-'}</td>
                                            <td style="padding: 10px; border-bottom: 1px solid #eee;">${formatTime(item.tanggal_perbaikan)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}

                <!-- Section Status Konfirmasi Verval -->
                <div id="konfirmasiVervalSection" style="margin-top: 30px;">
                    <!-- Will be loaded dynamically -->
                </div>

                <!-- Section Re-Upload Ijazah -->
                <div id="reuploadIjazahSection" style="margin-top: 30px;">
                    <!-- Will be loaded dynamically -->
                </div>

                <!-- Section Pengajuan Pembatalan -->
                <div id="pengajuanPembatalanSection" style="margin-top: 30px;">
                    <!-- Will be loaded dynamically -->
                </div>

                <div style="margin-top: 30px; padding: 20px; background: #f5f7fa; border-radius: 8px; text-align: center;">
                    <p style="margin: 0 0 15px 0; color: #666;">Terima kasih telah melakukan verifikasi dan rekam data dengan benar.</p>
                    <p style="margin: 0 0 15px 0; color: #ff9800; font-weight: 600;">⚠️ Penting: Silakan cek berkala untuk memastikan tidak ada data yang perlu konfirmasi lebih lanjut.</p>
                    <button type="button" onclick="location.reload()" class="btn-secondary">
                        🔄 Cek Lagi
                    </button>
                </div>
            `;

            // Load status konfirmasi and pengajuan pembatalan after rendering
            setTimeout(() => {
                loadKonfirmasiStatus(siswa.id);
                loadReuploadIjazahStatus(siswa.id);
                loadPengajuanPembatalanStatus(siswa.id);
            }, 500);

            return html;
        }

        function buildResultHTML(siswa) {
            // Jika sudah verval, tampilkan halaman data read-only
            if (siswa.verval_status === 'sudah') {
                return buildDataViewHTML(siswa);
            }

            // Jika belum verval, tampilkan form verifikasi
            const statusClass = siswa.verval_status === 'sudah' ? 'status-sudah' : 'status-belum';
            const statusText = siswa.verval_status === 'sudah' ? 'Sudah Melakukan Verval' : 'Belum Melakukan Verval';
            const statusMessage = siswa.verval_status === 'sudah' 
                ? 'Data Anda telah berhasil diverifikasi sesuai dengan dokumen pendukung.' 
                : 'Data Anda belum diverifikasi. Silakan lengkapi data dan upload dokumen pendukung di bawah.';
            const disableAttr = (verified) => verified ? 'disabled' : '';
            const checkedAttr = (verified) => verified ? 'checked' : '';

            let html = `
                <form id="vervalForm">
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
                                        <input type="text" id="nik_kk" name="nik_kk" value="${siswa.nik_kk || ''}" class="data-input" data-field="nik_kk" ${disableAttr(siswa.nik_kk_verified)}>
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
                                        <input type="text" id="nisn_ijazah" value="${siswa.nisn}" disabled data-always-disabled="true">
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nisn_verified" data-target-input="nisn_ijazah" ${checkedAttr(siswa.nisn_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Nama</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="text" id="nama_kk" name="nama_kk" value="${siswa.nama_kk || ''}" class="data-input" data-field="nama_kk" ${disableAttr(siswa.nama_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_kk_verified" data-target-input="nama_kk" ${checkedAttr(siswa.nama_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="text" id="nama_ijazah" name="nama_ijazah" value="${siswa.nama_ijazah || ''}" class="data-input" data-field="nama_ijazah" ${disableAttr(siswa.nama_ijazah_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_ijazah_verified" data-target-input="nama_ijazah" ${checkedAttr(siswa.nama_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Tempat Lahir</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="text" id="tempat_lahir_kk" name="tempat_lahir_kk" value="${siswa.tempat_lahir_kk || ''}" class="data-input" data-field="tempat_lahir_kk" ${disableAttr(siswa.tempat_lahir_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tempat_lahir_kk_verified" data-target-input="tempat_lahir_kk" ${checkedAttr(siswa.tempat_lahir_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="text" id="tempat_lahir_ijazah" name="tempat_lahir_ijazah" value="${siswa.tempat_lahir_ijazah || ''}" class="data-input" data-field="tempat_lahir_ijazah" ${disableAttr(siswa.tempat_lahir_ijazah_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tempat_lahir_ijazah_verified" data-target-input="tempat_lahir_ijazah" ${checkedAttr(siswa.tempat_lahir_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Tanggal Lahir</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <input type="date" id="tanggal_lahir_kk" name="tanggal_lahir_kk" value="${siswa.tanggal_lahir_kk || ''}" class="data-input" data-field="tanggal_lahir_kk" ${disableAttr(siswa.tanggal_lahir_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tanggal_lahir_kk_verified" data-target-input="tanggal_lahir_kk" ${checkedAttr(siswa.tanggal_lahir_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="date" id="tanggal_lahir_ijazah" name="tanggal_lahir_ijazah" value="${siswa.tanggal_lahir_ijazah || ''}" class="data-input" data-field="tanggal_lahir_ijazah" ${disableAttr(siswa.tanggal_lahir_ijazah_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="tanggal_lahir_ijazah_verified" data-target-input="tanggal_lahir_ijazah" ${checkedAttr(siswa.tanggal_lahir_ijazah_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="compare-label" data-label="">Jenis Kelamin</td>
                                    <td class="compare-cell" data-label="Data pada Kartu Keluarga">
                                        <select id="jenis_kelamin_kk" name="jenis_kelamin_kk" class="data-input" data-field="jenis_kelamin_kk" ${disableAttr(siswa.jenis_kelamin_kk_verified)}>
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
                                        <select id="jenis_kelamin_ijazah" name="jenis_kelamin_ijazah" class="data-input" data-field="jenis_kelamin_ijazah" ${disableAttr(siswa.jenis_kelamin_ijazah_verified)}>
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
                                        <input type="text" id="nama_ibu_kk" name="nama_ibu_kk" value="${siswa.nama_ibu_kk || ''}" class="data-input" data-field="nama_ibu_kk" ${disableAttr(siswa.nama_ibu_kk_verified)}>
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
                                        <input type="text" id="nama_ayah_kk" name="nama_ayah_kk" value="${siswa.nama_ayah_kk || ''}" class="data-input" data-field="nama_ayah_kk" ${disableAttr(siswa.nama_ayah_kk_verified)}>
                                        <div class="checkbox-group compact">
                                            <input type="checkbox" class="verify-checkbox" data-verify-field="nama_ayah_kk_verified" data-target-input="nama_ayah_kk" ${checkedAttr(siswa.nama_ayah_kk_verified)}>
                                            <label>Sudah sesuai</label>
                                        </div>
                                    </td>
                                    <td class="compare-cell" data-label="Data pada Ijazah">
                                        <input type="text" id="nama_ayah_ijazah" name="nama_ayah_ijazah" value="${siswa.nama_ayah_ijazah || ''}" class="data-input" data-field="nama_ayah_ijazah" ${disableAttr(siswa.nama_ayah_ijazah_verified)}>
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
                    
                    <input type="hidden" id="siswa_id" name="siswa_id" value="${siswa.id}">
                        
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
                            <label for="dokumen_ijazah">Upload Dokumen Ijazah Asli (JPG, JPEG, PNG - Max 1MB)</label>
                            <h4><b>*Wajib upload hasil scan Ijazah Asli</b></h4>
                            
                            <!-- Panduan Upload Ijazah dengan Contoh dari Gambar Asli -->
                            <div style="background: #f5f5f5; padding: 10px; border-radius: 6px; margin-bottom: 15px; border: 2px solid #667eea;">
                                <h5 style="margin: 0 0 10px 0; color: #333; font-size: 13px;">📋 Panduan Upload Ijazah</h5>
                                
                                <!-- Contoh BENAR -->
                                <div style="background: white; padding: 8px; border-radius: 4px; border: 2px solid #4caf50; margin-bottom: 10px;">
                                    <div style="color: #4caf50; font-weight: 600; font-size: 11px; margin-bottom: 6px;">✓ CONTOH BENAR</div>
                                    <img src="/assets/images/contohijazah.jpg" alt="Contoh Ijazah Benar" style="max-width: 200px; height: auto; border-radius: 3px; border: 1px solid #ddd; margin-bottom: 6px; display: block;">
                                    <ul style="margin: 0; padding-left: 18px; font-size: 10px; color: #555; line-height: 1.4; background: #f1f8e9; padding: 6px 8px; border-radius: 3px; margin: 0;">
                                        <li>✓ Bagian depan saja</li>
                                        <li>✓ Jelas dan terang</li>
                                        <li>✓ Teks terbaca</li>
                                        <li>✓ Posisi lurus</li>
                                    </ul>
                                </div>
                                
                                <!-- Contoh SALAH -->
                                <div style="background: white; padding: 8px; border-radius: 4px; border: 2px solid #f44336;">
                                    <div style="color: #f44336; font-weight: 600; font-size: 11px; margin-bottom: 8px;">✗ JANGAN - Kondisi Ditolak</div>
                                    
                                    <!-- Baris 1: Buram & Gelap (kecil) -->
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px; margin-bottom: 6px;">
                                        <!-- Buram -->
                                        <div>
                                            <small style="color: #d32f2f; font-weight: 600; font-size: 9px; display: block; margin-bottom: 2px;">Buram</small>
                                            <img src="/assets/images/contohijazah.jpg" alt="Buram" style="width: 100%; height: auto; border-radius: 3px; border: 1px solid #ddd; filter: blur(4px); max-width: 100px;">
                                        </div>
                                        <!-- Gelap -->
                                        <div>
                                            <small style="color: #d32f2f; font-weight: 600; font-size: 9px; display: block; margin-bottom: 2px;">Gelap</small>
                                            <img src="/assets/images/contohijazah.jpg" alt="Gelap" style="width: 100%; height: auto; border-radius: 3px; border: 1px solid #ddd; filter: brightness(0.4); max-width: 100px;">
                                        </div>
                                        <!-- B&W -->
                                        <div>
                                            <small style="color: #d32f2f; font-weight: 600; font-size: 9px; display: block; margin-bottom: 2px;">B&W / Buruk</small>
                                            <img src="/assets/images/contohijazah.jpg" alt="BW" style="width: 100%; height: auto; border-radius: 3px; border: 1px solid #ddd; filter: grayscale(100%) contrast(1.5); max-width: 100px;">
                                        </div>
                                    </div>
                                    
                                    <!-- Baris 2: Terpotong, Terlalu Dekat, Miring (full size) -->
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px;">
                                        <!-- Terpotong (bagian bawah terpotong) -->
                                        <div>
                                            <small style="color: #d32f2f; font-weight: 600; font-size: 9px; display: block; margin-bottom: 2px;">Terpotong</small>
                                            <div style="width: 100%; height: 120px; border-radius: 3px; border: 1px solid #ddd; overflow: hidden;">
                                                <img src="/assets/images/contohijazah.jpg" alt="Terpotong" style="width: 100%; height: auto; display: block; margin-top: -20px;">
                                            </div>
                                        </div>
                                        <!-- Terlalu Dekat (zoom tinggi) -->
                                        <div>
                                            <small style="color: #d32f2f; font-weight: 600; font-size: 9px; display: block; margin-bottom: 2px;">Terlalu Dekat</small>
                                            <div style="width: 100%; height: 120px; border-radius: 3px; border: 1px solid #ddd; overflow: hidden; background: #fafafa;">
                                                <img src="/assets/images/contohijazah.jpg" alt="Dekat" style="width: 280%; height: 280%; display: block; object-fit: cover; object-position: 35% 15%; border-radius: 2px;">
                                            </div>
                                        </div>
                                        <!-- Miring -->
                                        <div>
                                            <small style="color: #d32f2f; font-weight: 600; font-size: 9px; display: block; margin-bottom: 2px;">Miring</small>
                                            <img src="/assets/images/contohijazah.jpg" alt="Miring" style="width: 100%; height: auto; border-radius: 3px; border: 1px solid #ddd; transform: rotate(-15deg); transform-origin: center; display: block; max-width: 120px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="file" id="dokumen_ijazah" name="dokumen_ijazah" accept=".jpg,.jpeg,.png" required>
                            ${siswa.verval_data?.dokumen_ijazah ? `<small style="color: #666; margin-top: 5px; display: block;">📎 File saat ini: <a href="/uploads/ijazah/${siswa.verval_data.dokumen_ijazah}" target="_blank" style="color: #667eea; text-decoration: underline;">📥 Download Dokumen</a></small>` : ''}
                        </div>

                        <div class="button-group">
                            <button type="submit" class="button button-success">💾 Submit Data Verval</button>
                            <button type="reset" class="button button-secondary">↺ Ulangi Pengisian</button>
                        </div>
                </div>
                </form>
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
                                    <th>Data</th>
                                    <th>Sebelumnya</th>
                                    <th>Sesudah Perbaikan</th>
                                    <th>Tanggal Perbaikan</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            history.forEach(item => {
                html += `
                            <tr>
                                <td><strong>${getFieldLabel(item.field_name)}</strong></td>
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
                    console.log('🔔 Checkbox clicked');
                    
                    const fieldName = this.getAttribute('data-verify-field');
                    const targetInputId = this.getAttribute('data-target-input');

                    console.log('📋 Field info:', { fieldName, targetInputId });

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
                    let fieldValue = '';
                    let actualFieldName = '';
                    
                    if (targetInputId) {
                        const target = document.getElementById(targetInputId);
                        if (target) {
                            // Get actual field name (remove _kk or _ijazah suffix from target id)
                            actualFieldName = targetInputId;
                            fieldValue = target.value;
                            
                            if (target.tagName === 'SELECT') {
                                valueText = target.options[target.selectedIndex] ? target.options[target.selectedIndex].text : '';
                            } else {
                                valueText = target.value;
                            }
                        }
                    }

                    console.log('📝 Value info:', { actualFieldName, fieldValue, valueText });

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

                    console.log('✅ User confirmed:', confirmed);

                    if (!confirmed) {
                        this.checked = !isChecking;
                        return;
                    }

                    try {
                        // NEW: Save data field + verified flag sekaligus
                        const formData = new FormData();
                        formData.append('siswa_id', siswaId);
                        formData.append('field_name', actualFieldName);
                        formData.append('field_value', fieldValue);
                        formData.append('verified_flag', fieldName);
                        formData.append('is_verified', isChecking ? 1 : 0);

                        console.log('📤 Sending verification request:', {
                            siswa_id: siswaId,
                            field_name: actualFieldName,
                            field_value: fieldValue,
                            verified_flag: fieldName,
                            is_verified: isChecking ? 1 : 0
                        });

                        const response = await fetch('/api/save-field-verified.php', {
                            method: 'POST',
                            body: formData
                        });

                        console.log('📡 Response status:', response.status, response.statusText);

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const result = await response.json();
                        console.log('📥 Response:', result);

                        if (result.success) {
                            // Disable/enable target input based on verification status
                            if (targetInputId) {
                                const target = document.getElementById(targetInputId);
                                if (target && !target.hasAttribute('data-always-disabled')) {
                                    console.log('🔒 Setting field disabled:', isChecking);
                                    target.disabled = isChecking;
                                }
                            }
                            
                            showAlert(result.message, 'success');
                            
                            // Update status jika semua sudah verified
                            if (result.status_updated === 'sudah') {
                                showAlert('✅ Semua data Bagian A sudah diverifikasi! Status akan diupdate setelah Bagian B disimpan.', 'success');
                                setTimeout(() => location.reload(), 1500);
                            }
                        } else {
                            console.warn('⚠️ Save failed:', result.message);
                            this.checked = !isChecking;
                            showAlert(result.message, 'error');
                        }
                    } catch (error) {
                        console.error('❌ Error:', error);
                        this.checked = !isChecking;
                        showAlert('Terjadi kesalahan: ' + error.message, 'error');
                    }
                });
            });

            // Handle verval form submission (Bagian B only)
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

                // Validasi ukuran file sebelum submit
                const fileInput = document.getElementById('dokumen_ijazah');
                if (fileInput && fileInput.files && fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const maxSize = 1 * 1024 * 1024; // 1MB
                    
                    if (file.size > maxSize) {
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        showAlert(`Ukuran file terlalu besar (${fileSizeMB}MB). Maksimal 1MB. Silakan kompres atau resize gambar terlebih dahulu.`, 'error');
                        return;
                    }
                }

                // Collect form data (only Bagian B fields will be used)
                const formData = new FormData(this);
                
                // Debug: Log all form data being sent
                console.log('📤 Form Data Submitted (Bagian B):');
                const formEntries = {};
                for (let [key, value] of formData.entries()) {
                    if (key === 'dokumen_ijazah') {
                        console.log(`  ${key}: [File: ${value.name}]`);
                        formEntries[key] = `[File: ${value.name}]`;
                    } else if (!key.includes('_verified') && !['nik_kk', 'nama_kk', 'nama_ijazah', 'tempat_lahir_kk', 'tempat_lahir_ijazah', 'tanggal_lahir_kk', 'tanggal_lahir_ijazah', 'jenis_kelamin_kk', 'jenis_kelamin_ijazah', 'nama_ibu_kk', 'nama_ayah_kk', 'nama_ayah_ijazah'].includes(key)) {
                        console.log(`  ${key}: ${value}`);
                        formEntries[key] = value;
                    }
                }
                
                // Check for required Bagian B fields only
                const requiredBagianB = ['nama_sd', 'tahun_ajaran_kelulusan', 'dokumen_ijazah'];
                const missingBagianB = requiredBagianB.filter(f => !formEntries[f]);
                if (missingBagianB.length > 0) {
                    console.warn('⚠️  Missing Bagian B fields:', missingBagianB);
                }
                
                try {
                    // NEW: Hanya save Bagian B (jenjang sebelumnya + upload)
                    const response = await fetch('/api/save-bagian-b.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        console.log('✅ Bagian B saved:', result);
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: '<p>Data verval Anda telah berhasil disimpan.</p><p>Halaman akan menampilkan data verval Anda dalam beberapa saat...</p>',
                                timer: 2500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            showAlert(result.message, 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    } else {
                        console.error('❌ Error:', result.message);
                        showAlert(result.message, 'error');
                    }
                } catch (error) {
                    console.error('⚠️ Server Error:', error);
                    showAlert('Terjadi kesalahan: ' + error.message, 'error');
                }
            });
        }

        // ========================================
        // KONFIRMASI VERVAL STATUS (Enhanced)
        // ========================================
        async function loadKonfirmasiStatus(siswaId) {
            try {
                const response = await fetch(`/api/check-konfirmasi-status.php?siswa_id=${siswaId}`);
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    const container = document.getElementById('konfirmasiVervalSection');
                    
                    // Check approval status
                    if (data.approval_status === 'approved') {
                        // Semua sudah disetujui
                        container.innerHTML = `
                            <div class="alert alert-success" style="margin-bottom: 20px;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="font-size: 36px;">✅</div>
                                    <div>
                                        <h3 style="margin: 0 0 5px 0; color: #388e3c;">Verval Telah Disetujui!</h3>
                                        <p style="margin: 0;">Semua data Anda telah dikonfirmasi dan disetujui oleh admin.</p>
                                        ${data.catatan_konfirmasi ? `<p style="margin: 5px 0 0 0; font-style: italic;">Catatan: ${data.catatan_konfirmasi}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (data.approval_status === 'need_confirmation') {
                        // Ada field yang perlu action dari siswa
                        const fieldsNeedAction = data.fields_need_action || [];
                        
                        if (fieldsNeedAction.length > 0) {
                            let html = `
                                <div class="alert" style="background: #fff3e0; border-left: 4px solid #ff9800; margin-bottom: 20px;">
                                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                        <div style="font-size: 36px;">⚠️</div>
                                        <div>
                                            <h3 style="margin: 0 0 5px 0; color: #e65100;">Ada Data yang Perlu Konfirmasi!</h3>
                                            <p style="margin: 0;">Ada ${fieldsNeedAction.length} data yang memerlukan tindakan dari Anda.</p>
                                        </div>
                                    </div>

                                    <div style="background: white; padding: 15px; border-radius: 6px;">
                            `;

                            fieldsNeedAction.forEach(field => {
                                const isResponded = field.status === 'student_responded';
                                const tipe = field.tipe_konfirmasi;
                                
                                html += buildFieldConfirmationCard(siswaId, field, isResponded, tipe);
                            });

                            html += `
                                    </div>
                                </div>
                            `;

                            container.innerHTML = html;
                        }
                    } else if (data.approval_status === 'pending') {
                        // Menunggu review admin
                        container.innerHTML = `
                            <div class="alert" style="background: #e3f2fd; border-left: 4px solid #2196f3; margin-bottom: 20px;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="font-size: 36px;">⏳</div>
                                    <div>
                                        <h3 style="margin: 0 0 5px 0; color: #1976d2;">Menunggu Review Admin</h3>
                                        <p style="margin: 0;">Data verval Anda sedang dalam proses review oleh admin. Silakan cek kembali secara berkala.</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }
            } catch (error) {
                console.error('Error loading konfirmasi status:', error);
            }
        }

        function buildFieldConfirmationCard(siswaId, field, isResponded, tipe) {
            const formId = `form-${field.field_name}`;
            
            // Status badge
            let statusBadge = '';
            if (isResponded) {
                statusBadge = '<span style="background: #9c27b0; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;">📤 Terkirim</span>';
            } else {
                if (tipe === 'need_document') {
                    statusBadge = '<span style="background: #f44336; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;">📄 Perlu Berkas</span>';
                } else if (tipe === 'need_confirmation') {
                    statusBadge = '<span style="background: #ff9800; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;">💬 Perlu Konfirmasi</span>';
                } else if (tipe === 'need_edit') {
                    statusBadge = '<span style="background: #2196f3; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;">✏️ Perlu Edit</span>';
                }
            }

            let html = `
                <div style="padding: 15px; border-bottom: 1px solid #eee; ${isResponded ? 'background: #f5f5f5;' : ''}">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div style="flex: 1;">
                            <strong style="color: #333; display: block; margin-bottom: 5px;">${getFieldLabel(field.field_name)}</strong>
                            
                            ${field.catatan_admin ? `
                                <div style="background: #fff3e0; padding: 10px; border-radius: 4px; margin: 5px 0; font-size: 13px; border-left: 3px solid #ff9800;">
                                    <strong style="color: #e65100;">📌 Catatan Admin:</strong>
                                    <p style="margin: 5px 0 0 0; color: #333;">${field.catatan_admin}</p>
                                </div>
                            ` : ''}

                            ${isResponded && field.pesan_siswa ? `
                                <div style="background: #e8f5e9; padding: 10px; border-radius: 4px; margin: 5px 0; font-size: 13px; border-left: 3px solid #4caf50;">
                                    <strong style="color: #2e7d32;">💬 Pesan Anda:</strong>
                                    <p style="margin: 5px 0 0 0; color: #333;">${field.pesan_siswa}</p>
                                </div>
                            ` : ''}

                            ${isResponded && field.nilai_baru_siswa ? `
                                <div style="background: #e3f2fd; padding: 10px; border-radius: 4px; margin: 5px 0; font-size: 13px; border-left: 3px solid #2196f3;">
                                    <strong style="color: #1976d2;">✏️ Nilai Baru:</strong>
                                    <p style="margin: 5px 0 0 0; color: #333;">${field.nilai_baru_siswa}</p>
                                </div>
                            ` : ''}

                            ${field.berkas_pendukung ? `
                                <div style="margin-top: 8px;">
                                    <a href="/uploads/berkas_pendukung/${field.berkas_pendukung}" target="_blank" 
                                       style="color: #4caf50; font-size: 13px; text-decoration: underline;">
                                        ✓ Berkas telah diupload - Klik untuk melihat
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                        <div>
                            ${statusBadge}
                        </div>
                    </div>

                    ${!isResponded ? buildConfirmationForm(siswaId, field, tipe, formId) : ''}
                </div>
            `;

            return html;
        }

        function buildConfirmationForm(siswaId, field, tipe, formId) {
            const fieldName = field.field_name;

            if (tipe === 'need_document') {
                // Hanya upload berkas
                return `
                    <div style="margin-top: 10px; padding: 15px; background: #fafafa; border-radius: 4px; border: 1px solid #e0e0e0;">
                        <form id="${formId}" onsubmit="submitKonfirmasiSiswa(event, ${siswaId}, '${fieldName}', 'need_document')" style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">📄 Upload Berkas Pendukung:</label>
                                <input type="file" 
                                       name="berkas" 
                                       accept=".jpg,.jpeg,.png,.pdf" 
                                       required
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                <small style="color: #666; display: block; margin-top: 3px;">JPG, PNG, atau PDF (Max 2MB)</small>
                            </div>
                            <button type="submit" class="button button-primary" style="align-self: flex-start; padding: 10px 20px;">
                                📤 Kirim Berkas
                            </button>
                        </form>
                    </div>
                `;
            } else if (tipe === 'need_confirmation') {
                // Hanya pesan konfirmasi (no file)
                return `
                    <div style="margin-top: 10px; padding: 15px; background: #fafafa; border-radius: 4px; border: 1px solid #e0e0e0;">
                        <form id="${formId}" onsubmit="submitKonfirmasiSiswa(event, ${siswaId}, '${fieldName}', 'need_confirmation')" style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">💬 Pesan Konfirmasi:</label>
                                <textarea 
                                    name="pesan" 
                                    required 
                                    minlength="10"
                                    rows="3"
                                    placeholder="Jelaskan alasan atau konfirmasi data (minimal 10 karakter)"
                                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; font-family: inherit; resize: vertical;"></textarea>
                                <small style="color: #666; display: block; margin-top: 3px;">Minimal 10 karakter</small>
                            </div>
                            <button type="submit" class="button button-primary" style="align-self: flex-start; padding: 10px 20px;">
                                💬 Kirim Konfirmasi
                            </button>
                        </form>
                    </div>
                `;
            } else if (tipe === 'need_edit') {
                // Edit data + pesan + upload berkas
                return `
                    <div style="margin-top: 10px; padding: 15px; background: #fafafa; border-radius: 4px; border: 1px solid #e0e0e0;">
                        <form id="${formId}" onsubmit="submitKonfirmasiSiswa(event, ${siswaId}, '${fieldName}', 'need_edit')" style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">✏️ Nilai Baru:</label>
                                <input type="text" 
                                       name="nilai_baru" 
                                       required 
                                       placeholder="Masukkan nilai yang benar"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">💬 Pesan Penjelasan:</label>
                                <textarea 
                                    name="pesan" 
                                    required 
                                    minlength="10"
                                    rows="2"
                                    placeholder="Jelaskan alasan perubahan data (minimal 10 karakter)"
                                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; font-family: inherit; resize: vertical;"></textarea>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">📄 Berkas Pendukung:</label>
                                <input type="file" 
                                       name="berkas" 
                                       accept=".jpg,.jpeg,.png,.pdf" 
                                       required
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                <small style="color: #666; display: block; margin-top: 3px;">JPG, PNG, atau PDF (Max 2MB)</small>
                            </div>
                            <button type="submit" class="button button-primary" style="align-self: flex-start; padding: 10px 20px;">
                                ✏️ Kirim Perubahan
                            </button>
                        </form>
                    </div>
                `;
            }

            return '';
        }

        async function submitKonfirmasiSiswa(event, siswaId, fieldName, tipe) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            formData.append('siswa_id', siswaId);
            formData.append('field_name', fieldName);
            formData.append('tipe_konfirmasi', tipe);

            // Validasi file size jika ada file
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput && fileInput.files[0]) {
                const file = fileInput.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    showAlert(`Ukuran file terlalu besar (${sizeMB}MB). Maksimal 2MB`, 'error');
                    return;
                }
            }

            // Disable button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Mengirim...';

            try {
                const response = await fetch('/api/submit-konfirmasi-siswa.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Konfirmasi berhasil dikirim! ✓', 'success');
                    // Reload status setelah 1 detik
                    setTimeout(() => loadKonfirmasiStatus(siswaId), 1000);
                } else {
                    showAlert(result.message || 'Terjadi kesalahan', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
                console.error('Error submit konfirmasi:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // ========================================
        // RE-UPLOAD IJAZAH
        // ========================================
        async function loadReuploadIjazahStatus(siswaId) {
            try {
                const response = await fetch(`/api/check-reupload-ijazah.php?siswa_id=${siswaId}`);
                const result = await response.json();

                if (result.success && result.has_request) {
                    const container = document.getElementById('reuploadIjazahSection');
                    
                    container.innerHTML = `
                        <div class="data-section" style="border-left: 4px solid #ff5722;">
                            <h3 style="color: #ff5722; margin-bottom: 15px;">📸 Permintaan Upload Ulang Ijazah</h3>
                            
                            <div class="alert" style="background: #fff3e0; border: 1px solid #ff9800; margin-bottom: 15px;">
                                <div style="padding: 10px;">
                                    <strong style="color: #e65100; display: block; margin-bottom: 8px;">⚠️ Admin meminta Anda untuk mengupload ulang ijazah</strong>
                                    <div style="background: white; padding: 12px; border-radius: 4px; border-left: 3px solid #ff5722;">
                                        <strong style="color: #333;">Catatan Admin:</strong>
                                        <p style="margin: 5px 0 0 0; color: #555;">${result.catatan_admin}</p>
                                    </div>
                                </div>
                            </div>

                            <form id="formReuploadIjazah" onsubmit="submitReuploadIjazah(event, ${siswaId})" style="background: #fafafa; padding: 20px; border-radius: 6px; border: 1px solid #e0e0e0;">
                                <div style="margin-bottom: 15px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">📄 Upload Ijazah Baru:</label>
                                    <input type="file" 
                                           name="ijazah" 
                                           accept=".jpg,.jpeg,.png" 
                                           required
                                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background: white;">
                                    <small style="color: #666; display: block; margin-top: 5px;">
                                        Format: JPG atau PNG | Ukuran Maksimal: 1MB<br>
                                        Pastikan foto <strong>jelas, tidak blur, dan tidak terpotong</strong>
                                    </small>
                                </div>
                                <button type="submit" class="button button-primary" style="padding: 12px 24px;">
                                    📤 Upload Ijazah Baru
                                </button>
                            </form>
                        </div>
                    `;
                } else {
                    // Tidak ada permintaan re-upload, sembunyikan section
                    const container = document.getElementById('reuploadIjazahSection');
                    if (container) {
                        container.innerHTML = '';
                    }
                }
            } catch (error) {
                console.error('Error loading reupload ijazah status:', error);
            }
        }

        async function submitReuploadIjazah(event, siswaId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            formData.append('siswa_id', siswaId);

            // Validasi file
            const fileInput = form.querySelector('input[name="ijazah"]');
            const file = fileInput.files[0];

            if (!file) {
                showAlert('Silakan pilih file terlebih dahulu', 'error');
                return;
            }

            // Validasi ukuran
            const maxSize = 1 * 1024 * 1024; // 1MB
            if (file.size > maxSize) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                showAlert(`Ukuran file terlalu besar (${sizeMB}MB). Maksimal 1MB`, 'error');
                return;
            }

            // Validasi tipe file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showAlert('Format file tidak valid. Gunakan JPG atau PNG', 'error');
                return;
            }

            // Disable button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Mengupload...';

            try {
                const response = await fetch('/api/reupload-ijazah.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Ijazah berhasil diupload ulang! ✓', 'success');
                    // Reload status setelah 1 detik
                    setTimeout(() => {
                        loadReuploadIjazahStatus(siswaId);
                        // Juga reload verval jika perlu update tampilan
                        if (window.loadVervalBagianB) loadVervalBagianB(siswaId);
                    }, 1000);
                } else {
                    showAlert(result.message || 'Terjadi kesalahan', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
                console.error('Error reupload ijazah:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // ========================================
        // PENGAJUAN PEMBATALAN VERVAL
        // ========================================
        async function loadPengajuanPembatalanStatus(siswaId) {
            try {
                const response = await fetch(`/api/check-pengajuan-pembatalan.php?siswa_id=${siswaId}`);
                const result = await response.json();

                if (result.success) {
                    const container = document.getElementById('pengajuanPembatalanSection');
                    
                    if (result.has_pengajuan && result.data) {
                        const pengajuan = result.data;
                        
                        if (pengajuan.status === 'menunggu') {
                            // Ada pengajuan yang sedang menunggu
                            container.innerHTML = `
                                <div class="data-section" style="border-left: 4px solid #ff9800;">
                                    <h3 style="color: #ff9800; margin-bottom: 15px;">⏳ Pengajuan Pembatalan Sedang Diproses</h3>
                                    <div class="alert" style="background: #fff3e0; border: 1px solid #ff9800; color: #e65100;">
                                        <p style="margin: 0 0 10px 0;"><strong>Status:</strong> Menunggu Persetujuan Admin</p>
                                        <p style="margin: 0 0 10px 0;"><strong>Tanggal Pengajuan:</strong> ${new Date(pengajuan.created_at).toLocaleString('id-ID')}</p>
                                        <p style="margin: 0;"><strong>Alasan:</strong></p>
                                        <p style="margin: 5px 0 0 0; padding: 10px; background: white; border-radius: 4px;">${pengajuan.alasan}</p>
                                    </div>
                                </div>
                            `;
                        } else if (pengajuan.status === 'ditolak') {
                            // Pengajuan ditolak, bisa ajukan lagi
                            container.innerHTML = buildPengajuanFormHTML(siswaId, pengajuan);
                        } else if (pengajuan.status === 'disetujui') {
                            // Pengajuan disetujui (harusnya gak mungkin sampai sini karena status verval sudah berubah)
                            container.innerHTML = `
                                <div class="data-section" style="border-left: 4px solid #4caf50;">
                                    <h3 style="color: #4caf50; margin-bottom: 15px;">✅ Pengajuan Pembatalan Disetujui</h3>
                                    <div class="alert alert-success">
                                        <p style="margin: 0;">Pengajuan pembatalan Anda telah disetujui oleh admin. Status verval Anda telah direset.</p>
                                    </div>
                                </div>
                            `;
                        }
                    } else {
                        // Belum ada pengajuan, tampilkan form
                        container.innerHTML = buildPengajuanFormHTML(siswaId, null);
                    }
                }
            } catch (error) {
                console.error('Error loading pengajuan status:', error);
            }
        }

        function buildPengajuanFormHTML(siswaId, pengajuanSebelumnya) {
            let html = `
                <div class="data-section" style="border-left: 4px solid #f44336;">
                    <h3 style="color: #f44336; margin-bottom: 15px;">🔄 Pengajuan Pembatalan Verval</h3>
            `;

            if (pengajuanSebelumnya && pengajuanSebelumnya.status === 'ditolak') {
                html += `
                    <div class="alert" style="background: #ffebee; border: 1px solid #f44336; color: #c62828; margin-bottom: 20px;">
                        <p style="margin: 0 0 10px 0;"><strong>❌ Pengajuan Sebelumnya Ditolak</strong></p>
                        <p style="margin: 0 0 10px 0;"><strong>Tanggal:</strong> ${new Date(pengajuanSebelumnya.tanggal_diproses).toLocaleString('id-ID')}</p>
                        ${pengajuanSebelumnya.catatan_admin ? `<p style="margin: 0;"><strong>Catatan Admin:</strong> ${pengajuanSebelumnya.catatan_admin}</p>` : ''}
                    </div>
                `;
            }

            html += `
                    <p style="margin-bottom: 15px; color: #666;">
                        Jika Anda menemukan kesalahan pada data yang telah diverifikasi, Anda dapat mengajukan pembatalan verval.
                        Pengajuan akan ditinjau oleh admin.
                    </p>
                    <form id="formPengajuanPembatalan" onsubmit="submitPengajuanPembatalan(event, ${siswaId})">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
                                Alasan Pembatalan<span style="color: red;">*</span>
                            </label>
                            <textarea 
                                name="alasan" 
                                id="alasanPembatalan" 
                                rows="5" 
                                required 
                                minlength="20"
                                placeholder="Jelaskan alasan pembatalan verval (minimal 20 karakter)"
                                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; font-family: inherit; resize: vertical;"
                            ></textarea>
                            <small style="color: #999;">Minimal 20 karakter. Jelaskan dengan detail alasan pembatalan.</small>
                        </div>
                        <div style="margin-top: 20px;">
                            <button type="submit" class="btn-primary" style="background: #f44336; border: none; padding: 12px 24px; font-size: 15px; font-weight: 600; border-radius: 6px; cursor: pointer; transition: all 0.3s; width: 100%; max-width: 300px;">
                                ⚠️ Ajukan Pembatalan Verval
                            </button>
                            <p style="margin: 10px 0 0 0; color: #666; font-size: 13px; line-height: 1.6;">
                                <strong style="color: #f44336;">⚠️ Perhatian:</strong> Jika disetujui, status verval akan direset dan Anda harus melakukan verifikasi ulang dari awal.
                            </p>
                        </div>
                    </form>
                </div>
            `;

            return html;
        }

        async function submitPengajuanPembatalan(event, siswaId) {
            event.preventDefault();

            const form = event.target;
            const alasan = form.alasan.value.trim();

            if (alasan.length < 20) {
                showAlert('Alasan pembatalan minimal 20 karakter', 'error');
                return;
            }

            // Konfirmasi
            let confirmed = false;
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: 'Konfirmasi Pengajuan Pembatalan',
                    html: `
                        <p>Anda yakin ingin mengajukan pembatalan verval?</p>
                        <p style="color: #f44336; font-weight: bold;">Pengajuan ini akan ditinjau oleh admin.</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ajukan',
                    confirmButtonColor: '#f44336',
                    cancelButtonText: 'Batal'
                });
                confirmed = result.isConfirmed;
            } else {
                confirmed = window.confirm('Anda yakin ingin mengajukan pembatalan verval?');
            }

            if (!confirmed) return;

            try {
                const formData = new FormData();
                formData.append('siswa_id', siswaId);
                formData.append('alasan', alasan);

                const response = await fetch('/api/submit-pembatalan.php', {
                    method: 'POST',
                    body: formData
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
                            location.reload();
                        });
                    } else {
                        showAlert(result.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
                console.error('Error submitting pembatalan:', error);
            }
        }
    </script>
</body>
</html>