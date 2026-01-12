<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

// Loading states
const isUpdatingStatus = ref(false);
const isDownloadingPdf = ref(false);

// Status options
const statusOptions = computed(() => [
    { value: 'pending', labelKey: 'admin.orders.status.pending', color: 'yellow', icon: 'fa-clock' },
    { value: 'confirmed', labelKey: 'admin.orders.status.confirmed', color: 'blue', icon: 'fa-check' },
    { value: 'processing', labelKey: 'admin.orders.status.processing', color: 'blue', icon: 'fa-cog' },
    { value: 'packed', labelKey: 'admin.orders.status.packed', color: 'purple', icon: 'fa-box' },
    { value: 'shipped', labelKey: 'admin.orders.status.shipped', color: 'indigo', icon: 'fa-shipping-fast' },
    { value: 'in_transit', labelKey: 'admin.orders.status.inTransit', color: 'indigo', icon: 'fa-truck' },
    { value: 'delivered', labelKey: 'admin.orders.status.delivered', color: 'green', icon: 'fa-check-circle' },
    { value: 'cancelled', labelKey: 'admin.orders.status.cancelled', color: 'red', icon: 'fa-times-circle' },
    { value: 'refunded', labelKey: 'admin.orders.status.refunded', color: 'gray', icon: 'fa-undo' },
]);

const getStatusInfo = (status) => {
    const found = statusOptions.value.find(s => s.value === status);
    return found ? {
        label: t(found.labelKey),
        color: found.color,
        icon: found.icon
    } : { label: status, color: 'gray', icon: 'fa-question' };
};

const currentStatus = computed(() => getStatusInfo(props.order.status));

// Update order status
const updateStatus = (newStatus) => {
    isUpdatingStatus.value = true;
    router.put(`/admin/orders/${props.order.id}/status`, {
        status: newStatus,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isUpdatingStatus.value = false;
        },
    });
};

// Download PDF invoice
const downloadPdf = () => {
    isDownloadingPdf.value = true;
    window.open(`/admin/orders/${props.order.id}/invoice/pdf`, '_blank');
    setTimeout(() => {
        isDownloadingPdf.value = false;
    }, 1000);
};

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        style: 'currency',
        currency: 'EUR',
    }).format(price || 0);
};

// Format date
const formatDate = (date, includeTime = true) => {
    if (!date) return '-';
    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    };
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', options);
};

// Calculate subtotal
const subtotal = computed(() => {
    if (!props.order.items || props.order.items.length === 0) return 0;
    return props.order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

// Calculate VAT (21%)
const vatAmount = computed(() => {
    return props.order.total_amount * 0.21;
});

// Payment method label
const paymentMethodLabel = computed(() => {
    const methods = {
        'card': t('admin.orders.payment.card'),
        'bank_transfer': t('admin.orders.payment.bankTransfer'),
        'cash_on_delivery': t('admin.orders.payment.cashOnDelivery'),
    };
    return methods[props.order.payment?.payment_method] || props.order.payment?.payment_method || '-';
});

// Payment status
const paymentStatusInfo = computed(() => {
    const statuses = {
        'pending': { label: t('admin.orders.payment.statusPending'), color: 'yellow' },
        'paid': { label: t('admin.orders.payment.statusPaid'), color: 'green' },
        'failed': { label: t('admin.orders.payment.statusFailed'), color: 'red' },
        'refunded': { label: t('admin.orders.payment.statusRefunded'), color: 'gray' },
    };
    return statuses[props.order.payment?.status] || { label: '-', color: 'gray' };
});

// Timeline events
const timeline = computed(() => {
    const events = [];

    // Order created
    events.push({
        date: props.order.created_at,
        title: t('admin.orders.timeline.created'),
        icon: 'fa-shopping-cart',
        color: 'blue',
    });

    // Status changes from history if available
    if (props.order.status_history) {
        props.order.status_history.forEach(change => {
            events.push({
                date: change.created_at,
                title: t('admin.orders.timeline.statusChanged', { status: getStatusInfo(change.status).label }),
                icon: getStatusInfo(change.status).icon,
                color: getStatusInfo(change.status).color,
            });
        });
    }

    // Payment if exists
    if (props.order.payment?.paid_at) {
        events.push({
            date: props.order.payment.paid_at,
            title: t('admin.orders.timeline.paymentReceived'),
            icon: 'fa-credit-card',
            color: 'green',
        });
    }

    return events.sort((a, b) => new Date(b.date) - new Date(a.date));
});
</script>

<template>
    <Head :title="`${t('admin.orders.show.title')} #${order.order_number}`" />

    <AdminLayout>
        <template #title>{{ t('admin.orders.show.title') }} #{{ order.order_number }}</template>

        <!-- Header Actions -->
        <div class="page-header">
            <div class="header-left">
                <Link href="/admin/orders" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i>
                    {{ t('admin.common.back') }}
                </Link>
                <div class="order-meta">
                    <span :class="['status-badge', `status-${currentStatus.color}`]">
                        <i :class="['fas', currentStatus.icon]"></i>
                        {{ currentStatus.label }}
                    </span>
                    <span class="order-date">{{ formatDate(order.created_at) }}</span>
                </div>
            </div>
            <div class="header-actions">
                <button @click="downloadPdf" class="btn btn-secondary" :disabled="isDownloadingPdf">
                    <i :class="isDownloadingPdf ? 'fas fa-spinner fa-spin' : 'fas fa-file-pdf'"></i>
                    <span class="btn-text">{{ t('admin.orders.downloadPdf') }}</span>
                </button>
                <a :href="`/admin/orders/${order.id}/invoice/print`" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-print"></i>
                    <span class="btn-text">{{ t('admin.orders.print') }}</span>
                </a>
            </div>
        </div>

        <div class="order-layout">
            <!-- Main Content -->
            <div class="order-main">
                <!-- Order Items -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-shopping-basket"></i>
                            {{ t('admin.orders.show.items') }}
                            <span class="items-count">({{ order.items?.length || 0 }})</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="items-list">
                            <div v-for="item in order.items" :key="item.id" class="order-item">
                                <div class="item-image">
                                    <img
                                        :src="item.product?.image ? `/storage/${item.product.image}` : '/img/placeholder-product.png'"
                                        :alt="item.product_name"
                                    >
                                </div>
                                <div class="item-details">
                                    <h4 class="item-name">{{ item.product_name }}</h4>
                                    <p class="item-sku" v-if="item.product?.sku">SKU: {{ item.product.sku }}</p>
                                    <div class="item-meta">
                                        <span class="item-price">{{ formatPrice(item.price) }}</span>
                                        <span class="item-qty">× {{ item.quantity }}</span>
                                    </div>
                                </div>
                                <div class="item-total">
                                    {{ formatPrice(item.price * item.quantity) }}
                                </div>
                            </div>

                            <div v-if="!order.items || order.items.length === 0" class="empty-items">
                                <i class="fas fa-box-open"></i>
                                <p>{{ t('admin.orders.show.noItems') }}</p>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="order-totals">
                            <div class="totals-row">
                                <span class="totals-label">{{ t('admin.orders.show.subtotal') }}</span>
                                <span class="totals-value">{{ formatPrice(subtotal) }}</span>
                            </div>
                            <div class="totals-row">
                                <span class="totals-label">{{ t('admin.orders.show.shipping') }}</span>
                                <span class="totals-value">{{ formatPrice(order.shipping_cost) }}</span>
                            </div>
                            <div class="totals-row">
                                <span class="totals-label">{{ t('admin.orders.show.vat') }} (21%)</span>
                                <span class="totals-value">{{ formatPrice(vatAmount) }}</span>
                            </div>
                            <div class="totals-row totals-final">
                                <span class="totals-label">{{ t('admin.orders.show.total') }}</span>
                                <span class="totals-value">{{ formatPrice(order.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user"></i>
                            {{ t('admin.orders.show.customer') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">{{ t('admin.orders.show.name') }}</span>
                                <span class="info-value">{{ order.customer_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ t('admin.orders.show.email') }}</span>
                                <span class="info-value">
                                    <a :href="`mailto:${order.customer_email}`">{{ order.customer_email }}</a>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ t('admin.orders.show.phone') }}</span>
                                <span class="info-value">
                                    <a :href="`tel:${order.customer_phone}`">{{ order.customer_phone || '-' }}</a>
                                </span>
                            </div>
                            <div class="info-item" v-if="order.user">
                                <span class="info-label">{{ t('admin.orders.show.account') }}</span>
                                <span class="info-value">
                                    <Link :href="`/admin/users/${order.user.id}`" class="user-link">
                                        {{ order.user.username }}
                                    </Link>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ t('admin.orders.show.deliveryAddress') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="address-block">
                            <p>{{ order.delivery_address }}</p>
                            <p>{{ order.delivery_city }}, {{ order.delivery_postal_code }}</p>
                            <p>{{ order.delivery_country }}</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card" v-if="order.notes">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sticky-note"></i>
                            {{ t('admin.orders.show.notes') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="notes-content">
                            {{ order.notes }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="order-sidebar">
                <!-- Status Update -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sync-alt"></i>
                            {{ t('admin.orders.show.updateStatus') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="status-buttons">
                            <button
                                v-for="status in statusOptions"
                                :key="status.value"
                                @click="updateStatus(status.value)"
                                :class="[
                                    'status-btn',
                                    `status-btn-${status.color}`,
                                    { 'active': order.status === status.value }
                                ]"
                                :disabled="isUpdatingStatus || order.status === status.value"
                            >
                                <i :class="['fas', status.icon]"></i>
                                {{ t(status.labelKey) }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-credit-card"></i>
                            {{ t('admin.orders.show.payment') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="payment-info">
                            <div class="payment-row">
                                <span class="payment-label">{{ t('admin.orders.show.paymentMethod') }}</span>
                                <span class="payment-value">{{ paymentMethodLabel }}</span>
                            </div>
                            <div class="payment-row" v-if="order.payment?.card_last4">
                                <span class="payment-label">{{ t('admin.orders.show.cardNumber') }}</span>
                                <span class="payment-value">•••• {{ order.payment.card_last4 }}</span>
                            </div>
                            <div class="payment-row">
                                <span class="payment-label">{{ t('admin.orders.show.paymentStatus') }}</span>
                                <span :class="['payment-status', `payment-status-${paymentStatusInfo.color}`]">
                                    {{ paymentStatusInfo.label }}
                                </span>
                            </div>
                            <div class="payment-row" v-if="order.payment?.paid_at">
                                <span class="payment-label">{{ t('admin.orders.show.paidAt') }}</span>
                                <span class="payment-value">{{ formatDate(order.payment.paid_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card" v-if="timeline.length > 0">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            {{ t('admin.orders.show.timeline') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div v-for="(event, index) in timeline" :key="index" class="timeline-item">
                                <div :class="['timeline-icon', `timeline-icon-${event.color}`]">
                                    <i :class="['fas', event.icon]"></i>
                                </div>
                                <div class="timeline-content">
                                    <p class="timeline-title">{{ event.title }}</p>
                                    <p class="timeline-date">{{ formatDate(event.date) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.order-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.order-date {
    color: #6b7280;
    font-size: 0.875rem;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-yellow { background: #fef3c7; color: #92400e; }
.status-blue { background: #dbeafe; color: #1e40af; }
.status-purple { background: #ede9fe; color: #6d28d9; }
.status-indigo { background: #e0e7ff; color: #4338ca; }
.status-green { background: #d1fae5; color: #065f46; }
.status-red { background: #fee2e2; color: #991b1b; }
.status-gray { background: #f3f4f6; color: #4b5563; }

/* Layout */
.order-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 1.5rem;
}

.order-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Cards */
.card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-title i {
    color: #dc2626;
}

.items-count {
    font-weight: 400;
    color: #6b7280;
}

.card-body {
    padding: 1.25rem;
}

/* Order Items */
.items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}

.item-image {
    width: 4rem;
    height: 4rem;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0.375rem;
}

.item-details {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem;
}

.item-sku {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0 0 0.5rem;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.item-price {
    font-weight: 500;
    color: #374151;
}

.item-qty {
    color: #6b7280;
}

.item-total {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    white-space: nowrap;
}

.empty-items {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.empty-items i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

/* Order Totals */
.order-totals {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px dashed #e5e7eb;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.totals-label {
    color: #6b7280;
}

.totals-value {
    font-weight: 600;
    color: #111827;
}

.totals-final {
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    border-top: 2px solid #dc2626;
}

.totals-final .totals-label {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
}

.totals-final .totals-value {
    font-size: 1.25rem;
    color: #dc2626;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
}

.info-value {
    font-size: 0.95rem;
    color: #111827;
}

.info-value a {
    color: #2563eb;
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

.user-link {
    color: #dc2626;
    font-weight: 500;
}

/* Address Block */
.address-block {
    line-height: 1.6;
    color: #374151;
}

.address-block p {
    margin: 0;
}

/* Notes */
.notes-content {
    padding: 1rem;
    background: #fef3c7;
    border-radius: 0.5rem;
    color: #92400e;
    line-height: 1.6;
}

/* Status Buttons */
.status-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.status-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border: 2px solid transparent;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    background: #f9fafb;
    color: #374151;
}

.status-btn:hover:not(:disabled) {
    background: #f3f4f6;
}

.status-btn.active {
    border-color: currentColor;
}

.status-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.status-btn-yellow { color: #92400e; }
.status-btn-yellow.active { background: #fef3c7; }
.status-btn-blue { color: #1e40af; }
.status-btn-blue.active { background: #dbeafe; }
.status-btn-purple { color: #6d28d9; }
.status-btn-purple.active { background: #ede9fe; }
.status-btn-indigo { color: #4338ca; }
.status-btn-indigo.active { background: #e0e7ff; }
.status-btn-green { color: #065f46; }
.status-btn-green.active { background: #d1fae5; }
.status-btn-red { color: #991b1b; }
.status-btn-red.active { background: #fee2e2; }
.status-btn-gray { color: #4b5563; }
.status-btn-gray.active { background: #f3f4f6; }

/* Payment Info */
.payment-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.payment-value {
    font-weight: 500;
    color: #111827;
}

.payment-status {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.payment-status-yellow { background: #fef3c7; color: #92400e; }
.payment-status-green { background: #d1fae5; color: #065f46; }
.payment-status-red { background: #fee2e2; color: #991b1b; }
.payment-status-gray { background: #f3f4f6; color: #4b5563; }

/* Timeline */
.timeline {
    position: relative;
}

.timeline-item {
    display: flex;
    gap: 1rem;
    padding-bottom: 1.25rem;
    position: relative;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 0.875rem;
    top: 2rem;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-icon {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
    z-index: 1;
}

.timeline-icon-blue { background: #dbeafe; color: #1e40af; }
.timeline-icon-green { background: #d1fae5; color: #065f46; }
.timeline-icon-yellow { background: #fef3c7; color: #92400e; }
.timeline-icon-red { background: #fee2e2; color: #991b1b; }
.timeline-icon-purple { background: #ede9fe; color: #6d28d9; }
.timeline-icon-indigo { background: #e0e7ff; color: #4338ca; }
.timeline-icon-gray { background: #f3f4f6; color: #4b5563; }

.timeline-content {
    flex: 1;
    min-width: 0;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
    margin: 0 0 0.125rem;
}

.timeline-date {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0;
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
    font-size: 0.875rem;
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
    background: #f9fafb;
}

.btn-secondary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 1024px) {
    .order-layout {
        grid-template-columns: 1fr;
    }

    .order-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
    }

    .header-actions .btn {
        flex: 1;
        justify-content: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .btn-text {
        display: none;
    }
}

@media (max-width: 480px) {
    .order-item {
        flex-direction: column;
    }

    .item-image {
        width: 100%;
        height: 8rem;
    }

    .item-total {
        align-self: flex-end;
    }

    .header-left {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
