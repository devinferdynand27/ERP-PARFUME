<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PesananPembelianRepository
{
    public function paginate(int $perPage, int $page, ?string $search = null, ?string $status = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $bindings = [];

        if ($search !== null && $search !== '') {
            $where .= ' AND (pp.nomor_po LIKE ? OR sp.nama_supplier LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        if ($status !== null && $status !== '') {
            $where .= ' AND pp.status = ?';
            $bindings[] = $status;
        }

        $total = DB::selectOne("
            SELECT COUNT(*) AS total FROM pesanan_pembelian pp
            JOIN supplier sp ON sp.spid = pp.spid
            {$where}
        ", $bindings)->total;

        $data = DB::select("
            SELECT pp.ppid, pp.nomor_po, pp.pbid, pp.spid, sp.nama_supplier,
                pp.tanggal, pp.status, pp.total_item, pp.total_qty, pp.total_harga
            FROM pesanan_pembelian pp
            JOIN supplier sp ON sp.spid = pp.spid
            {$where}
            ORDER BY pp.tanggal DESC, pp.ppid DESC
            LIMIT ? OFFSET ?
        ", [...$bindings, $perPage, $offset]);

        return ['data' => $data, 'total' => $total];
    }

    public function find(int $ppid): ?object
    {
        return DB::selectOne('
            SELECT pp.ppid, pp.nomor_po, pp.pbid, pb.nomor_permintaan, pp.spid, sp.nama_supplier,
                pp.tanggal, pp.status, pp.total_item, pp.total_qty, pp.total_harga, pp.catatan,
                pp.create_id, pp.create_time, pp.modify_id, pp.modify_time
            FROM pesanan_pembelian pp
            JOIN supplier sp ON sp.spid = pp.spid
            LEFT JOIN permintaan_barang pb ON pb.pbid = pp.pbid
            WHERE pp.ppid = ?
        ', [$ppid]);
    }

    public function getItems(int $ppid): array
    {
        return DB::select('
            SELECT d.ppdid, d.ppid, d.pbdid, d.mbid, mb.nama_barang,
                d.stid, s.nama_satuan, d.qty_dipesan, d.qty_diterima, d.harga_satuan, d.subtotal
            FROM pesanan_pembelian_detail d
            JOIN master_barang mb ON mb.mbid = d.mbid
            JOIN satuan s ON s.stid = d.stid
            WHERE d.ppid = ?
            ORDER BY d.ppdid ASC
        ', [$ppid]);
    }

    /**
     * Detail PO yang masih punya sisa qty untuk diterima (qty_dipesan >
     * qty_diterima) — dipakai form Penerimaan Barang.
     */
    public function getItemsWithSisa(int $ppid): array
    {
        return DB::select('
            SELECT d.ppdid, d.mbid, mb.nama_barang,
                d.stid, s.nama_satuan, d.qty_dipesan, d.qty_diterima, d.harga_satuan,
                (d.qty_dipesan - d.qty_diterima) AS sisa
            FROM pesanan_pembelian_detail d
            JOIN master_barang mb ON mb.mbid = d.mbid
            JOIN satuan s ON s.stid = d.stid
            WHERE d.ppid = ? AND d.qty_dipesan > d.qty_diterima
            ORDER BY d.ppdid ASC
        ', [$ppid]);
    }

    /**
     * Generate nomor PO format PO-{DDMMYYYY}-{0001}, berdasarkan nomor
     * tertinggi hari berjalan (bukan COUNT, aman dari gap data terhapus).
     */
    public function generateNomorPO(): string
    {
        $prefix = 'PO-' . now()->format('dmY') . '-';

        $last = DB::selectOne('
            SELECT nomor_po FROM pesanan_pembelian
            WHERE nomor_po LIKE ?
            ORDER BY CAST(SUBSTRING(nomor_po, ?) AS UNSIGNED) DESC
            LIMIT 1
        ', ["{$prefix}%", strlen($prefix) + 1]);

        $nextNumber = $last ? ((int) substr($last->nomor_po, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $header, array $items, ?int $adid): int
    {
        $totalItem = count($items);
        $totalQty = array_sum(array_column($items, 'qty_dipesan'));
        $totalHarga = 0;
        foreach ($items as $item) {
            $totalHarga += $item['qty_dipesan'] * $item['harga_satuan'];
        }

        DB::insert('
            INSERT INTO pesanan_pembelian (
                nomor_po, pbid, spid, tanggal, status,
                total_item, total_qty, total_harga, catatan,
                create_id, create_time, modify_id, modify_time
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())
        ', [
            $header['nomor_po'],
            $header['pbid'] ?? null,
            $header['spid'],
            $header['tanggal'],
            $header['status'] ?? 'draft',
            $totalItem,
            $totalQty,
            $totalHarga,
            $header['catatan'] ?? null,
            $adid,
            $adid,
        ]);

        $ppid = (int) DB::getPdo()->lastInsertId();

        $this->insertItems($ppid, $items);

        return $ppid;
    }

    private function insertItems(int $ppid, array $items): void
    {
        foreach ($items as $item) {
            $subtotal = $item['qty_dipesan'] * $item['harga_satuan'];

            DB::insert('
                INSERT INTO pesanan_pembelian_detail (ppid, pbdid, mbid, stid, qty_dipesan, harga_satuan, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ', [
                $ppid,
                $item['pbdid'] ?? null,
                $item['mbid'],
                $item['stid'],
                $item['qty_dipesan'],
                $item['harga_satuan'],
                $subtotal,
            ]);
        }
    }

    /**
     * Row lock header PO, dipakai update() untuk cek ulang status di dalam
     * transaction sebelum menerapkan perubahan (pola sama seperti
     * getDetailForUpdate()) — cegah race dengan penerimaan barang yang
     * berjalan bersamaan.
     */
    public function findForUpdate(int $ppid): ?object
    {
        return DB::selectOne('
            SELECT ppid, status FROM pesanan_pembelian WHERE ppid = ? FOR UPDATE
        ', [$ppid]);
    }

    /**
     * Hanya boleh dipanggil saat status masih 'diterbitkan' (dicek di
     * controller & findForUpdate) — status itu berarti belum ada
     * qty_diterima sama sekali (lihat recomputeStatus()), jadi replace
     * seluruh baris detail aman, tidak ada FK penerimaan_barang_detail yang
     * menunjuk ppdid lama.
     */
    public function update(int $ppid, array $header, array $items, ?int $adid): bool
    {
        $totalItem = count($items);
        $totalQty = array_sum(array_column($items, 'qty_dipesan'));
        $totalHarga = 0;
        foreach ($items as $item) {
            $totalHarga += $item['qty_dipesan'] * $item['harga_satuan'];
        }

        $updated = DB::update('
            UPDATE pesanan_pembelian
            SET spid = ?, tanggal = ?, total_item = ?, total_qty = ?, total_harga = ?, catatan = ?,
                modify_id = ?, modify_time = NOW()
            WHERE ppid = ?
        ', [
            $header['spid'],
            $header['tanggal'],
            $totalItem,
            $totalQty,
            $totalHarga,
            $header['catatan'] ?? null,
            $adid,
            $ppid,
        ]) > 0;

        DB::delete('DELETE FROM pesanan_pembelian_detail WHERE ppid = ?', [$ppid]);
        $this->insertItems($ppid, $items);

        return $updated;
    }

    public function updateStatus(int $ppid, string $status, ?int $adid): bool
    {
        return DB::update('
            UPDATE pesanan_pembelian
            SET status = ?, modify_id = ?, modify_time = NOW()
            WHERE ppid = ?
        ', [$status, $adid, $ppid]) > 0;
    }

    public function addQtyDiterima(int $ppdid, float $qty): bool
    {
        return DB::update('
            UPDATE pesanan_pembelian_detail SET qty_diterima = qty_diterima + ? WHERE ppdid = ?
        ', [$qty, $ppdid]) > 0;
    }

    public function getDetailForUpdate(int $ppdid): ?object
    {
        return DB::selectOne('
            SELECT ppdid, ppid, mbid, qty_dipesan, qty_diterima
            FROM pesanan_pembelian_detail
            WHERE ppdid = ?
            FOR UPDATE
        ', [$ppdid]);
    }

    /**
     * Hitung ulang status PO berdasarkan agregat qty_diterima vs qty_dipesan
     * seluruh baris detail, dipanggil setelah penerimaan barang disimpan.
     */
    public function recomputeStatus(int $ppid, ?int $adid): void
    {
        $agg = DB::selectOne('
            SELECT SUM(qty_dipesan) AS total_dipesan, SUM(qty_diterima) AS total_diterima
            FROM pesanan_pembelian_detail
            WHERE ppid = ?
        ', [$ppid]);

        $status = 'diterbitkan';
        if ($agg->total_diterima > 0 && $agg->total_diterima < $agg->total_dipesan) {
            $status = 'diterima_sebagian';
        } elseif ($agg->total_diterima >= $agg->total_dipesan) {
            $status = 'diterima_penuh';
        }

        $this->updateStatus($ppid, $status, $adid);
    }
}
