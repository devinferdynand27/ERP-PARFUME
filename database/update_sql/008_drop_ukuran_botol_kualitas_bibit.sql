-- Hapus modul Master Ukuran Botol & Master Kualitas Bibit (diminta user,
-- 2026-08-17). Keduanya sengaja tanpa FK ke produk sejak awal (lihat
-- CLAUDE.md §10), jadi aman didrop tanpa dampak ke tabel lain — sudah
-- diverifikasi tidak ada FK yang mereferensikan kedua tabel ini.
-- ukuran_botol sebelumnya juga dipakai sebagai dropdown bantu (non-FK) di
-- form Produk untuk auto-compose nama_produk — field itu sudah dihapus dari
-- ProdukManager.vue bersamaan dengan perubahan ini.

DROP TABLE IF EXISTS ukuran_botol;
DROP TABLE IF EXISTS kualitas_bibit;
