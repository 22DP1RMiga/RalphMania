<script setup>
import { ref, watch, computed } from 'vue';
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

// ─── REPORT PROBLEM ───────────────────────────────────────────────────────────
const showReportModal  = ref(false);
const isSendingReport  = ref(false);
const reportForm = ref({ problem_type: '', order_id: props.order.id, description: '', locale: locale.value });
watch(locale, (val) => { reportForm.value.locale = val; });

const sendReport = async () => {
    isSendingReport.value = true;
    try {
        const { data } = await axios.post('/courier/report', reportForm.value);
        showToast(data.message || 'Ziņojums nosūtīts!', 'success');
        showReportModal.value = false;
        reportForm.value = { problem_type: '', order_id: props.order.id, description: '', locale: locale.value };
    } catch (err) {
        const errors = err.response?.data?.errors;
        const msg = errors ? Object.values(errors)[0][0] : (err.response?.data?.message || 'Kļūda.');
        showToast(msg, 'error');
    } finally {
        isSendingReport.value = false;
    }
};

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

// PVN "tai skaitā" — izvelk no subtotal bruto: vat = subtotal * 21 / 121
const VAT_RATE = 21;
const vatAmount = computed(() => {
    const subtotal = parseFloat(props.order.subtotal || 0);
    return Math.round(subtotal * VAT_RATE / (100 + VAT_RATE) * 100) / 100;
});
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
                                    <span class="item-unit-price">{{ formatPrice(item.price) }}€ × {{ item.quantity }}</span>
                                </div>
                                <span class="item-price">{{ formatPrice(item.total) }}€</span>
                            </div>
                        </div>

                        <!-- Cenu sadalījums -->
                        <div class="price-breakdown">
                            <div class="breakdown-row">
                                <span>{{ locale === 'lv' ? 'Produkti:' : 'Products:' }}</span>
                                <span>{{ formatPrice(order.subtotal) }}€</span>
                            </div>
                            <div class="breakdown-row breakdown-vat">
                                <span>
                                    <i class="fas fa-info-circle"></i>
                                    {{ locale === 'lv' ? `t.sk. PVN (${VAT_RATE}%):` : `incl. VAT (${VAT_RATE}%):` }}
                                </span>
                                <span>{{ formatPrice(vatAmount) }}€</span>
                            </div>
                            <div class="breakdown-row" v-if="order.shipping_cost > 0">
                                <span>{{ locale === 'lv' ? 'Piegāde:' : 'Shipping:' }}</span>
                                <span>{{ formatPrice(order.shipping_cost) }}€</span>
                            </div>
                            <div class="breakdown-row shipping-free" v-else>
                                <span>{{ locale === 'lv' ? 'Piegāde:' : 'Shipping:' }}</span>
                                <span>{{ locale === 'lv' ? 'Bezmaksas' : 'Free' }}</span>
                            </div>
                            <div class="breakdown-row discount-row" v-if="order.discount_amount > 0">
                                <span>
                                    {{ locale === 'lv' ? 'Atlaide:' : 'Discount:' }}
                                    <code v-if="order.coupon_code" class="coupon-chip">{{ order.coupon_code }}</code>
                                </span>
                                <span class="discount-val">-{{ formatPrice(order.discount_amount) }}€</span>
                            </div>
                            <div class="breakdown-divider"></div>
                            <div class="breakdown-row breakdown-total">
                                <span>{{ locale === 'lv' ? 'Kopā:' : 'Total:' }}</span>
                                <span class="total-amount">{{ formatPrice(order.total_amount) }}€</span>
                            </div>
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

                    <!-- Report problem -->
                    <div v-if="!assignment.is_completed" class="card report-card">
                        <h2 class="card-title card-title-warning"><i class="fas fa-exclamation-triangle"></i> {{ locale === 'lv' ? 'Piegādes problēma?' : 'Delivery Problem?' }}</h2>
                        <p class="report-hint">{{ locale === 'lv' ? 'Ziņo administratoram par jebkuru problēmu saistībā ar šo piegādi.' : 'Notify the admin about any issue with this delivery.' }}</p>
                        <button @click="showReportModal = true" class="report-btn">
                            <i class="fas fa-paper-plane"></i>
                            {{ locale === 'lv' ? 'Ziņot par problēmu' : 'Report Problem' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── REPORT PROBLEM MODAL ─── -->
        <div v-if="showReportModal" class="modal-overlay" @click.self="showReportModal = false">
            <div class="modal">
                <div class="modal-header modal-header-danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> {{ locale === 'lv' ? 'Ziņot par problēmu' : 'Report Problem' }}</h3>
                    <button @click="showReportModal = false" class="close-btn-m"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body-m">
                    <div class="form-row-m">
                        <label class="form-label-m">{{ locale === 'lv' ? 'Problēmas veids *' : 'Problem Type *' }}</label>
                        <select v-model="reportForm.problem_type" class="form-select-m">
                            <option value="">{{ locale === 'lv' ? 'Izvēlēties...' : 'Select...' }}</option>
                            <option value="address">{{ locale === 'lv' ? 'Nepareiza adrese' : 'Wrong address' }}</option>
                            <option value="customer">{{ locale === 'lv' ? 'Klients nesasniegts' : 'Customer unreachable' }}</option>
                            <option value="vehicle">{{ locale === 'lv' ? 'Transportlīdzekļa problēma' : 'Vehicle problem' }}</option>
                            <option value="package">{{ locale === 'lv' ? 'Bojāts iepakojums' : 'Damaged package' }}</option>
                            <option value="other">{{ locale === 'lv' ? 'Cita problēma' : 'Other problem' }}</option>
                        </select>
                    </div>
                    <div class="form-row-m">
                        <label class="form-label-m">{{ locale === 'lv' ? 'Apraksts *' : 'Description *' }}</label>
                        <textarea v-model="reportForm.description" class="form-select-m" rows="5" style="resize:vertical"
                                  :placeholder="locale === 'lv' ? 'Apraksti problēmu...' : 'Describe the problem...'"
                        ></textarea>
                    </div>
                    <p class="report-info-m"><i class="fas fa-info-circle"></i> {{ locale === 'lv' ? 'Administrators saņems e-pastu un redzēs ziņojumu kontaktu panelī.' : 'The admin will receive an email and see this in the contacts panel.' }}</p>
                </div>
                <div class="modal-footer-m">
                    <button @click="showReportModal = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="sendReport" :disabled="isSendingReport || !reportForm.problem_type || reportForm.description.length < 10" class="btn btn-report-send">
                        <i v-if="isSendingReport" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-paper-plane"></i>
                        {{ locale === 'lv' ? 'Nosūtīt' : 'Send' }}
                    </button>
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
.item-info { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.item-name { font-size: 14px; font-weight: 500; color: #111827; }
.item-size { display: inline-block; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 3px; padding: 1px 6px; font-size: 11px; font-weight: 700; color: #374151; width: fit-content; }
.item-unit-price { font-size: 12px; color: #9ca3af; }
.item-qty { font-size: 14px; color: #6b7280; font-weight: 500; }
.item-price { font-size: 14px; font-weight: 700; color: #1f2937; min-width: 60px; text-align: right; }

/* Cenu sadalījums */
.price-breakdown { border-top: 1px solid #f3f4f6; padding-top: 14px; display: flex; flex-direction: column; gap: 7px; }
.breakdown-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #6b7280; }
.breakdown-row.shipping-free span:last-child { color: #059669; font-weight: 600; }
.breakdown-row.discount-row { color: #059669; }
.breakdown-vat { font-size: 12px; color: #9ca3af; font-style: italic; }
.breakdown-vat i { font-size: 10px; margin-right: 3px; color: #d1d5db; }
.discount-val { color: #059669; font-weight: 700; }
.coupon-chip { background: #d1fae5; color: #065f46; padding: 1px 5px; border-radius: 3px; font-size: 10px; margin-left: 5px; font-family: monospace; border: 1px solid #a7f3d0; }
.breakdown-divider { border-top: 1px solid #e5e7eb; margin: 4px 0; }
.breakdown-total { font-size: 15px; font-weight: 600; color: #1f2937; }
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

/* Report problem card */
.report-card { border-color: #fed7aa !important; background: #fff7ed; }
.card-title-warning { color: #9a3412 !important; }
.card-title-warning i { color: #ea580c !important; }
.report-hint { font-size: 13px; color: #c2410c; margin: 0 0 12px 0; line-height: 1.5; }
.report-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 10px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    color: #c2410c;
    cursor: pointer;
    transition: all 0.15s;
}
.report-btn:hover { background: #ffedd5; border-color: #fb923c; }
.report-btn i { color: #ea580c; }

/* Report modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal { background: white; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; border-bottom: 1px solid #e5e7eb; }
.modal-header h3 { margin: 0; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.modal-header-danger { background: #fff5f5; }
.modal-header-danger h3 { color: #991b1b; }
.modal-header-danger h3 i { color: #dc2626; }
.close-btn-m { background: none; border: none; cursor: pointer; font-size: 17px; color: #9ca3af; padding: 4px; line-height: 1; }
.close-btn-m:hover { color: #374151; }
.modal-body-m { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.modal-footer-m { padding: 14px 22px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px; }
.form-row-m { display: flex; flex-direction: column; gap: 5px; }
.form-label-m { font-size: 13px; font-weight: 500; color: #374151; }
.form-select-m { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 7px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
.form-select-m:focus { outline: none; border-color: #dc2626; }
.report-info-m { font-size: 12px; color: #6b7280; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; margin: 0; display: flex; align-items: flex-start; gap: 7px; }
.report-info-m i { color: #3b82f6; margin-top: 1px; flex-shrink: 0; }
.btn-report-send { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: #dc2626; color: white; transition: all 0.15s; }
.btn-report-send:hover:not(:disabled) { background: #b91c1c; }
.btn-report-send:disabled { opacity: 0.5; cursor: not-allowed; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; }
.btn-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover:not(:disabled) { background: #f3f4f6; }
.btn-outline:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 1024px) { .detail-grid { grid-template-columns: 1fr; } .detail-sidebar { order: -1; } }
@media (max-width: 640px) { .timeline { gap: 4px; } .tl-label { font-size: 10px; } }
</style>
