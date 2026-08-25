<script setup>
import { ref, reactive } from 'vue';
import { Lock, Mail, Loader2 } from '@lucide/vue';
import { http } from '@/lib/http';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

const props = defineProps({
    loginUrl: { type: String, required: true },
});

const form = reactive({
    email: '',
    password: '',
});

const formErrors = ref({});
const errorMessage = ref('');
const loading = ref(false);

async function handleLogin() {
    formErrors.value = {};
    errorMessage.value = '';
    loading.value = true;

    try {
        const response = await http.post(props.loginUrl, form);
        if (response.success && response.redirect) {
            window.location.href = response.redirect;
        }
    } catch (e) {
        if (e.status === 422 && e.body?.errors) {
            formErrors.value = e.body.errors;
        } else {
            errorMessage.value = e.body?.message ?? 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex items-center justify-center min-h-[80vh]">
        <Card class="w-full max-w-md border-border shadow-md">
            <CardHeader class="space-y-1 text-center">
                <div class="flex justify-center mb-2">
                    <div class="flex items-center justify-center rounded-xl bg-foreground text-background p-2.5 shadow-sm">
                        <span class="text-xl font-bold tracking-tight leading-none px-1">ERP PARFUME</span>
                    </div>
                </div>
                <CardTitle class="text-2xl font-bold tracking-tight">Selamat Datang Kembali</CardTitle>
                <CardDescription>
                    Masukkan email dan password untuk masuk ke sistem ERP Parfume
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="handleLogin" class="space-y-4">
                    <div v-if="errorMessage" class="rounded-lg bg-destructive/10 p-3 text-sm text-destructive font-medium border border-destructive/20">
                        {{ errorMessage }}
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <div class="relative">
                            <Mail class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="nama@perusahaan.com"
                                class="pl-9"
                                :disabled="loading"
                                required
                            />
                        </div>
                        <p v-if="formErrors.email" class="text-sm text-destructive font-medium">
                            {{ formErrors.email[0] }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <div class="relative">
                            <Lock class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="••••••••"
                                class="pl-9"
                                :disabled="loading"
                                required
                            />
                        </div>
                        <p v-if="formErrors.password" class="text-sm text-destructive font-medium">
                            {{ formErrors.password[0] }}
                        </p>
                    </div>

                    <Button type="submit" class="w-full mt-2" :disabled="loading">
                        <Loader2 v-if="loading" class="mr-2 size-4 animate-spin" />
                        {{ loading ? 'Memverifikasi...' : 'Masuk' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
