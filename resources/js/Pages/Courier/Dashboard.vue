<script setup>
import { ref, watch, computed } from 'vue';
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

// ─── PROFILE EDIT ─────────────────────────────────────────────────────────────
const showEditProfile = ref(false);
const isSavingProfile = ref(false);
const profileForm = ref({
    full_name:     props.courier.full_name || '',
    phone:         props.courier.phone || '',
    vehicle_type:  props.courier.vehicle_type || '',
    delivery_area: props.courier.delivery_area || '',
    username:      props.courier.user?.username || '',
});

const saveProfile = async () => {
    isSavingProfile.value = true;
    try {
        const { data } = await axios.put('/courier/profile', profileForm.value);
        showToast(data.message || 'Profils atjaunināts!', 'success');
        showEditProfile.value = false;
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        const errors = err.response?.data?.errors;
        const msg = errors ? Object.values(errors)[0][0] : (err.response?.data?.message || 'Kļūda saglabājot.');
        showToast(msg, 'error');
    } finally {
        isSavingProfile.value = false;
    }
};

// ─── REPORT PROBLEM ───────────────────────────────────────────────────────────
const showReportModal  = ref(false);
const isSendingReport  = ref(false);
const reportForm = ref({ problem_type: '', order_id: null, description: '', locale: locale.value });
watch(locale, (val) => { reportForm.value.locale = val; });

const sendReport = async () => {
    isSendingReport.value = true;
    try {
        const { data } = await axios.post('/courier/report', reportForm.value);
        showToast(data.message || 'Ziņojums nosūtīts!', 'success');
        showReportModal.value = false;
        reportForm.value = { problem_type: '', order_id: null, description: '', locale: locale.value };
        // Refresh inbox after new report
        loadInbox();
    } catch (err) {
        const errors = err.response?.data?.errors;
        const msg = errors ? Object.values(errors)[0][0] : (err.response?.data?.message || 'Kļūda.');
        showToast(msg, 'error');
    } finally {
        isSendingReport.value = false;
    }
};

// ─── INBOX ────────────────────────────────────────────────────────────────────
const inboxMessages  = ref([]);
const inboxLoading   = ref(false);
const inboxLoaded    = ref(false);
const expandedMsg    = ref(null);

const loadInbox = async () => {
    inboxLoading.value = true;
    try {
        const { data } = await axios.get('/courier/inbox');
        inboxMessages.value = data.messages || [];
        inboxLoaded.value = true;
    } catch (err) {
        showToast(locale.value === 'lv' ? 'Nevarēja ielādēt ziņojumus.' : 'Could not load messages.', 'error');
    } finally {
        inboxLoading.value = false;
    }
};

const formatInboxDate = (d) => d ? new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
}).format(new Date(d)) : '—';

// Strip the "[🚨 Kurjers]" prefix for display
const cleanSubject = (subject) => subject?.replace(/^\[.*?\]\s*/, '') || subject;

// Toggle expanded message
const toggleMsg = (id) => {
    expandedMsg.value = expandedMsg.value === id ? null : id;
};
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
                <div class="header-right">
                    <div class="courier-badge">
                        <i class="fas fa-id-badge"></i>
                        {{ courier.full_name }}
                        <span class="area-tag" v-if="courier.delivery_area">{{ courier.delivery_area }}</span>
                    </div>
                    <div class="header-btns">
                        <button @click="showReportModal = true" class="btn-report-problem">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ locale === 'lv' ? 'Ziņot par problēmu' : 'Report Problem' }}
                        </button>
                        <button @click="showEditProfile = true" class="btn-edit-profile">
                            <i class="fas fa-edit"></i>
                            {{ locale === 'lv' ? 'Rediģēt profilu' : 'Edit Profile' }}
                        </button>
                    </div>
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

            <!-- ─── IESŪTNE ─── -->
            <div class="section inbox-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-inbox"></i>
                        {{ locale === 'lv' ? 'Mani ziņojumi' : 'My Messages' }}
                        <span v-if="inboxMessages.length > 0" class="count-badge">{{ inboxMessages.length }}</span>
                        <span
                            v-if="inboxMessages.filter(m => m.is_replied).length > 0"
                            class="reply-badge"
                        >
                            <i class="fas fa-reply"></i>
                            {{ inboxMessages.filter(m => m.is_replied).length }}
                            {{ locale === 'lv' ? 'atbilde' : 'repl.' }}
                        </span>
                    </h2>
                    <button
                        @click="loadInbox"
                        class="inbox-load-btn"
                        :disabled="inboxLoading"
                    >
                        <i :class="inboxLoading ? 'fas fa-spinner fa-spin' : (inboxLoaded ? 'fas fa-sync-alt' : 'fas fa-download')"></i>
                        {{ inboxLoading
                        ? (locale === 'lv' ? 'Ielādē...' : 'Loading...')
                        : (inboxLoaded
                            ? (locale === 'lv' ? 'Atjaunot' : 'Refresh')
                            : (locale === 'lv' ? 'Ielādēt ziņojumus' : 'Load Messages')) }}
                    </button>
                </div>

                <!-- Not loaded yet -->
                <div v-if="!inboxLoaded && !inboxLoading" class="inbox-placeholder">
                    <i class="fas fa-inbox"></i>
                    <p>{{ locale === 'lv' ? 'Nospied "Ielādēt ziņojumus", lai apskatītu savus nosūtītos ziņojumus un administratora atbildes.' : 'Press "Load Messages" to see your sent reports and admin replies.' }}</p>
                </div>

                <!-- Loading -->
                <div v-if="inboxLoading" class="inbox-placeholder">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>{{ locale === 'lv' ? 'Ielādē...' : 'Loading...' }}</p>
                </div>

                <!-- Empty -->
                <div v-if="inboxLoaded && !inboxLoading && inboxMessages.length === 0" class="inbox-empty">
                    <i class="fas fa-paper-plane"></i>
                    <p>{{ locale === 'lv' ? 'Vēl nav nosūtīts neviens ziņojums.' : 'No messages sent yet.' }}</p>
                </div>

                <!-- Message list -->
                <div v-if="inboxLoaded && inboxMessages.length > 0" class="inbox-list">
                    <div
                        v-for="msg in inboxMessages"
                        :key="msg.id"
                        class="inbox-item"
                        :class="{ 'has-reply': msg.is_replied, 'expanded': expandedMsg === msg.id }"
                    >
                        <!-- Item header - always visible, clickable to expand -->
                        <div class="inbox-item-head" @click="toggleMsg(msg.id)">
                            <div class="inbox-item-left">
                                <span class="inbox-status-dot" :class="msg.is_replied ? 'replied' : (msg.is_read ? 'read' : 'unread')">
                                    <i :class="msg.is_replied ? 'fas fa-reply' : (msg.is_read ? 'fas fa-envelope-open' : 'fas fa-envelope')"></i>
                                </span>
                                <div class="inbox-subject-wrap">
                                    <span class="inbox-subject">{{ cleanSubject(msg.subject) }}</span>
                                    <span v-if="msg.is_replied" class="inbox-replied-tag">
                                        <i class="fas fa-check-double"></i>
                                        {{ locale === 'lv' ? 'Atbildēts' : 'Replied' }}
                                    </span>
                                </div>
                            </div>
                            <div class="inbox-item-right">
                                <span class="inbox-date">{{ formatInboxDate(msg.created_at) }}</span>
                                <i :class="expandedMsg === msg.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="inbox-chevron"></i>
                            </div>
                        </div>

                        <!-- Expanded content -->
                        <Transition name="expand">
                            <div v-if="expandedMsg === msg.id" class="inbox-item-body">
                                <!-- Original message -->
                                <div class="inbox-msg-block sent">
                                    <div class="inbox-msg-label">
                                        <i class="fas fa-paper-plane"></i>
                                        {{ locale === 'lv' ? 'Mans ziņojums' : 'My Report' }}
                                        <span class="inbox-msg-date">{{ formatInboxDate(msg.created_at) }}</span>
                                    </div>
                                    <div class="inbox-msg-text">{{ msg.message }}</div>
                                </div>

                                <!-- Admin reply -->
                                <div v-if="msg.is_replied && msg.reply_text" class="inbox-msg-block reply">
                                    <div class="inbox-msg-label">
                                        <i class="fas fa-headset"></i>
                                        {{ locale === 'lv' ? 'Administratora atbilde' : 'Admin Reply' }}
                                        <span class="inbox-msg-date">{{ formatInboxDate(msg.replied_at) }}</span>
                                    </div>
                                    <div class="inbox-msg-text">{{ msg.reply_text }}</div>
                                </div>

                                <!-- Pending reply -->
                                <div v-else-if="!msg.is_replied" class="inbox-pending">
                                    <i class="fas fa-clock"></i>
                                    {{ locale === 'lv' ? 'Gaida administratora atbildi...' : 'Awaiting admin reply...' }}
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

        </div>

        <!-- ─── REPORT PROBLEM MODAL ─── -->
        <div v-if="showReportModal" class="modal-overlay" @click.self="showReportModal = false">
            <div class="modal">
                <div class="modal-header modal-header-danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> {{ locale === 'lv' ? 'Ziņot par piegādes problēmu' : 'Report Delivery Problem' }}</h3>
                    <button @click="showReportModal = false" class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Problēmas veids *' : 'Problem Type *' }}</label>
                        <select v-model="reportForm.problem_type" class="form-input">
                            <option value="">{{ locale === 'lv' ? 'Izvēlēties...' : 'Select...' }}</option>
                            <option value="address">{{ locale === 'lv' ? 'Nepareiza adrese' : 'Wrong address' }}</option>
                            <option value="customer">{{ locale === 'lv' ? 'Klients nesasniegts' : 'Customer unreachable' }}</option>
                            <option value="vehicle">{{ locale === 'lv' ? 'Transportlīdzekļa problēma' : 'Vehicle problem' }}</option>
                            <option value="package">{{ locale === 'lv' ? 'Bojāts iepakojums' : 'Damaged package' }}</option>
                            <option value="other">{{ locale === 'lv' ? 'Cita problēma' : 'Other problem' }}</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Saistītais pasūtījums (neobligāti)' : 'Related Order (optional)' }}</label>
                        <select v-model="reportForm.order_id" class="form-input">
                            <option :value="null">{{ locale === 'lv' ? 'Nav konkrēta pasūtījuma' : 'No specific order' }}</option>
                            <option v-for="a in activeOrders" :key="a.order.id" :value="a.order.id">
                                #{{ a.order.order_number }} — {{ a.order.customer_name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Apraksts *' : 'Description *' }}</label>
                        <textarea
                            v-model="reportForm.description"
                            class="form-input"
                            rows="5"
                            style="resize:vertical"
                            :placeholder="locale === 'lv' ? 'Apraksti problēmu pēc iespējas precīzāk...' : 'Describe the problem in as much detail as possible...'"
                        ></textarea>
                    </div>
                    <div class="report-info">
                        <i class="fas fa-info-circle"></i>
                        {{ locale === 'lv' ? 'Administrators saņems e-pastu un redzēs ziņojumu kontaktu panelī.' : 'The administrator will receive an email and see the report in the contacts panel.' }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showReportModal = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="sendReport" :disabled="isSendingReport || !reportForm.problem_type || reportForm.description.length < 10" class="btn btn-danger">
                        <i v-if="isSendingReport" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-paper-plane"></i>
                        {{ locale === 'lv' ? 'Nosūtīt ziņojumu' : 'Send Report' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── EDIT PROFILE MODAL ─── -->
        <div v-if="showEditProfile" class="modal-overlay" @click.self="showEditProfile = false">
            <div class="modal">
                <div class="modal-header">
                    <h3><i class="fas fa-edit"></i> {{ locale === 'lv' ? 'Rediģēt profilu' : 'Edit Profile' }}</h3>
                    <button @click="showEditProfile = false" class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Lietotājvārds *' : 'Username *' }}</label>
                        <input v-model="profileForm.username" type="text" class="form-input" placeholder="username" />
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Pilns vārds *' : 'Full Name *' }}</label>
                        <input v-model="profileForm.full_name" type="text" class="form-input" />
                    </div>
                    <div class="form-grid2">
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Tālrunis *' : 'Phone *' }}</label>
                            <input v-model="profileForm.phone" type="text" class="form-input" placeholder="+371 2000 0000" />
                        </div>
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Transportlīdzeklis' : 'Vehicle' }}</label>
                            <input v-model="profileForm.vehicle_type" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Auto, Velosipēds...' : 'Car, Bicycle...'" />
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Piegādes rajons' : 'Delivery Area' }}</label>
                        <input v-model="profileForm.delivery_area" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Rīgas centrs, Purvciems...' : 'City center, Suburbs...'" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showEditProfile = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="saveProfile" :disabled="isSavingProfile || !profileForm.full_name || !profileForm.phone" class="btn btn-primary">
                        <i v-if="isSavingProfile" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ locale === 'lv' ? 'Saglabāt' : 'Save' }}
                    </button>
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

.header-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.header-btns {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.btn-report-problem {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #c2410c;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-report-problem:hover {
    background: #ffedd5;
    border-color: #fb923c;
}
.btn-report-problem i { color: #ea580c; }

.btn-edit-profile {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-edit-profile:hover {
    background: #f9fafb;
    border-color: #dc2626;
    color: #dc2626;
}
.btn-edit-profile i { color: #dc2626; }

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}
.modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}
.modal-header h3 i { color: #dc2626; }
.modal-header.modal-header-danger { background: #fff5f5; }
.modal-header.modal-header-danger h3 { color: #991b1b; }
.modal-header.modal-header-danger h3 i { color: #dc2626; }
.report-info {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 12px;
    color: #6b7280;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 10px 12px;
}
.report-info i { color: #3b82f6; margin-top: 1px; flex-shrink: 0; }
.close-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    color: #9ca3af;
    padding: 4px;
    line-height: 1;
}
.close-btn:hover { color: #374151; }
.modal-body {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.form-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}
.form-input {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #d1d5db;
    border-radius: 7px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}
.form-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 2px rgba(220,38,38,0.1);
}
.form-grid2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-row { display: flex; flex-direction: column; }

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
.btn-danger { background: #dc2626; color: white; border: none; }
.btn-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }

/* Inbox section */
.inbox-section { border-top: 3px solid #fef2f2; }

.reply-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #d1fae5;
    color: #065f46;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
}

.inbox-load-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s;
}
.inbox-load-btn:hover:not(:disabled) { background: #e5e7eb; }
.inbox-load-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.inbox-placeholder {
    text-align: center;
    padding: 32px;
    color: #9ca3af;
    font-size: 13px;
}
.inbox-placeholder i { font-size: 28px; display: block; margin-bottom: 10px; color: #d1d5db; }

.inbox-empty {
    text-align: center;
    padding: 32px;
    color: #9ca3af;
    font-size: 13px;
}
.inbox-empty i { font-size: 28px; display: block; margin-bottom: 10px; color: #d1d5db; }

.inbox-list { display: flex; flex-direction: column; gap: 0; }

.inbox-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 8px;
    transition: box-shadow 0.15s;
}
.inbox-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.inbox-item.has-reply { border-color: #bbf7d0; }
.inbox-item.expanded { border-color: #fca5a5; }

.inbox-item-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    background: #fafafa;
    gap: 12px;
}
.inbox-item.has-reply .inbox-item-head { background: #f0fdf4; }
.inbox-item-head:hover { background: #f3f4f6; }
.inbox-item.has-reply .inbox-item-head:hover { background: #dcfce7; }

.inbox-item-left { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
.inbox-item-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.inbox-status-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    flex-shrink: 0;
}
.inbox-status-dot.unread  { background: #fee2e2; color: #dc2626; }
.inbox-status-dot.read    { background: #fef3c7; color: #d97706; }
.inbox-status-dot.replied { background: #d1fae5; color: #059669; }

.inbox-subject-wrap { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0; }
.inbox-subject { font-size: 13px; font-weight: 600; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.inbox-replied-tag { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 700; color: #059669; background: #d1fae5; padding: 1px 6px; border-radius: 10px; white-space: nowrap; }

.inbox-date { font-size: 11px; color: #9ca3af; white-space: nowrap; }
.inbox-chevron { font-size: 11px; color: #9ca3af; transition: transform 0.2s; }

.inbox-item-body {
    padding: 14px 16px;
    border-top: 1px solid #f3f4f6;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: white;
}

.inbox-msg-block { border-radius: 8px; overflow: hidden; }
.inbox-msg-block.sent  { border: 1px solid #e5e7eb; }
.inbox-msg-block.reply { border: 2px solid #bbf7d0; }

.inbox-msg-label {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.inbox-msg-block.sent  .inbox-msg-label { background: #f9fafb; color: #6b7280; }
.inbox-msg-block.reply .inbox-msg-label { background: #f0fdf4; color: #059669; }

.inbox-msg-date {
    margin-left: auto;
    font-size: 10px;
    font-weight: 400;
    text-transform: none;
    color: #9ca3af;
}

.inbox-msg-text { padding: 10px 12px; font-size: 13px; color: #374151; line-height: 1.6; white-space: pre-wrap; }

.inbox-pending {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #9ca3af;
    font-style: italic;
    padding: 8px 0;
}
.inbox-pending i { color: #d1d5db; }

/* expand transition */
.expand-enter-active, .expand-leave-active { transition: all 0.2s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 600px; }

/* Report button in header */
.header-btns { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
.btn-report-problem {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #c2410c;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-report-problem:hover { background: #ffedd5; border-color: #fb923c; }

/* Modal danger header */
.modal-header.modal-header-danger { background: #fff5f5; }
.modal-header.modal-header-danger h3 { color: #991b1b; }
.modal-header.modal-header-danger h3 i { color: #dc2626; }
.report-info {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 12px;
    color: #6b7280;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 4px;
}
.report-info i { color: #3b82f6; margin-top: 1px; flex-shrink: 0; }

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
