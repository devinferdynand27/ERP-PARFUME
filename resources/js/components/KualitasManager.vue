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
    { key: 'nama_kualitas', label: 'Kualitas' },
    { key: 'keterangan', label: 'Keterangan' },
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
const editingKuid = ref(null);
const form = reactive({ nama_kualitas: '', keterangan: '' });
const formErrors = ref({});

function buildUrl(template, kuid) {
    return template.replace('__kuid__', kuid);
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
        errorMessage.value = 'Gagal memuat data kualitas.';
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editingKuid.value = null;
    form.nama_kualitas = '';
    form.keterangan = '';
    formErrors.value = {};
    dialogOpen.value = true;
}

function openEdit(item) {
    editingKuid.value = item.kuid;
    form.nama_kualitas = item.nama_kualitas;
    form.keterangan = item.keterangan;
    formErrors.value = {};
    dialogOpen.value = true;
}

async function submitForm() {
    formErrors.value = {};
    try {
        if (editingKuid.value) {
            await http.put(buildUrl(props.updateUrlTemplate, editingKuid.value), form);
        } else {
            await http.post(props.storeUrl, form);
        }
        dialogOpen.value = false;
        await loadData();
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = 'Gagal menyimpan data kualitas.';
        }
    }
}

async function toggleAktif(item) {
    const aksi = item.aktif ? 'Nonaktifkan' : 'Aktifkan';
    if (!confirm(`${aksi} kualitas "${item.nama_kualitas}"?`)) return;
    try {
        await http.patch(buildUrl(props.toggleUrlTemplate, item.kuid));
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal mengubah status kualitas.';
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
        exportToExcel(EXPORT_COLUMNS, rows, 'master-kualitas');
    } catch (e) {
        errorMessage.value = 'Gagal export Excel.';
    }
}

async function handleExportPdf() {
    try {
        const rows = await loadAllForExport();
        exportToPdf(EXPORT_COLUMNS, rows, 'master-kualitas', 'Master Kualitas');
    } catch (e) {
        errorMessage.value = 'Gagal export PDF.';
    }
}

async function handlePrint() {
    try {
        const rows = await loadAllForExport();
        printTable(EXPORT_COLUMNS, rows, 'Master Kualitas');
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
                <h1 class="text-2xl font-bold tracking-tight">Master Kualitas</h1>
                <p class="text-sm text-muted-foreground">Kelola data kualitas produk</p>
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
                    <span class="mr-1 text-base leading-none">+</span> Tambah Kualitas
                </Button>
            </div>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="relative max-w-xs flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Cari kualitas..." class="pl-9" />
                </div>
                <span class="shrink-0 text-sm text-muted-foreground">{{ total }} data</span>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="uppercase tracking-wide text-xs">Kualitas</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Keterangan</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Status</TableHead>
                        <TableHead class="text-right uppercase tracking-wide text-xs">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="4" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="4">
                        Belum ada data kualitas.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.kuid">
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <Avatar class="size-9 rounded-lg">
                                    <AvatarFallback :class="['rounded-lg font-semibold', colorFor(item.nama_kualitas)]">
                                        {{ initialOf(item.nama_kualitas) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="font-medium">{{ item.nama_kualitas }}</span>
                            </div>
                        </TableCell>
                        <TableCell>{{ item.keterangan || '-' }}</TableCell>
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
                    <DialogTitle>{{ editingKuid ? 'Edit Kualitas' : 'Tambah Kualitas' }}</DialogTitle>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="space-y-2">
                        <Label for="nama_kualitas">Kualitas</Label>
                        <Input id="nama_kualitas" v-model="form.nama_kualitas" placeholder="Premium, Standar, Ekonomis" />
                        <p v-if="formErrors.nama_kualitas" class="text-sm text-destructive">
                            {{ formErrors.nama_kualitas[0] }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="keterangan">Keterangan</Label>
                        <Input id="keterangan" v-model="form.keterangan" placeholder="Opsional" />
                        <p v-if="formErrors.keterangan" class="text-sm text-destructive">
                            {{ formErrors.keterangan[0] }}
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
