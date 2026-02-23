<?php
/**
 * API: Import Data Siswa dari Excel (XLSX)
 * POST /api/admin/import-siswa.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/Database.php';

$response = ['success' => false, 'message' => '', 'details' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File Excel wajib diunggah');
    }

    $file = $_FILES['file'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'xlsx') {
        throw new Exception('Format file harus .xlsx');
    }

    if ($file['size'] > $max_size) {
        throw new Exception('Ukuran file maksimal 2MB');
    }

    if (!class_exists('ZipArchive')) {
        throw new Exception('ZipArchive tidak tersedia di server');
    }

    $zip = new ZipArchive();
    if ($zip->open($file['tmp_name']) !== true) {
        throw new Exception('Gagal membuka file Excel');
    }

    $shared_strings = [];
    $shared_path = 'xl/sharedStrings.xml';
    if ($zip->locateName($shared_path) !== false) {
        $shared_xml = $zip->getFromName($shared_path);
        if ($shared_xml !== false) {
            $shared = simplexml_load_string($shared_xml);
            if ($shared && isset($shared->si)) {
                foreach ($shared->si as $si) {
                    if (isset($si->t)) {
                        $shared_strings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $run) {
                            $text .= (string)$run->t;
                        }
                        $shared_strings[] = $text;
                    } else {
                        $shared_strings[] = '';
                    }
                }
            }
        }
    }

    $sheet_path = 'xl/worksheets/sheet1.xml';
    if ($zip->locateName($sheet_path) === false) {
        throw new Exception('Sheet1 tidak ditemukan di file Excel');
    }

    $sheet_xml = $zip->getFromName($sheet_path);
    $zip->close();

    if ($sheet_xml === false) {
        throw new Exception('Gagal membaca data sheet');
    }

    $sheet = simplexml_load_string($sheet_xml);
    if (!$sheet || !isset($sheet->sheetData->row)) {
        throw new Exception('Sheet tidak memiliki data');
    }

    $rows = [];

    foreach ($sheet->sheetData->row as $row) {
        $row_data = [];
        foreach ($row->c as $c) {
            $cell_ref = (string)$c['r'];
            $cell_type = (string)$c['t'];
            $value = '';

            if (isset($c->v)) {
                $value = (string)$c->v;
                if ($cell_type === 's') {
                    $idx = intval($value);
                    $value = $shared_strings[$idx] ?? '';
                }
            } elseif ($cell_type === 'inlineStr' && isset($c->is->t)) {
                $value = (string)$c->is->t;
            }

            $col_index = getColumnIndex($cell_ref);
            if ($col_index > 0) {
                $row_data[$col_index] = $value;
            }
        }

        if (!empty($row_data)) {
            $rows[] = $row_data;
        }
    }

    if (count($rows) < 2) {
        throw new Exception('File tidak memiliki data siswa');
    }

    $expected_headers = [
        'nisn',
        'nik_kk',
        'nama_kk',
        'tempat_lahir_kk',
        'tanggal_lahir_kk',
        'jenis_kelamin_kk',
        'nama_ibu_kk',
        'nama_ayah_kk',
        'nama_ijazah',
        'tempat_lahir_ijazah',
        'tanggal_lahir_ijazah',
        'jenis_kelamin_ijazah',
        'nama_ayah_ijazah',
        'verval_status'
    ];

    $header_row = $rows[0];
    $header_map = [];

    foreach ($header_row as $col_index => $header_value) {
        $header_key = strtolower(trim($header_value));
        if ($header_key !== '') {
            $header_map[$header_key] = $col_index;
        }
    }

    $missing_headers = [];
    foreach ($expected_headers as $h) {
        if (!isset($header_map[$h])) {
            $missing_headers[] = $h;
        }
    }

    if (!empty($missing_headers)) {
        $response['details'] = ['missing_headers' => $missing_headers];
        throw new Exception('Kolom header tidak lengkap');
    }

    $data_rows = [];
    $invalid_rows = [];
    $duplicate_in_file = [];
    $nisn_seen = [];

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        $record = [];

        foreach ($expected_headers as $h) {
            $col_index = $header_map[$h];
            $record[$h] = isset($row[$col_index]) ? trim((string)$row[$col_index]) : '';
        }

        $record['tanggal_lahir_kk'] = normalizeDate($record['tanggal_lahir_kk']);
        $record['tanggal_lahir_ijazah'] = normalizeDate($record['tanggal_lahir_ijazah']);

        $all_empty = true;
        foreach ($record as $val) {
            if ($val !== '') {
                $all_empty = false;
                break;
            }
        }
        if ($all_empty) {
            continue;
        }

        $nisn = $record['nisn'];
        if ($nisn === '' || !ctype_digit($nisn) || strlen($nisn) !== 10) {
            $invalid_rows[] = ['row' => $i + 1, 'reason' => 'NISN tidak valid'];
            continue;
        }

        if (isset($nisn_seen[$nisn])) {
            $duplicate_in_file[] = $nisn;
            continue;
        }
        $nisn_seen[$nisn] = true;

        $required_fields = [
            'nama_kk',
            'tempat_lahir_kk',
            'tanggal_lahir_kk',
            'jenis_kelamin_kk',
            'nama_ibu_kk',
            'nama_ayah_kk'
        ];
        $missing = [];
        foreach ($required_fields as $rf) {
            if ($record[$rf] === '') {
                $missing[] = $rf;
            }
        }
        if (!empty($missing)) {
            $invalid_rows[] = ['row' => $i + 1, 'reason' => 'Kolom wajib kosong: ' . implode(', ', $missing)];
            continue;
        }

        if (!in_array($record['jenis_kelamin_kk'], ['L', 'P'], true)) {
            $invalid_rows[] = ['row' => $i + 1, 'reason' => 'jenis_kelamin_kk harus L atau P'];
            continue;
        }

        if ($record['jenis_kelamin_ijazah'] !== '' && !in_array($record['jenis_kelamin_ijazah'], ['L', 'P'], true)) {
            $invalid_rows[] = ['row' => $i + 1, 'reason' => 'jenis_kelamin_ijazah harus L atau P'];
            continue;
        }

        if ($record['tanggal_lahir_kk'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $record['tanggal_lahir_kk'])) {
            $invalid_rows[] = ['row' => $i + 1, 'reason' => 'tanggal_lahir_kk harus YYYY-MM-DD'];
            continue;
        }

        if ($record['tanggal_lahir_ijazah'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $record['tanggal_lahir_ijazah'])) {
            $invalid_rows[] = ['row' => $i + 1, 'reason' => 'tanggal_lahir_ijazah harus YYYY-MM-DD'];
            continue;
        }

        if ($record['verval_status'] === '') {
            $record['verval_status'] = 'belum';
        }
        if (!in_array($record['verval_status'], ['belum', 'sudah'], true)) {
            $invalid_rows[] = ['row' => $i + 1, 'reason' => 'verval_status harus belum/sudah'];
            continue;
        }

        $data_rows[] = $record;
    }

    if (!empty($duplicate_in_file)) {
        $response['details'] = ['duplicate_in_file' => array_values(array_unique($duplicate_in_file))];
        throw new Exception('Terdapat NISN duplikat di file');
    }

    if (!empty($invalid_rows)) {
        $response['details'] = ['invalid_rows' => $invalid_rows];
        throw new Exception('Terdapat baris data yang tidak valid');
    }

    if (empty($data_rows)) {
        throw new Exception('Tidak ada data valid untuk diimpor');
    }

    $db = new Database();
    $conn = $db->connect();

    $nisn_list = array_column($data_rows, 'nisn');
    $placeholders = implode(',', array_fill(0, count($nisn_list), '?'));
    $types = str_repeat('s', count($nisn_list));

    $stmt = $conn->prepare("SELECT nisn FROM siswa WHERE nisn IN ($placeholders)");
    $stmt->bind_param($types, ...$nisn_list);
    $stmt->execute();
    $result = $stmt->get_result();

    $duplicates_db = [];
    while ($row = $result->fetch_assoc()) {
        $duplicates_db[] = $row['nisn'];
    }
    $stmt->close();

    if (!empty($duplicates_db)) {
        $response['details'] = ['duplicate_in_db' => $duplicates_db];
        $conn->close();
        throw new Exception('Terdapat NISN duplikat di database');
    }

    $conn->begin_transaction();

    $insert_stmt = $conn->prepare('INSERT INTO siswa (
        nisn, nik_kk, nama_kk, tempat_lahir_kk, tanggal_lahir_kk,
        jenis_kelamin_kk, nama_ibu_kk, nama_ayah_kk,
        nama_ijazah, tempat_lahir_ijazah, tanggal_lahir_ijazah,
        jenis_kelamin_ijazah, nama_ayah_ijazah, verval_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

    foreach ($data_rows as $r) {
        $nik_kk = $r['nik_kk'] !== '' ? $r['nik_kk'] : null;
        $nama_ijazah = $r['nama_ijazah'] !== '' ? $r['nama_ijazah'] : null;
        $tempat_lahir_ijazah = $r['tempat_lahir_ijazah'] !== '' ? $r['tempat_lahir_ijazah'] : null;
        $tanggal_lahir_ijazah = $r['tanggal_lahir_ijazah'] !== '' ? $r['tanggal_lahir_ijazah'] : null;
        $jenis_kelamin_ijazah = $r['jenis_kelamin_ijazah'] !== '' ? $r['jenis_kelamin_ijazah'] : null;
        $nama_ayah_ijazah = $r['nama_ayah_ijazah'] !== '' ? $r['nama_ayah_ijazah'] : null;

        $insert_stmt->bind_param(
            'ssssssssssssss',
            $r['nisn'], $nik_kk, $r['nama_kk'], $r['tempat_lahir_kk'], $r['tanggal_lahir_kk'],
            $r['jenis_kelamin_kk'], $r['nama_ibu_kk'], $r['nama_ayah_kk'],
            $nama_ijazah, $tempat_lahir_ijazah, $tanggal_lahir_ijazah,
            $jenis_kelamin_ijazah, $nama_ayah_ijazah, $r['verval_status']
        );

        if (!$insert_stmt->execute()) {
            $conn->rollback();
            $insert_stmt->close();
            $conn->close();
            throw new Exception('Gagal insert data: ' . $insert_stmt->error);
        }
    }

    $insert_stmt->close();
    $conn->commit();
    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Import berhasil';
    $response['details'] = ['inserted' => count($data_rows)];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

function getColumnIndex($cell_ref) {
    $letters = preg_replace('/[^A-Z]/', '', strtoupper($cell_ref));
    if ($letters === '') {
        return 0;
    }
    $col = 0;
    $len = strlen($letters);
    for ($i = 0; $i < $len; $i++) {
        $col = $col * 26 + (ord($letters[$i]) - 64);
    }
    return $col;
}

function normalizeDate($value) {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    // Excel serial date number
    if (is_numeric($value)) {
        $serial = (int)floor((float)$value);
        if ($serial > 0) {
            $base = strtotime('1899-12-30');
            $timestamp = $base + ($serial * 86400);
            return date('Y-m-d', $timestamp);
        }
    }

    // dd/mm/yyyy or dd-mm-yyyy
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $m)) {
        $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        $year = $m[3];
        return $year . '-' . $month . '-' . $day;
    }

    // yyyy/mm/dd or yyyy-mm-dd
    if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $value, $m)) {
        $year = $m[1];
        $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        $day = str_pad($m[3], 2, '0', STR_PAD_LEFT);
        return $year . '-' . $month . '-' . $day;
    }

    return $value;
}
?>
