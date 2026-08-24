<script setup>
import { ref, watch, onMounted } from 'vue';
import { Search, Plus, Printer } from '@lucide/vue';
import { http } from '@/lib/http';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

const props = defineProps({
    dataUrl: { type: String, required: true },
    createUrl: { type: String, required: true },
    printUrlTemplate: { type: String, required: true },
});

function buildUrl(template, id) {
    return template.replace(/__\w+__/, id);
}

const items = ref([]);
const total = ref(0);
const page = ref(1);
const perPage = 10;
const search = ref('');
const loading = ref(false);
const errorMessage = ref('');

async function loadData() {
    loading.value = true;
    errorMessage.value = '';
    try {
        const params = new URLSearchParams({ page: page.value, per_page: perPage });
        if (search.value) params.set('search', search.value);

        const result = await http.get(`${props.dataUrl}?${params.toString()}`);
        items.value = result.data;
        total.value = result.total;
    } catch (e) {
        errorMessage.value = 'Gagal memuat data penerimaan barang.';
    } finally {
        loading.value = false;
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

onMounted(() => {
    loadData();
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Penerimaan Barang</h1>
                <p class="text-sm text-muted-foreground">Catat penerimaan fisik barang dari pesanan pembelian — stok bertambah otomatis</p>
            </div>
            <a :href="createUrl">
                <Button>
                    <Plus class="mr-1 size-4" /> Terima Barang
                </Button>
            </a>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="relative max-w-xs flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Cari nomor GRN / PO / faktur..." class="pl-9" />
                </div>
                <span class="shrink-0 text-sm text-muted-foreground">{{ total }} data</span>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="uppercase tracking-wide text-xs">Nomor Penerimaan</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">PO</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">No. Faktur</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Tanggal</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Item</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="6" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="6">
                        Belum ada penerimaan barang.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.pnid">
                        <TableCell class="font-mono font-medium">{{ item.nomor_penerimaan }}</TableCell>
                        <TableCell class="font-mono">{{ item.nomor_po }}</TableCell>
                        <TableCell>{{ item.nomor_faktur_supplier ?? '-' }}</TableCell>
                        <TableCell>{{ item.tanggal }}</TableCell>
                        <TableCell>{{ item.total_item }} item</TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <a
                                    :href="buildUrl(printUrlTemplate, item.pnid)"
                                    target="_blank"
                                    class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                    title="Cetak"
                                >
                                    <Printer class="size-4" />
                                </a>
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
