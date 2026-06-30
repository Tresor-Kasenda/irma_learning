<script lang="ts" setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {computed} from 'vue';
import FileUpload from '@/Components/Admin/FileUpload.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import SelectField from '@/Components/Admin/Fields/SelectField.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import TextareaField from '@/Components/Admin/Fields/TextareaField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface SectionData {
    id: number;
    title: string;
    description: string | null;
    duration: number | null;
    is_active: boolean;
    chapters_count: number;
}

interface FormationData {
    id: number;
    title: string;
    short_description: string | null;
    description: string | null;
    image: string | null;
    difficulty_level: string;
    duration_hours: number | null;
    price: number | string | null;
    tags: string[] | null;
    is_active: boolean;
    is_featured: boolean;
    sections: SectionData[];
}

interface SectionRow {
    id: number | null;
    title: string;
    description: string;
    duration: number | null;
    is_active: boolean;
    chapters_count: number;
}

const props = defineProps<{
    formation: FormationData | null;
}>();

const isEdit = computed(() => props.formation !== null);

const difficultyOptions = [
    {value: 'beginner', label: 'Débutant'},
    {value: 'intermediate', label: 'Intermédiaire'},
    {value: 'advanced', label: 'Avancé'},
];

const form = useForm<{
    title: string;
    short_description: string;
    description: string;
    difficulty_level: string;
    duration_hours: number | null;
    price: number | null;
    tags: string[];
    image: File | null;
    is_active: boolean;
    is_featured: boolean;
    sections: SectionRow[];
}>({
    title: props.formation?.title ?? '',
    short_description: props.formation?.short_description ?? '',
    description: props.formation?.description ?? '',
    difficulty_level: props.formation?.difficulty_level ?? 'beginner',
    duration_hours: props.formation?.duration_hours ?? null,
    price: props.formation?.price == null ? null : Number(props.formation.price),
    tags: props.formation?.tags ?? [],
    image: null,
    is_active: props.formation?.is_active ?? true,
    is_featured: props.formation?.is_featured ?? false,
    sections: props.formation?.sections.map((section) => ({
        id: section.id,
        title: section.title,
        description: section.description ?? '',
        duration: section.duration,
        is_active: section.is_active,
        chapters_count: section.chapters_count,
    })) ?? [{id: null, title: '', description: '', duration: null, is_active: true, chapters_count: 0}],
});

function addSection(): void {
    form.sections.push({id: null, title: '', description: '', duration: null, is_active: true, chapters_count: 0});
}

function removeSection(index: number): void {
    form.sections.splice(index, 1);
}

function sectionError(index: number): string | undefined {
    return (form.errors as Record<string, string>)[`sections.${index}.title`];
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        sections: data.sections.filter((section) => section.title.trim() !== ''),
    }));

    if (props.formation) {
        form.post(safeRoute('admin.formations.update', props.formation.id), {forceFormData: true});
    } else {
        form.post(safeRoute('admin.formations.store'), {forceFormData: true});
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier la formation' : 'Nouvelle formation'"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.formations.index')" class="text-slate-500 transition hover:text-slate-800">
                Formations
            </Link>
            <span class="text-slate-300">/</span>
            <span class="font-medium text-slate-700">{{ isEdit ? 'Modifier' : 'Nouvelle' }}</span>
        </template>

        <form class="mx-auto max-w-3xl" @submit.prevent="submit">
            <div class="mb-5 flex items-center gap-3">
                <Link
                    :href="safeRoute('admin.formations.index')"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100"
                >
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </Link>
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ isEdit ? 'Modifier la formation' : 'Nouvelle formation' }}
                </h1>
            </div>

            <div class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
                <TextField v-model="form.title" :error="form.errors.title" label="Titre" required/>
                <TextareaField v-model="form.short_description" :error="form.errors.short_description" :rows="2" label="Description courte"/>
                <TextareaField v-model="form.description" :error="form.errors.description" :rows="5" label="Description" required/>

                <div class="grid gap-4 sm:grid-cols-2">
                    <SelectField v-model="form.difficulty_level" :error="form.errors.difficulty_level" :options="difficultyOptions" label="Niveau" required/>
                    <TextField v-model="form.duration_hours" :error="form.errors.duration_hours" label="Durée (heures)" required type="number"/>
                </div>

                <TextField v-model="form.price" :error="form.errors.price" hint="Vide = gratuit." label="Prix (USD)" type="number"/>

                <SearchableSelect
                    :model-value="form.tags"
                    hint="Tapez un tag puis Entrée pour l'ajouter."
                    label="Tags"
                    multiple
                    placeholder="Ajouter des tags…"
                    taggable
                    @update:model-value="form.tags = ($event as string[])"
                />

                <FileUpload
                    v-model="form.image"
                    :current-url="formation?.image ? `/storage/${formation.image}` : null"
                    :error="form.errors.image"
                    :max-size-mb="2"
                    :progress="form.progress?.percentage ?? null"
                    accept="image/*"
                    label="Image de couverture"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <ToggleField v-model="form.is_active" label="Active"/>
                    <ToggleField v-model="form.is_featured" label="Mise en avant"/>
                </div>
            </div>

            <!-- Sections -->
            <div class="mt-5 rounded-xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Sections</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Définissez le programme. Les chapitres se gèrent ensuite dans chaque section.</p>
                    </div>
                    <button
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-[#bf045b]/40 px-3 text-sm font-medium text-[#bf045b] hover:bg-[#bf045b]/5"
                        type="button"
                        @click="addSection"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                        </svg>
                        Ajouter
                    </button>
                </div>

                <div class="mt-4 grid gap-3">
                    <div
                        v-for="(section, index) in form.sections"
                        :key="index"
                        class="rounded-lg border border-slate-200 p-3"
                    >
                        <div class="flex items-start gap-2">
                            <span class="mt-2 grid size-7 shrink-0 place-items-center rounded-md bg-slate-100 text-xs font-semibold text-slate-500">
                                {{ index + 1 }}
                            </span>
                            <input
                                v-model="section.title"
                                :placeholder="`Titre de la section ${index + 1}`"
                                class="h-10 min-w-0 flex-1 rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-[#bf045b]"
                                type="text"
                            />
                            <button
                                class="mt-1.5 grid size-7 shrink-0 place-items-center rounded-md text-slate-400 hover:bg-red-50 hover:text-red-500"
                                title="Retirer"
                                type="button"
                                @click="removeSection(index)"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="sectionError(index)" class="ml-9 mt-1 text-xs text-red-600">{{ sectionError(index) }}</p>

                        <div class="ml-9 mt-2 space-y-2">
                            <textarea
                                v-model="section.description"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#bf045b]"
                                placeholder="Description (optionnelle)"
                                rows="2"
                            />
                            <div class="flex flex-wrap items-center gap-4">
                                <input
                                    v-model.number="section.duration"
                                    class="h-9 w-36 rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-[#bf045b]"
                                    min="0"
                                    placeholder="Durée (min)"
                                    type="number"
                                />
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input v-model="section.is_active" class="rounded border-slate-300" type="checkbox"/>
                                    Active
                                </label>
                                <span v-if="section.chapters_count > 0" class="text-xs text-slate-400">
                                    {{ section.chapters_count }} chapitre(s) — supprimés si la section est retirée.
                                </span>
                            </div>
                        </div>
                    </div>

                    <p v-if="form.sections.length === 0" class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center text-sm text-slate-400">
                        Aucune section. Cliquez sur « Ajouter ».
                    </p>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <Link
                    :href="safeRoute('admin.formations.index')"
                    class="inline-flex h-10 items-center rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 hover:bg-slate-100"
                >
                    Annuler
                </Link>
                <button
                    :disabled="form.processing"
                    class="inline-flex h-10 items-center rounded-lg bg-[#bf045b] px-5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                    type="submit"
                >
                    {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Enregistrer les modifications' : 'Créer la formation') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
