<script setup>
import { ref } from 'vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    order: Object,
});

// Toast state
const toast = ref({
    show: false,
    message: '',
    type: 'success',
});

// Cancel modal state
const showCancelModal = ref(false);

// Format price
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

// Format date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(date);
};

// Format date and time
const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

// Get status label
const getStatusLabel = (status) => {
    const labels = {
        lv: {
            pending: 'Gaida apstiprinājumu',
            confirmed: 'Apstiprināts',
            processing: 'Apstrādē',
            packed: 'Iepakots',
            shipped: 'Nosūtīts',
            in_transit: 'Ceļā',
            delivered: 'Piegādāts',
            cancelled: 'Atcelts',
            refunded: 'Atmaksāts',
        },
        en: {
            pending: 'Pending',
            confirmed: 'Confirmed',
            processing: 'Processing',
            packed: 'Packed',
            shipped: 'Shipped',
            in_transit: 'In Transit',
            delivered: 'Delivered',
            cancelled: 'Cancelled',
            refunded: 'Refunded',
        }
    };

    return labels[locale.value][status] || status;
};

// Get status icon
const getStatusIcon = (status) => {
    const icons = {
        pending: 'fas fa-clock',
        confirmed: 'fas fa-check-circle',
        processing: 'fas fa-cog fa-spin',
        packed: 'fas fa-box',
        shipped: 'fas fa-shipping-fast',
        in_transit: 'fas fa-truck',
        delivered: 'fas fa-check-double',
        cancelled: 'fas fa-times-circle',
        refunded: 'fas fa-undo',
    };

    return icons[status] || 'fas fa-circle';
};

// Get payment method label
const getPaymentMethodLabel = (method) => {
    const labels = {
        lv: {
            card: 'Kredītkarte',
            bank_transfer: 'Bankas pārskaitījums',
            cash_on_delivery: 'Skaidrā nauda',
        },
        en: {
            card: 'Credit Card',
            bank_transfer: 'Bank Transfer',
            cash_on_delivery: 'Cash on Delivery',
        }
    };

    return labels[locale.value][method] || method;
};

// Get payment status label
const getPaymentStatusLabel = (status) => {
    const labels = {
        lv: {
            pending: 'Gaida',
            completed: 'Pabeigts',
            failed: 'Neizdevās',
            refunded: 'Atmaksāts',
        },
        en: {
            pending: 'Pending',
            completed: 'Completed',
            failed: 'Failed',
            refunded: 'Refunded',
        }
    };

    return labels[locale.value][status] || status;
};

// Get order timeline
const getOrderTimeline = () => {
    const statuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'in_transit', 'delivered'];
    const currentIndex = statuses.indexOf(props.order.status);

    return [
        {
            label: locale.value === 'lv' ? 'Pasūtījums izveidots' : 'Order placed',
            icon: 'fas fa-check',
            date: props.order.created_at,
            completed: true,
            active: currentIndex === 0,
        },
        {
            label: locale.value === 'lv' ? 'Apstiprināts' : 'Confirmed',
            icon: 'fas fa-check-circle',
            date: props.order.status === 'confirmed' ? props.order.updated_at : null,
            completed: currentIndex >= 1,
            active: currentIndex === 1,
        },
        {
            label: locale.value === 'lv' ? 'Apstrādē' : 'Processing',
            icon: 'fas fa-cog',
            date: ['processing', 'packed', 'shipped', 'in_transit', 'delivered'].includes(props.order.status) ? props.order.updated_at : null,
            completed: currentIndex >= 2,
            active: currentIndex === 2,
        },
        {
            label: locale.value === 'lv' ? 'Nosūtīts' : 'Shipped',
            icon: 'fas fa-shipping-fast',
            date: props.order.shipped_at,
            completed: currentIndex >= 4,
            active: currentIndex === 4,
        },
        {
            label: locale.value === 'lv' ? 'Piegādāts' : 'Delivered',
            icon: 'fas fa-check-double',
            date: props.order.delivered_at,
            completed: currentIndex >= 6,
            active: currentIndex === 6,
        },
    ];
};

// Check if order can be cancelled
const canCancelOrder = () => {
    return ['pending', 'confirmed', 'processing', 'packed'].includes(props.order.status);
};

// Show toast
const showToast = (message, type = 'success') => {
    toast.value = {
        show: true,
        message,
        type,
    };
};

// Cancel order
const cancelOrder = async () => {
    try {
        await axios.put(`/orders/${props.order.id}/cancel`);

        showToast(
            locale.value === 'lv'
                ? 'Pasūtījums veiksmīgi atcelts'
                : 'Order cancelled successfully',
            'success'
        );

        showCancelModal.value = false;

        // Redirect to orders list after short delay
        setTimeout(() => {
            router.visit('/orders');
        }, 1500);

    } catch (error) {
        console.error('Cancel error:', error);

        showToast(
            error.response?.data?.message ||
            (locale.value === 'lv'
                ? 'Kļūda atceļot pasūtījumu'
                : 'Error cancelling order'),
            'error'
        );
    }
};

// Download invoice (placeholder)
const downloadInvoice = () => {
    showToast(
        locale.value === 'lv'
            ? 'Rēķina lejupielāde sāksies drīz...'
            : 'Invoice download will start shortly...',
        'info'
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${locale === 'lv' ? 'Pasūtījums' : 'Order'} #${order.order_number}`" />

        <!-- Toast Notification -->
        <ToastNotification
            :show="toast.show"
            :message="toast.message"
            :type="toast.type"
            @close="toast.show = false"
        />

        <div class="order-detail-page">
            <div class="order-detail-container">
                <!-- Header -->
                <div class="order-header">
                    <Link href="/orders" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        {{ locale === 'lv' ? 'Atpakaļ uz pasūtījumiem' : 'Back to Orders' }}
                    </Link>
                    <div class="header-content">
                        <div class="header-info">
                            <h1 class="order-title">
                                {{ locale === 'lv' ? 'Pasūtījums' : 'Order' }} #{{ order.order_number }}
                            </h1>
                            <p class="order-date">
                                <i class="fas fa-calendar"></i>
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <div class="header-status">
                            <span :class="['status-badge', `status-${order.status}`]">
                                <i :class="getStatusIcon(order.status)"></i>
                                {{ getStatusLabel(order.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="order-content">
                    <!-- Left Column -->
                    <div class="order-main">
                        <!-- Order Items -->
                        <div class="order-section">
                            <h2 class="section-title">
                                <i class="fas fa-box"></i>
                                {{ locale === 'lv' ? 'Pasūtītie produkti' : 'Ordered Products' }}
                            </h2>
                            <div class="items-list">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="order-item"
                                >
                                    <img
                                        :src="item.product?.image || '/img/Products/placeholder.png'"
                                        :alt="item.product_name"
                                        class="item-image"
                                    >
                                    <div class="item-details">
                                        <h3 class="item-name">{{ item.product_name }}</h3>
                                        <div class="item-meta">
                                            <span class="item-price">{{ formatPrice(item.price) }}€ × {{ item.quantity }}</span>
                                        </div>
                                    </div>
                                    <div class="item-total">
                                        {{ formatPrice(item.total) }}€
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="order-section">
                            <h2 class="section-title">
                                <i class="fas fa-user"></i>
                                {{ locale === 'lv' ? 'Klienta informācija' : 'Customer Information' }}
                            </h2>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">{{ locale === 'lv' ? 'Vārds:' : 'Name:' }}</span>
                                    <span class="info-value">{{ order.customer_name }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">{{ locale === 'lv' ? 'E-pasts:' : 'Email:' }}</span>
                                    <span class="info-value">{{ order.customer_email }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">{{ locale === 'lv' ? 'Tālrunis:' : 'Phone:' }}</span>
                                    <span class="info-value">{{ order.customer_phone }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Info -->
                        <div class="order-section">
                            <h2 class="section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ locale === 'lv' ? 'Piegādes informācija' : 'Delivery Information' }}
                            </h2>
                            <div class="info-grid">
                                <div class="info-item full-width">
                                    <span class="info-label">{{ locale === 'lv' ? 'Adrese:' : 'Address:' }}</span>
                                    <span class="info-value">
                                        {{ order.delivery_address }}<br>
                                        {{ order.delivery_city }}, {{ order.delivery_postal_code }}<br>
                                        {{ order.delivery_country }}
                                    </span>
                                </div>
                                <div v-if="order.tracking_number" class="info-item">
                                    <span class="info-label">{{ locale === 'lv' ? 'Izsekošanas numurs:' : 'Tracking Number:' }}</span>
                                    <span class="info-value tracking-number">{{ order.tracking_number }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="order.notes" class="order-section">
                            <h2 class="section-title">
                                <i class="fas fa-comment"></i>
                                {{ locale === 'lv' ? 'Piezīmes' : 'Notes' }}
                            </h2>
                            <p class="order-notes">{{ order.notes }}</p>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="order-sidebar">
                        <!-- Order Summary -->
                        <div class="summary-card">
                            <h3 class="summary-title">
                                {{ locale === 'lv' ? 'Pasūtījuma kopsavilkums' : 'Order Summary' }}
                            </h3>

                            <div class="summary-row">
                                <span>{{ locale === 'lv' ? 'Starpsumma:' : 'Subtotal:' }}</span>
                                <span>{{ formatPrice(order.subtotal) }}€</span>
                            </div>

                            <div class="summary-row">
                                <span>{{ locale === 'lv' ? 'Piegāde:' : 'Shipping:' }}</span>
                                <span>{{ formatPrice(order.shipping_cost) }}€</span>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-row summary-total">
                                <span>{{ locale === 'lv' ? 'Kopā:' : 'Total:' }}</span>
                                <span>{{ formatPrice(order.total_amount) }}€</span>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div v-if="order.payment" class="summary-card">
                            <h3 class="summary-title">
                                {{ locale === 'lv' ? 'Apmaksa' : 'Payment' }}
                            </h3>

                            <div class="payment-info">
                                <div class="payment-row">
                                    <span class="payment-label">{{ locale === 'lv' ? 'Veids:' : 'Method:' }}</span>
                                    <span class="payment-value">{{ getPaymentMethodLabel(order.payment.payment_method) }}</span>
                                </div>
                                <div class="payment-row">
                                    <span class="payment-label">{{ locale === 'lv' ? 'Statuss:' : 'Status:' }}</span>
                                    <span :class="['payment-status', `payment-${order.payment.status}`]">
                                        {{ getPaymentStatusLabel(order.payment.status) }}
                                    </span>
                                </div>
                                <div v-if="order.paid_at" class="payment-row">
                                    <span class="payment-label">{{ locale === 'lv' ? 'Apmaksāts:' : 'Paid at:' }}</span>
                                    <span class="payment-value">{{ formatDateTime(order.paid_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Timeline -->
                        <div class="summary-card">
                            <h3 class="summary-title">
                                {{ locale === 'lv' ? 'Pasūtījuma statuss' : 'Order Timeline' }}
                            </h3>

                            <div class="timeline">
                                <div
                                    v-for="(step, index) in getOrderTimeline()"
                                    :key="index"
                                    :class="['timeline-item', { 'active': step.active, 'completed': step.completed }]"
                                >
                                    <div class="timeline-icon">
                                        <i :class="step.icon"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <p class="timeline-label">{{ step.label }}</p>
                                        <p v-if="step.date" class="timeline-date">{{ formatDateTime(step.date) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Darbības -->
                        <div class="order-actions">
                            <!-- Pasūtījuma atcelšana -->
                            <button
                                v-if="canCancelOrder()"
                                @click="showCancelModal = true"
                                class="btn btn-cancel"
                            >
                                <i class="fas fa-times"></i>
                                {{ locale === 'lv' ? 'Atcelt pasūtījumu' : 'Cancel Order' }}
                            </button>

                            <!-- Pasūtījuma rēķina lejupielāde -->
                            <a
                                :href="`/orders/${order.id}/invoice`"
                                class="btn btn-invoice"
                            >
                                <i class="fas fa-file-invoice"></i>
                                {{ locale === 'lv' ? 'Lejupielādēt rēķinu' : 'Download Invoice' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <ConfirmModal
            :show="showCancelModal"
            :title="locale === 'lv' ? 'Atcelt pasūtījumu?' : 'Cancel Order?'"
            :message="locale === 'lv'
                ? `Vai tiešām vēlaties atcelt pasūtījumu #${order.order_number}?`
                : `Are you sure you want to cancel order #${order.order_number}?`"
            :confirmText="locale === 'lv' ? 'Jā, atcelt' : 'Yes, Cancel'"
            :cancelText="locale === 'lv' ? 'Nē' : 'No'"
            type="warning"
            @confirm="cancelOrder"
            @cancel="showCancelModal = false"
            @close="showCancelModal = false"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.order-detail-page {
    background: #f9fafb;
    min-height: 100vh;
    padding: 40px 20px;
}

.order-detail-container {
    max-width: 1400px;
    margin: 0 auto;
}

.order-header {
    margin-bottom: 32px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 16px;
    transition: color 0.2s;
}

.back-link:hover {
    color: #dc2626;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px 0;
}

.order-date {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-confirmed { background: #dbeafe; color: #1e40af; }
.status-processing { background: #e0e7ff; color: #4338ca; }
.status-packed { background: #ede9fe; color: #6b21a8; }
.status-shipped { background: #fed7aa; color: #9a3412; }
.status-in_transit { background: #ccfbf1; color: #115e59; }
.status-delivered { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-refunded { background: #f3f4f6; color: #374151; }

.order-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 24px;
}

.order-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    color: #dc2626;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.order-item {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 16px;
    align-items: center;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.item-name {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.item-meta {
    font-size: 14px;
    color: #6b7280;
}

.item-total {
    font-size: 18px;
    font-weight: 700;
    color: #dc2626;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

.info-value {
    font-size: 15px;
    color: #1f2937;
}

.tracking-number {
    font-family: monospace;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
}

.order-notes {
    font-size: 15px;
    color: #374151;
    line-height: 1.6;
    margin: 0;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

/* Sidebar */
.summary-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.summary-title {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 16px 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    color: #6b7280;
}

.summary-total {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    padding-top: 16px;
    border-top: 2px solid #e5e7eb;
}

.summary-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 12px 0;
}

.payment-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-label {
    font-size: 14px;
    color: #6b7280;
}

.payment-value {
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.payment-status {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.payment-pending { background: #fef3c7; color: #92400e; }
.payment-completed { background: #d1fae5; color: #065f46; }
.payment-failed { background: #fee2e2; color: #991b1b; }
.payment-refunded { background: #f3f4f6; color: #374151; }

/* Timeline */
.timeline {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    position: relative;
    padding-left: 4px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 36px;
    width: 2px;
    height: calc(100% + 8px);
    background: #e5e7eb;
}

.timeline-item.completed::before {
    background: #10b981;
}

.timeline-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e5e7eb;
    color: #9ca3af;
    font-size: 14px;
    flex-shrink: 0;
    z-index: 1;
}

.timeline-item.completed .timeline-icon {
    background: #d1fae5;
    color: #059669;
}

.timeline-item.active .timeline-icon {
    background: #dc2626;
    color: white;
    animation: pulse 2s infinite;
}

.timeline-content {
    flex: 1;
    padding-top: 4px;
}

.timeline-label {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    margin: 0 0 4px 0;
}

.timeline-date {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

/* Actions */
.order-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn {
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
}

.btn-cancel {
    background: #fee2e2;
    color: #991b1b;
}

.btn-cancel:hover {
    background: #fecaca;
}

.btn-invoice {
    background: #dc2626;
    color: white;
}

.btn-invoice:hover {
    background: #b91c1c;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .order-content {
        grid-template-columns: 1fr;
    }

    .order-main {
        order: 2;
    }

    .order-sidebar {
        order: 1;
    }
}

@media (max-width: 768px) {
    .order-title {
        font-size: 24px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .order-item {
        grid-template-columns: 60px 1fr;
        gap: 12px;
    }

    .item-total {
        grid-column: 2;
        text-align: right;
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
</style>
