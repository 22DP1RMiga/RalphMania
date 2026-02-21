<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { locale } = useI18n();

const props = defineProps({
    courier:     { type: Object,  required: true },
    assignments: { type: Object,  default: () => ({ data: [], links: [] }) },
    filters:     { type: Object,  default: () => ({}) },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/courier/orders', { search: search.value, status: status.value }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    router.get('/courier/orders');
};

const statusConfig = {
    packed:     { label: { lv: 'Iepakots',   en: 'Packed'     }, color: '#8b5cf6', bg: '#ede9fe' },
    shipped:    { label: { lv: 'Nosūtīts',   en: 'Shipped'    }, color: '#f59e0b', bg: '#fef3c7' },
    in_transit: { label: { lv: 'Ceļā',       en: 'In Transit' }, color: '#3b82f6', bg: '#dbeafe' },
    delivered:  { label: { lv: 'Piegādāts',  en: 'Delivered'  }, color: '#10b981', bg: '#d1fae5' },
    cancelled:  { label: { lv: 'Atcelts',    en: 'Cancelled'  }, color: '#ef4444', bg: '#fee2e2' },
};
const getStatus = (s) => statusConfig[s] || { label: { lv: s, en: s }, color: '#6b7280', bg: '#f3f4f6' };
const getStatusLabel = (s) => getStatus(s).label[locale.value];

const formatDate = (d) => d ? new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
    year: 'numeric', month: 'short', day: 'numeric'
}).format(new Date(d)) : '—';

const formatPrice = (p) => parseFloat(p || 0).toFixed(2);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="locale === 'lv' ? 'Mani pasūtījumi' : 'My Orders'" />

        <div class="courier-orders">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <Link href="/courier/dashboard" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        {{ locale === 'lv' ? 'Atpakaļ uz paneli' : 'Back to Dashboard' }}
                    </Link>
                    <h1 class="page-title">
                        <i class="fas fa-box"></i>
                        {{ locale === 'lv' ? 'Mani pasūtījumi' : 'My Orders' }}
                    </h1>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input
                    v-model="search"
                    @keyup.enter="applyFilters"
                    type="text"
                    :placeholder="locale === 'lv' ? 'Meklēt pēc nr. vai klienta...' : 'Search by order # or customer...'"
                    class="search-input"
                />
                <select v-model="status" @change="applyFilters" class="filter-select">
                    <option value="">{{ locale === 'lv' ? 'Visi statusi' : 'All statuses' }}</option>
                    <option value="active">{{ locale === 'lv' ? 'Aktīvie' : 'Active' }}</option>
                    <option value="completed">{{ locale === 'lv' ? 'Pabeigti' : 'Completed' }}</option>
                </select>
                <button @click="applyFilters" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
                <button v-if="filters.search || filters.status" @click="clearFilters" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    {{ locale === 'lv' ? 'Notīrīt' : 'Clear' }}
                </button>
            </div>

            <!-- Table -->
            <div class="orders-table-wrap">
                <div v-if="assignments.data.length === 0" class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>{{ locale === 'lv' ? 'Nav atrasts neviens pasūtījums' : 'No orders found' }}</p>
                </div>

                <table v-else class="orders-table">
                    <thead>
                    <tr>
                        <th>{{ locale === 'lv' ? 'Pasūtījums' : 'Order' }}</th>
                        <th>{{ locale === 'lv' ? 'Klients' : 'Customer' }}</th>
                        <th>{{ locale === 'lv' ? 'Piegādes adrese' : 'Delivery Address' }}</th>
                        <th>{{ locale === 'lv' ? 'Statuss' : 'Status' }}</th>
                        <th>{{ locale === 'lv' ? 'Piešķirts' : 'Assigned' }}</th>
                        <th>{{ locale === 'lv' ? 'Pabeigts' : 'Completed' }}</th>
                        <th>{{ locale === 'lv' ? 'Summa' : 'Amount' }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="a in assignments.data" :key="a.id">
                        <td>
                            <span class="order-num">{{ a.order?.order_number }}</span>
                        </td>
                        <td>
                            <div>{{ a.order?.customer_name }}</div>
                            <div class="secondary">{{ a.order?.customer_phone }}</div>
                        </td>
                        <td>
                            <div>{{ a.order?.delivery_address }}</div>
                            <div class="secondary">{{ a.order?.delivery_city }}, {{ a.order?.delivery_country }}</div>
                        </td>
                        <td>
                                <span
                                    class="status-badge"
                                    :style="{ background: getStatus(a.order?.status).bg, color: getStatus(a.order?.status).color }"
                                >
                                    {{ getStatusLabel(a.order?.status) }}
                                </span>
                        </td>
                        <td class="secondary">{{ formatDate(a.assigned_at) }}</td>
                        <td>
                                <span v-if="a.completed_at" class="completed-check">
                                    <i class="fas fa-check-circle"></i>
                                    {{ formatDate(a.completed_at) }}
                                </span>
                            <span v-else class="secondary">—</span>
                        </td>
                        <td class="amount">{{ formatPrice(a.order?.total_amount) }}€</td>
                        <td>
                            <Link :href="`/courier/orders/${a.order?.id}`" class="btn btn-sm">
                                <i class="fas fa-eye"></i>
                            </Link>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination" v-if="assignments.links?.length > 3">
                    <component
                        v-for="link in assignments.links"
                        :key="link.label"
                        :is="link.url ? 'a' : 'span'"
                        :href="link.url"
                        v-html="link.label"
                        :class="['page-btn', { active: link.active, disabled: !link.url }]"
                        @click.prevent="link.url && router.visit(link.url)"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.courier-orders { background: #f9fafb; min-height: 100vh; padding: 32px 20px; max-width: 1300px; margin: 0 auto; }
.back-link { display: inline-flex; align-items: center; gap: 6px; color: #6b7280; text-decoration: none; font-size: 13px; margin-bottom: 8px; }
.back-link:hover { color: #dc2626; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title { font-size: 26px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
.page-title i { color: #dc2626; }

.filters-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.search-input { flex: 1; min-width: 200px; padding: 9px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
.search-input:focus { outline: none; border-color: #dc2626; }
.filter-select { padding: 9px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer; }

.orders-table-wrap { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; }
.orders-table { width: 100%; border-collapse: collapse; }
.orders-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.orders-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; vertical-align: middle; }
.orders-table tr:last-child td { border-bottom: none; }
.orders-table tr:hover td { background: #fafafa; }

.order-num { font-weight: 700; color: #111827; }
.secondary { font-size: 12px; color: #9ca3af; margin-top: 2px; }
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.completed-check { color: #10b981; font-size: 12px; display: flex; align-items: center; gap: 4px; }
.amount { font-weight: 600; color: #dc2626; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; }
.btn-primary { background: #dc2626; color: white; }
.btn-primary:hover { background: #b91c1c; }
.btn-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f3f4f6; }
.btn-sm { padding: 6px 10px; }
.btn-sm:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

.empty-state { text-align: center; padding: 60px; color: #9ca3af; }
.empty-state i { font-size: 40px; display: block; margin-bottom: 12px; }

.pagination { display: flex; gap: 4px; padding: 16px; justify-content: center; border-top: 1px solid #e5e7eb; }
.page-btn { padding: 6px 12px; border-radius: 6px; font-size: 13px; cursor: pointer; border: 1px solid #e5e7eb; text-decoration: none; color: #374151; }
.page-btn.active { background: #dc2626; color: white; border-color: #dc2626; }
.page-btn.disabled { opacity: 0.4; cursor: default; }

@media (max-width: 768px) {
    .orders-table { display: none; }
}
</style>
