<script lang="ts" setup>
import {Check, ChevronDown, Plus, X} from '@lucide/vue';
import {computed, nextTick, onBeforeUnmount, onMounted, ref} from 'vue';
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

type OptionValue = string | number;

interface Option {
    value: OptionValue;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: OptionValue | OptionValue[] | null;
        label: string;
        options?: Option[];
        multiple?: boolean;
        searchable?: boolean;
        clearable?: boolean;
        taggable?: boolean;
        placeholder?: string;
        id?: string;
        error?: string;
        hint?: string;
        required?: boolean;
        compact?: boolean;
        hideLabel?: boolean;
    }>(),
    {
        options: () => [],
        multiple: false,
        searchable: true,
        clearable: true,
        taggable: false,
        placeholder: 'Sélectionner…',
        compact: false,
        hideLabel: false,
    },
);

const emit = defineEmits<{ (e: 'update:modelValue', value: OptionValue | OptionValue[] | null): void }>();

const root = ref<HTMLElement | null>(null);
const trigger = ref<HTMLElement | null>(null);
const dropdown = ref<HTMLElement | null>(null);
const open = ref(false);
const search = ref('');
const dropdownStyle = ref<Record<string, string>>({});

const selectedValues = computed<OptionValue[]>(() => {
    if (props.multiple) {
        return (props.modelValue as OptionValue[] | null) ?? [];
    }

    return props.modelValue == null ? [] : [props.modelValue as OptionValue];
});

function labelFor(value: OptionValue): string {
    return props.options.find((option) => option.value === value)?.label ?? String(value);
}

const singleLabel = computed(() => (selectedValues.value.length > 0 ? labelFor(selectedValues.value[0]) : ''));

const filteredOptions = computed<Option[]>(() => {
    const term = search.value.trim().toLowerCase();

    return props.options.filter((option) => {
        if (props.multiple && selectedValues.value.includes(option.value)) {
            return false;
        }

        return term === '' || option.label.toLowerCase().includes(term);
    });
});

const canAddTag = computed(() => {
    if (!props.taggable) {
        return false;
    }
    const term = search.value.trim();
    if (term === '') {
        return false;
    }
    const exists = props.options.some((option) => option.label.toLowerCase() === term.toLowerCase());

    return ! exists && ! selectedValues.value.some((value) => String(value).toLowerCase() === term.toLowerCase());
});

function pick(value: OptionValue): void {
    if (props.multiple) {
        emit('update:modelValue', [...selectedValues.value, value]);
        search.value = '';
    } else {
        emit('update:modelValue', value);
        open.value = false;
    }
}

function addTag(): void {
    pick(search.value.trim());
    search.value = '';
}

function remove(value: OptionValue): void {
    if (props.multiple) {
        emit('update:modelValue', selectedValues.value.filter((current) => current !== value));
    } else {
        emit('update:modelValue', null);
    }
}

function clearAll(): void {
    emit('update:modelValue', props.multiple ? [] : null);
}

function toggle(): void {
    open.value = !open.value;
    if (open.value) {
        search.value = '';
        void nextTick(updateDropdownPosition);
    }
}

function updateDropdownPosition(): void {
    const element = trigger.value;
    if (! element || ! open.value) {
        return;
    }

    const rect = element.getBoundingClientRect();
    const gap = 4;
    const viewportPadding = 8;
    const desiredHeight = 280;
    const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
    const spaceAbove = rect.top - viewportPadding;
    const opensUpward = spaceBelow < 180 && spaceAbove > spaceBelow;
    const availableHeight = Math.max(120, Math.min(desiredHeight, opensUpward ? spaceAbove - gap : spaceBelow - gap));

    const dropdownWidth = Math.min(rect.width, window.innerWidth - viewportPadding * 2);
    const dropdownLeft = Math.min(
        Math.max(viewportPadding, rect.left),
        window.innerWidth - dropdownWidth - viewportPadding,
    );

    dropdownStyle.value = {
        position: 'fixed',
        left: `${dropdownLeft}px`,
        top: opensUpward ? 'auto' : `${rect.bottom + gap}px`,
        bottom: opensUpward ? `${window.innerHeight - rect.top + gap}px` : 'auto',
        width: `${dropdownWidth}px`,
        maxHeight: `${availableHeight}px`,
        zIndex: '9999',
    };
}

function onClickOutside(event: MouseEvent): void {
    const target = event.target as Node;
    if (root.value && !root.value.contains(target) && !dropdown.value?.contains(target)) {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside);
    window.addEventListener('resize', updateDropdownPosition);
    window.addEventListener('scroll', updateDropdownPosition, true);
});
onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside);
    window.removeEventListener('resize', updateDropdownPosition);
    window.removeEventListener('scroll', updateDropdownPosition, true);
});
</script>

<template>
    <FieldWrapper
        :error="error"
        :for-id="id"
        :hint="hint"
        :label="hideLabel ? undefined : label"
        :required="required"
    >
        <div ref="root" class="relative min-w-0 max-w-full">
            <div
                ref="trigger"
                :id="id"
                :aria-label="label"
                :aria-expanded="open"
                aria-haspopup="listbox"
                :class="[
                    open ? 'border-[#c23a72]' : '',
                    compact ? 'min-h-10 py-1.5' : 'min-h-11 py-2',
                ]"
                class="admin-field flex w-full cursor-pointer flex-wrap items-center gap-1.5 border bg-[var(--admin-field)] px-3 text-[var(--admin-heading)] transition"
                role="combobox"
                tabindex="0"
                @click="toggle"
                @keydown.enter.prevent="toggle"
                @keydown.escape="open = false"
                @keydown.space.prevent="toggle"
            >
                <!-- Multiple: chips -->
                <template v-if="multiple && selectedValues.length > 0">
                    <span
                        v-for="value in selectedValues"
                        :key="value"
                        class="inline-flex items-center gap-1 bg-[#7d254a]/60 py-1 pl-2 pr-1 text-xs font-medium text-rose-100"
                    >
                        {{ labelFor(value) }}
                        <button
                            :aria-label="`Retirer ${labelFor(value)}`"
                            class="grid size-4 place-items-center hover:bg-white/10"
                            type="button"
                            @click.stop="remove(value)"
                        >
                            <X class="size-3" :stroke-width="2.5"/>
                        </button>
                    </span>
                </template>

                <!-- Single: label -->
                <span v-else-if="!multiple && singleLabel" class="admin-heading flex-1 truncate">{{ singleLabel }}</span>

                <!-- Placeholder -->
                <span v-else class="flex-1 truncate text-slate-400">{{ placeholder }}</span>

                <span class="ml-auto flex items-center gap-1">
                    <button
                        v-if="clearable && selectedValues.length > 0"
                        aria-label="Effacer la sélection"
                        class="grid size-5 place-items-center text-slate-500 hover:text-rose-400"
                        type="button"
                        @click.stop="clearAll"
                    >
                        <X class="size-3.5" :stroke-width="2.2"/>
                    </button>
                    <ChevronDown :class="open ? 'rotate-180' : ''" class="size-4 text-slate-500 transition"/>
                </span>
            </div>

            <Teleport to="body">
            <div v-if="open" ref="dropdown" :style="dropdownStyle" class="admin-panel flex flex-col overflow-hidden border bg-[var(--admin-panel)] text-[var(--admin-text)] shadow-2xl shadow-black/20">
                <div v-if="searchable" class="admin-divider border-b p-2">
                    <input
                        v-model="search"
                        :placeholder="taggable ? 'Rechercher ou ajouter…' : 'Rechercher…'"
                        class="admin-field h-9 w-full border px-2.5 text-sm outline-none"
                        type="text"
                        @keydown.enter.prevent="canAddTag && addTag()"
                        @click.stop
                    />
                </div>
                <ul class="min-h-0 flex-1 overflow-y-auto py-1" role="listbox">
                    <li
                        v-for="option in filteredOptions"
                        :key="option.value"
                        :class="selectedValues.includes(option.value) ? 'bg-[#7d254a]/15 text-[#a23362] dark:bg-[#7d254a]/40 dark:text-rose-200' : 'admin-text admin-hover'"
                        :aria-selected="selectedValues.includes(option.value)"
                        class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm"
                        role="option"
                        tabindex="0"
                        @click="pick(option.value)"
                        @keydown.enter.prevent="pick(option.value)"
                        @keydown.space.prevent="pick(option.value)"
                    >
                        {{ option.label }}
                        <Check v-if="selectedValues.includes(option.value)" class="size-4" :stroke-width="2.2"/>
                    </li>

                    <li
                        v-if="canAddTag"
                        class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm text-[#ef477d] hover:bg-white/5"
                        role="option"
                        tabindex="0"
                        @click="addTag"
                        @keydown.enter.prevent="addTag"
                        @keydown.space.prevent="addTag"
                    >
                        <Plus class="size-4" :stroke-width="2"/>
                        Ajouter « {{ search.trim() }} »
                    </li>

                    <li v-if="filteredOptions.length === 0 && !canAddTag" class="px-3 py-3 text-center text-sm text-slate-400">
                        Aucun résultat
                    </li>
                </ul>
            </div>
            </Teleport>
        </div>
    </FieldWrapper>
</template>
