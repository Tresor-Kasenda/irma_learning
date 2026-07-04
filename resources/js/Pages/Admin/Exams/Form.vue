<script lang="ts" setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ArrowLeft, ClipboardCheck, Save} from '@lucide/vue';
import {computed, ref} from 'vue';
import ExamEditor from '@/Components/Admin/ExamEditor.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {createEmptyExam, type ExamEditorData} from '@/types/admin-exam';
import {safeRoute} from '@/utilities/route';

interface ParentOption {
    value: string;
    label: string;
    group: string;
}

interface ExamData extends ExamEditorData {
    examable_type: string;
    examable_id: number;
}

const props = defineProps<{
    exam: ExamData | null;
    parentOptions: ParentOption[];
}>();

const isEditing = Boolean(props.exam?.id);
const parentValue = ref(props.exam ? `${props.exam.examable_type}:${props.exam.examable_id}` : '');
const form = useForm<ExamData>({
    ...createEmptyExam(),
    ...props.exam,
    examable_type: props.exam?.examable_type ?? '',
    examable_id: props.exam?.examable_id ?? 0,
    questions: props.exam?.questions ?? [],
});

const editorModel = computed<ExamEditorData>({
    get: () => form,
    set: (value) => Object.assign(form, value),
});

const parentOptions = computed(() => props.parentOptions.map((option) => ({
    value: option.value,
    label: `${option.group} · ${option.label}`,
})));

function updateParent(value: unknown): void {
    if (Array.isArray(value)) {
        return;
    }

    parentValue.value = String(value ?? '');
    const separator = parentValue.value.lastIndexOf(':');
    form.examable_type = parentValue.value.slice(0, separator);
    form.examable_id = Number(parentValue.value.slice(separator + 1));
}

function submit(): void {
    const options = {preserveScroll: true};
    if (isEditing && props.exam?.id) {
        form.post(safeRoute('admin.exams.update', props.exam.id), options);
        return;
    }

    form.post(safeRoute('admin.exams.store'), options);
}
</script>

<template>
    <Head :title="isEditing ? 'Modifier l’examen' : 'Nouvel examen'"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.exams.index')" class="admin-muted transition hover:text-[#a23362]">Examens
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text font-medium">{{ isEditing ? 'Modifier' : 'Nouvel examen' }}</span>
        </template>

        <form class="mx-auto grid min-w-0 max-w-6xl gap-6 overflow-x-clip" @submit.prevent="submit">
            <header class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <Link :href="safeRoute('admin.exams.index')" aria-label="Retour"
                          class="admin-divider admin-muted admin-hover grid size-10 shrink-0 place-items-center border transition">
                        <ArrowLeft :stroke-width="1.7" class="size-5"/>
                    </Link>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Évaluations</p>
                        <h1 class="admin-heading mt-1 wrap-break-word text-2xl font-semibold tracking-tight sm:text-3xl">
                            {{ isEditing ? 'Modifier l’examen' : 'Nouvel examen' }}
                        </h1>
                        <p class="admin-muted mt-2 text-sm">Configurez l’évaluation et ses questions dans un espace
                            dédié.</p>
                    </div>
                </div>
            </header>

            <section class="admin-panel min-w-0 overflow-hidden border">
                <div class="admin-divider flex items-center gap-3 border-b px-4 py-4 sm:px-5">
                    <span class="grid size-10 shrink-0 place-items-center bg-violet-400/10 text-violet-400"><ClipboardCheck
                        class="size-5"/></span>
                    <div class="min-w-0">
                        <h2 class="admin-heading font-semibold">Rattachement</h2>
                        <p class="admin-muted mt-0.5 text-xs">Choisissez la section ou la formation évaluée.</p>
                    </div>
                </div>
                <div class="p-4 sm:p-5">
                    <SearchableSelect
                        :error="form.errors.examable_id || form.errors.examable_type"
                        :model-value="parentValue"
                        :options="parentOptions"
                        label="Élément associé"
                        placeholder="Rechercher une section ou une formation…"
                        required
                        @update:model-value="updateParent"
                    />
                </div>
            </section>

            <ExamEditor v-model="editorModel" :errors="form.errors" show-availability/>

            <footer
                class="admin-panel sticky bottom-0 z-20 flex flex-col-reverse gap-3 border p-4 shadow-2xl shadow-black/15 sm:flex-row sm:items-center sm:justify-between">
                <p class="admin-muted text-xs">Les champs marqués d’un <span class="text-[#ef477d]">*</span> sont
                    obligatoires.</p>
                <div class="flex items-center justify-end gap-2">
                    <Link :href="safeRoute('admin.exams.index')"
                          class="admin-divider admin-text admin-hover inline-flex h-11 items-center border px-4 text-sm font-medium transition">
                        Annuler
                    </Link>
                    <button :disabled="form.processing"
                            class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e] disabled:opacity-60"
                            type="submit">
                        <Save class="size-4"/>
                        {{ form.processing ? 'Enregistrement…' : (isEditing ? 'Enregistrer' : 'Créer l’examen') }}
                    </button>
                </div>
            </footer>
        </form>
    </AdminLayout>
</template>
