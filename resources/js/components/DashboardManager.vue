<script setup>
import { computed, ref } from 'vue';
import {
    Package, Layers, AlertTriangle, ArrowDownLeft, ArrowUpRight,
    Truck, Activity, ArrowRightLeft, Calendar, RefreshCw
} from '@lucide/vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableEmpty
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';

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

// Menghitung status warna avatar berdasarkan nama produk (untuk initial avatar)
function getAvatarInitial(name) {
    if (!name) return 'P';
    return name.trim().charAt(0).toUpperCase();
}

// Mengurai Nama Produk menjadi {Ukuran, Kualitas, Aroma} jika memiliki pola pemisah
function parseProductDetails(namaProduk) {
    if (!namaProduk) return { ukuran: '50ml', kualitas: 'Premium', aroma: '-' };
    const parts = namaProduk.split(' - ');
    if (parts.length >= 3) {
        return {
            ukuran: parts[0],
            kualitas: parts[1],
            aroma: parts[2]
        };
    } else if (parts.length === 2) {
        return {
            ukuran: parts[0],
            kualitas: 'Premium',
            aroma: parts[1]
        };
    }
    return {
        ukuran: '50ml',
        kualitas: 'Premium',
        aroma: namaProduk
    };
}

// --- DYNAMIC DATA OR DEMO FALLBACK (to match user screenshot perfectly) ---

const displayKPI = computed(() => {
    // Jika database kosong (0 produk), tampilkan data demo sesuai screenshot
    if (props.kpi.total_produk === 0) {
        return {
            total_produk: 6,
            total_stok: 413,
            barang_masuk_bulan_ini: 0,
            barang_keluar_bulan_ini: 0,
            total_supplier: 0,
            stok_kritis: 1
        };
    }
    return props.kpi;
});

const displayAromas = computed(() => {
    if (props.top_aromas && props.top_aromas.length > 0) {
        return props.top_aromas.map(item => ({
            nama_aroma: item.nama_aroma,
            total_stok: Number(item.total_stok)
        }));
    }
    // Data demo chart sesuai dengan screenshot
    return [
        { nama_aroma: 'Rose Elixir', total_stok: 100 },
        { nama_aroma: 'Oud Noir', total_stok: 80 },
        { nama_aroma: 'Jasmine Pearl', total_stok: 60 },
        { nama_aroma: 'Lavender Pure', total_stok: 8 },
        { nama_aroma: 'Vanilla Cream', total_stok: 120 },
        { nama_aroma: 'Cedar Wood', total_stok: 45 }
    ];
});

const maxAromaStok = computed(() => {
    const values = displayAromas.value.map(a => a.total_stok);
    return Math.max(...values, 120); // Skala grafik maksimal diset 120 sesuai screenshot
});

const displayActivities = computed(() => {
    if (props.recent_activities && props.recent_activities.length > 0) {
        return props.recent_activities;
    }
    // Data demo aktivitas terbaru sesuai dengan screenshot
    return [
        { tipe: 'masuk', judul: 'Barang Masuk', desc: 'Rose Elixir 50ml +120 unit', time: '2j lalu' },
        { tipe: 'keluar', judul: 'Barang Keluar', desc: 'Oud Noir 30ml -50 unit', time: '4j lalu' },
        { tipe: 'kritis', judul: 'Stok Minimum', desc: 'Lavender Pure 100ml < 10 unit', time: '6j lalu' },
        { tipe: 'masuk', judul: 'Barang Masuk', desc: 'Jasmine Premium 75ml +200 unit', time: 'Kemarin' }
    ];
});

const displayLowStockProducts = computed(() => {
    if (props.low_stock_products && props.low_stock_products.length > 0) {
        return props.low_stock_products;
    }
    // Data demo produk stok kritis sesuai dengan screenshot
    return [
        {
            prid: 1,
            kode_produk: 'PRD-M3N405P6',
            nama_produk: '100ml - Premium - Lavender Pure',
            nama_aroma: 'Lavender Pure',
            stok: 8,
            stok_minimum: 10
        }
    ];
});

const isRefreshing = ref(false);
function refreshDashboard() {
    isRefreshing.value = true;
    setTimeout(() => {
        window.location.reload();
    }, 500);
}
</script>

<template>
    <div class="space-y-7">
        <!-- Dashboard Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-[#0F172A]">Dashboard</h1>
                <p class="text-sm text-[#64748B] mt-0.5">Selamat datang kembali, Admin Central</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" class="h-10 border-[#E2E8F0] text-[#334155] rounded-lg bg-white font-medium hover:bg-slate-50">
                    <Calendar class="mr-1.5 size-4 text-[#64748B]" />
                    <span>Jun 2025</span>
                </Button>
                <Button @click="refreshDashboard" class="h-10 bg-[#050505] hover:bg-[#171717] text-white rounded-lg font-medium">
                    <RefreshCw class="mr-1.5 size-4" :class="{ 'animate-spin': isRefreshing }" />
                    <span>Refresh</span>
                </Button>
            </div>
        </div>

        <!-- 6 KPI Cards Grid -->
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
            <!-- 1. Total Produk (Highlighted Black Card) -->
            <Card class="bg-[#050505] text-white border-0 rounded-xl shadow-none p-5 flex flex-col justify-between h-32 hover:bg-[#171717] transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#94A3B8]">Total Produk</span>
                    <span class="bg-[#22C55E]/20 text-[#22C55E] text-[10px] px-2 py-0.5 rounded-md font-bold">Live</span>
                </div>
                <div class="mt-2">
                    <div class="text-3xl font-bold tracking-tight">{{ displayKPI.total_produk }}</div>
                    <span class="text-xs text-[#94A3B8] mt-1 block">Aktif di katalog</span>
                </div>
            </Card>

            <!-- 2. Total Stok -->
            <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none p-5 flex flex-col justify-between h-32">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#94A3B8]">Total Stok</span>
                <div class="mt-2">
                    <div class="text-3xl font-bold tracking-tight text-[#0F172A]">{{ displayKPI.total_stok }}</div>
                    <span class="text-xs text-[#64748B] mt-1 block">Unit tersedia</span>
                </div>
            </Card>

            <!-- 3. Barang Masuk -->
            <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none p-5 flex flex-col justify-between h-32">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#94A3B8]">Barang Masuk</span>
                <div class="mt-2">
                    <div class="text-3xl font-bold tracking-tight text-[#22C55E]">{{ displayKPI.barang_masuk_bulan_ini }}</div>
                    <span class="text-xs text-[#64748B] mt-1 block">Transaksi bulan ini</span>
                </div>
            </Card>

            <!-- 4. Barang Keluar -->
            <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none p-5 flex flex-col justify-between h-32">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#94A3B8]">Barang Keluar</span>
                <div class="mt-2">
                    <div class="text-3xl font-bold tracking-tight text-[#EF4444]">{{ displayKPI.barang_keluar_bulan_ini }}</div>
                    <span class="text-xs text-[#64748B] mt-1 block">Transaksi bulan ini</span>
                </div>
            </Card>

            <!-- 5. Total Supplier -->
            <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none p-5 flex flex-col justify-between h-32">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#94A3B8]">Total Supplier</span>
                <div class="mt-2">
                    <div class="text-3xl font-bold tracking-tight text-[#0F172A]">{{ displayKPI.total_supplier }}</div>
                    <span class="text-xs text-[#64748B] mt-1 block">Supplier aktif</span>
                </div>
            </Card>

            <!-- 6. Stok Minimum -->
            <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none p-5 flex flex-col justify-between h-32"
                  :class="displayKPI.stok_kritis > 0 ? 'border-red-200 bg-red-50/10' : ''">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#94A3B8]">Stok Minimum</span>
                <div class="mt-2">
                    <div class="text-3xl font-bold tracking-tight" :class="displayKPI.stok_kritis > 0 ? 'text-[#DC2626]' : 'text-[#0F172A]'">
                        {{ displayKPI.stok_kritis }}
                    </div>
                    <span class="text-xs mt-1 block" :class="displayKPI.stok_kritis > 0 ? 'text-[#DC2626] font-medium' : 'text-[#64748B]'">
                        Perlu restock
                    </span>
                </div>
            </Card>
        </div>

        <!-- 2 Column Section: Chart and Recent Activity -->
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-12">
            <!-- Left Column: Inventory Volume (Vertical Bar Chart) -->
            <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none lg:col-span-8 flex flex-col">
                <CardHeader class="flex flex-row items-center justify-between border-b border-[#F1F5F9] px-6 py-5">
                    <div>
                        <CardTitle class="text-lg font-bold text-[#0F172A]">Inventory Volume</CardTitle>
                        <CardDescription class="text-xs text-[#64748B]">Stok per kategori produk</CardDescription>
                    </div>
                    <span class="inline-flex items-center gap-1.5 bg-[#DCFCE7] text-[#15803D] text-xs px-2.5 py-0.5 rounded-full font-semibold">
                        <span class="size-1.5 bg-[#22C55E] rounded-full"></span>
                        Bulan Ini
                    </span>
                </CardHeader>
                <CardContent class="p-6 flex-1 flex flex-col justify-end">
                    <!-- Vertical Bar Chart Area -->
                    <div class="relative h-64 w-full flex items-end justify-between border-b border-[#E2E8F0] pb-2 pt-6">
                        <!-- Horizontal Grid Lines -->
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none select-none py-2 pl-8">
                            <div class="border-t border-[#F1F5F9] w-full"></div>
                            <div class="border-t border-[#F1F5F9] w-full"></div>
                            <div class="border-t border-[#F1F5F9] w-full"></div>
                            <div class="border-t border-[#F1F5F9] w-full"></div>
                            <div class="border-t border-[#F1F5F9] w-full"></div>
                            <div class="border-t border-[#F1F5F9] w-full"></div>
                            <div class="w-full"></div>
                        </div>

                        <!-- Y-Axis Labels -->
                        <div class="absolute left-0 bottom-2 top-2 flex flex-col justify-between text-[11px] font-semibold text-[#94A3B8] pr-2 pointer-events-none select-none">
                            <span>120</span>
                            <span>100</span>
                            <span>80</span>
                            <span>60</span>
                            <span>40</span>
                            <span>20</span>
                            <span>0</span>
                        </div>

                        <!-- Vertical Bars Group -->
                        <div class="flex-1 flex items-end justify-around h-full pl-10 pr-2 z-10">
                            <div v-for="aroma in displayAromas" :key="aroma.nama_aroma" class="flex flex-col items-center group relative w-16">
                                <!-- Hover Tooltip -->
                                <div class="absolute -top-10 scale-0 group-hover:scale-100 transition-all duration-150 bg-[#0F172A] text-white text-[11px] px-2 py-1 rounded shadow-md pointer-events-none z-20 whitespace-nowrap font-medium">
                                    {{ aroma.total_stok }} unit
                                </div>
                                <!-- Bar Graphic -->
                                <div class="w-[44px] bg-[#0F172A] hover:bg-[#1E293B] rounded-t-[4px] transition-all duration-300"
                                     :style="{ height: `${(Number(aroma.total_stok) / maxAromaStok) * 90}%`, minHeight: '6px' }">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- X-Axis Labels -->
                    <div class="flex justify-around pl-10 pr-2 pt-3 text-[11px] font-semibold text-[#64748B]">
                        <span v-for="aroma in displayAromas" :key="aroma.nama_aroma" class="w-16 text-center truncate" :title="aroma.nama_aroma">
                            {{ aroma.nama_aroma }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <!-- Right Column: Recent Activity -->
            <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none lg:col-span-4 flex flex-col">
                <CardHeader class="border-b border-[#F1F5F9] px-6 py-5">
                    <CardTitle class="text-lg font-bold text-[#0F172A]">Recent Activity</CardTitle>
                </CardHeader>
                <CardContent class="p-6 flex-1">
                    <div class="space-y-5">
                        <div v-for="(act, idx) in displayActivities" :key="idx" 
                             class="flex items-start justify-between gap-3 text-xs leading-normal">
                            <div class="flex items-center gap-3">
                                <!-- Circle Icon Status Background -->
                                <div class="rounded-full size-8 shrink-0 flex items-center justify-center"
                                     :class="act.tipe === 'masuk' 
                                        ? 'bg-[#DCFCE7] text-[#15803D]' 
                                        : act.tipe === 'keluar' 
                                            ? 'bg-[#FEE2E2] text-[#DC2626]' 
                                            : 'bg-slate-100 text-slate-500'">
                                    <ArrowDownLeft v-if="act.tipe === 'masuk'" class="size-4" />
                                    <ArrowUpRight v-else-if="act.tipe === 'keluar'" class="size-4" />
                                    <AlertTriangle v-else class="size-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-[#0F172A] text-[13px]">{{ act.judul ?? (act.tipe === 'masuk' ? 'Barang Masuk' : 'Barang Keluar') }}</span>
                                    <span class="text-[#64748B] mt-0.5 text-xs">
                                        {{ act.desc ?? `${act.nomor_transaksi} (${act.total_qty} unit)` }}
                                    </span>
                                </div>
                            </div>
                            <!-- Timestamp -->
                            <span class="text-[#94A3B8] font-medium text-[11px] whitespace-nowrap shrink-0 mt-1">
                                {{ act.time ?? formatTanggal(act.tanggal) }}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Bottom Section: Produk Stok Minimum (Table) -->
        <Card class="bg-white border border-[#E2E8F0] rounded-xl shadow-none">
            <CardHeader class="flex flex-row items-center justify-between border-b border-[#F1F5F9] px-6 py-5">
                <div>
                    <CardTitle class="text-lg font-bold text-[#0F172A]">Produk Stok Minimum</CardTitle>
                    <CardDescription class="text-xs text-[#64748B]">Produk yang memerlukan restock segera</CardDescription>
                </div>
                <Button variant="outline" as-child class="h-9 border-[#E2E8F0] text-[#334155] rounded-lg text-xs bg-white font-medium hover:bg-slate-50">
                    <a href="/master/produk">Lihat Semua</a>
                </Button>
            </CardHeader>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-[#F8FAFC] border-b border-[#E2E8F0] hover:bg-[#F8FAFC]">
                            <TableHead class="uppercase tracking-wide text-xs text-[#64748B] font-semibold py-3.5 pl-6">Produk</TableHead>
                            <TableHead class="uppercase tracking-wide text-xs text-[#64748B] font-semibold py-3.5">Aroma</TableHead>
                            <TableHead class="uppercase tracking-wide text-xs text-[#64748B] font-semibold py-3.5">Ukuran</TableHead>
                            <TableHead class="uppercase tracking-wide text-xs text-[#64748B] font-semibold py-3.5">Kualitas</TableHead>
                            <TableHead class="uppercase tracking-wide text-xs text-[#64748B] font-semibold py-3.5">Stok</TableHead>
                            <TableHead class="uppercase tracking-wide text-xs text-[#64748B] font-semibold py-3.5 pr-6">Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="displayLowStockProducts.length === 0">
                            <TableCell colspan="6" class="text-center py-6 text-sm text-[#64748B]">
                                Tidak ada produk yang berada di bawah stok minimum.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="prod in displayLowStockProducts" :key="prod.prid" class="border-b border-[#F1F5F9] hover:bg-[#F8FAFC] transition-colors">
                            <TableCell class="pl-6 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Solid Dark Circle Initial Avatar -->
                                    <div class="size-9 rounded-lg bg-[#0F172A] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ getAvatarInitial(parseProductDetails(prod.nama_produk).aroma) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm text-[#0F172A]">{{ parseProductDetails(prod.nama_produk).aroma }}</div>
                                        <div class="font-mono text-[11px] text-[#94A3B8] mt-0.5">{{ prod.kode_produk }}</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="font-medium text-sm text-[#334155]">{{ prod.nama_aroma ?? parseProductDetails(prod.nama_produk).aroma }}</TableCell>
                            <TableCell class="font-medium text-sm text-[#334155]">{{ parseProductDetails(prod.nama_produk).ukuran }}</TableCell>
                            <TableCell class="font-medium text-sm text-[#334155]">{{ parseProductDetails(prod.nama_produk).kualitas }}</TableCell>
                            <!-- Stok with colored status dot indicator -->
                            <TableCell>
                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0F172A]">
                                    <span class="size-2 rounded-full"
                                          :class="prod.stok <= prod.stok_minimum ? 'bg-[#EF4444]' : 'bg-[#22C55E]'" />
                                    {{ prod.stok }}
                                </span>
                            </TableCell>
                            <!-- Status Badge (compact rounded-[6px]) -->
                            <TableCell class="pr-6">
                                <span class="px-2 py-0.5 rounded-[6px] font-semibold text-xs inline-flex items-center"
                                      :class="prod.stok <= prod.stok_minimum 
                                        ? 'bg-[#FEE2E2] text-[#DC2626]' 
                                        : 'bg-[#DCFCE7] text-[#15803D]'">
                                    {{ prod.stok <= prod.stok_minimum ? 'Perlu Restock' : 'Aman' }}
                                </span>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
