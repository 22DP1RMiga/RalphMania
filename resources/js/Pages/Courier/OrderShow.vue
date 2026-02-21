<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    courier:    { type: Object, required: true },
    assignment: { type: Object, required: true },
    order:      { type: Object, required: true },
});

const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => toast.value = { show: true, message, type };

const notes = ref(props.assignment.notes || '');
const saving = ref(false);
const updating = ref(false);

// ─── STATUS CONFIG ────────────────────────────────────────────────────────────
const statusConfig = {
    pending:    { label: { lv: 'Gaida',      en: 'Pending'    }, color: '#92400e', bg: '#fef3c7' },
    confirmed:  { label: { lv: 'Apstiprināts',en: 'Confirmed' }, color: '#1e40af', bg: '#dbeafe' },
    processing: { label: { lv: 'Apstrādē',   en: 'Processing' }, color: '#4338ca', bg: '#e0e7ff' },
    packed:     { label: { lv: 'Iepakots',   en: 'Packed'     }, color: '#6d28d9', bg: '#ede9fe' },
    shipped:    { label: { lv: 'Nosūtīts',   en: 'Shipped'    }, color: '#d97706', bg: '#fef3c7' },
    in_transit: { label: { lv: 'Ceļā',       en: 'In Transit' }, color: '#2563eb', bg: '#dbeafe' },
    delivered:  { label: { lv: 'Piegādāts',  en: 'Delivered'  }, color: '#059669', bg: '#d1fae5' },
    cancelled:  { label: { lv: 'Atcelts',    en: 'Cancelled'  }, color: '#dc2626', bg: '#fee2e2' },
};
const getStatus = (s) => statusConfig[s] || { label: { lv: s, en: s }, color: '#6b7280', bg: '#f3f4f6' };
const getStatusLabel = (s) => getStatus(s).label[locale.value];

// ─── TIMELINE ────────────────────────────────────────────────────────────────
const timeline = [
    { status: 'packed',     icon: 'fas fa-box',            label: { lv: 'Iepakots',   en: 'Packed'     } },
    { status: 'shipped',    icon: 'fas fa-shipping-fast',  label: { lv: 'Nosūtīts',   en: 'Shipped'    } },
    { status: 'in_transit', icon: 'fas fa-truck',          label: { lv: 'Ceļā',       en: 'In Transit' } },
    { status: 'delivered',  icon: 'fas fa-check-double',   label: { lv: 'Piegādāts',  en: 'Delivered'  } },
];

const statusOrder = ['packed', 'shipped', 'in_transit', 'delivered'];
const currentStatusIndex = (s) => statusOrder.indexOf(s);

// ─── ALLOWED TRANSITIONS ─────────────────────────────────────────────────────
const nextStatusMap = {
    packed:     { status: 'shipped',    label: { lv: 'Nosūtīts',       en: 'Shipped'     }, icon: 'fas fa-shipping-fast' },
    shipped:    { status: 'in_transit', label: { lv: 'Ceļā',           en: 'In Transit'  }, icon: 'fas fa-truck' },
    in_transit: { status: 'delivered',  label: { lv: 'Piegādāts',      en: 'Delivered'   }, icon: 'fas fa-check-double' },
};
const nextStep = nextStatusMap[props.order.status] || null;

// ─── UPDATE STATUS ────────────────────────────────────────────────────────────
const currentOrderStatus = ref(props.order.status);

const updateStatus = async () => {
    if (!nextStep) return;
    updating.value = true;

    try {
        const { data } = await axios.put(`/courier/orders/${props.order.id}/status`, {
            status: nextStep.status,
            notes: notes.value || null,
        });

        currentOrderStatus.value = nextStep.status;
        showToast(data.message || 'Statuss atjaunināts!', 'success');

        setTimeout(() => window.location.reload(), 1200);
    } catch (err) {
        showToast(err.response?.data?.message || 'Kļūda mainot statusu.', 'error');
    } finally {
        updating.value = false;
    }
};

// ─── SAVE NOTES ───────────────────────────────────────────────────────────────
const saveNotes = async () => {
    saving.value = true;
    try {
        await axios.put(`/courier/orders/${props.order.id}/notes`, { notes: notes.value });
        showToast(locale.value === 'lv' ? 'Piezīmes saglabātas!' : 'Notes saved!', 'success');
    } catch {
        showToast(locale.value === 'lv' ? 'Kļūda saglabājot.' : 'Error saving.', 'error');
    } finally {
        saving.value = false;
    }
};

const formatDate = (d) => d ? new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
}).format(new Date(d)) : null;

const formatPrice = (p) => parseFloat(p || 0).toFixed(2);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${locale === 'lv' ? 'Pasūtījums' : 'Order'} #${order.order_number}`" />

        <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

        <div class="order-detail">
            <!-- Header -->
            <div class="page-header">
                <Link href="/courier/orders" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    {{ locale === 'lv' ? 'Atpakaļ uz pasūtījumiem' : 'Back to Orders' }}
                </Link>
                <div class="header-row">
                    <h1 class="page-title">
                        {{ locale === 'lv' ? 'Pasūtījums' : 'Order' }} #{{ order.order_number }}
                    </h1>
                    <span
                        class="status-badge large"
                        :style="{ background: getStatus(currentOrderStatus).bg, color: getStatus(currentOrderStatus).color }"
                    >
                        {{ getStatusLabel(currentOrderStatus) }}
                    </span>
                </div>
            </div>

            <div class="detail-grid">
                <!-- LEFT COLUMN -->
                <div class="detail-main">

                    <!-- Delivery timeline -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-route"></i> {{ locale === 'lv' ? 'Piegādes gaita' : 'Delivery Progress' }}</h2>
                        <div class="timeline">
                            <div
                                v-for="(step, i) in timeline"
                                :key="step.status"
                                :class="['timeline-step',
                                    { 'done': currentStatusIndex(currentOrderStatus) > i,
                                      'active': currentOrderStatus === step.status }
                                ]"
                            >
                                <div class="tl-icon">
                                    <i :class="step.icon"></i>
                                </div>
                                <div class="tl-label">{{ step.label[locale] }}</div>
                                <div v-if="i < timeline.length - 1" class="tl-line"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action: next status -->
                    <div class="card action-card" v-if="nextStep && !assignment.is_completed">
                        <h2 class="card-title"><i class="fas fa-bolt"></i> {{ locale === 'lv' ? 'Nākamā darbība' : 'Next Action' }}</h2>
                        <p class="action-hint">
                            {{ locale === 'lv'
                            ? 'Kad pasūtījums ir gatavs, nospied pogu, lai atjauninātu statusu.'
                            : 'When the order is ready, press the button to update status.' }}
                        </p>
                        <button @click="updateStatus" :disabled="updating" class="action-btn">
                            <i v-if="updating" class="fas fa-spinner fa-spin"></i>
                            <i v-else :class="nextStep.icon"></i>
                            {{ nextStep.label[locale] }}
                        </button>
                    </div>

                    <div class="card delivered-card" v-if="assignment.is_completed">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>{{ locale === 'lv' ? 'Piegāde pabeigta!' : 'Delivery completed!' }}</strong>
                            <p>{{ formatDate(assignment.completed_at) }}</p>
                        </div>
                    </div>

                    <!-- Order items -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-box"></i> {{ locale === 'lv' ? 'Pasūtītie produkti' : 'Order Items' }}</h2>
                        <div class="items-list">
                            <div v-for="item in order.items" :key="item.id" class="item-row">
                                <img :src="item.image || '/img/Products/placeholder.png'" :alt="item.product_name" class="item-img" />
                                <div class="item-info">
                                    <span class="item-name">{{ item.product_name }}</span>
                                    <span v-if="item.size" class="item-size">{{ item.size }}</span>
                                </div>
                                <span class="item-qty">×{{ item.quantity }}</span>
                                <span class="item-price">{{ formatPrice(item.total) }}€</span>
                            </div>
                        </div>
                        <div class="total-row">
                            <span>{{ locale === 'lv' ? 'Kopā:' : 'Total:' }}</span>
                            <span class="total-amount">{{ formatPrice(order.total_amount) }}€</span>
                        </div>
                    </div>

                    <!-- Courier notes -->
                    <div class="card" v-if="!assignment.is_completed">
                        <h2 class="card-title"><i class="fas fa-sticky-note"></i> {{ locale === 'lv' ? 'Manas piezīmes' : 'My Notes' }}</h2>
                        <textarea
                            v-model="notes"
                            :placeholder="locale === 'lv' ? 'Pievieno piezīmes par šo piegādi...' : 'Add notes about this delivery...'"
                            class="notes-textarea"
                            rows="4"
                        ></textarea>
                        <button @click="saveNotes" :disabled="saving" class="btn btn-outline">
                            <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            {{ locale === 'lv' ? 'Saglabāt piezīmes' : 'Save Notes' }}
                        </button>
                    </div>
                    <div class="card" v-else-if="assignment.notes">
                        <h2 class="card-title"><i class="fas fa-sticky-note"></i> {{ locale === 'lv' ? 'Piezīmes' : 'Notes' }}</h2>
                        <p class="readonly-notes">{{ assignment.notes }}</p>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="detail-sidebar">

                    <!-- Customer info -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-user"></i> {{ locale === 'lv' ? 'Klienta informācija' : 'Customer Info' }}</h2>
                        <div class="info-list">
                            <div class="info-row">
                                <span class="info-label">{{ locale === 'lv' ? 'Vārds' : 'Name' }}</span>
                                <span>{{ order.customer_name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">{{ locale === 'lv' ? 'Tālrunis' : 'Phone' }}</span>
                                <a :href="`tel:${order.customer_phone}`" class="phone-link">{{ order.customer_phone }}</a>
                            </div>
                            <div class="info-row">
                                <span class="info-label">E-pasts</span>
                                <a :href="`mailto:${order.customer_email}`" class="phone-link">{{ order.customer_email }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery address -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-map-marker-alt"></i> {{ locale === 'lv' ? 'Piegādes adrese' : 'Delivery Address' }}</h2>
                        <div class="address-block">
                            <p>{{ order.delivery_address }}</p>
                            <p>{{ order.delivery_city }}, {{ order.delivery_postal_code }}</p>
                            <p>{{ order.delivery_country }}</p>
                        </div>
                        <a
                            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(order.delivery_address + ', ' + order.delivery_city)}`"
                            target="_blank"
                            class="maps-link"
                        >
                            <i class="fas fa-external-link-alt"></i>
                            {{ locale === 'lv' ? 'Atvērt Google Maps' : 'Open Google Maps' }}
                        </a>
                    </div>

                    <!-- Order notes from customer -->
                    <div class="card" v-if="order.notes">
                        <h2 class="card-title"><i class="fas fa-comment-alt"></i> {{ locale === 'lv' ? 'Klienta piezīmes' : 'Customer Notes' }}</h2>
                        <p class="customer-notes">{{ order.notes }}</p>
                    </div>

                    <!-- Assignment info -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-calendar"></i> {{ locale === 'lv' ? 'Piešķīruma info' : 'Assignment Info' }}</h2>
                        <div class="info-list">
                            <div class="info-row">
                                <span class="info-label">{{ locale === 'lv' ? 'Piešķirts' : 'Assigned' }}</span>
                                <span>{{ formatDate(assignment.assigned_at) }}</span>
                            </div>
                            <div class="info-row" v-if="assignment.completed_at">
                                <span class="info-label">{{ locale === 'lv' ? 'Pabeigts' : 'Completed' }}</span>
                                <span class="done-text">{{ formatDate(assignment.completed_at) }}</span>
                            </div>
                            <div class="info-row" v-if="order.tracking_number">
                                <span class="info-label">{{ locale === 'lv' ? 'Izsekošana' : 'Tracking' }}</span>
                                <span class="tracking">{{ order.tracking_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.order-detail { background: #f9fafb; min-height: 100vh; padding: 32px 20px; max-width: 1300px; margin: 0 auto; }
.back-link { display: inline-flex; align-items: center; gap: 6px; color: #6b7280; text-decoration: none; font-size: 13px; margin-bottom: 12px; }
.back-link:hover { color: #dc2626; }
.page-header { margin-bottom: 24px; }
.header-row { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.page-title { font-size: 26px; font-weight: 700; color: #1f2937; margin: 0; }
.status-badge { padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
.status-badge.large { font-size: 14px; padding: 6px 16px; }

.detail-grid { display: grid; grid-template-columns: 1fr 360px; gap: 20px; }
.detail-main { display: flex; flex-direction: column; gap: 20px; }
.detail-sidebar { display: flex; flex-direction: column; gap: 20px; }

.card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.card-title { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; }
.card-title i { color: #dc2626; }

/* Timeline */
.timeline { display: flex; align-items: center; justify-content: space-between; position: relative; }
.timeline-step { display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; position: relative; }
.tl-icon { width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 16px; z-index: 1; transition: all 0.3s; }
.timeline-step.done .tl-icon { background: #d1fae5; color: #059669; }
.timeline-step.active .tl-icon { background: #dc2626; color: white; box-shadow: 0 0 0 4px #fecaca; }
.tl-label { font-size: 12px; color: #6b7280; font-weight: 500; text-align: center; }
.timeline-step.done .tl-label { color: #059669; }
.timeline-step.active .tl-label { color: #dc2626; font-weight: 700; }
.tl-line { position: absolute; left: 50%; top: 20px; width: 100%; height: 2px; background: #e5e7eb; z-index: 0; }
.timeline-step.done .tl-line { background: #d1fae5; }

/* Action card */
.action-card { border: 2px solid #fecaca; background: #fff5f5; }
.action-hint { font-size: 13px; color: #6b7280; margin: 0 0 16px 0; }
.action-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px; background: #dc2626; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.15s; }
.action-btn:hover:not(:disabled) { background: #b91c1c; }
.action-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.delivered-card { display: flex; align-items: center; gap: 16px; background: #d1fae5; border: 2px solid #6ee7b7; }
.delivered-card i { font-size: 32px; color: #059669; }
.delivered-card strong { font-size: 16px; color: #065f46; }
.delivered-card p { margin: 4px 0 0 0; font-size: 13px; color: #059669; }

/* Items */
.items-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; }
.item-row { display: flex; align-items: center; gap: 12px; }
.item-img { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; }
.item-info { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.item-name { font-size: 14px; font-weight: 500; color: #111827; }
.item-size { display: inline-block; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 3px; padding: 1px 6px; font-size: 11px; font-weight: 700; color: #374151; width: fit-content; }
.item-qty { font-size: 14px; color: #6b7280; font-weight: 500; }
.item-price { font-size: 14px; font-weight: 700; color: #1f2937; min-width: 60px; text-align: right; }
.total-row { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 15px; }
.total-amount { font-size: 20px; font-weight: 700; color: #dc2626; }

/* Notes */
.notes-textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-family: inherit; resize: vertical; margin-bottom: 12px; box-sizing: border-box; }
.notes-textarea:focus { outline: none; border-color: #dc2626; }
.readonly-notes { font-size: 14px; color: #374151; line-height: 1.6; }

/* Info list */
.info-list { display: flex; flex-direction: column; gap: 10px; }
.info-row { display: flex; justify-content: space-between; align-items: flex-start; font-size: 13px; gap: 12px; }
.info-label { color: #9ca3af; font-weight: 500; flex-shrink: 0; }
.phone-link { color: #dc2626; text-decoration: none; font-weight: 500; }
.phone-link:hover { text-decoration: underline; }
.done-text { color: #059669; font-weight: 500; }
.tracking { font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }

.address-block { font-size: 14px; color: #374151; line-height: 1.8; margin-bottom: 12px; }
.address-block p { margin: 0; }
.maps-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 500; }
.maps-link:hover { text-decoration: underline; }

.customer-notes { font-size: 14px; color: #374151; line-height: 1.6; font-style: italic; margin: 0; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; }
.btn-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover:not(:disabled) { background: #f3f4f6; }
.btn-outline:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 1024px) { .detail-grid { grid-template-columns: 1fr; } .detail-sidebar { order: -1; } }
@media (max-width: 640px) { .timeline { gap: 4px; } .tl-label { font-size: 10px; } }
</style>
