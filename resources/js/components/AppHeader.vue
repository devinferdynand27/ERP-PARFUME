<script setup>
import { LogOut, Menu } from '@lucide/vue';
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
        window.location.href = '/login';
    } catch (e) {
        alert('Gagal melakukan logout. Silakan coba lagi.');
    }
}

function toggleSidebar() {
    window.dispatchEvent(new CustomEvent('app:sidebar:toggle'));
}
</script>

<template>
    <header class="flex h-[70px] shrink-0 items-center justify-between border-b border-[#E2E8F0] bg-white px-6 md:px-8">
        <!-- Hamburger Button on Left (Mobile Only) -->
        <button
            type="button"
            @click="toggleSidebar"
            class="lg:hidden flex items-center justify-center p-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:bg-slate-50 cursor-pointer transition-colors"
            aria-label="Toggle navigation menu"
        >
            <Menu class="size-5" />
        </button>

        <!-- Right Side: User profile & Logout -->
        <div class="flex items-center gap-5 ml-auto">
            <!-- User Profile Info -->
            <div class="flex flex-col items-end leading-tight">
                <span class="text-sm font-semibold text-[#0F172A]">{{ userName }}</span>
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider mt-0.5">
                    {{ userRole === 'admin' ? 'Administrator' : 'Kasir' }}
                </span>
            </div>

            <!-- Logout Button -->
            <button
                type="button"
                @click="handleLogout"
                class="flex items-center gap-1.5 rounded-lg border border-red-200 px-3.5 py-1.5 text-xs font-semibold text-red-600 bg-white hover:bg-red-50/50 hover:text-red-700 transition-all duration-150 cursor-pointer"
            >
                <LogOut class="size-3.5" />
                <span>Keluar</span>
            </button>
        </div>
    </header>
</template>
