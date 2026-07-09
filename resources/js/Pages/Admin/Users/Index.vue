<script lang="ts" setup>
import {Head, useForm} from '@inertiajs/vue3';
import {Pencil, Plus, ShieldCheck, Users} from '@lucide/vue';
import {ref} from 'vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import ResourceFormModal from '@/Components/Admin/ResourceFormModal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Option {value: string; label: string}
interface UserRow {id: number; name: string; email: string; avatar_url: string; role: string; role_label: string; status: string; status_label: string; must_change_password: boolean; enrollments_count: number; certificates_count: number; created_at: string}
interface PageData {data: UserRow[]; from: number | null; to: number | null; total: number; links: {url: string | null; label: string; active: boolean}[]}

const props = defineProps<{users: PageData; filters: Record<string, string | undefined>; roleOptions: Option[]; assignableRoleOptions: Option[]; statusOptions: Option[]; canManageRoot: boolean}>();
const selectedUser = ref<UserRow | null>(null);
const showCreate = ref(false);
const form = useForm({role: 'student', status: 'active', must_change_password: false});
const createForm = useForm({name: '', email: '', password: '', password_confirmation: '', role: 'student', status: 'active', must_change_password: true});
const columns: Column[] = [{key: 'name', label: 'Utilisateur'}, {key: 'role', label: 'Rôle'}, {key: 'status', label: 'Accès'}, {key: 'enrollments_count', label: 'Inscriptions'}, {key: 'certificates_count', label: 'Certificats'}];
const filterDefs: FilterDef[] = [
    {key: 'role', label: 'Rôle', options: props.roleOptions},
    {key: 'status', label: 'Statut', options: props.statusOptions},
    {key: 'per_page', label: 'Lignes', options: [{value: '10', label: '10 lignes'}, {value: '25', label: '25 lignes'}, {value: '50', label: '50 lignes'}], includeEmpty: false, defaultValue: '10'},
];

function openEditor(user: UserRow): void {
    selectedUser.value = user;
    form.role = user.role;
    form.status = user.status;
    form.must_change_password = user.must_change_password;
    form.clearErrors();
}

function updateUser(): void {
    if (!selectedUser.value) return;
    form.patch(safeRoute('admin.users.update', selectedUser.value.id), {preserveScroll: true, onSuccess: () => selectedUser.value = null});
}

function openCreate(): void {
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
}

function createUser(): void {
    createForm.post(safeRoute('admin.users.store'), {preserveScroll: true, onSuccess: () => showCreate.value = false});
}
</script>

<template>
    <Head title="Utilisateurs"/>
    <AdminLayout>
        <template #breadcrumb><span class="admin-text font-medium">Utilisateurs</span></template>
        <div class="mx-auto min-w-0 max-w-7xl">
            <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Administration</p><h1 class="admin-heading mt-2 text-2xl font-semibold sm:text-3xl">Utilisateurs et accès</h1><p class="admin-muted mt-2 text-sm">{{ users.total }} compte(s). Les rôles administrateur et root donnent accès à /manage.</p></div>
                <div class="flex items-center gap-3">
                    <div class="admin-panel-muted flex items-center gap-3 border px-4 py-3"><ShieldCheck class="size-5 text-emerald-400"/><p class="admin-muted text-xs"><strong class="admin-heading block">Contrôle d’accès</strong>Étudiant : learning · Instructeur : contenu · Admin/Root : gestion</p></div>
                    <button class="inline-flex h-11 shrink-0 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white" type="button" @click="openCreate"><Plus class="size-4"/>Créer un utilisateur</button>
                </div>
            </header>
            <DataTable :columns="columns" :filters="filters" :index-route="safeRoute('admin.users.index')" :rows="users" searchable>
                <template #filters><FilterBar :definitions="filterDefs" :filters="filters" :index-route="safeRoute('admin.users.index')"/></template>
                <template #cell-name="{row}"><div class="flex min-w-0 items-center gap-3"><img :src="row.avatar_url" alt="" class="size-10 shrink-0 rounded-full object-cover"/><div class="min-w-0"><p class="admin-heading truncate text-sm font-medium">{{ row.name }}</p><p class="admin-muted truncate text-xs">{{ row.email }}</p></div></div></template>
                <template #cell-role="{row}"><span class="admin-panel-muted px-2 py-1 text-xs font-semibold">{{ row.role_label }}</span></template>
                <template #cell-status="{row}"><span :class="row.status === 'active' ? 'bg-emerald-400/10 text-emerald-400' : 'bg-rose-400/10 text-rose-400'" class="px-2 py-1 text-xs font-semibold">{{ row.status_label }}</span></template>
                <template #actions="{row}"><button v-if="row.role !== 'root' || canManageRoot" class="admin-muted admin-hover grid size-9 place-items-center" title="Modifier les accès" type="button" @click="openEditor(row)"><Pencil class="size-4"/></button></template>
            </DataTable>
        </div>

        <ResourceFormModal :show="Boolean(selectedUser)" :processing="form.processing" title="Modifier les accès" submit-label="Enregistrer les accès" @close="selectedUser = null" @submit="updateUser">
            <div v-if="selectedUser" class="grid gap-5">
                <div class="admin-panel-muted flex min-w-0 items-center gap-3 border p-4"><img :src="selectedUser.avatar_url" alt="" class="size-12 rounded-full object-cover"/><div class="min-w-0"><p class="admin-heading truncate font-semibold">{{ selectedUser.name }}</p><p class="admin-muted truncate text-sm">{{ selectedUser.email }}</p></div></div>
                <SearchableSelect v-model="form.role" :clearable="false" :error="form.errors.role" :options="assignableRoleOptions" :searchable="false" label="Rôle" required/>
                <SearchableSelect v-model="form.status" :clearable="false" :error="form.errors.status" :options="statusOptions" :searchable="false" label="Statut du compte" required/>
                <ToggleField v-model="form.must_change_password" hint="L’utilisateur devra définir un nouveau mot de passe à sa prochaine connexion." label="Forcer le changement de mot de passe"/>
            </div>
        </ResourceFormModal>

        <ResourceFormModal :show="showCreate" :processing="createForm.processing" title="Créer un utilisateur" submit-label="Créer l’utilisateur" @close="showCreate = false" @submit="createUser">
            <div class="grid gap-5">
                <TextField v-model="createForm.name" :error="createForm.errors.name" label="Nom complet" required/>
                <TextField v-model="createForm.email" :error="createForm.errors.email" label="E-mail" type="email" required/>
                <TextField v-model="createForm.password" :error="createForm.errors.password" hint="8 caractères minimum." label="Mot de passe" type="password" required/>
                <TextField v-model="createForm.password_confirmation" label="Confirmer le mot de passe" type="password" required/>
                <SearchableSelect v-model="createForm.role" :clearable="false" :error="createForm.errors.role" :options="assignableRoleOptions" :searchable="false" label="Rôle" required/>
                <SearchableSelect v-model="createForm.status" :clearable="false" :error="createForm.errors.status" :options="statusOptions" :searchable="false" label="Statut du compte" required/>
                <ToggleField v-model="createForm.must_change_password" hint="L’utilisateur devra définir un nouveau mot de passe à sa première connexion." label="Forcer le changement de mot de passe"/>
            </div>
        </ResourceFormModal>
    </AdminLayout>
</template>
