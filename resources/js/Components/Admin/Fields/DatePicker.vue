<script lang="ts" setup>
import {computed, onBeforeUnmount, onMounted, ref} from 'vue';
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        label: string;
        error?: string;
        hint?: string;
        required?: boolean;
        placeholder?: string;
        clearable?: boolean;
    }>(),
    {
        placeholder: 'Sélectionner une date',
        clearable: true,
    },
);

const emit = defineEmits<{ (e: 'update:modelValue', value: string | null): void }>();

const root = ref<HTMLElement | null>(null);
const open = ref(false);

const weekDays = ['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'];
const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

function parse(value: string | null): Date | null {
    if (!value) {
        return null;
    }
    const [y, m, d] = value.split('-').map(Number);

    return Number.isNaN(y) ? null : new Date(y, m - 1, d);
}

function toISO(date: Date): string {
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${m}-${d}`;
}

const selected = computed(() => parse(props.modelValue));
const view = ref<Date>(selected.value ?? new Date());

const displayValue = computed(() => {
    const date = selected.value;

    return date ? `${date.getDate()} ${months[date.getMonth()].toLowerCase()} ${date.getFullYear()}` : '';
});

const grid = computed(() => {
    const year = view.value.getFullYear();
    const month = view.value.getMonth();
    const first = new Date(year, month, 1);
    const offset = (first.getDay() + 6) % 7; // semaine commence lundi
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const cells: (Date | null)[] = [];

    for (let i = 0; i < offset; i++) {
        cells.push(null);
    }
    for (let d = 1; d <= daysInMonth; d++) {
        cells.push(new Date(year, month, d));
    }

    return cells;
});

const today = toISO(new Date());

function isSelected(date: Date): boolean {
    return props.modelValue === toISO(date);
}

function isToday(date: Date): boolean {
    return today === toISO(date);
}

function select(date: Date): void {
    emit('update:modelValue', toISO(date));
    open.value = false;
}

function clear(): void {
    emit('update:modelValue', null);
}

function prevMonth(): void {
    view.value = new Date(view.value.getFullYear(), view.value.getMonth() - 1, 1);
}

function nextMonth(): void {
    view.value = new Date(view.value.getFullYear(), view.value.getMonth() + 1, 1);
}

function toggle(): void {
    if (!open.value && selected.value) {
        view.value = selected.value;
    }
    open.value = !open.value;
}

function onClickOutside(event: MouseEvent): void {
    if (root.value && !root.value.contains(event.target as Node)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <FieldWrapper :error="error" :hint="hint" :label="label" :required="required">
        <div ref="root" class="relative">
            <button
                :class="open ? 'border-[#bf045b]' : 'border-slate-200'"
                class="flex h-10 w-full items-center justify-between rounded-lg border bg-white px-3 text-left text-sm outline-none"
                type="button"
                @click="toggle"
            >
                <span :class="displayValue ? 'text-slate-700' : 'text-slate-400'">
                    {{ displayValue || placeholder }}
                </span>
                <span class="flex items-center gap-1">
                    <span
                        v-if="clearable && modelValue"
                        class="grid size-5 place-items-center rounded text-slate-400 hover:text-red-500"
                        role="button"
                        @click.stop="clear"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path d="M8 2v3M16 2v3M3 8h18M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" stroke-linecap="round"/>
                    </svg>
                </span>
            </button>

            <div
                v-if="open"
                class="absolute z-50 mt-1 w-72 rounded-lg border border-slate-200 bg-white p-3 shadow-lg"
            >
                <div class="mb-2 flex items-center justify-between">
                    <button class="grid size-7 place-items-center rounded-md text-slate-500 hover:bg-slate-100" type="button" @click="prevMonth">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <span class="text-sm font-semibold text-slate-700">{{ months[view.getMonth()] }} {{ view.getFullYear() }}</span>
                    <button class="grid size-7 place-items-center rounded-md text-slate-500 hover:bg-slate-100" type="button" @click="nextMonth">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-slate-400">
                    <span v-for="day in weekDays" :key="day">{{ day }}</span>
                </div>
                <div class="mt-1 grid grid-cols-7 gap-1">
                    <template v-for="(cell, index) in grid" :key="index">
                        <span v-if="!cell"/>
                        <button
                            v-else
                            :class="isSelected(cell)
                                ? 'bg-[#bf045b] font-semibold text-white'
                                : isToday(cell)
                                    ? 'text-[#bf045b] ring-1 ring-[#bf045b]/40'
                                    : 'text-slate-600 hover:bg-slate-100'"
                            class="grid h-8 place-items-center rounded-md text-sm transition"
                            type="button"
                            @click="select(cell)"
                        >
                            {{ cell.getDate() }}
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </FieldWrapper>
</template>
