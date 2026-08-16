-- Tabel admin: autentikasi & manajemen profil (modul 5.1 PRD)
CREATE TABLE admin (
    adid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_admin VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL,
    token VARCHAR(100) NULL,
    token_kadaluarsa TIMESTAMP NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_admin_username (username),
    KEY idx_admin_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
