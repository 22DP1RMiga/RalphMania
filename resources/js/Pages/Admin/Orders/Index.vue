<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Filters
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Debounced search
let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get('/admin/orders', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    statusFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get('/admin/orders');
};

// Status options
const statusOptions = [
    { value: 'pending', label: 'Gaida', color: 'yellow' },
    { value: 'confirmed', label: 'Apstiprināts', color: 'blue' },
    { value: 'processing', label: 'Apstrādē', color: 'blue' },
    { value: 'packed', label: 'Iepakots', color: 'purple' },
    { value: 'shipped', label: 'Nosūtīts', color: 'indigo' },
    { value: 'in_transit', label: 'Ceļā', color: 'indigo' },
    { value: 'delivered', label: 'Piegādāts', color: 'green' },
    { value: 'cancelled', label: 'Atcelts', color: 'red' },
    { value: 'refunded', label: 'Atmaksāts', color: 'gray' },
];

const getStatusInfo = (status) => {
    return statusOptions.find(s => s.value === status) || { label: status, color: 'gray' };
};

// Update order status
const updateStatus = (orderId, newStatus) => {
    router.put(`/admin/orders/${orderId}/status`, {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('lv-LV', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('lv-LV', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Pasūtījumi - Admin" />

    <AdminLayout>
        <template #title>Pasūtījumi</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">Pārvaldiet klientu pasūtījumus</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-mini pending">
                <i class="fas fa-clock"></i>
                <span class="stat-count">{{ orders.data.filter(o => o.status === 'pending').length }}</span>
                <span class="stat-label">Gaida</span>
            </div>
            <div class="stat-mini processing">
                <i class="fas fa-cog"></i>
                <span class="stat-count">{{ orders.data.filter(o => ['confirmed', 'processing', 'packed'].includes(o.status)).length }}</span>
                <span class="stat-label">Apstrādē</span>
            </div>
            <div class="stat-mini shipped">
                <i class="fas fa-truck"></i>
                <span class="stat-count">{{ orders.data.filter(o => ['shipped', 'in_transit'].includes(o.status)).length }}</span>
                <span class="stat-label">Ceļā</span>
            </div>
            <div class="stat-mini delivered">
                <i class="fas fa-check-circle"></i>
                <span class="stat-count">{{ orders.data.filter(o => o.status === 'delivered').length }}</span>
                <span class="stat-label">Piegādāti</span>
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
                        placeholder="Meklēt pēc numura, klienta..."
                        class="search-input"
                    >
                </div>

                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi statusi</option>
                    <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                        {{ status.label }}
                    </option>
                </select>

                <div class="date-filters">
                    <input v-model="dateFrom" type="date" class="filter-input" @change="applyFilters">
                    <span class="date-separator">—</span>
                    <input v-model="dateTo" type="date" class="filter-input" @change="applyFilters">
                </div>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Notīrīt
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-card">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Pasūtījums</th>
                    <th>Klients</th>
                    <th>Datums</th>
                    <th>Summa</th>
                    <th>Statuss</th>
                    <th>Darbības</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="order in orders.data" :key="order.id">
                    <td>
                        <div class="order-number">
                            <span class="number">#{{ order.order_number }}</span>
                            <span class="items-count">{{ order.items?.length || 0 }} preces</span>
                        </div>
                    </td>
                    <td>
                        <div class="customer-info">
                            <span class="customer-name">{{ order.customer_name }}</span>
                            <span class="customer-email">{{ order.customer_email }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="date">{{ formatDate(order.created_at) }}</span>
                    </td>
                    <td>
                        <div class="amount-cell">
                            <span class="amount">{{ formatPrice(order.total_amount) }}</span>
                            <span v-if="order.shipping_cost > 0" class="shipping">
                                    + {{ formatPrice(order.shipping_cost) }} piegāde
                                </span>
                        </div>
                    </td>
                    <td>
                        <select
                            :value="order.status"
                            @change="updateStatus(order.id, $event.target.value)"
                            :class="['status-select', `status-${getStatusInfo(order.status).color}`]"
                        >
                            <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                {{ status.label }}
                            </option>
                        </select>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <Link :href="`/admin/orders/${order.id}`" class="btn-icon btn-icon-view" title="Skatīt detaļas">
                                <i class="fas fa-eye"></i>
                            </Link>
                            <a :href="`/orders/${order.id}/invoice`" target="_blank" class="btn-icon btn-icon-print" title="Rēķins">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <tr v-if="orders.data.length === 0">
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Nav atrasts neviens pasūtījums</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="orders.links && orders.links.length > 3" class="pagination">
                <Link
                    v-for="link in orders.links"
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
/* Page Header */
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
    opacity: 0.8;
}

.stat-mini.pending i { color: #d97706; }
.stat-mini.processing i { color: #2563eb; }
.stat-mini.shipped i { color: #7c3aed; }
.stat-mini.delivered i { color: #059669; }

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
    min-width: 200px;
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

.filter-select,
.filter-input {
    padding: 0.625rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
}

.date-filters {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.date-separator {
    color: #9ca3af;
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

/* Order Number */
.order-number {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.order-number .number {
    font-weight: 600;
    color: #111827;
    font-family: monospace;
}

.order-number .items-count {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Customer Info */
.customer-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.customer-name {
    font-weight: 500;
    color: #111827;
}

.customer-email {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Date */
.date {
    font-size: 0.875rem;
    color: #4b5563;
}

/* Amount */
.amount-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.amount {
    font-weight: 600;
    color: #111827;
}

.shipping {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Status Select */
.status-select {
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    border: none;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.status-yellow { background: #fef3c7; color: #92400e; }
.status-blue { background: #dbeafe; color: #1e40af; }
.status-purple { background: #ede9fe; color: #6d28d9; }
.status-indigo { background: #e0e7ff; color: #4338ca; }
.status-green { background: #d1fae5; color: #065f46; }
.status-red { background: #fee2e2; color: #991b1b; }
.status-gray { background: #f3f4f6; color: #4b5563; }

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

.btn-icon-print {
    background: #f3f4f6;
    color: #374151;
}

.btn-icon-print:hover {
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

    .date-filters {
        width: 100%;
    }

    .data-table {
        display: block;
        overflow-x: auto;
    }
}
</style>
