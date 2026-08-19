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
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const EXPORT_COLUMNS = [
    { key: 'nama_barang', label: 'Barang' },
    { key: 'kategori', label: 'Kategori' },
];

const props = defineProps({
    dataUrl: { type: String, required: true },
    storeUrl: { type: String, required: true },
    updateUrlTemplate: { type: String, required: true },
    toggleUrlTemplate: { type: String, required: true },
});

const items = ref([]);
const total = ref(0);
const page = ref(1);
const perPage = 10;
const search = ref('');
const loading = ref(false);
const errorMessage = ref('');

const dialogOpen = ref(false);
const editingMbid = ref(null);
const form = reactive({ nama_barang: '', kategori: '' });
const formErrors = ref({});

function buildUrl(template, mbid) {
    return template.replace('__mbid__', mbid);
}

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
        errorMessage.value = 'Gagal memuat data master barang.';
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editingMbid.value = null;
    form.nama_barang = '';
    form.kategori = '';
    formErrors.value = {};
    dialogOpen.value = true;
}

function openEdit(item) {
    editingMbid.value = item.mbid;
    form.nama_barang = item.nama_barang;
    form.kategori = item.kategori;
    formErrors.value = {};
    dialogOpen.value = true;
}

async function submitForm() {
    formErrors.value = {};
    try {
        if (editingMbid.value) {
            await http.put(buildUrl(props.updateUrlTemplate, editingMbid.value), form);
        } else {
            await http.post(props.storeUrl, form);
        }
        dialogOpen.value = false;
        await loadData();
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = 'Gagal menyimpan data master barang.';
        }
    }
}

async function nonaktifkan(item) {
    if (!confirm(`Nonaktifkan barang "${item.nama_barang}"?`)) return;
    try {
        await http.patch(buildUrl(props.toggleUrlTemplate, item.mbid));
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal menonaktifkan barang.';
    }
}

async function loadAllForExport() {
    const params = new URLSearchParams({ page: 1, per_page: 1000000 });
    if (search.value) params.set('search', search.value);
    const result = await http.get(`${props.dataUrl}?${params.toString()}`);
    return result.data;
}

async function handleExportExcel() {
    try {
        exportToExcel(EXPORT_COLUMNS, await loadAllForExport(), 'master-barang');
    } catch (e) {
        errorMessage.value = 'Gagal export Excel.';
    }
}

async function handleExportPdf() {
    try {
        exportToPdf(EXPORT_COLUMNS, await loadAllForExport(), 'master-barang', 'Master Barang');
    } catch (e) {
        errorMessage.value = 'Gagal export PDF.';
    }
}

async function handlePrint() {
    try {
        printTable(EXPORT_COLUMNS, await loadAllForExport(), 'Master Barang');
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

onMounted(loadData);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Master Barang</h1>
                <p class="text-sm text-muted-foreground">Kelola data master barang</p>
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
                    <span class="mr-1 text-base leading-none">+</span> Tambah Barang
                </Button>
            </div>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="relative max-w-xs flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Cari nama barang atau kategori..." class="pl-9" />
                </div>
                <span class="shrink-0 text-sm text-muted-foreground">{{ total }} data</span>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="uppercase tracking-wide text-xs">Barang</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Kategori</TableHead>
                        <TableHead class="text-right uppercase tracking-wide text-xs">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="3" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="3">
                        Belum ada data master barang.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.mbid">
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <Avatar class="size-9 rounded-lg">
                                    <AvatarFallback :class="['rounded-lg font-semibold', colorFor(item.nama_barang)]">
                                        {{ initialOf(item.nama_barang) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="font-medium">{{ item.nama_barang }}</span>
                            </div>
                        </TableCell>
                        <TableCell>{{ item.kategori }}</TableCell>
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
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingMbid ? 'Edit Barang' : 'Tambah Barang' }}</DialogTitle>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="space-y-2">
                        <Label for="nama_barang">Nama Barang</Label>
                        <Input id="nama_barang" v-model="form.nama_barang" />
                        <p v-if="formErrors.nama_barang" class="text-sm text-destructive">
                            {{ formErrors.nama_barang[0] }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="kategori">Kategori</Label>
                        <Input id="kategori" v-model="form.kategori" placeholder="Floral, Woody, Oriental, Fresh, Sweet" />
                        <p v-if="formErrors.kategori" class="text-sm text-destructive">
                            {{ formErrors.kategori[0] }}
                        </p>
                    </div>
                    <DialogFooter>
                        <Button type="submit">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
