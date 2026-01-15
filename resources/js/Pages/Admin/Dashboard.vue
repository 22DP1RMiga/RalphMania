<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const page = usePage();
const user = computed(() => page.props.auth.user);
const isSuperAdmin = computed(() => user.value?.is_super_admin || false);

const props = defineProps({
    stats: { type: Object, default: () => ({ totalUsers: 0, totalOrders: 0, totalProducts: 0, totalRevenue: 0, pendingOrders: 0, pendingReviews: 0, unreadContacts: 0, newUsersToday: 0 }) },
    recentOrders: { type: Array, default: () => [] },
    administrators: { type: Array, default: () => [] },
    allUsers: { type: Array, default: () => [] },
});

// Toast
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Available permissions
const availablePermissions = computed(() => ({
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
    ],
    [t('admin.permissions.reviews')]: [
        { key: 'reviews.view', labelKey: 'admin.permissions.reviewsView' },
        { key: 'reviews.moderate', labelKey: 'admin.permissions.reviewsModerate' },
        { key: 'reviews.delete', labelKey: 'admin.permissions.reviewsDelete' },
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

// Modal states
const showAddAdminModal = ref(false);
const showEditPermissionsModal = ref(false);
const showRemoveAdminModal = ref(false);
const selectedAdmin = ref(null);
const selectedUserId = ref(null);
const selectedPermissions = ref([]);
const isLoading = ref(false);

// Flash messages
onMounted(() => {
    const flash = page.props.flash;
    if (flash?.success) { toastMessage.value = flash.success; toastType.value = 'success'; showToast.value = true; }
    if (flash?.error) { toastMessage.value = flash.error; toastType.value = 'error'; showToast.value = true; }
});

watch(() => page.props.flash, (flash) => {
    if (flash?.success) { toastMessage.value = flash.success; toastType.value = 'success'; showToast.value = true; }
    if (flash?.error) { toastMessage.value = flash.error; toastType.value = 'error'; showToast.value = true; }
}, { deep: true });

// Open modals
const openAddAdminModal = () => { selectedUserId.value = null; selectedPermissions.value = []; showAddAdminModal.value = true; };
const openEditPermissionsModal = (admin) => { selectedAdmin.value = admin; selectedPermissions.value = [...(admin.permissions || [])]; showEditPermissionsModal.value = true; };
const openRemoveAdminModal = (admin) => { selectedAdmin.value = admin; showRemoveAdminModal.value = true; };

// Close modals
const closeModals = () => {
    showAddAdminModal.value = false;
    showEditPermissionsModal.value = false;
    showRemoveAdminModal.value = false;
    selectedAdmin.value = null;
    selectedUserId.value = null;
    selectedPermissions.value = [];
};

// Permission helpers
const togglePermission = (key) => {
    const idx = selectedPermissions.value.indexOf(key);
    if (idx > -1) selectedPermissions.value.splice(idx, 1);
    else selectedPermissions.value.push(key);
};

const toggleGroup = (perms) => {
    const keys = perms.map(p => p.key);
    const all = keys.every(k => selectedPermissions.value.includes(k));
    if (all) selectedPermissions.value = selectedPermissions.value.filter(p => !keys.includes(p));
    else keys.forEach(k => { if (!selectedPermissions.value.includes(k)) selectedPermissions.value.push(k); });
};

const selectAllPermissions = () => { selectedPermissions.value = Object.values(availablePermissions.value).flat().map(p => p.key); };
const clearAllPermissions = () => { selectedPermissions.value = []; };

// Save actions
const saveNewAdmin = () => {
    if (!selectedUserId.value) { toastMessage.value = t('admin.dashboard.selectUserError'); toastType.value = 'error'; showToast.value = true; return; }
    isLoading.value = true;
    router.post('/admin/administrators', { user_id: selectedUserId.value, permissions: selectedPermissions.value }, {
        preserveScroll: true,
        onSuccess: () => { toastMessage.value = t('admin.dashboard.adminAdded'); toastType.value = 'success'; showToast.value = true; closeModals(); },
        onError: (err) => { toastMessage.value = Object.values(err)[0] || t('admin.dashboard.addAdminError'); toastType.value = 'error'; showToast.value = true; },
        onFinish: () => { isLoading.value = false; },
    });
};

const updatePermissions = () => {
    if (!selectedAdmin.value) return;
    isLoading.value = true;
    router.put(`/admin/administrators/${selectedAdmin.value.id}/permissions`, { permissions: selectedPermissions.value }, {
        preserveScroll: true,
        onSuccess: () => { toastMessage.value = t('admin.dashboard.permissionsUpdated'); toastType.value = 'success'; showToast.value = true; closeModals(); },
        onError: () => { toastMessage.value = t('admin.dashboard.updateError'); toastType.value = 'error'; showToast.value = true; },
        onFinish: () => { isLoading.value = false; },
    });
};

const confirmRemoveAdmin = () => {
    if (!selectedAdmin.value) return;
    isLoading.value = true;
    router.delete(`/admin/administrators/${selectedAdmin.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { toastMessage.value = t('admin.dashboard.adminRemoved'); toastType.value = 'success'; showToast.value = true; closeModals(); },
        onFinish: () => { isLoading.value = false; },
    });
};

// Helpers
const formatCurrency = (amount) => new Intl.NumberFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', { style: 'currency', currency: 'EUR' }).format(amount);
const formatDate = (date) => date ? new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US') : t('admin.dashboard.noData');
const currentDateFormatted = computed(() => new Date().toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
const nonAdminUsers = computed(() => { const ids = props.administrators.map(a => a.user_id); return props.allUsers.filter(u => !ids.includes(u.id)); });
const getUserAvatar = (user) => {
    if (!user?.profile_picture) return '/img/default-avatar.png';
    if (user.profile_picture.startsWith('http')) return user.profile_picture;
    return `/storage/${user.profile_picture}`;
};
</script>

<template>
    <Head :title="t('admin.dashboard.title')" />
    <AdminLayout>
        <template #title>{{ t('admin.dashboard.overview') }}</template>

        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h2 class="welcome-title">
                    {{ t('admin.dashboard.welcome', { name: user.username }) }}
                    <span v-if="isSuperAdmin" class="super-badge"><i class="fas fa-crown"></i> Super Admin</span>
                </h2>
                <p class="welcome-subtitle">{{ t('admin.dashboard.welcomeSubtitle') }}</p>
            </div>
            <div class="welcome-date">{{ currentDateFormatted }}</div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-users">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalUsers }}</span>
                    <span class="stat-label">{{ t('admin.dashboard.stats.users') }}</span>
                </div>
                <div class="stat-badge" v-if="stats.newUsersToday > 0">+{{ stats.newUsersToday }} {{ t('admin.dashboard.stats.today') }}</div>
            </div>
            <div class="stat-card stat-orders">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalOrders }}</span>
                    <span class="stat-label">{{ t('admin.dashboard.stats.orders') }}</span>
                </div>
                <div class="stat-badge stat-badge-warning" v-if="stats.pendingOrders > 0">{{ stats.pendingOrders }} {{ t('admin.dashboard.stats.pending') }}</div>
            </div>
            <div class="stat-card stat-products">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalProducts }}</span>
                    <span class="stat-label">{{ t('admin.dashboard.stats.products') }}</span>
                </div>
            </div>
            <div class="stat-card stat-revenue">
                <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ formatCurrency(stats.totalRevenue) }}</span>
                    <span class="stat-label">{{ t('admin.dashboard.stats.revenue') }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 class="section-title">{{ t('admin.dashboard.quickActions') }}</h3>
            <div class="actions-grid">
                <Link href="/admin/products/create" class="action-card"><i class="fas fa-plus"></i><span>{{ t('admin.dashboard.newProduct') }}</span></Link>
                <Link href="/admin/content/create" class="action-card"><i class="fas fa-newspaper"></i><span>{{ t('admin.dashboard.newContent') }}</span></Link>
                <Link v-if="stats.pendingOrders > 0" href="/admin/orders" class="action-card action-card-warning"><i class="fas fa-clock"></i><span>{{ stats.pendingOrders }} {{ t('admin.dashboard.awaitingProcessing') }}</span></Link>
                <Link v-if="stats.unreadContacts > 0" href="/admin/contacts" class="action-card action-card-info"><i class="fas fa-envelope"></i><span>{{ stats.unreadContacts }} {{ t('admin.dashboard.unread') }}</span></Link>
            </div>
        </div>

        <!-- Admin Management (Super Admin Only) -->
        <div v-if="isSuperAdmin" class="admin-section">
            <div class="section-header">
                <h3 class="section-title"><i class="fas fa-user-shield"></i> {{ t('admin.dashboard.adminManagement') }}</h3>
                <button @click="openAddAdminModal" class="btn btn-primary"><i class="fas fa-plus"></i><span>{{ t('admin.dashboard.addAdmin') }}</span></button>
            </div>

            <!-- Admin Cards -->
            <div class="admin-grid">
                <div v-for="admin in administrators" :key="admin.id" class="admin-card" :class="{ 'super-admin-card': admin.is_super_admin }">
                    <div v-if="admin.is_super_admin" class="card-super-badge"><i class="fas fa-crown"></i> Super</div>
                    <div class="admin-card-header">
                        <img :src="getUserAvatar(admin.user)" class="admin-avatar">
                        <div class="admin-info">
                            <span class="admin-name">{{ admin.full_name }}</span>
                            <span class="admin-email">{{ admin.user?.email }}</span>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        <div class="admin-stat"><span class="stat-label">{{ t('admin.dashboard.table.permissions') }}</span><span class="stat-value">{{ admin.is_super_admin ? t('admin.dashboard.allPermissions') : (admin.permissions?.length || 0) }}</span></div>
                        <div class="admin-stat"><span class="stat-label">{{ t('admin.dashboard.table.lastLogin') }}</span><span class="stat-value">{{ formatDate(admin.last_login_at) }}</span></div>
                    </div>
                    <div class="admin-card-footer" v-if="!admin.is_super_admin">
                        <button @click="openEditPermissionsModal(admin)" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i> {{ t('admin.common.edit') }}</button>
                        <button @click="openRemoveAdminModal(admin)" class="btn btn-sm btn-danger-outline"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="admin-card-footer" v-else><span class="protected-label"><i class="fas fa-lock"></i> {{ t('admin.dashboard.protected') }}</span></div>
                </div>
            </div>
        </div>

        <!-- Add Admin Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showAddAdminModal" class="modal-overlay" @click.self="closeModals">
                    <div class="modal-container modal-lg">
                        <div class="modal-header">
                            <h3 class="modal-title"><i class="fas fa-user-plus"></i> {{ t('admin.dashboard.addAdminTitle') }}</h3>
                            <button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">{{ t('admin.dashboard.selectUser') }}</label>
                                <select v-model="selectedUserId" class="form-select">
                                    <option :value="null">{{ t('admin.dashboard.selectUserPlaceholder') }}</option>
                                    <option v-for="u in nonAdminUsers" :key="u.id" :value="u.id">{{ u.username }} ({{ u.email }})</option>
                                </select>
                            </div>
                            <div class="permissions-section">
                                <div class="permissions-header">
                                    <label class="form-label">{{ t('admin.dashboard.permissionsLabel') }}</label>
                                    <div class="permissions-actions">
                                        <button @click="selectAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.dashboard.selectAll') }}</button>
                                        <button @click="clearAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.common.clear') }}</button>
                                    </div>
                                </div>
                                <div class="permissions-grid">
                                    <div v-for="(perms, group) in availablePermissions" :key="group" class="permission-group">
                                        <div class="permission-group-header" @click="toggleGroup(perms)">
                                            <span>{{ group }}</span>
                                            <span class="group-count">{{ perms.filter(p => selectedPermissions.includes(p.key)).length }}/{{ perms.length }}</span>
                                        </div>
                                        <div class="permission-items">
                                            <label v-for="perm in perms" :key="perm.key" class="permission-item">
                                                <input type="checkbox" :checked="selectedPermissions.includes(perm.key)" @change="togglePermission(perm.key)">
                                                <span>{{ t(perm.labelKey) }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button>
                            <button @click="saveNewAdmin" class="btn btn-primary" :disabled="isLoading">
                                <i v-if="isLoading" class="fas fa-spinner fa-spin"></i> {{ t('admin.dashboard.add') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Edit Permissions Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showEditPermissionsModal" class="modal-overlay" @click.self="closeModals">
                    <div class="modal-container modal-lg">
                        <div class="modal-header">
                            <h3 class="modal-title"><i class="fas fa-edit"></i> {{ t('admin.dashboard.editPermissionsFor', { name: selectedAdmin?.full_name }) }}</h3>
                            <button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="admin-preview">
                                <img :src="getUserAvatar(selectedAdmin?.user)" class="preview-avatar">
                                <div class="preview-info"><span class="preview-name">{{ selectedAdmin?.full_name }}</span><span class="preview-email">{{ selectedAdmin?.user?.email }}</span></div>
                            </div>
                            <div class="permissions-section">
                                <div class="permissions-header">
                                    <label class="form-label">{{ t('admin.dashboard.permissionsLabel') }}</label>
                                    <div class="permissions-actions">
                                        <button @click="selectAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.dashboard.selectAll') }}</button>
                                        <button @click="clearAllPermissions" class="btn btn-sm btn-outline">{{ t('admin.common.clear') }}</button>
                                    </div>
                                </div>
                                <div class="permissions-grid">
                                    <div v-for="(perms, group) in availablePermissions" :key="group" class="permission-group">
                                        <div class="permission-group-header" @click="toggleGroup(perms)">
                                            <span>{{ group }}</span>
                                            <span class="group-count">{{ perms.filter(p => selectedPermissions.includes(p.key)).length }}/{{ perms.length }}</span>
                                        </div>
                                        <div class="permission-items">
                                            <label v-for="perm in perms" :key="perm.key" class="permission-item">
                                                <input type="checkbox" :checked="selectedPermissions.includes(perm.key)" @change="togglePermission(perm.key)">
                                                <span>{{ t(perm.labelKey) }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <span class="selected-count">{{ selectedPermissions.length }} {{ t('admin.administrators.permissionsSelected') }}</span>
                            <button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button>
                            <button @click="updatePermissions" class="btn btn-primary" :disabled="isLoading">
                                <i v-if="isLoading" class="fas fa-spinner fa-spin"></i> {{ t('admin.common.save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Remove Admin Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showRemoveAdminModal" class="modal-overlay" @click.self="closeModals">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3 class="modal-title"><i class="fas fa-user-minus"></i> {{ t('admin.dashboard.removeAdminTitle') }}</h3>
                            <button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="admin-preview">
                                <img :src="getUserAvatar(selectedAdmin?.user)" class="preview-avatar">
                                <div class="preview-info"><span class="preview-name">{{ selectedAdmin?.full_name }}</span><span class="preview-email">{{ selectedAdmin?.user?.email }}</span></div>
                            </div>
                            <div class="danger-box">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>{{ t('admin.dashboard.removeWarningTitle') }}</strong>
                                    <p>{{ t('admin.dashboard.removeWarningText') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button>
                            <button @click="confirmRemoveAdmin" class="btn btn-danger" :disabled="isLoading">
                                <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-user-minus"></i> {{ t('admin.dashboard.confirmRemove') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
/* Welcome Section */
.welcome-section { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1.5rem 2rem; border-radius: 1rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 1rem; }
.welcome-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.welcome-subtitle { color: #6b7280; margin: 0; }
.welcome-date { color: #6b7280; font-size: 0.875rem; }
.super-badge { background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 0.375rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; color: #92400e; display: inline-flex; align-items: center; gap: 0.375rem; }

/* Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 1.5rem; }
.stat-card { background: white; border-radius: 1rem; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: relative; }
.stat-icon { width: 3.5rem; height: 3.5rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.stat-users .stat-icon { background: linear-gradient(135deg, #dbeafe, #93c5fd); color: #1e40af; }
.stat-orders .stat-icon { background: linear-gradient(135deg, #fef3c7, #fcd34d); color: #92400e; }
.stat-products .stat-icon { background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; }
.stat-revenue .stat-icon { background: linear-gradient(135deg, #fce7f3, #f9a8d4); color: #9d174d; }
.stat-content { display: flex; flex-direction: column; }
.stat-value { font-size: 1.75rem; font-weight: 700; color: #111827; }
.stat-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }
.stat-badge { position: absolute; top: 0.75rem; right: 0.75rem; background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 1rem; font-size: 0.65rem; font-weight: 600; }
.stat-badge-warning { background: #fef3c7; color: #92400e; }

/* Quick Actions */
.quick-actions { margin-bottom: 1.5rem; }
.section-title { font-size: 1.125rem; font-weight: 700; color: #111827; margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem; }
.section-title i { color: #dc2626; }
.actions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.action-card { background: white; border-radius: 0.75rem; padding: 1.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; text-decoration: none; color: #374151; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.action-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.action-card i { font-size: 1.5rem; color: #dc2626; }
.action-card-warning { border: 2px solid #fbbf24; }
.action-card-warning i { color: #f59e0b; }
.action-card-info { border: 2px solid #3b82f6; }
.action-card-info i { color: #3b82f6; }

/* Admin Section */
.admin-section { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.admin-card { background: #f9fafb; border-radius: 0.75rem; padding: 1.25rem; position: relative; border: 2px solid transparent; transition: all 0.2s; }
.admin-card.super-admin-card { background: linear-gradient(135deg, #fef3c7, #fde68a); border-color: #f59e0b; }
.card-super-badge { position: absolute; top: -0.5rem; right: 1rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 700; display: flex; align-items: center; gap: 0.25rem; }
.admin-card-header { display: flex; gap: 1rem; margin-bottom: 1rem; }
.admin-avatar { width: 3.5rem; height: 3.5rem; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.admin-info { display: flex; flex-direction: column; }
.admin-name { font-weight: 700; color: #111827; }
.admin-email { font-size: 0.75rem; color: #6b7280; }
.admin-card-body { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; padding: 0.75rem; background: white; border-radius: 0.5rem; margin-bottom: 1rem; }
.admin-stat { display: flex; flex-direction: column; }
.admin-stat .stat-label { font-size: 0.65rem; color: #6b7280; text-transform: uppercase; }
.admin-stat .stat-value { font-size: 0.85rem; color: #111827; font-weight: 600; }
.admin-card-footer { display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; }
.protected-label { font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 0.375rem; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; font-size: 0.875rem; }
.btn-primary { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
.btn-primary:hover:not(:disabled) { box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-danger { background: #dc2626; color: white; }
.btn-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
.btn-outline { background: white; border: 1px solid #d1d5db; color: #374151; }
.btn-outline:hover { background: #f3f4f6; }
.btn-danger-outline { background: #fee2e2; color: #991b1b; border: none; }
.btn-danger-outline:hover { background: #dc2626; color: white; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal-container { background: white; border-radius: 1rem; width: 100%; max-width: 500px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
.modal-lg { max-width: 700px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.modal-title i { color: #dc2626; }
.modal-close { background: none; border: none; color: #6b7280; font-size: 1.25rem; cursor: pointer; }
.modal-body { padding: 1.5rem; overflow-y: auto; }
.modal-footer { display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; }
.selected-count { margin-right: auto; font-size: 0.875rem; color: #6b7280; }

/* Admin Preview */
.admin-preview { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f9fafb; border-radius: 0.75rem; margin-bottom: 1.5rem; }
.preview-avatar { width: 3.5rem; height: 3.5rem; border-radius: 50%; object-fit: cover; }
.preview-info { display: flex; flex-direction: column; }
.preview-name { font-weight: 600; color: #111827; }
.preview-email { font-size: 0.875rem; color: #6b7280; }

/* Danger Box */
.danger-box { display: flex; gap: 1rem; padding: 1rem; background: #fee2e2; border-radius: 0.75rem; color: #991b1b; }
.danger-box i { font-size: 1.5rem; flex-shrink: 0; }
.danger-box strong { display: block; margin-bottom: 0.25rem; }
.danger-box p { margin: 0; font-size: 0.875rem; opacity: 0.9; }

/* Form */
.form-group { margin-bottom: 1.5rem; }
.form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
.form-select { width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; }
.form-select:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }

/* Permissions */
.permissions-section { margin-top: 1rem; }
.permissions-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem; }
.permissions-actions { display: flex; gap: 0.5rem; }
.permissions-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; max-height: 400px; overflow-y: auto; }
.permission-group { background: #f9fafb; border-radius: 0.5rem; overflow: hidden; }
.permission-group-header { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: #e5e7eb; cursor: pointer; font-weight: 600; color: #374151; font-size: 0.875rem; }
.permission-group-header:hover { background: #d1d5db; }
.group-count { font-size: 0.7rem; color: #6b7280; background: white; padding: 0.125rem 0.5rem; border-radius: 9999px; }
.permission-items { padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
.permission-item { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem; color: #4b5563; }
.permission-item:hover { color: #111827; }
.permission-item input { accent-color: #dc2626; }

/* Transitions */
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-container, .modal-leave-to .modal-container { transform: scale(0.9); }

/* Responsive */
@media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .actions-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .welcome-section { flex-direction: column; align-items: flex-start; padding: 1.25rem; }
    .stats-grid { grid-template-columns: 1fr; }
    .actions-grid { grid-template-columns: 1fr; }
    .section-header { flex-direction: column; align-items: stretch; }
    .section-header .btn { width: 100%; justify-content: center; }
    .admin-grid { grid-template-columns: 1fr; }
    .permissions-grid { grid-template-columns: 1fr; }
    .modal-lg { max-width: 100%; }
    .modal-footer { flex-direction: column; gap: 0.5rem; }
    .modal-footer .btn { width: 100%; justify-content: center; }
    .selected-count { text-align: center; margin-right: 0; }
}
@media (max-width: 480px) {
    .admin-card-body { grid-template-columns: 1fr; }
    .admin-card-footer { flex-direction: column; }
    .admin-card-footer .btn { width: 100%; justify-content: center; }
}
</style>
