<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const page = usePage();

const props = defineProps({
    logs: { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({}) },
    activityTypes: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

// Toast
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Filters
const search = ref(props.filters?.search || '');
const activityTypeFilter = ref(props.filters?.activity_type || '');
const userFilter = ref(props.filters?.user_id || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

// Modal
const showDetailsModal = ref(false);
const selectedLog = ref(null);

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

const applyFilters = () => {
    router.get('/admin/logs', {
        search: search.value || undefined,
        activity_type: activityTypeFilter.value || undefined,
        user_id: userFilter.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    activityTypeFilter.value = '';
    userFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get('/admin/logs');
};

const hasFilters = computed(() => search.value || activityTypeFilter.value || userFilter.value || dateFrom.value || dateTo.value);

// View log details
const viewDetails = (log) => { selectedLog.value = log; showDetailsModal.value = true; };
const closeModal = () => { showDetailsModal.value = false; selectedLog.value = null; };

// Format date
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// Get activity type icon and color
const getActivityStyle = (type) => {
    const styles = {
        'login': { icon: 'fa-sign-in-alt', color: 'green' },
        'logout': { icon: 'fa-sign-out-alt', color: 'gray' },
        'order_created': { icon: 'fa-shopping-cart', color: 'blue' },
        'order_updated': { icon: 'fa-edit', color: 'blue' },
        'order_cancelled': { icon: 'fa-times-circle', color: 'red' },
        'profile_updated': { icon: 'fa-user-edit', color: 'purple' },
        'password_changed': { icon: 'fa-key', color: 'orange' },
        'review_created': { icon: 'fa-star', color: 'yellow' },
        'comment_created': { icon: 'fa-comment', color: 'blue' },
        'contact_sent': { icon: 'fa-envelope', color: 'green' },
        'product_viewed': { icon: 'fa-eye', color: 'gray' },
        'cart_updated': { icon: 'fa-shopping-basket', color: 'purple' },
        'settings_updated': { icon: 'fa-cog', color: 'gray' },
        'test_email_sent': { icon: 'fa-envelope', color: 'orange' },
    };
    return styles[type] || { icon: 'fa-circle', color: 'gray' };
};

// Get user avatar
const getUserAvatar = (user) => {
    if (!user?.profile_picture) return '/img/default-avatar.png';
    if (user.profile_picture.startsWith('http')) return user.profile_picture;
    return `/storage/${user.profile_picture}`;
};

// Export logs
const exportLogs = () => {
    const params = new URLSearchParams({
        format: 'csv',
        search: search.value,
        activity_type: activityTypeFilter.value,
        user_id: userFilter.value,
        date_from: dateFrom.value,
        date_to: dateTo.value
    });
    window.location.href = `/admin/logs/export?${params.toString()}`;
};

// Translate activity type
const translateActivityType = (type) => {
    const key = `admin.logs.types.${type}`;
    const translated = t(key);
    return translated !== key ? translated : type;
};
</script>

<template>
    <Head :title="t('admin.logs.title')" />
    <AdminLayout>
        <template #title>{{ t('admin.logs.title') }}</template>

        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input v-model="search" type="text" :placeholder="t('admin.logs.searchPlaceholder')" class="search-input">
                    <button v-if="search" @click="search = ''" class="clear-search"><i class="fas fa-times"></i></button>
                </div>

                <select v-model="activityTypeFilter" @change="applyFilters" class="filter-select">
                    <option value="">{{ t('admin.logs.allTypes') }}</option>
                    <option v-for="type in activityTypes" :key="type" :value="type">
                        {{ translateActivityType(type) }}
                    </option>
                </select>

                <select v-model="userFilter" @change="applyFilters" class="filter-select">
                    <option value="">{{ t('admin.logs.allUsers') }}</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.username }}</option>
                </select>

                <input v-model="dateFrom" type="date" @change="applyFilters" class="filter-date" :title="t('admin.logs.dateFrom')">
                <input v-model="dateTo" type="date" @change="applyFilters" class="filter-date" :title="t('admin.logs.dateTo')">

                <button v-if="hasFilters" @click="resetFilters" class="btn btn-secondary btn-icon-only" :title="t('admin.common.clear')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="filters-actions">
                <button @click="exportLogs" class="btn btn-sm btn-outline">
                    <i class="fas fa-download"></i> CSV
                </button>
            </div>
        </div>

        <!-- Logs Table (Desktop) -->
        <div class="table-card">
            <table class="data-table">
                <thead>
                <tr>
                    <th>{{ t('admin.logs.table.time') }}</th>
                    <th>{{ t('admin.logs.table.user') }}</th>
                    <th>{{ t('admin.logs.table.type') }}</th>
                    <th>{{ t('admin.logs.table.description') }}</th>
                    <th>{{ t('admin.logs.table.ip') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="log in logs.data" :key="log.id">
                    <td><span class="log-time">{{ formatDate(log.created_at) }}</span></td>
                    <td>
                        <div class="user-cell">
                            <img :src="getUserAvatar(log.user)" class="user-avatar">
                            <span>{{ log.user?.username || t('admin.logs.system') }}</span>
                        </div>
                    </td>
                    <td>
                            <span :class="['activity-badge', `activity-${getActivityStyle(log.activity_type).color}`]">
                                <i :class="['fas', getActivityStyle(log.activity_type).icon]"></i>
                                {{ translateActivityType(log.activity_type) }}
                            </span>
                    </td>
                    <td><span class="log-description">{{ log.description || '-' }}</span></td>
                    <td><code class="ip-address">{{ log.ip_address || '-' }}</code></td>
                    <td>
                        <button @click="viewDetails(log)" class="btn-icon btn-view"><i class="fas fa-eye"></i></button>
                    </td>
                </tr>
                <tr v-if="logs.data.length === 0">
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>{{ t('admin.logs.noLogs') }}</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <div v-if="logs.links && logs.links.length > 3" class="pagination">
                <Link v-for="link in logs.links" :key="link.label" :href="link.url || '#'"
                      :class="['page-link', { active: link.active, disabled: !link.url }]" v-html="link.label" preserve-scroll />
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="mobile-cards">
            <div v-if="logs.data.length === 0" class="empty-state-mobile">
                <i class="fas fa-history"></i>
                <p>{{ t('admin.logs.noLogs') }}</p>
            </div>
            <div v-for="log in logs.data" :key="log.id" class="log-card" @click="viewDetails(log)">
                <div class="card-header">
                    <span :class="['activity-badge', `activity-${getActivityStyle(log.activity_type).color}`]">
                        <i :class="['fas', getActivityStyle(log.activity_type).icon]"></i>
                        {{ translateActivityType(log.activity_type) }}
                    </span>
                    <span class="log-time-sm">{{ formatDate(log.created_at) }}</span>
                </div>
                <div class="card-body">
                    <p class="log-description">{{ log.description || '-' }}</p>
                    <div class="card-meta">
                        <div class="user-cell-sm">
                            <img :src="getUserAvatar(log.user)" class="user-avatar-sm">
                            <span>{{ log.user?.username || t('admin.logs.system') }}</span>
                        </div>
                        <code class="ip-address-sm">{{ log.ip_address || '-' }}</code>
                    </div>
                </div>
            </div>

            <div v-if="logs.links && logs.links.length > 3" class="pagination mobile-pagination">
                <Link v-for="link in logs.links" :key="link.label" :href="link.url || '#'"
                      :class="['page-link', { active: link.active, disabled: !link.url }]" v-html="link.label" preserve-scroll />
            </div>
        </div>

        <!-- Details Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDetailsModal" class="modal-overlay" @click.self="closeModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3 class="modal-title"><i class="fas fa-info-circle"></i> {{ t('admin.logs.details') }}</h3>
                            <button @click="closeModal" class="modal-close"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="detail-row">
                                <label>{{ t('admin.logs.table.time') }}</label>
                                <span>{{ formatDate(selectedLog?.created_at) }}</span>
                            </div>
                            <div class="detail-row">
                                <label>{{ t('admin.logs.table.user') }}</label>
                                <div class="user-cell">
                                    <img :src="getUserAvatar(selectedLog?.user)" class="user-avatar">
                                    <span>{{ selectedLog?.user?.username || t('admin.logs.system') }}</span>
                                </div>
                            </div>
                            <div class="detail-row">
                                <label>{{ t('admin.logs.table.type') }}</label>
                                <span :class="['activity-badge', `activity-${getActivityStyle(selectedLog?.activity_type).color}`]">
                                    <i :class="['fas', getActivityStyle(selectedLog?.activity_type).icon]"></i>
                                    {{ translateActivityType(selectedLog?.activity_type) }}
                                </span>
                            </div>
                            <div class="detail-row">
                                <label>{{ t('admin.logs.table.description') }}</label>
                                <span>{{ selectedLog?.description || '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <label>{{ t('admin.logs.table.ip') }}</label>
                                <code class="ip-address">{{ selectedLog?.ip_address || '-' }}</code>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="closeModal" class="btn btn-secondary">{{ t('admin.common.close') }}</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
/* Filters */
.filters-card { background: white; border-radius: 0.75rem; padding: 1rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
.filters-row { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; flex: 1; }
.search-box { flex: 1; min-width: 200px; position: relative; }
.search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.search-input { width: 100%; padding: 0.625rem 2.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; }
.search-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
.clear-search { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; }
.filter-select, .filter-date { padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; background: white; }
.filters-actions { display: flex; gap: 0.5rem; }

/* Table */
.table-card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.data-table th { background: #f9fafb; font-weight: 600; color: #374151; font-size: 0.75rem; text-transform: uppercase; }
.data-table tr:hover { background: #f9fafb; }

.log-time { font-size: 0.8rem; color: #6b7280; }
.user-cell { display: flex; align-items: center; gap: 0.5rem; }
.user-avatar { width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; }
.user-avatar-sm { width: 1.5rem; height: 1.5rem; border-radius: 50%; object-fit: cover; }

.activity-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; border-radius: 1rem; font-size: 0.7rem; font-weight: 600; }
.activity-green { background: #d1fae5; color: #065f46; }
.activity-blue { background: #dbeafe; color: #1e40af; }
.activity-red { background: #fee2e2; color: #991b1b; }
.activity-purple { background: #ede9fe; color: #5b21b6; }
.activity-orange { background: #ffedd5; color: #9a3412; }
.activity-yellow { background: #fef3c7; color: #92400e; }
.activity-gray { background: #f3f4f6; color: #6b7280; }

.log-description { color: #374151; font-size: 0.875rem; }
.ip-address { background: #f3f4f6; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.7rem; color: #6b7280; }

/* Empty State */
.empty-state { text-align: center; padding: 3rem !important; color: #6b7280; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; display: block; }

/* Pagination */
.pagination { display: flex; justify-content: center; gap: 0.25rem; padding: 1rem; border-top: 1px solid #e5e7eb; flex-wrap: wrap; }
.page-link { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; color: #374151; text-decoration: none; font-size: 0.875rem; }
.page-link:hover:not(.disabled):not(.active) { background: #f3f4f6; }
.page-link.active { background: #dc2626; border-color: #dc2626; color: white; }
.page-link.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; font-size: 0.875rem; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
.btn-outline { background: white; border: 1px solid #d1d5db; color: #374151; }
.btn-outline:hover { background: #f3f4f6; }
.btn-icon { width: 2rem; height: 2rem; border: none; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.btn-icon-only { padding: 0.5rem; }
.btn-view { background: #dbeafe; color: #2563eb; }
.btn-view:hover { background: #2563eb; color: white; }

/* Mobile Cards */
.mobile-cards { display: none; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal-container { background: white; border-radius: 1rem; width: 100%; max-width: 500px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.modal-title i { color: #2563eb; }
.modal-close { background: none; border: none; color: #6b7280; font-size: 1.25rem; cursor: pointer; }
.modal-body { padding: 1.5rem; overflow-y: auto; }
.modal-footer { display: flex; justify-content: flex-end; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; }

.detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6; gap: 1rem; }
.detail-row:last-child { border-bottom: none; }
.detail-row label { font-weight: 600; color: #6b7280; font-size: 0.875rem; min-width: 100px; flex-shrink: 0; }

/* Transitions */
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-container, .modal-leave-to .modal-container { transform: scale(0.9); }

/* Responsive */
@media (max-width: 1024px) {
    .filters-row { flex-direction: column; align-items: stretch; }
    .search-box { min-width: 100%; }
    .filter-select, .filter-date { width: 100%; }
}

@media (max-width: 768px) {
    .filters-card { flex-direction: column; }
    .filters-actions { width: 100%; justify-content: flex-end; }
    .table-card { display: none; }
    .mobile-cards { display: flex; flex-direction: column; gap: 1rem; }

    .log-card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; cursor: pointer; }
    .log-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
    .log-time-sm { font-size: 0.7rem; color: #9ca3af; }
    .card-body { padding: 1rem; }
    .card-body .log-description { margin-bottom: 0.75rem; }
    .card-meta { display: flex; justify-content: space-between; align-items: center; }
    .user-cell-sm { display: flex; align-items: center; gap: 0.375rem; font-size: 0.8rem; color: #6b7280; }
    .ip-address-sm { font-size: 0.65rem; }

    .empty-state-mobile { text-align: center; padding: 3rem; background: white; border-radius: 0.75rem; }
    .empty-state-mobile i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
    .mobile-pagination { background: white; border-radius: 0.75rem; margin-top: 1rem; border-top: none; }

    .modal-footer { justify-content: center; }
    .modal-footer .btn { width: 100%; justify-content: center; }

    .detail-row { flex-direction: column; gap: 0.25rem; }
    .detail-row label { min-width: unset; }
}
</style>
