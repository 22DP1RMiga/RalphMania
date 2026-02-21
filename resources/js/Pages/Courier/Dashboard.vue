<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    courier:         { type: Object, required: true },
    activeOrders:    { type: Array,  default: () => [] },
    recentCompleted: { type: Array,  default: () => [] },
    stats:           { type: Object, default: () => ({}) },
});

const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
};

// ─── STATUS HELPERS ───────────────────────────────────────────────────────────

const statusConfig = {
    packed:     { label: { lv: 'Iepakots',   en: 'Packed'      }, color: '#8b5cf6', bg: '#ede9fe' },
    shipped:    { label: { lv: 'Nosūtīts',   en: 'Shipped'     }, color: '#f59e0b', bg: '#fef3c7' },
    in_transit: { label: { lv: 'Ceļā',       en: 'In Transit'  }, color: '#3b82f6', bg: '#dbeafe' },
    delivered:  { label: { lv: 'Piegādāts',  en: 'Delivered'   }, color: '#10b981', bg: '#d1fae5' },
    cancelled:  { label: { lv: 'Atcelts',    en: 'Cancelled'   }, color: '#ef4444', bg: '#fee2e2' },
};

const getStatus = (status) => statusConfig[status] || { label: { lv: status, en: status }, color: '#6b7280', bg: '#f3f4f6' };
const getStatusLabel = (status) => getStatus(status).label[locale.value] ?? status;

const formatDate = (d) => d ? new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
}).format(new Date(d)) : '—';

const formatPrice = (p) => parseFloat(p || 0).toFixed(2);

// ─── NEXT STATUS ─────────────────────────────────────────────────────────────
const nextStatusMap = {
    packed:     { status: 'shipped',    label: { lv: 'Atzīmēt kā Nosūtītu',  en: 'Mark as Shipped'     }, icon: 'fas fa-shipping-fast' },
    shipped:    { status: 'in_transit', label: { lv: 'Atzīmēt — Ceļā',       en: 'Mark In Transit'     }, icon: 'fas fa-truck' },
    in_transit: { status: 'delivered',  label: { lv: 'Atzīmēt kā Piegādātu', en: 'Mark as Delivered'   }, icon: 'fas fa-check-double' },
};

const getNextStatus = (orderStatus) => nextStatusMap[orderStatus] || null;

// ─── QUICK STATUS UPDATE ──────────────────────────────────────────────────────
const updatingOrderId = ref(null);

const quickUpdateStatus = async (assignment) => {
    const next = getNextStatus(assignment.order.status);
    if (!next) return;

    updatingOrderId.value = assignment.order.id;

    try {
        await axios.put(`/courier/orders/${assignment.order.id}/status`, { status: next.status });

        showToast(
            locale.value === 'lv'
                ? `Statuss mainīts uz: ${getStatusLabel(next.status)}`
                : `Status updated to: ${getStatusLabel(next.status)}`,
            'success'
        );

        // Reload after short delay
        setTimeout(() => window.location.reload(), 1200);

    } catch (err) {
        showToast(err.response?.data?.message || 'Kļūda mainot statusu', 'error');
    } finally {
        updatingOrderId.value = null;
    }
};

const currentDate = new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
}).format(new Date());
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="locale === 'lv' ? 'Kurjera panelis' : 'Courier Dashboard'" />

        <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

        <div class="courier-dashboard">

            <!-- ─── HEADER ─── -->
            <div class="dashboard-header">
                <div class="header-info">
                    <h1 class="header-title">
                        <i class="fas fa-truck"></i>
                        {{ locale === 'lv' ? 'Kurjera panelis' : 'Courier Dashboard' }}
                    </h1>
                    <p class="header-subtitle">{{ currentDate }}</p>
                </div>
                <div class="courier-badge">
                    <i class="fas fa-id-badge"></i>
                    {{ courier.full_name }}
                    <span class="area-tag" v-if="courier.delivery_area">{{ courier.delivery_area }}</span>
                </div>
            </div>

            <!-- ─── STATS ─── -->
            <div class="stats-grid">
                <div class="stat-card stat-active">
                    <div class="stat-icon"><i class="fas fa-route"></i></div>
                    <div class="stat-body">
                        <span class="stat-value">{{ stats.totalActive }}</span>
                        <span class="stat-label">{{ locale === 'lv' ? 'Aktīvie piegādājumi' : 'Active Deliveries' }}</span>
                    </div>
                </div>
                <div class="stat-card stat-done">
                    <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                    <div class="stat-body">
                        <span class="stat-value">{{ stats.totalCompleted }}</span>
                        <span class="stat-label">{{ locale === 'lv' ? 'Pabeigti' : 'Completed' }}</span>
                    </div>
                </div>
                <div class="stat-card stat-total">
                    <div class="stat-icon"><i class="fas fa-box"></i></div>
                    <div class="stat-body">
                        <span class="stat-value">{{ stats.totalAssigned }}</span>
                        <span class="stat-label">{{ locale === 'lv' ? 'Kopā piešķirti' : 'Total Assigned' }}</span>
                    </div>
                </div>
                <div class="stat-card stat-rate">
                    <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="stat-body">
                        <span class="stat-value">{{ stats.completionRate }}%</span>
                        <span class="stat-label">{{ locale === 'lv' ? 'Izpildes līmenis' : 'Completion Rate' }}</span>
                    </div>
                </div>
            </div>

            <!-- ─── ACTIVE ORDERS ─── -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-shipping-fast"></i>
                        {{ locale === 'lv' ? 'Aktīvie piegādājumi' : 'Active Deliveries' }}
                        <span class="count-badge">{{ activeOrders.length }}</span>
                    </h2>
                    <Link href="/courier/orders" class="view-all">
                        {{ locale === 'lv' ? 'Visi pasūtījumi' : 'All Orders' }}
                        <i class="fas fa-arrow-right"></i>
                    </Link>
                </div>

                <div v-if="activeOrders.length === 0" class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>{{ locale === 'lv' ? 'Nav aktīvo piegādājumu' : 'No active deliveries' }}</p>
                </div>

                <div v-else class="orders-grid">
                    <div
                        v-for="assignment in activeOrders"
                        :key="assignment.id"
                        class="order-card"
                    >
                        <!-- Order header -->
                        <div class="order-card-header">
                            <div class="order-num">
                                <i class="fas fa-hashtag"></i>
                                {{ assignment.order.order_number }}
                            </div>
                            <span
                                class="status-badge"
                                :style="{ background: getStatus(assignment.order.status).bg, color: getStatus(assignment.order.status).color }"
                            >
                                {{ getStatusLabel(assignment.order.status) }}
                            </span>
                        </div>

                        <!-- Customer & delivery -->
                        <div class="order-card-body">
                            <div class="info-row">
                                <i class="fas fa-user"></i>
                                <strong>{{ assignment.order.customer_name }}</strong>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-phone"></i>
                                <a :href="`tel:${assignment.order.customer_phone}`" class="phone-link">
                                    {{ assignment.order.customer_phone }}
                                </a>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ assignment.order.delivery_address }}, {{ assignment.order.delivery_city }}</span>
                            </div>
                            <div class="info-row" v-if="assignment.order.notes">
                                <i class="fas fa-comment-alt"></i>
                                <span class="customer-note">{{ assignment.order.notes }}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-calendar-plus"></i>
                                <span>{{ locale === 'lv' ? 'Piešķirts:' : 'Assigned:' }} {{ formatDate(assignment.assigned_at) }}</span>
                            </div>
                        </div>

                        <!-- Items summary -->
                        <div class="items-summary">
                            <span v-for="item in assignment.order.items" :key="item.id" class="item-pill">
                                {{ item.product_name }}
                                <span v-if="item.size" class="item-size">{{ item.size }}</span>
                                ×{{ item.quantity }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="order-card-footer">
                            <Link :href="`/courier/orders/${assignment.order.id}`" class="btn btn-outline">
                                <i class="fas fa-eye"></i>
                                {{ locale === 'lv' ? 'Detaļas' : 'Details' }}
                            </Link>

                            <button
                                v-if="getNextStatus(assignment.order.status)"
                                @click="quickUpdateStatus(assignment)"
                                :disabled="updatingOrderId === assignment.order.id"
                                class="btn btn-primary"
                            >
                                <i v-if="updatingOrderId === assignment.order.id" class="fas fa-spinner fa-spin"></i>
                                <i v-else :class="getNextStatus(assignment.order.status).icon"></i>
                                {{ getNextStatus(assignment.order.status)?.label[locale] }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── RECENT COMPLETED ─── -->
            <div class="section" v-if="recentCompleted.length > 0">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i>
                        {{ locale === 'lv' ? 'Nesen pabeigti' : 'Recently Completed' }}
                    </h2>
                </div>

                <div class="completed-table">
                    <div class="table-header">
                        <span>{{ locale === 'lv' ? 'Pasūtījums' : 'Order' }}</span>
                        <span>{{ locale === 'lv' ? 'Klients' : 'Customer' }}</span>
                        <span>{{ locale === 'lv' ? 'Piegādāts' : 'Delivered' }}</span>
                        <span>{{ locale === 'lv' ? 'Summa' : 'Amount' }}</span>
                    </div>
                    <div v-for="a in recentCompleted" :key="a.id" class="table-row">
                        <span class="order-num-sm">{{ a.order?.order_number }}</span>
                        <span>{{ a.order?.customer_name }}</span>
                        <span>{{ formatDate(a.completed_at) }}</span>
                        <span class="amount">{{ formatPrice(a.order?.total_amount) }}€</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.courier-dashboard {
    background: #f9fafb;
    min-height: 100vh;
    padding: 32px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Header */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}
.header-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.header-title i { color: #dc2626; }
.header-subtitle { font-size: 14px; color: #6b7280; margin: 0; }
.courier-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 16px;
    font-weight: 600;
    color: #1f2937;
    font-size: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.courier-badge i { color: #dc2626; }
.area-tag {
    background: #fef2f2;
    color: #dc2626;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 500;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border: 1px solid #f3f4f6;
}
.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.stat-active .stat-icon { background: #fef3c7; color: #d97706; }
.stat-done   .stat-icon { background: #d1fae5; color: #059669; }
.stat-total  .stat-icon { background: #dbeafe; color: #2563eb; }
.stat-rate   .stat-icon { background: #fce7f3; color: #db2777; }
.stat-body { display: flex; flex-direction: column; }
.stat-value { font-size: 26px; font-weight: 700; color: #1f2937; line-height: 1; }
.stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }

/* Section */
.section { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 24px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.section-title {
    font-size: 17px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-title i { color: #dc2626; }
.count-badge {
    background: #fef2f2;
    color: #dc2626;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
}
.view-all {
    font-size: 13px;
    color: #dc2626;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}
.view-all:hover { text-decoration: underline; }

.empty-state {
    text-align: center;
    padding: 40px;
    color: #10b981;
    font-size: 15px;
}
.empty-state i { font-size: 36px; display: block; margin-bottom: 12px; }

/* Orders grid */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 16px;
}
.order-card {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.order-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

.order-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.order-num { font-weight: 700; color: #1f2937; font-size: 14px; display: flex; align-items: center; gap: 4px; }
.order-num i { color: #9ca3af; font-size: 11px; }
.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.order-card-body { padding: 14px 16px; display: flex; flex-direction: column; gap: 8px; }
.info-row { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #374151; }
.info-row i { width: 14px; color: #9ca3af; margin-top: 2px; flex-shrink: 0; }
.info-row strong { color: #111827; }
.phone-link { color: #dc2626; text-decoration: none; font-weight: 500; }
.phone-link:hover { text-decoration: underline; }
.customer-note { font-style: italic; color: #6b7280; }

.items-summary { padding: 10px 16px; display: flex; flex-wrap: wrap; gap: 6px; border-top: 1px solid #f3f4f6; }
.item-pill {
    background: #f3f4f6;
    border-radius: 4px;
    padding: 3px 8px;
    font-size: 11px;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 4px;
}
.item-size {
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    padding: 0 4px;
    font-size: 10px;
    font-weight: 700;
}

.order-card-footer {
    padding: 12px 16px;
    display: flex;
    gap: 10px;
    border-top: 1px solid #e5e7eb;
    background: #fafafa;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.15s;
}
.btn-primary { background: #dc2626; color: white; flex: 1; justify-content: center; }
.btn-primary:hover:not(:disabled) { background: #b91c1c; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f3f4f6; }

/* Completed table */
.completed-table { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
.table-header, .table-row {
    display: grid;
    grid-template-columns: 2fr 2fr 2fr 1fr;
    gap: 12px;
    padding: 10px 16px;
    font-size: 13px;
}
.table-header {
    background: #f9fafb;
    font-weight: 600;
    color: #6b7280;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e5e7eb;
}
.table-row { border-bottom: 1px solid #f3f4f6; color: #374151; }
.table-row:last-child { border-bottom: none; }
.order-num-sm { font-weight: 600; color: #1f2937; }
.amount { font-weight: 600; color: #dc2626; text-align: right; }

/* Responsive */
@media (max-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .orders-grid { grid-template-columns: 1fr; }
    .dashboard-header { flex-direction: column; }
    .table-header, .table-row { grid-template-columns: 1fr 1fr; }
    .table-header span:nth-child(3),
    .table-row span:nth-child(3) { display: none; }
}
</style>
