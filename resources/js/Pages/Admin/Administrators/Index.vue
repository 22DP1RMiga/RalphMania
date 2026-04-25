<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAdminPermission } from '@/Composables/useAdminPermission.js';

const { t, locale } = useI18n({ useScope: 'global' });
const {
    can, openUnauthorized, UnauthorizedModal,
    actionBtnClass, actionBtnStyle, noPermTitle,
} = useAdminPermission();

const props = defineProps({
    administrators: Object,
    filters: Object,
    stats: Object,
    availablePermissions: Array,
    permissionGroups: Object,
    availableUsers: Array,
});

// ── Toast ──────────────────────────────────────────────────────
const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer;
const showToast = (message, type = 'success') => {
    clearTimeout(toastTimer);
    toast.value = { show: true, message, type };
    toastTimer = setTimeout(() => { toast.value.show = false; }, 3000);
};

// ── Search / Filter ────────────────────────────────────────────
const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');
let searchTimer;
const doSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get('/admin/administrators', {
            search: search.value || undefined,
            type: typeFilter.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
};

// ── Add Admin Modal ────────────────────────────────────────────
const addModal = ref({ show: false });
const addForm = ref({ user_id: '', full_name: '', permissions: [] });
const addSubmitting = ref(false);

const openAddModal = () => {
    addForm.value = { user_id: '', full_name: '', permissions: [] };
    addModal.value.show = true;
};
const closeAddModal = () => { addModal.value.show = false; };

const submitAdd = () => {
    addSubmitting.value = true;
    router.post('/admin/administrators', addForm.value, {
        preserveScroll: true,
        onSuccess: () => { closeAddModal(); showToast(locale.value === 'lv' ? 'Administrators pievienots!' : 'Administrator added!'); },
        onError: () => showToast(locale.value === 'lv' ? 'Kļūda!' : 'Error!', 'error'),
        onFinish: () => { addSubmitting.value = false; },
    });
};

// ── Edit Permissions Modal ─────────────────────────────────────
const editModal = ref({ show: false, admin: null });
const editPermissions = ref([]);

const openEditModal = (admin) => {
    editModal.value = { show: true, admin };
    editPermissions.value = [...(admin.permissions || [])];
};
const closeEditModal = () => { editModal.value = { show: false, admin: null }; };

const togglePermission = (perm) => {
    const idx = editPermissions.value.indexOf(perm);
    if (idx >= 0) editPermissions.value.splice(idx, 1);
    else editPermissions.value.push(perm);
};

const submitEditPermissions = () => {
    router.put(`/admin/administrators/${editModal.value.admin.id}/permissions`, {
        permissions: editPermissions.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { closeEditModal(); showToast(locale.value === 'lv' ? 'Atļaujas atjauninātas!' : 'Permissions updated!'); },
        onError: () => showToast(locale.value === 'lv' ? 'Kļūda!' : 'Error!', 'error'),
    });
};

// ── Delete Modal ───────────────────────────────────────────────
const deleteModal = ref({ show: false, item: null });
const openDeleteModal = (admin) => { deleteModal.value = { show: true, item: admin }; };
const closeDeleteModal = () => { deleteModal.value = { show: false, item: null }; };
const confirmDelete = () => {
    if (!deleteModal.value.item) return;
    router.delete(`/admin/administrators/${deleteModal.value.item.id}`, {
        preserveScroll: true,
        onSuccess: () => { showToast(locale.value === 'lv' ? 'Administrators noņemts!' : 'Administrator removed!'); closeDeleteModal(); },
        onError: (errors) => {
            const msg = errors?.message || (locale.value === 'lv' ? 'Kļūda!' : 'Error!');
            showToast(msg, 'error');
            closeDeleteModal();
        },
    });
};

// ── Atļauju nosaukumi (lv/en) ──────────────────────────────────
const PERM_LABELS = {
    // Produkti
    'products.view':     { lv: 'Apskatīt produktus',    en: 'View products' },
    'products.create':   { lv: 'Pievienot produktus',   en: 'Create products' },
    'products.edit':     { lv: 'Rediģēt produktus',     en: 'Edit products' },
    'products.delete':   { lv: 'Dzēst produktus',       en: 'Delete products' },
    // Pasūtījumi
    'orders.view':       { lv: 'Apskatīt pasūtījumus',  en: 'View orders' },
    'orders.edit':       { lv: 'Rediģēt pasūtījumus',   en: 'Edit orders' },
    'orders.delete':     { lv: 'Dzēst pasūtījumus',     en: 'Delete orders' },
    'orders.export':     { lv: 'Eksportēt pasūtījumus', en: 'Export orders' },
    // Saturs
    'content.view':      { lv: 'Apskatīt saturu',       en: 'View content' },
    'content.create':    { lv: 'Pievienot saturu',      en: 'Create content' },
    'content.edit':      { lv: 'Rediģēt saturu',        en: 'Edit content' },
    'content.delete':    { lv: 'Dzēst saturu',          en: 'Delete content' },
    'content.publish':   { lv: 'Publicēt saturu',       en: 'Publish content' },
    // Kategorijas
    'categories.view':   { lv: 'Apskatīt kategorijas',  en: 'View categories' },
    'categories.create': { lv: 'Pievienot kategorijas', en: 'Create categories' },
    'categories.edit':   { lv: 'Rediģēt kategorijas',   en: 'Edit categories' },
    'categories.delete': { lv: 'Dzēst kategorijas',     en: 'Delete categories' },
    // Lietotāji
    'users.view':        { lv: 'Apskatīt lietotājus',   en: 'View users' },
    'users.create':        { lv: 'Izveidot lietotājus',   en: 'Create users' },
    'users.edit':        { lv: 'Rediģēt lietotājus',    en: 'Edit users' },
    'users.delete':      { lv: 'Dzēst lietotājus',      en: 'Delete users' },
    'users.ban':        { lv: 'Noraidīt lietotājus',   en: 'Ban users' },
    // Atsauksmes
    'reviews.view':      { lv: 'Apskatīt atsauksmes',   en: 'View reviews' },
    'reviews.approve':   { lv: 'Apstiprināt atsauksmes',en: 'Approve reviews' },
    'reviews.moderate':      { lv: 'Regulēt atsauksmes',   en: 'Moderate reviews' },
    'reviews.delete':    { lv: 'Dzēst atsauksmes',      en: 'Delete reviews' },
    // Komentāri
    'comments.view':     { lv: 'Apskatīt komentārus',   en: 'View comments' },
    'comments.approve':  { lv: 'Apstiprināt komentārus',en: 'Approve comments' },
    'comments.moderate':      { lv: 'Regulēt komentārus',   en: 'Moderate comments' },
    'comments.delete':   { lv: 'Dzēst komentārus',      en: 'Delete comments' },
    // Kontakti
    'contacts.view':     { lv: 'Apskatīt ziņojumus',    en: 'View messages' },
    'contacts.reply':    { lv: 'Atbildēt uz ziņojumiem',en: 'Reply to messages' },
    'contacts.delete':   { lv: 'Dzēst ziņojumus',       en: 'Delete messages' },
    // Žurnāli
    'logs.view':         { lv: 'Apskatīt žurnālus',     en: 'View logs' },
    'logs.export':       { lv: 'Eksportēt žurnālus',    en: 'Export logs' },
    // Iestatījumi
    'settings.view':     { lv: 'Apskatīt iestatījumus', en: 'View settings' },
    'settings.edit':     { lv: 'Rediģēt iestatījumus',  en: 'Edit settings' },
    // Lietotāji
    'admins.view':        { lv: 'Apskatīt administratorus',   en: 'View admins' },
    'admins.create':      { lv: 'Izveidot administratorus',   en: 'Create admins' },
    'admins.edit':        { lv: 'Rediģēt administratorus',    en: 'Edit admins' },
    'admins.delete':      { lv: 'Dzēst administratorus',      en: 'Delete admins' },
    // Kurjeri
    'couriers.view':      { lv: 'Apskatīt kurjerus',          en: 'View couriers' },
    'couriers.create':    { lv: 'Pievienot kurjerus',          en: 'Create couriers' },
    'couriers.edit':      { lv: 'Rediģēt kurjerus',            en: 'Edit couriers' },
    'couriers.delete':    { lv: 'Dzēst kurjerus',              en: 'Delete couriers' },
    'couriers.assign':    { lv: 'Piešķirt pasūtījumus kurjeriem', en: 'Assign orders to couriers' },
    // Komentāri
    'comments.view':      { lv: 'Apskatīt komentārus',        en: 'View comments' },
    'comments.moderate':  { lv: 'Moderēt komentārus',          en: 'Moderate comments' },
    'comments.delete':    { lv: 'Dzēst komentārus',           en: 'Delete comments' },
    // Pasūtījumu eksports
    'orders.export':      { lv: 'Eksportēt pasūtījumus',      en: 'Export orders' },
    // Žurnālu eksports
    'logs.export':        { lv: 'Eksportēt žurnālus',         en: 'Export logs' },
};

// Backend atgriež grupas latviski (no PERMISSION_GROUPS konstantes)
// Tāpēc getGroupLabel atgriež tieši grupu atslēgu angliski ja locale=en
const GROUP_LABELS = {
    'Produkti':       { en: 'Products' },
    'Kategorijas':    { en: 'Categories' },
    'Pasūtījumi':     { en: 'Orders' },
    'Lietotāji':      { en: 'Users' },
    'Saturs':         { en: 'Content' },
    'Atsauksmes':     { en: 'Reviews' },
    'Komentāri':      { en: 'Comments' },
    'Kontakti':       { en: 'Contacts' },
    'Kurjeri':        { en: 'Couriers' },
    'Iestatījumi':    { en: 'Settings' },
    'Administratori': { en: 'Admins' },
    'Žurnāls':        { en: 'Logs' },
};

const getPermLabel = (perm) => {
    const entry = PERM_LABELS[perm];
    if (!entry) return perm;
    return locale.value === 'lv' ? entry.lv : entry.en;
};

const getGroupLabel = (group) => {
    // Backend grupas nosaukumi jau ir latviski
    if (locale.value === 'lv') return group;
    const entry = GROUP_LABELS[group];
    return entry ? entry.en : group;
};

// ── Mobilais skats ─────────────────────────────────────────────
const mobileExpanded = ref({});
// Bulk permission helpers
const allPermKeys = computed(() => {
    if (!props.permissionGroups) return [];
    return Object.values(props.permissionGroups).flat();
});

const selectAllPerms = (mode) => {
    if (mode === 'add') addForm.value.permissions = [...allPermKeys.value];
    else editPermissions.value = [...allPermKeys.value];
};
const clearAllPerms = (mode) => {
    if (mode === 'add') addForm.value.permissions = [];
    else editPermissions.value = [];
};
const toggleGroupPerms = (mode, perms) => {
    const arr = mode === 'add' ? addForm.value.permissions : editPermissions.value;
    const all = perms.every(p => arr.includes(p));
    if (all) {
        if (mode === 'add') addForm.value.permissions = arr.filter(p => !perms.includes(p));
        else editPermissions.value = arr.filter(p => !perms.includes(p));
    } else {
        perms.forEach(p => { if (!arr.includes(p)) arr.push(p); });
    }
};

const getAvatar = (admin) => {
    if (admin.user?.profile_picture) return `/storage/${admin.user.profile_picture}`;
    return '/img/default-avatar.png';
};

const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
};

const groupEntries = computed(() => {
    if (!props.permissionGroups) return [];
    return Object.entries(props.permissionGroups);
});
</script>

<template>
    <Head :title="locale === 'lv' ? 'Administratori' : 'Administrators'" />
    <AdminLayout>
        <div class="admins-page">

            <!-- Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <h1 class="page-title">
                        <i class="fas fa-user-shield"></i>
                        {{ locale === 'lv' ? 'Administratori' : 'Administrators' }}
                    </h1>
                    <p class="page-subtitle">{{ locale === 'lv' ? 'Super Admin sadaļa' : 'Super Admin section' }}</p>
                </div>
                <button @click="openAddModal" class="btn-add">
                    <i class="fas fa-plus"></i>
                    {{ locale === 'lv' ? 'Pievienot adminu' : 'Add Admin' }}
                </button>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-chip">
                    <i class="fas fa-users"></i>
                    <span>{{ stats.total }}</span>
                    <small>{{ locale === 'lv' ? 'Kopā' : 'Total' }}</small>
                </div>
                <div class="stat-chip stat-chip--super">
                    <i class="fas fa-crown"></i>
                    <span>{{ stats.super_admins }}</span>
                    <small>Super Admin</small>
                </div>
                <div class="stat-chip stat-chip--regular">
                    <i class="fas fa-user-cog"></i>
                    <span>{{ stats.regular_admins }}</span>
                    <small>{{ locale === 'lv' ? 'Parasti' : 'Regular' }}</small>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input v-model="search" @input="doSearch" type="text"
                           :placeholder="locale === 'lv' ? 'Meklēt...' : 'Search...'"
                           class="search-input" />
                </div>
                <select v-model="typeFilter" @change="doSearch" class="filter-select">
                    <option value="">{{ locale === 'lv' ? 'Visi tipi' : 'All types' }}</option>
                    <option value="super">Super Admin</option>
                    <option value="regular">{{ locale === 'lv' ? 'Parasti' : 'Regular' }}</option>
                </select>
            </div>

            <!-- Table -->
            <!-- Desktop tabula -->
            <div class="table-wrap">
                <table class="admins-table">
                    <thead>
                    <tr>
                        <th>{{ locale === 'lv' ? 'Lietotājs' : 'User' }}</th>
                        <th>{{ locale === 'lv' ? 'Vārds' : 'Full Name' }}</th>
                        <th>{{ locale === 'lv' ? 'Tips' : 'Type' }}</th>
                        <th>{{ locale === 'lv' ? 'Atļaujas' : 'Permissions' }}</th>
                        <th>{{ locale === 'lv' ? 'Pēd. pieslēgšanās' : 'Last Login' }}</th>
                        <th>{{ locale === 'lv' ? 'Darbības' : 'Actions' }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="admin in administrators.data" :key="admin.id">
                        <td>
                            <div class="user-cell">
                                <img :src="getAvatar(admin)" class="user-avatar"
                                     @error="$event.target.src='/img/default-avatar.png'" />
                                <div>
                                    <div class="user-name">{{ admin.user?.username }}</div>
                                    <div class="user-email">{{ admin.user?.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ admin.full_name }}</td>
                        <td>
                            <span class="type-badge" :class="admin.is_super_admin ? 'type-super' : 'type-regular'">
                                <i :class="admin.is_super_admin ? 'fas fa-crown' : 'fas fa-user-cog'"></i>
                                {{ admin.is_super_admin ? 'Super Admin' : (locale === 'lv' ? 'Parasts' : 'Regular') }}
                            </span>
                        </td>
                        <td>
                            <span v-if="admin.is_super_admin" class="perm-all">
                                {{ locale === 'lv' ? 'Visas atļaujas' : 'All permissions' }}
                            </span>
                            <span v-else class="perm-count">
                                {{ (admin.permissions || []).length }}
                                {{ locale === 'lv' ? 'atļaujas' : 'permissions' }}
                            </span>
                        </td>
                        <td class="date-cell">{{ formatDate(admin.last_login_at) }}</td>
                        <td>
                            <div class="actions-cell">
                                <button v-if="!admin.is_super_admin"
                                        @click="openEditModal(admin)"
                                        class="btn-icon btn-icon-edit"
                                        :title="locale === 'lv' ? 'Rediģēt atļaujas' : 'Edit permissions'">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button v-if="!admin.is_super_admin"
                                        @click="openDeleteModal(admin)"
                                        class="btn-icon btn-icon-delete"
                                        :title="locale === 'lv' ? 'Noņemt adminu' : 'Remove admin'">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                                <span v-if="admin.is_super_admin" class="protected-label">
                                    <i class="fas fa-lock"></i>
                                    {{ locale === 'lv' ? 'Aizsargāts' : 'Protected' }}
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!administrators.data?.length">
                        <td colspan="6" class="empty-row">
                            <i class="fas fa-user-shield"></i>
                            {{ locale === 'lv' ? 'Nav administratoru' : 'No administrators found' }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobilās kartītes (rādās tikai mobilajā) -->
            <div class="mobile-cards">
                <div v-if="!administrators.data?.length" class="mobile-empty">
                    <i class="fas fa-user-shield"></i>
                    {{ locale === 'lv' ? 'Nav administratoru' : 'No administrators found' }}
                </div>
                <div v-for="admin in administrators.data" :key="'m-' + admin.id"
                     class="mobile-card"
                     :class="{ 'mobile-card--super': admin.is_super_admin }">
                    <!-- Karte virsraksts -->
                    <div class="mobile-card-header">
                        <img :src="getAvatar(admin)" class="mobile-avatar"
                             @error="$event.target.src='/img/default-avatar.png'" />
                        <div class="mobile-card-info">
                            <div class="mobile-card-name">{{ admin.full_name }}</div>
                            <div class="mobile-card-user">@{{ admin.user?.username }}</div>
                            <div class="mobile-card-email">{{ admin.user?.email }}</div>
                        </div>
                        <span class="type-badge" :class="admin.is_super_admin ? 'type-super' : 'type-regular'">
                            <i :class="admin.is_super_admin ? 'fas fa-crown' : 'fas fa-user-cog'"></i>
                            {{ admin.is_super_admin ? 'Super' : (locale === 'lv' ? 'Parasts' : 'Regular') }}
                        </span>
                    </div>
                    <!-- Karte dati -->
                    <div class="mobile-card-body">
                        <div class="mobile-card-row">
                            <span class="mobile-card-label">{{ locale === 'lv' ? 'Atļaujas' : 'Permissions' }}</span>
                            <span class="mobile-card-value">
                                <span v-if="admin.is_super_admin" class="perm-all">
                                    {{ locale === 'lv' ? 'Visas' : 'All' }}
                                </span>
                                <span v-else class="perm-count">
                                    {{ (admin.permissions || []).length }}
                                </span>
                            </span>
                        </div>
                        <div class="mobile-card-row">
                            <span class="mobile-card-label">{{ locale === 'lv' ? 'Pēd. pieslēgšanās' : 'Last Login' }}</span>
                            <span class="mobile-card-value">{{ formatDate(admin.last_login_at) }}</span>
                        </div>
                    </div>
                    <!-- Karte darbības -->
                    <div class="mobile-card-footer">
                        <template v-if="!admin.is_super_admin">
                            <button @click="openEditModal(admin)" class="mobile-btn mobile-btn-edit">
                                <i class="fas fa-key"></i>
                                {{ locale === 'lv' ? 'Atļaujas' : 'Permissions' }}
                            </button>
                            <button @click="openDeleteModal(admin)" class="mobile-btn mobile-btn-delete">
                                <i class="fas fa-user-minus"></i>
                                {{ locale === 'lv' ? 'Noņemt' : 'Remove' }}
                            </button>
                        </template>
                        <span v-else class="protected-label">
                            <i class="fas fa-lock"></i>
                            {{ locale === 'lv' ? 'Aizsargāts' : 'Protected' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="administrators.last_page > 1" class="pagination">
                <button v-for="page in administrators.last_page" :key="page"
                        @click="router.get('/admin/administrators', { page, search: search || undefined, type: typeFilter || undefined })"
                        class="page-btn" :class="{ 'page-btn--active': page === administrators.current_page }">
                    {{ page }}
                </button>
            </div>

        </div>

        <!-- ── Pievienot adminu modālis ── -->
        <Transition name="modal-fade">
            <div v-if="addModal.show" class="modal-overlay" @click.self="closeAddModal">
                <div class="modal-box">
                    <div class="modal-header">
                        <h2><i class="fas fa-user-plus"></i> {{ locale === 'lv' ? 'Pievienot administratoru' : 'Add Administrator' }}</h2>
                        <button @click="closeAddModal" class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ locale === 'lv' ? 'Lietotājs' : 'User' }}</label>
                            <select v-model="addForm.user_id" class="form-control">
                                <option value="">{{ locale === 'lv' ? 'Izvēlēties...' : 'Select...' }}</option>
                                <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                                    {{ u.username }} ({{ u.email }})
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ locale === 'lv' ? 'Pilnais vārds' : 'Full Name' }}</label>
                            <input v-model="addForm.full_name" type="text" class="form-control"
                                   :placeholder="locale === 'lv' ? 'Vārds Uzvārds' : 'First Last'" />
                        </div>
                        <div class="form-group">
                            <label>{{ locale === 'lv' ? 'Sākotnējās atļaujas' : 'Initial Permissions' }}</label>
                            <div class="perm-bulk-actions">
                                <button type="button" @click="selectAllPerms('add')" class="perm-bulk-btn">
                                    <i class="fas fa-check-double"></i>
                                    {{ locale === 'lv' ? 'Atlasīt visu' : 'Select all' }}
                                </button>
                                <button type="button" @click="clearAllPerms('add')" class="perm-bulk-btn perm-bulk-btn--clear">
                                    <i class="fas fa-times"></i>
                                    {{ locale === 'lv' ? 'Noņemt visu' : 'Clear all' }}
                                </button>
                            </div>
                            <div class="perm-grid">
                                <div v-for="(perms, group) in permissionGroups" :key="group" class="perm-group">
                                    <div class="perm-group-label">
                                        {{ getGroupLabel(group) }}
                                        <button type="button" @click="toggleGroupPerms('add', perms)" class="perm-group-toggle">
                                            {{ perms.every(p => addForm.permissions.includes(p)) ? (locale === 'lv' ? 'Noņemt' : 'Clear') : (locale === 'lv' ? 'Visus' : 'All') }}
                                        </button>
                                    </div>
                                    <label v-for="perm in perms" :key="perm" class="perm-item">
                                        <input type="checkbox" :value="perm" v-model="addForm.permissions" />
                                        <span class="perm-label">{{ getPermLabel(perm) }}</span>
                                        <span class="perm-key">{{ perm }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeAddModal" class="btn-cancel">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                        <button @click="submitAdd" :disabled="addSubmitting || !addForm.user_id || !addForm.full_name" class="btn-save">
                            <i v-if="addSubmitting" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-user-plus"></i>
                            {{ locale === 'lv' ? 'Pievienot' : 'Add' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Rediģēt atļaujas modālis ── -->
        <Transition name="modal-fade">
            <div v-if="editModal.show" class="modal-overlay" @click.self="closeEditModal">
                <div class="modal-box modal-box--wide">
                    <div class="modal-header">
                        <h2><i class="fas fa-key"></i> {{ locale === 'lv' ? 'Rediģēt atļaujas' : 'Edit Permissions' }} — {{ editModal.admin?.user?.username }}</h2>
                        <button @click="closeEditModal" class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="perm-bulk-actions">
                            <button type="button" @click="selectAllPerms('edit')" class="perm-bulk-btn">
                                <i class="fas fa-check-double"></i>
                                {{ locale === 'lv' ? 'Atlasīt visu' : 'Select all' }}
                            </button>
                            <button type="button" @click="clearAllPerms('edit')" class="perm-bulk-btn perm-bulk-btn--clear">
                                <i class="fas fa-times"></i>
                                {{ locale === 'lv' ? 'Noņemt visu' : 'Clear all' }}
                            </button>
                            <span class="perm-selected-count">
                                    {{ editPermissions.length }} {{ locale === 'lv' ? 'atlasīts' : 'selected' }}
                                </span>
                        </div>
                        <div class="perm-grid">
                            <div v-for="(perms, group) in permissionGroups" :key="group" class="perm-group">
                                <div class="perm-group-label">
                                    {{ getGroupLabel(group) }}
                                    <button type="button" @click="toggleGroupPerms('edit', perms)" class="perm-group-toggle">
                                        {{ perms.every(p => editPermissions.includes(p)) ? (locale === 'lv' ? 'Noņemt' : 'Clear') : (locale === 'lv' ? 'Visus' : 'All') }}
                                    </button>
                                </div>
                                <label v-for="perm in perms" :key="perm" class="perm-item">
                                    <input type="checkbox" :value="perm"
                                           :checked="editPermissions.includes(perm)"
                                           @change="togglePermission(perm)" />
                                    <span class="perm-label">{{ getPermLabel(perm) }}</span>
                                    <span class="perm-key">{{ perm }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeEditModal" class="btn-cancel">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                        <button @click="submitEditPermissions" class="btn-save">
                            <i class="fas fa-save"></i>
                            {{ locale === 'lv' ? 'Saglabāt' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Dzēšanas modālis ── -->
        <Transition name="modal-fade">
            <div v-if="deleteModal.show" class="delete-modal-overlay" @click.self="closeDeleteModal">
                <div class="delete-modal">
                    <div class="delete-modal-icon"><i class="fas fa-user-minus"></i></div>
                    <h3 class="delete-modal-title">{{ locale === 'lv' ? 'Noņemt administratoru?' : 'Remove administrator?' }}</h3>
                    <p class="delete-modal-body">{{ locale === 'lv' ? 'Lietotājs tiks degradēts uz klientu. Šo darbību nevar atsaukt.' : 'The user will be demoted to customer. This cannot be undone.' }}</p>
                    <div v-if="deleteModal.item" class="delete-modal-preview">
                        {{ deleteModal.item.full_name }} ({{ deleteModal.item.user?.username }})
                    </div>
                    <div class="delete-modal-actions">
                        <button @click="closeDeleteModal" class="delete-modal-cancel">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                        <button @click="confirmDelete" class="delete-modal-confirm">
                            <i class="fas fa-user-minus"></i>{{ locale === 'lv' ? 'Noņemt' : 'Remove' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Toast -->
        <Transition name="toast-fade">
            <div v-if="toast.show" class="toast-notif" :class="`toast-${toast.type}`">
                <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
                {{ toast.message }}
            </div>
        </Transition>

        <UnauthorizedModal />
    </AdminLayout>
</template>

<style scoped>
.admins-page { max-width: 1200px; margin: 0 auto; padding: 2rem; }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap; }
.page-title { font-size: 1.75rem; font-weight: 800; color: #111827; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.page-title i { color: #dc2626; }
.page-subtitle { color: #6b7280; margin: 0.25rem 0 0; font-size: 0.9rem; }
.btn-add { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #dc2626; color: white; border: none; border-radius: 0.5rem; font-weight: 700; cursor: pointer; transition: background 0.15s; }
.btn-add:hover { background: #b91c1c; }

/* Stats */
.stats-row { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.stat-chip { display: flex; align-items: center; gap: 0.5rem; background: white; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 0.75rem 1.25rem; font-size: 0.9rem; color: #374151; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.stat-chip i { color: #6b7280; }
.stat-chip span { font-size: 1.25rem; font-weight: 800; color: #111827; }
.stat-chip small { color: #9ca3af; font-size: 0.75rem; }
.stat-chip--super i { color: #d97706; }
.stat-chip--regular i { color: #3b82f6; }

/* Filters */
.filters-bar { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-wrap i { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.search-input { width: 100%; padding: 0.625rem 1rem 0.625rem 2.5rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.9rem; box-sizing: border-box; }
.search-input:focus { outline: none; border-color: #dc2626; }
.filter-select { padding: 0.625rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.9rem; background: white; cursor: pointer; }
.filter-select:focus { outline: none; border-color: #dc2626; }

/* Table */
.table-wrap { background: white; border-radius: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 1.5rem; }
.admins-table { width: 100%; border-collapse: collapse; }
.admins-table th { background: #f9fafb; padding: 0.875rem 1rem; text-align: left; font-size: 0.8rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; }
.admins-table td { padding: 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.admins-table tr:last-child td { border-bottom: none; }
.admins-table tr:hover td { background: #fafafa; }

.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar { width: 2.25rem; height: 2.25rem; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; flex-shrink: 0; }
.user-name { font-weight: 600; color: #111827; font-size: 0.875rem; }
.user-email { font-size: 0.75rem; color: #9ca3af; }

.type-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; }
.type-super { background: #fef3c7; color: #d97706; }
.type-regular { background: #eff6ff; color: #3b82f6; }

.perm-all { font-size: 0.75rem; color: #059669; font-weight: 600; }
.perm-count { font-size: 0.75rem; color: #6b7280; }

.date-cell { font-size: 0.8rem; color: #6b7280; white-space: nowrap; }

.actions-cell { display: flex; align-items: center; gap: 0.5rem; }
.btn-icon { width: 2rem; height: 2rem; border: none; border-radius: 0.375rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; transition: all 0.15s; }
.btn-icon-edit { background: #eff6ff; color: #3b82f6; }
.btn-icon-edit:hover { background: #3b82f6; color: white; }
.btn-icon-delete { background: #fee2e2; color: #dc2626; }
.btn-icon-delete:hover { background: #dc2626; color: white; }
.protected-label { font-size: 0.7rem; color: #9ca3af; display: flex; align-items: center; gap: 0.25rem; }

.empty-row { text-align: center; padding: 3rem; color: #9ca3af; font-size: 0.9rem; }
.empty-row i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }

/* Pagination */
.pagination { display: flex; gap: 0.375rem; justify-content: center; }
.page-btn { width: 2rem; height: 2rem; border: 1px solid #e5e7eb; background: white; border-radius: 0.375rem; font-size: 0.8rem; cursor: pointer; transition: all 0.15s; }
.page-btn--active { background: #dc2626; border-color: #dc2626; color: white; font-weight: 700; }

/* Add/Edit Modals */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.modal-box { background: white; border-radius: 1rem; max-width: 540px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.modal-box--wide { max-width: 700px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-header h2 { margin: 0; font-size: 1.125rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 0.5rem; }
.modal-header h2 i { color: #dc2626; }
.modal-close { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1.125rem; padding: 0.25rem; border-radius: 0.25rem; }
.modal-close:hover { background: #f3f4f6; color: #374151; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; gap: 0.75rem; justify-content: flex-end; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
.form-control { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.9rem; box-sizing: border-box; }
.form-control:focus { outline: none; border-color: #dc2626; }
.perm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
.perm-group { background: #f9fafb; border-radius: 0.5rem; padding: 0.75rem; }
.perm-group-label { font-size: 0.75rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
.perm-item { display: flex; align-items: flex-start; gap: 0.4rem; font-size: 0.8rem; color: #374151; margin-bottom: 0.375rem; cursor: pointer; line-height: 1.4; }
.perm-item input { cursor: pointer; flex-shrink: 0; margin-top: 0.15rem; }
.perm-label { font-weight: 600; color: #111827; }
.perm-key { font-size: 0.65rem; color: #9ca3af; font-family: monospace; background: #f3f4f6; padding: 0.1rem 0.3rem; border-radius: 0.25rem; white-space: nowrap; margin-left: auto; flex-shrink: 0; }

/* Bulk atļauju pogas */
.perm-bulk-actions {
    display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.875rem;
    padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap;
}
.perm-bulk-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;
    background: white; font-size: 0.78rem; font-weight: 600; color: #374151;
    cursor: pointer; transition: all 0.15s;
}
.perm-bulk-btn:hover { background: #f3f4f6; border-color: #9ca3af; }
.perm-bulk-btn--clear { color: #dc2626; border-color: #fca5a5; }
.perm-bulk-btn--clear:hover { background: #fee2e2; border-color: #dc2626; }
.perm-selected-count { margin-left: auto; font-size: 0.78rem; color: #6b7280; font-weight: 600; }
.perm-group-label {
    font-size: 0.75rem; font-weight: 700; color: #6b7280; text-transform: uppercase;
    letter-spacing: 0.05em; margin-bottom: 0.5rem;
    display: flex; align-items: center; justify-content: space-between;
}
.perm-group-toggle {
    font-size: 0.65rem; font-weight: 700; color: #3b82f6; background: none; border: none;
    cursor: pointer; padding: 0.1rem 0.35rem; border-radius: 0.25rem;
    text-transform: none; letter-spacing: 0;
}
.perm-group-toggle:hover { background: #eff6ff; }
.btn-cancel { padding: 0.625rem 1.25rem; background: #f3f4f6; border: none; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; color: #374151; cursor: pointer; }
.btn-cancel:hover { background: #e5e7eb; }
.btn-save { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.625rem 1.25rem; background: #dc2626; border: none; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 700; color: white; cursor: pointer; transition: background 0.15s; }
.btn-save:hover:not(:disabled) { background: #b91c1c; }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

/* Delete Modal */
.delete-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.delete-modal { background: white; border-radius: 1rem; padding: 2rem; max-width: 420px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.delete-modal-icon { width: 3.5rem; height: 3.5rem; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 1.375rem; color: #dc2626; }
.delete-modal-title { font-size: 1.2rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem; }
.delete-modal-body { font-size: 0.875rem; color: #6b7280; margin: 0 0 1rem; }
.delete-modal-preview { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-size: 0.825rem; color: #374151; font-style: italic; margin-bottom: 1.5rem; }
.delete-modal-actions { display: flex; gap: 0.75rem; justify-content: center; }
.delete-modal-cancel { flex: 1; padding: 0.75rem; background: #f3f4f6; border: none; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; color: #374151; cursor: pointer; }
.delete-modal-cancel:hover { background: #e5e7eb; }
.delete-modal-confirm { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.75rem; background: #dc2626; border: none; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 700; color: white; cursor: pointer; }
.delete-modal-confirm:hover { background: #b91c1c; }

/* Toast */
.toast-notif { position: fixed; bottom: 1.5rem; right: 1.5rem; display: flex; align-items: center; gap: 0.625rem; padding: 0.875rem 1.25rem; border-radius: 0.75rem; font-size: 0.9rem; font-weight: 600; z-index: 10000; box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
.toast-success { background: #059669; color: white; }
.toast-error { background: #dc2626; color: white; }
.toast-fade-enter-active, .toast-fade-leave-active { transition: all 0.3s ease; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translateY(1rem); }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-active .delete-modal,
.modal-fade-enter-active .modal-box { transition: transform 0.2s ease; }
.modal-fade-enter-from .delete-modal,
.modal-fade-enter-from .modal-box { transform: scale(0.93); }

/* ── Mobilās kartītes — slēptas desktop, rādās mobilajā ── */
.mobile-cards { display: none; }

@media (max-width: 768px) {
    /* Lapa */
    .admins-page { padding: 1rem; }
    .page-header { flex-direction: column; gap: 0.75rem; }
    .btn-add { width: 100%; justify-content: center; }

    /* Stats */
    .stats-row { gap: 0.5rem; flex-wrap: wrap; }
    .stat-chip { flex: 1; min-width: calc(33% - 0.35rem); padding: 0.5rem 0.625rem; }
    .stat-chip span { font-size: 1rem; }

    /* Filtri */
    .filters-bar { flex-direction: column; }
    .search-wrap { width: 100%; }
    .filter-select { width: 100%; }

    /* Tabula — paslēpta mobilajā */
    .table-wrap { display: none; }

    /* Mobilās kartītes */
    .mobile-cards { display: flex; flex-direction: column; gap: 0.875rem; margin-bottom: 1.5rem; }
    .mobile-empty {
        text-align: center; padding: 3rem 1rem; color: #9ca3af;
        background: white; border-radius: 0.875rem; font-size: 0.9rem;
    }
    .mobile-empty i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
    .mobile-card {
        background: white; border-radius: 0.875rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb; overflow: hidden;
    }
    .mobile-card--super { border-color: #fbbf24; background: linear-gradient(to bottom, #fffbeb, white); }
    .mobile-card-header {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 1rem; border-bottom: 1px solid #f3f4f6;
    }
    .mobile-avatar {
        width: 2.75rem; height: 2.75rem; border-radius: 50%;
        object-fit: cover; border: 2px solid #e5e7eb; flex-shrink: 0;
    }
    .mobile-card--super .mobile-avatar { border-color: #fbbf24; }
    .mobile-card-info { flex: 1; min-width: 0; }
    .mobile-card-name { font-weight: 700; color: #111827; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mobile-card-user { font-size: 0.75rem; color: #6b7280; }
    .mobile-card-email { font-size: 0.7rem; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mobile-card-body { padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
    .mobile-card-row { display: flex; align-items: center; justify-content: space-between; }
    .mobile-card-label { font-size: 0.75rem; color: #9ca3af; }
    .mobile-card-value { font-size: 0.8rem; color: #374151; font-weight: 600; }
    .mobile-card-footer {
        display: flex; gap: 0.5rem; padding: 0.75rem 1rem;
        border-top: 1px solid #f3f4f6; background: #fafafa; align-items: center;
    }
    .mobile-btn {
        flex: 1; display: inline-flex; align-items: center; justify-content: center;
        gap: 0.35rem; padding: 0.5rem 0.75rem; border: none; border-radius: 0.5rem;
        font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: all 0.15s;
    }
    .mobile-btn-edit { background: #eff6ff; color: #2563eb; }
    .mobile-btn-edit:hover { background: #2563eb; color: white; }
    .mobile-btn-delete { background: #fee2e2; color: #dc2626; }
    .mobile-btn-delete:hover { background: #dc2626; color: white; }

    /* Modāļi — slide-up no apakšas */
    .modal-overlay { align-items: flex-end; }
    .modal-box {
        max-width: 100%; max-height: 92vh; border-radius: 1rem 1rem 0 0;
        position: relative; margin: 0;
    }
    .modal-footer { flex-direction: column; }
    .btn-cancel, .btn-save { width: 100%; justify-content: center; }
    .perm-grid { grid-template-columns: 1fr; }
    .perm-key { display: none; }
    .perm-bulk-actions { flex-wrap: wrap; }

    /* Dzēšanas modālis */
    .delete-modal { margin: 0 0.5rem; }
    .delete-modal-actions { flex-direction: column; }
    .delete-modal-cancel, .delete-modal-confirm { width: 100%; justify-content: center; }

    /* Paginācija */
    .pagination { flex-wrap: wrap; gap: 0.25rem; }
}

@media (max-width: 480px) {
    .stat-chip { min-width: calc(50% - 0.25rem); }
}
</style>
