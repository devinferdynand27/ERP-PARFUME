<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class KualitasRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT kuid, nama_kualitas, keterangan
            FROM kualitas
            WHERE aktif = 1
            ORDER BY nama_kualitas ASC
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
            $where .= ' AND (nama_kualitas LIKE ? OR keterangan LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM kualitas {$where}", $bindings)->total;

        $data = DB::select("
            SELECT k.kuid, k.nama_kualitas, k.keterangan, k.aktif,
                k.create_id, k.create_time,
                k.modify_id, k.modify_time,
                pembuat.nama_admin AS create_nama,
                pengubah.nama_admin AS modify_nama
            FROM kualitas k
            LEFT JOIN admin pembuat ON pembuat.adid = k.create_id
            LEFT JOIN admin pengubah ON pengubah.adid = k.modify_id
            {$where}
            ORDER BY k.nama_kualitas ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $kuid): ?object
    {
        return DB::selectOne('
            SELECT kuid, nama_kualitas, keterangan, aktif,
                create_id, create_time, modify_id, modify_time
            FROM kualitas
            WHERE kuid = ?
        ', [$kuid]);
    }

    public function create(array $data, ?int $adid): int
    {
        DB::insert('
            INSERT INTO kualitas (nama_kualitas, keterangan, aktif, create_id, create_time, modify_id, modify_time)
            VALUES (?, ?, 1, ?, NOW(), ?, NOW())
        ', [$data['nama_kualitas'], $data['keterangan'] ?? null, $adid, $adid]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $kuid, array $data, ?int $adid): bool
    {
        return DB::update('
            UPDATE kualitas
            SET nama_kualitas = ?, keterangan = ?, modify_id = ?, modify_time = NOW()
            WHERE kuid = ?
        ', [$data['nama_kualitas'], $data['keterangan'] ?? null, $adid, $kuid]) > 0;
    }

    public function setAktif(int $kuid, bool $aktif, ?int $adid): bool
    {
        return DB::update('
            UPDATE kualitas
            SET aktif = ?, modify_id = ?, modify_time = NOW()
            WHERE kuid = ?
        ', [$aktif ? 1 : 0, $adid, $kuid]) > 0;
    }
}
