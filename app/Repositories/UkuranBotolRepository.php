<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class UkuranBotolRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT ubid, ukuran
            FROM ukuran_botol
            WHERE aktif = 1
            ORDER BY ukuran ASC
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
            $where .= ' AND ukuran LIKE ?';
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM ukuran_botol {$where}", $bindings)->total;

        $data = DB::select("
            SELECT ubid, ukuran, aktif
            FROM ukuran_botol
            {$where}
            ORDER BY ukuran ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $ubid): ?object
    {
        return DB::selectOne('
            SELECT ubid, ukuran, aktif
            FROM ukuran_botol
            WHERE ubid = ?
        ', [$ubid]);
    }

    public function create(array $data): int
    {
        DB::insert('
            INSERT INTO ukuran_botol (ukuran, aktif, created_at, updated_at)
            VALUES (?, 1, NOW(), NOW())
        ', [$data['ukuran']]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $ubid, array $data): bool
    {
        return DB::update('
            UPDATE ukuran_botol
            SET ukuran = ?, updated_at = NOW()
            WHERE ubid = ?
        ', [$data['ukuran'], $ubid]) > 0;
    }

    public function setAktif(int $ubid, bool $aktif): bool
    {
        return DB::update('
            UPDATE ukuran_botol SET aktif = ?, updated_at = NOW() WHERE ubid = ?
        ', [$aktif ? 1 : 0, $ubid]) > 0;
    }
}
