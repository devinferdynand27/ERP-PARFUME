<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import {
    LayoutDashboard, FlaskConical, Truck, Scale,
    ClipboardList, ShoppingCart, PackageCheck, X
} from '@lucide/vue';

const props = defineProps({
    active: { type: String, required: true },
    urls: { type: Object, required: true },
    userRole: { type: String, default: 'admin' },
});

const active = ref(props.active);
const isOpen = ref(false);

function onSidebarToggle() {
    isOpen.value = !isOpen.value;
}

function onNavigated(event) {
    active.value = event.detail.active;
    isOpen.value = false; // Otomatis tutup sidebar setelah berpindah halaman di mobile
}

onMounted(() => {
    window.addEventListener('app:sidebar:toggle', onSidebarToggle);
    window.addEventListener('app:navigated', onNavigated);
});

onUnmounted(() => {
    window.removeEventListener('app:sidebar:toggle', onSidebarToggle);
    window.removeEventListener('app:navigated', onNavigated);
});

const menu = [
    { key: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, urlKey: 'dashboard' },
];

const masterData = [
    { key: 'supplier', label: 'Master Supplier', icon: Truck, urlKey: 'supplier' },
    { key: 'aroma', label: 'Master Aroma', icon: FlaskConical, urlKey: 'aroma' },
    { key: 'satuan', label: 'Master Satuan', icon: Scale, urlKey: 'satuan' },
];

const transaksi = [
    { key: 'permintaan-barang', label: 'Permintaan Barang', icon: ClipboardList, urlKey: 'permintaanBarang' },
    { key: 'pesanan-pembelian', label: 'Pesanan Pembelian', icon: ShoppingCart, urlKey: 'pesananPembelian' },
    { key: 'penerimaan-barang', label: 'Penerimaan Barang', icon: PackageCheck, urlKey: 'penerimaanBarang' },
];
</script>

<template>
    <!-- Backdrop Overlay untuk Mobile (Aktif saat sidebar terbuka) -->
    <div
        v-if="isOpen"
        @click="isOpen = false"
        class="fixed inset-0 bg-black/40 z-40 lg:hidden transition-opacity duration-300"
    ></div>

    <!-- Aside Container (Dengan transisi slide-in slide-out) -->
    <aside
        class="flex w-64 shrink-0 flex-col border-r border-[#E2E8F0] bg-white select-none h-screen transition-transform duration-300 ease-in-out z-50"
        :class="isOpen 
            ? 'fixed inset-y-0 left-0 translate-x-0 shadow-lg' 
            : 'fixed inset-y-0 left-0 -translate-x-full lg:static lg:translate-x-0'"
    >
        <!-- Brand / Logo Area (Ditambah tombol close khusus mobile) -->
        <div class="border-b border-[#E2E8F0] px-6 py-5 flex items-center justify-between">
            <div>
                <div class="text-xl font-bold tracking-tight text-[#0F172A]">CAVA</div>
                <div class="text-[9px] font-semibold tracking-widest text-[#94A3B8] uppercase">
                    Parfums | Luxury Fragrances
                </div>
            </div>
            <!-- Tombol Close (Hanya Muncul di Mobile) -->
            <button
                type="button"
                @click="isOpen = false"
                class="lg:hidden p-1.5 rounded-lg hover:bg-slate-50 text-[#64748B] border border-[#E2E8F0] cursor-pointer transition-colors"
                aria-label="Close navigation sidebar"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-1.5 overflow-y-auto px-4 py-6 text-sm">
            <a
                v-for="item in menu"
                :key="item.key"
                :href="urls[item.urlKey]"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition-all duration-150"
                :class="active === item.key 
                    ? 'bg-[#050505] text-white shadow-sm' 
                    : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]'"
            >
                <component :is="item.icon" class="size-[18px]" />
                <span>{{ item.label }}</span>
            </a>

            <div class="mt-5 mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                Transaksi
            </div>
            <a
                v-for="item in transaksi"
                :key="item.key"
                :href="urls[item.urlKey]"
                class="flex items-center gap-3 rounded-md px-3 py-2 font-medium transition-colors"
                :class="active === item.key ? 'bg-foreground text-background' : 'text-foreground hover:bg-accent'"
            >
                <component :is="item.icon" class="size-4" />
                {{ item.label }}
            </a>
            <!-- Master Data Group -->
            <template v-if="userRole === 'admin'">
                <div class="mt-6 mb-2 px-3 text-[11px] font-semibold tracking-[0.12em] uppercase text-[#94A3B8]">
                    Master Data
                </div>
                <a
                    v-for="item in masterData"
                    :key="item.key"
                    :href="urls[item.urlKey]"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition-all duration-150"
                    :class="active === item.key 
                        ? 'bg-[#050505] text-white shadow-sm' 
                        : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]'"
                >
                    <component :is="item.icon" class="size-[18px]" />
                    <span>{{ item.label }}</span>
                </a>
            </template>
        </nav>
    </aside>
</template>
