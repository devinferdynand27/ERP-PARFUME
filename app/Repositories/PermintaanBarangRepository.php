<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PermintaanBarangRepository
{
    public function paginate(int $perPage, int $page, ?string $search = null, ?string $status = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND pb.nomor_permintaan LIKE ?';
            $bindings[] = "%{$search}%";
        }

        if ($status !== null && $status !== '') {
            $where .= ' AND pb.status = ?';
            $bindings[] = $status;
        }

        $total = DB::selectOne("SELECT COUNT(*) AS total FROM permintaan_barang pb {$where}", $bindings)->total;

        $data = DB::select("
            SELECT pb.pbid, pb.nomor_permintaan, pb.tanggal, pb.status, pb.catatan,
                pb.create_id, pb.create_time, pb.modify_id, pb.modify_time,
                pembuat.nama_admin AS create_nama,
                (SELECT COUNT(*) FROM permintaan_barang_detail d WHERE d.pbid = pb.pbid) AS total_item
            FROM permintaan_barang pb
            LEFT JOIN admin pembuat ON pembuat.adid = pb.create_id
            {$where}
            ORDER BY pb.tanggal DESC, pb.pbid DESC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $pbid): ?object
    {
        return DB::selectOne('
            SELECT pb.pbid, pb.nomor_permintaan, pb.tanggal, pb.status, pb.catatan,
                pb.create_id, a.nama_admin AS dibuat_oleh, pb.create_time, pb.modify_id, pb.modify_time
            FROM permintaan_barang pb
            LEFT JOIN admin a ON a.adid = pb.create_id
            WHERE pb.pbid = ?
        ', [$pbid]);
    }

    public function getItems(int $pbid): array
    {
        return DB::select('
            SELECT d.pbdid, d.pbid, d.mbid, mb.nama_barang,
                d.stid, s.nama_satuan, d.qty_diminta
            FROM permintaan_barang_detail d
            JOIN master_barang mb ON mb.mbid = d.mbid
            JOIN satuan s ON s.stid = d.stid
            WHERE d.pbid = ?
            ORDER BY d.pbdid ASC
        ', [$pbid]);
    }

    /**
     * Generate nomor permintaan format PB-{DDMMYYYY}-{0001}, berdasarkan nomor
     * tertinggi hari berjalan (bukan COUNT, aman dari gap data terhapus).
     */
    public function generateNomorPermintaan(): string
    {
        $prefix = 'PB-' . now()->format('dmY') . '-';

        $last = DB::selectOne('
            SELECT nomor_permintaan FROM permintaan_barang
            WHERE nomor_permintaan LIKE ?
            ORDER BY CAST(SUBSTRING(nomor_permintaan, ?) AS UNSIGNED) DESC
            LIMIT 1
        ', ["{$prefix}%", strlen($prefix) + 1]);

        $nextNumber = $last ? ((int) substr($last->nomor_permintaan, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $header, array $items, ?int $adid): int
    {
        DB::insert('
            INSERT INTO permintaan_barang (nomor_permintaan, tanggal, status, catatan, create_id, create_time, modify_id, modify_time)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, NOW())
        ', [
            $header['nomor_permintaan'],
            $header['tanggal'],
            $header['status'] ?? 'draft',
            $header['catatan'] ?? null,
            $adid,
            $adid,
        ]);

        $pbid = (int) DB::getPdo()->lastInsertId();

        $this->insertItems($pbid, $items);

        return $pbid;
    }

    public function update(int $pbid, array $header, array $items, ?int $adid): bool
    {
        $updated = DB::update('
            UPDATE permintaan_barang
            SET tanggal = ?, catatan = ?, modify_id = ?, modify_time = NOW()
            WHERE pbid = ?
        ', [$header['tanggal'], $header['catatan'] ?? null, $adid, $pbid]) > 0;

        DB::delete('DELETE FROM permintaan_barang_detail WHERE pbid = ?', [$pbid]);
        $this->insertItems($pbid, $items);

        return $updated;
    }

    private function insertItems(int $pbid, array $items): void
    {
        foreach ($items as $item) {
            DB::insert('
                INSERT INTO permintaan_barang_detail (pbid, mbid, stid, qty_diminta)
                VALUES (?, ?, ?, ?)
            ', [$pbid, $item['mbid'], $item['stid'], $item['qty_diminta']]);
        }
    }

    public function updateStatus(int $pbid, string $status, ?int $adid): bool
    {
        return DB::update('
            UPDATE permintaan_barang
            SET status = ?, modify_id = ?, modify_time = NOW()
            WHERE pbid = ?
        ', [$status, $adid, $pbid]) > 0;
    }

    public function delete(int $pbid): bool
    {
        DB::delete('DELETE FROM permintaan_barang_detail WHERE pbid = ?', [$pbid]);

        return DB::delete('DELETE FROM permintaan_barang WHERE pbid = ?', [$pbid]) > 0;
    }
}
