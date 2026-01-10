<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    roles: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Filters
const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const statusFilter = ref(props.filters.status || '');

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get('/admin/users', {
        search: search.value || undefined,
        role: roleFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    roleFilter.value = '';
    statusFilter.value = '';
    router.get('/admin/users');
};

// Toggle user active status
const toggleActive = (userId) => {
    router.put(`/admin/users/${userId}/toggle-active`, {}, {
        preserveScroll: true,
    });
};

// Format date
const formatDate = (date) => {
    if (!date) return 'Nav datu';
    return new Date(date).toLocaleDateString('lv-LV', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Get role badge color
const getRoleColor = (roleName) => {
    const colors = {
        'administrator': 'role-admin',
        'customer': 'role-customer',
        'courier': 'role-courier',
    };
    return colors[roleName] || 'role-default';
};

// Get role display name
const getRoleName = (roleName) => {
    const names = {
        'administrator': 'Administrators',
        'customer': 'Klients',
        'courier': 'Kurjers',
    };
    return names[roleName] || roleName;
};
</script>

<template>
    <Head title="Lietotāji - Admin" />

    <AdminLayout>
        <template #title>Lietotāji</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">Pārvaldiet sistēmas lietotājus</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-mini total">
                <i class="fas fa-users"></i>
                <span class="stat-count">{{ users.data?.length || 0 }}</span>
                <span class="stat-label">Kopā</span>
            </div>
            <div class="stat-mini active">
                <i class="fas fa-user-check"></i>
                <span class="stat-count">{{ users.data?.filter(u => u.is_active).length || 0 }}</span>
                <span class="stat-label">Aktīvi</span>
            </div>
            <div class="stat-mini inactive">
                <i class="fas fa-user-times"></i>
                <span class="stat-count">{{ users.data?.filter(u => !u.is_active).length || 0 }}</span>
                <span class="stat-label">Bloķēti</span>
            </div>
            <div class="stat-mini verified">
                <i class="fas fa-user-shield"></i>
                <span class="stat-count">{{ users.data?.filter(u => u.email_verified_at).length || 0 }}</span>
                <span class="stat-label">Verificēti</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Meklēt pēc vārda, e-pasta..."
                        class="search-input"
                    >
                </div>

                <select v-model="roleFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visas lomas</option>
                    <option value="administrator">Administratori</option>
                    <option value="customer">Klienti</option>
                    <option value="courier">Kurjeri</option>
                </select>

                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi statusi</option>
                    <option value="active">Aktīvi</option>
                    <option value="inactive">Bloķēti</option>
                    <option value="verified">Verificēti</option>
                    <option value="unverified">Neverificēti</option>
                </select>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Notīrīt
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-card">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Lietotājs</th>
                    <th>E-pasts</th>
                    <th>Loma</th>
                    <th>Reģistrēts</th>
                    <th>Pēdējā pieslēgšanās</th>
                    <th>Statuss</th>
                    <th>Darbības</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="user in users.data" :key="user.id">
                    <td>
                        <div class="user-cell">
                            <img
                                :src="user.profile_picture ? `/storage/${user.profile_picture}` : '/img/default-avatar.png'"
                                class="user-avatar"
                            >
                            <div class="user-info">
                                <span class="user-name">{{ user.username }}</span>
                                <span class="user-fullname" v-if="user.first_name || user.last_name">
                                        {{ user.first_name }} {{ user.last_name }}
                                    </span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="email-cell">
                            <span class="email">{{ user.email }}</span>
                            <span v-if="user.email_verified_at" class="verified-badge" title="E-pasts verificēts">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                        </div>
                    </td>
                    <td>
                            <span :class="['role-badge', getRoleColor(user.role?.name)]">
                                {{ getRoleName(user.role?.name) }}
                            </span>
                    </td>
                    <td>
                        <span class="date">{{ formatDate(user.created_at) }}</span>
                    </td>
                    <td>
                        <span class="date">{{ formatDate(user.last_login_at) }}</span>
                    </td>
                    <td>
                        <button
                            @click="toggleActive(user.id)"
                            :class="['status-toggle', user.is_active ? 'active' : 'inactive']"
                        >
                            <i :class="user.is_active ? 'fas fa-check' : 'fas fa-ban'"></i>
                            {{ user.is_active ? 'Aktīvs' : 'Bloķēts' }}
                        </button>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <Link :href="`/admin/users/${user.id}`" class="btn-icon btn-icon-view" title="Skatīt profilu">
                                <i class="fas fa-eye"></i>
                            </Link>
                            <Link :href="`/admin/users/${user.id}/edit`" class="btn-icon btn-icon-edit" title="Rediģēt">
                                <i class="fas fa-edit"></i>
                            </Link>
                            <a
                                :href="`mailto:${user.email}`"
                                class="btn-icon btn-icon-email"
                                title="Sūtīt e-pastu"
                            >
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <tr v-if="users.data?.length === 0">
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>Nav atrasts neviens lietotājs</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="users.links && users.links.length > 3" class="pagination">
                <Link
                    v-for="link in users.links"
                    :key="link.label"
                    :href="link.url"
                    :class="['page-link', { active: link.active, disabled: !link.url }]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.header-subtitle {
    color: #6b7280;
    margin: 0;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

.stat-mini {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-mini i {
    font-size: 1.5rem;
}

.stat-mini.total i { color: #6366f1; }
.stat-mini.active i { color: #059669; }
.stat-mini.inactive i { color: #dc2626; }
.stat-mini.verified i { color: #2563eb; }

.stat-count {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
}

/* Filters */
.filters-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.95rem;
}

.search-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.filter-select {
    padding: 0.625rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
}

/* Table */
.table-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.data-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.data-table tr:hover {
    background: #f9fafb;
}

/* User Cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #111827;
}

.user-fullname {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Email Cell */
.email-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.email {
    color: #374151;
}

.verified-badge {
    color: #10b981;
    font-size: 0.875rem;
}

/* Role Badge */
.role-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.role-admin {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.role-customer {
    background: #dbeafe;
    color: #1e40af;
}

.role-courier {
    background: #d1fae5;
    color: #065f46;
}

.role-default {
    background: #f3f4f6;
    color: #374151;
}

/* Date */
.date {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Status Toggle */
.status-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border: none;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.status-toggle.active {
    background: #d1fae5;
    color: #065f46;
}

.status-toggle.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.status-toggle:hover {
    transform: scale(1.05);
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
    text-decoration: none;
}

.btn-icon-view {
    background: #dbeafe;
    color: #2563eb;
}

.btn-icon-view:hover {
    background: #2563eb;
    color: white;
}

.btn-icon-edit {
    background: #fef3c7;
    color: #d97706;
}

.btn-icon-edit:hover {
    background: #d97706;
    color: white;
}

.btn-icon-email {
    background: #f3f4f6;
    color: #374151;
}

.btn-icon-email:hover {
    background: #374151;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem !important;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.page-link:hover:not(.disabled):not(.active) {
    background: #f3f4f6;
}

.page-link.active {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}

.page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
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

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Responsive */
@media (max-width: 1024px) {
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: 100%;
    }

    .data-table {
        display: block;
        overflow-x: auto;
    }
}
</style>
