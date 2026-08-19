<script setup>
import { computed, ref } from 'vue';
import { CheckIcon, ChevronDownIcon } from '@lucide/vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: [Number, String], default: null },
    options: { type: Array, required: true },
    optionValue: { type: String, default: 'value' },
    optionLabel: { type: String, default: 'label' },
    placeholder: { type: String, default: 'Pilih...' },
    emptyText: { type: String, default: 'Tidak ada hasil.' },
    class: { type: [String, Object, Array], default: '' },
});

const emit = defineEmits(['update:modelValue']);

const searchTerm = ref('');

const selectedOption = computed(
    () => props.options.find((o) => o[props.optionValue] === props.modelValue) ?? null,
);

const filteredOptions = computed(() => {
    const term = searchTerm.value.trim().toLowerCase();
    if (!term) return props.options;
    return props.options.filter((o) => String(o[props.optionLabel]).toLowerCase().includes(term));
});

function onUpdateModelValue(value) {
    emit('update:modelValue', value);
}

function onUpdateOpen(open) {
    if (!open) searchTerm.value = '';
}
</script>

<template>
    <ComboboxRoot
        :model-value="modelValue"
        :ignore-filter="true"
        class="relative"
        @update:model-value="onUpdateModelValue"
        @update:open="onUpdateOpen"
    >
        <ComboboxAnchor
            :class="
                cn(
                    'border-input data-placeholder:text-muted-foreground dark:bg-input/30 dark:hover:bg-input/50 focus-within:border-ring focus-within:ring-ring/50 flex h-8 w-full items-center gap-1.5 rounded-lg border bg-transparent py-2 pr-2 pl-2.5 text-sm transition-colors focus-within:ring-3',
                    props.class,
                )
            "
        >
            <ComboboxInput
                v-model="searchTerm"
                :display-value="() => (selectedOption ? selectedOption[optionLabel] : '')"
                :placeholder="placeholder"
                class="w-full flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
            />
            <ComboboxTrigger class="flex items-center">
                <ChevronDownIcon class="text-muted-foreground pointer-events-none size-4" />
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxPortal>
            <ComboboxContent
                position="popper"
                side="bottom"
                :side-offset="4"
                align="start"
                class="bg-popover text-popover-foreground ring-foreground/10 relative z-50 max-h-64 w-(--reka-combobox-trigger-width) overflow-hidden rounded-lg shadow-md ring-1"
            >
                <ComboboxViewport class="max-h-56 overflow-y-auto p-1">
                    <ComboboxEmpty class="py-4 text-center text-sm text-muted-foreground">
                        {{ emptyText }}
                    </ComboboxEmpty>
                    <ComboboxItem
                        v-for="option in filteredOptions"
                        :key="option[optionValue]"
                        :value="option[optionValue]"
                        class="focus:bg-accent focus:text-accent-foreground data-highlighted:bg-accent data-highlighted:text-accent-foreground relative flex w-full cursor-default items-center gap-1.5 rounded-md py-1 pr-8 pl-1.5 text-sm outline-hidden select-none"
                    >
                        <span class="pointer-events-none absolute right-2 flex size-4 items-center justify-center">
                            <ComboboxItemIndicator>
                                <CheckIcon class="pointer-events-none size-4" />
                            </ComboboxItemIndicator>
                        </span>
                        {{ option[optionLabel] }}
                    </ComboboxItem>
                </ComboboxViewport>
            </ComboboxContent>
        </ComboboxPortal>
    </ComboboxRoot>
</template>
