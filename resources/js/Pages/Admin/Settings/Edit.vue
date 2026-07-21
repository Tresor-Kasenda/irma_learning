<script lang="ts" setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import {Globe2, ImageIcon, Mail, MapPin, Palette, Phone, Save, Settings2, ShieldCheck} from '@lucide/vue';
import {watch} from 'vue';
import FileUpload from '@/Components/Admin/FileUpload.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import TextareaField from '@/Components/Admin/Fields/TextareaField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface CatalogInformationItem {title: string; content: string}
interface Settings {app_name: string; app_tagline: string | null; support_email: string | null; contact_email: string; contact_phone: string; contact_address_primary: string; contact_address_secondary: string; home_hero_title: string; home_hero_subtitle: string; home_features: string[]; logo_path: string | null; logo_url: string; primary_color: string; default_currency: string; allow_registration: boolean; maintenance_message: string | null; certificate_signature_name: string | null; auth_page_subtitle: string; auth_login_title: string; auth_register_title: string; auth_login_subtitle: string; auth_register_subtitle: string; catalog_information_heading: string; catalog_information_items: CatalogInformationItem[]}
const props = defineProps<{settings: Settings}>();
const form = useForm({app_name: props.settings.app_name, app_tagline: props.settings.app_tagline ?? '', support_email: props.settings.support_email ?? '', contact_email: props.settings.contact_email, contact_phone: props.settings.contact_phone, contact_address_primary: props.settings.contact_address_primary, contact_address_secondary: props.settings.contact_address_secondary, home_hero_title: props.settings.home_hero_title, home_hero_subtitle: props.settings.home_hero_subtitle, home_features: [...props.settings.home_features], logo: null as File | null, primary_color: props.settings.primary_color, default_currency: props.settings.default_currency, allow_registration: props.settings.allow_registration, maintenance_message: props.settings.maintenance_message ?? '', certificate_signature_name: props.settings.certificate_signature_name ?? '', auth_page_subtitle: props.settings.auth_page_subtitle, auth_login_title: props.settings.auth_login_title, auth_register_title: props.settings.auth_register_title, auth_login_subtitle: props.settings.auth_login_subtitle, auth_register_subtitle: props.settings.auth_register_subtitle, catalog_information_heading: props.settings.catalog_information_heading, catalog_information_items: props.settings.catalog_information_items.map(item => ({...item}))});
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
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b p-5"><Globe2 class="size-5 text-sky-400"/><div><h2 class="admin-heading font-semibold">Site public</h2><p class="admin-muted text-xs">Accueil et coordonnées visibles dans le pied de page</p></div></div>
                        <div class="grid gap-5 p-5 sm:p-6">
                            <TextField v-model="form.home_hero_title" :error="form.errors.home_hero_title" label="Titre principal de l’accueil" required/>
                            <TextField v-model="form.home_hero_subtitle" :error="form.errors.home_hero_subtitle" label="Sous-titre de l’accueil" required/>
                            <div class="grid gap-4">
                                <TextareaField v-for="(_, index) in form.home_features" :key="index" v-model="form.home_features[index]" :error="form.errors[`home_features.${index}`]" :label="`Présentation ${index + 1}`" :rows="4" required/>
                            </div>
                            <div class="grid gap-5 sm:grid-cols-2">
                                <TextField v-model="form.contact_email" :error="form.errors.contact_email" :icon="Mail" label="E-mail public" type="email"/>
                                <TextField v-model="form.contact_phone" :error="form.errors.contact_phone" :icon="Phone" label="Téléphone public"/>
                            </div>
                            <TextField v-model="form.contact_address_primary" :error="form.errors.contact_address_primary" :icon="MapPin" label="Adresse de Lubumbashi"/>
                            <TextField v-model="form.contact_address_secondary" :error="form.errors.contact_address_secondary" :icon="MapPin" label="Adresse de Kinshasa"/>
                        </div>
                    </section>
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b p-5"><svg class="size-5 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" stroke-linecap="round" stroke-linejoin="round"/></svg><div><h2 class="admin-heading font-semibold">Pages de connexion et inscription</h2><p class="admin-muted text-xs">Textes affichés sur les écrans d'authentification</p></div></div>
                        <div class="grid gap-5 p-5 sm:p-6">
                            <TextField v-model="form.auth_login_title" :error="form.errors.auth_login_title" label="Titre de connexion" hint="Utilisez {app_name} pour afficher automatiquement le nom de l’application."/>
                            <TextField v-model="form.auth_login_subtitle" :error="form.errors.auth_login_subtitle" label="Sous-titre de connexion" placeholder="Connectez-vous pour accéder à votre espace de formation"/>
                            <TextField v-model="form.auth_register_title" :error="form.errors.auth_register_title" label="Titre d’inscription" hint="Utilisez {app_name} pour afficher automatiquement le nom de l’application."/>
                            <TextField v-model="form.auth_register_subtitle" :error="form.errors.auth_register_subtitle" label="Sous-titre d’inscription" placeholder="Créez votre compte et commencez votre parcours"/>
                        </div>
                    </section>
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b p-5"><Globe2 class="size-5 text-sky-400"/><div><h2 class="admin-heading font-semibold">Informations du catalogue</h2><p class="admin-muted text-xs">Accordéons visibles sur la page Formations</p></div></div>
                        <div class="grid gap-5 p-5 sm:p-6">
                            <TextField v-model="form.catalog_information_heading" :error="form.errors.catalog_information_heading" label="Titre de la section" required/>
                            <div v-for="(_, index) in form.catalog_information_items" :key="index" class="admin-panel-muted grid gap-4 border p-4">
                                <p class="admin-heading text-sm font-semibold">Information {{ index + 1 }}</p>
                                <TextField v-model="form.catalog_information_items[index].title" :error="form.errors[`catalog_information_items.${index}.title`]" label="Titre" required/>
                                <TextareaField v-model="form.catalog_information_items[index].content" :error="form.errors[`catalog_information_items.${index}.content`]" :rows="4" label="Contenu" required/>
                            </div>
                        </div>
                    </section>
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b p-5"><svg class="size-5 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" stroke-linecap="round" stroke-linejoin="round"/></svg><div><h2 class="admin-heading font-semibold">Pages de connexion et inscription</h2><p class="admin-muted text-xs">Textes affichés sur les écrans d'authentification</p></div></div>
                        <div class="grid gap-5 p-5 sm:p-6">
                            <TextField v-model="form.auth_page_subtitle" :error="form.errors.auth_page_subtitle" label="Sous-titre général" placeholder="Bâtissez votre avenir professionnel dans le BTP et l'Artisanat"/>
                        </div>
                    </section>
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
