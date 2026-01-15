<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const props = defineProps({
    administrators: Object,
    filters: Object,
    stats: Object,
    availablePermissions: Object,
    permissionGroups: Object,
    availableUsers: Array,
});

// Same permissions config as Dashboard.vue
const permissionsConfig = computed(() => ({
    [t('admin.permissions.products')]: [
        { key: 'products.view', labelKey: 'admin.permissions.productsView' },
        { key: 'products.create', labelKey: 'admin.permissions.productsCreate' },
        { key: 'products.edit', labelKey: 'admin.permissions.productsEdit' },
        { key: 'products.delete', labelKey: 'admin.permissions.productsDelete' },
    ],
    [t('admin.permissions.categories')]: [
        { key: 'categories.view', labelKey: 'admin.permissions.categoriesView' },
        { key: 'categories.create', labelKey: 'admin.permissions.categoriesCreate' },
        { key: 'categories.edit', labelKey: 'admin.permissions.categoriesEdit' },
        { key: 'categories.delete', labelKey: 'admin.permissions.categoriesDelete' },
    ],
    [t('admin.permissions.orders')]: [
        { key: 'orders.view', labelKey: 'admin.permissions.ordersView' },
        { key: 'orders.edit', labelKey: 'admin.permissions.ordersEdit' },
        { key: 'orders.delete', labelKey: 'admin.permissions.ordersDelete' },
    ],
    [t('admin.permissions.users')]: [
        { key: 'users.view', labelKey: 'admin.permissions.usersView' },
        { key: 'users.create', labelKey: 'admin.permissions.usersCreate' },
        { key: 'users.edit', labelKey: 'admin.permissions.usersEdit' },
        { key: 'users.delete', labelKey: 'admin.permissions.usersDelete' },
        { key: 'users.ban', labelKey: 'admin.permissions.usersBan' },
    ],
    [t('admin.permissions.content')]: [
        { key: 'content.view', labelKey: 'admin.permissions.contentView' },
        { key: 'content.create', labelKey: 'admin.permissions.contentCreate' },
        { key: 'content.edit', labelKey: 'admin.permissions.contentEdit' },
        { key: 'content.delete', labelKey: 'admin.permissions.contentDelete' },
        { key: 'content.publish', labelKey: 'admin.permissions.contentPublish' },
    ],
    [t('admin.permissions.reviews')]: [
        { key: 'reviews.view', labelKey: 'admin.permissions.reviewsView' },
        { key: 'reviews.moderate', labelKey: 'admin.permissions.reviewsModerate' },
        { key: 'reviews.delete', labelKey: 'admin.permissions.reviewsDelete' },
    ],
    [t('admin.permissions.comments')]: [
        { key: 'comments.view', labelKey: 'admin.permissions.commentsView' },
        { key: 'comments.moderate', labelKey: 'admin.permissions.commentsModerate' },
        { key: 'comments.delete', labelKey: 'admin.permissions.commentsDelete' },
    ],
    [t('admin.permissions.contacts')]: [
        { key: 'contacts.view', labelKey: 'admin.permissions.contactsView' },
        { key: 'contacts.reply', labelKey: 'admin.permissions.contactsReply' },
        { key: 'contacts.delete', labelKey: 'admin.permissions.contactsDelete' },
    ],
    [t('admin.permissions.settings')]: [
        { key: 'settings.view', labelKey: 'admin.permissions.settingsView' },
        { key: 'settings.edit', labelKey: 'admin.permissions.settingsEdit' },
    ],
    [t('admin.permissions.logs')]: [
        { key: 'logs.view', labelKey: 'admin.permissions.logsView' },
    ],
}));

const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');
const showAddAdminModal = ref(false);
const showEditPermissionsModal = ref(false);
const selectedAdmin = ref(null);
const selectedUserId = ref(null);
const adminFullName = ref('');
const selectedPermissions = ref([]);
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

let searchTimeout = null;
const debounceSearch = (fn, delay = 300) => { clearTimeout(searchTimeout); searchTimeout = setTimeout(fn, delay); };

const applyFilters = () => {
    router.get('/admin/administrators', { search: search.value || undefined, type: typeFilter.value || undefined }, { preserveState: true, replace: true });
};

watch(typeFilter, applyFilters);
watch(search, () => debounceSearch(applyFilters));

const clearFilters = () => { search.value = ''; typeFilter.value = ''; };
const hasFilters = computed(() => search.value || typeFilter.value);

const formatDate = (date) => {
    if (!date) return t('admin.administrators.never');
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getUserAvatar = (user) => {
    if (!user?.profile_picture) return '/img/default-avatar.png';
    if (user.profile_picture.startsWith('http')) return user.profile_picture;
    return `/storage/${user.profile_picture}`;
};

const nonAdminUsers = computed(() => {
    const adminUserIds = props.administrators.data.map(a => a.user_id);
    return props.availableUsers.filter(u => !adminUserIds.includes(u.id));
});

const openAddAdminModal = () => { selectedUserId.value = null; adminFullName.value = ''; selectedPermissions.value = []; errorMessage.value = ''; showAddAdminModal.value = true; };
const openEditPermissionsModal = (admin) => { selectedAdmin.value = admin; selectedPermissions.value = [...(admin.permissions || [])]; errorMessage.value = ''; showEditPermissionsModal.value = true; };
const closeModals = () => { showAddAdminModal.value = false; showEditPermissionsModal.value = false; selectedAdmin.value = null; selectedUserId.value = null; adminFullName.value = ''; selectedPermissions.value = []; errorMessage.value = ''; };

const togglePermission = (permissionKey) => { const index = selectedPermissions.value.indexOf(permissionKey); if (index > -1) { selectedPermissions.value.splice(index, 1); } else { selectedPermissions.value.push(permissionKey); } };
const toggleGroup = (groupPermissions) => { const groupKeys = groupPermissions.map(p => p.key); const allSelected = groupKeys.every(key => selectedPermissions.value.includes(key)); if (allSelected) { selectedPermissions.value = selectedPermissions.value.filter(p => !groupKeys.includes(p)); } else { groupKeys.forEach(key => { if (!selectedPermissions.value.includes(key)) { selectedPermissions.value.push(key); } }); } };
const selectAllPermissions = () => { const allKeys = Object.values(permissionsConfig.value).flat().map(p => p.key); selectedPermissions.value = [...allKeys]; };
const clearAllPermissions = () => { selectedPermissions.value = []; };

const onUserSelected = () => { const user = nonAdminUsers.value.find(u => u.id === selectedUserId.value); if (user && !adminFullName.value) { adminFullName.value = user.username; } };

const saveNewAdmin = () => {
    if (!selectedUserId.value) { errorMessage.value = t('admin.dashboard.selectUserError'); return; }
    if (!adminFullName.value.trim()) { errorMessage.value = t('admin.administrators.fullNameRequired'); return; }
    isLoading.value = true; errorMessage.value = '';
    router.post('/admin/administrators', { user_id: selectedUserId.value, full_name: adminFullName.value, permissions: selectedPermissions.value }, {
        preserveScroll: true,
        onSuccess: () => { successMessage.value = t('admin.dashboard.adminAdded'); closeModals(); setTimeout(() => successMessage.value = '', 3000); },
        onError: (errors) => { errorMessage.value = Object.values(errors)[0] || t('admin.dashboard.addAdminError'); },
        onFinish: () => isLoading.value = false,
    });
};

const updatePermissions = () => {
    if (!selectedAdmin.value) return;
    isLoading.value = true; errorMessage.value = '';
    router.put(`/admin/administrators/${selectedAdmin.value.id}/permissions`, { permissions: selectedPermissions.value }, {
        preserveScroll: true,
        onSuccess: () => { successMessage.value = t('admin.dashboard.permissionsUpdated'); closeModals(); setTimeout(() => successMessage.value = '', 3000); },
        onError: (errors) => { errorMessage.value = Object.values(errors)[0] || t('admin.dashboard.updateError'); },
        onFinish: () => isLoading.value = false,
    });
};

const removeAdmin = (admin) => {
    if (!confirm(t('admin.dashboard.removeAdminConfirm'))) return;
    router.delete(`/admin/administrators/${admin.id}`, { preserveScroll: true, onSuccess: () => { successMessage.value = t('admin.dashboard.adminRemoved'); setTimeout(() => successMessage.value = '', 3000); } });
};
</script>

<template>
    <Head :title="t('admin.administrators.index.title')" />
    <AdminLayout>
        <template #title>{{ t('admin.administrators.index.title') }}</template>

        <Transition name="slide-down"><div v-if="successMessage" class="alert alert-success"><i class="fas fa-check-circle"></i> {{ successMessage }}</div></Transition>

        <div class="stats-row">
            <div class="stat-card"><div class="stat-icon total"><i class="fas fa-user-shield"></i></div><div class="stat-info"><span class="stat-value">{{ stats?.total || 0 }}</span><span class="stat-label">{{ t('admin.administrators.stats.total') }}</span></div></div>
            <div class="stat-card"><div class="stat-icon super"><i class="fas fa-crown"></i></div><div class="stat-info"><span class="stat-value">{{ stats?.super_admins || 0 }}</span><span class="stat-label">{{ t('admin.administrators.stats.superAdmins') }}</span></div></div>
            <div class="stat-card"><div class="stat-icon regular"><i class="fas fa-user-cog"></i></div><div class="stat-info"><span class="stat-value">{{ stats?.regular_admins || 0 }}</span><span class="stat-label">{{ t('admin.administrators.stats.regularAdmins') }}</span></div></div>
        </div>

        <div class="filters-card">
            <div class="filters-row">
                <div class="filter-group search-group"><div class="search-input-wrapper"><i class="fas fa-search"></i><input v-model="search" type="text" class="search-input" :placeholder="t('admin.administrators.searchPlaceholder')"><button v-if="search" @click="search = ''" class="clear-search"><i class="fas fa-times"></i></button></div></div>
                <div class="filter-group"><select v-model="typeFilter" class="filter-select"><option value="">{{ t('admin.administrators.allTypes') }}</option><option value="super">{{ t('admin.administrators.types.super') }}</option><option value="regular">{{ t('admin.administrators.types.regular') }}</option></select></div>
                <button v-if="hasFilters" @click="clearFilters" class="btn btn-secondary"><i class="fas fa-times"></i> {{ t('admin.common.clearFilters') }}</button>
                <button @click="openAddAdminModal" class="btn btn-primary"><i class="fas fa-plus"></i> {{ t('admin.administrators.addNew') }}</button>
            </div>
        </div>

        <div class="admins-container">
            <div v-if="administrators.data.length === 0" class="empty-state"><i class="fas fa-user-shield"></i><h3>{{ t('admin.administrators.noAdmins') }}</h3><p>{{ t('admin.administrators.noAdminsDesc') }}</p><button @click="openAddAdminModal" class="btn btn-primary"><i class="fas fa-plus"></i> {{ t('admin.administrators.addNew') }}</button></div>
            <div v-else class="admins-grid">
                <div v-for="admin in administrators.data" :key="admin.id" class="admin-card" :class="{ 'super-admin': admin.is_super_admin }">
                    <div v-if="admin.is_super_admin" class="super-badge"><i class="fas fa-crown"></i> Super Admin</div>
                    <div class="admin-header"><img :src="getUserAvatar(admin.user)" :alt="admin.full_name" class="admin-avatar"><div class="admin-info"><h3 class="admin-name">{{ admin.full_name }}</h3><span class="admin-username">@{{ admin.user?.username }}</span><span class="admin-email">{{ admin.user?.email }}</span></div></div>
                    <div class="admin-stats"><div class="stat-item"><span class="stat-label">{{ t('admin.administrators.permissions') }}</span><span class="stat-value"><template v-if="admin.is_super_admin"><i class="fas fa-infinity"></i> {{ t('admin.administrators.allPermissions') }}</template><template v-else>{{ admin.permissions?.length || 0 }}</template></span></div><div class="stat-item"><span class="stat-label">{{ t('admin.administrators.lastLogin') }}</span><span class="stat-value">{{ formatDate(admin.last_login_at) }}</span></div></div>
                    <div v-if="!admin.is_super_admin && admin.permissions?.length > 0" class="permissions-preview"><span v-for="perm in admin.permissions.slice(0, 3)" :key="perm" class="permission-tag">{{ perm.split('.')[0] }}</span><span v-if="admin.permissions.length > 3" class="permission-more">+{{ admin.permissions.length - 3 }}</span></div>
                    <div class="admin-actions"><button v-if="!admin.is_super_admin" @click="openEditPermissionsModal(admin)" class="btn btn-permissions"><i class="fas fa-key"></i> {{ t('admin.administrators.editPermissions') }}</button><button v-if="!admin.is_super_admin" @click="removeAdmin(admin)" class="btn btn-danger-outline"><i class="fas fa-trash"></i></button><span v-if="admin.is_super_admin" class="protected-note"><i class="fas fa-lock"></i> {{ t('admin.administrators.protected') }}</span></div>
                </div>
            </div>
            <div v-if="administrators.links && administrators.links.length > 3" class="pagination-wrapper"><div class="pagination"><template v-for="link in administrators.links" :key="link.label"><Link v-if="link.url" :href="link.url" class="pagination-link" :class="{ active: link.active }" v-html="link.label" preserve-scroll /><span v-else class="pagination-link disabled" v-html="link.label" /></template></div></div>
        </div>

        <!-- Add Admin Modal -->
        <Teleport to="body"><div v-if="showAddAdminModal" class="modal-overlay" @click.self="closeModals"><div class="modal-container modal-lg"><div class="modal-header"><h3 class="modal-title"><i class="fas fa-user-plus"></i> {{ t('admin.dashboard.addAdminTitle') }}</h3><button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button></div><div class="modal-body"><div v-if="errorMessage" class="alert alert-error alert-sm"><i class="fas fa-exclamation-circle"></i> {{ errorMessage }}</div><div class="form-group"><label class="form-label">{{ t('admin.dashboard.selectUser') }}</label><select v-model="selectedUserId" @change="onUserSelected" class="form-select"><option :value="null">{{ t('admin.dashboard.selectUserPlaceholder') }}</option><option v-for="user in nonAdminUsers" :key="user.id" :value="user.id">{{ user.username }} ({{ user.email }})</option></select></div><div class="form-group"><label class="form-label">{{ t('admin.administrators.fullName') }} *</label><input v-model="adminFullName" type="text" class="form-input" :placeholder="t('admin.administrators.fullNamePlaceholder')"></div><div class="permissions-section"><div class="permissions-header"><label class="form-label">{{ t('admin.dashboard.permissionsLabel') }}</label><div class="permissions-actions"><button type="button" @click="selectAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.dashboard.selectAll') }}</button><button type="button" @click="clearAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.common.clear') }}</button></div></div><div class="permissions-grid"><div v-for="(permissions, group) in permissionsConfig" :key="group" class="permission-group"><div class="permission-group-header" @click="toggleGroup(permissions)"><span class="group-name">{{ group }}</span><span class="group-count">{{ permissions.filter(p => selectedPermissions.includes(p.key)).length }}/{{ permissions.length }}</span></div><div class="permission-items"><label v-for="perm in permissions" :key="perm.key" class="permission-item"><input type="checkbox" :checked="selectedPermissions.includes(perm.key)" @change="togglePermission(perm.key)"><span>{{ t(perm.labelKey) }}</span></label></div></div></div></div></div><div class="modal-footer"><button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button><button @click="saveNewAdmin" class="btn btn-primary" :disabled="isLoading"><i v-if="isLoading" class="fas fa-spinner fa-spin"></i><span>{{ t('admin.dashboard.add') }}</span></button></div></div></div></Teleport>

        <!-- Edit Permissions Modal -->
        <Teleport to="body"><div v-if="showEditPermissionsModal" class="modal-overlay" @click.self="closeModals"><div class="modal-container modal-lg"><div class="modal-header"><h3 class="modal-title"><i class="fas fa-edit"></i> {{ t('admin.dashboard.editPermissionsFor', { name: selectedAdmin?.full_name }) }}</h3><button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button></div><div class="modal-body"><div v-if="errorMessage" class="alert alert-error alert-sm"><i class="fas fa-exclamation-circle"></i> {{ errorMessage }}</div><div class="admin-preview"><img :src="getUserAvatar(selectedAdmin?.user)" class="preview-avatar"><div class="preview-info"><span class="preview-name">{{ selectedAdmin?.full_name }}</span><span class="preview-email">{{ selectedAdmin?.user?.email }}</span></div></div><div class="permissions-section"><div class="permissions-header"><label class="form-label">{{ t('admin.dashboard.permissionsLabel') }}</label><div class="permissions-actions"><button type="button" @click="selectAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.dashboard.selectAll') }}</button><button type="button" @click="clearAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.common.clear') }}</button></div></div><div class="permissions-grid"><div v-for="(permissions, group) in permissionsConfig" :key="group" class="permission-group"><div class="permission-group-header" @click="toggleGroup(permissions)"><span class="group-name">{{ group }}</span><span class="group-count">{{ permissions.filter(p => selectedPermissions.includes(p.key)).length }}/{{ permissions.length }}</span></div><div class="permission-items"><label v-for="perm in permissions" :key="perm.key" class="permission-item"><input type="checkbox" :checked="selectedPermissions.includes(perm.key)" @change="togglePermission(perm.key)"><span>{{ t(perm.labelKey) }}</span></label></div></div></div></div></div><div class="modal-footer"><div class="selected-count">{{ selectedPermissions.length }} {{ t('admin.administrators.permissionsSelected') }}</div><button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button><button @click="updatePermissions" class="btn btn-primary" :disabled="isLoading"><i v-if="isLoading" class="fas fa-spinner fa-spin"></i><span>{{ t('admin.common.save') }}</span></button></div></div></div></Teleport>
    </AdminLayout>
</template>

<style scoped>
.alert{display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;border-radius:.5rem;margin-bottom:1.5rem;font-weight:500}.alert-sm{padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem}.alert-success{background:#d1fae5;color:#065f46;border:1px solid #10b981}.alert-error{background:#fee2e2;color:#991b1b;border:1px solid #ef4444}
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem}.stat-card{background:#fff;border-radius:.75rem;padding:1.25rem;display:flex;align-items:center;gap:1rem;box-shadow:0 1px 3px rgba(0,0,0,.1)}.stat-icon{width:3rem;height:3rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-size:1.25rem}.stat-icon.total{background:linear-gradient(135deg,#fef3c7,#fbbf24);color:#92400e}.stat-icon.super{background:linear-gradient(135deg,#fce7f3,#f472b6);color:#9d174d}.stat-icon.regular{background:linear-gradient(135deg,#dbeafe,#60a5fa);color:#1e40af}.stat-info{display:flex;flex-direction:column}.stat-value{font-size:1.5rem;font-weight:700;color:#111827}.stat-label{font-size:.75rem;color:#6b7280;text-transform:uppercase}
.filters-card{background:#fff;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.1)}.filters-row{display:flex;flex-wrap:wrap;gap:1rem;align-items:center}.filter-group{flex-shrink:0}.search-group{flex:1;min-width:200px}.search-input-wrapper{position:relative}.search-input-wrapper>i{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#9ca3af}.search-input{width:100%;padding:.625rem 2.5rem;border:1px solid #e5e7eb;border-radius:.5rem;font-size:.875rem}.search-input:focus{outline:none;border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.1)}.clear-search{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#9ca3af;cursor:pointer}.filter-select{padding:.625rem 1rem;border:1px solid #e5e7eb;border-radius:.5rem;font-size:.875rem;background:#fff}
.admins-container{background:#fff;border-radius:.75rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.1)}.empty-state{text-align:center;padding:4rem 2rem}.empty-state i{font-size:4rem;color:#d1d5db;margin-bottom:1rem}.empty-state h3{font-size:1.25rem;color:#374151;margin-bottom:.5rem}.empty-state p{color:#6b7280;margin-bottom:1.5rem}.admins-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(350px,1fr));gap:1.5rem}
.admin-card{background:#f9fafb;border-radius:.75rem;padding:1.25rem;position:relative;border:2px solid transparent;transition:all .2s}.admin-card.super-admin{background:linear-gradient(135deg,#fef3c7,#fde68a);border-color:#f59e0b}.admin-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1)}.super-badge{position:absolute;top:-.5rem;right:1rem;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:.25rem .75rem;border-radius:9999px;font-size:.65rem;font-weight:700;display:flex;align-items:center;gap:.375rem;text-transform:uppercase}.admin-header{display:flex;gap:1rem;margin-bottom:1rem}.admin-avatar{width:4rem;height:4rem;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.1)}.admin-info{display:flex;flex-direction:column;gap:.125rem}.admin-name{font-size:1.1rem;font-weight:700;color:#111827;margin:0}.admin-username{font-size:.8rem;color:#6b7280}.admin-email{font-size:.75rem;color:#9ca3af}.admin-stats{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;padding:.75rem;background:#fff;border-radius:.5rem}.stat-item{display:flex;flex-direction:column;gap:.125rem}.stat-item .stat-label{font-size:.65rem;color:#6b7280;text-transform:uppercase}.stat-item .stat-value{font-size:.85rem;color:#111827;font-weight:600}.permissions-preview{display:flex;flex-wrap:wrap;gap:.375rem;margin-bottom:1rem}.permission-tag{background:#dbeafe;color:#1e40af;padding:.125rem .5rem;border-radius:9999px;font-size:.65rem;font-weight:500;text-transform:uppercase}.permission-more{background:#f3f4f6;color:#6b7280;padding:.125rem .5rem;border-radius:9999px;font-size:.65rem}.admin-actions{display:flex;gap:.5rem;padding-top:1rem;border-top:1px solid #e5e7eb}.protected-note{font-size:.75rem;color:#6b7280;display:flex;align-items:center;gap:.375rem}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.25rem;border-radius:.5rem;font-weight:600;cursor:pointer;transition:all .2s;border:none;text-decoration:none;font-size:.875rem}.btn-primary{background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff}.btn-primary:hover:not(:disabled){box-shadow:0 4px 12px rgba(220,38,38,.3);transform:translateY(-1px)}.btn-primary:disabled{opacity:.6;cursor:not-allowed}.btn-secondary{background:#f3f4f6;color:#374151}.btn-secondary:hover{background:#e5e7eb}.btn-permissions{flex:1;background:#dbeafe;color:#1e40af;justify-content:center}.btn-permissions:hover{background:#bfdbfe}.btn-danger-outline{background:#fee2e2;color:#991b1b}.btn-danger-outline:hover{background:#dc2626;color:#fff}.btn-sm{padding:.375rem .75rem;font-size:.875rem}.btn-outline{background:transparent;border:1px solid #d1d5db;color:#374151}.btn-outline:hover{background:#f3f4f6}
.pagination-wrapper{display:flex;justify-content:center;padding-top:1.5rem;margin-top:1.5rem;border-top:1px solid #e5e7eb}.pagination{display:flex;gap:.25rem}.pagination-link{padding:.5rem .75rem;border-radius:.375rem;font-size:.875rem;color:#374151;text-decoration:none}.pagination-link:hover:not(.disabled):not(.active){background:#f3f4f6}.pagination-link.active{background:#dc2626;color:#fff}.pagination-link.disabled{color:#d1d5db}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1000;padding:1rem}.modal-container{background:#fff;border-radius:1rem;width:100%;max-width:500px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column}.modal-lg{max-width:700px}.modal-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #e5e7eb}.modal-title{font-size:1.25rem;font-weight:700;color:#111827;margin:0;display:flex;align-items:center;gap:.5rem}.modal-title i{color:#dc2626}.modal-close{background:none;border:none;color:#6b7280;font-size:1.25rem;cursor:pointer}.modal-close:hover{color:#111827}.modal-body{padding:1.5rem;overflow-y:auto}.modal-footer{display:flex;justify-content:flex-end;align-items:center;gap:.75rem;padding:1.25rem 1.5rem;border-top:1px solid #e5e7eb}.selected-count{margin-right:auto;font-size:.875rem;color:#6b7280}
.admin-preview{display:flex;align-items:center;gap:1rem;padding:1rem;background:#f9fafb;border-radius:.75rem;margin-bottom:1.5rem}.preview-avatar{width:3.5rem;height:3.5rem;border-radius:50%;object-fit:cover}.preview-info{flex:1;display:flex;flex-direction:column}.preview-name{font-weight:600;color:#111827}.preview-email{font-size:.875rem;color:#6b7280}
.form-group{margin-bottom:1.5rem}.form-label{display:block;font-weight:600;color:#374151;margin-bottom:.5rem}.form-select,.form-input{width:100%;padding:.75rem 1rem;border:1px solid #d1d5db;border-radius:.5rem;font-size:1rem}.form-select:focus,.form-input:focus{outline:none;border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.1)}
.permissions-section{margin-top:1rem}.permissions-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem}.permissions-actions{display:flex;gap:.5rem}.permissions-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;max-height:400px;overflow-y:auto}.permission-group{background:#f9fafb;border-radius:.5rem;overflow:hidden}.permission-group-header{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;background:#e5e7eb;cursor:pointer;transition:background .2s}.permission-group-header:hover{background:#d1d5db}.group-name{font-weight:600;color:#374151;font-size:.875rem}.group-count{font-size:.75rem;color:#6b7280;background:#fff;padding:.125rem .5rem;border-radius:9999px}.permission-items{padding:.75rem 1rem;display:flex;flex-direction:column;gap:.5rem}.permission-item{display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.875rem;color:#4b5563}.permission-item:hover{color:#111827}.permission-item input{accent-color:#dc2626}
.slide-down-enter-active,.slide-down-leave-active{transition:all .3s ease}.slide-down-enter-from,.slide-down-leave-to{opacity:0;transform:translateY(-10px)}
@media(max-width:1024px){.stats-row{grid-template-columns:repeat(3,1fr)}.admins-grid{grid-template-columns:repeat(auto-fill,minmax(300px,1fr))}}
@media(max-width:768px){.stats-row{grid-template-columns:1fr}.filters-row{flex-direction:column;align-items:stretch}.search-group{min-width:unset}.filter-select{width:100%}.btn-primary{margin-left:0;width:100%;justify-content:center}.admins-grid{grid-template-columns:1fr}.modal-container{margin:.5rem;max-height:95vh}.permissions-grid{grid-template-columns:1fr}.modal-footer{flex-direction:column;gap:.5rem}.selected-count{margin-right:0;text-align:center}.modal-footer .btn{width:100%;justify-content:center}}
@media(max-width:480px){.admins-container{padding:1rem}.admin-card{padding:1rem}.admin-header{flex-direction:column;align-items:center;text-align:center}.admin-avatar{width:5rem;height:5rem}.admin-stats{grid-template-columns:1fr}.admin-actions{flex-direction:column}.btn-permissions,.btn-danger-outline{justify-content:center}}
@media(max-width:360px){.stat-card{gap:.75rem}.stat-icon{width:2.5rem;height:2.5rem;font-size:1rem}.stat-value{font-size:1.25rem}}
</style>
