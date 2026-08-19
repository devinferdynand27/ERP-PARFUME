<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    /**
     * Mengambil seluruh data statistik untuk dashboard utama.
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        // 1. Ambil KPI Utama (Semua queries wajib menggunakan raw SQL & parameter binding)
        $totalProduk = DB::selectOne('SELECT COUNT(*) AS total FROM produk WHERE aktif = 1')->total;
        
        $totalStok = DB::selectOne('SELECT COALESCE(SUM(stok), 0) AS total FROM produk WHERE aktif = 1')->total;
        
        $barangMasukBulanIni = DB::selectOne('
            SELECT COALESCE(SUM(total_qty), 0) AS total 
            FROM barang_masuk 
            WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())
        ')->total;

        $barangKeluarBulanIni = DB::selectOne('
            SELECT COALESCE(SUM(total_qty), 0) AS total 
            FROM barang_keluar 
            WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())
        ')->total;

        $totalSupplier = DB::selectOne('SELECT COUNT(*) AS total FROM supplier WHERE aktif = 1')->total;
        
        $stokKritisCount = DB::selectOne('
            SELECT COUNT(*) AS total 
            FROM produk 
            WHERE aktif = 1 AND stok <= stok_minimum
        ')->total;

        // 2. Daftar produk dengan stok kritis (Alert)
        $lowStockProducts = DB::select('
            SELECT prid, kode_produk, nama_produk, stok, stok_minimum
            FROM produk
            WHERE aktif = 1 AND stok <= stok_minimum
            ORDER BY stok ASC
            LIMIT 5
        ');

        // 3. Aktivitas Transaksi Terbaru (Union Barang Masuk & Keluar)
        $recentActivities = DB::select('
            SELECT * FROM (
                SELECT \'masuk\' AS tipe, nomor_transaksi, tanggal, total_qty, total_harga, created_at
                FROM barang_masuk
                UNION ALL
                SELECT \'keluar\' AS tipe, nomor_transaksi, tanggal, total_qty, total_harga, created_at
                FROM barang_keluar
            ) AS gabungan
            ORDER BY created_at DESC, nomor_transaksi DESC
            LIMIT 5
        ');

        // 4. Volume Stok per Aroma Teraktif (Top 8)
        $topAromas = DB::select('
            SELECT a.nama_aroma, COALESCE(SUM(p.stok), 0) AS total_stok
            FROM produk p
            JOIN aroma a ON p.arid = a.arid
            WHERE p.aktif = 1 AND a.aktif = 1
            GROUP BY p.arid, a.nama_aroma
            ORDER BY total_stok DESC, a.nama_aroma ASC
            LIMIT 8
        ');

        return [
            'kpi' => [
                'total_produk' => (int) $totalProduk,
                'total_stok' => (int) $totalStok,
                'barang_masuk_bulan_ini' => (int) $barangMasukBulanIni,
                'barang_keluar_bulan_ini' => (int) $barangKeluarBulanIni,
                'total_supplier' => (int) $totalSupplier,
                'stok_kritis' => (int) $stokKritisCount,
            ],
            'low_stock_products' => $lowStockProducts,
            'recent_activities' => $recentActivities,
            'top_aromas' => $topAromas,
        ];
    }
}
