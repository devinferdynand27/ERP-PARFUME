<script setup>
import { ref, watch, onMounted } from 'vue';
import {
    Pencil, Search, Plus, Printer, Trash2,
} from '@lucide/vue';
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
    diajukan: 'Diajukan',
    disetujui: 'Disetujui',
    ditolak: 'Ditolak',
    ditutup: 'Ditutup',
};
const STATUS_BADGE = {
    draft: 'bg-slate-100 text-slate-700',
    diajukan: 'bg-amber-50 text-amber-700',
    disetujui: 'bg-emerald-50 text-emerald-700',
    ditolak: 'bg-rose-50 text-rose-700',
    ditutup: 'bg-slate-100 text-slate-500',
};

const props = defineProps({
    dataUrl: { type: String, required: true },
    createUrl: { type: String, required: true },
    editUrlTemplate: { type: String, required: true },
    statusUrlTemplate: { type: String, required: true },
    printUrlTemplate: { type: String, required: true },
    deleteUrlTemplate: { type: String, required: true },
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
        errorMessage.value = 'Gagal memuat data permintaan barang.';
    } finally {
        loading.value = false;
    }
}

async function ubahStatus(item, status) {
    const label = STATUS_LABEL[status] ?? status;
    if (!confirm(`Ubah status "${item.nomor_permintaan}" menjadi "${label}"?`)) return;
    try {
        await http.patch(buildUrl(props.statusUrlTemplate, item.pbid), { status });
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal mengubah status.';
    }
}

async function hapus(item) {
    if (!confirm(`Hapus permintaan "${item.nomor_permintaan}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    try {
        await http.delete(buildUrl(props.deleteUrlTemplate, item.pbid));
        if (items.value.length === 1 && page.value > 1) page.value--;
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal menghapus permintaan barang.';
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
                <h1 class="text-2xl font-bold tracking-tight">Permintaan Barang</h1>
                <p class="text-sm text-muted-foreground">Ajukan kebutuhan barang untuk dibelikan ke supplier</p>
            </div>
            <a :href="createUrl">
                <Button>
                    <Plus class="mr-1 size-4" /> Buat Permintaan
                </Button>
            </a>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="flex flex-1 items-center gap-3">
                    <div class="relative max-w-xs flex-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nomor permintaan..." class="pl-9" />
                    </div>
                    <Select v-model="filterStatus">
                        <SelectTrigger class="w-44">
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
                        <TableHead class="uppercase tracking-wide text-xs">Nomor</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Tanggal</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Item</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Status</TableHead>
                        <TableHead class="text-right uppercase tracking-wide text-xs">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="5" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="5">
                        Belum ada permintaan barang.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.pbid">
                        <TableCell class="font-mono font-medium">{{ item.nomor_permintaan }}</TableCell>
                        <TableCell>{{ item.tanggal }}</TableCell>
                        <TableCell>{{ item.total_item }} item</TableCell>
                        <TableCell>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" :class="STATUS_BADGE[item.status]">
                                {{ STATUS_LABEL[item.status] }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <a
                                    :href="buildUrl(printUrlTemplate, item.pbid)"
                                    target="_blank"
                                    class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                    title="Cetak"
                                >
                                    <Printer class="size-4" />
                                </a>
                                <a
                                    v-if="item.status === 'draft'"
                                    :href="buildUrl(editUrlTemplate, item.pbid)"
                                    class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                    title="Edit"
                                >
                                    <Pencil class="size-4" />
                                </a>
                                <button
                                    v-if="item.status === 'draft'"
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-destructive"
                                    title="Hapus"
                                    @click="hapus(item)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                                <Button v-if="item.status === 'draft'" size="sm" variant="outline" @click="ubahStatus(item, 'diajukan')">Ajukan</Button>
                                <Button v-if="item.status === 'diajukan'" size="sm" variant="outline" @click="ubahStatus(item, 'disetujui')">Setujui</Button>
                                <Button v-if="item.status === 'diajukan'" size="sm" variant="outline" class="text-destructive" @click="ubahStatus(item, 'ditolak')">Tolak</Button>
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
