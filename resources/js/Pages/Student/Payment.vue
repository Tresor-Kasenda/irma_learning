<script lang="ts" setup>
import {useForm} from '@inertiajs/vue3';
import {computed} from 'vue';
import InputError from '@/Components/InputError.vue';
import LearningLayout from '@/Layouts/LearningLayout.vue';
import {safeRoute} from '@/utilities/route';
import {useCurrencyFormatter} from '@/composables/useCurrencyFormatter';

interface Formation {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    image: string | null;
    price: number | null;
    duration_hours: number | null;
    sections_count: number;
}

const props = defineProps<{
    formation: Formation;
}>();
const {formatCurrency} = useCurrencyFormatter();

const form = useForm({
    payment_method: 'card',
});

const formattedPrice = computed(() => {
    const amount = Number(props.formation.price ?? 0);

    if (amount <= 0) {
        return 'Gratuit';
    }

    return formatCurrency(amount);
});

function imageUrl(): string {
    if (!props.formation.image) {
        return '/images/image1.webp';
    }

    if (/^https?:\/\//.test(props.formation.image) || props.formation.image.startsWith('/')) {
        return props.formation.image;
    }

    return `/storage/${props.formation.image}`;
}

function submit(): void {
    form.post(safeRoute('student.payment.create', props.formation.id));
}
</script>

<template>
    <LearningLayout active-item="formations">
        <template #breadcrumb>
            <span class="text-slate-300">Paiement</span>
        </template>

        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <section class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-400">
                    Inscription
                </p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                    Finaliser votre paiement
                </h1>
                <p class="mt-3 text-sm leading-6 text-slate-400">
                    Réglez par carte bancaire pour débloquer l'accès à cette formation.
                </p>
            </section>

            <div class="mt-8 grid gap-5 lg:grid-cols-[1fr_1.2fr]">
                <aside class="border border-white/10 bg-[#101d2d] p-5">
                    <div class="flex items-start gap-4">
                        <img
                            :alt="formation.title"
                            :src="imageUrl()"
                            class="size-20 shrink-0 rounded-lg object-cover"
                        />
                        <div class="min-w-0">
                            <h2 class="line-clamp-2 text-base font-semibold text-white">{{ formation.title }}</h2>
                            <p v-if="formation.short_description" class="mt-1 line-clamp-2 text-xs text-slate-400">
                                {{ formation.short_description }}
                            </p>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-2 border-t border-white/10 pt-4 text-sm">
                        <div class="flex justify-between text-slate-400">
                            <dt>Sections</dt>
                            <dd class="text-slate-200">{{ formation.sections_count }}</dd>
                        </div>
                        <div v-if="formation.duration_hours" class="flex justify-between text-slate-400">
                            <dt>Durée</dt>
                            <dd class="text-slate-200">{{ formation.duration_hours }} h</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4">
                        <span class="text-sm text-slate-400">Total</span>
                        <span class="text-2xl font-semibold text-white">{{ formattedPrice }}</span>
                    </div>
                </aside>

                <form class="border border-white/10 bg-[#101d2d] p-5 sm:p-6" @submit.prevent="submit">
                    <section>
                        <p class="text-sm font-semibold text-white">Moyen de paiement</p>
                        <div class="mt-3 flex items-center gap-3 border border-sky-400/70 bg-sky-400/10 p-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded bg-violet-400/15 text-violet-300">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                    <path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 19.5h16.5A2.25 2.25 0 0022.5 17.25V6.75A2.25 2.25 0 0020.25 4.5H3.75A2.25 2.25 0 001.5 6.75v10.5A2.25 2.25 0 003.75 19.5z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-white">Carte bancaire</span>
                                <span class="block text-xs text-slate-400">Sécurisé par Stripe</span>
                            </span>
                        </div>
                    </section>

                    <div class="mt-6 border border-white/10 bg-[#0c1a2a] p-4">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-violet-300" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                <path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-sm leading-6 text-slate-400">
                                Paiement par carte sécurisé via <span class="font-semibold text-white">Stripe</span>.
                                Vous serez redirigé vers une page de paiement protégée pour saisir les détails de votre carte.
                            </p>
                        </div>
                    </div>

                    <InputError :message="form.errors.payment_method" class="mt-4"/>

                    <button
                        :disabled="form.processing"
                        class="mt-6 flex h-11 w-full items-center justify-center bg-sky-500 px-4 text-sm font-semibold text-white transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-60"
                        type="submit"
                    >
                        {{ form.processing ? 'Traitement…' : `Payer ${formattedPrice}` }}
                    </button>

                    <p class="mt-3 text-center text-xs text-slate-500">
                        Paiement sécurisé · Accès immédiat après confirmation
                    </p>
                </form>
            </div>
        </div>
    </LearningLayout>
</template>
