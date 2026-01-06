<script setup>
import { ref } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    orders: Array,
});

// Toast state
const toast = ref({
    show: false,
    message: '',
    type: 'success',
});

// Cancel modal state
const cancelModal = ref({
    show: false,
    order: null,
});

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

// Get status label
const getStatusLabel = (status) => {
    const labels = {
        lv: {
            pending: 'Gaida',
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

// Check if order can be cancelled
const canCancelOrder = (order) => {
    return ['pending', 'confirmed', 'processing', 'packed'].includes(order.status);
};

// Show cancel modal
const showCancelModal = (order) => {
    cancelModal.value = {
        show: true,
        order: order,
    };
};

// Close cancel modal
const closeCancelModal = () => {
    cancelModal.value = {
        show: false,
        order: null,
    };
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
    const order = cancelModal.value.order;

    try {
        await axios.put(`/orders/${order.id}/cancel`);

        // Update local order status
        order.status = 'cancelled';

        showToast(
            locale.value === 'lv'
                ? 'Pasūtījums veiksmīgi atcelts'
                : 'Order cancelled successfully',
            'success'
        );

        closeCancelModal();

        // Reload page after short delay
        setTimeout(() => {
            window.location.reload();
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
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="locale === 'lv' ? 'Mani pasūtījumi' : 'My Orders'" />

        <!-- Toast Notification -->
        <ToastNotification
            :show="toast.show"
            :message="toast.message"
            :type="toast.type"
            @close="toast.show = false"
        />

        <div class="orders-page">
            <div class="orders-container">
                <!-- Header -->
                <div class="orders-header">
                    <h1 class="orders-title">
                        <i class="fas fa-box"></i>
                        {{ locale === 'lv' ? 'Mani pasūtījumi' : 'My Orders' }}
                    </h1>
                    <p class="orders-subtitle">
                        {{ locale === 'lv'
                        ? 'Visi jūsu pasūtījumi vienuviet'
                        : 'All your orders in one place'
                        }}
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="orders.length === 0" class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h2 class="empty-title">
                        {{ locale === 'lv' ? 'Nav pasūtījumu' : 'No Orders Yet' }}
                    </h2>
                    <p class="empty-text">
                        {{ locale === 'lv'
                        ? 'Jūs vēl neesat veicis nevienu pasūtījumu'
                        : 'You haven\'t placed any orders yet'
                        }}
                    </p>
                    <Link href="/shop" class="empty-btn">
                        <i class="fas fa-shopping-bag"></i>
                        {{ locale === 'lv' ? 'Doties uz veikalu' : 'Go to Shop' }}
                    </Link>
                </div>

                <!-- Orders List -->
                <div v-else class="orders-list">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="order-card"
                    >
                        <!-- Order Header -->
                        <div class="order-header">
                            <div class="order-info">
                                <div class="order-number">
                                    <i class="fas fa-hashtag"></i>
                                    {{ order.order_number }}
                                </div>
                                <div class="order-date">
                                    <i class="fas fa-calendar"></i>
                                    {{ formatDate(order.created_at) }}
                                </div>
                            </div>
                            <div class="order-status">
                                <span :class="['status-badge', `status-${order.status}`]">
                                    <i :class="getStatusIcon(order.status)"></i>
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Order Items Preview -->
                        <div class="order-items">
                            <div
                                v-for="item in order.items.slice(0, 3)"
                                :key="item.id"
                                class="order-item"
                            >
                                <img
                                    :src="item.product?.image || '/img/Products/placeholder.png'"
                                    :alt="item.product_name"
                                    class="item-image"
                                >
                                <div class="item-info">
                                    <p class="item-name">{{ item.product_name }}</p>
                                    <p class="item-quantity">
                                        {{ locale === 'lv' ? 'Daudzums' : 'Qty' }}: {{ item.quantity }}
                                    </p>
                                </div>
                                <div class="item-price">
                                    {{ formatPrice(item.total) }}€
                                </div>
                            </div>

                            <div v-if="order.items.length > 3" class="more-items">
                                <i class="fas fa-ellipsis-h"></i>
                                {{ locale === 'lv'
                                ? `Vēl ${order.items.length - 3} preces`
                                : `${order.items.length - 3} more items`
                                }}
                            </div>
                        </div>

                        <!-- Order Footer -->
                        <div class="order-footer">
                            <div class="order-total">
                                <span class="total-label">
                                    {{ locale === 'lv' ? 'Kopā:' : 'Total:' }}
                                </span>
                                <span class="total-amount">
                                    {{ formatPrice(order.total_amount) }}€
                                </span>
                            </div>
                            <div class="order-actions">
                                <Link
                                    :href="`/orders/${order.id}`"
                                    class="btn btn-view"
                                >
                                    <i class="fas fa-eye"></i>
                                    {{ locale === 'lv' ? 'Skatīt' : 'View' }}
                                </Link>

                                <button
                                    v-if="canCancelOrder(order)"
                                    @click="showCancelModal(order)"
                                    class="btn btn-cancel"
                                >
                                    <i class="fas fa-times"></i>
                                    {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <ConfirmModal
            :show="cancelModal.show"
            :title="locale === 'lv' ? 'Atcelt pasūtījumu?' : 'Cancel Order?'"
            :message="locale === 'lv'
                ? `Vai tiešām vēlaties atcelt pasūtījumu ${cancelModal.order?.order_number}?`
                : `Are you sure you want to cancel order ${cancelModal.order?.order_number}?`"
            :confirmText="locale === 'lv' ? 'Jā, atcelt' : 'Yes, Cancel'"
            :cancelText="locale === 'lv' ? 'Nē' : 'No'"
            type="warning"
            @confirm="cancelOrder"
            @cancel="closeCancelModal"
            @close="closeCancelModal"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.orders-page {
    background: #f9fafb;
    min-height: 100vh;
    padding: 40px 20px;
}

.orders-container {
    max-width: 1200px;
    margin: 0 auto;
}

.orders-header {
    margin-bottom: 32px;
}

.orders-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.orders-title i {
    color: #dc2626;
}

.orders-subtitle {
    font-size: 16px;
    color: #6b7280;
    margin: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 24px;
    background: #fef2f2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: #dc2626;
}

.empty-title {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 12px 0;
}

.empty-text {
    font-size: 16px;
    color: #6b7280;
    margin: 0 0 32px 0;
}

.empty-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: #dc2626;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.empty-btn:hover {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Orders List */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.order-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.order-info {
    display: flex;
    gap: 24px;
}

.order-number {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 6px;
}

.order-date {
    font-size: 14px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
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

.order-items {
    margin-bottom: 20px;
}

.order-item {
    display: grid;
    grid-template-columns: 60px 1fr auto;
    gap: 16px;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.item-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.item-name {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    margin: 0;
}

.item-quantity {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.item-price {
    font-size: 15px;
    font-weight: 600;
    color: #dc2626;
}

.more-items {
    text-align: center;
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
    color: #6b7280;
    font-size: 13px;
    margin-top: 8px;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

.order-total {
    display: flex;
    align-items: center;
    gap: 12px;
}

.total-label {
    font-size: 14px;
    color: #6b7280;
}

.total-amount {
    font-size: 20px;
    font-weight: 700;
    color: #dc2626;
}

.order-actions {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    border: none;
}

.btn-view {
    background: #dc2626;
    color: white;
}

.btn-view:hover {
    background: #b91c1c;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .orders-title {
        font-size: 24px;
    }

    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .order-info {
        flex-direction: column;
        gap: 8px;
    }

    .order-footer {
        flex-direction: column;
        gap: 16px;
        align-items: stretch;
    }

    .order-actions {
        width: 100%;
    }

    .btn {
        flex: 1;
        justify-content: center;
    }
}
</style>
