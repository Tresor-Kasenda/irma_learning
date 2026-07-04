<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import {ArrowLeft, Award, BookOpen} from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Student {id: number; name: string; email: string; avatar_url: string; certificates: {id: number; formation: {title: string} | null; number: string; status: string; score: number; issue_date: string}[]; enrollments: {id: number; formation: {title: string} | null; status: string; progress: number}[]}
defineProps<{student: Student}>();
</script>

<template>
    <Head :title="student.name"/>
    <AdminLayout>
        <template #breadcrumb><Link :href="safeRoute('admin.certificates.index')" class="admin-muted">Certificats</Link><span class="admin-faint">/</span><span class="admin-text">{{ student.name }}</span></template>
        <div class="mx-auto grid min-w-0 max-w-6xl gap-6">
            <header class="flex min-w-0 items-center gap-4"><Link :href="safeRoute('admin.certificates.index')" class="admin-divider admin-hover grid size-10 shrink-0 place-items-center border"><ArrowLeft class="size-5"/></Link><img :src="student.avatar_url" alt="" class="size-14 shrink-0 rounded-full object-cover"/><div class="min-w-0"><h1 class="admin-heading truncate text-2xl font-semibold">{{ student.name }}</h1><p class="admin-muted truncate text-sm">{{ student.email }}</p></div></header>
            <div class="grid min-w-0 gap-6 lg:grid-cols-2">
                <section class="admin-panel min-w-0 overflow-hidden border"><div class="admin-divider flex items-center gap-3 border-b p-5"><Award class="size-5 text-[#ef477d]"/><div><h2 class="admin-heading font-semibold">Certificats</h2><p class="admin-muted text-xs">{{ student.certificates.length }} obtenu(s)</p></div></div><div class="divide-y divide-[color:var(--admin-border)]"><article v-for="certificate in student.certificates" :key="certificate.id" class="grid min-w-0 gap-2 p-5"><div class="flex min-w-0 justify-between gap-3"><h3 class="admin-heading min-w-0 break-words font-medium">{{ certificate.formation?.title }}</h3><span class="shrink-0 text-xs text-emerald-400">{{ certificate.status }}</span></div><p class="admin-faint text-xs">{{ certificate.number }} · Score {{ Math.round(certificate.score) }}%</p></article></div></section>
                <section class="admin-panel min-w-0 overflow-hidden border"><div class="admin-divider flex items-center gap-3 border-b p-5"><BookOpen class="size-5 text-sky-400"/><div><h2 class="admin-heading font-semibold">Parcours de formation</h2><p class="admin-muted text-xs">Progression globale de l’étudiant</p></div></div><div class="grid gap-4 p-5"><article v-for="enrollment in student.enrollments" :key="enrollment.id" class="admin-panel-muted min-w-0 border p-4"><div class="flex min-w-0 justify-between gap-3"><h3 class="admin-heading min-w-0 break-words text-sm font-medium">{{ enrollment.formation?.title }}</h3><span class="admin-muted shrink-0 text-xs">{{ enrollment.status }}</span></div><div class="mt-3 flex items-center gap-3"><div class="h-2 flex-1 overflow-hidden bg-slate-500/15"><div class="h-full bg-[#a23362]" :style="{width: `${Math.min(100, enrollment.progress)}%`}"/></div><span class="admin-muted text-xs">{{ Math.round(enrollment.progress) }}%</span></div></article></div></section>
            </div>
        </div>
    </AdminLayout>
</template>
