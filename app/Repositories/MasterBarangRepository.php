<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class MasterBarangRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT mbid, nama_barang, kategori
            FROM master_barang
            WHERE aktif = 1
            ORDER BY nama_barang ASC
        ');
    }

    /**
     * Untuk halaman manajemen (CRUD admin) — tampilkan aktif & nonaktif
     * sekaligus, supaya item yang dinonaktifkan tetap terlihat dan bisa
     * diaktifkan kembali. Gunakan getAktif() untuk dropdown/pilihan di form
     * lain yang memang harus hanya menampilkan master barang aktif.
     */
    public function paginate(int $perPage, int $page, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND (nama_barang LIKE ? OR kategori LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM master_barang {$where}", $bindings)->total;

        $data = DB::select("
            SELECT mbid, nama_barang, kategori, aktif
            FROM master_barang
            {$where}
            ORDER BY nama_barang ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Untuk dialog pilih barang di form transaksi (permintaan/pesanan/
     * penerimaan) — hanya master barang aktif, dipaginate + searchable supaya
     * aman untuk data ribuan baris (tidak seperti getAktif() yang ambil semua
     * sekaligus).
     */
    public function paginateAktif(int $perPage, int $page, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE aktif = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND nama_barang LIKE ?';
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM master_barang {$where}", $bindings)->total;

        $data = DB::select("
            SELECT mbid, nama_barang, kategori
            FROM master_barang
            {$where}
            ORDER BY nama_barang ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $mbid): ?object
    {
        return DB::selectOne('
            SELECT mbid, nama_barang, kategori, aktif
            FROM master_barang
            WHERE mbid = ?
        ', [$mbid]);
    }

    public function create(array $data): int
    {
        DB::insert('
            INSERT INTO master_barang (nama_barang, kategori, aktif, created_at, updated_at)
            VALUES (?, ?, 1, NOW(), NOW())
        ', [$data['nama_barang'], $data['kategori']]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $mbid, array $data): bool
    {
        return DB::update('
            UPDATE master_barang
            SET nama_barang = ?, kategori = ?, updated_at = NOW()
            WHERE mbid = ?
        ', [$data['nama_barang'], $data['kategori'], $mbid]) > 0;
    }

    public function setAktif(int $mbid, bool $aktif): bool
    {
        return DB::update('
            UPDATE master_barang SET aktif = ?, updated_at = NOW() WHERE mbid = ?
        ', [$aktif ? 1 : 0, $mbid]) > 0;
    }

    public function isUsedByActiveProduk(int $mbid): bool
    {
        $result = DB::selectOne('
            SELECT COUNT(*) AS total FROM produk WHERE mbid = ? AND aktif = 1
        ', [$mbid]);

        return $result->total > 0;
    }
}
