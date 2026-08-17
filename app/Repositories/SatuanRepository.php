<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SatuanRepository
{
    public function getAktif(): array
    {
        return DB::select('
            SELECT stid, nama_satuan, tipe, isi
            FROM satuan
            WHERE aktif = 1
            ORDER BY nama_satuan ASC
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
            $where .= ' AND (nama_satuan LIKE ? OR tipe LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM satuan {$where}", $bindings)->total;

        $data = DB::select("
            SELECT s.stid, s.nama_satuan, s.tipe, s.isi, s.aktif,
                s.create_id, s.create_time,
                s.modify_id, s.modify_time,
                pembuat.nama_admin AS create_nama,
                pengubah.nama_admin AS modify_nama
            FROM satuan s
            LEFT JOIN admin pembuat ON pembuat.adid = s.create_id
            LEFT JOIN admin pengubah ON pengubah.adid = s.modify_id
            {$where}
            ORDER BY s.nama_satuan ASC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $stid): ?object
    {
        return DB::selectOne('
            SELECT stid, nama_satuan, tipe, isi, aktif,
                create_id, create_time, modify_id, modify_time
            FROM satuan
            WHERE stid = ?
        ', [$stid]);
    }

    public function create(array $data, ?int $adid): int
    {
        DB::insert('
            INSERT INTO satuan (nama_satuan, tipe, isi, aktif, create_id, create_time, modify_id, modify_time)
            VALUES (?, ?, ?, 1, ?, NOW(), ?, NOW())
        ', [$data['nama_satuan'], $data['tipe'], $data['isi'], $adid, $adid]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function update(int $stid, array $data, ?int $adid): bool
    {
        return DB::update('
            UPDATE satuan
            SET nama_satuan = ?, tipe = ?, isi = ?, modify_id = ?, modify_time = NOW()
            WHERE stid = ?
        ', [$data['nama_satuan'], $data['tipe'], $data['isi'], $adid, $stid]) > 0;
    }

    public function setAktif(int $stid, bool $aktif, ?int $adid): bool
    {
        return DB::update('
            UPDATE satuan
            SET aktif = ?, modify_id = ?, modify_time = NOW()
            WHERE stid = ?
        ', [$aktif ? 1 : 0, $adid, $stid]) > 0;
    }
}
