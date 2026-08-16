-- Tabel produk (modul 5.3 PRD)
-- Nama produk disusun otomatis di aplikasi: "{Ukuran Botol} - {Nama Aroma}"
-- ukuran_botol & kualitas_bibit TIDAK diikat FK (sengaja, lihat CLAUDE.md §10)

CREATE TABLE produk (
    prid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(50) NOT NULL,
    nama_produk VARCHAR(200) NOT NULL,
    arid BIGINT UNSIGNED NOT NULL,
    harga_beli_default DECIMAL(15,2) NOT NULL DEFAULT 0,
    harga_jual_default DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    stok_minimum INT NOT NULL DEFAULT 0,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_produk_kode_produk (kode_produk),
    KEY idx_produk_arid (arid),
    KEY idx_produk_aktif (aktif),
    KEY idx_produk_stok_stok_minimum (stok, stok_minimum),

    CONSTRAINT fk_produk_arid FOREIGN KEY (arid) REFERENCES aroma (arid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
