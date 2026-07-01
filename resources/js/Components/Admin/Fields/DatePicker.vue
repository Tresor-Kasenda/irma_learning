<script lang="ts" setup>
import {CalendarDays, ChevronLeft, ChevronRight, X} from '@lucide/vue';
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
            <div
                :aria-expanded="open"
                aria-haspopup="dialog"
                :class="open ? 'border-[#c23a72]' : ''"
                class="admin-field flex h-11 w-full cursor-pointer items-center justify-between border px-3 text-left text-sm outline-none transition"
                role="button"
                tabindex="0"
                @click="toggle"
                @keydown.enter.prevent="toggle"
                @keydown.escape="open = false"
                @keydown.space.prevent="toggle"
            >
                <span :class="displayValue ? 'admin-heading' : 'admin-muted'">
                    {{ displayValue || placeholder }}
                </span>
                <CalendarDays class="size-4 text-slate-500" :stroke-width="1.7"/>
            </div>

            <button
                v-if="clearable && modelValue"
                aria-label="Effacer la date"
                class="absolute right-9 top-1/2 grid size-5 -translate-y-1/2 place-items-center text-slate-500 hover:text-rose-400"
                type="button"
                @click="clear"
            >
                <X class="size-3.5" :stroke-width="2.2"/>
            </button>

            <div
                v-if="open"
                class="admin-panel absolute z-50 mt-1 w-72 border p-3 shadow-2xl shadow-black/20"
            >
                <div class="mb-2 flex items-center justify-between">
                    <button aria-label="Mois précédent" class="admin-muted admin-hover grid size-7 place-items-center" type="button" @click="prevMonth">
                        <ChevronLeft class="size-4"/>
                    </button>
                    <span class="admin-heading text-sm font-semibold">{{ months[view.getMonth()] }} {{ view.getFullYear() }}</span>
                    <button aria-label="Mois suivant" class="admin-muted admin-hover grid size-7 place-items-center" type="button" @click="nextMonth">
                        <ChevronRight class="size-4"/>
                    </button>
                </div>

                <div class="admin-muted grid grid-cols-7 gap-1 text-center text-[11px] font-medium">
                    <span v-for="day in weekDays" :key="day">{{ day }}</span>
                </div>
                <div class="mt-1 grid grid-cols-7 gap-1">
                    <template v-for="(cell, index) in grid" :key="index">
                        <span v-if="!cell"/>
                        <button
                            v-else
                            :class="isSelected(cell)
                                ? 'bg-[#a23362] font-semibold text-white'
                                : isToday(cell)
                                    ? 'text-[#ef477d] ring-1 ring-[#a23362]/60'
                                    : 'admin-muted admin-hover'"
                            class="grid h-8 place-items-center text-sm transition"
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
