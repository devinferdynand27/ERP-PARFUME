<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class KualitasBibitRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT kbid, kualitas
            FROM kualitas_bibit
            WHERE aktif = 1
            ORDER BY kualitas ASC
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
            $where .= ' AND kualitas LIKE ?';
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM kualitas_bibit {$where}", $bindings)->total;

        $data = DB::select("
            SELECT kbid, kualitas, aktif
            FROM kualitas_bibit
            {$where}
            ORDER BY kualitas ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $kbid): ?object
    {
        return DB::selectOne('
            SELECT kbid, kualitas, aktif
            FROM kualitas_bibit
            WHERE kbid = ?
        ', [$kbid]);
    }

    public function create(array $data): int
    {
        DB::insert('
            INSERT INTO kualitas_bibit (kualitas, aktif, created_at, updated_at)
            VALUES (?, 1, NOW(), NOW())
        ', [$data['kualitas']]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $kbid, array $data): bool
    {
        return DB::update('
            UPDATE kualitas_bibit
            SET kualitas = ?, updated_at = NOW()
            WHERE kbid = ?
        ', [$data['kualitas'], $kbid]) > 0;
    }

    public function setAktif(int $kbid, bool $aktif): bool
    {
        return DB::update('
            UPDATE kualitas_bibit SET aktif = ?, updated_at = NOW() WHERE kbid = ?
        ', [$aktif ? 1 : 0, $kbid]) > 0;
    }
}
