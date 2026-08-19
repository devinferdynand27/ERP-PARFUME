-- Modul Master Produk (CRUD) dihapus dari aplikasi (tabel produk kosong,
-- tidak ada rencana mengisi lewat UI produk lagi). Modul transaksi
-- procurement (Permintaan Barang, Pesanan Pembelian, Penerimaan Barang) yang
-- tadinya mereferensikan produk (kolom prid) sekarang direferensikan ke
-- aroma (kolom arid, master data yang sudah lengkap & terisi) sebagai
-- gantinya. Ketiga tabel detail berikut sudah diverifikasi 0 baris sebelum
-- perubahan ini dijalankan, jadi aman tanpa migrasi data.
--
-- Tabel produk & satuan TIDAK didrop di sini — di luar scope perubahan ini.

-- permintaan_barang_detail: prid -> arid
ALTER TABLE permintaan_barang_detail
    DROP FOREIGN KEY fk_permintaan_barang_detail_prid;

ALTER TABLE permintaan_barang_detail
    DROP INDEX idx_permintaan_barang_detail_prid;

ALTER TABLE permintaan_barang_detail
    CHANGE COLUMN prid arid BIGINT UNSIGNED NOT NULL;

ALTER TABLE permintaan_barang_detail
    ADD KEY idx_permintaan_barang_detail_arid (arid);

ALTER TABLE permintaan_barang_detail
    ADD CONSTRAINT fk_permintaan_barang_detail_arid FOREIGN KEY (arid) REFERENCES aroma (arid);

-- pesanan_pembelian_detail: prid -> arid
ALTER TABLE pesanan_pembelian_detail
    DROP FOREIGN KEY fk_pesanan_pembelian_detail_prid;

ALTER TABLE pesanan_pembelian_detail
    DROP INDEX idx_pesanan_pembelian_detail_prid;

ALTER TABLE pesanan_pembelian_detail
    CHANGE COLUMN prid arid BIGINT UNSIGNED NOT NULL;

ALTER TABLE pesanan_pembelian_detail
    ADD KEY idx_pesanan_pembelian_detail_arid (arid);

ALTER TABLE pesanan_pembelian_detail
    ADD CONSTRAINT fk_pesanan_pembelian_detail_arid FOREIGN KEY (arid) REFERENCES aroma (arid);

-- penerimaan_barang_detail: prid -> arid
ALTER TABLE penerimaan_barang_detail
    DROP FOREIGN KEY fk_penerimaan_barang_detail_prid;

ALTER TABLE penerimaan_barang_detail
    DROP INDEX idx_penerimaan_barang_detail_prid;

ALTER TABLE penerimaan_barang_detail
    CHANGE COLUMN prid arid BIGINT UNSIGNED NOT NULL;

ALTER TABLE penerimaan_barang_detail
    ADD KEY idx_penerimaan_barang_detail_arid (arid);

ALTER TABLE penerimaan_barang_detail
    ADD CONSTRAINT fk_penerimaan_barang_detail_arid FOREIGN KEY (arid) REFERENCES aroma (arid);
