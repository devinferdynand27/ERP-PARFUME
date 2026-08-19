<script setup>
import { LogOut } from '@lucide/vue';
import { http } from '@/lib/http';

const props = defineProps({
    userName: { type: String, default: 'Administrator' },
    userRole: { type: String, default: 'admin' },
    logoutUrl: { type: String, required: true },
});

async function handleLogout() {
    if (!confirm('Apakah Anda yakin ingin keluar?')) return;
    try {
        await http.post(props.logoutUrl);
        // Redirect ke halaman login secara hard reload untuk membersihkan session frontend
        window.location.href = '/login';
    } catch (e) {
        alert('Gagal melakukan logout. Silakan coba lagi.');
    }
}
</script>

<template>
    <header class="flex items-center justify-end gap-4 border-b border-border bg-card px-8 py-4 shadow-sm">
        <div class="flex flex-col items-end text-right">
            <span class="text-sm font-semibold text-foreground">{{ userName }}</span>
            <span class="text-xs text-muted-foreground uppercase font-medium tracking-wide">
                {{ userRole === 'admin' ? 'Administrator' : 'Kasir' }}
            </span>
        </div>
        <button
            type="button"
            @click="handleLogout"
            class="flex items-center gap-1.5 rounded-md border border-destructive/30 px-3 py-1.5 text-sm font-medium text-destructive transition-colors hover:bg-destructive/10 cursor-pointer"
        >
            <LogOut class="size-4" />
            Keluar
        </button>
    </header>
</template>
