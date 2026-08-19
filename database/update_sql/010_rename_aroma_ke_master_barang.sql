-- Rename modul Aroma -> Master Barang (PF-10). Tabel `aroma` -> `master_barang`,
-- PK `arid` -> `mbid` (ikut konvensi §7 CLAUDE.md: PK = inisial nama tabel),
-- kolom `nama_aroma` -> `nama_barang`. Kolom FK `arid` di produk dan ketiga
-- tabel detail procurement (permintaan_barang_detail, pesanan_pembelian_detail,
-- penerimaan_barang_detail) ikut di-rename jadi `mbid` supaya konsisten dengan
-- nama PK yang direferensikan (§7). Data existing di semua tabel ini masih
-- sedikit/kosong (aroma: 2 baris, produk & ketiga tabel detail: 0 baris),
-- jadi aman tanpa migrasi data tambahan.

RENAME TABLE aroma TO master_barang;

ALTER TABLE master_barang
    CHANGE COLUMN arid mbid BIGINT UNSIGNED AUTO_INCREMENT,
    CHANGE COLUMN nama_aroma nama_barang VARCHAR(100) NOT NULL;

ALTER TABLE master_barang
    DROP INDEX idx_aroma_kategori,
    ADD KEY idx_master_barang_kategori (kategori);

ALTER TABLE master_barang
    DROP INDEX idx_aroma_aktif,
    ADD KEY idx_master_barang_aktif (aktif);

-- produk.arid -> produk.mbid
ALTER TABLE produk
    DROP FOREIGN KEY fk_produk_arid;

ALTER TABLE produk
    DROP INDEX idx_produk_arid;

ALTER TABLE produk
    CHANGE COLUMN arid mbid BIGINT UNSIGNED NOT NULL;

ALTER TABLE produk
    ADD KEY idx_produk_mbid (mbid);

ALTER TABLE produk
    ADD CONSTRAINT fk_produk_mbid FOREIGN KEY (mbid) REFERENCES master_barang (mbid);

-- permintaan_barang_detail.arid -> mbid
ALTER TABLE permintaan_barang_detail
    DROP FOREIGN KEY fk_permintaan_barang_detail_arid;

ALTER TABLE permintaan_barang_detail
    DROP INDEX idx_permintaan_barang_detail_arid;

ALTER TABLE permintaan_barang_detail
    CHANGE COLUMN arid mbid BIGINT UNSIGNED NOT NULL;

ALTER TABLE permintaan_barang_detail
    ADD KEY idx_permintaan_barang_detail_mbid (mbid);

ALTER TABLE permintaan_barang_detail
    ADD CONSTRAINT fk_permintaan_barang_detail_mbid FOREIGN KEY (mbid) REFERENCES master_barang (mbid);

-- pesanan_pembelian_detail.arid -> mbid
ALTER TABLE pesanan_pembelian_detail
    DROP FOREIGN KEY fk_pesanan_pembelian_detail_arid;

ALTER TABLE pesanan_pembelian_detail
    DROP INDEX idx_pesanan_pembelian_detail_arid;

ALTER TABLE pesanan_pembelian_detail
    CHANGE COLUMN arid mbid BIGINT UNSIGNED NOT NULL;

ALTER TABLE pesanan_pembelian_detail
    ADD KEY idx_pesanan_pembelian_detail_mbid (mbid);

ALTER TABLE pesanan_pembelian_detail
    ADD CONSTRAINT fk_pesanan_pembelian_detail_mbid FOREIGN KEY (mbid) REFERENCES master_barang (mbid);

-- penerimaan_barang_detail.arid -> mbid
ALTER TABLE penerimaan_barang_detail
    DROP FOREIGN KEY fk_penerimaan_barang_detail_arid;

ALTER TABLE penerimaan_barang_detail
    DROP INDEX idx_penerimaan_barang_detail_arid;

ALTER TABLE penerimaan_barang_detail
    CHANGE COLUMN arid mbid BIGINT UNSIGNED NOT NULL;

ALTER TABLE penerimaan_barang_detail
    ADD KEY idx_penerimaan_barang_detail_mbid (mbid);

ALTER TABLE penerimaan_barang_detail
    ADD CONSTRAINT fk_penerimaan_barang_detail_mbid FOREIGN KEY (mbid) REFERENCES master_barang (mbid);
