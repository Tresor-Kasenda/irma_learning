<script lang="ts" setup>
import {useForm} from '@inertiajs/vue3';
import {computed} from 'vue';
import InputError from '@/Components/InputError.vue';
import LearningLayout from '@/Layouts/LearningLayout.vue';
import {safeRoute} from '@/utilities/route';

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

const operators = [
    {value: 'orange', label: 'Orange Money'},
    {value: 'airtel', label: 'Airtel Money'},
    {value: 'mpesa', label: 'M-Pesa'},
    {value: 'africell', label: 'Africell Money'},
];

const form = useForm({
    payment_method: 'mobile_money',
    operator: 'orange',
    phone: '',
});

const formattedPrice = computed(() => {
    const amount = Number(props.formation.price ?? 0);

    if (amount <= 0) {
        return 'Gratuit';
    }

    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(amount);
});

function selectMethod(method: 'mobile_money' | 'card'): void {
    form.payment_method = method;
    form.clearErrors();
}

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
                    Choisissez votre moyen de paiement pour débloquer l'accès à cette formation.
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
                    <fieldset>
                        <legend class="text-sm font-semibold text-white">Moyen de paiement</legend>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <button
                                :class="form.payment_method === 'mobile_money'
                                    ? 'border-sky-400/70 bg-sky-400/10'
                                    : 'border-white/10 hover:border-white/25'"
                                class="flex items-center gap-3 border p-3 text-left transition"
                                type="button"
                                @click="selectMethod('mobile_money')"
                            >
                                <span class="grid size-9 shrink-0 place-items-center rounded bg-[#df3e75]/15 text-[#ff79a5]">
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-white">Mobile Money</span>
                                    <span class="block text-xs text-slate-400">Orange, Airtel, M-Pesa…</span>
                                </span>
                            </button>

                            <button
                                :class="form.payment_method === 'card'
                                    ? 'border-sky-400/70 bg-sky-400/10'
                                    : 'border-white/10 hover:border-white/25'"
                                class="flex items-center gap-3 border p-3 text-left transition"
                                type="button"
                                @click="selectMethod('card')"
                            >
                                <span class="grid size-9 shrink-0 place-items-center rounded bg-violet-400/15 text-violet-300">
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 19.5h16.5A2.25 2.25 0 0022.5 17.25V6.75A2.25 2.25 0 0020.25 4.5H3.75A2.25 2.25 0 001.5 6.75v10.5A2.25 2.25 0 003.75 19.5z" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-white">Carte bancaire</span>
                                    <span class="block text-xs text-slate-400">Sécurisé par Stripe</span>
                                </span>
                            </button>
                        </div>
                    </fieldset>

                    <div v-if="form.payment_method === 'mobile_money'" class="mt-6 space-y-4">
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Opérateur</span>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button
                                    v-for="op in operators"
                                    :key="op.value"
                                    :class="form.operator === op.value
                                        ? 'border-sky-400/60 bg-sky-400/10 text-sky-300'
                                        : 'border-white/10 text-slate-400 hover:border-white/25 hover:text-white'"
                                    class="h-9 border px-3 text-xs transition"
                                    type="button"
                                    @click="form.operator = op.value"
                                >
                                    {{ op.label }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300" for="phone">
                                Numéro de téléphone
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                class="mt-1 h-11 w-full border border-white/10 bg-[#0c1a2a] px-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-sky-400/60"
                                inputmode="tel"
                                placeholder="Ex. +243 81 234 5678"
                                type="tel"
                            />
                            <InputError :message="form.errors.phone" class="mt-2"/>
                            <p class="mt-2 text-xs text-slate-500">
                                Vous recevrez une demande de confirmation sur ce numéro.
                            </p>
                        </div>
                    </div>

                    <div v-else class="mt-6 border border-white/10 bg-[#0c1a2a] p-4">
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
