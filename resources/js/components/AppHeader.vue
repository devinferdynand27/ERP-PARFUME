<script setup>
import { LogOut } from '@lucide/vue';
</script>

<template>
    <header class="flex items-center justify-end gap-4 border-b border-border bg-card px-8 py-4">
        <span class="text-sm font-medium text-foreground">Administrator</span>
        <button
            type="button"
            class="flex items-center gap-1.5 rounded-md border border-destructive/30 px-3 py-1.5 text-sm font-medium text-destructive transition-colors hover:bg-destructive/10"
        >
            <LogOut class="size-4" />
            Keluar
        </button>
    </header>
</template>
