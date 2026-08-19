<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import {
    LayoutDashboard, Package, Ruler, FlaskConical, Truck, Scale,
} from '@lucide/vue';

const props = defineProps({
    active: { type: String, required: true },
    urls: { type: Object, required: true },
    userRole: { type: String, default: 'admin' },
});

// Sidebar tidak ikut di-remount saat navigasi tanpa reload (lihat lib/navigate.js
// — hanya <main> yang di-swap), jadi active-state harus tetap reactive lewat
// event ini, bukan cuma prop awal.
const active = ref(props.active);

function onNavigated(event) {
    active.value = event.detail.active;
}

onMounted(() => window.addEventListener('app:navigated', onNavigated));
onUnmounted(() => window.removeEventListener('app:navigated', onNavigated));

const menu = [
    { key: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, urlKey: 'dashboard' },
];

const masterData = [
    { key: 'produk', label: 'Master Produk', icon: Package, urlKey: 'produk' },
    { key: 'ukuran-botol', label: 'Master Ukuran Botol', icon: Ruler, urlKey: 'ukuranBotol' },
    { key: 'kualitas-bibit', label: 'Master Kualitas Bibit', icon: FlaskConical, urlKey: 'kualitasBibit' },
    { key: 'supplier', label: 'Master Supplier', icon: Truck, urlKey: 'supplier' },
    { key: 'aroma', label: 'Master Aroma', icon: FlaskConical, urlKey: 'aroma' },
    { key: 'satuan', label: 'Master Satuan', icon: Scale, urlKey: 'satuan' },
];
</script>

<template>
    <aside class="flex w-64 shrink-0 flex-col border-r border-border bg-card">
        <div class="border-b border-border px-6 py-5">
            <div class="text-xl font-bold tracking-tight">CAVA</div>
            <div class="text-[10px] font-medium tracking-widest text-muted-foreground">
                PARFUMS | LUXURY FRAGRANCES
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
            <a
                v-for="item in menu"
                :key="item.key"
                :href="urls[item.urlKey]"
                class="flex items-center gap-3 rounded-md px-3 py-2 font-medium transition-colors"
                :class="active === item.key ? 'bg-foreground text-background' : 'text-foreground hover:bg-accent'"
            >
                <component :is="item.icon" class="size-4" />
                {{ item.label }}
            </a>

            <template v-if="userRole === 'admin'">
                <div class="mt-5 mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                    Master Data
                </div>
                <a
                    v-for="item in masterData"
                    :key="item.key"
                    :href="urls[item.urlKey]"
                    class="flex items-center gap-3 rounded-md px-3 py-2 font-medium transition-colors"
                    :class="active === item.key ? 'bg-foreground text-background' : 'text-foreground hover:bg-accent'"
                >
                    <component :is="item.icon" class="size-4" />
                    {{ item.label }}
                </a>
            </template>
        </nav>
    </aside>
</template>
