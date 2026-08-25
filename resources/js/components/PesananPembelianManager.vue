<script setup>
import { ref, watch, onMounted } from 'vue';
import { Search, Plus, Printer, Pencil } from '@lucide/vue';
import { http } from '@/lib/http';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';

const STATUS_LABEL = {
    draft: 'Draft',
    diterbitkan: 'Diterbitkan',
    diterima_sebagian: 'Diterima Sebagian',
    diterima_penuh: 'Diterima Penuh',
    dibatalkan: 'Dibatalkan',
};
const STATUS_BADGE = {
    draft: 'bg-slate-100 text-slate-700',
    diterbitkan: 'bg-amber-50 text-amber-700',
    diterima_sebagian: 'bg-sky-50 text-sky-700',
    diterima_penuh: 'bg-emerald-50 text-emerald-700',
    dibatalkan: 'bg-rose-50 text-rose-700',
};

const props = defineProps({
    dataUrl: { type: String, required: true },
    createUrl: { type: String, required: true },
    statusUrlTemplate: { type: String, required: true },
    printUrlTemplate: { type: String, required: true },
    editUrlTemplate: { type: String, required: true },
});

const items = ref([]);
const total = ref(0);
const page = ref(1);
const perPage = 10;
const search = ref('');
const filterStatus = ref('all');
const loading = ref(false);
const errorMessage = ref('');

function buildUrl(template, id) {
    return template.replace(/__\w+__/, id);
}

function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

async function loadData() {
    loading.value = true;
    errorMessage.value = '';
    try {
        const params = new URLSearchParams({ page: page.value, per_page: perPage });
        if (search.value) params.set('search', search.value);
        if (filterStatus.value !== 'all') params.set('status', filterStatus.value);

        const result = await http.get(`${props.dataUrl}?${params.toString()}`);
        items.value = result.data;
        total.value = result.total;
    } catch (e) {
        errorMessage.value = 'Gagal memuat data pesanan pembelian.';
    } finally {
        loading.value = false;
    }
}

async function batalkan(item) {
    if (!confirm(`Batalkan PO "${item.nomor_po}"?`)) return;
    try {
        await http.patch(buildUrl(props.statusUrlTemplate, item.ppid), { status: 'dibatalkan' });
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal membatalkan PO.';
    }
}

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        page.value = 1;
        loadData();
    }, 300);
});

watch(page, loadData);
watch(filterStatus, () => {
    page.value = 1;
    loadData();
});

onMounted(() => {
    loadData();
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Pesanan Pembelian</h1>
                <p class="text-sm text-muted-foreground">Terbitkan pesanan pembelian ke supplier</p>
            </div>
            <a :href="createUrl">
                <Button>
                    <Plus class="mr-1 size-4" /> Buat PO
                </Button>
            </a>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="flex flex-1 items-center gap-3">
                    <div class="relative max-w-xs flex-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nomor PO / supplier..." class="pl-9" />
                    </div>
                    <Select v-model="filterStatus">
                        <SelectTrigger class="w-48">
                            <SelectValue placeholder="Semua Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Status</SelectItem>
                            <SelectItem v-for="(label, key) in STATUS_LABEL" :key="key" :value="key">{{ label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <span class="shrink-0 text-sm text-muted-foreground">{{ total }} data</span>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="uppercase tracking-wide text-xs">Nomor PO</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Supplier</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Tanggal</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Total</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Status</TableHead>
                        <TableHead class="text-right uppercase tracking-wide text-xs">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="6" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="6">
                        Belum ada pesanan pembelian.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.ppid">
                        <TableCell class="font-mono font-medium">{{ item.nomor_po }}</TableCell>
                        <TableCell>{{ item.nama_supplier }}</TableCell>
                        <TableCell>{{ item.tanggal }}</TableCell>
                        <TableCell class="font-mono">{{ formatRupiah(item.total_harga) }}</TableCell>
                        <TableCell>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" :class="STATUS_BADGE[item.status]">
                                {{ STATUS_LABEL[item.status] }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <a
                                    v-if="item.status === 'diterbitkan'"
                                    :href="buildUrl(editUrlTemplate, item.ppid)"
                                    class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                    title="Edit"
                                >
                                    <Pencil class="size-4" />
                                </a>
                                <a
                                    :href="buildUrl(printUrlTemplate, item.ppid)"
                                    target="_blank"
                                    class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                    title="Cetak"
                                >
                                    <Printer class="size-4" />
                                </a>
                                <Button
                                    v-if="item.status === 'diterbitkan'"
                                    size="sm" variant="outline" class="text-destructive"
                                    @click="batalkan(item)"
                                >
                                    Batalkan
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div class="flex items-center justify-between border-t border-border p-4 text-sm text-muted-foreground">
                <span>Menampilkan {{ items.length }} dari {{ total }}</span>
                <div class="space-x-2">
                    <Button variant="outline" size="sm" :disabled="page <= 1" @click="page--">‹</Button>
                    <span class="px-2">{{ page }}</span>
                    <Button variant="outline" size="sm" :disabled="page * perPage >= total" @click="page++">›</Button>
                </div>
            </div>
        </div>
    </div>
</template>
