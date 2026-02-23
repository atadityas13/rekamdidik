/**
 * Admin Review Verval - Enhanced Functions
 * Menangani berbagai tipe konfirmasi: need_document, need_confirmation, need_edit
 */

// Global variable untuk store data siswa yang sedang direview
let currentReviewData = null;

/**
 * Open modal review verval
 */
async function openReviewVerval(siswaId) {
    try {
        const response = await fetch(`../api/admin/get-verval-review.php?siswa_id=${siswaId}`);
        
        // Cek response status
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Ambil text dulu untuk debugging
        const text = await response.text();
        
        // Log response untuk debugging (hapus di production)
        console.log('API Response:', text);
        
        // Parse JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response text:', text);
            throw new Error('Invalid JSON response from server. Check console for details.');
        }

        if (!result.success) {
            showAlert(result.message || 'Terjadi kesalahan', 'error');
            return;
        }

        currentReviewData = result.data;
        buildReviewModal(result.data);
        openModal('reviewVervalModal');
    } catch (error) {
        showAlert('Terjadi kesalahan: ' + error.message, 'error');
        console.error('Error:', error);
    }
}

/**
 * Build review modal content
 */
function buildReviewModal(data) {
    const siswa = data.siswa;
    const konfirmasi = data.konfirmasi;
    const stats = data.stats;

    // Info Siswa
    document.getElementById('reviewSiswaInfo').innerHTML = `
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div><strong>NISN:</strong> ${siswa.nisn}</div>
            <div><strong>Nama:</strong> ${siswa.nama_kk || siswa.nama_ijazah || '-'}</div>
            <div><strong>Status Verval:</strong> <span class="status-badge status-sudah">Sudah Verval</span></div>
        </div>
    `;

    // Stats Overview
    document.getElementById('reviewStats').innerHTML = `
        <div style="background: white; padding: 15px; border-left: 4px solid #9e9e9e; border-radius: 4px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #9e9e9e;">${stats.pending}</div>
            <div style="font-size: 12px; color: #666;">⏳ Pending</div>
        </div>
        <div style="background: white; padding: 15px; border-left: 4px solid #4caf50; border-radius: 4px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #4caf50;">${stats.approved}</div>
            <div style="font-size: 12px; color: #666;">✓ Approved</div>
        </div>
        <div style="background: white; padding: 15px; border-left: 4px solid #ff9800; border-radius: 4px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #ff9800;">${stats.need_document + stats.need_confirmation + stats.need_edit}</div>
            <div style="font-size: 12px; color: #666;">⚠️ Need Action</div>
        </div>
        <div style="background: white; padding: 15px; border-left: 4px solid #2196f3; border-radius: 4px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #2196f3;">${stats.student_responded}</div>
            <div style="font-size: 12px; color: #666;">📤 Responded</div>
        </div>
    `;

    // Fields List
    buildFieldsList(siswa, konfirmasi);

    // Final Action
    checkFinalAction(stats);
}

/**
 * Build fields list with enhanced status
 */
function buildFieldsList(siswa, konfirmasi) {
    const container = document.getElementById('reviewFieldsList');
    let html = '';

    for (const [fieldName, fieldStatus] of Object.entries(konfirmasi)) {
        const fieldValue = getFieldValue(siswa, fieldName);
        const statusBadge = getStatusBadge(fieldStatus.status);
        const typeBadge = fieldStatus.tipe_konfirmasi ? getTypeBadge(fieldStatus.tipe_konfirmasi) : '';

        html += `
            <div style="border-bottom: 1px solid #eee; padding: 15px; background: ${getFieldBgColor(fieldStatus.status)};">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                    <div style="flex: 1;">
                        <strong style="font-size: 14px;">${getFieldLabel(fieldName)}</strong>
                        <div style="color: #666; margin-top: 5px;">${fieldValue || '-'}</div>
                        ${fieldStatus.catatan_admin ? `
                            <div style="background: #fff3e0; padding: 8px; border-radius: 4px; margin-top: 8px; font-size: 13px;">
                                <strong>💬 Catatan Admin:</strong> ${fieldStatus.catatan_admin}
                            </div>
                        ` : ''}
                        ${fieldStatus.pesan_siswa ? `
                            <div style="background: #e3f2fd; padding: 8px; border-radius: 4px; margin-top: 8px; font-size: 13px;">
                                <strong>💬 Respon Siswa:</strong> ${fieldStatus.pesan_siswa}
                            </div>
                        ` : ''}
                        ${fieldStatus.nilai_baru_siswa ? `
                            <div style="background: #f3e5f5; padding: 8px; border-radius: 4px; margin-top: 8px; font-size: 13px;">
                                <strong>📝 Data Baru dari Siswa:</strong> ${fieldStatus.nilai_baru_siswa}
                            </div>
                        ` : ''}
                        ${fieldStatus.berkas_pendukung ? `
                            <div style="margin-top: 8px;">
                                <a href="../uploads/berkas_pendukung/${fieldStatus.berkas_pendukung}" target="_blank" 
                                   style="color: #667eea; text-decoration: underline; font-size: 13px;">
                                    📎 Lihat Berkas Pendukung
                                </a>
                            </div>
                        ` : ''}
                    </div>
                    <div style="display: flex; gap: 5px; align-items: center;">
                        ${statusBadge}
                        ${typeBadge}
                    </div>
                </div>
                
                ${buildFieldActions(siswa.id, fieldName, fieldStatus)}
            </div>
        `;
    }

    container.innerHTML = html;
}

/**
 * Build action buttons for each field
 */
function buildFieldActions(siswaId, fieldName, fieldStatus) {
    const status = fieldStatus.status;

    // Jika sudah approved, tidak perlu tombol
    if (status === 'approved') {
        return '<div style="text-align: center; padding: 10px; color: #4caf50; font-weight: 600;">✓ Field ini sudah disetujui</div>';
    }

    return `
        <div style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
            <button onclick="konfirmasiField(${siswaId}, '${fieldName}', 'approve')" 
                    class="btn-small" 
                    style="background: #4caf50; color: white; flex: 1;">
                ✓ Setujui
            </button>
            <button onclick="konfirmasiField(${siswaId}, '${fieldName}', 'need_document')" 
                    class="btn-small" 
                    style="background: #ff9800; color: white; flex: 1;">
                📄 Minta Berkas
            </button>
            <button onclick="konfirmasiField(${siswaId}, '${fieldName}', 'need_confirmation')" 
                    class="btn-small" 
                    style="background: #2196f3; color: white; flex: 1;">
                💬 Minta Konfirmasi
            </button>
            <button onclick="konfirmasiField(${siswaId}, '${fieldName}', 'need_edit')" 
                    class="btn-small" 
                    style="background: #9c27b0; color: white; flex: 1;">
                ✏️ Minta Edit Data
            </button>
        </div>
    `;
}

/**
 * Konfirmasi field action
 */
async function konfirmasiField(siswaId, fieldName, action) {
    // Mapping action ke pesan
    const actionLabels = {
        'approve': 'Setujui field ini',
        'need_document': 'Minta berkas pendukung',
        'need_confirmation': 'Minta konfirmasi/penjelasan',
        'need_edit': 'Minta edit data + berkas'
    };

    const actionDescriptions = {
        'need_document': 'Siswa akan diminta upload berkas pendukung (misal: scan KK, Ijazah).',
        'need_confirmation': 'Siswa cukup memberikan penjelasan tanpa perlu upload berkas.',
        'need_edit': 'Siswa akan diminta edit data dan upload berkas pendukung.'
    };

    let catatan = null;

    if (action !== 'approve') {
        const { value: text } = await Swal.fire({
            title: actionLabels[action],
            html: `
                <div style="text-align: left; margin-bottom: 15px;">
                    <p style="color: #666; font-size: 14px;">${actionDescriptions[action]}</p>
                </div>
            `,
            input: 'textarea',
            inputLabel: 'Catatan untuk Siswa',
            inputPlaceholder: 'Jelaskan dengan detail apa yang perlu dikonfirmasi...',
            inputAttributes: {
                'aria-label': 'Catatan',
                'rows': 4
            },
            showCancelButton: true,
            confirmButtonText: 'Kirim',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Catatan wajib diisi!';
                }
                if (value.length < 10) {
                    return 'Catatan minimal 10 karakter!';
                }
            }
        });

        if (!text) return; // User cancelled
        catatan = text;
    } else {
        // Optional catatan for approve
        const { value: text } = await Swal.fire({
            title: 'Setujui Field',
            input: 'textarea',
            inputLabel: 'Catatan (Optional)',
            inputPlaceholder: 'Tambahkan catatan jika diperlukan...',
            showCancelButton: true,
            confirmButtonText: 'Setujui',
            cancelButtonText: 'Batal'
        });

        if (text === undefined) return; // User cancelled
        catatan = text;
    }

    try {
        const response = await fetch('../api/admin/konfirmasi-field.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                siswa_id: siswaId,
                field_name: fieldName,
                action: action,
                catatan: catatan
            })
        });

        const result = await response.json();

        if (result.success) {
            showAlert(result.message, 'success');
            // Refresh modal
            await openReviewVerval(siswaId);
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Terjadi kesalahan: ' + error.message, 'error');
        console.error('Error:', error);
    }
}

/**
 * Check if final approval is possible
 */
function checkFinalAction(stats) {
    const container = document.getElementById('finalActionArea');
    const allApproved = stats.pending === 0 && stats.need_document === 0 && 
                        stats.need_confirmation === 0 && stats.need_edit === 0 &&
                        stats.student_responded === 0;

    if (allApproved && stats.approved > 0) {
        container.innerHTML = `
            <div style="border: 2px solid #4caf50; border-radius: 8px; padding: 20px; background: #f1f8e9;">
                <h3 style="margin: 0 0 10px 0; color: #4caf50;">✅ Semua Field Sudah Disetujui!</h3>
                <p style="margin: 0 0 15px 0; color: #666;">Anda bisa memberikan persetujuan final untuk verval ini.</p>
                <button onclick="finalApproveVerval(${currentReviewData.siswa.id})" 
                        class="button button-success" 
                        style="padding: 12px 30px; font-size: 16px;">
                    ✓ Setujui Verval Sepenuhnya
                </button>
            </div>
        `;
    } else {
        const needReview = stats.student_responded;
        const needAction = stats.need_document + stats.need_confirmation + stats.need_edit;
        
        container.innerHTML = `
            <div style="padding: 15px; background: #f5f5f5; border-radius: 8px;">
                <p style="margin: 0; color: #666; text-align: center;">
                    ${needReview > 0 ? `<span style="color: #2196f3;">📤 ${needReview} field menunggu review Anda</span><br>` : ''}
                    ${needAction > 0 ? `<span style="color: #ff9800;">⚠️ ${needAction} field menunggu tindakan siswa</span><br>` : ''}
                    ${stats.pending > 0 ? `<span style="color: #9e9e9e;">⏳ ${stats.pending} field belum direview</span>` : ''}
                </p>
            </div>
        `;
    }
}

/**
 * Final approve verval
 */
async function finalApproveVerval(siswaId) {
    const { value: catatan } = await Swal.fire({
        title: 'Persetujuan Final Verval',
        input: 'textarea',
        inputLabel: 'Catatan Final (Optional)',
        inputPlaceholder: 'Tambahkan catatan final jika diperlukan...',
        showCancelButton: true,
        confirmButtonText: 'Setujui Sepenuhnya',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4caf50'
    });

    if (catatan === undefined) return; // User cancelled

    try {
        const response = await fetch('../api/admin/final-approve-verval.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                siswa_id: siswaId,
                catatan: catatan
            })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message,
                confirmButtonText: 'OK'
            }).then(() => {
                closeModal('reviewVervalModal');
                loadData(); // Refresh list
            });
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Terjadi kesalahan: ' + error.message, 'error');
        console.error('Error:', error);
    }
}

/**
 * Request re-upload ijazah
 */
async function requestReuploadIjazah(siswaId) {
    const { value: catatan } = await Swal.fire({
        title: 'Minta Re-upload Ijazah',
        html: '<p style="color:#666; font-size:14px;">Jelaskan kenapa ijazah perlu diupload ulang (misal: gambar blur, terpotong, dll)</p>',
        input: 'textarea',
        inputLabel: 'Alasan',
        inputPlaceholder: 'Contoh: Gambar ijazah terpotong dan tidak jelas. Mohon upload ulang dengan foto yang lebih baik.',
        inputAttributes: { 'rows': 4 },
        showCancelButton: true,
        confirmButtonText: 'Kirim Permintaan',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value || value.length < 10) {
                return 'Alasan minimal 10 karakter!';
            }
        }
    });

    if (!catatan) return;

    try {
        const response = await fetch('../api/admin/request-reupload-ijazah.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                siswa_id: siswaId,
                catatan: catatan
            })
        });

        const result = await response.json();

        if (result.success) {
            showAlert(result.message, 'success');
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Terjadi kesalahan: ' + error.message, 'error');
        console.error('Error:', error);
    }
}

// Helper functions
function getFieldValue(siswa, fieldName) {
    return siswa[fieldName] || '-';
}

function getFieldBgColor(status) {
    const colors = {
        'pending': '#fafafa',
        'approved': '#f1f8e9',
        'need_document': '#fff3e0',
        'need_confirmation': '#e3f2fd',
        'need_edit': '#f3e5f5',
        'student_responded': '#e1f5fe'
    };
    return colors[status] || '#fafafa';
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span style="background:#9e9e9e; color:white; padding:5px 10px; border-radius:4px; font-size:11px;">⏳ Pending</span>',
        'approved': '<span style="background:#4caf50; color:white; padding:5px 10px; border-radius:4px; font-size:11px;">✓ Approved</span>',
        'need_document': '<span style="background:#ff9800; color:white; padding:5px 10px; border-radius:4px; font-size:11px;">📄 Need Doc</span>',
        'need_confirmation': '<span style="background:#2196f3; color:white; padding:5px 10px; border-radius:4px; font-size:11px;">💬 Need Confirm</span>',
        'need_edit': '<span style="background:#9c27b0; color:white; padding:5px 10px; border-radius:4px; font-size:11px;">✏️ Need Edit</span>',
        'document_uploaded': '<span style="background:#00bcd4; color:white; padding:5px 10px; border-radius:4px; font-size:11px;">📤 Uploaded</span>',
        'student_responded': '<span style="background:#2196f3; color:white; padding:5px 10px; border-radius:4px; font-size:11px;">📤 Responded</span>'
    };
    return badges[status] || '';
}

function getTypeBadge(tipe) {
    const badges = {
        'need_document': '<span style="background:#ff5722; color:white; padding:5px 8px; border-radius:4px; font-size:10px;">📄 BERKAS</span>',
        'need_confirmation': '<span style="background:#03a9f4; color:white; padding:5px 8px; border-radius:4px; font-size:10px;">💬 KONFIRMASI</span>',
        'need_edit': '<span style="background:#9c27b0; color:white; padding:5px 8px; border-radius:4px; font-size:10px;">✏️ EDIT+BERKAS</span>'
    };
    return badges[tipe] || '';
}
