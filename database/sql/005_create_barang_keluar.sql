-- Transaksi barang keluar / stock out (modul 5.4 PRD)
-- Validasi qty <= produk.stok WAJIB dilakukan di server (repository), bukan
-- hanya di frontend. Lihat CLAUDE.md §10.

CREATE TABLE barang_keluar (
    bkid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_transaksi VARCHAR(50) NOT NULL,
    tanggal DATE NOT NULL,
    total_item INT NOT NULL DEFAULT 0,
    total_qty INT NOT NULL DEFAULT 0,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_barang_keluar_nomor_transaksi (nomor_transaksi),
    KEY idx_barang_keluar_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE barang_keluar_detail (
    bkdid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bkid BIGINT UNSIGNED NOT NULL,
    prid BIGINT UNSIGNED NOT NULL,
    qty INT NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    KEY idx_barang_keluar_detail_bkid (bkid),
    KEY idx_barang_keluar_detail_prid (prid),

    CONSTRAINT fk_barang_keluar_detail_bkid FOREIGN KEY (bkid) REFERENCES barang_keluar (bkid) ON DELETE CASCADE,
    CONSTRAINT fk_barang_keluar_detail_prid FOREIGN KEY (prid) REFERENCES produk (prid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
