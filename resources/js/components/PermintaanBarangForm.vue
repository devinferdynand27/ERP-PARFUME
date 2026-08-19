<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Trash2, ArrowLeft } from '@lucide/vue';
import { http } from '@/lib/http';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Combobox } from '@/components/ui/combobox';

const props = defineProps({
    pbid: { type: [Number, String], default: null },
    formOptionsUrl: { type: String, required: true },
    showUrl: { type: String, default: null },
    storeUrl: { type: String, default: null },
    updateUrl: { type: String, default: null },
    indexUrl: { type: String, required: true },
});

const isEdit = !!props.pbid;

const aromaOptions = ref([]);
const satuanOptions = ref([]);
const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');

const form = reactive({
    tanggal: new Date().toISOString().slice(0, 10),
    catatan: '',
});
const formItems = ref([]);
const formErrors = ref({});

function addRow() {
    formItems.value.push({ arid: null, stid: null, qty_diminta: 1 });
}

function removeRow(index) {
    formItems.value.splice(index, 1);
}

async function load() {
    loading.value = true;
    const options = await http.get(props.formOptionsUrl);
    aromaOptions.value = options.aroma;
    satuanOptions.value = options.satuan;

    if (isEdit) {
        const detail = await http.get(props.showUrl);
        form.tanggal = detail.header.tanggal;
        form.catatan = detail.header.catatan ?? '';
        formItems.value = detail.items.map((i) => ({
            arid: i.arid, stid: i.stid, qty_diminta: Number(i.qty_diminta),
        }));
    } else {
        formItems.value = [];
        addRow();
    }
    loading.value = false;
}

async function submitForm() {
    formErrors.value = {};
    saving.value = true;
    const payload = { ...form, items: formItems.value };
    try {
        if (isEdit) {
            await http.put(props.updateUrl, payload);
        } else {
            await http.post(props.storeUrl, payload);
        }
        window.location.href = props.indexUrl;
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = e.body?.message ?? 'Gagal menyimpan permintaan barang.';
        }
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    load();
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a :href="indexUrl" class="flex size-8 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
                <ArrowLeft class="size-4" />
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ isEdit ? 'Edit Permintaan Barang' : 'Buat Permintaan Barang' }}</h1>
                <p class="text-sm text-muted-foreground">Ajukan kebutuhan barang untuk dibelikan ke supplier</p>
            </div>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div v-if="loading" class="rounded-xl border border-border bg-card p-8 text-center text-sm text-muted-foreground shadow-sm">
            Memuat...
        </div>

        <form v-else class="space-y-6 rounded-xl border border-border bg-card p-6 shadow-sm" @submit.prevent="submitForm">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="tanggal">Tanggal</Label>
                    <Input id="tanggal" v-model="form.tanggal" type="date" />
                    <p v-if="formErrors.tanggal" class="text-sm text-destructive">{{ formErrors.tanggal[0] }}</p>
                </div>
                <div class="space-y-2">
                    <Label for="catatan">Catatan</Label>
                    <Input id="catatan" v-model="form.catatan" placeholder="Opsional" />
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label>Item Barang</Label>
                    <Button type="button" size="sm" variant="outline" @click="addRow">
                        <Plus class="mr-1 size-4" /> Tambah Item
                    </Button>
                </div>
                <p v-if="formErrors.items" class="text-sm text-destructive">{{ formErrors.items[0] }}</p>

                <div class="overflow-hidden rounded-lg border border-border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-black text-left text-xs font-medium text-white">
                                <th class="p-2 font-medium">Aroma</th>
                                <th class="w-[140px] p-2 font-medium">Satuan</th>
                                <th class="w-[120px] p-2 font-medium">Qty</th>
                                <th class="w-9 p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in formItems" :key="index" class="border-b border-border last:border-b-0">
                                <td class="p-2 align-top">
                                    <Combobox
                                        v-model="row.arid"
                                        :options="aromaOptions"
                                        option-value="arid"
                                        option-label="nama_aroma"
                                        placeholder="Pilih aroma"
                                        empty-text="Aroma tidak ditemukan."
                                    />
                                </td>
                                <td class="p-2 align-top">
                                    <Select v-model="row.stid">
                                        <SelectTrigger><SelectValue placeholder="Satuan" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="s in satuanOptions" :key="s.stid" :value="s.stid">{{ s.nama_satuan }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </td>
                                <td class="p-2 align-top">
                                    <Input v-model="row.qty_diminta" type="number" min="0.01" step="0.01" placeholder="Qty" />
                                </td>
                                <td class="p-2 align-top">
                                    <button type="button" class="flex size-9 items-center justify-center text-muted-foreground hover:text-destructive" @click="removeRow(index)">
                                        <Trash2 class="size-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="formItems.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                        Belum ada item, klik "Tambah Item".
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a :href="indexUrl">
                    <Button type="button" variant="outline">Batal</Button>
                </a>
                <Button type="submit" :disabled="formItems.length === 0 || saving">Simpan</Button>
            </div>
        </form>
    </div>
</template>
