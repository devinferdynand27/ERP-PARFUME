import { createApp } from 'vue';

const components = import.meta.glob('../components/*.vue', { eager: true });

function resolveComponent(name) {
    const entry = components[`../components/${name}.vue`];
    return entry?.default ?? null;
}

/**
 * Mount setiap [data-vue-component] dalam root ke Vue app baru dan simpan
 * instance-nya di elemen (._vueApp) supaya navigate.js bisa unmount-nya
 * sebelum swap konten halaman.
 */
export function mountIn(root) {
    root.querySelectorAll('[data-vue-component]').forEach((el) => {
        const name = el.dataset.vueComponent;
        const component = resolveComponent(name);

        if (!component) {
            console.error(`[mount.js] Komponen Vue "${name}" tidak ditemukan di resources/js/components/`);
            return;
        }

        const props = el.dataset.vueProps ? JSON.parse(el.dataset.vueProps) : {};
        const app = createApp(component, props);
        app.mount(el);
        el._vueApp = app;
    });
}

/** Unmount semua Vue app yang pernah di-mount di dalam root (lihat mountIn). */
export function unmountIn(root) {
    root.querySelectorAll('[data-vue-component]').forEach((el) => {
        el._vueApp?.unmount();
        el._vueApp = null;
    });
}
