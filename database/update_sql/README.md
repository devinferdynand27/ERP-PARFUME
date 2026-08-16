# update_sql

Perubahan skema **setelah** setup awal (`database/sql/001`–`005`) masuk ke sini
— bukan ke `database/migrations/` (project ini tidak memakai Laravel migration,
lihat `CLAUDE.md`).

Satu file per perubahan, penomoran urut lanjutan dari `database/sql/`, format
`00N_deskripsi_singkat.sql`:

```
update_sql/006_add_kategori_to_produk.sql
update_sql/007_create_laporan_view.sql
```

Jalankan manual dan urut:

```
mariadb -u root cava_parfums < database/update_sql/006_xxx.sql
```

Setiap file wajib idempotent-aware (pakai `IF NOT EXISTS` / cek kolom dulu kalau
memungkinkan) supaya aman dijalankan ulang di environment lain. Update juga
tabel PK di `CLAUDE.md` §7/§10 kalau menambah tabel baru dengan inisial baru.
