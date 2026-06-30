<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import LearningLayout from '@/Layouts/LearningLayout.vue';
import {safeRoute} from '@/utilities/route';

interface CertificateFormation {
    id: number;
    title: string;
    slug: string;
    difficulty_level?: string | null;
}

interface Certificate {
    id: number;
    certificate_number: string;
    final_score: number | string | null;
    issue_date: string | null;
    status: string;
    formation: CertificateFormation | null;
}

const props = defineProps<{
    certificates: Certificate[];
}>();

function score(certificate: Certificate): number {
    return Math.round(Number(certificate.final_score ?? 0));
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Mes certifications"/>

    <LearningLayout active-item="certified">
        <template #breadcrumb>
            <span class="text-slate-300">Certifications</span>
        </template>

        <div class="mx-auto max-w-[1540px] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <section class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#caa45a]">
                    IRMA Learning
                </p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Vos certifications
                </h1>
                <p class="mt-3 text-sm leading-6 text-slate-400 sm:text-base">
                    Retrouvez les certificats des formations que vous avez réussies. Chaque certificat est vérifiable
                    par son numéro unique.
                </p>
            </section>

            <section class="mt-9">
                <div
                    v-if="certificates.length > 0"
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <Link
                        v-for="certificate in certificates"
                        :key="certificate.id"
                        :href="safeRoute('certificats.show', certificate.id)"
                        class="group flex flex-col border border-[#caa45a]/30 bg-gradient-to-b from-[#11233a] to-[#0b1827] p-5 transition hover:-translate-y-0.5 hover:border-[#caa45a]/60 hover:shadow-2xl"
                    >
                        <div class="flex items-center justify-between">
                            <span class="grid size-11 place-items-center border border-[#caa45a]/40 bg-[#caa45a]/10 text-[#caa45a]">
                                <LearningIcon class="size-5 brightness-0 invert opacity-80" name="academic-cap"/>
                            </span>
                            <span class="bg-emerald-400/15 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-300">
                                Réussi
                            </span>
                        </div>

                        <h2 class="mt-5 line-clamp-2 text-lg font-semibold text-white transition group-hover:text-[#ff79a5]">
                            {{ certificate.formation?.title ?? 'Formation' }}
                        </h2>

                        <dl class="mt-auto grid grid-cols-2 gap-3 pt-6 text-sm">
                            <div>
                                <dt class="text-[11px] uppercase tracking-wide text-slate-500">Score</dt>
                                <dd class="mt-1 text-base font-semibold text-emerald-300">{{ score(certificate) }}%</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] uppercase tracking-wide text-slate-500">Délivré le</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-200">{{ formatDate(certificate.issue_date) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4">
                            <span class="font-mono text-[11px] text-slate-500">{{ certificate.certificate_number }}</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#caa45a]">
                                Voir le certificat
                                <LearningIcon class="size-3.5 brightness-0 invert opacity-80" name="arrow-right"/>
                            </span>
                        </div>
                    </Link>
                </div>

                <div
                    v-else
                    class="flex min-h-72 flex-col items-center justify-center border border-dashed border-white/15 bg-[#0c1b2c] px-5 text-center"
                >
                    <LearningIcon class="size-10 brightness-0 invert opacity-30" name="academic-cap"/>
                    <h2 class="mt-5 text-lg font-semibold text-white">Aucune certification pour le moment</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-400">
                        Terminez une formation et réussissez l'examen de chaque section pour obtenir votre certificat.
                    </p>
                    <Link
                        :href="safeRoute('student.learnings')"
                        class="mt-5 inline-flex h-10 items-center bg-sky-500 px-4 text-sm font-semibold text-white transition hover:bg-sky-400"
                    >
                        Explorer les formations
                    </Link>
                </div>
            </section>
        </div>
    </LearningLayout>
</template>
