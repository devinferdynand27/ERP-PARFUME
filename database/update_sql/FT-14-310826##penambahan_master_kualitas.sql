-- Master data: kualitas (mis. Premium, Standar, Ekonomis) — modul Master Data
-- tambahan, mengikuti pola satuan (006_create_satuan.sql): kolom audit
-- create_id/create_time/modify_id/modify_time (§6a CLAUDE.md), FK ke
-- admin.adid, nullable karena diisi via optional($request->user())->adid.
-- Sengaja TANPA FK ke produk — murni master data/dropdown, sama seperti
-- kualitas_bibit lama sebelum dihapus di 008_drop_ukuran_botol_kualitas_bibit.sql
-- (§10 CLAUDE.md), bukan retrofit tabel lama.

CREATE TABLE IF NOT EXISTS kualitas (
    kuid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kualitas VARCHAR(50) NOT NULL,
    keterangan VARCHAR(255) NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    create_id BIGINT UNSIGNED NULL,
    create_time TIMESTAMP NULL DEFAULT NULL,
    modify_id BIGINT UNSIGNED NULL,
    modify_time TIMESTAMP NULL DEFAULT NULL,

    KEY idx_kualitas_aktif (aktif),
    KEY idx_kualitas_create_id (create_id),
    KEY idx_kualitas_modify_id (modify_id),

    CONSTRAINT fk_kualitas_create_id FOREIGN KEY (create_id) REFERENCES admin (adid),
    CONSTRAINT fk_kualitas_modify_id FOREIGN KEY (modify_id) REFERENCES admin (adid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
