# CAVA Parfums — Konvensi Project

Laravel (backend) + Vue 3 (frontend, sprinkle di atas Blade, bukan SPA/Inertia).
Database: MySQL. UI komponen: **shadcn-vue** (shadcn-vue.com) + Tailwind CSS.

Project ini **berdiri sendiri**, tidak terkait/dibandingkan dengan project ERP lain
manapun. Sistem manajemen stok parfum mewah — lihat §10 untuk spesifikasi bisnis
lengkap (sumber: PRD `erp.md`, CAVA Parfums v1.0.0, 16 Agustus 2026).

Catatan: PRD asli membayangkan stack Spring Boot + SPA penuh (Vue Router, token di
localStorage). Itu **tidak dipakai** — stack final project ini adalah Laravel +
Blade + Vue sprinkle sesuai §1–§9 di bawah. Skema data, modul fungsional, dan
business rules dari PRD tetap jadi acuan.

## 1. Raw SQL, bukan Eloquent Model, bukan query builder berantai

Semua akses data memakai **raw SQL string** lewat `DB::select` / `DB::insert` /
`DB::update` / `DB::delete` / `DB::statement`, dengan parameter binding.

```php
// BENAR
DB::select('SELECT adid, nama_admin FROM admin WHERE aktif = ?', [1]);

// SALAH — Eloquent
Admin::where('aktif', 1)->get();

// SALAH — query builder berantai
DB::table('admin')->where('aktif', 1)->get();
```

Pengecualian yang tidak bisa dihindari: autentikasi Laravel mewajibkan objek
`Authenticatable`. Kalau perlu, buat satu class entity User tipis dengan custom
`UserProvider`, tapi query di dalamnya **tetap raw SQL** di repository.

## 2. Wajib anti SQL injection

Setiap nilai dari input **wajib** lewat parameter binding. Dilarang menyambung
string ke dalam SQL.

```php
// BENAR
DB::select('SELECT * FROM produk WHERE kode_produk = ?', [$kode]);
DB::select('SELECT * FROM produk WHERE kode_produk = :kode', ['kode' => $kode]);

// SALAH — celah SQL injection
DB::select("SELECT * FROM produk WHERE kode_produk = '$kode'");
```

Nama tabel/kolom tidak bisa di-bind. Kalau harus dinamis (misal kolom sorting),
validasi dengan whitelist eksplisit, jangan pernah ambil langsung dari request.

```php
$allowed = ['prid', 'nama_produk', 'dibuat_pada'];
$sort = in_array($request->sort, $allowed, true) ? $request->sort : 'prid';
```

## 3. Dilarang query ganda (N+1)

Jangan menjalankan query di dalam loop. Ambil sekaligus dengan `JOIN` atau
`WHERE ... IN (...)`.

```php
// SALAH — N+1
foreach ($produkList as $p) {
    $aroma = DB::select('SELECT * FROM aroma WHERE arid = ?', [$p->arid]);
}

// BENAR — satu query
DB::select('
    SELECT p.prid, p.nama_produk, a.nama_aroma
    FROM produk p
    LEFT JOIN aroma a ON a.arid = p.arid
    WHERE p.aktif = ?
', [1]);
```

Jangan memanggil query yang sama dua kali dalam satu request — simpan hasilnya ke
variabel.

## 4. Query dipisah dari controller — wajib di `app/Repositories/`

Controller **tidak boleh** memuat SQL. Semua query tinggal di `app/Repositories/`,
satu class per entitas/tabel utama.

```
app/Repositories/ProdukRepository.php
app/Repositories/AromaRepository.php
```

Controller hanya memanggil repository:

```php
public function index(ProdukRepository $repo)
{
    return view('produk.index', [
        'produkList' => $repo->getAktif(),
    ]);
}
```

## 4a. Skema database: raw SQL file, BUKAN Laravel migration

Project ini **tidak memakai** `database/migrations/` / `php artisan migrate`.
Skema database ditulis sebagai file `.sql` mentah, dijalankan manual:

- **`database/sql/`** — skema awal (setup pertama), file bernomor urut:
  `001_create_admin.sql`, `002_create_master_data.sql`, dst. Sudah dijalankan ke
  database `cava_parfums`.
- **`database/update_sql/`** — perubahan skema setelahnya (tambah tabel/kolom
  baru), lanjutan penomoran dari `database/sql/`. Lihat
  `database/update_sql/README.md`.

Jalankan dengan:

```
/Applications/XAMPP/xamppfiles/bin/mysql -u root cava_parfums < database/sql/00X_xxx.sql
```

Konsekuensi: **jangan pernah** menyarankan `php artisan make:migration` atau
`Schema::create()` Blueprint untuk project ini. `SESSION_DRIVER`, `CACHE_STORE`,
`QUEUE_CONNECTION` sengaja diset `file`/`file`/`sync` (bukan `database`) di
`.env` supaya Laravel tidak butuh tabel `sessions`/`cache`/`jobs` yang biasanya
datang dari migration bawaan.

## 5. Optimalisasi query & indexing wajib

- Setiap kolom yang dipakai di `WHERE`, `JOIN ... ON`, `ORDER BY`, atau `GROUP BY`
  **wajib punya index** — buat lewat migration (`$table->index(...)`) atau DDL SQL
  eksplisit, jangan andalkan default.
- Foreign key **wajib** punya index (MySQL tidak otomatis index FK di raw
  `ALTER TABLE` seperti Eloquent migration kadang lakukan — cek eksplisit).
- Kombinasi kolom yang sering di-filter bersamaan → composite index, urutan kolom
  sesuai selektivitas & pola query (kolom equality dulu, baru range).
- `SELECT` hanya kolom yang dipakai — hindari `SELECT *` di query yang hasilnya
  besar atau sering dipanggil.
- Query list/report yang berat wajib pakai `EXPLAIN` untuk verifikasi index
  terpakai sebelum dianggap selesai.
- Pagination wajib untuk query yang berpotensi mengembalikan banyak baris (pakai
  `LIMIT`/`OFFSET` atau keyset pagination untuk data besar).

## 6. Penamaan tabel & kolom: Bahasa Indonesia

Nama tabel **dan** kolom Bahasa Indonesia, huruf kecil, snake_case, tabel
tunggal (bukan jamak — ikut istilah bisnis di PRD): `produk`, `aroma`,
`barang_masuk`, `ukuran_botol`.

Nama kolom juga Bahasa Indonesia: `nama_produk`, `harga_beli`, `stok_minimum`,
`dibuat_pada`, `diperbarui_pada`, `aktif`.

Pengecualian: kolom timestamp bawaan Laravel (`created_at`, `updated_at`,
`deleted_at`) — kalau memakai fitur Laravel yang mengasumsikan nama ini
(`softDeletes()`, dsb), boleh tetap Inggris supaya tidak menulis ulang mekanisme
bawaan. Kalau ditulis manual lewat migration/raw SQL sendiri, pakai
`dibuat_pada` / `diperbarui_pada`.

Teks yang dilihat user (label form, pesan error, judul halaman) tetap Bahasa
Indonesia lewat `lang/id/`.

### 6a. Kolom audit (siapa/kapan create & modify)

Untuk tabel baru mulai `satuan` (2026-08-17) dan seterusnya, kolom audit
create/modify pakai pola Inggris `create_id` / `create_time` / `modify_id` /
`modify_time` — **bukan** `dibuat_oleh_*` / `dibuat_pada` / `diperbarui_oleh_*`
/ `diperbarui_pada` (§6 di atas), dan **bukan** `created_at`/`updated_at`
bawaan Laravel. Ini pengecualian eksplisit dari user, khusus untuk 4 kolom
audit ini — nama tabel & kolom bisnis lain tetap Bahasa Indonesia seperti biasa.

```sql
create_id BIGINT UNSIGNED NULL,
create_time TIMESTAMP NULL DEFAULT NULL,
modify_id BIGINT UNSIGNED NULL,
modify_time TIMESTAMP NULL DEFAULT NULL,

KEY idx_xxx_create_id (create_id),
KEY idx_xxx_modify_id (modify_id),
CONSTRAINT fk_xxx_create_id FOREIGN KEY (create_id) REFERENCES admin (adid),
CONSTRAINT fk_xxx_modify_id FOREIGN KEY (modify_id) REFERENCES admin (adid)
```

`create_id`/`modify_id` FK ke `admin.adid`, nullable — auth admin belum
dibangun, diisi begitu login sudah ada (lihat `optional($request->user())->adid`
di controller). Tabel yang dibuat sebelum `satuan` (aroma, ukuran_botol,
kualitas_bibit, supplier, produk, barang_masuk, barang_keluar) **tidak** perlu
di-retrofit ke pola ini kecuali diminta eksplisit — tetap pakai
`created_at`/`updated_at` seperti sudah berjalan.

## 7. Inisial pada primary key

Primary key memakai inisial dari nama tabel Bahasa Indonesia, bukan `id`:

| Tabel | Primary key |
|---|---|
| `produk` | `prid` |
| `master_barang` | `mbid` |

Foreign key memakai nama primary key yang dirujuk. Kalau satu tabel punya dua
relasi ke tabel yang sama, beri prefix peran: `mbid_utama`, `dibuat_oleh_adid`.

Sebelum menambah tabel baru, cek inisial PK belum dipakai tabel lain:

```sql
SELECT DISTINCT COLUMN_NAME FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_KEY = 'PRI';
```

## 8. Frontend: Blade + Vue components (sprinkle) + shadcn-vue

Bukan SPA, bukan Inertia. Laravel tetap render Blade seperti biasa; Vue dipasang
hanya di bagian yang butuh interaktivitas (dashboard, form transaksi, tabel dengan
filter/pagination), dipasang lewat `@vite` dan mount ke elemen tertentu.

- Komponen Vue di `resources/js/components/`, satu file per komponen (`.vue`).
- Mount point eksplisit per halaman Blade, bukan mount global ke seluruh app.
- Komunikasi Blade → Vue lewat props (data-attribute atau initial state di
  `<script>` JSON), Vue → Laravel lewat fetch/axios ke endpoint JSON biasa
  (bukan REST API penuh, cukup endpoint yang dibutuhkan komponen tsb).
- Endpoint yang dipanggil dari Vue tetap lewat Controller → Repository, ikuti
  aturan §1–§4 di atas — tidak ada jalur pintas raw SQL di luar repository.
- **UI kit: shadcn-vue** (bukan React shadcn/ui) + Tailwind CSS. Komponen di-copy
  ke `resources/js/components/ui/` via CLI shadcn-vue (bukan diinstal sebagai npm
  package biasa) — boleh diedit langsung sesuai kebutuhan, bukan black-box
  dependency.
- Autentikasi Blade-based (session Laravel biasa), **bukan** token di
  `localStorage` seperti dibayangkan PRD asli — konsekuensi dari keputusan §8
  (bukan SPA). Proteksi halaman lewat middleware `auth` Laravel standar.

## 9. Bahasa

- Nama tabel & kolom database: **Bahasa Indonesia** (§6–§7).
- Kode PHP/JS (nama class, method, variabel, file): **Bahasa Inggris** — mis.
  `ProdukRepository`, `getAktif()`, `$produkList`. Nama class repository/model
  tetap Inggris meski nama tabelnya Indonesia (`ProdukRepository` untuk tabel
  `produk`), supaya konsisten dengan konvensi framework Laravel.
- Teks untuk user, label form, pesan error, komentar, pesan commit: **Bahasa
  Indonesia**.

## 10. Spesifikasi bisnis: CAVA Parfums — Stock Management

Sumber: PRD `erp.md` v1.0.0 (16 Agustus 2026). Sistem manajemen stok internal
untuk bisnis parfum mewah — satu aroma bisa dikemas dalam berbagai ukuran botol
dan kualitas bibit berbeda.

**5 modul fungsional:**

1. **Autentikasi & Akun** — login admin (session Laravel, bukan token
   localStorage — lihat §8), manajemen profil.
2. **Dashboard** — KPI (total produk aktif, total stok, transaksi masuk/keluar
   bulan berjalan, jumlah supplier, jumlah produk stok kritis), grafik volume
   stok (8 produk teratas / per kategori), tabel low-stock alert, activity feed.
3. **Master Data** — Aroma, Ukuran Botol, Kualitas Bibit, Supplier, Produk.
4. **Transaksi Inventaris** — Barang Masuk (stock in, nomor transaksi otomatis,
   multi-item, stok bertambah), Barang Keluar (stock out, validasi stok cukup
   sebelum simpan, stok berkurang). Wajib `DB::transaction()` — kegagalan satu
   baris detail membatalkan seluruh transaksi (rollback).
5. **Pelaporan** — Laporan Barang Masuk/Keluar (filter rentang tanggal), Laporan
   Stok (filter stok kritis), Laporan Keuntungan (modal, omzet, gross profit,
   margin % — filter rentang tanggal).

**Keputusan desain kunci (beda dari PRD asli, sudah dikonfirmasi user):**

- **Nama tabel & kolom Bahasa Indonesia** + **PK berinisial**, bukan `id` bigint
  — mengikuti §6–§7 di atas, bukan skema PRD asli (yang pakai nama tabel
  Indonesia tapi PK `id` semua tabel).
- **`ukuran_botol` dan `kualitas_bibit` DIHAPUS (2026-08-17)** — awalnya
  sengaja dibuat TANPA foreign key ke `produk` (murni master data/dropdown di
  frontend untuk menyusun nama produk otomatis, `{Ukuran} - {Nama Aroma}`),
  tapi kedua modul (tabel, repository, controller, route, view, komponen
  Vue, menu sidebar) dihapus total atas permintaan eksplisit user. Field
  dropdown "Ukuran Botol" di form Produk juga sudah dihapus dari
  `ProdukManager.vue` — `nama_produk` sekarang murni diketik manual, tidak
  ada auto-compose lagi. Lihat `database/update_sql/008_drop_ukuran_botol_
  kualitas_bibit.sql` (DROP TABLE, sudah dijalankan). Jangan buat ulang
  modul ini kecuali user minta lagi.
- **`supplier` TIDAK direlasikan ke `barang_masuk`** — sesuai PRD asli, tidak
  ada kolom `spid` di `barang_masuk`. Supplier murni master data kontak, tidak
  dicatat per transaksi.
- **Frontend bukan SPA** — beda dari PRD asli, lihat §8.

**Penamaan tabel (Indonesia, sesuai PRD) & PK berinisial (final):**

| Tabel | PK |
|---|---|
| `admin` | `adid` |
| `master_barang` | `mbid` |
| `produk` | `prid` |
| `barang_masuk` | `bmid` |
| `barang_masuk_detail` | `bmdid` |
| `barang_keluar` | `bkid` |
| `barang_keluar_detail` | `bkdid` |
| `supplier` | `spid` |
| `satuan` | `stid` |
| `permintaan_barang` | `pbid` |
| `permintaan_barang_detail` | `pbdid` |
| `pesanan_pembelian` | `ppid` |
| `pesanan_pembelian_detail` | `ppdid` |
| `penerimaan_barang` | `pnid` |
| `penerimaan_barang_detail` | `pndid` |

FK mengikuti nama PK yang dirujuk: `produk.mbid` → `master_barang.mbid`,
`barang_masuk_detail.bmid` → `barang_masuk.bmid`, `barang_masuk_detail.prid` →
`produk.prid`, dst. Semua kolom FK **wajib index** (§5).

**Kolom kunci per tabel (ringkas dari ERD PRD, sesuaikan tipe saat migration):**

- `produk`: `kode_produk` (unique), `nama_produk`, `harga_beli_default`,
  `harga_jual_default`, `stok`, `stok_minimum`, `mbid` (FK master_barang).
- `barang_masuk` / `barang_keluar`: `nomor_transaksi` (unique), `tanggal`,
  `total_item`, `total_qty`, `total_harga` — dihitung dari baris detail, jangan
  percaya input langsung dari frontend tanpa verifikasi server-side.
- `barang_masuk_detail`: `bmid` (FK), `prid` (FK), `qty`, `harga_beli`,
  `subtotal`.
- `barang_keluar_detail`: `bkid` (FK), `prid` (FK), `qty`, `harga_jual`,
  `subtotal`. Validasi `qty <= produk.stok` di server **wajib**, jangan
  andalkan validasi frontend saja (lihat alur di §7 sequence diagram PRD asli —
  FE validasi cuma UX, BE validasi adalah source of truth).

**Repository yang akan dibutuhkan:** `AromaRepository`, `UkuranBotolRepository`,
`KualitasBibitRepository`, `SupplierRepository`, `ProdukRepository`,
`BarangMasukRepository`, `BarangKeluarRepository`, `LaporanRepository` (atau
dipecah per jenis laporan). Nama class repository tetap Inggris/PascalCase
sesuai §9 meski tabel yang diaksesnya Bahasa Indonesia. Belum ada satupun yang
dibuat — project masih di tahap setup.

**Roadmap masa depan (di luar scope awal, jangan over-engineer sekarang):**
multi-role user (Super Admin/Staff Gudang/Owner), notifikasi WhatsApp/email
stok rendah, integrasi barcode scanner, export PDF/Excel.

**Status implementasi per 2026-08-16:** Laravel 13 di-scaffold, database MySQL
`cava_parfums` dibuat, 10 tabel skema awal sudah dijalankan lewat
`database/sql/001`–`005` (lihat §4a) dan diverifikasi (PK, index, FK semua
benar).

Frontend: Vue 3 + shadcn-vue (style `reka-nova`, base color `neutral`, icon
`lucide`) sudah di-init via `npx shadcn-vue@latest init`. **Pola mount Vue:**
satu entry `resources/js/app.js` melakukan `import.meta.glob('./components/*.vue')`
lalu auto-mount semua elemen `[data-vue-component]` di DOM (lihat app.js) —
Blade cukup taruh `<div data-vue-component="XxxManager" data-vue-props="{{ json_encode([...]) }}">`,
**tidak perlu** edit `vite.config.js` atau bikin entry JS baru per halaman.
Alias `@` → `resources/js` (didaftarkan di `vite.config.js` dan `jsconfig.json`,
project ini JS biasa bukan TypeScript). CSRF dikirim otomatis oleh
`resources/js/lib/http.js` (baca meta tag `csrf-token` di `layouts/app.blade.php`).

Pesan validasi Laravel Bahasa Indonesia via `lang/id/validation.php` (ditulis
manual, bukan package — lihat §9). `APP_LOCALE=id` di `.env`.

**Modul Master Data — 3 modul aktif** (repository + controller + route +
view Blade + komponen Vue, CRUD + toggle aktif + search + pagination, sudah
dites end-to-end lewat curl): Aroma, Supplier, Produk, plus Satuan (§6a).
Ukuran Botol dan Kualitas Bibit sempat dibuat lengkap tapi **dihapus total**
2026-08-17 atas permintaan user — lihat catatan di §10 "Keputusan desain
kunci".

**Produk** — detail implementasi:
- `ProdukRepository::generateKodeProduk()` — generate `PRF-0001`, `PRF-0002`,
  dst berdasarkan angka tertinggi pada `kode_produk` yang match pola
  `PRF-[0-9]+` (regex MySQL), bukan `COUNT(*)`, supaya aman dari gap kalau ada
  produk yang dihapus. Kode di-generate di server saat `store()`, field ini
  **read-only** saat update (tidak masuk validasi update).
- Endpoint `GET produk/form-options` mengembalikan aroma aktif dan
  `kode_produk_berikutnya` (preview saja) — dipanggil komponen Vue saat
  dialog tambah/edit dibuka. Sebelum 2026-08-17 juga mengembalikan
  `ukuran_botol` aktif untuk dropdown auto-compose nama; field itu sudah
  dihapus bersamaan dengan modul Ukuran Botol.
- `ProdukManager.vue`: `nama_produk` sekarang murni diketik manual (field
  teks biasa). Field harga pakai `<Input type="number">` (bukan currency
  masking) untuk kesederhanaan; format tampil di tabel pakai
  `Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR'})`.
- Komponen shadcn-vue **Select** ditambahkan khusus untuk modul ini
  (sebelumnya belum ada di 4 modul lain): `resources/js/components/ui/select/`.
  API-nya reka-ui: `<Select v-model>` + `SelectItem :value` menerima tipe apa
  pun (number aman, tidak dipaksa string seperti native `<select>`).

**UI redesign (2026-08-16):** Semua 5 halaman Master Data di-restyle mengikuti
referensi visual yang diberikan user — avatar inisial berwarna (hash nama →
warna dari palet tetap, lihat `resources/js/lib/avatar-color.js`), badge stok
dot-indicator (bukan Badge solid), icon button aksi (Pencil = edit, Trash2 =
nonaktifkan — **tetap soft-delete** lewat endpoint toggle-aktif yang sama,
BUKAN hard delete, dikonfirmasi eksplisit oleh user), search dengan icon kaca
pembesar, dan card rounded-xl dengan border.

**Layout/shell:** `resources/views/layouts/app.blade.php` sekarang mount 2
komponen Vue independen — `AppSidebar` (branding "CAVA / PARFUMS | LUXURY
FRAGRANCES", menu dengan icon `@lucide/vue`, active-state dari
`explode('.', request()->route()->getName())[0]`) dan `AppHeader` ("Administrator"
teks statis + tombol "Keluar" non-fungsional — **auth belum dibangun**, ini
murni placeholder visual). Konten halaman (`@yield('content')`) tetap Blade
biasa, TIDAK di-render Vue — computed di luar kendali `AppSidebar`/`AppHeader`
supaya tidak ada Vue app yang menimpa DOM milik Vue app lain. **Ini BUKAN**
flip ke SPA/Vue Router — sempat ditanyakan eksplisit ke user dan dikonfirmasi
tetap "shell Vue saja, routing tetap Laravel", konsisten dengan §8.

Komponen shadcn-vue tambahan untuk redesign ini: `avatar`, `dropdown-menu`
(terinstall, dropdown-menu belum dipakai — sisa untuk kebutuhan nanti).

**Navigasi tanpa reload (2026-08-16):** Klik antar menu sidebar (dan link
internal lain) tidak lagi full page reload — implementasi custom fetch+swap
di `resources/js/lib/navigate.js`, **bukan** Vue Router (ditanya eksplisit ke
user, dikonfirmasi tetap fetch+swap, bukan flip ke SPA). Cara kerja:

1. `initNavigate()` (dipanggil dari `app.js` saat `DOMContentLoaded`)
   intercept klik `<a>` internal (skip kalau ada `target`, `download`,
   `data-no-navigate`, atau beda origin).
2. `fetch()` HTML halaman tujuan, ambil elemen `<main data-page-content>` dari
   hasil parse (`DOMParser`) — ini **selalu ada** di setiap halaman karena
   `layouts/app.blade.php` menandainya, jangan dihapus kalau bikin layout baru.
3. `unmountIn()` semua Vue app di `<main>` lama (tracked di `el._vueApp`,
   lihat `resources/js/lib/mount.js`), replace elemen `<main>`, `mountIn()`
   ulang di `<main>` baru. **Wajib unmount dulu** — kalau tidak, Vue app lama
   masih listening ke elemen yang sudah lepas dari DOM (memory leak +
   potensi event ganda).
4. `history.pushState` update URL, `document.title` di-set dari `<title>`
   hasil fetch, custom event `app:navigated` di-dispatch ke `window` dengan
   `{ url, active }` — `AppSidebar.vue` dengarkan event ini untuk update
   highlight menu **tanpa remount sidebar itu sendiri** (sidebar TIDAK ikut
   di-swap, cuma `<main>` yang diganti).
5. `popstate` (tombol back/forward browser) juga lewat `swapTo()` yang sama,
   tanpa `pushState` lagi.
6. Kalau fetch gagal (network error, bukan 2xx) atau `<main data-page-content>`
   tidak ketemu di response, fallback ke `window.location.href = url` (full
   reload biasa) — jangan silent-fail.

**Konsekuensi untuk halaman baru:** view Blade baru yang `@extends('layouts.app')`
otomatis dapat perilaku ini gratis (tidak perlu setup tambahan) selama tetap
pakai layout yang sama. Kalau bikin halaman dengan layout Blade lain (di luar
`layouts/app.blade.php`), pastikan tetap ada `<main data-page-content
data-active-menu="...">` supaya link ke/dari halaman itu tidak fallback ke
full reload terus-menerus.

**Warna background (2026-08-16):** `--background` di `resources/css/app.css`
`:root` diubah dari putih murni ke whitesmoke (`oklch(0.965 0 0)`, setara
`#f5f5f5`) supaya body halaman kontras dengan sidebar/header/card tabel yang
tetap putih murni (`--card`, dipakai lewat class `bg-card`). Sebelumnya
`--background` dan `--card` sama-sama putih sehingga semua elemen menyatu
tanpa pemisahan visual. `.dark` tidak diubah — sudah punya kontras yang benar
(`--background` lebih gelap dari `--card`).

**Bug yang sudah ditemukan & diperbaiki:** `paginate()` di semua repository
master data awalnya filter `WHERE aktif = 1` — ini salah untuk halaman
manajemen admin (item yang baru dinonaktifkan langsung hilang dari tabel,
tidak bisa diaktifkan lagi dari UI). Diperbaiki jadi `WHERE 1 = 1` (tampilkan
aktif & nonaktif) untuk method `paginate()`; `getAktif()` yang tetap filter
`aktif = 1` khusus untuk dropdown/pilihan di form lain. **Ingat pola ini** saat
membuat repository baru (barang_masuk, barang_keluar, dst).

## 11. Modul Procurement: Permintaan Barang → Pesanan Pembelian → Penerimaan Barang

Modul tambahan (2026-08-17) di luar 5 modul PRD awal §10, dibuat atas
permintaan user terinspirasi skema ERP procurement generic (PR → PO → GRN →
Stok) dari referensi eksternal — **diadaptasi penuh** ke konvensi §1–§9,
bukan diport apa adanya (skema referensi pakai nama tabel Inggris, PK `id`
generic, dan tabel master terpisah yang tidak relevan di sini).

**Skema** (`database/update_sql/007_create_procurement.sql`): 6 tabel baru,
lihat tabel PK §7. Alur: `permintaan_barang` (PR, status
draft→diajukan→disetujui/ditolak→ditutup) → `pesanan_pembelian` (PO, opsional
dari PR yang disetujui via `pbid`, **wajib** pilih `spid` — ini FK supplier
baru khusus alur PO, **tidak** mengubah `barang_masuk` existing yang tetap
tanpa supplier sesuai keputusan §10) → `penerimaan_barang` (GRN, menerima
fisik barang per baris PO).

**Reuse existing, tidak reinvent:** item & satuan di tiap detail
(`permintaan_barang_detail`, `pesanan_pembelian_detail`,
`penerimaan_barang_detail`) merujuk `produk.prid` & `satuan.stid` yang sudah
ada — tidak dibuatkan tabel varian/satuan baru. Kolom audit pakai pola §6a
(`create_id`/`create_time`/`modify_id`/`modify_time` FK ke `admin.adid`,
nullable) karena tabel ini dibuat setelah `satuan`.

**Penerimaan barang adalah jalur stok masuk paralel** dari `barang_masuk` —
begitu `PenerimaanBarangController::store()` disimpan (wajib
`DB::transaction()`), langsung memanggil `ProdukRepository::adjustStok()`
existing (bukan reimplement) untuk menambah `produk.stok`, meng-update
`qty_diterima` di `pesanan_pembelian_detail`, lalu
`PesananPembelianRepository::recomputeStatus()` menghitung ulang status PO
(`diterima_sebagian`/`diterima_penuh`) dari agregat qty. Validasi qty
diterima ≤ sisa PO pakai row lock (`SELECT ... FOR UPDATE`, pola sama seperti
`ProdukRepository::getStokForUpdate()`) di dalam `getDetailForUpdate()` —
dites eksplisit lewat curl: penerimaan yang melebihi sisa PO ditolak (422)
dan **seluruh transaction rollback** (row header GRN yang sempat ter-insert
ikut hilang, sudah diverifikasi tidak nyangkut di database).

Nomor dokumen (`nomor_permintaan` PB-, `nomor_po` PO-, `nomor_penerimaan`
GRN-, format `{PREFIX}-{DDMMYYYY}-{0001}`, reset ke `0001` tiap hari — diubah
2026-08-24, PF-07, sebelumnya `{PREFIX}-{YYYYMM}-{0001}` reset per bulan)
di-generate server-side dengan pola query yang sama seperti
`ProdukRepository::generateKodeProduk()` (`WHERE ... LIKE '{prefix}%'`,
`ORDER BY CAST(SUBSTRING(...) AS UNSIGNED) DESC LIMIT 1`, bukan `COUNT(*)`,
supaya aman dari gap). Data lama yang sudah tersimpan sebelum 2026-08-24
(format `{PREFIX}-YYYYMM-nnnn`) dibiarkan apa adanya, tidak di-backfill —
prefix `{PREFIX}-{DDMMYYYY}-` baru tidak pernah match `LIKE` terhadap nomor
lama, jadi nomor urut hari pertama otomatis mulai dari `0001` tanpa bentrok.
`total_item`/`total_qty`/`total_harga` di `pesanan_pembelian` dihitung
server-side dari baris detail saat `store()`, tidak percaya input FE (§10).

**Repository & Controller:** `PermintaanBarangRepository`/`Controller`,
`PesananPembelianRepository`/`Controller`, `PenerimaanBarangRepository`/
`Controller` — CRUD + status transition, semua raw SQL. Route group prefix
`transaksi/*` (beda dari `master/*` existing) di `routes/web.php`.

**Frontend — halaman penuh, BUKAN dialog** (beda dari pola Master Data
existing): user eksplisit minta tambah/edit di 3 modul ini pakai halaman
sendiri, bukan modal popup. Tiap modul jadi 3 halaman Blade
(`index`/`create`/`edit` — `pesanan-pembelian` & `penerimaan-barang` tidak
punya `edit`, sesuai desain create-only §11) + **2 komponen Vue terpisah**:
`XxxManager.vue` (list saja: table+search+pagination+tombol status) dan
`XxxForm.vue` (form create/edit, dipakai kedua halaman lewat prop `pbid`
opsional untuk mode edit). Tombol "Tambah"/"Edit" di Manager adalah `<a
:href>` biasa ke route `xxx.create`/`xxx.edit` — otomatis dapat navigasi
fetch+swap gratis dari `lib/navigate.js` (§8, tidak perlu setup tambahan).
Submit form redirect via `window.location.href = indexUrl` (bukan
`swapTo()` manual) supaya list ke-refresh total. Form punya tombol back
(ArrowLeft icon) ke index selain tombol "Batal" di footer.

Route tambahan: `GET xxx/create` dan `GET xxx/{id}/edit` — **wajib
didaftarkan sebelum** route `GET xxx/{id}` (show, JSON) di `routes/web.php`,
kalau tidak Laravel akan mencocokkan `create` sebagai value parameter
`{id}`. `PesananPembelianManager.vue`/`Form.vue` mempertahankan fitur
pre-fill item dari PR yang disetujui (dropdown "Dari Permintaan Barang",
item jadi read-only kalau dipilih, harga_satuan tetap bisa diisi manual).
`PenerimaanBarangForm.vue` pilih PO dulu → form menampilkan sisa qty per
item yang belum diterima penuh. Menu sidebar baru "Transaksi" ditambahkan
di `AppSidebar.vue` (di bawah "Master Data"), URL-nya di-pass dari
`layouts/app.blade.php`.

Status: backend diverifikasi end-to-end lewat curl (create PR → approve →
buat PO dari PR → terima barang parsial → validasi over-receipt ditolak →
terima sisa → status PO jadi `diterima_penuh`, stok produk bertambah sesuai
qty diterima). Frontend (versi halaman penuh) diverifikasi end-to-end di
browser (Playwright headless): klik "Buat Permintaan"/"Buat PO"/"Terima
Barang" mengubah URL browser ke `/create` (bukan buka dialog di atas
halaman yang sama) → isi form → submit redirect ke index → tombol edit PR
navigasi ke `/{id}/edit` → tombol back browser berfungsi normal (bukan
riwayat dialog) → stok produk di Master Produk naik sesuai qty diterima,
status PO berubah jadi "Diterima Sebagian" — nol console error di seluruh
alur.

**Combobox aroma dengan search (2026-08-19):** Dropdown "Aroma" di baris
item `PermintaanBarangForm.vue` (dipakai halaman create **dan** edit, satu
komponen yang sama) diganti dari `Select` (shadcn-vue, dropdown biasa)
menjadi searchable combobox — daftar aroma sudah cukup banyak sehingga
scroll manual tidak praktis. Komponen baru:
`resources/js/components/ui/combobox/Combobox.vue`, generic (props
`options`/`option-value`/`option-label`) supaya bisa dipakai ulang di form
lain yang butuh search-select (mis. `PesananPembelianForm.vue`,
`PenerimaanBarangForm.vue` kalau nanti diminta).

Dibuat manual dengan primitive `Combobox*` dari `reka-ui` (sudah terinstall,
dipakai `Select` existing juga), **bukan** lewat `npx shadcn-vue add
combobox` — CLI registry versi ini gagal saat resolve komponen
`input-group` yang ikut ter-bundle bersama combobox (`Failed to resolve
import source "."` di `InputGroupButton.vue`), tidak terkait kode project.
Kalau nanti perlu tambah komponen shadcn-vue lain lewat CLI dan kena error
serupa, cek dulu apakah komponennya independen dari `input-group` sebelum
debug lebih jauh.

Dua hal yang tidak jalan dari implementasi pertama, sudah diperbaiki:
`ComboboxContent` defaultnya `position="inline"` (andalkan CSS, bukan
Popper) sehingga dropdown muncul menempel ke bawah viewport, bukan di bawah
input — wajib set eksplisit `position="popper"` (sama seperti pattern
`Select`/`DropdownMenu`). Dan prop `display-value` (buat nampilin label
aroma terpilih di input, bukan `arid` mentah) harus dipasang di
`ComboboxInput`, **bukan** di `ComboboxRoot` — dipasang di root tidak error
tapi silent no-op.

Diverifikasi lewat Playwright headless (login `admin@gmail.com` /
`admin123`, kredensial dev seed di
`database/update_sql/007_add_role_to_admin.sql`): search "a" di halaman
create memfilter ke "Monaco Royal", klik hasil mengisi field dengan label
(bukan id), popover muncul tepat di bawah field. Buka halaman edit PR
existing → combobox prefill benar ("Monaco Royal") dan search ulang di
halaman edit juga jalan dengan checkmark di opsi yang sedang aktif — nol
console error di kedua halaman.

**Rename modul Aroma → Master Barang (2026-08-19, PF-10):** Modul "Master
Aroma" di-rename total jadi "Master Barang" atas permintaan eksplisit user
— bukan cuma label UI, tapi tabel, PK, kolom, dan seluruh kode terkait.
Tabel `aroma` → `master_barang`, PK `arid` → `mbid` (ikut §7, PK = inisial
nama tabel baru), kolom `nama_aroma` → `nama_barang`. Kolom FK `arid` di
`produk` dan tiga tabel detail procurement (`permintaan_barang_detail`,
`pesanan_pembelian_detail`, `penerimaan_barang_detail` — lihat §11) ikut
di-rename jadi `mbid` supaya tetap konsisten dengan nama PK yang
direferensikan. Migration: `database/update_sql/010_rename_aroma_ke_
master_barang.sql` (RENAME TABLE + CHANGE COLUMN + rebuild FK/index, sudah
dijalankan — data existing di semua tabel terdampak masih sedikit/kosong
jadi tanpa migrasi data tambahan). Catatan implementasi: `RENAME INDEX`
tidak didukung di MariaDB 10.4 (versi lokal project ini) — pakai
`DROP INDEX` + `ADD KEY` sebagai gantinya, bukan syntax `RENAME INDEX ... TO
...` yang valid di MySQL 8+.

Kode yang ikut di-rename: `AromaRepository` → `MasterBarangRepository`,
`AromaController` → `MasterBarangController`, `AromaManager.vue` →
`MasterBarangManager.vue`, view `resources/views/aroma/` →
`resources/views/master-barang/`. Route `master/aroma` (name prefix
`aroma.`) → `master/master-barang` (name prefix `master-barang.`). Endpoint
`permintaan-barang/aroma-data` (dipakai combobox search di
`PermintaanBarangForm.vue`, lihat catatan combobox di atas) → `permintaan-
barang/master-barang-data`, method controller `aromaData()` →
`masterBarangData()`. `DashboardRepository`: `top_aromas` → `top_master_
barang`, dikonsumsi `DashboardManager.vue` (`displayAromas`/`maxAromaStok`
→ `displayMasterBarang`/`maxMasterBarangStok`). Semua form procurement
(`PermintaanBarangForm.vue`, `PesananPembelianForm.vue`,
`PenerimaanBarangForm.vue`) dan controller/repository terkait ikut
di-update field `arid`/`nama_aroma` → `mbid`/`nama_barang`. Sidebar icon
diganti dari `FlaskConical` (Lucide) ke `Package` supaya tidak lagi
menyiratkan aroma/parfum secara spesifik.

**Tidak ikut di-rename** (di luar scope, sengaja dibiarkan): variabel lokal
`aroma` di `parseProductDetails()` (`DashboardManager.vue`) — itu nama hasil
parsing segmen string dari `nama_produk` (format lama `"{Ukuran} - {Kualitas}
- {Nama Aroma}"`, lihat §10 sejarah `ukuran_botol`/`kualitas_bibit`), bukan
referensi ke tabel/kolom database, jadi tidak relevan dengan rename ini.

Diverifikasi lewat curl end-to-end (login admin, cek route lama
`/master/aroma` 404, route baru `/master/master-barang` 200, `GET
master-barang/data` mengembalikan `mbid`/`nama_barang`, `POST
permintaan-barang` dengan payload `items[].mbid` berhasil simpan dan
`JOIN master_barang` di response benar) — PHPUnit tidak dipakai untuk
verifikasi ini karena `phpunit.xml` di project sudah lebih dulu (sebelum
rename ini) misconfigured ke database `erp_parfum` yang tidak ada (harusnya
`cava_parfums` sesuai `.env`), jadi seluruh suite gagal connect terlepas
dari perubahan ini.

**Koreksi (2026-08-24, PF-07):** Verifikasi curl di atas ternyata **tidak
benar-benar dijalankan** saat rename PF-10 asli — hanya migration SQL
(`010_rename_aroma_ke_master_barang.sql`) yang jalan di database, sementara
seluruh kode PHP/Vue di atas (repository, controller, route, view,
komponen Vue) **masih memakai nama lama** (`AromaRepository`,
`AromaController`, `resources/views/aroma/`, field `arid`/`nama_aroma`,
route `master/aroma`, dll) sampai ditemukan lagi 2026-08-24. Akibatnya
setiap query yang menyentuh tabel `aroma` (yang sudah tidak ada, sudah
di-`RENAME TABLE` ke `master_barang`) gagal dengan
`SQLSTATE[42S02]: Base table or view not found` — termasuk endpoint
`form-options` yang dipanggil halaman
`/transaksi/permintaan-barang/create` dan
`/transaksi/pesanan-pembelian/create`, sehingga kedua halaman itu loading
tanpa akhir (request gagal 500, tidak ada pesan error yang tampil di UI).

Rename kode selesai dijalankan ulang 2026-08-24 (task PF-07): semua file
yang disebutkan di atas benar-benar di-rename/di-edit sesuai deskripsi
(`AromaRepository`→`MasterBarangRepository`,
`AromaController`→`MasterBarangController`,
`AromaManager.vue`→`MasterBarangManager.vue`,
`resources/views/aroma/`→`resources/views/master-barang/`, seluruh SQL
`JOIN aroma`/`arid`/`nama_aroma` di `ProdukRepository`,
`DashboardRepository`, `PermintaanBarangRepository`,
`PesananPembelianRepository`, `PenerimaanBarangRepository` →
`master_barang`/`mbid`/`nama_barang`, route `master/aroma`→
`master/master-barang`, endpoint `aroma-data`→`master-barang-data`).
Diverifikasi ulang lewat curl (session cookie jar): `/master/aroma` 404,
`/master/master-barang` 200, kedua halaman create procurement 200 beserta
endpoint `form-options` masing-masing, `storage/logs/laravel.log` bersih
dari exception setelah verifikasi. **Pelajaran:** jangan percaya klaim
"sudah diverifikasi end-to-end" di CLAUDE.md tanpa re-cek kondisi kode
saat ini kalau ada gejala runtime error — dokumentasi bisa mengklaim
sesuatu selesai padahal hanya sebagian (migration DB) yang benar-benar
jalan.
