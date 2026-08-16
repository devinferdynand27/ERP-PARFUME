-- Transaksi barang masuk / stock in (modul 5.4 PRD)
-- supplier SENGAJA tidak direlasikan ke barang_masuk (lihat CLAUDE.md §10)

CREATE TABLE barang_masuk (
    bmid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_transaksi VARCHAR(50) NOT NULL,
    tanggal DATE NOT NULL,
    total_item INT NOT NULL DEFAULT 0,
    total_qty INT NOT NULL DEFAULT 0,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_barang_masuk_nomor_transaksi (nomor_transaksi),
    KEY idx_barang_masuk_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE barang_masuk_detail (
    bmdid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bmid BIGINT UNSIGNED NOT NULL,
    prid BIGINT UNSIGNED NOT NULL,
    qty INT NOT NULL,
    harga_beli DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    KEY idx_barang_masuk_detail_bmid (bmid),
    KEY idx_barang_masuk_detail_prid (prid),

    CONSTRAINT fk_barang_masuk_detail_bmid FOREIGN KEY (bmid) REFERENCES barang_masuk (bmid) ON DELETE CASCADE,
    CONSTRAINT fk_barang_masuk_detail_prid FOREIGN KEY (prid) REFERENCES produk (prid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
