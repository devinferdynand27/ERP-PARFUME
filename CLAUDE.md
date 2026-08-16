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

## 7. Inisial pada primary key

Primary key memakai inisial dari nama tabel Bahasa Indonesia, bukan `id`:

| Tabel | Primary key |
|---|---|
| `produk` | `prid` |
| `aroma` | `arid` |

Foreign key memakai nama primary key yang dirujuk. Kalau satu tabel punya dua
relasi ke tabel yang sama, beri prefix peran: `arid_utama`, `dibuat_oleh_adid`.

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
- **`ukuran_botol` dan `kualitas_bibit` sengaja TANPA foreign key** ke `produk`
  — sesuai PRD asli: murni master data/dropdown di frontend untuk menyusun nama
  produk otomatis (`{Ukuran} - {Nama Aroma}`, mis. `50ml - Rose Elixir`), bukan
  kolom relasional. Jangan tambahkan FK `ukuran_botol`/`kualitas_bibit` ke
  `produk` kecuali user minta ubah.
- **`supplier` TIDAK direlasikan ke `barang_masuk`** — sesuai PRD asli, tidak
  ada kolom `spid` di `barang_masuk`. Supplier murni master data kontak, tidak
  dicatat per transaksi.
- **Frontend bukan SPA** — beda dari PRD asli, lihat §8.

**Penamaan tabel (Indonesia, sesuai PRD) & PK berinisial (final):**

| Tabel | PK |
|---|---|
| `admin` | `adid` |
| `aroma` | `arid` |
| `produk` | `prid` |
| `barang_masuk` | `bmid` |
| `barang_masuk_detail` | `bmdid` |
| `barang_keluar` | `bkid` |
| `barang_keluar_detail` | `bkdid` |
| `ukuran_botol` | `ubid` |
| `kualitas_bibit` | `kbid` |
| `supplier` | `spid` |

FK mengikuti nama PK yang dirujuk: `produk.arid` → `aroma.arid`,
`barang_masuk_detail.bmid` → `barang_masuk.bmid`, `barang_masuk_detail.prid` →
`produk.prid`, dst. Semua kolom FK **wajib index** (§5).

**Kolom kunci per tabel (ringkas dari ERD PRD, sesuaikan tipe saat migration):**

- `produk`: `kode_produk` (unique), `nama_produk`, `harga_beli_default`,
  `harga_jual_default`, `stok`, `stok_minimum`, `arid` (FK aroma).
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

**Modul Master Data — 5 dari 5 selesai penuh** (repository + controller +
route + view Blade + komponen Vue, CRUD + toggle aktif + search + pagination,
sudah dites end-to-end lewat curl): Aroma, Ukuran Botol, Kualitas Bibit,
Supplier, Produk.

**Produk** — detail implementasi:
- `ProdukRepository::generateKodeProduk()` — generate `PRF-0001`, `PRF-0002`,
  dst berdasarkan angka tertinggi pada `kode_produk` yang match pola
  `PRF-[0-9]+` (regex MySQL), bukan `COUNT(*)`, supaya aman dari gap kalau ada
  produk yang dihapus. Kode di-generate di server saat `store()`, field ini
  **read-only** saat update (tidak masuk validasi update).
- Endpoint `GET produk/form-options` mengembalikan aroma aktif, ukuran_botol
  aktif, dan `kode_produk_berikutnya` (preview saja) — dipanggil komponen Vue
  saat dialog tambah/edit dibuka.
- `ProdukManager.vue`: field "Ukuran Botol" adalah dropdown dari
  `ukuran_botol` tapi **tidak disimpan** ke tabel produk (sesuai desain
  tanpa-FK di atas) — cuma dipakai untuk auto-compose `nama_produk =
  "{ukuran} - {nama_aroma}"` lewat `watch()`, nama tetap bisa diedit manual
  setelah itu. Field harga pakai `<Input type="number">` (bukan currency
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
