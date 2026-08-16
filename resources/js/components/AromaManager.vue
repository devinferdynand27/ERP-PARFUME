<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { Pencil, Trash2, Search } from '@lucide/vue';
import { http } from '@/lib/http';
import { colorFor, initialOf } from '@/lib/avatar-color';
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
const editingArid = ref(null);
const form = reactive({ nama_aroma: '', kategori: '' });
const formErrors = ref({});

function buildUrl(template, arid) {
    return template.replace('__arid__', arid);
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
        errorMessage.value = 'Gagal memuat data aroma.';
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editingArid.value = null;
    form.nama_aroma = '';
    form.kategori = '';
    formErrors.value = {};
    dialogOpen.value = true;
}

function openEdit(item) {
    editingArid.value = item.arid;
    form.nama_aroma = item.nama_aroma;
    form.kategori = item.kategori;
    formErrors.value = {};
    dialogOpen.value = true;
}

async function submitForm() {
    formErrors.value = {};
    try {
        if (editingArid.value) {
            await http.put(buildUrl(props.updateUrlTemplate, editingArid.value), form);
        } else {
            await http.post(props.storeUrl, form);
        }
        dialogOpen.value = false;
        await loadData();
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = 'Gagal menyimpan data aroma.';
        }
    }
}

async function nonaktifkan(item) {
    if (!confirm(`Nonaktifkan aroma "${item.nama_aroma}"?`)) return;
    try {
        await http.patch(buildUrl(props.toggleUrlTemplate, item.arid));
        await loadData();
    } catch (e) {
        errorMessage.value = e.body?.message ?? 'Gagal menonaktifkan aroma.';
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
                <h1 class="text-2xl font-bold tracking-tight">Master Aroma</h1>
                <p class="text-sm text-muted-foreground">Kelola varian aroma parfum</p>
            </div>
            <Button @click="openCreate">
                <span class="mr-1 text-base leading-none">+</span> Tambah Aroma
            </Button>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-border p-4">
                <div class="relative max-w-xs flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Cari nama aroma atau kategori..." class="pl-9" />
                </div>
                <span class="shrink-0 text-sm text-muted-foreground">{{ total }} data</span>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="uppercase tracking-wide text-xs">Aroma</TableHead>
                        <TableHead class="uppercase tracking-wide text-xs">Kategori</TableHead>
                        <TableHead class="text-right uppercase tracking-wide text-xs">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="3" class="text-center text-muted-foreground">Memuat...</TableCell>
                    </TableRow>
                    <TableEmpty v-else-if="items.length === 0" :colspan="3">
                        Belum ada data aroma.
                    </TableEmpty>
                    <TableRow v-for="item in items" :key="item.arid">
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <Avatar class="size-9 rounded-lg">
                                    <AvatarFallback :class="['rounded-lg font-semibold', colorFor(item.nama_aroma)]">
                                        {{ initialOf(item.nama_aroma) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="font-medium">{{ item.nama_aroma }}</span>
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
                    <DialogTitle>{{ editingArid ? 'Edit Aroma' : 'Tambah Aroma' }}</DialogTitle>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="space-y-2">
                        <Label for="nama_aroma">Nama Aroma</Label>
                        <Input id="nama_aroma" v-model="form.nama_aroma" />
                        <p v-if="formErrors.nama_aroma" class="text-sm text-destructive">
                            {{ formErrors.nama_aroma[0] }}
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
