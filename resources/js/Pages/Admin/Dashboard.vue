<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const page = usePage();
const user = computed(() => page.props.auth.user);
const isSuperAdmin = computed(() => user.value?.is_super_admin || false);

// Dashboard data from props
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalUsers: 0,
            totalOrders: 0,
            totalProducts: 0,
            totalRevenue: 0,
            pendingOrders: 0,
            pendingReviews: 0,
            unreadContacts: 0,
            newUsersToday: 0,
        }),
    },
    recentOrders: {
        type: Array,
        default: () => [],
    },
    administrators: {
        type: Array,
        default: () => [],
    },
    allUsers: {
        type: Array,
        default: () => [],
    },
});

// Available permissions with translation keys
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

// Modal states
const showAddAdminModal = ref(false);
const showEditPermissionsModal = ref(false);
const selectedAdmin = ref(null);
const selectedUserId = ref(null);
const selectedPermissions = ref([]);
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Open add admin modal
const openAddAdminModal = () => {
    selectedUserId.value = null;
    selectedPermissions.value = [];
    showAddAdminModal.value = true;
};

// Open edit permissions modal
const openEditPermissionsModal = (admin) => {
    selectedAdmin.value = admin;
    selectedPermissions.value = [...(admin.permissions || [])];
    showEditPermissionsModal.value = true;
};

// Close modals
const closeModals = () => {
    showAddAdminModal.value = false;
    showEditPermissionsModal.value = false;
    selectedAdmin.value = null;
    selectedUserId.value = null;
    selectedPermissions.value = [];
    errorMessage.value = '';
};

// Toggle permission
const togglePermission = (permissionKey) => {
    const index = selectedPermissions.value.indexOf(permissionKey);
    if (index > -1) {
        selectedPermissions.value.splice(index, 1);
    } else {
        selectedPermissions.value.push(permissionKey);
    }
};

// Toggle all permissions in group
const toggleGroup = (groupPermissions) => {
    const groupKeys = groupPermissions.map(p => p.key);
    const allSelected = groupKeys.every(key => selectedPermissions.value.includes(key));

    if (allSelected) {
        selectedPermissions.value = selectedPermissions.value.filter(p => !groupKeys.includes(p));
    } else {
        groupKeys.forEach(key => {
            if (!selectedPermissions.value.includes(key)) {
                selectedPermissions.value.push(key);
            }
        });
    }
};

// Select all permissions
const selectAllPermissions = () => {
    const allKeys = Object.values(availablePermissions.value).flat().map(p => p.key);
    selectedPermissions.value = [...allKeys];
};

// Clear all permissions
const clearAllPermissions = () => {
    selectedPermissions.value = [];
};

// Save new administrator
const saveNewAdmin = () => {
    if (!selectedUserId.value) {
        errorMessage.value = t('admin.dashboard.selectUserError');
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';

    router.post('/admin/administrators', {
        user_id: selectedUserId.value,
        permissions: selectedPermissions.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = t('admin.dashboard.adminAdded');
            closeModals();
            setTimeout(() => successMessage.value = '', 3000);
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0] || t('admin.dashboard.addAdminError');
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

// Update administrator permissions
const updatePermissions = () => {
    if (!selectedAdmin.value) return;

    isLoading.value = true;
    errorMessage.value = '';

    router.put(`/admin/administrators/${selectedAdmin.value.id}/permissions`, {
        permissions: selectedPermissions.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = t('admin.dashboard.permissionsUpdated');
            closeModals();
            setTimeout(() => successMessage.value = '', 3000);
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0] || t('admin.dashboard.updateError');
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

// Remove administrator
const removeAdmin = (adminId) => {
    if (!confirm(t('admin.dashboard.removeAdminConfirm'))) return;

    router.delete(`/admin/administrators/${adminId}`, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = t('admin.dashboard.adminRemoved');
            setTimeout(() => successMessage.value = '', 3000);
        },
    });
};

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

// Format date
const formatDate = (date) => {
    if (!date) return t('admin.dashboard.noData');
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US');
};

// Current date formatted
const currentDateFormatted = computed(() => {
    return new Date().toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

// Get non-admin users for selection
const nonAdminUsers = computed(() => {
    const adminUserIds = props.administrators.map(a => a.user_id);
    return props.allUsers.filter(u => !adminUserIds.includes(u.id));
});
</script>

<template>
    <Head :title="t('admin.dashboard.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.dashboard.overview') }}</template>

        <!-- Success/Error Messages -->
        <Transition name="slide-down">
            <div v-if="successMessage" class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ successMessage }}
            </div>
        </Transition>

        <Transition name="slide-down">
            <div v-if="errorMessage && !showAddAdminModal && !showEditPermissionsModal" class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ errorMessage }}
            </div>
        </Transition>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h2 class="welcome-title">
                    {{ t('admin.dashboard.welcome', { name: user.username }) }}
                    <span v-if="isSuperAdmin" class="super-badge">
                        <i class="fas fa-crown"></i> Super Admin
                    </span>
                </h2>
                <p class="welcome-subtitle">{{ t('admin.dashboard.welcomeSubtitle') }}</p>
            </div>
            <div class="welcome-date">
                {{ currentDateFormatted }}
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-users">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalUsers }}</span>
                    <span class="stat-label">{{ t('admin.dashboard.stats.users') }}</span>
                </div>
                <div class="stat-badge" v-if="stats.newUsersToday > 0">
                    +{{ stats.newUsersToday }} {{ t('admin.dashboard.stats.today') }}
                </div>
            </div>

            <div class="stat-card stat-orders">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalOrders }}</span>
                    <span class="stat-label">{{ t('admin.dashboard.stats.orders') }}</span>
                </div>
                <div class="stat-badge stat-badge-warning" v-if="stats.pendingOrders > 0">
                    {{ stats.pendingOrders }} {{ t('admin.dashboard.stats.pending') }}
                </div>
            </div>

            <div class="stat-card stat-products">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalProducts }}</span>
                    <span class="stat-label">{{ t('admin.dashboard.stats.products') }}</span>
                </div>
            </div>

            <div class="stat-card stat-revenue">
                <div class="stat-icon">
                    <i class="fas fa-euro-sign"></i>
                </div>
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
                <Link href="/admin/products/create" class="action-card">
                    <i class="fas fa-plus"></i>
                    <span>{{ t('admin.dashboard.newProduct') }}</span>
                </Link>
                <Link href="/admin/content/create" class="action-card">
                    <i class="fas fa-newspaper"></i>
                    <span>{{ t('admin.dashboard.newContent') }}</span>
                </Link>
                <Link href="/admin/orders" class="action-card action-card-warning" v-if="stats.pendingOrders > 0">
                    <i class="fas fa-clock"></i>
                    <span>{{ stats.pendingOrders }} {{ t('admin.dashboard.awaitingProcessing') }}</span>
                </Link>
                <Link href="/admin/contacts" class="action-card action-card-info" v-if="stats.unreadContacts > 0">
                    <i class="fas fa-envelope"></i>
                    <span>{{ stats.unreadContacts }} {{ t('admin.dashboard.unread') }}</span>
                </Link>
            </div>
        </div>

        <!-- Super Admin Section: Administrator Management -->
        <div v-if="isSuperAdmin" class="admin-management-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-user-shield"></i>
                    {{ t('admin.dashboard.adminManagement') }}
                </h3>
                <button @click="openAddAdminModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="btn-text">{{ t('admin.dashboard.addAdmin') }}</span>
                </button>
            </div>

            <!-- Administrators Table (Desktop) -->
            <div class="admin-table-container desktop-only">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>{{ t('admin.dashboard.table.user') }}</th>
                        <th>{{ t('admin.dashboard.table.email') }}</th>
                        <th>{{ t('admin.dashboard.table.status') }}</th>
                        <th>{{ t('admin.dashboard.table.permissions') }}</th>
                        <th>{{ t('admin.dashboard.table.lastLogin') }}</th>
                        <th>{{ t('admin.dashboard.table.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="admin in administrators" :key="admin.id" :class="{ 'super-admin-row': admin.is_super_admin }">
                        <td>
                            <div class="user-cell">
                                <img :src="admin.user?.profile_picture ? `/storage/${admin.user.profile_picture}` : '/img/default-avatar.png'" class="user-avatar-small">
                                <span>{{ admin.full_name }}</span>
                            </div>
                        </td>
                        <td>{{ admin.user?.email }}</td>
                        <td>
                                <span v-if="admin.is_super_admin" class="status-badge status-super">
                                    <i class="fas fa-crown"></i> Super Admin
                                </span>
                            <span v-else class="status-badge status-admin">
                                    <i class="fas fa-user-shield"></i> Admin
                                </span>
                        </td>
                        <td>
                                <span v-if="admin.is_super_admin" class="permissions-badge permissions-all">
                                    {{ t('admin.dashboard.allPermissions') }}
                                </span>
                            <span v-else class="permissions-badge">
                                    {{ admin.permissions?.length || 0 }} {{ t('admin.dashboard.permissionsCount') }}
                                </span>
                        </td>
                        <td>{{ formatDate(admin.last_login_at) }}</td>
                        <td>
                            <div class="action-buttons">
                                <button
                                    v-if="!admin.is_super_admin"
                                    @click="openEditPermissionsModal(admin)"
                                    class="btn-icon btn-icon-edit"
                                    :title="t('admin.dashboard.editPermissions')"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    v-if="!admin.is_super_admin"
                                    @click="removeAdmin(admin.id)"
                                    class="btn-icon btn-icon-delete"
                                    :title="t('admin.dashboard.removeAdmin')"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                                <span v-if="admin.is_super_admin" class="protected-label">
                                        <i class="fas fa-lock"></i> {{ t('admin.dashboard.protected') }}
                                    </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Administrators Cards (Mobile) -->
            <div class="admin-cards mobile-only">
                <div v-for="admin in administrators" :key="admin.id" class="admin-card" :class="{ 'super-admin-card': admin.is_super_admin }">
                    <div class="admin-card-header">
                        <div class="user-cell">
                            <img :src="admin.user?.profile_picture ? `/storage/${admin.user.profile_picture}` : '/img/default-avatar.png'" class="user-avatar-small">
                            <div class="user-info">
                                <span class="user-name">{{ admin.full_name }}</span>
                                <span class="user-email">{{ admin.user?.email }}</span>
                            </div>
                        </div>
                        <span v-if="admin.is_super_admin" class="status-badge status-super">
                            <i class="fas fa-crown"></i>
                        </span>
                        <span v-else class="status-badge status-admin">
                            <i class="fas fa-user-shield"></i>
                        </span>
                    </div>
                    <div class="admin-card-body">
                        <div class="admin-card-row">
                            <span class="label">{{ t('admin.dashboard.table.permissions') }}:</span>
                            <span v-if="admin.is_super_admin" class="permissions-badge permissions-all">
                                {{ t('admin.dashboard.allPermissions') }}
                            </span>
                            <span v-else class="permissions-badge">
                                {{ admin.permissions?.length || 0 }}
                            </span>
                        </div>
                        <div class="admin-card-row">
                            <span class="label">{{ t('admin.dashboard.table.lastLogin') }}:</span>
                            <span>{{ formatDate(admin.last_login_at) }}</span>
                        </div>
                    </div>
                    <div v-if="!admin.is_super_admin" class="admin-card-footer">
                        <button @click="openEditPermissionsModal(admin)" class="btn btn-sm btn-secondary">
                            <i class="fas fa-edit"></i>
                            {{ t('admin.common.edit') }}
                        </button>
                        <button @click="removeAdmin(admin.id)" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            {{ t('admin.common.delete') }}
                        </button>
                    </div>
                    <div v-else class="admin-card-footer protected">
                        <span class="protected-label">
                            <i class="fas fa-lock"></i> {{ t('admin.dashboard.protected') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Admin Modal -->
        <Transition name="modal">
            <div v-if="showAddAdminModal" class="modal-overlay" @click.self="closeModals">
                <div class="modal-container modal-lg">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-user-plus"></i>
                            {{ t('admin.dashboard.addNewAdmin') }}
                        </h3>
                        <button @click="closeModals" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Error in modal -->
                        <div v-if="errorMessage" class="alert alert-error alert-sm">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ errorMessage }}
                        </div>

                        <!-- User Selection -->
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.dashboard.selectUser') }}</label>
                            <select v-model="selectedUserId" class="form-select">
                                <option :value="null">-- {{ t('admin.dashboard.selectUser') }} --</option>
                                <option v-for="u in nonAdminUsers" :key="u.id" :value="u.id">
                                    {{ u.username }} ({{ u.email }})
                                </option>
                            </select>
                        </div>

                        <!-- Permissions Selection -->
                        <div class="permissions-section">
                            <div class="permissions-header">
                                <label class="form-label">{{ t('admin.dashboard.permissionsLabel') }}</label>
                                <div class="permissions-actions">
                                    <button type="button" @click="selectAllPermissions" class="btn btn-sm btn-outline">
                                        {{ t('admin.dashboard.selectAll') }}
                                    </button>
                                    <button type="button" @click="clearAllPermissions" class="btn btn-sm btn-outline">
                                        {{ t('admin.common.clear') }}
                                    </button>
                                </div>
                            </div>

                            <div class="permissions-grid">
                                <div v-for="(permissions, group) in availablePermissions" :key="group" class="permission-group">
                                    <div class="permission-group-header" @click="toggleGroup(permissions)">
                                        <span class="group-name">{{ group }}</span>
                                        <span class="group-count">{{ permissions.filter(p => selectedPermissions.includes(p.key)).length }}/{{ permissions.length }}</span>
                                    </div>
                                    <div class="permission-items">
                                        <label v-for="perm in permissions" :key="perm.key" class="permission-item">
                                            <input
                                                type="checkbox"
                                                :checked="selectedPermissions.includes(perm.key)"
                                                @change="togglePermission(perm.key)"
                                            >
                                            <span>{{ t(perm.labelKey) }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModals" class="btn btn-secondary">
                            {{ t('admin.common.cancel') }}
                        </button>
                        <button @click="saveNewAdmin" class="btn btn-primary" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <span>{{ t('admin.dashboard.add') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Edit Permissions Modal -->
        <Transition name="modal">
            <div v-if="showEditPermissionsModal" class="modal-overlay" @click.self="closeModals">
                <div class="modal-container modal-lg">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-edit"></i>
                            {{ t('admin.dashboard.editPermissionsFor', { name: selectedAdmin?.full_name }) }}
                        </h3>
                        <button @click="closeModals" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Permissions Selection -->
                        <div class="permissions-section">
                            <div class="permissions-header">
                                <label class="form-label">{{ t('admin.dashboard.permissionsLabel') }}</label>
                                <div class="permissions-actions">
                                    <button type="button" @click="selectAllPermissions" class="btn btn-sm btn-outline">
                                        {{ t('admin.dashboard.selectAll') }}
                                    </button>
                                    <button type="button" @click="clearAllPermissions" class="btn btn-sm btn-outline">
                                        {{ t('admin.common.clear') }}
                                    </button>
                                </div>
                            </div>

                            <div class="permissions-grid">
                                <div v-for="(permissions, group) in availablePermissions" :key="group" class="permission-group">
                                    <div class="permission-group-header" @click="toggleGroup(permissions)">
                                        <span class="group-name">{{ group }}</span>
                                        <span class="group-count">{{ permissions.filter(p => selectedPermissions.includes(p.key)).length }}/{{ permissions.length }}</span>
                                    </div>
                                    <div class="permission-items">
                                        <label v-for="perm in permissions" :key="perm.key" class="permission-item">
                                            <input
                                                type="checkbox"
                                                :checked="selectedPermissions.includes(perm.key)"
                                                @change="togglePermission(perm.key)"
                                            >
                                            <span>{{ t(perm.labelKey) }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModals" class="btn btn-secondary">
                            {{ t('admin.common.cancel') }}
                        </button>
                        <button @click="updatePermissions" class="btn btn-primary" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <span>{{ t('admin.common.save') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
/* Alerts */
.alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-sm {
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

/* Welcome Section */
.welcome-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    flex-wrap: wrap;
    gap: 1rem;
}

.welcome-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.super-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 1rem;
}

.super-badge i {
    color: #f59e0b;
}

.welcome-subtitle {
    color: #6b7280;
    margin: 0;
}

.welcome-date {
    color: #6b7280;
    font-size: 0.875rem;
    text-align: right;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.stat-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-users .stat-icon { background: #dbeafe; color: #2563eb; }
.stat-orders .stat-icon { background: #fef3c7; color: #d97706; }
.stat-products .stat-icon { background: #d1fae5; color: #059669; }
.stat-revenue .stat-icon { background: #ede9fe; color: #7c3aed; }

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-value {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.stat-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: #d1fae5;
    color: #059669;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
}

.stat-badge-warning {
    background: #fef3c7;
    color: #d97706;
}

/* Section Title */
.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #dc2626;
}

/* Quick Actions */
.quick-actions {
    margin-bottom: 2rem;
}

.quick-actions .section-title {
    margin-bottom: 1rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: #374151;
    font-weight: 500;
    transition: all 0.2s;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.action-card i {
    font-size: 1.25rem;
    color: #dc2626;
}

.action-card-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.action-card-warning i {
    color: #d97706;
}

.action-card-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
}

.action-card-info i {
    color: #2563eb;
}

/* Admin Management Section */
.admin-management-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

/* Admin Table */
.admin-table-container {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.admin-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.admin-table tr:hover {
    background: #f9fafb;
}

.super-admin-row {
    background: linear-gradient(135deg, rgba(254, 243, 199, 0.3) 0%, rgba(253, 230, 138, 0.3) 100%);
}

/* User Cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar-small {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 500;
    color: #111827;
}

.user-email {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-super {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.status-super i {
    color: #f59e0b;
}

.status-admin {
    background: #dbeafe;
    color: #1e40af;
}

/* Permissions Badge */
.permissions-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    color: #374151;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.permissions-all {
    background: #d1fae5;
    color: #059669;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon-edit {
    background: #dbeafe;
    color: #2563eb;
}

.btn-icon-edit:hover {
    background: #2563eb;
    color: white;
}

.btn-icon-delete {
    background: #fee2e2;
    color: #dc2626;
}

.btn-icon-delete:hover {
    background: #dc2626;
    color: white;
}

.protected-label {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #9ca3af;
    font-size: 0.75rem;
}

/* Mobile Cards */
.admin-cards {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.admin-card {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1rem;
}

.super-admin-card {
    background: linear-gradient(135deg, rgba(254, 243, 199, 0.5) 0%, rgba(253, 230, 138, 0.5) 100%);
}

.admin-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.admin-card-body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.admin-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-card-row .label {
    font-size: 0.875rem;
    color: #6b7280;
}

.admin-card-footer {
    display: flex;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.admin-card-footer.protected {
    justify-content: center;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    transform: translateY(-1px);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-danger {
    background: #fee2e2;
    color: #dc2626;
}

.btn-danger:hover {
    background: #dc2626;
    color: white;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.btn-outline {
    background: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
}

.btn-outline:hover {
    background: #f3f4f6;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 1rem;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-lg {
    max-width: 700px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-title i {
    color: #dc2626;
}

.modal-close {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.25rem;
}

.modal-close:hover {
    color: #111827;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* Form */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    background: white;
}

.form-select:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

/* Permissions Section */
.permissions-section {
    margin-top: 1rem;
}

.permissions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.permissions-actions {
    display: flex;
    gap: 0.5rem;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.permission-group {
    background: #f9fafb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.permission-group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #e5e7eb;
    cursor: pointer;
    font-weight: 600;
    color: #374151;
}

.permission-group-header:hover {
    background: #d1d5db;
}

.group-count {
    font-size: 0.75rem;
    color: #6b7280;
    background: white;
    padding: 0.125rem 0.5rem;
    border-radius: 1rem;
}

.permission-items {
    padding: 0.5rem;
}

.permission-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    cursor: pointer;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    color: #374151;
}

.permission-item:hover {
    background: white;
}

.permission-item input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    accent-color: #dc2626;
}

/* Animations */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-1rem);
}

.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
    transform: scale(0.9);
}

/* Responsive Visibility */
.desktop-only {
    display: block;
}

.mobile-only {
    display: none;
}

/* Responsive - 1200px */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Responsive - 1024px */
@media (max-width: 1024px) {
    .permissions-grid {
        grid-template-columns: 1fr;
    }
}

/* Responsive - 768px */
@media (max-width: 768px) {
    .welcome-section {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.25rem;
    }

    .welcome-date {
        text-align: left;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .desktop-only {
        display: none;
    }

    .mobile-only {
        display: flex;
    }

    .btn-text {
        display: none;
    }

    .modal-lg {
        max-width: 100%;
    }

    .modal-body {
        padding: 1rem;
    }
}

/* Responsive - 640px */
@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 3rem;
        height: 3rem;
        font-size: 1.25rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .actions-grid {
        grid-template-columns: 1fr;
    }

    .welcome-title {
        font-size: 1.25rem;
    }

    .admin-management-section {
        padding: 1rem;
    }
}

/* Responsive - 480px */
@media (max-width: 480px) {
    .permissions-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .permissions-actions {
        width: 100%;
    }

    .permissions-actions .btn {
        flex: 1;
        justify-content: center;
    }

    .admin-card-footer {
        flex-direction: column;
    }

    .admin-card-footer .btn {
        width: 100%;
        justify-content: center;
    }

    .modal-header,
    .modal-footer {
        padding: 1rem;
    }
}
</style>
