<script lang="ts" setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import {ImageIcon, Mail, Palette, Save, Settings2, ShieldCheck} from '@lucide/vue';
import {watch} from 'vue';
import FileUpload from '@/Components/Admin/FileUpload.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import TextareaField from '@/Components/Admin/Fields/TextareaField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Settings {app_name: string; app_tagline: string | null; support_email: string | null; logo_path: string | null; logo_url: string; primary_color: string; default_currency: string; allow_registration: boolean; maintenance_message: string | null; certificate_signature_name: string | null}
const props = defineProps<{settings: Settings}>();
const form = useForm({app_name: props.settings.app_name, app_tagline: props.settings.app_tagline ?? '', support_email: props.settings.support_email ?? '', logo: null as File | null, primary_color: props.settings.primary_color, default_currency: props.settings.default_currency, allow_registration: props.settings.allow_registration, maintenance_message: props.settings.maintenance_message ?? '', certificate_signature_name: props.settings.certificate_signature_name ?? ''});
const currencies = [{value: 'USD', label: 'USD — Dollar américain'}, {value: 'CDF', label: 'CDF — Franc congolais'}, {value: 'EUR', label: 'EUR — Euro'}];
watch(() => form.primary_color, (color) => {
    if (/^#[0-9a-f]{6}$/i.test(color)) {
        document.documentElement.style.setProperty('--irma-primary', color);
    }
}, {immediate: true});
function submit(): void {
    form.post(safeRoute('admin.settings.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('logo');
            router.reload({only: ['settings', 'appSettings']});
        },
    });
}
</script>

<template>
    <Head title="Paramètres système"/>
    <AdminLayout>
        <template #breadcrumb><span class="admin-text font-medium">Paramètres</span></template>
        <form class="mx-auto grid min-w-0 max-w-6xl gap-6" @submit.prevent="submit">
            <header><p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Configuration</p><h1 class="admin-heading mt-2 text-2xl font-semibold sm:text-3xl">Paramètres système</h1><p class="admin-muted mt-2 max-w-2xl text-sm">Personnalisez l’identité de la plateforme, les contacts, les inscriptions et les certificats.</p></header>
            <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="grid min-w-0 gap-6">
                    <section class="admin-panel min-w-0 overflow-hidden border"><div class="admin-divider flex items-center gap-3 border-b p-5"><Settings2 class="size-5 text-[#ef477d]"/><div><h2 class="admin-heading font-semibold">Identité de l’application</h2><p class="admin-muted text-xs">Nom, promesse et assistance</p></div></div><div class="grid gap-5 p-5 sm:p-6"><TextField v-model="form.app_name" :error="form.errors.app_name" label="Nom de l’application" required/><TextField v-model="form.app_tagline" :error="form.errors.app_tagline" label="Signature" placeholder="Développez vos compétences professionnelles"/><TextField v-model="form.support_email" :error="form.errors.support_email" :icon="Mail" label="E-mail du support" type="email" placeholder="support@irma.cd"/></div></section>
                    <section class="admin-panel min-w-0 overflow-hidden border"><div class="admin-divider flex items-center gap-3 border-b p-5"><ShieldCheck class="size-5 text-emerald-400"/><div><h2 class="admin-heading font-semibold">Accès et communication</h2><p class="admin-muted text-xs">Comptes, maintenance et certificats</p></div></div><div class="grid gap-5 p-5 sm:p-6"><ToggleField v-model="form.allow_registration" hint="Autoriser la création autonome de comptes apprenants." label="Inscriptions publiques"/><TextareaField v-model="form.maintenance_message" :error="form.errors.maintenance_message" :rows="3" label="Message de maintenance" placeholder="Laissez vide en fonctionnement normal."/><TextField v-model="form.certificate_signature_name" :error="form.errors.certificate_signature_name" label="Signataire des certificats" placeholder="Nom et fonction du responsable"/></div></section>
                </div>
                <aside class="grid min-w-0 content-start gap-6 lg:sticky lg:top-24">
                    <section class="admin-panel min-w-0 overflow-hidden border"><div class="admin-divider flex items-center gap-3 border-b p-5"><ImageIcon class="size-5 text-sky-400"/><h2 class="admin-heading font-semibold">Logo</h2></div><div class="grid gap-4 p-5"><div class="admin-panel-muted grid min-h-36 place-items-center border p-5"><img :src="settings.logo_url" alt="Logo actuel" class="max-h-24 max-w-full object-contain"/></div><FileUpload v-model="form.logo" :current-url="settings.logo_url" :error="form.errors.logo" :max-size-mb="2" accept="image/png,image/jpeg,image/webp,image/svg+xml" hint="PNG, JPG, WebP ou SVG. 2 Mo maximum." label="Nouveau logo"/></div></section>
                    <section class="admin-panel relative z-30 min-w-0 overflow-visible border"><div class="admin-divider flex items-center gap-3 border-b p-5"><Palette class="size-5 text-amber-400"/><h2 class="admin-heading font-semibold">Affichage et devise</h2></div><div class="grid gap-5 p-5"><label><span class="admin-muted mb-2 block text-xs font-semibold uppercase tracking-[0.08em]">Couleur principale</span><div class="admin-field flex h-11 items-center gap-3 border px-3"><input v-model="form.primary_color" type="color" class="size-7 border-0 bg-transparent"/><input v-model="form.primary_color" class="admin-heading min-w-0 flex-1 bg-transparent text-sm outline-none"/></div><p v-if="form.errors.primary_color" class="mt-1 text-xs text-rose-400">{{ form.errors.primary_color }}</p></label><SearchableSelect v-model="form.default_currency" :clearable="false" :options="currencies" :searchable="false" label="Devise par défaut"/></div></section>
                </aside>
            </div>
            <footer class="admin-panel sticky bottom-0 z-20 flex justify-end border p-4"><button :disabled="form.processing" class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white disabled:opacity-60" type="submit"><Save class="size-4"/>{{ form.processing ? 'Enregistrement…' : 'Enregistrer les paramètres' }}</button></footer>
        </form>
    </AdminLayout>
</template>
