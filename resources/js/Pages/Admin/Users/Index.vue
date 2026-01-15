<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
    stats: Object,
});

// Filter state
const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');
const statusFilter = ref(props.filters?.status || '');

// Modal states
const showEmailModal = ref(false);
const showBanModal = ref(false);
const showDeleteModal = ref(false);
const selectedUser = ref(null);

// Form states
const emailForm = ref({ subject: '', message: '' });
const banForm = ref({ reason: '' });
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Debounce
let searchTimeout = null;
const debounceSearch = (fn, delay = 300) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fn, delay);
};

// Apply filters
const applyFilters = () => {
    router.get('/admin/users', {
        search: search.value || undefined,
        role: roleFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

watch([roleFilter, statusFilter], applyFilters);
watch(search, () => debounceSearch(applyFilters));

// Clear filters
const clearFilters = () => {
    search.value = '';
    roleFilter.value = '';
    statusFilter.value = '';
};

const hasFilters = computed(() => search.value || roleFilter.value || statusFilter.value);

// Format date
const formatDate = (date) => {
    if (!date) return t('admin.users.noDate');
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
};

// Get role badge class
const getRoleBadgeClass = (roleName) => {
    return { 'administrator': 'role-admin', 'user': 'role-user', 'courier': 'role-courier' }[roleName] || 'role-default';
};

// Get role display name
const getRoleName = (roleName) => t(`admin.users.roles.${roleName}`) || roleName;

// Get user avatar
const getUserAvatar = (user) => {
    if (!user.profile_picture) return '/img/default-avatar.png';
    if (user.profile_picture.startsWith('http')) return user.profile_picture;
    return `/storage/${user.profile_picture}`;
};

// Open email modal
const openEmailModal = (user) => {
    selectedUser.value = user;
    emailForm.value = { subject: '', message: '' };
    errorMessage.value = '';
    showEmailModal.value = true;
};

// Open ban modal
const openBanModal = (user) => {
    selectedUser.value = user;
    banForm.value = { reason: '' };
    errorMessage.value = '';
    showBanModal.value = true;
};

// Open delete modal
const openDeleteModal = (user) => {
    selectedUser.value = user;
    errorMessage.value = '';
    showDeleteModal.value = true;
};

// Close all modals
const closeModals = () => {
    showEmailModal.value = false;
    showBanModal.value = false;
    showDeleteModal.value = false;
    selectedUser.value = null;
    errorMessage.value = '';
};

// Send email (opens default email client)
const sendEmail = () => {
    if (!emailForm.value.subject.trim()) {
        errorMessage.value = t('admin.users.emailSubjectRequired');
        return;
    }
    const mailtoUrl = `mailto:${selectedUser.value.email}?subject=${encodeURIComponent(emailForm.value.subject)}&body=${encodeURIComponent(emailForm.value.message)}`;
    window.open(mailtoUrl, '_blank');
    successMessage.value = t('admin.users.emailOpened');
    closeModals();
    setTimeout(() => successMessage.value = '', 3000);
};

// Toggle user active status (ban/unban)
const toggleActive = () => {
    isLoading.value = true;
    router.put(`/admin/users/${selectedUser.value.id}/toggle-active`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = selectedUser.value.is_active
                ? t('admin.users.userBanned')
                : t('admin.users.userUnbanned');
            closeModals();
            setTimeout(() => successMessage.value = '', 3000);
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0] || t('admin.users.banError');
        },
        onFinish: () => isLoading.value = false,
    });
};

// Quick toggle (without modal)
const quickToggleActive = (user) => {
    if (confirm(user.is_active ? t('admin.users.confirmBan') : t('admin.users.confirmUnban'))) {
        router.put(`/admin/users/${user.id}/toggle-active`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = user.is_active
                    ? t('admin.users.userBanned')
                    : t('admin.users.userUnbanned');
                setTimeout(() => successMessage.value = '', 3000);
            },
        });
    }
};

// Delete user
const deleteUser = () => {
    isLoading.value = true;
    router.delete(`/admin/users/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = t('admin.users.userDeleted');
            closeModals();
            setTimeout(() => successMessage.value = '', 3000);
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0] || t('admin.users.deleteError');
        },
        onFinish: () => isLoading.value = false,
    });
};

// Check if can modify user
const canModifyUser = (user) => {
    // Cannot modify self
    if (user.id === currentUser.value?.id) return false;
    // Cannot modify super admin
    if (user.role?.name === 'administrator') {
        // Check if user is super admin (would need additional check from backend)
        return true; // For now allow, backend will prevent
    }
    return true;
};
</script>

<template>
    <Head :title="t('admin.users.index.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.users.index.title') }}</template>

        <!-- Success/Error Messages -->
        <Transition name="slide-down">
            <div v-if="successMessage" class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ successMessage }}
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon total"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.total || 0 }}</span>
                    <span class="stat-label">{{ t('admin.users.stats.total') }}</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active"><i class="fas fa-user-check"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.active || 0 }}</span>
                    <span class="stat-label">{{ t('admin.users.stats.active') }}</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon inactive"><i class="fas fa-user-times"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.inactive || 0 }}</span>
                    <span class="stat-label">{{ t('admin.users.stats.inactive') }}</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon verified"><i class="fas fa-user-shield"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.verified || 0 }}</span>
                    <span class="stat-label">{{ t('admin.users.stats.verified') }}</span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <div class="filter-group search-group">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input v-model="search" type="text" class="search-input" :placeholder="t('admin.users.searchPlaceholder')">
                        <button v-if="search" @click="search = ''" class="clear-search"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="filter-group">
                    <select v-model="roleFilter" class="filter-select">
                        <option value="">{{ t('admin.users.allRoles') }}</option>
                        <option value="administrator">{{ t('admin.users.roles.administrator') }}</option>
                        <option value="user">{{ t('admin.users.roles.user') }}</option>
                        <option value="courier">{{ t('admin.users.roles.courier') }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select v-model="statusFilter" class="filter-select">
                        <option value="">{{ t('admin.users.allStatuses') }}</option>
                        <option value="active">{{ t('admin.users.status.active') }}</option>
                        <option value="inactive">{{ t('admin.users.status.inactive') }}</option>
                        <option value="verified">{{ t('admin.users.status.verified') }}</option>
                        <option value="unverified">{{ t('admin.users.status.unverified') }}</option>
                    </select>
                </div>
                <button v-if="hasFilters" @click="clearFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ t('admin.common.clearFilters') }}
                </button>
            </div>
        </div>

        <!-- Users Table Container -->
        <div class="table-container">
            <!-- Empty State -->
            <div v-if="users.data.length === 0" class="empty-state">
                <i class="fas fa-users"></i>
                <h3>{{ t('admin.users.noUsers') }}</h3>
                <p>{{ t('admin.users.noUsersDesc') }}</p>
            </div>

            <!-- Desktop Table -->
            <table v-else class="data-table">
                <thead>
                <tr>
                    <th>{{ t('admin.users.table.user') }}</th>
                    <th>{{ t('admin.users.table.email') }}</th>
                    <th>{{ t('admin.users.table.role') }}</th>
                    <th>{{ t('admin.users.table.registered') }}</th>
                    <th>{{ t('admin.users.table.lastLogin') }}</th>
                    <th>{{ t('admin.users.table.status') }}</th>
                    <th>{{ t('admin.users.table.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="user in users.data" :key="user.id">
                    <td>
                        <div class="user-cell">
                            <img :src="getUserAvatar(user)" :alt="user.username" class="user-avatar">
                            <div class="user-info">
                                <span class="user-name">{{ user.username }}</span>
                                <span v-if="user.first_name || user.last_name" class="user-fullname">
                                        {{ user.first_name }} {{ user.last_name }}
                                    </span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="email-cell">
                            <span class="email">{{ user.email }}</span>
                            <span v-if="user.email_verified_at" class="verified-badge" :title="t('admin.users.emailVerified')">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                        </div>
                    </td>
                    <td>
                            <span :class="['role-badge', getRoleBadgeClass(user.role?.name)]">
                                {{ getRoleName(user.role?.name) }}
                            </span>
                    </td>
                    <td><span class="date">{{ formatDate(user.created_at) }}</span></td>
                    <td><span class="date">{{ formatDate(user.last_login_at) }}</span></td>
                    <td>
                        <button
                            @click="quickToggleActive(user)"
                            :class="['status-toggle', user.is_active ? 'active' : 'inactive']"
                            :disabled="!canModifyUser(user)"
                        >
                            <i :class="user.is_active ? 'fas fa-check' : 'fas fa-ban'"></i>
                            {{ user.is_active ? t('admin.users.status.active') : t('admin.users.status.inactive') }}
                        </button>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <Link :href="`/admin/users/${user.id}`" class="btn-icon btn-view" :title="t('admin.common.view')">
                                <i class="fas fa-eye"></i>
                            </Link>
                            <Link :href="`/admin/users/${user.id}/edit`" class="btn-icon btn-edit" :title="t('admin.common.edit')">
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button @click="openEmailModal(user)" class="btn-icon btn-email" :title="t('admin.users.sendEmail')">
                                <i class="fas fa-envelope"></i>
                            </button>
                            <button
                                v-if="canModifyUser(user)"
                                @click="openBanModal(user)"
                                :class="['btn-icon', user.is_active ? 'btn-ban' : 'btn-unban']"
                                :title="user.is_active ? t('admin.users.ban') : t('admin.users.unban')"
                            >
                                <i :class="user.is_active ? 'fas fa-ban' : 'fas fa-unlock'"></i>
                            </button>
                            <button
                                v-if="canModifyUser(user)"
                                @click="openDeleteModal(user)"
                                class="btn-icon btn-delete"
                                :title="t('admin.common.delete')"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Mobile Cards -->
            <div class="mobile-cards">
                <div v-for="user in users.data" :key="user.id" class="user-card">
                    <div class="card-header">
                        <div class="user-cell">
                            <img :src="getUserAvatar(user)" :alt="user.username" class="user-avatar">
                            <div class="user-info">
                                <span class="user-name">{{ user.username }}</span>
                                <span class="user-email">{{ user.email }}</span>
                            </div>
                        </div>
                        <span :class="['role-badge', getRoleBadgeClass(user.role?.name)]">
                            {{ getRoleName(user.role?.name) }}
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="card-row">
                            <span class="card-label">{{ t('admin.users.table.registered') }}</span>
                            <span class="card-value">{{ formatDate(user.created_at) }}</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">{{ t('admin.users.table.lastLogin') }}</span>
                            <span class="card-value">{{ formatDate(user.last_login_at) }}</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">{{ t('admin.users.table.status') }}</span>
                            <button
                                @click="quickToggleActive(user)"
                                :class="['status-toggle', user.is_active ? 'active' : 'inactive']"
                                :disabled="!canModifyUser(user)"
                            >
                                <i :class="user.is_active ? 'fas fa-check' : 'fas fa-ban'"></i>
                                {{ user.is_active ? t('admin.users.status.active') : t('admin.users.status.inactive') }}
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Action Buttons - ALL VISIBLE -->
                    <div class="card-actions">
                        <Link :href="`/admin/users/${user.id}`" class="btn btn-action btn-view">
                            <i class="fas fa-eye"></i>
                            <span>{{ t('admin.common.view') }}</span>
                        </Link>
                        <Link :href="`/admin/users/${user.id}/edit`" class="btn btn-action btn-edit">
                            <i class="fas fa-edit"></i>
                            <span>{{ t('admin.common.edit') }}</span>
                        </Link>
                        <button @click="openEmailModal(user)" class="btn btn-action btn-email">
                            <i class="fas fa-envelope"></i>
                            <span>{{ t('admin.users.email') }}</span>
                        </button>
                        <button
                            v-if="canModifyUser(user)"
                            @click="openBanModal(user)"
                            :class="['btn', 'btn-action', user.is_active ? 'btn-ban' : 'btn-unban']"
                        >
                            <i :class="user.is_active ? 'fas fa-ban' : 'fas fa-unlock'"></i>
                            <span>{{ user.is_active ? t('admin.users.ban') : t('admin.users.unban') }}</span>
                        </button>
                        <button
                            v-if="canModifyUser(user)"
                            @click="openDeleteModal(user)"
                            class="btn btn-action btn-delete"
                        >
                            <i class="fas fa-trash"></i>
                            <span>{{ t('admin.common.delete') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.links && users.links.length > 3" class="pagination-wrapper">
                <div class="pagination-info">
                    {{ t('admin.common.showing') }} {{ users.from }}-{{ users.to }} {{ t('admin.common.of') }} {{ users.total }}
                </div>
                <div class="pagination">
                    <template v-for="link in users.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" class="pagination-link" :class="{ active: link.active }" v-html="link.label" preserve-scroll />
                        <span v-else class="pagination-link disabled" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Email Modal -->
        <Transition name="modal">
            <div v-if="showEmailModal" class="modal-overlay" @click.self="closeModals">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-envelope"></i>
                            {{ t('admin.users.sendEmailTo', { name: selectedUser?.username }) }}
                        </h3>
                        <button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="errorMessage" class="alert alert-error alert-sm">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ errorMessage }}
                        </div>

                        <div class="user-preview">
                            <img :src="getUserAvatar(selectedUser)" class="preview-avatar">
                            <div class="preview-info">
                                <span class="preview-name">{{ selectedUser?.username }}</span>
                                <span class="preview-email">{{ selectedUser?.email }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ t('admin.users.emailSubject') }} *</label>
                            <input v-model="emailForm.subject" type="text" class="form-input" :placeholder="t('admin.users.emailSubjectPlaceholder')">
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ t('admin.users.emailMessage') }}</label>
                            <textarea v-model="emailForm.message" class="form-textarea" rows="5" :placeholder="t('admin.users.emailMessagePlaceholder')"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button>
                        <button @click="sendEmail" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            {{ t('admin.users.openEmailClient') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Ban/Unban Modal -->
        <Transition name="modal">
            <div v-if="showBanModal" class="modal-overlay" @click.self="closeModals">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i :class="selectedUser?.is_active ? 'fas fa-ban' : 'fas fa-unlock'"></i>
                            {{ selectedUser?.is_active ? t('admin.users.banUser') : t('admin.users.unbanUser') }}
                        </h3>
                        <button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="errorMessage" class="alert alert-error alert-sm">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ errorMessage }}
                        </div>

                        <div class="user-preview">
                            <img :src="getUserAvatar(selectedUser)" class="preview-avatar">
                            <div class="preview-info">
                                <span class="preview-name">{{ selectedUser?.username }}</span>
                                <span class="preview-email">{{ selectedUser?.email }}</span>
                            </div>
                            <span :class="['status-badge', selectedUser?.is_active ? 'status-active' : 'status-inactive']">
                                {{ selectedUser?.is_active ? t('admin.users.status.active') : t('admin.users.status.inactive') }}
                            </span>
                        </div>

                        <div v-if="selectedUser?.is_active" class="warning-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>{{ t('admin.users.banWarningTitle') }}</strong>
                                <p>{{ t('admin.users.banWarningText') }}</p>
                            </div>
                        </div>
                        <div v-else class="success-box">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>{{ t('admin.users.unbanInfoTitle') }}</strong>
                                <p>{{ t('admin.users.unbanInfoText') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button>
                        <button @click="toggleActive" :class="['btn', selectedUser?.is_active ? 'btn-danger' : 'btn-success']" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <i v-else :class="selectedUser?.is_active ? 'fas fa-ban' : 'fas fa-unlock'"></i>
                            {{ selectedUser?.is_active ? t('admin.users.confirmBanBtn') : t('admin.users.confirmUnbanBtn') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Delete Modal -->
        <Transition name="modal">
            <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeModals">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-trash"></i>
                            {{ t('admin.users.deleteUser') }}
                        </h3>
                        <button @click="closeModals" class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="errorMessage" class="alert alert-error alert-sm">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ errorMessage }}
                        </div>

                        <div class="user-preview">
                            <img :src="getUserAvatar(selectedUser)" class="preview-avatar">
                            <div class="preview-info">
                                <span class="preview-name">{{ selectedUser?.username }}</span>
                                <span class="preview-email">{{ selectedUser?.email }}</span>
                            </div>
                        </div>

                        <div class="danger-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>{{ t('admin.users.deleteWarningTitle') }}</strong>
                                <p>{{ t('admin.users.deleteWarningText') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeModals" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button>
                        <button @click="deleteUser" class="btn btn-danger" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-trash"></i>
                            {{ t('admin.users.confirmDeleteBtn') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
/* Alerts */
.alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-weight: 500; }
.alert-sm { padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.875rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

/* Stats Row */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: white; border-radius: 0.75rem; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.stat-icon { width: 3rem; height: 3rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.stat-icon.total { background: linear-gradient(135deg, #e0e7ff, #818cf8); color: #3730a3; }
.stat-icon.active { background: linear-gradient(135deg, #d1fae5, #34d399); color: #065f46; }
.stat-icon.inactive { background: linear-gradient(135deg, #fee2e2, #f87171); color: #991b1b; }
.stat-icon.verified { background: linear-gradient(135deg, #dbeafe, #60a5fa); color: #1e40af; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #111827; }
.stat-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }

/* Filters */
.filters-card { background: white; border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.filters-row { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; }
.filter-group { flex-shrink: 0; }
.search-group { flex: 1; min-width: 250px; }
.search-input-wrapper { position: relative; display: flex; align-items: center; }
.search-input-wrapper > i { position: absolute; left: 1rem; color: #9ca3af; pointer-events: none; }
.search-input { width: 100%; padding: 0.625rem 2.5rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; }
.search-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
.clear-search { position: absolute; right: 0.75rem; background: none; border: none; color: #9ca3af; cursor: pointer; }
.clear-search:hover { color: #dc2626; }
.filter-select { padding: 0.625rem 2rem 0.625rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; background: white; cursor: pointer; }
.filter-select:focus { outline: none; border-color: #dc2626; }

/* Table Container */
.table-container { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }

/* Empty State */
.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-state i { font-size: 4rem; color: #d1d5db; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.25rem; color: #374151; margin-bottom: 0.5rem; }
.empty-state p { color: #6b7280; }

/* Data Table */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.data-table th { background: #f9fafb; font-weight: 600; color: #374151; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
.data-table tr:hover { background: #f9fafb; }

/* User Cell */
.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar { width: 2.5rem; height: 2.5rem; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.user-info { display: flex; flex-direction: column; }
.user-name { font-weight: 600; color: #111827; }
.user-fullname, .user-email { font-size: 0.75rem; color: #6b7280; }

/* Email Cell */
.email-cell { display: flex; align-items: center; gap: 0.5rem; }
.email { color: #374151; font-size: 0.875rem; }
.verified-badge { color: #10b981; }

/* Role Badge */
.role-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
.role-admin { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
.role-user { background: #dbeafe; color: #1e40af; }
.role-courier { background: #d1fae5; color: #065f46; }
.role-default { background: #f3f4f6; color: #374151; }

/* Date */
.date { font-size: 0.875rem; color: #6b7280; }

/* Status Toggle */
.status-toggle { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border: none; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.status-toggle.active { background: #d1fae5; color: #065f46; }
.status-toggle.inactive { background: #fee2e2; color: #991b1b; }
.status-toggle:hover:not(:disabled) { transform: scale(1.05); }
.status-toggle:disabled { opacity: 0.6; cursor: not-allowed; }

/* Action Buttons */
.action-buttons { display: flex; gap: 0.375rem; }
.btn-icon { width: 2rem; height: 2rem; border: none; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; text-decoration: none; font-size: 0.8rem; }
.btn-icon:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-view { background: #dbeafe; color: #2563eb; }
.btn-view:hover { background: #2563eb; color: white; }
.btn-edit { background: #fef3c7; color: #d97706; }
.btn-edit:hover { background: #d97706; color: white; }
.btn-email { background: #e0e7ff; color: #4f46e5; }
.btn-email:hover { background: #4f46e5; color: white; }
.btn-ban { background: #fed7aa; color: #c2410c; }
.btn-ban:hover { background: #c2410c; color: white; }
.btn-unban { background: #d1fae5; color: #059669; }
.btn-unban:hover { background: #059669; color: white; }
.btn-delete { background: #fee2e2; color: #dc2626; }
.btn-delete:hover:not(:disabled) { background: #dc2626; color: white; }

/* Mobile Cards - Hidden by default */
.mobile-cards { display: none; }

/* Pagination */
.pagination-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; }
.pagination-info { font-size: 0.875rem; color: #6b7280; }
.pagination { display: flex; gap: 0.25rem; }
.pagination-link { padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; color: #374151; text-decoration: none; transition: all 0.2s; }
.pagination-link:hover:not(.disabled):not(.active) { background: #f3f4f6; }
.pagination-link.active { background: #dc2626; color: white; }
.pagination-link.disabled { color: #d1d5db; cursor: not-allowed; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; font-size: 0.875rem; }
.btn-primary { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
.btn-primary:hover:not(:disabled) { box-shadow: 0 4px 12px rgba(220,38,38,0.3); transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-danger { background: #dc2626; color: white; }
.btn-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-success { background: #059669; color: white; }
.btn-success:hover:not(:disabled) { background: #047857; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal-container { background: white; border-radius: 1rem; width: 100%; max-width: 500px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.modal-title i { color: #dc2626; }
.modal-close { background: none; border: none; color: #6b7280; font-size: 1.25rem; cursor: pointer; padding: 0.25rem; }
.modal-close:hover { color: #111827; }
.modal-body { padding: 1.5rem; overflow-y: auto; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; }

/* User Preview */
.user-preview { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f9fafb; border-radius: 0.75rem; margin-bottom: 1.5rem; }
.preview-avatar { width: 3.5rem; height: 3.5rem; border-radius: 50%; object-fit: cover; }
.preview-info { flex: 1; display: flex; flex-direction: column; }
.preview-name { font-weight: 600; color: #111827; }
.preview-email { font-size: 0.875rem; color: #6b7280; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }
.status-active { background: #d1fae5; color: #065f46; }
.status-inactive { background: #fee2e2; color: #991b1b; }

/* Form */
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
.form-input, .form-textarea { width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; }
.form-input:focus, .form-textarea:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
.form-textarea { resize: vertical; min-height: 100px; }

/* Warning/Danger/Success Boxes */
.warning-box, .danger-box, .success-box { display: flex; gap: 1rem; padding: 1rem; border-radius: 0.75rem; margin-top: 1rem; }
.warning-box { background: #fef3c7; color: #92400e; }
.danger-box { background: #fee2e2; color: #991b1b; }
.success-box { background: #d1fae5; color: #065f46; }
.warning-box i, .danger-box i, .success-box i { font-size: 1.5rem; flex-shrink: 0; margin-top: 0.25rem; }
.warning-box strong, .danger-box strong, .success-box strong { display: block; margin-bottom: 0.25rem; }
.warning-box p, .danger-box p, .success-box p { margin: 0; font-size: 0.875rem; opacity: 0.9; }

/* Transitions */
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-10px); }
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-container, .modal-leave-to .modal-container { transform: scale(0.9); }

/* ========================================
   RESPONSIVE STYLES
   ======================================== */

@media (max-width: 1024px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .data-table { display: block; overflow-x: auto; }
}

@media (max-width: 768px) {
    .stats-row { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .stat-card { padding: 1rem; }
    .stat-icon { width: 2.5rem; height: 2.5rem; font-size: 1rem; }
    .stat-value { font-size: 1.25rem; }
    .filters-card { padding: 1rem; }
    .filters-row { flex-direction: column; align-items: stretch; gap: 0.75rem; }
    .filter-group { width: 100%; }
    .search-group { min-width: unset; }
    .filter-select { width: 100%; }
    .btn-secondary { width: 100%; justify-content: center; }

    /* Hide table, show cards */
    .data-table { display: none; }
    .mobile-cards { display: flex; flex-direction: column; gap: 1rem; padding: 1rem; }

    .user-card { background: #f9fafb; border-radius: 0.75rem; padding: 1rem; border: 1px solid #e5e7eb; }
    .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
    .card-body { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
    .card-row { display: flex; justify-content: space-between; align-items: center; }
    .card-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }
    .card-value { font-size: 0.875rem; color: #111827; }

    /* Mobile Action Buttons - Grid layout */
    .card-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; }
    .btn-action { padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.375rem; text-decoration: none; border: none; cursor: pointer; }
    .btn-action.btn-view { background: #dbeafe; color: #1e40af; }
    .btn-action.btn-edit { background: #fef3c7; color: #92400e; }
    .btn-action.btn-email { background: #e0e7ff; color: #4338ca; }
    .btn-action.btn-ban { background: #fed7aa; color: #c2410c; }
    .btn-action.btn-unban { background: #d1fae5; color: #059669; }
    .btn-action.btn-delete { background: #fee2e2; color: #dc2626; grid-column: span 2; }

    .pagination-wrapper { flex-direction: column; gap: 1rem; align-items: center; padding: 1rem; }
    .pagination { flex-wrap: wrap; justify-content: center; }

    .modal-container { margin: 0.5rem; max-height: 95vh; }
}

@media (max-width: 480px) {
    .stats-row { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .stat-card { padding: 0.75rem; gap: 0.5rem; }
    .stat-icon { width: 2rem; height: 2rem; font-size: 0.875rem; }
    .stat-value { font-size: 1.1rem; }
    .stat-label { font-size: 0.6rem; }
    .mobile-cards { padding: 0.75rem; }
    .user-card { padding: 0.875rem; }
    .user-avatar { width: 2.25rem; height: 2.25rem; }
    .user-name { font-size: 0.9rem; }
    .role-badge { font-size: 0.6rem; padding: 0.2rem 0.5rem; }

    /* Stack action buttons vertically on very small screens */
    .card-actions { grid-template-columns: 1fr; }
    .btn-action.btn-delete { grid-column: span 1; }

    .pagination-link { padding: 0.35rem 0.5rem; font-size: 0.75rem; }

    .modal-body { padding: 1rem; }
    .modal-footer { flex-direction: column; gap: 0.5rem; }
    .modal-footer .btn { width: 100%; justify-content: center; }
}

@media (max-width: 360px) {
    .stats-row { grid-template-columns: 1fr; }
    .stat-card { flex-direction: row; }
    .preview-avatar { width: 3rem; height: 3rem; }
    .preview-name { font-size: 0.9rem; }
    .preview-email { font-size: 0.75rem; }
}
</style>
