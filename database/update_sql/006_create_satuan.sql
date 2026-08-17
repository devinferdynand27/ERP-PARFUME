-- Master data: satuan (mis. pcs, box, liter, ml) — modul Master Data tambahan
-- Kolom audit pakai create_id/create_time/modify_id/modify_time (bukan pola
-- dibuat_pada/diperbarui_pada §6 CLAUDE.md) — diminta khusus untuk tabel ini,
-- dan dijadikan konvensi baru untuk kolom audit tabel berikutnya (lihat §6a).
-- create_id / modify_id nullable: auth admin belum dibangun saat tabel ini
-- dibuat (lihat CLAUDE.md §8), diisi begitu login sudah ada.

CREATE TABLE IF NOT EXISTS satuan (
    stid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_satuan VARCHAR(50) NOT NULL,
    tipe VARCHAR(50) NOT NULL,
    isi DECIMAL(15,2) NOT NULL DEFAULT 0,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    create_id BIGINT UNSIGNED NULL,
    create_time TIMESTAMP NULL DEFAULT NULL,
    modify_id BIGINT UNSIGNED NULL,
    modify_time TIMESTAMP NULL DEFAULT NULL,

    KEY idx_satuan_aktif (aktif),
    KEY idx_satuan_tipe (tipe),
    KEY idx_satuan_create_id (create_id),
    KEY idx_satuan_modify_id (modify_id),

    CONSTRAINT fk_satuan_create_id FOREIGN KEY (create_id) REFERENCES admin (adid),
    CONSTRAINT fk_satuan_modify_id FOREIGN KEY (modify_id) REFERENCES admin (adid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
