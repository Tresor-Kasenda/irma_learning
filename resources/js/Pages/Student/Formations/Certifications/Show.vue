<script lang="ts" setup>
import {Head, Link, router} from '@inertiajs/vue3';
import {computed} from 'vue';
import LearningLayout from '@/Layouts/LearningLayout.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import {safeRoute} from '@/utilities/route';

interface CertificateShow {
    id: number;
    certificate_number: string;
    final_score?: number | string | null;
    issue_date?: string | null;
    expiry_date?: string | null;
    verification_hash: string;
    download_url: string;
    verification_url: string;
    status: string;
    formation: { id: number; title: string; slug: string } | null;
    user: { id: number; name: string } | null;
}

const props = defineProps<{
    certificate: CertificateShow;
}>();

const score = computed(() => Math.round(Number(props.certificate.final_score ?? 0)));

function formatDate(value?: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function printCertificate(): void {
    window.print();
}

function downloadCertificate(): void {
    router.post(props.certificate.download_url);
}
</script>

<template>
    <Head title="Certification"/>

    <LearningLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('certificats')" class="text-slate-500 transition hover:text-slate-300">
                Certifications
            </Link>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300">{{ certificate.certificate_number }}</span>
        </template>

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
            <div class="mb-6 flex items-center justify-between gap-4 print:hidden">
                <Link
                    :href="safeRoute('certificats')"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white"
                >
                    <LearningIcon class="size-4 brightness-0 invert opacity-70" name="arrow-left"/>
                    Retour aux certifications
                </Link>
                <div class="flex gap-2">
                    <button
                        class="inline-flex h-10 items-center gap-2 border border-white/15 px-4 text-sm font-semibold text-white transition hover:bg-white/5"
                        type="button"
                        @click="printCertificate"
                    >
                        <LearningIcon class="size-4 brightness-0 invert opacity-80" name="document-text"/>
                        Imprimer
                    </button>
                    <button
                        class="inline-flex h-10 items-center gap-2 bg-irma-primary px-4 text-sm font-semibold text-white transition hover:opacity-90"
                        type="button"
                        @click="downloadCertificate"
                    >
                        <LearningIcon class="size-4 brightness-0 invert opacity-80" name="arrow-down-tray"/>
                        Télécharger
                    </button>
                </div>
            </div>

            <!-- Certificat -->
            <article
                class="relative overflow-hidden border border-[#caa45a]/40 bg-gradient-to-b from-[#11233a] to-[#0a1726] p-8 text-center sm:p-12"
            >
                <div class="pointer-events-none absolute inset-3 border border-[#caa45a]/20"/>

                <div class="relative">
                    <img alt="IRMA" class="mx-auto h-12 w-auto" src="/images/irma-logo-base.svg"/>

                    <p class="mt-6 text-xs font-semibold uppercase tracking-[0.3em] text-[#caa45a]">
                        Certificat de réussite
                    </p>

                    <p class="mt-6 text-sm text-slate-400">Décerné à</p>
                    <h1 class="mt-1 text-3xl font-semibold text-white sm:text-4xl">
                        {{ certificate.user?.name ?? 'Apprenant' }}
                    </h1>

                    <p class="mx-auto mt-6 max-w-lg text-sm leading-6 text-slate-400">
                        pour avoir complété avec succès la formation
                    </p>
                    <h2 class="mt-1 text-xl font-medium text-[#ff79a5] sm:text-2xl">
                        {{ certificate.formation?.title ?? 'Formation' }}
                    </h2>

                    <div class="mx-auto mt-8 flex max-w-md flex-wrap items-stretch justify-center gap-4">
                        <div class="min-w-[120px] flex-1 border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Score</p>
                            <p class="mt-1 text-lg font-semibold text-emerald-300">{{ score }}%</p>
                        </div>
                        <div class="min-w-[120px] flex-1 border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Délivré le</p>
                            <p class="mt-1 text-sm font-semibold text-white">{{ formatDate(certificate.issue_date) }}</p>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-center gap-2">
                        <LearningIcon class="size-5 brightness-0 invert opacity-80" name="academic-cap"/>
                        <span class="text-sm font-semibold text-white">IRMA Learning</span>
                    </div>

                    <div class="mt-8 border-t border-white/10 pt-5 text-xs text-slate-500">
                        <p>N° de certificat : <span class="font-mono text-slate-300">{{ certificate.certificate_number }}</span></p>
                        <p class="mt-1 break-all">
                            Vérification : <span class="font-mono text-slate-400">{{ certificate.verification_hash }}</span>
                        </p>
                    </div>
                </div>
            </article>
        </div>
    </LearningLayout>
</template>
