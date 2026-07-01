<script lang="ts" setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Clock3,
    DollarSign,
    ImageIcon,
    Layers3,
    Plus,
    Save,
    Settings2,
    Sparkles,
    Trash2,
} from '@lucide/vue';
import {computed} from 'vue';
import FileUpload from '@/Components/Admin/FileUpload.vue';
import NumberField from '@/Components/Admin/Fields/NumberField.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import TextareaField from '@/Components/Admin/Fields/TextareaField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {notify} from '@/utilities/toast';

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
const currentImageName = computed(() => props.formation?.image?.split('/').pop() ?? null);

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
        form.post(safeRoute('admin.formations.update', props.formation.id), {
            forceFormData: true,
            onError: () => notify({type: 'error', message: 'Vérifiez les champs signalés avant d’enregistrer.'}),
        });
    } else {
        form.post(safeRoute('admin.formations.store'), {
            forceFormData: true,
            onError: () => notify({type: 'error', message: 'Vérifiez les champs signalés avant de créer la formation.'}),
        });
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier la formation' : 'Nouvelle formation'"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.formations.index')" class="admin-muted transition hover:text-[#a23362]">
                Formations
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text font-medium">{{ isEdit ? 'Modifier' : 'Nouvelle' }}</span>
        </template>

        <form class="mx-auto max-w-7xl" @submit.prevent="submit">
            <div class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-start gap-4">
                    <Link
                        :href="safeRoute('admin.formations.index')"
                        aria-label="Retour aux formations"
                        class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                    >
                        <ArrowLeft class="size-5" :stroke-width="1.7"/>
                    </Link>
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <span class="bg-[#7d254a]/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#7d254a] dark:bg-[#7d254a]/50 dark:text-rose-200">
                                {{ isEdit ? 'Modification' : 'Création' }}
                            </span>
                            <span v-if="form.isDirty" class="text-xs text-amber-300">Modifications non enregistrées</span>
                        </div>
                        <h1 class="admin-heading text-2xl font-semibold tracking-tight sm:text-3xl">
                            {{ isEdit ? 'Modifier la formation' : 'Nouvelle formation' }}
                        </h1>
                        <p class="admin-muted mt-2 max-w-2xl text-sm leading-6">
                            Renseignez les informations visibles dans le catalogue, puis organisez le programme en sections.
                        </p>
                    </div>
                </div>

                <div class="admin-muted flex items-center gap-2 text-xs">
                    <span class="size-2 rounded-full" :class="form.is_active ? 'bg-emerald-400' : 'bg-slate-600'"/>
                    {{ form.is_active ? 'Formation active' : 'Formation masquée' }}
                </div>
            </div>

            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div class="grid gap-6">
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-start gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-[#7d254a]/35 text-[#ef477d]">
                                <BookOpen class="size-5" :stroke-width="1.7"/>
                            </span>
                            <div>
                                <h2 class="admin-heading font-semibold">Contenu de la formation</h2>
                                <p class="admin-muted mt-1 text-xs leading-5">Le titre et les descriptions sont visibles par les apprenants.</p>
                            </div>
                        </div>

                        <div class="grid gap-5 p-5 sm:p-6">
                            <TextField
                                v-model="form.title"
                                :error="form.errors.title"
                                label="Titre de la formation"
                                placeholder="Ex. Développement Web Full Stack"
                                required
                            />
                            <TextareaField
                                v-model="form.short_description"
                                :error="form.errors.short_description"
                                :rows="2"
                                hint="Une phrase claire qui apparaîtra sur les cartes du catalogue."
                                label="Accroche courte"
                                placeholder="Résumez la promesse de la formation."
                            />
                            <TextareaField
                                v-model="form.description"
                                :error="form.errors.description"
                                :rows="5"
                                label="Description détaillée"
                                placeholder="Présentez les objectifs, les acquis et le public concerné."
                                required
                            />
                            <SearchableSelect
                                :model-value="form.tags"
                                hint="Tapez un tag puis Entrée pour l’ajouter."
                                label="Tags et compétences"
                                multiple
                                placeholder="Ajouter des tags…"
                                taggable
                                @update:model-value="form.tags = ($event as string[])"
                            />
                        </div>
                    </section>

                    <section class="admin-panel border">
                        <div class="admin-divider flex flex-col gap-4 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex items-start gap-3">
                                <span class="grid size-10 shrink-0 place-items-center bg-sky-400/10 text-sky-300">
                                    <Layers3 class="size-5" :stroke-width="1.7"/>
                                </span>
                                <div>
                                    <h2 class="admin-heading font-semibold">Programme de la formation</h2>
                                    <p class="admin-muted mt-1 text-xs leading-5">Créez la structure maintenant ; les chapitres seront ajoutés ensuite.</p>
                                </div>
                            </div>
                            <button
                                class="inline-flex h-10 shrink-0 items-center justify-center gap-2 border border-[#a23362] px-4 text-sm font-semibold text-[#7d254a] transition hover:bg-[#7d254a]/10 dark:text-rose-200 dark:hover:bg-[#7d254a]/30"
                                type="button"
                                @click="addSection"
                            >
                                <Plus class="size-4" :stroke-width="2"/>
                                Ajouter une section
                            </button>
                        </div>

                        <div class="grid gap-3 p-5 sm:p-6">
                            <article
                                v-for="(section, index) in form.sections"
                                :key="section.id ?? `new-${index}`"
                                class="admin-panel-muted border"
                            >
                                <div class="admin-divider flex items-center gap-3 border-b px-4 py-3">
                                    <span class="admin-text grid size-7 shrink-0 place-items-center bg-slate-200 text-xs font-bold dark:bg-white/5">
                                        {{ String(index + 1).padStart(2, '0') }}
                                    </span>
                                    <p class="admin-heading min-w-0 flex-1 truncate text-sm font-medium">
                                        {{ section.title || `Section ${index + 1}` }}
                                    </p>
                                    <span v-if="section.chapters_count > 0" class="hidden text-xs text-slate-500 sm:inline">
                                        {{ section.chapters_count }} chapitre(s)
                                    </span>
                                    <button
                                        :aria-label="`Retirer la section ${index + 1}`"
                                        class="grid size-8 shrink-0 place-items-center text-slate-500 transition hover:bg-rose-400/10 hover:text-rose-400"
                                        type="button"
                                        @click="removeSection(index)"
                                    >
                                        <Trash2 class="size-4" :stroke-width="1.8"/>
                                    </button>
                                </div>

                                <div class="grid gap-4 p-4">
                                    <div>
                                        <label :for="`section-title-${index}`" class="admin-muted mb-2 block text-xs font-semibold uppercase tracking-[0.08em]">
                                            Titre de la section <span class="text-[#ef477d]">*</span>
                                        </label>
                                        <input
                                            :id="`section-title-${index}`"
                                            v-model="section.title"
                                            :placeholder="`Ex. Introduction et fondamentaux`"
                                            class="admin-field h-11 w-full border px-3 text-sm outline-none transition"
                                            type="text"
                                        />
                                        <p v-if="sectionError(index)" class="mt-1.5 text-xs text-rose-400">{{ sectionError(index) }}</p>
                                    </div>

                                    <TextareaField
                                        v-model="section.description"
                                        :rows="2"
                                        label="Description de la section"
                                        placeholder="Décrivez brièvement ce que couvre cette partie."
                                    />

                                    <div class="admin-divider flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-end sm:justify-between">
                                        <NumberField
                                            v-model="section.duration"
                                            :icon="Clock3"
                                            :id="`section-duration-${index}`"
                                            :min="0"
                                            label="Durée de la section"
                                            placeholder="0"
                                            suffix="min"
                                        />
                                        <ToggleField v-model="section.is_active" compact label="Section active"/>
                                    </div>

                                    <p v-if="section.chapters_count > 0" class="text-xs leading-5 text-amber-300/80">
                                        Retirer cette section supprimera également ses {{ section.chapters_count }} chapitre(s).
                                    </p>
                                </div>
                            </article>

                            <div v-if="form.sections.length === 0" class="admin-divider border border-dashed px-5 py-10 text-center">
                                <Layers3 class="mx-auto size-8 text-slate-600" :stroke-width="1.5"/>
                                <p class="admin-heading mt-3 text-sm font-medium">Le programme est vide</p>
                                <p class="admin-muted mt-1 text-xs">Ajoutez une première section pour structurer la formation.</p>
                                <button class="mt-4 text-sm font-semibold text-[#ef477d] hover:text-rose-300" type="button" @click="addSection">
                                    Ajouter une section
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="grid gap-6 xl:sticky xl:top-24">
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Settings2 class="size-5 text-[#ef477d]" :stroke-width="1.7"/>
                            <div>
                                <h2 class="admin-heading font-semibold">Publication</h2>
                                <p class="admin-muted mt-0.5 text-xs">Visibilité dans le catalogue</p>
                            </div>
                        </div>
                        <div class="grid gap-3 p-5">
                            <ToggleField
                                v-model="form.is_active"
                                hint="La formation peut être consultée et achetée."
                                label="Formation active"
                            />
                            <ToggleField
                                v-model="form.is_featured"
                                hint="Mettre cette formation en avant sur l’accueil."
                                label="À la une"
                            />
                        </div>
                    </section>

                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Sparkles class="size-5 text-amber-300" :stroke-width="1.7"/>
                            <div>
                                <h2 class="admin-heading font-semibold">Détails du catalogue</h2>
                                <p class="admin-muted mt-0.5 text-xs">Niveau, durée et tarification</p>
                            </div>
                        </div>
                        <div class="grid gap-5 p-5">
                            <SearchableSelect
                                :clearable="false"
                                :error="form.errors.difficulty_level"
                                :model-value="form.difficulty_level"
                                :options="difficultyOptions"
                                label="Niveau"
                                required
                                @update:model-value="form.difficulty_level = ($event as string)"
                            />
                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                                <NumberField
                                    v-model="form.duration_hours"
                                    :error="form.errors.duration_hours"
                                    :icon="Clock3"
                                    id="formation-duration-hours"
                                    :min="0"
                                    label="Durée (heures)"
                                    placeholder="0"
                                    required
                                    suffix="h"
                                />
                                <NumberField
                                    v-model="form.price"
                                    :error="form.errors.price"
                                    :icon="DollarSign"
                                    id="formation-price"
                                    :min="0"
                                    hint="Laissez vide pour une formation gratuite."
                                    label="Prix (USD)"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <ImageIcon class="size-5 text-sky-300" :stroke-width="1.7"/>
                            <div>
                                <h2 class="admin-heading font-semibold">Visuel de couverture</h2>
                                <p class="admin-muted mt-0.5 text-xs">Format horizontal recommandé</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <FileUpload
                                v-model="form.image"
                                :current-name="currentImageName"
                                :current-url="formation?.image ? `/storage/${formation.image}` : null"
                                :error="form.errors.image"
                                :max-size-mb="2"
                                :progress="form.progress?.percentage ?? null"
                                accept="image/*"
                                hint="JPG, PNG ou WebP, 2 Mo maximum."
                            />
                        </div>
                    </section>
                </aside>
            </div>

            <div class="admin-panel sticky bottom-0 z-20 mt-6 flex flex-col-reverse gap-3 border p-4 shadow-2xl shadow-black/15 sm:flex-row sm:items-center sm:justify-between">
                <p class="admin-muted text-xs">
                    Les champs marqués d’un <span class="text-[#ef477d]">*</span> sont obligatoires.
                </p>
                <div class="flex items-center justify-end gap-2">
                    <Link
                        :href="safeRoute('admin.formations.index')"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center border px-4 text-sm font-medium transition"
                    >
                        Annuler
                    </Link>
                    <button
                        :disabled="form.processing"
                        class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e] disabled:cursor-not-allowed disabled:opacity-60"
                        type="submit"
                    >
                        <Save class="size-4" :stroke-width="1.8"/>
                        {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Enregistrer' : 'Créer la formation') }}
                    </button>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
