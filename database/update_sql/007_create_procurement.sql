-- Modul Procurement: Permintaan Barang (PR) -> Pesanan Pembelian (PO) ->
-- Penerimaan Barang (GRN). Terinspirasi skema ERP generic (PDF referensi
-- user) tapi diadaptasi penuh ke konvensi CAVA Parfums (CLAUDE.md §1-§9):
-- nama tabel Indonesia, PK berinisial, reuse produk & satuan existing
-- (tidak bikin master_varian/master_satuan baru), kolom audit pola §6a.
--
-- Alur: permintaan_barang -> pesanan_pembelian (opsional dari PR, wajib
-- pilih supplier) -> penerimaan_barang (menerima fisik barang dari PO,
-- langsung menambah produk.stok, PARALEL dari barang_masuk existing --
-- tidak menyentuh tabel barang_masuk).

CREATE TABLE IF NOT EXISTS permintaan_barang (
    pbid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_permintaan VARCHAR(50) NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('draft','diajukan','disetujui','ditolak','ditutup') NOT NULL DEFAULT 'draft',
    catatan TEXT NULL,
    create_id BIGINT UNSIGNED NULL,
    create_time TIMESTAMP NULL DEFAULT NULL,
    modify_id BIGINT UNSIGNED NULL,
    modify_time TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_permintaan_barang_nomor (nomor_permintaan),
    KEY idx_permintaan_barang_tanggal (tanggal),
    KEY idx_permintaan_barang_status (status),
    KEY idx_permintaan_barang_create_id (create_id),
    KEY idx_permintaan_barang_modify_id (modify_id),

    CONSTRAINT fk_permintaan_barang_create_id FOREIGN KEY (create_id) REFERENCES admin (adid),
    CONSTRAINT fk_permintaan_barang_modify_id FOREIGN KEY (modify_id) REFERENCES admin (adid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permintaan_barang_detail (
    pbdid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pbid BIGINT UNSIGNED NOT NULL,
    prid BIGINT UNSIGNED NOT NULL,
    stid BIGINT UNSIGNED NOT NULL,
    qty_diminta DECIMAL(12,2) NOT NULL,

    KEY idx_permintaan_barang_detail_pbid (pbid),
    KEY idx_permintaan_barang_detail_prid (prid),
    KEY idx_permintaan_barang_detail_stid (stid),

    CONSTRAINT fk_permintaan_barang_detail_pbid FOREIGN KEY (pbid) REFERENCES permintaan_barang (pbid) ON DELETE CASCADE,
    CONSTRAINT fk_permintaan_barang_detail_prid FOREIGN KEY (prid) REFERENCES produk (prid),
    CONSTRAINT fk_permintaan_barang_detail_stid FOREIGN KEY (stid) REFERENCES satuan (stid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pesanan_pembelian (
    ppid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_po VARCHAR(50) NOT NULL,
    pbid BIGINT UNSIGNED NULL,
    spid BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('draft','diterbitkan','diterima_sebagian','diterima_penuh','dibatalkan') NOT NULL DEFAULT 'draft',
    total_item INT NOT NULL DEFAULT 0,
    total_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    catatan TEXT NULL,
    create_id BIGINT UNSIGNED NULL,
    create_time TIMESTAMP NULL DEFAULT NULL,
    modify_id BIGINT UNSIGNED NULL,
    modify_time TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_pesanan_pembelian_nomor (nomor_po),
    KEY idx_pesanan_pembelian_pbid (pbid),
    KEY idx_pesanan_pembelian_spid (spid),
    KEY idx_pesanan_pembelian_tanggal (tanggal),
    KEY idx_pesanan_pembelian_status (status),
    KEY idx_pesanan_pembelian_create_id (create_id),
    KEY idx_pesanan_pembelian_modify_id (modify_id),

    CONSTRAINT fk_pesanan_pembelian_pbid FOREIGN KEY (pbid) REFERENCES permintaan_barang (pbid),
    CONSTRAINT fk_pesanan_pembelian_spid FOREIGN KEY (spid) REFERENCES supplier (spid),
    CONSTRAINT fk_pesanan_pembelian_create_id FOREIGN KEY (create_id) REFERENCES admin (adid),
    CONSTRAINT fk_pesanan_pembelian_modify_id FOREIGN KEY (modify_id) REFERENCES admin (adid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pesanan_pembelian_detail (
    ppdid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ppid BIGINT UNSIGNED NOT NULL,
    pbdid BIGINT UNSIGNED NULL,
    prid BIGINT UNSIGNED NOT NULL,
    stid BIGINT UNSIGNED NOT NULL,
    qty_dipesan DECIMAL(12,2) NOT NULL,
    qty_diterima DECIMAL(12,2) NOT NULL DEFAULT 0,
    harga_satuan DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,

    KEY idx_pesanan_pembelian_detail_ppid (ppid),
    KEY idx_pesanan_pembelian_detail_pbdid (pbdid),
    KEY idx_pesanan_pembelian_detail_prid (prid),
    KEY idx_pesanan_pembelian_detail_stid (stid),

    CONSTRAINT fk_pesanan_pembelian_detail_ppid FOREIGN KEY (ppid) REFERENCES pesanan_pembelian (ppid) ON DELETE CASCADE,
    CONSTRAINT fk_pesanan_pembelian_detail_pbdid FOREIGN KEY (pbdid) REFERENCES permintaan_barang_detail (pbdid),
    CONSTRAINT fk_pesanan_pembelian_detail_prid FOREIGN KEY (prid) REFERENCES produk (prid),
    CONSTRAINT fk_pesanan_pembelian_detail_stid FOREIGN KEY (stid) REFERENCES satuan (stid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS penerimaan_barang (
    pnid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_penerimaan VARCHAR(50) NOT NULL,
    ppid BIGINT UNSIGNED NOT NULL,
    nomor_faktur_supplier VARCHAR(100) NULL,
    tanggal DATETIME NOT NULL,
    catatan TEXT NULL,
    create_id BIGINT UNSIGNED NULL,
    create_time TIMESTAMP NULL DEFAULT NULL,
    modify_id BIGINT UNSIGNED NULL,
    modify_time TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_penerimaan_barang_nomor (nomor_penerimaan),
    KEY idx_penerimaan_barang_ppid (ppid),
    KEY idx_penerimaan_barang_tanggal (tanggal),
    KEY idx_penerimaan_barang_create_id (create_id),
    KEY idx_penerimaan_barang_modify_id (modify_id),

    CONSTRAINT fk_penerimaan_barang_ppid FOREIGN KEY (ppid) REFERENCES pesanan_pembelian (ppid),
    CONSTRAINT fk_penerimaan_barang_create_id FOREIGN KEY (create_id) REFERENCES admin (adid),
    CONSTRAINT fk_penerimaan_barang_modify_id FOREIGN KEY (modify_id) REFERENCES admin (adid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS penerimaan_barang_detail (
    pndid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pnid BIGINT UNSIGNED NOT NULL,
    ppdid BIGINT UNSIGNED NOT NULL,
    prid BIGINT UNSIGNED NOT NULL,
    stid BIGINT UNSIGNED NOT NULL,
    qty_diterima DECIMAL(12,2) NOT NULL,
    harga_beli DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,

    KEY idx_penerimaan_barang_detail_pnid (pnid),
    KEY idx_penerimaan_barang_detail_ppdid (ppdid),
    KEY idx_penerimaan_barang_detail_prid (prid),
    KEY idx_penerimaan_barang_detail_stid (stid),

    CONSTRAINT fk_penerimaan_barang_detail_pnid FOREIGN KEY (pnid) REFERENCES penerimaan_barang (pnid) ON DELETE CASCADE,
    CONSTRAINT fk_penerimaan_barang_detail_ppdid FOREIGN KEY (ppdid) REFERENCES pesanan_pembelian_detail (ppdid),
    CONSTRAINT fk_penerimaan_barang_detail_prid FOREIGN KEY (prid) REFERENCES produk (prid),
    CONSTRAINT fk_penerimaan_barang_detail_stid FOREIGN KEY (stid) REFERENCES satuan (stid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
