<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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

// Available permissions
const availablePermissions = {
    'Produkti': [
        { key: 'products.view', label: 'Skatīt produktus' },
        { key: 'products.create', label: 'Izveidot produktus' },
        { key: 'products.edit', label: 'Rediģēt produktus' },
        { key: 'products.delete', label: 'Dzēst produktus' },
    ],
    'Kategorijas': [
        { key: 'categories.view', label: 'Skatīt kategorijas' },
        { key: 'categories.create', label: 'Izveidot kategorijas' },
        { key: 'categories.edit', label: 'Rediģēt kategorijas' },
        { key: 'categories.delete', label: 'Dzēst kategorijas' },
    ],
    'Pasūtījumi': [
        { key: 'orders.view', label: 'Skatīt pasūtījumus' },
        { key: 'orders.edit', label: 'Rediģēt pasūtījumus' },
        { key: 'orders.delete', label: 'Dzēst pasūtījumus' },
    ],
    'Lietotāji': [
        { key: 'users.view', label: 'Skatīt lietotājus' },
        { key: 'users.create', label: 'Izveidot lietotājus' },
        { key: 'users.edit', label: 'Rediģēt lietotājus' },
        { key: 'users.delete', label: 'Dzēst lietotājus' },
        { key: 'users.ban', label: 'Bloķēt lietotājus' },
    ],
    'Saturs': [
        { key: 'content.view', label: 'Skatīt saturu' },
        { key: 'content.create', label: 'Izveidot saturu' },
        { key: 'content.edit', label: 'Rediģēt saturu' },
        { key: 'content.delete', label: 'Dzēst saturu' },
        { key: 'content.publish', label: 'Publicēt saturu' },
    ],
    'Atsauksmes': [
        { key: 'reviews.view', label: 'Skatīt atsauksmes' },
        { key: 'reviews.moderate', label: 'Moderēt atsauksmes' },
        { key: 'reviews.delete', label: 'Dzēst atsauksmes' },
    ],
    'Komentāri': [
        { key: 'comments.view', label: 'Skatīt komentārus' },
        { key: 'comments.moderate', label: 'Moderēt komentārus' },
        { key: 'comments.delete', label: 'Dzēst komentārus' },
    ],
    'Kontakti': [
        { key: 'contacts.view', label: 'Skatīt kontaktus' },
        { key: 'contacts.reply', label: 'Atbildēt uz ziņojumiem' },
        { key: 'contacts.delete', label: 'Dzēst ziņojumus' },
    ],
    'Iestatījumi': [
        { key: 'settings.view', label: 'Skatīt iestatījumus' },
        { key: 'settings.edit', label: 'Rediģēt iestatījumus' },
    ],
    'Žurnāls': [
        { key: 'logs.view', label: 'Skatīt aktivitāšu žurnālu' },
    ],
};

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
        // Remove all from group
        selectedPermissions.value = selectedPermissions.value.filter(p => !groupKeys.includes(p));
    } else {
        // Add all from group
        groupKeys.forEach(key => {
            if (!selectedPermissions.value.includes(key)) {
                selectedPermissions.value.push(key);
            }
        });
    }
};

// Select all permissions
const selectAllPermissions = () => {
    const allKeys = Object.values(availablePermissions).flat().map(p => p.key);
    selectedPermissions.value = [...allKeys];
};

// Clear all permissions
const clearAllPermissions = () => {
    selectedPermissions.value = [];
};

// Save new administrator
const saveNewAdmin = () => {
    if (!selectedUserId.value) {
        errorMessage.value = 'Lūdzu izvēlieties lietotāju';
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
            successMessage.value = 'Administrators veiksmīgi pievienots!';
            closeModals();
            setTimeout(() => successMessage.value = '', 3000);
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0] || 'Kļūda pievienojot administratoru';
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
            successMessage.value = 'Atļaujas veiksmīgi atjauninātas!';
            closeModals();
            setTimeout(() => successMessage.value = '', 3000);
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0] || 'Kļūda atjauninot atļaujas';
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

// Remove administrator
const removeAdmin = (adminId) => {
    if (!confirm('Vai tiešām vēlaties noņemt šo administratoru?')) return;

    router.delete(`/admin/administrators/${adminId}`, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Administrators veiksmīgi noņemts!';
            setTimeout(() => successMessage.value = '', 3000);
        },
    });
};

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('lv-LV', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

// Get non-admin users for selection
const nonAdminUsers = computed(() => {
    const adminUserIds = props.administrators.map(a => a.user_id);
    return props.allUsers.filter(u => !adminUserIds.includes(u.id));
});
</script>

<template>
    <Head title="Admin Panelis" />

    <AdminLayout>
        <template #title>Pārskats</template>

        <!-- Success/Error Messages -->
        <Transition name="slide-down">
            <div v-if="successMessage" class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ successMessage }}
            </div>
        </Transition>

        <Transition name="slide-down">
            <div v-if="errorMessage" class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ errorMessage }}
            </div>
        </Transition>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h2 class="welcome-title">
                    Sveiks, {{ user.username }}!
                    <span v-if="isSuperAdmin" class="super-badge">
                        <i class="fas fa-crown"></i> Super Admin
                    </span>
                </h2>
                <p class="welcome-subtitle">Šeit ir jūsu administrācijas paneļa pārskats.</p>
            </div>
            <div class="welcome-date">
                {{ new Date().toLocaleDateString('lv-LV', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
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
                    <span class="stat-label">Lietotāji</span>
                </div>
                <div class="stat-badge" v-if="stats.newUsersToday > 0">
                    +{{ stats.newUsersToday }} šodien
                </div>
            </div>

            <div class="stat-card stat-orders">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalOrders }}</span>
                    <span class="stat-label">Pasūtījumi</span>
                </div>
                <div class="stat-badge stat-badge-warning" v-if="stats.pendingOrders > 0">
                    {{ stats.pendingOrders }} gaida
                </div>
            </div>

            <div class="stat-card stat-products">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ stats.totalProducts }}</span>
                    <span class="stat-label">Produkti</span>
                </div>
            </div>

            <div class="stat-card stat-revenue">
                <div class="stat-icon">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ formatCurrency(stats.totalRevenue) }}</span>
                    <span class="stat-label">Ieņēmumi</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 class="section-title">Ātrās darbības</h3>
            <div class="actions-grid">
                <Link href="/admin/products/create" class="action-card">
                    <i class="fas fa-plus"></i>
                    <span>Jauns produkts</span>
                </Link>
                <Link href="/admin/content/create" class="action-card">
                    <i class="fas fa-newspaper"></i>
                    <span>Jauns saturs</span>
                </Link>
                <Link href="/admin/orders" class="action-card action-card-warning" v-if="stats.pendingOrders > 0">
                    <i class="fas fa-clock"></i>
                    <span>{{ stats.pendingOrders }} gaida apstrādi</span>
                </Link>
                <Link href="/admin/contacts" class="action-card action-card-info" v-if="stats.unreadContacts > 0">
                    <i class="fas fa-envelope"></i>
                    <span>{{ stats.unreadContacts }} neizlasīti</span>
                </Link>
            </div>
        </div>

        <!-- Super Admin Section: Administrator Management -->
        <div v-if="isSuperAdmin" class="admin-management-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-user-shield"></i>
                    Administratoru pārvaldība
                </h3>
                <button @click="openAddAdminModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Pievienot administratoru
                </button>
            </div>

            <!-- Administrators Table -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Lietotājs</th>
                        <th>E-pasts</th>
                        <th>Statuss</th>
                        <th>Atļaujas</th>
                        <th>Pēdējā pieslēgšanās</th>
                        <th>Darbības</th>
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
                                    Visas atļaujas
                                </span>
                            <span v-else class="permissions-badge">
                                    {{ admin.permissions?.length || 0 }} atļaujas
                                </span>
                        </td>
                        <td>
                            {{ admin.last_login_at ? new Date(admin.last_login_at).toLocaleDateString('lv-LV') : 'Nav datu' }}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button
                                    v-if="!admin.is_super_admin"
                                    @click="openEditPermissionsModal(admin)"
                                    class="btn-icon btn-icon-edit"
                                    title="Rediģēt atļaujas"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    v-if="!admin.is_super_admin"
                                    @click="removeAdmin(admin.id)"
                                    class="btn-icon btn-icon-delete"
                                    title="Noņemt administratoru"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                                <span v-if="admin.is_super_admin" class="protected-label">
                                        <i class="fas fa-lock"></i> Aizsargāts
                                    </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Admin Modal -->
        <Transition name="modal">
            <div v-if="showAddAdminModal" class="modal-overlay" @click.self="closeModals">
                <div class="modal-container modal-lg">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-user-plus"></i>
                            Pievienot jaunu administratoru
                        </h3>
                        <button @click="closeModals" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- User Selection -->
                        <div class="form-group">
                            <label class="form-label">Izvēlieties lietotāju</label>
                            <select v-model="selectedUserId" class="form-select">
                                <option :value="null">-- Izvēlieties lietotāju --</option>
                                <option v-for="u in nonAdminUsers" :key="u.id" :value="u.id">
                                    {{ u.username }} ({{ u.email }})
                                </option>
                            </select>
                        </div>

                        <!-- Permissions Selection -->
                        <div class="permissions-section">
                            <div class="permissions-header">
                                <label class="form-label">Atļaujas</label>
                                <div class="permissions-actions">
                                    <button type="button" @click="selectAllPermissions" class="btn btn-sm btn-outline">
                                        Atzīmēt visas
                                    </button>
                                    <button type="button" @click="clearAllPermissions" class="btn btn-sm btn-outline">
                                        Notīrīt
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
                                            <span>{{ perm.label }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModals" class="btn btn-secondary">
                            Atcelt
                        </button>
                        <button @click="saveNewAdmin" class="btn btn-primary" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <span>Pievienot</span>
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
                            Rediģēt atļaujas: {{ selectedAdmin?.full_name }}
                        </h3>
                        <button @click="closeModals" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Permissions Selection -->
                        <div class="permissions-section">
                            <div class="permissions-header">
                                <label class="form-label">Atļaujas</label>
                                <div class="permissions-actions">
                                    <button type="button" @click="selectAllPermissions" class="btn btn-sm btn-outline">
                                        Atzīmēt visas
                                    </button>
                                    <button type="button" @click="clearAllPermissions" class="btn btn-sm btn-outline">
                                        Notīrīt
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
                                            <span>{{ perm.label }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModals" class="btn btn-secondary">
                            Atcelt
                        </button>
                        <button @click="updatePermissions" class="btn btn-primary" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <span>Saglabāt</span>
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
}

.welcome-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
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
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
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
}

.stat-users .stat-icon {
    background: #dbeafe;
    color: #2563eb;
}

.stat-orders .stat-icon {
    background: #fef3c7;
    color: #d97706;
}

.stat-products .stat-icon {
    background: #d1fae5;
    color: #059669;
}

.stat-revenue .stat-icon {
    background: #ede9fe;
    color: #7c3aed;
}

.stat-content {
    flex: 1;
}

.stat-value {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
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
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

/* Quick Actions */
.quick-actions {
    margin-bottom: 2rem;
}

.actions-grid {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: white;
    border-radius: 0.5rem;
    color: #374151;
    font-weight: 500;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
}

.action-card:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.action-card-warning {
    background: #fef3c7;
    color: #92400e;
}

.action-card-warning:hover {
    background: #f59e0b;
    color: white;
}

.action-card-info {
    background: #dbeafe;
    color: #1e40af;
}

.action-card-info:hover {
    background: #3b82f6;
    color: white;
}

/* Admin Management Section */
.admin-management-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.admin-management-section .section-title {
    color: #92400e;
}

.admin-management-section .section-title i {
    color: #f59e0b;
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
}

.admin-table tr:hover {
    background: #f9fafb;
}

.super-admin-row {
    background: linear-gradient(90deg, #fefce8 0%, #fff 50%);
}

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
}

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
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover {
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

@media (max-width: 640px) {
    .permissions-grid {
        grid-template-columns: 1fr;
    }
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

/* Responsive */
@media (max-width: 768px) {
    .welcome-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>
