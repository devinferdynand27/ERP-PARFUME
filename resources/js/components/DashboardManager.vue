<script setup>
import { computed } from 'vue';
import {
    Package, Layers, AlertTriangle, ArrowDownLeft, ArrowUpRight,
    Truck, Activity, ArrowRightLeft, ShieldAlert
} from '@lucide/vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from '@/components/ui/table';

const props = defineProps({
    kpi: {
        type: Object,
        required: true,
        default: () => ({
            total_produk: 0,
            total_stok: 0,
            barang_masuk_bulan_ini: 0,
            barang_keluar_bulan_ini: 0,
            total_supplier: 0,
            stok_kritis: 0
        })
    },
    low_stock_products: {
        type: Array,
        required: true,
        default: () => []
    },
    recent_activities: {
        type: Array,
        required: true,
        default: () => []
    },
    top_aromas: {
        type: Array,
        required: true,
        default: () => []
    }
});

// Format Rupiah helper
function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

// Menghitung persentase bar untuk aroma berdasarkan stok terbanyak di top 8
const maxAromaStok = computed(() => {
    if (props.top_aromas.length === 0) return 1;
    return Math.max(...props.top_aromas.map(a => Number(a.total_stok)), 1);
});

// Format tanggal ke format lokal Indonesia
function formatTanggal(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).format(date);
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard Ringkasan</h1>
            <p class="text-sm text-muted-foreground">Monitor performa stok dan inventaris ERP CAVA Parfums</p>
        </div>

        <!-- Grid KPI Utama -->
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <!-- Total Produk -->
            <Card class="border-border bg-card shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <span class="text-sm font-medium text-muted-foreground">Total Produk</span>
                        <Package class="size-4 text-muted-foreground" />
                    </div>
                    <div class="text-2xl font-bold">{{ kpi.total_produk }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Produk aktif terdaftar</p>
                </CardContent>
            </Card>

            <!-- Total Stok -->
            <Card class="border-border bg-card shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <span class="text-sm font-medium text-muted-foreground">Total Volume Stok</span>
                        <Layers class="size-4 text-muted-foreground" />
                    </div>
                    <div class="text-2xl font-bold">{{ kpi.total_stok }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Pcs item di gudang</p>
                </CardContent>
            </Card>

            <!-- Stok Kritis (Highlight jika > 0) -->
            <Card class="border-border bg-card shadow-sm transition-all"
                  :class="kpi.stok_kritis > 0 ? 'border-destructive/40 bg-destructive/5' : ''">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <span class="text-sm font-medium" :class="kpi.stok_kritis > 0 ? 'text-destructive' : 'text-muted-foreground'">Stok Kritis</span>
                        <AlertTriangle class="size-4" :class="kpi.stok_kritis > 0 ? 'text-destructive animate-pulse' : 'text-muted-foreground'" />
                    </div>
                    <div class="text-2xl font-bold" :class="kpi.stok_kritis > 0 ? 'text-destructive' : ''">{{ kpi.stok_kritis }}</div>
                    <p class="text-xs mt-1" :class="kpi.stok_kritis > 0 ? 'text-destructive/80 font-medium' : 'text-muted-foreground'">
                        {{ kpi.stok_kritis > 0 ? 'Butuh restok segera!' : 'Semua stok aman' }}
                    </p>
                </CardContent>
            </Card>

            <!-- Transaksi Bulan Ini -->
            <Card class="border-border bg-card shadow-sm col-span-1 xl:col-span-1">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <span class="text-sm font-medium text-muted-foreground">Transaksi Bulan Ini</span>
                        <ArrowRightLeft class="size-4 text-muted-foreground" />
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <div>
                            <span class="text-xs text-muted-foreground block">Masuk</span>
                            <span class="text-base font-bold text-emerald-600">+{{ kpi.barang_masuk_bulan_ini }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-muted-foreground block">Keluar</span>
                            <span class="text-base font-bold text-blue-600">-{{ kpi.barang_keluar_bulan_ini }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Total Supplier -->
            <Card class="border-border bg-card shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <span class="text-sm font-medium text-muted-foreground">Supplier Aktif</span>
                        <Truck class="size-4 text-muted-foreground" />
                    </div>
                    <div class="text-2xl font-bold">{{ kpi.total_supplier }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Rekan penyuplai bahan</p>
                </CardContent>
            </Card>
        </div>

        <!-- Baris Kedua: Volume Stok & Alerts -->
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-12">
            <!-- Volume Stok per Aroma (Progress Bar Chart) -->
            <Card class="border-border bg-card shadow-sm lg:col-span-7">
                <CardHeader>
                    <CardTitle class="text-lg font-bold">Volume Stok per Aroma</CardTitle>
                    <CardDescription>Visualisasi persediaan untuk 8 jenis aroma teraktif</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="top_aromas.length === 0" class="text-center py-8 text-sm text-muted-foreground">
                        Belum ada data stok aroma.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="aroma in top_aromas" :key="aroma.nama_aroma" class="space-y-1.5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-foreground">{{ aroma.nama_aroma }}</span>
                                <span class="font-semibold text-muted-foreground">{{ aroma.total_stok }} pcs</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2.5 overflow-hidden">
                                <div class="bg-foreground h-full rounded-full transition-all duration-500"
                                     :style="{ width: `${(Number(aroma.total_stok) / maxAromaStok) * 100}%` }">
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Alerts Stok Kritis (Table) -->
            <Card class="border-border bg-card shadow-sm lg:col-span-5 flex flex-col">
                <CardHeader>
                    <CardTitle class="text-lg font-bold flex items-center gap-2">
                        <ShieldAlert class="size-5 text-destructive" />
                        Peringatan Stok Rendah
                    </CardTitle>
                    <CardDescription>Daftar produk dengan persediaan di bawah stok minimum</CardDescription>
                </CardHeader>
                <CardContent class="flex-1 overflow-auto">
                    <div v-if="low_stock_products.length === 0" class="flex flex-col items-center justify-center h-48 text-center text-sm text-muted-foreground">
                        <p class="font-medium text-emerald-600">Semua Persediaan Aman</p>
                        <p class="text-xs text-muted-foreground mt-1">Tidak ada produk yang berada di bawah stok minimum.</p>
                    </div>
                    <Table v-else>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="uppercase tracking-wide text-xs">Kode</TableHead>
                                <TableHead class="uppercase tracking-wide text-xs">Produk</TableHead>
                                <TableHead class="uppercase tracking-wide text-xs text-right">Stok / Min</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="prod in low_stock_products" :key="prod.prid">
                                <TableCell class="font-mono text-xs font-medium">{{ prod.kode_produk }}</TableCell>
                                <TableCell class="font-medium text-xs">{{ prod.nama_produk }}</TableCell>
                                <TableCell class="text-right text-xs font-semibold text-destructive">
                                    {{ prod.stok }} <span class="text-muted-foreground">/ {{ prod.stok_minimum }}</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Baris Ketiga: Aktivitas Transaksi Terbaru -->
        <Card class="border-border bg-card shadow-sm">
            <CardHeader class="flex flex-row items-center justify-between pb-4">
                <div>
                    <CardTitle class="text-lg font-bold flex items-center gap-2">
                        <Activity class="size-5 text-muted-foreground" />
                        Aktivitas Transaksi Terbaru
                    </CardTitle>
                    <CardDescription>Log transaksi masuk dan keluar barang inventaris terakhir</CardDescription>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="recent_activities.length === 0" class="text-center py-10 text-sm text-muted-foreground">
                    Belum ada riwayat transaksi barang masuk atau keluar.
                </div>
                <div v-else class="space-y-4">
                    <div v-for="act in recent_activities" :key="act.nomor_transaksi" 
                         class="flex items-center justify-between p-4 border border-border rounded-xl bg-card hover:bg-accent/10 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg p-2 flex items-center justify-center"
                                 :class="act.tipe === 'masuk' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'">
                                <ArrowDownLeft v-if="act.tipe === 'masuk'" class="size-5" />
                                <ArrowUpRight v-else class="size-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold">{{ act.nomor_transaksi }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                          :class="act.tipe === 'masuk' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'">
                                        {{ act.tipe === 'masuk' ? 'Barang Masuk' : 'Barang Keluar' }}
                                    </span>
                                </div>
                                <span class="text-xs text-muted-foreground">{{ formatTanggal(act.tanggal) }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold">{{ act.total_qty }} pcs</div>
                            <div class="text-xs text-muted-foreground">{{ formatRupiah(Number(act.total_harga)) }}</div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
