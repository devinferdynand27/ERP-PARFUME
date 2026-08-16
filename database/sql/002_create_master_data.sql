-- Master data: aroma, ukuran_botol, kualitas_bibit, supplier (modul 5.3 PRD)
-- ukuran_botol & kualitas_bibit SENGAJA tanpa FK ke produk (lihat CLAUDE.md §10)

CREATE TABLE aroma (
    arid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_aroma VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    KEY idx_aroma_kategori (kategori),
    KEY idx_aroma_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ukuran_botol (
    ubid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ukuran VARCHAR(50) NOT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    KEY idx_ukuran_botol_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kualitas_bibit (
    kbid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kualitas VARCHAR(50) NOT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    KEY idx_kualitas_bibit_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE supplier (
    spid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(150) NOT NULL,
    kontak VARCHAR(100) NULL,
    alamat TEXT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    KEY idx_supplier_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
