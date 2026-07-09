<script lang="ts" setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import {Head, Link, useForm, usePage} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import {safeRoute} from "@/utilities/route";
import LearningLayout from "@/Layouts/LearningLayout.vue";

interface ProfileFormation {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    image: string | null;
    progress: number;
    status: string;
    last_accessed_at: string | null;
}

interface ProfileCertificate {
    id: number;
    number: string;
    formation_title: string;
    score: number;
    status: string;
    issue_date: string;
}

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    formations: ProfileFormation[];
    certificates: ProfileCertificate[];
}>();

type TabKey = 'formations' | 'certificats' | 'settings';

const page = usePage();
const user = computed(() => page.props.auth.user);

const handle = computed(() => '@' + (user.value.name ?? '').trim().replace(/\s+/g, '-'));

const avatarInput = ref<HTMLInputElement | null>(null);
const avatarForm = useForm<{ avatar: File | null }>({avatar: null});

const selectAvatar = (): void => {
    avatarInput.value?.click();
};

const uploadAvatar = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    avatarForm.avatar = file;
    avatarForm.post(safeRoute('profile.avatar.update'), {
        preserveScroll: true,
        onFinish: () => {
            avatarForm.reset('avatar');

            if (avatarInput.value) {
                avatarInput.value.value = '';
            }
        },
    });
};

const tabs = computed<Array<{ key: TabKey; label: string; count: number | null }>>(() => [
    {key: 'formations', label: 'Formations', count: props.formations.length},
    {key: 'certificats', label: 'Certificats', count: props.certificates.length},
    {key: 'settings', label: 'Paramètres', count: null},
]);

const TAB_STORAGE_KEY = 'profile-active-tab';

const isValidTab = (value: string | null): value is TabKey =>
    tabs.value.some((tab) => tab.key === value);

const storedTab = localStorage.getItem(TAB_STORAGE_KEY);
const activeTab = ref<TabKey>(isValidTab(storedTab) ? storedTab : 'formations');
const filterQuery = ref('');

const filteredFormations = computed(() => props.formations.filter((formation) =>
    formation.title.toLocaleLowerCase().includes(filterQuery.value.toLocaleLowerCase()),
));
const filteredCertificates = computed(() => props.certificates.filter((certificate) =>
    certificate.formation_title.toLocaleLowerCase().includes(filterQuery.value.toLocaleLowerCase()),
));

watch(activeTab, (value) => {
    localStorage.setItem(TAB_STORAGE_KEY, value);
});

const emptyState = computed(() =>
    activeTab.value === 'certificats'
        ? "Vous n'avez aucun certificat."
        : "Vous n'avez aucune formation.",
);
</script>

<template>
    <Head title="Profil"/>

    <LearningLayout>
        <template #breadcrumb>
            <span class="text-slate-300">Profil</span>
        </template>

        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            <!-- Profile header -->
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <div class="relative shrink-0">
                    <img
                        :src="user.avatar_url"
                        alt=""
                        class="size-32 rounded-full object-cover object-top ring-2 ring-white/10"
                    />

                    <input
                        ref="avatarInput"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        type="file"
                        @change="uploadAvatar"
                    />

                    <button
                        :disabled="avatarForm.processing"
                        class="absolute bottom-1 right-1 grid size-9 place-items-center rounded-full border-2 border-[#071525] bg-irma-primary text-white transition hover:opacity-90 disabled:opacity-60"
                        title="Changer la photo de profil"
                        type="button"
                        @click="selectAvatar"
                    >
                        <svg v-if="!avatarForm.processing" class="size-4" fill="none" stroke="currentColor"
                             stroke-width="1.5" viewBox="0 0 24 24">
                            <path
                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"
                                stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg v-else class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="text-3xl font-bold text-white sm:text-4xl">{{ user.name }}</h1>
                    <p class="mt-1 text-base text-slate-400">{{ handle }}</p>
                    <p v-if="avatarForm.errors.avatar" class="mt-1 text-sm text-red-400">
                        {{ avatarForm.errors.avatar }}
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <Link
                            :href="safeRoute('logout')"
                            as="button"
                            class="inline-flex items-center gap-2 rounded border border-[#3a4a63] px-3.5 py-2 text-xs font-semibold uppercase tracking-wide text-[#5e9bff] transition hover:bg-white/5"
                            method="post"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24">
                                <path
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
                                    stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Déconnexion
                        </Link>

                        <button
                            class="inline-flex items-center gap-2 rounded border border-[#3a4a63] px-3.5 py-2 text-xs font-semibold uppercase tracking-wide text-[#5e9bff] transition hover:bg-white/5"
                            type="button"
                            @click="activeTab = 'settings'"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24">
                                <path
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                                    stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Modifier le profil
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-8 border-b border-white/10">
                <nav class="-mb-px flex gap-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        :class="activeTab === tab.key
                            ? 'border-white text-white'
                            : 'border-transparent text-slate-400 hover:text-slate-200'"
                        class="flex items-center gap-1.5 border-b-2 pb-3 text-sm font-medium transition"
                        type="button"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                        <span v-if="tab.count !== null" class="text-xs text-slate-500">{{ tab.count }}</span>
                    </button>
                </nav>
            </div>

            <!-- Formations / Certificats tabs -->
            <div v-if="activeTab !== 'settings'" class="mt-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-1 items-center gap-3">
                        <span
                            class="grid size-9 shrink-0 place-items-center rounded border border-white/10 bg-white/5 text-slate-300">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24">
                                <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <input
                            v-model="filterQuery"
                            class="w-full border-0 bg-transparent p-0 text-sm text-slate-200 placeholder:text-slate-500 focus:outline-none focus:ring-0"
                            :placeholder="`Filtrer ${activeTab === 'formations' ? formations.length : certificates.length} résultat(s)…`"
                            type="text"
                        />
                    </div>

                    <div
                        class="inline-flex items-center gap-2 self-start rounded border border-white/10 px-3 py-1.5 text-sm text-slate-300 sm:self-auto">
                        <span>Dernière modification</span>
                        <button class="text-slate-500 transition hover:text-slate-300" type="button">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24">
                                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button class="text-slate-400 transition hover:text-slate-200" type="button">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24">
                                <path d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-if="activeTab === 'formations' && filteredFormations.length" class="mt-5 grid gap-4 md:grid-cols-2">
                    <article v-for="formation in filteredFormations" :key="formation.id" class="min-w-0 overflow-hidden border border-white/10 bg-[#0e2035]">
                        <div class="flex min-w-0 gap-4 p-4">
                            <img v-if="formation.image" :src="`/storage/${formation.image}`" alt="" class="h-24 w-32 shrink-0 object-cover"/>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#ef477d]">{{ formation.status }}</p>
                                <h3 class="mt-1 break-words font-semibold text-white [overflow-wrap:anywhere]">{{ formation.title }}</h3>
                                <p v-if="formation.short_description" class="mt-1 line-clamp-2 text-xs leading-5 text-slate-400">{{ formation.short_description }}</p>
                            </div>
                        </div>
                        <div class="border-t border-white/10 p-4">
                            <div class="flex items-center justify-between text-xs text-slate-400"><span>Progression</span><span>{{ Math.round(formation.progress) }}%</span></div>
                            <div class="mt-2 h-1.5 overflow-hidden bg-white/10"><div class="h-full bg-[#df3e75]" :style="{width: `${Math.min(100, formation.progress)}%`}"/></div>
                            <Link :href="safeRoute('course.player', formation.id)" class="mt-4 inline-flex h-9 items-center bg-[#a72f5d] px-4 text-xs font-semibold text-white">Continuer la formation</Link>
                        </div>
                    </article>
                </div>

                <div v-else-if="activeTab === 'certificats' && filteredCertificates.length" class="mt-5 grid gap-4 md:grid-cols-2">
                    <Link v-for="certificate in filteredCertificates" :key="certificate.id" :href="safeRoute('certificats.show', certificate.id)" class="border border-emerald-400/20 bg-emerald-400/5 p-5 transition hover:border-emerald-400/50">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-300">{{ certificate.status }}</p>
                        <h3 class="mt-2 break-words font-semibold text-white [overflow-wrap:anywhere]">{{ certificate.formation_title }}</h3>
                        <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-400"><span>{{ certificate.number }}</span><span>Score {{ Math.round(certificate.score) }}%</span><span>{{ new Date(certificate.issue_date).toLocaleDateString('fr-FR') }}</span></div>
                    </Link>
                </div>

                <div v-else class="mt-4 grid place-items-center rounded-lg border border-dashed border-white/10 py-20 text-center">
                    <p class="text-sm text-slate-500">{{ emptyState }}</p>
                </div>
            </div>

            <!-- Settings tab -->
            <div v-else class="mt-6 space-y-6">
                <div class="rounded-lg border border-white/10 bg-[#0e2035] p-4 shadow sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="rounded-lg border border-white/10 bg-[#0e2035] p-4 shadow sm:p-8">
                    <UpdatePasswordForm class="max-w-xl"/>
                </div>

                <div class="rounded-lg border border-white/10 bg-[#0e2035] p-4 shadow sm:p-8">
                    <DeleteUserForm class="max-w-xl"/>
                </div>
            </div>
        </div>
    </LearningLayout>
</template>
