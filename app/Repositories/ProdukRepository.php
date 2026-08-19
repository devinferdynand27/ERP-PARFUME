<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ProdukRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT p.prid, p.kode_produk, p.nama_produk, p.stok, p.stok_minimum,
                   p.harga_beli_default, p.harga_jual_default, mb.nama_barang
            FROM produk p
            JOIN master_barang mb ON mb.mbid = p.mbid
            WHERE p.aktif = 1
            ORDER BY p.nama_produk ASC
        ');
    }

    /**
     * Untuk halaman manajemen — tampilkan aktif & nonaktif sekaligus supaya
     * item nonaktif tetap terlihat dan bisa diaktifkan lagi. getAktif() yang
     * dipakai untuk dropdown di form lain (mis. pemilihan produk saat input
     * barang masuk/keluar).
     */
    public function paginate(int $perPage, int $page, ?string $search = null, ?int $mbid = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND (p.nama_produk LIKE ? OR p.kode_produk LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        if ($mbid !== null) {
            $where .= ' AND p.mbid = ?';
            $bindings[] = $mbid;
        }

        $total = DB::selectOne("
            SELECT COUNT(*) AS total FROM produk p {$where}
        ", $bindings)->total;

        $data = DB::select("
            SELECT p.prid, p.kode_produk, p.nama_produk, p.mbid, mb.nama_barang,
                   p.stok, p.stok_minimum,
                   p.harga_beli_default, p.harga_jual_default, p.aktif
            FROM produk p
            JOIN master_barang mb ON mb.mbid = p.mbid
            {$where}
            ORDER BY p.nama_produk ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $prid): ?object
    {
        return DB::selectOne('
            SELECT p.prid, p.kode_produk, p.nama_produk, p.mbid, mb.nama_barang,
                   p.harga_beli_default, p.harga_jual_default,
                   p.stok, p.stok_minimum, p.aktif
            FROM produk p
            JOIN master_barang mb ON mb.mbid = p.mbid
            WHERE p.prid = ?
        ', [$prid]);
    }

    public function getLowStock(): array
    {
        return DB::select('
            SELECT p.prid, p.kode_produk, p.nama_produk, p.stok, p.stok_minimum
            FROM produk p
            WHERE p.aktif = 1 AND p.stok <= p.stok_minimum
            ORDER BY p.stok ASC
        ');
    }

    public function countLowStock(): int
    {
        return DB::selectOne('
            SELECT COUNT(*) AS total FROM produk WHERE aktif = 1 AND stok <= stok_minimum
        ')->total;
    }

    public function countAktif(): int
    {
        return DB::selectOne('SELECT COUNT(*) AS total FROM produk WHERE aktif = 1')->total;
    }

    public function sumStok(): int
    {
        return (int) DB::selectOne('SELECT COALESCE(SUM(stok), 0) AS total FROM produk WHERE aktif = 1')->total;
    }

    /**
     * Generate kode produk unik format PRF-0001, PRF-0002, dst — berdasarkan
     * kode_produk PRF-xxxx tertinggi yang sudah ada (bukan COUNT, supaya
     * aman dari gap akibat produk yang dihapus/dinonaktifkan).
     */
    public function generateKodeProduk(): string
    {
        $last = DB::selectOne("
            SELECT kode_produk FROM produk
            WHERE kode_produk REGEXP '^PRF-[0-9]+$'
            ORDER BY CAST(SUBSTRING(kode_produk, 5) AS UNSIGNED) DESC
            LIMIT 1
        ");

        $nextNumber = $last ? ((int) substr($last->kode_produk, 4)) + 1 : 1;

        return 'PRF-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function kodeProdukExists(string $kodeProduk, ?int $exceptPrid = null): bool
    {
        if ($exceptPrid !== null) {
            $result = DB::selectOne('
                SELECT COUNT(*) AS total FROM produk WHERE kode_produk = ? AND prid != ?
            ', [$kodeProduk, $exceptPrid]);
        } else {
            $result = DB::selectOne('
                SELECT COUNT(*) AS total FROM produk WHERE kode_produk = ?
            ', [$kodeProduk]);
        }

        return $result->total > 0;
    }

    public function create(array $data): int
    {
        DB::insert('
            INSERT INTO produk (
                kode_produk, nama_produk, mbid,
                harga_beli_default, harga_jual_default,
                stok, stok_minimum, aktif, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ', [
            $data['kode_produk'],
            $data['nama_produk'],
            $data['mbid'],
            $data['harga_beli_default'],
            $data['harga_jual_default'],
            $data['stok'] ?? 0,
            $data['stok_minimum'] ?? 0,
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $prid, array $data): bool
    {
        return DB::update('
            UPDATE produk
            SET nama_produk = ?, mbid = ?, harga_beli_default = ?,
                harga_jual_default = ?, stok_minimum = ?, updated_at = NOW()
            WHERE prid = ?
        ', [
            $data['nama_produk'],
            $data['mbid'],
            $data['harga_beli_default'],
            $data['harga_jual_default'],
            $data['stok_minimum'] ?? 0,
            $prid,
        ]) > 0;
    }

    public function setAktif(int $prid, bool $aktif): bool
    {
        return DB::update('
            UPDATE produk SET aktif = ?, updated_at = NOW() WHERE prid = ?
        ', [$aktif ? 1 : 0, $prid]) > 0;
    }

    /**
     * Tambah/kurangi stok produk. $delta boleh negatif (stock out) atau
     * positif (stock in). Dipanggil dari dalam DB::transaction() oleh
     * BarangMasukRepository/BarangKeluarRepository.
     */
    public function adjustStok(int $prid, int $delta): bool
    {
        return DB::update('
            UPDATE produk SET stok = stok + ?, updated_at = NOW() WHERE prid = ?
        ', [$delta, $prid]) > 0;
    }

    public function getStokForUpdate(int $prid): ?object
    {
        return DB::selectOne('
            SELECT prid, stok FROM produk WHERE prid = ? FOR UPDATE
        ', [$prid]);
    }
}
