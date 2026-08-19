<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PenerimaanBarangRepository
{
    public function paginate(int $perPage, int $page, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND (pn.nomor_penerimaan LIKE ? OR pn.nomor_faktur_supplier LIKE ? OR pp.nomor_po LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $total = DB::selectOne("
            SELECT COUNT(*) AS total FROM penerimaan_barang pn
            JOIN pesanan_pembelian pp ON pp.ppid = pn.ppid
            {$where}
        ", $bindings)->total;

        $data = DB::select("
            SELECT pn.pnid, pn.nomor_penerimaan, pn.ppid, pp.nomor_po,
                pn.nomor_faktur_supplier, pn.tanggal, pn.catatan,
                (SELECT COUNT(*) FROM penerimaan_barang_detail d WHERE d.pnid = pn.pnid) AS total_item
            FROM penerimaan_barang pn
            JOIN pesanan_pembelian pp ON pp.ppid = pn.ppid
            {$where}
            ORDER BY pn.tanggal DESC, pn.pnid DESC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $pnid): ?object
    {
        return DB::selectOne('
            SELECT pn.pnid, pn.nomor_penerimaan, pn.ppid, pp.nomor_po, sp.nama_supplier,
                pn.nomor_faktur_supplier, pn.tanggal, pn.catatan,
                pn.create_id, pn.create_time, pn.modify_id, pn.modify_time
            FROM penerimaan_barang pn
            JOIN pesanan_pembelian pp ON pp.ppid = pn.ppid
            JOIN supplier sp ON sp.spid = pp.spid
            WHERE pn.pnid = ?
        ', [$pnid]);
    }

    public function getItems(int $pnid): array
    {
        return DB::select('
            SELECT d.pndid, d.pnid, d.ppdid, d.mbid, mb.nama_barang,
                d.stid, s.nama_satuan, d.qty_diterima, d.harga_beli, d.subtotal
            FROM penerimaan_barang_detail d
            JOIN master_barang mb ON mb.mbid = d.mbid
            JOIN satuan s ON s.stid = d.stid
            WHERE d.pnid = ?
            ORDER BY d.pndid ASC
        ', [$pnid]);
    }

    public function generateNomorPenerimaan(): string
    {
        $prefix = 'GRN-' . now()->format('Ym') . '-';

        $last = DB::selectOne('
            SELECT nomor_penerimaan FROM penerimaan_barang
            WHERE nomor_penerimaan LIKE ?
            ORDER BY CAST(SUBSTRING(nomor_penerimaan, ?) AS UNSIGNED) DESC
            LIMIT 1
        ', ["{$prefix}%", strlen($prefix) + 1]);

        $nextNumber = $last ? ((int) substr($last->nomor_penerimaan, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function createHeader(array $header, ?int $adid): int
    {
        DB::insert('
            INSERT INTO penerimaan_barang (
                nomor_penerimaan, ppid, nomor_faktur_supplier, tanggal, catatan,
                create_id, create_time, modify_id, modify_time
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, NOW())
        ', [
            $header['nomor_penerimaan'],
            $header['ppid'],
            $header['nomor_faktur_supplier'] ?? null,
            $header['tanggal'],
            $header['catatan'] ?? null,
            $adid,
            $adid,
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function insertItem(int $pnid, array $item): int
    {
        $subtotal = $item['qty_diterima'] * $item['harga_beli'];

        DB::insert('
            INSERT INTO penerimaan_barang_detail (pnid, ppdid, mbid, stid, qty_diterima, harga_beli, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ', [
            $pnid,
            $item['ppdid'],
            $item['mbid'],
            $item['stid'],
            $item['qty_diterima'],
            $item['harga_beli'],
            $subtotal,
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }
}
