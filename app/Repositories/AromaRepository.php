<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class AromaRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT arid, nama_aroma, kategori
            FROM aroma
            WHERE aktif = 1
            ORDER BY nama_aroma ASC
        ');
    }

    /**
     * Untuk halaman manajemen (CRUD admin) — tampilkan aktif & nonaktif
     * sekaligus, supaya item yang dinonaktifkan tetap terlihat dan bisa
     * diaktifkan kembali. Gunakan getAktif() untuk dropdown/pilihan di form
     * lain yang memang harus hanya menampilkan aroma aktif.
     */
    public function paginate(int $perPage, int $page, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND (nama_aroma LIKE ? OR kategori LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM aroma {$where}", $bindings)->total;

        $data = DB::select("
            SELECT arid, nama_aroma, kategori, aktif
            FROM aroma
            {$where}
            ORDER BY nama_aroma ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Untuk dialog pilih barang di form transaksi (permintaan/pesanan/
     * penerimaan) — hanya aroma aktif, dipaginate + searchable supaya aman
     * untuk data ribuan baris (tidak seperti getAktif() yang ambil semua
     * sekaligus).
     */
    public function paginateAktif(int $perPage, int $page, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE aktif = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND nama_aroma LIKE ?';
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM aroma {$where}", $bindings)->total;

        $data = DB::select("
            SELECT arid, nama_aroma, kategori
            FROM aroma
            {$where}
            ORDER BY nama_aroma ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $arid): ?object
    {
        return DB::selectOne('
            SELECT arid, nama_aroma, kategori, aktif
            FROM aroma
            WHERE arid = ?
        ', [$arid]);
    }

    public function create(array $data): int
    {
        DB::insert('
            INSERT INTO aroma (nama_aroma, kategori, aktif, created_at, updated_at)
            VALUES (?, ?, 1, NOW(), NOW())
        ', [$data['nama_aroma'], $data['kategori']]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $arid, array $data): bool
    {
        return DB::update('
            UPDATE aroma
            SET nama_aroma = ?, kategori = ?, updated_at = NOW()
            WHERE arid = ?
        ', [$data['nama_aroma'], $data['kategori'], $arid]) > 0;
    }

    public function setAktif(int $arid, bool $aktif): bool
    {
        return DB::update('
            UPDATE aroma SET aktif = ?, updated_at = NOW() WHERE arid = ?
        ', [$aktif ? 1 : 0, $arid]) > 0;
    }

    public function isUsedByActiveProduk(int $arid): bool
    {
        $result = DB::selectOne('
            SELECT COUNT(*) AS total FROM produk WHERE arid = ? AND aktif = 1
        ', [$arid]);

        return $result->total > 0;
    }
}
