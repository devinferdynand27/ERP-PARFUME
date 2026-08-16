<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SupplierRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT spid, nama_supplier, kontak
            FROM supplier
            WHERE aktif = 1
            ORDER BY nama_supplier ASC
        ');
    }

    /**
     * Untuk halaman manajemen — tampilkan aktif & nonaktif sekaligus supaya
     * item nonaktif tetap terlihat dan bisa diaktifkan lagi. getAktif() yang
     * dipakai untuk dropdown di form lain.
     */
    public function paginate(int $perPage, int $page, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND (nama_supplier LIKE ? OR kontak LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM supplier {$where}", $bindings)->total;

        $data = DB::select("
            SELECT spid, nama_supplier, kontak, alamat, aktif
            FROM supplier
            {$where}
            ORDER BY nama_supplier ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $spid): ?object
    {
        return DB::selectOne('
            SELECT spid, nama_supplier, kontak, alamat, aktif
            FROM supplier
            WHERE spid = ?
        ', [$spid]);
    }

    public function create(array $data): int
    {
        DB::insert('
            INSERT INTO supplier (nama_supplier, kontak, alamat, aktif, created_at, updated_at)
            VALUES (?, ?, ?, 1, NOW(), NOW())
        ', [$data['nama_supplier'], $data['kontak'] ?? null, $data['alamat'] ?? null]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $spid, array $data): bool
    {
        return DB::update('
            UPDATE supplier
            SET nama_supplier = ?, kontak = ?, alamat = ?, updated_at = NOW()
            WHERE spid = ?
        ', [$data['nama_supplier'], $data['kontak'] ?? null, $data['alamat'] ?? null, $spid]) > 0;
    }

    public function setAktif(int $spid, bool $aktif): bool
    {
        return DB::update('
            UPDATE supplier SET aktif = ?, updated_at = NOW() WHERE spid = ?
        ', [$aktif ? 1 : 0, $spid]) > 0;
    }
}
