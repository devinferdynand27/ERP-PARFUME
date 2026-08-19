<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { Plus, Trash2, ArrowLeft } from '@lucide/vue';
import { http } from '@/lib/http';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';

const props = defineProps({
    formOptionsUrl: { type: String, required: true },
    dariPermintaanUrlTemplate: { type: String, required: true },
    storeUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    permintaanBarangDataUrl: { type: String, required: true },
});

const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');

const masterBarangOptions = ref([]);
const satuanOptions = ref([]);
const supplierOptions = ref([]);
const permintaanDisetujui = ref([]);

const selectedPbid = ref('none');
const form = reactive({
    spid: null,
    tanggal: new Date().toISOString().slice(0, 10),
    catatan: '',
});
const formItems = ref([]);
const formErrors = ref({});

function buildUrl(template, id) {
    return template.replace(/__\w+__/, id);
}

function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

const previewTotal = computed(() => formItems.value.reduce(
    (sum, row) => sum + (Number(row.qty_dipesan) || 0) * (Number(row.harga_satuan) || 0),
    0,
));

function addRow() {
    formItems.value.push({
        pbdid: null, mbid: null, stid: null, qty_dipesan: 1, harga_satuan: 0,
    });
}

function removeRow(index) {
    formItems.value.splice(index, 1);
}

async function load() {
    loading.value = true;
    const [options, prResult] = await Promise.all([
        http.get(props.formOptionsUrl),
        http.get(`${props.permintaanBarangDataUrl}?status=disetujui&per_page=1000`),
    ]);
    masterBarangOptions.value = options.master_barang;
    satuanOptions.value = options.satuan;
    supplierOptions.value = options.supplier;
    permintaanDisetujui.value = prResult.data;
    addRow();
    loading.value = false;
}

watch(selectedPbid, async (pbid) => {
    if (pbid === 'none') return;
    const result = await http.get(buildUrl(props.dariPermintaanUrlTemplate, pbid));
    formItems.value = result.items.map((i) => ({
        pbdid: i.pbdid,
        mbid: i.mbid,
        stid: i.stid,
        qty_dipesan: Number(i.qty_diminta),
        harga_satuan: 0,
    }));
});

async function submitForm() {
    formErrors.value = {};
    saving.value = true;
    const payload = {
        ...form,
        pbid: selectedPbid.value === 'none' ? null : selectedPbid.value,
        items: formItems.value,
    };
    try {
        await http.post(props.storeUrl, payload);
        window.location.href = props.indexUrl;
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = e.body?.message ?? 'Gagal menyimpan pesanan pembelian.';
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
                <h1 class="text-2xl font-bold tracking-tight">Buat Pesanan Pembelian</h1>
                <p class="text-sm text-muted-foreground">Terbitkan pesanan pembelian ke supplier</p>
            </div>
        </div>

        <p v-if="errorMessage" class="text-sm text-destructive">{{ errorMessage }}</p>

        <div v-if="loading" class="rounded-xl border border-border bg-card p-8 text-center text-sm text-muted-foreground shadow-sm">
            Memuat...
        </div>

        <form v-else class="space-y-6 rounded-xl border border-border bg-card p-6 shadow-sm" @submit.prevent="submitForm">
            <div class="grid grid-cols-3 gap-4">
                <div class="space-y-2">
                    <Label>Dari Permintaan Barang</Label>
                    <Select v-model="selectedPbid">
                        <SelectTrigger><SelectValue placeholder="Tanpa PR" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">Tanpa PR (langsung)</SelectItem>
                            <SelectItem v-for="pr in permintaanDisetujui" :key="pr.pbid" :value="pr.pbid">
                                {{ pr.nomor_permintaan }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <Label>Supplier</Label>
                    <Select v-model="form.spid">
                        <SelectTrigger><SelectValue placeholder="Pilih supplier" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in supplierOptions" :key="s.spid" :value="s.spid">{{ s.nama_supplier }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="formErrors.spid" class="text-sm text-destructive">{{ formErrors.spid[0] }}</p>
                </div>
                <div class="space-y-2">
                    <Label for="tanggal">Tanggal</Label>
                    <Input id="tanggal" v-model="form.tanggal" type="date" />
                </div>
            </div>

            <div class="space-y-2">
                <Label for="catatan">Catatan</Label>
                <Input id="catatan" v-model="form.catatan" placeholder="Opsional" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label>Item Barang</Label>
                    <Button type="button" size="sm" variant="outline" @click="addRow" :disabled="selectedPbid !== 'none'">
                        <Plus class="mr-1 size-4" /> Tambah Item
                    </Button>
                </div>
                <p v-if="formErrors.items" class="text-sm text-destructive">{{ formErrors.items[0] }}</p>

                <div class="overflow-hidden rounded-lg border border-border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50 text-left text-xs font-medium text-foreground">
                                <th class="p-2 font-medium">Barang</th>
                                <th class="w-[110px] p-2 font-medium">Satuan</th>
                                <th class="w-[110px] p-2 font-medium">Qty</th>
                                <th class="w-[150px] p-2 font-medium">Harga Satuan</th>
                                <th class="w-9 p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in formItems" :key="index" class="border-b border-border last:border-b-0">
                                <td class="p-2 align-top">
                                    <Select v-model="row.mbid" :disabled="selectedPbid !== 'none'">
                                        <SelectTrigger><SelectValue placeholder="Pilih barang" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="a in masterBarangOptions" :key="a.mbid" :value="a.mbid">{{ a.nama_barang }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </td>
                                <td class="p-2 align-top">
                                    <Select v-model="row.stid" :disabled="selectedPbid !== 'none'">
                                        <SelectTrigger><SelectValue placeholder="Satuan" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="s in satuanOptions" :key="s.stid" :value="s.stid">{{ s.nama_satuan }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </td>
                                <td class="p-2 align-top">
                                    <Input v-model="row.qty_dipesan" type="number" min="0.01" step="0.01" placeholder="Qty" :disabled="selectedPbid !== 'none'" />
                                </td>
                                <td class="p-2 align-top">
                                    <Input v-model="row.harga_satuan" type="number" min="0" placeholder="Harga satuan" />
                                </td>
                                <td class="p-2 align-top">
                                    <button type="button" class="flex size-9 items-center justify-center text-muted-foreground hover:text-destructive" :disabled="selectedPbid !== 'none'" @click="removeRow(index)">
                                        <Trash2 class="size-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="formItems.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                        Belum ada item.
                    </p>
                </div>
                <div class="flex justify-end text-sm">
                    <span class="text-muted-foreground">Estimasi Total:&nbsp;</span>
                    <span class="font-mono font-semibold">{{ formatRupiah(previewTotal) }}</span>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a :href="indexUrl">
                    <Button type="button" variant="outline">Batal</Button>
                </a>
                <Button type="submit" :disabled="formItems.length === 0 || !form.spid || saving">Simpan &amp; Terbitkan</Button>
            </div>
        </form>
    </div>
</template>
