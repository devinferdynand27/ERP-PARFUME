<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { ArrowLeft } from '@lucide/vue';
import { http } from '@/lib/http';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';

const props = defineProps({
    formOptionsUrl: { type: String, required: true },
    storeUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
});

const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');

const poOptions = ref([]);
const selectedPpid = ref(null);
const sisaItems = ref([]);

const form = reactive({
    nomor_faktur_supplier: '',
    tanggal: new Date().toISOString().slice(0, 16).replace('T', ' '),
    catatan: '',
});
const qtyInput = reactive({});
const formErrors = ref({});

async function load() {
    loading.value = true;
    const result = await http.get(props.formOptionsUrl);
    poOptions.value = result.pesanan_pembelian;
    loading.value = false;
}

watch(selectedPpid, async (ppid) => {
    sisaItems.value = [];
    Object.keys(qtyInput).forEach((k) => delete qtyInput[k]);
    if (!ppid) return;

    const result = await http.get(`${props.formOptionsUrl}?ppid=${ppid}`);
    sisaItems.value = result.items;
    result.items.forEach((i) => { qtyInput[i.ppdid] = ''; });
});

async function submitForm() {
    formErrors.value = {};
    const formItems = sisaItems.value
        .filter((i) => Number(qtyInput[i.ppdid]) > 0)
        .map((i) => ({ ppdid: i.ppdid, qty_diterima: Number(qtyInput[i.ppdid]) }));

    if (formItems.length === 0) {
        errorMessage.value = 'Isi minimal satu qty yang diterima.';
        return;
    }

    saving.value = true;
    try {
        await http.post(props.storeUrl, {
            ppid: selectedPpid.value,
            nomor_faktur_supplier: form.nomor_faktur_supplier || null,
            tanggal: form.tanggal,
            catatan: form.catatan || null,
            items: formItems,
        });
        window.location.href = props.indexUrl;
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = e.body?.message ?? 'Gagal menyimpan penerimaan barang.';
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
                <h1 class="text-2xl font-bold tracking-tight">Terima Barang</h1>
                <p class="text-sm text-muted-foreground">Catat penerimaan fisik barang dari pesanan pembelian — stok bertambah otomatis</p>
            </div>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div v-if="loading" class="rounded-xl border border-border bg-card p-8 text-center text-sm text-muted-foreground shadow-sm">
            Memuat...
        </div>

        <form v-else class="space-y-6 rounded-xl border border-border bg-card p-6 shadow-sm" @submit.prevent="submitForm">
            <div class="space-y-2">
                <Label>Pesanan Pembelian</Label>
                <Select v-model="selectedPpid">
                    <SelectTrigger><SelectValue placeholder="Pilih PO yang belum diterima penuh" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="po in poOptions" :key="po.ppid" :value="po.ppid">
                            {{ po.nomor_po }} — {{ po.nama_supplier }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="formErrors.ppid" class="text-sm text-destructive">{{ formErrors.ppid[0] }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="nomor_faktur_supplier">No. Faktur Supplier</Label>
                    <Input id="nomor_faktur_supplier" v-model="form.nomor_faktur_supplier" placeholder="Opsional" />
                </div>
                <div class="space-y-2">
                    <Label for="tanggal">Tanggal Terima</Label>
                    <Input id="tanggal" v-model="form.tanggal" type="datetime-local" />
                </div>
            </div>

            <div v-if="selectedPpid" class="space-y-2">
                <Label>Item yang Diterima</Label>
                <p v-if="formErrors.items" class="text-sm text-destructive">{{ formErrors.items[0] }}</p>
                <div class="rounded-lg border border-border">
                    <div v-for="row in sisaItems" :key="row.ppdid" class="grid grid-cols-[1fr_100px_120px] items-center gap-2 border-b border-border p-2 last:border-b-0">
                        <div>
                            <div class="text-sm font-medium">{{ row.nama_produk }}</div>
                            <div class="text-xs text-muted-foreground">Sisa: {{ row.sisa }} {{ row.nama_satuan }}</div>
                        </div>
                        <Input v-model="qtyInput[row.ppdid]" type="number" min="0" :max="row.sisa" step="0.01" placeholder="Qty" />
                        <span class="text-xs text-muted-foreground">{{ row.nama_satuan }}</span>
                    </div>
                    <p v-if="sisaItems.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                        Semua item PO ini sudah diterima penuh.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a :href="indexUrl">
                    <Button type="button" variant="outline">Batal</Button>
                </a>
                <Button type="submit" :disabled="!selectedPpid || sisaItems.length === 0 || saving">Simpan Penerimaan</Button>
            </div>
        </form>
    </div>
</template>
