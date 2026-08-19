<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import {
    Pencil, Trash2, Search, Download, ChevronDown,
} from '@lucide/vue';
import { http } from '@/lib/http';
import { colorFor, initialOf } from '@/lib/avatar-color';
import { exportToExcel, exportToPdf, printTable } from '@/lib/export';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const EXPORT_COLUMNS = [
    { key: 'kode_produk', label: 'Kode Produk' },
    { key: 'nama_produk', label: 'Produk' },
    { key: 'nama_aroma', label: 'Aroma' },
    { key: 'harga_beli_default', label: 'Harga Modal' },
    { key: 'harga_jual_default', label: 'Harga Jual' },
    { key: 'stok', label: 'Stok' },
];

const props = defineProps({
    dataUrl: { type: String, required: true },
    formOptionsUrl: { type: String, required: true },
    storeUrl: { type: String, required: true },
    updateUrlTemplate: { type: String, required: true },
    toggleUrlTemplate: { type: String, required: true },
});

const items = ref([]);
const total = ref(0);
const page = ref(1);
const perPage = 10;
const search = ref('');
const filterArid = ref('all');
const loading = ref(false);
const errorMessage = ref('');

const aromaOptions = ref([]);

const dialogOpen = ref(false);
const editingPrid = ref(null);
const form = reactive({
    nama_produk: '',
    arid: null,
    harga_beli_default: '',
    harga_jual_default: '',
    stok: 0,
    stok_minimum: 0,
});
const formErrors = ref({});

function buildUrl(template, prid) {
    return template.replace('__prid__', prid);
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
        if (filterArid.value !== 'all') params.set('arid', filterArid.value);

        const result = await http.get(`${props.dataUrl}?${params.toString()}`);
        items.value = result.data;
        total.value = result.total;
    } catch (e) {
        errorMessage.value = 'Gagal memuat data produk.';
    } finally {
        loading.value = false;
    }
}

async function loadFormOptions() {
    const result = await http.get(props.formOptionsUrl);
    aromaOptions.value = result.aroma;
    return result;
}

async function openCreate() {
    editingPrid.value = null;
    form.nama_produk = '';
    form.arid = null;
    form.harga_beli_default = '';
    form.harga_jual_default = '';
    form.stok = 0;
    form.stok_minimum = 0;
    formErrors.value = {};
    await loadFormOptions();
    dialogOpen.value = true;
}

async function openEdit(item) {
    editingPrid.value = item.prid;
    form.nama_produk = item.nama_produk;
    form.arid = item.arid;
    form.harga_beli_default = item.harga_beli_default;
    form.harga_jual_default = item.harga_jual_default;
    form.stok_minimum = item.stok_minimum;
    formErrors.value = {};
    await loadFormOptions();
    dialogOpen.value = true;
}

async function submitForm() {
    formErrors.value = {};
    try {
        if (editingPrid.value) {
            await http.put(buildUrl(props.updateUrlTemplate, editingPrid.value), form);
        } else {
            await http.post(props.storeUrl, form);
        }
        dialogOpen.value = false;
        await loadData();
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = 'Gagal menyimpan data produk.';
        }
    }
}

async function nonaktifkan(item) {
    if (!confirm(`Nonaktifkan produk "${item.nama_produk}"?`)) return;
    try {
        await http.patch(buildUrl(props.toggleUrlTemplate, item.prid));
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal menonaktifkan produk.';
    }
}

async function loadAllForExport() {
    const params = new URLSearchParams({ page: 1, per_page: 1000000 });
    if (search.value) params.set('search', search.value);
    if (filterArid.value !== 'all') params.set('arid', filterArid.value);
    const result = await http.get(`${props.dataUrl}?${params.toString()}`);
    return result.data;
}

async function handleExportExcel() {
    try {
        exportToExcel(EXPORT_COLUMNS, await loadAllForExport(), 'master-produk');
    } catch (e) {
        errorMessage.value = 'Gagal export Excel.';
    }
}

async function handleExportPdf() {
    try {
        exportToPdf(EXPORT_COLUMNS, await loadAllForExport(), 'master-produk', 'Master Produk');
    } catch (e) {
        errorMessage.value = 'Gagal export PDF.';
    }
}

async function handlePrint() {
    try {
        printTable(EXPORT_COLUMNS, await loadAllForExport(), 'Master Produk');
    } catch (e) {
        errorMessage.value = 'Gagal mencetak data.';
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
watch(filterArid, () => {
    page.value = 1;
    loadData();
});

onMounted(() => {
    loadData();
    loadFormOptions();
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Master Produk</h1>
                <p class="text-sm text-muted-foreground">Kelola katalog produk parfum</p>
            </div>
            <div class="flex items-center gap-2">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            <Download class="mr-1 size-4" /> Export
                            <ChevronDown class="ml-1 size-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="handleExportExcel">Export Excel</DropdownMenuItem>
                        <DropdownMenuItem @click="handleExportPdf">Download PDF</DropdownMenuItem>
                        <DropdownMenuItem @click="handlePrint">Print</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button @click="openCreate">
                    <span class="mr-1 text-base leading-none">+</span> Tambah Produk
                </Button>
            </div>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="flex flex-1 items-center gap-3">
                    <div class="relative max-w-xs flex-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari produk..." class="pl-9" />
                    </div>
                    <Select v-model="filterArid">
                        <SelectTrigger class="w-48">
                            <SelectValue placeholder="Semua Aroma" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Aroma</SelectItem>
                            <SelectItem v-for="a in aromaOptions" :key="a.arid" :value="a.arid">
                                {{ a.nama_aroma }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <span class="shrink-0 text-sm text-muted-foreground">{{ total }} data</span>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="uppercase tracking-wide text-xs">Produk</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Aroma</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Harga Modal</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Harga Jual</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Stok</TableHead>
                        <TableHead class="text-right uppercase tracking-wide text-xs">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="6" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="6">
                        Belum ada data produk.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.prid">
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <Avatar class="size-9 rounded-lg">
                                    <AvatarFallback :class="['rounded-lg font-semibold', colorFor(item.nama_produk)]">
                                        {{ initialOf(item.nama_produk) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <div class="font-medium">{{ item.nama_produk }}</div>
                                    <div class="font-mono text-xs text-muted-foreground">{{ item.kode_produk }}</div>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>{{ item.nama_aroma }}</TableCell>
                        <TableCell class="font-mono">{{ formatRupiah(item.harga_beli_default) }}</TableCell>
                        <TableCell class="font-mono font-medium">{{ formatRupiah(item.harga_jual_default) }}</TableCell>
                        <TableCell>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="item.stok <= item.stok_minimum ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'"
                            >
                                <span class="size-1.5 rounded-full" :class="item.stok <= item.stok_minimum ? 'bg-rose-500' : 'bg-emerald-500'" />
                                {{ item.stok }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                    @click="openEdit(item)"
                                >
                                    <Pencil class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-md bg-destructive text-destructive-foreground transition-colors hover:bg-destructive/90"
                                    @click="nonaktifkan(item)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
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

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ editingPrid ? 'Edit Produk' : 'Tambah Produk' }}</DialogTitle>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="space-y-2">
                        <Label>Aroma</Label>
                        <Select v-model="form.arid">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih aroma" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="a in aromaOptions" :key="a.arid" :value="a.arid">
                                    {{ a.nama_aroma }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="formErrors.arid" class="text-sm text-destructive">{{ formErrors.arid[0] }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="nama_produk">Nama Produk</Label>
                        <Input id="nama_produk" v-model="form.nama_produk" placeholder="Nama produk" />
                        <p v-if="formErrors.nama_produk" class="text-sm text-destructive">
                            {{ formErrors.nama_produk[0] }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="harga_beli_default">Harga Modal</Label>
                            <Input id="harga_beli_default" v-model="form.harga_beli_default" type="number" min="0" />
                            <p v-if="formErrors.harga_beli_default" class="text-sm text-destructive">
                                {{ formErrors.harga_beli_default[0] }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="harga_jual_default">Harga Jual</Label>
                            <Input id="harga_jual_default" v-model="form.harga_jual_default" type="number" min="0" />
                            <p v-if="formErrors.harga_jual_default" class="text-sm text-destructive">
                                {{ formErrors.harga_jual_default[0] }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2" v-if="!editingPrid">
                            <Label for="stok">Stok Awal</Label>
                            <Input id="stok" v-model="form.stok" type="number" min="0" />
                        </div>
                        <div class="space-y-2">
                            <Label for="stok_minimum">Stok Minimum</Label>
                            <Input id="stok_minimum" v-model="form.stok_minimum" type="number" min="0" />
                            <p v-if="formErrors.stok_minimum" class="text-sm text-destructive">
                                {{ formErrors.stok_minimum[0] }}
                            </p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="submit">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
