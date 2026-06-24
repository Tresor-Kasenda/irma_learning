<script lang="ts" setup>
import {Link} from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    formation?: {
        id: number;
        title: string;
        slug: string;
        short_description: string | null;
        image: string | null;
        price: number;
        duration_hours: number;
    } | null;
}>();

function formatPrice(price: number): string {
    if (price <= 0) return 'Gratuit';
    return new Intl.NumberFormat('fr-CD', {style: 'currency', currency: 'USD'}).format(price);
}
</script>

<template>
    <PublicLayout title="Acceuil | Home Page">
        <section class="pt-32">
            <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col lg:flex-row gap-16">
                <div
                    class="lg:w-1/2 lg:py-12 xl:py-20 space-y-7 flex flex-col items-center lg:items-start text-center lg:text-left">
                    <h1 class="font-medium text-3xl md:text-4xl text-gray-900">
                        Devenez Leader du Risque en RD Congo avec l'iRMA
                    </h1>
                    <p class="text-gray-700">L'iRMA offre 3 types de formation</p>
                    <ul class="text-gray-600 grid gap-y-4">
                        <li class="flex items-start">
                            <svg class="size-5 mr-3 mt-0.5 text-irma-primary shrink-0" fill="none" stroke="currentColor"
                                 stroke-width="3" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Les certifications professionnelles à travers des formations menant à des
                                Titres Professionnels qui témoignent de l'autorité et de la crédibilité
                                professionnelles des membres. Chaque membre doit totaliser au moins 25 points
                                DPC par période de douze mois afin de conserver son titre professionnel.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="size-5 mr-3 mt-0.5 text-irma-primary shrink-0" fill="none" stroke="currentColor"
                                 stroke-width="3" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>La Formation Continue à travers de courts programmes qui permettent aux
                                membres d'acquérir, d'actualiser ou d'améliorer rapidement leur compétence
                                tout au long de leur vie professionnelle. Chaque membre doit cumuler au moins
                                20 Unités de Formation Continue (UFC) par période de référence de 12 mois.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="size-5 mr-3 mt-0.5 text-irma-primary shrink-0" fill="none" stroke="currentColor"
                                 stroke-width="3" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>La Formation en Entreprise livrées en respectant les besoins spécifiques
                                des sociétés à travers une démarche partenariale sur mesure.</span>
                        </li>
                    </ul>
                    <Link :href="route('certifications')"
                          class="mt-8 inline-flex items-center gap-2 rounded-lg bg-irma-primary px-6 py-3 text-white font-medium hover:opacity-90 transition-opacity">
                        Voir Nos Certifications
                        <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd"
                                  d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z"
                                  fill-rule="evenodd"/>
                        </svg>
                    </Link>
                </div>

                <div v-if="formation"
                     class="max-w-4xl flex lg:flex-1 relative before:absolute before:inset-y-3 md:before:inset-y-10 before:border-y before:border-gray-200 after:border-gray-200 before:w-full after:absolute after:inset-x-3 md:after:inset-x-10 after:border-x after:h-full">
                    <div class="flex items-center justify-center relative px-4 py-6 w-full h-full">
                        <div
                            class="w-full flex flex-col max-w-sm bg-white/80 backdrop-blur-lg border border-gray-200/30 rounded-lg shadow-sm p-6 relative z-5">
                            <img
                                v-if="formation.image"
                                :alt="formation.title"
                                :src="`/storage/${formation.image}`"
                                class="w-full aspect-video rounded-md object-cover"
                            />
                            <div class="mt-6">
                                <h2 class="font-semibold text-gray-900 text-xl line-clamp-2">
                                    {{ formation.title }}
                                </h2>
                                <p class="mt-4 line-clamp-2 text-gray-700 text-sm">
                                    {{ formation.short_description?.substring(0, 98) }}
                                </p>
                                <div class="flex items-center justify-between mt-4">
                                    <span class="text-base font-bold text-gray-900">
                                        {{ formatPrice(formation.price) }}
                                    </span>
                                    <span class="flex items-center gap-1.5 text-sm font-medium text-gray-700">
                                        <svg class="size-4" fill="none" stroke="currentColor"
                                             stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                        </svg>
                                        {{ formation.duration_hours }}h
                                    </span>
                                </div>
                                <div class="mt-5">
                                    <Link :href="route('formation.show', formation.slug)"
                                          class="flex w-full justify-center rounded-lg bg-irma-primary px-4 py-2.5 text-sm font-medium text-white hover:opacity-90 transition-opacity">
                                        En Savoir Plus
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
