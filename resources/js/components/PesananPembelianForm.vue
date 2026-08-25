<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { Plus, Trash2, ArrowLeft } from '@lucide/vue';
import { http } from '@/lib/http';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Combobox from '@/components/ui/combobox/Combobox.vue';

const props = defineProps({
    ppid: { type: [Number, String], default: null },
    formOptionsUrl: { type: String, required: true },
    dariPermintaanUrlTemplate: { type: String, required: true },
    showUrl: { type: String, default: null },
    storeUrl: { type: String, required: true },
    updateUrl: { type: String, default: null },
    indexUrl: { type: String, required: true },
    permintaanBarangDataUrl: { type: String, required: true },
});

const isEditMode = computed(() => !!props.ppid);

const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');

const masterBarangOptions = ref([]);
const satuanOptions = ref([]);
const supplierOptions = ref([]);
const permintaanDisetujui = ref([]);

const selectedPbid = ref(null);
const selectedPrHeader = ref(null);
const prOptions = computed(() => [
    { pbid: 'none', nomor_permintaan: 'Tanpa PR (langsung)' },
    ...permintaanDisetujui.value,
]);
const hasPr = computed(() => !!selectedPbid.value && selectedPbid.value !== 'none');
const itemsLockedByPr = computed(() => hasPr.value && !isEditMode.value);
const initializing = ref(true);
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

// ponytail: cursor jumps to end on each keystroke, acceptable for append-style price entry
function onHargaInput(index, value) {
    const digits = String(value).replace(/\D/g, '');
    formItems.value[index].harga_satuan = digits === '' ? 0 : Number(digits);
}

async function load() {
    loading.value = true;
    const [options, prResult] = await Promise.all([
        http.get(props.formOptionsUrl),
        http.get(`${props.permintaanBarangDataUrl}?status=disetujui&per_page=1000`),
    ]);
    masterBarangOptions.value = options.masterBarang;
    satuanOptions.value = options.satuan;
    supplierOptions.value = options.supplier;
    permintaanDisetujui.value = prResult.data;

    if (isEditMode.value) {
        const detail = await http.get(props.showUrl);
        form.spid = detail.header.spid;
        form.tanggal = detail.header.tanggal;
        form.catatan = detail.header.catatan ?? '';
        formItems.value = detail.items.map((i) => ({
            pbdid: i.pbdid,
            mbid: i.mbid,
            stid: i.stid,
            qty_dipesan: Number(i.qty_dipesan),
            harga_satuan: Number(i.harga_satuan),
        }));
        if (detail.header.pbid) {
            selectedPbid.value = detail.header.pbid;
            selectedPrHeader.value = { nomor_permintaan: detail.header.nomor_permintaan };
        }
    } else {
        addRow();
    }

    loading.value = false;
    initializing.value = false;
}

watch(selectedPbid, async (pbid) => {
    if (initializing.value) return;
    if (!pbid || pbid === 'none') {
        selectedPrHeader.value = null;
        return;
    }
    const result = await http.get(buildUrl(props.dariPermintaanUrlTemplate, pbid));
    selectedPrHeader.value = result.header;
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
    try {
        if (isEditMode.value) {
            await http.put(props.updateUrl, { ...form, items: formItems.value });
        } else {
            await http.post(props.storeUrl, {
                ...form,
                pbid: hasPr.value ? selectedPbid.value : null,
                items: formItems.value,
            });
        }
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
                <h1 class="text-2xl font-bold tracking-tight">{{ isEditMode ? 'Edit Pesanan Pembelian' : 'Buat Pesanan Pembelian' }}</h1>
                <p class="text-sm text-muted-foreground">{{ isEditMode ? 'Ubah pesanan pembelian yang belum diterima' : 'Terbitkan pesanan pembelian ke supplier' }}</p>
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
                    <Combobox
                        v-model="selectedPbid"
                        :options="prOptions"
                        option-value="pbid"
                        option-label="nomor_permintaan"
                        placeholder="Tanpa PR"
                        :disabled="isEditMode"
                    />
                </div>
                <div class="space-y-2">
                    <Label>Supplier</Label>
                    <Combobox
                        v-model="form.spid"
                        :options="supplierOptions"
                        option-value="spid"
                        option-label="nama_supplier"
                        placeholder="Pilih supplier"
                    />
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

            <div v-if="selectedPrHeader" class="rounded-lg border border-border bg-muted/30 p-4">
                <p class="text-xs font-medium uppercase text-muted-foreground">Detail Permintaan Barang</p>
                <div class="mt-2 grid grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-muted-foreground">Nomor Permintaan</p>
                        <p class="font-medium">{{ selectedPrHeader.nomor_permintaan }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Tanggal</p>
                        <p class="font-medium">{{ selectedPrHeader.tanggal }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Status</p>
                        <p class="font-medium capitalize">{{ selectedPrHeader.status }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Dibuat Oleh</p>
                        <p class="font-medium">{{ selectedPrHeader.dibuat_oleh || '-' }}</p>
                    </div>
                    <div class="col-span-4">
                        <p class="text-xs text-muted-foreground">Catatan</p>
                        <p class="font-medium">{{ selectedPrHeader.catatan || '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label>Item Barang</Label>
                    <Button type="button" size="sm" variant="outline" @click="addRow" :disabled="itemsLockedByPr">
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
                                    <Combobox
                                        v-model="row.mbid"
                                        :options="masterBarangOptions"
                                        option-value="mbid"
                                        option-label="nama_barang"
                                        placeholder="Pilih barang"
                                        :disabled="itemsLockedByPr"
                                    />
                                </td>
                                <td class="p-2 align-top">
                                    <Combobox
                                        v-model="row.stid"
                                        :options="satuanOptions"
                                        option-value="stid"
                                        option-label="nama_satuan"
                                        placeholder="Satuan"
                                        :disabled="itemsLockedByPr"
                                    />
                                </td>
                                <td class="p-2 align-top">
                                    <Input v-model="row.qty_dipesan" type="number" min="0.01" step="0.01" placeholder="Qty" :disabled="itemsLockedByPr" />
                                </td>
                                <td class="p-2 align-top">
                                    <Input
                                        :model-value="formatRupiah(row.harga_satuan)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="Rp 0"
                                        @update:model-value="onHargaInput(index, $event)"
                                    />
                                </td>
                                <td class="p-2 align-top">
                                    <button type="button" class="flex size-9 items-center justify-center text-muted-foreground hover:text-destructive" :disabled="itemsLockedByPr" @click="removeRow(index)">
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
                <Button type="submit" :disabled="formItems.length === 0 || !form.spid || saving">{{ isEditMode ? 'Simpan Perubahan' : 'Simpan & Terbitkan' }}</Button>
            </div>
        </form>
    </div>
</template>
