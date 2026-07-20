<script lang="ts" setup>
import {ref} from 'vue';
import {Link} from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

interface Tab {
    id: string;
    text: string;
    title: string;
}

const tabs: Tab[] = [
    {
        id: 'certif-1',
        text: 'Risques Logistiques',
        title: "Maîtriser l'Inattendu : Gestion des Risques Logistiques et Résilience",
    },
    {
        id: 'certif-2',
        text: 'Certification 2',
        title: 'Certification title',
    },
];

const activeTab = ref<string>(tabs[0].id);
</script>

<template>
    <PublicLayout
        title="Tarifs des certifications"
        meta-description="Consultez nos tarifs pour les certifications professionnelles BTP & Artisanat – formules individuelles et équipes."
    >
        <!-- Hero -->
        <section
            class="relative overflow-hidden pt-40 pb-24"
            style="background: linear-gradient(135deg, #0f1f35 0%, #1a3a5c 100%);"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-10"
                style="background-image: radial-gradient(circle at 30% 50%, #df3e75 0%, transparent 60%), radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 50%);"
            />
            <div class="relative mx-auto w-full max-w-7xl px-5 sm:px-10 flex flex-col items-center gap-6 text-center">
                <div class="flex items-center divide-x divide-white/30 text-sm *:px-4 first:*:pl-0 last:*:pr-0">
                    <Link :href="route('home-page')" aria-label="Accueil"
                          class="text-white/70 hover:text-white transition">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                                stroke-linecap="round"
                                stroke-linejoin="round"/>
                        </svg>
                    </Link>
                    <span class="text-irma-primary">Tarifs</span>
                </div>

                <h1 class="text-4xl/tight font-semibold text-white md:text-5xl/tight xl:text-6xl/tight">
                    Certifications
                </h1>
                <p class="max-w-lg text-gray-300">
                    Validez vos compétences et boostez votre carrière grâce à nos certifications reconnues.
                </p>
            </div>
        </section>

        <!-- Tabs + Pricing -->
        <section class="mx-auto w-full max-w-5xl px-5 py-16 sm:px-10">
            <div class="mx-auto mt-10 grid w-full max-w-4xl grid-cols-1 items-stretch gap-6 sm:grid-cols-2">

                <!-- Individuel -->
                <div
                    class="flex flex-col rounded-2xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-100 sm:p-10">
                    <h3 class="text-base/7 font-semibold text-irma-primary">Individuel</h3>
                    <p class="mt-4 flex items-baseline gap-x-2">
                        <span class="text-5xl font-semibold tracking-tight text-gray-900">$150</span>
                        <span class="text-base text-gray-500">/Certification</span>
                    </p>
                    <p class="mt-6 text-base/7 text-gray-600">
                        Idéal pour valider une compétence spécifique à un prix accessible, seul(e).
                    </p>
                    <ul class="mt-8 flex flex-1 flex-col gap-3 text-sm/6 text-gray-600 sm:mt-10">
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Accès à l'examen en ligne
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Résultats instantanés
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Certificat numérique téléchargeable
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Support par e-mail sous 48h
                        </li>
                    </ul>
                    <a
                        class="mt-8 flex h-11 w-full items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-800 transition hover:bg-irma-primary hover:text-white hover:border-irma-primary sm:mt-10"
                        href="#"
                    >
                        Commencer maintenant
                    </a>
                </div>

                <!-- Équipe -->
                <div
                    class="relative flex flex-col rounded-2xl bg-[#0f1f35] p-8 shadow-2xl ring-1 ring-gray-900/10 sm:p-10">
                    <h3 class="text-base/7 font-semibold text-irma-primary">Équipe (3+ personnes)</h3>
                    <p class="mt-4 flex items-baseline gap-x-2">
                        <span class="text-5xl font-semibold tracking-tight text-white">$140</span>
                        <span class="text-base text-gray-400">/Certification</span>
                    </p>
                    <p class="mt-6 text-base/7 text-gray-300">
                        Une formule complète conçue pour les équipes, avec accompagnement et avantages supplémentaires.
                    </p>
                    <ul class="mt-8 flex flex-1 flex-col gap-3 text-sm/6 text-gray-300 sm:mt-10">
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Tous les avantages du plan Individuel
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Session de coaching préparatoire (30 min)
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Support prioritaire (réponse sous 12h)
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="size-4 shrink-0 text-irma-primary" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Repassage gratuit inclus
                        </li>
                    </ul>
                    <a
                        class="mt-8 flex h-11 w-full items-center justify-center rounded-lg bg-irma-primary text-sm font-semibold text-white transition hover:opacity-90 sm:mt-10"
                        href="#"
                    >
                        Commencer maintenant
                    </a>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
