-- =====================================================
-- Database Schema: Verval Rekam Didik
-- MTsN 11 Majalengka
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS rekamdidik_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rekamdidik_db;

-- =====================================================
-- Table: siswa (Data Siswa Utama)
-- =====================================================
CREATE TABLE siswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nisn VARCHAR(20) UNIQUE NOT NULL,
    
    -- Data dari KK
    nik_kk VARCHAR(20),
    nama_kk VARCHAR(100),
    tempat_lahir_kk VARCHAR(100),
    tanggal_lahir_kk DATE,
    jenis_kelamin_kk ENUM('L', 'P'),
    nama_ibu_kk VARCHAR(100),
    nama_ayah_kk VARCHAR(100),
    
    -- Data dari Ijazah
    nama_ijazah VARCHAR(100),
    tempat_lahir_ijazah VARCHAR(100),
    tanggal_lahir_ijazah DATE,
    jenis_kelamin_ijazah ENUM('L', 'P'),
    nama_ayah_ijazah VARCHAR(100),
    
    -- Data Verval
    verval_status ENUM('belum', 'sudah') DEFAULT 'belum',
    
    -- Checkbox Status (apakah data sudah diceklis)
    nik_kk_verified BOOLEAN DEFAULT FALSE,
    nisn_verified BOOLEAN DEFAULT FALSE,
    nama_kk_verified BOOLEAN DEFAULT FALSE,
    nama_ijazah_verified BOOLEAN DEFAULT FALSE,
    tempat_lahir_kk_verified BOOLEAN DEFAULT FALSE,
    tempat_lahir_ijazah_verified BOOLEAN DEFAULT FALSE,
    tanggal_lahir_kk_verified BOOLEAN DEFAULT FALSE,
    tanggal_lahir_ijazah_verified BOOLEAN DEFAULT FALSE,
    jenis_kelamin_kk_verified BOOLEAN DEFAULT FALSE,
    jenis_kelamin_ijazah_verified BOOLEAN DEFAULT FALSE,
    nama_ibu_kk_verified BOOLEAN DEFAULT FALSE,
    nama_ayah_kk_verified BOOLEAN DEFAULT FALSE,
    nama_ayah_ijazah_verified BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nisn (nisn),
    INDEX idx_verval_status (verval_status)
);

-- =====================================================
-- Table: verval_jenjang_sebelumnya (Data Verval Jenjang Sebelumnya)
-- =====================================================
CREATE TABLE verval_jenjang_sebelumnya (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    
    -- Data Sekolah Sebelumnya
    nama_sd VARCHAR(100),
    tahun_ajaran_kelulusan VARCHAR(20),
    
    -- Data Ijazah
    nip_kepala_sekolah VARCHAR(30),
    nama_kepala_sekolah VARCHAR(100),
    nomor_seri_ijazah VARCHAR(50),
    tanggal_terbit_ijazah DATE,
    
    -- File Upload
    dokumen_ijazah VARCHAR(255),
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    UNIQUE KEY unique_siswa (siswa_id)
);

-- =====================================================
-- Table: history_perbaikan (Riwayat Perbaikan Data)
-- =====================================================
CREATE TABLE history_perbaikan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    
    -- Field yang diperbaiki
    field_name VARCHAR(100) NOT NULL,
    
    -- Nilai Sebelum dan Sesudah
    nilai_sebelum TEXT,
    nilai_sesudah TEXT,
    
    -- Timestamps
    tanggal_perbaikan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    INDEX idx_siswa_id (siswa_id),
    INDEX idx_tanggal (tanggal_perbaikan)
);

-- =====================================================
-- Table: admin_users (Admin Users)
-- =====================================================
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    nama_lengkap VARCHAR(100),
    role ENUM('admin', 'operator') DEFAULT 'operator',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username)
);

-- =====================================================
-- Table: pengajuan_pembatalan (Pengajuan Pembatalan Verval)
-- =====================================================
CREATE TABLE pengajuan_pembatalan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    
    -- Data Pengajuan
    alasan TEXT NOT NULL,
    status ENUM('menunggu', 'disetujui', 'ditolak') DEFAULT 'menunggu',
    
    -- Data Admin Response
    admin_id INT,
    catatan_admin TEXT,
    tanggal_diproses TIMESTAMP NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_siswa_id (siswa_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- =====================================================
-- Insert Default Admin User
-- =====================================================
INSERT INTO admin_users (username, password, email, nama_lengkap, role) VALUES 
('admin', MD5('admin123'), 'admin@mtsn11.sch.id', 'Administrator', 'admin');

-- =====================================================
-- Sample Data for Testing
-- =====================================================
-- Uncomment untuk test data
/*
INSERT INTO siswa (nisn, nik_kk, nama_kk, tempat_lahir_kk, tanggal_lahir_kk, jenis_kelamin_kk, nama_ibu_kk, nama_ayah_kk) VALUES
('0123456789', '3273050101010001', 'BUDI SANTOSO', 'MAJALENGKA', '2010-01-15', 'L', 'SITI NURHALIZA', 'RAHMAT SURYANTO');
*/
