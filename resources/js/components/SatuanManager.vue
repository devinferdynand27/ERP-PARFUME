<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import {
    Pencil, Check, Minus, Search, Download, ChevronDown,
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
    { key: 'nama_satuan', label: 'Satuan' },
    { key: 'tipe', label: 'Tipe' },
    { key: 'isi', label: 'Isi' },
    { key: 'status', label: 'Status' },
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
const editingStid = ref(null);
const form = reactive({ nama_satuan: '', tipe: '', isi: '' });
const formErrors = ref({});

function buildUrl(template, stid) {
    return template.replace('__stid__', stid);
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
        errorMessage.value = 'Gagal memuat data satuan.';
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editingStid.value = null;
    form.nama_satuan = '';
    form.tipe = '';
    form.isi = '';
    formErrors.value = {};
    dialogOpen.value = true;
}

function openEdit(item) {
    editingStid.value = item.stid;
    form.nama_satuan = item.nama_satuan;
    form.tipe = item.tipe;
    form.isi = item.isi;
    formErrors.value = {};
    dialogOpen.value = true;
}

async function submitForm() {
    formErrors.value = {};
    try {
        if (editingStid.value) {
            await http.put(buildUrl(props.updateUrlTemplate, editingStid.value), form);
        } else {
            await http.post(props.storeUrl, form);
        }
        dialogOpen.value = false;
        await loadData();
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = 'Gagal menyimpan data satuan.';
        }
    }
}

async function toggleAktif(item) {
    const aksi = item.aktif ? 'Nonaktifkan' : 'Aktifkan';
    if (!confirm(`${aksi} satuan "${item.nama_satuan}"?`)) return;
    try {
        await http.patch(buildUrl(props.toggleUrlTemplate, item.stid));
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal mengubah status satuan.';
    }
}

async function loadAllForExport() {
    const params = new URLSearchParams({ page: 1, per_page: 1000000 });
    if (search.value) params.set('search', search.value);

    const result = await http.get(`${props.dataUrl}?${params.toString()}`);
    return result.data.map((item) => ({
        ...item,
        status: item.aktif ? 'Aktif' : 'Nonaktif',
    }));
}

async function handleExportExcel() {
    try {
        const rows = await loadAllForExport();
        exportToExcel(EXPORT_COLUMNS, rows, 'master-satuan');
    } catch (e) {
        errorMessage.value = 'Gagal export Excel.';
    }
}

async function handleExportPdf() {
    try {
        const rows = await loadAllForExport();
        exportToPdf(EXPORT_COLUMNS, rows, 'master-satuan', 'Master Satuan');
    } catch (e) {
        errorMessage.value = 'Gagal export PDF.';
    }
}

async function handlePrint() {
    try {
        const rows = await loadAllForExport();
        printTable(EXPORT_COLUMNS, rows, 'Master Satuan');
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
                <h1 class="text-2xl font-bold tracking-tight">Master Satuan</h1>
                <p class="text-sm text-muted-foreground">Kelola satuan, tipe, dan isi</p>
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
                    <span class="mr-1 text-base leading-none">+</span> Tambah Satuan
                </Button>
            </div>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="relative max-w-xs flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Cari satuan..." class="pl-9" />
                </div>
                <span class="shrink-0 text-sm text-muted-foreground">{{ total }} data</span>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="uppercase tracking-wide text-xs">Satuan</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Tipe</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Isi</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Status</TableHead>
                        <TableHead class="text-right uppercase tracking-wide text-xs">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="5" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="5">
                        Belum ada data satuan.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.stid">
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <Avatar class="size-9 rounded-lg">
                                    <AvatarFallback :class="['rounded-lg font-semibold', colorFor(item.nama_satuan)]">
                                        {{ initialOf(item.nama_satuan) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="font-medium">{{ item.nama_satuan }}</span>
                            </div>
                        </TableCell>
                        <TableCell>{{ item.tipe }}</TableCell>
                        <TableCell>{{ item.isi }}</TableCell>
                        <TableCell>
                            <span class="inline-flex items-center gap-1.5 text-sm">
                                <span
                                    class="size-2 rounded-full"
                                    :class="item.aktif ? 'bg-emerald-500' : 'bg-muted-foreground/40'"
                                ></span>
                                {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
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
                                    class="flex size-8 items-center justify-center rounded-md transition-colors"
                                    :class="item.aktif
                                        ? 'bg-emerald-600 text-white hover:bg-emerald-600/90'
                                        : 'bg-destructive text-destructive-foreground hover:bg-destructive/90'"
                                    :title="item.aktif ? 'Nonaktifkan' : 'Aktifkan'"
                                    @click="toggleAktif(item)"
                                >
                                    <Check v-if="item.aktif" class="size-4" />
                                    <Minus v-else class="size-4" />
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
                    <DialogTitle>{{ editingStid ? 'Edit Satuan' : 'Tambah Satuan' }}</DialogTitle>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="space-y-2">
                        <Label for="nama_satuan">Satuan</Label>
                        <Input id="nama_satuan" v-model="form.nama_satuan" placeholder="pcs, box, liter" />
                        <p v-if="formErrors.nama_satuan" class="text-sm text-destructive">
                            {{ formErrors.nama_satuan[0] }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="tipe">Tipe</Label>
                        <Input id="tipe" v-model="form.tipe" placeholder="berat, volume, jumlah" />
                        <p v-if="formErrors.tipe" class="text-sm text-destructive">
                            {{ formErrors.tipe[0] }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="isi">Isi</Label>
                        <Input id="isi" v-model="form.isi" type="number" step="0.01" min="0" placeholder="0" />
                        <p v-if="formErrors.isi" class="text-sm text-destructive">
                            {{ formErrors.isi[0] }}
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
