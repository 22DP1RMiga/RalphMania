<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    courier:           { type: Object, required: true },
    recentAssignments: { type: Array,  default: () => [] },
});

const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (msg, type = 'success') => toast.value = { show: true, message: msg, type };

// ─── STATUS CONFIG ────────────────────────────────────────────────────────────
const statusConfig = {
    packed:     { label: { lv: 'Iepakots',  en: 'Packed'     }, color: '#8b5cf6', bg: '#ede9fe' },
    shipped:    { label: { lv: 'Nosūtīts',  en: 'Shipped'    }, color: '#d97706', bg: '#fef3c7' },
    in_transit: { label: { lv: 'Ceļā',      en: 'In Transit' }, color: '#2563eb', bg: '#dbeafe' },
    delivered:  { label: { lv: 'Piegādāts', en: 'Delivered'  }, color: '#059669', bg: '#d1fae5' },
    cancelled:  { label: { lv: 'Atcelts',   en: 'Cancelled'  }, color: '#dc2626', bg: '#fee2e2' },
};
const getStatus = (s) => statusConfig[s] || { label: { lv: s, en: s }, color: '#6b7280', bg: '#f3f4f6' };
const getStatusLabel = (s) => getStatus(s).label[locale.value] ?? s;

const formatDate = (d) => d ? new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
}).format(new Date(d)) : '—';

const formatDateShort = (d) => d ? new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
    year: 'numeric', month: 'short', day: 'numeric'
}).format(new Date(d)) : '—';

const formatPrice = (p) => parseFloat(p || 0).toFixed(2);

// ─── ACTIONS ─────────────────────────────────────────────────────────────────
const toggling = ref(false);
const toggleActive = async () => {
    toggling.value = true;
    try {
        const { data } = await axios.put(`/admin/couriers/${props.courier.id}/toggle-active`);
        showToast(data.message);
        setTimeout(() => router.reload(), 600);
    } catch (err) {
        showToast(err.response?.data?.message || 'Kļūda.', 'error');
    } finally {
        toggling.value = false;
    }
};

const confirmRemove = ref(false);
const removeConfirmed = () => {
    router.delete(`/admin/couriers/${props.courier.id}`, {
        onSuccess: () => router.visit('/admin/couriers'),
        onError: (e) => showToast(Object.values(e)[0] || 'Kļūda.', 'error'),
    });
};

// ─── EDIT MODAL ───────────────────────────────────────────────────────────────
const showEditModal = ref(false);
const isEditing     = ref(false);
const editForm = ref({
    full_name:     props.courier.full_name,
    phone:         props.courier.phone,
    vehicle_type:  props.courier.vehicle_type || '',
    delivery_area: props.courier.delivery_area || '',
    hired_at:      props.courier.hired_at ? String(props.courier.hired_at).substring(0, 10) : '',
    username:      props.courier.user?.username || '',
});

const submitEdit = async () => {
    isEditing.value = true;
    try {
        const { data } = await axios.put(`/admin/couriers/${props.courier.id}`, editForm.value);
        showToast(data.message || 'Saglabāts!');
        showEditModal.value = false;
        setTimeout(() => router.reload(), 600);
    } catch (err) {
        const errors = err.response?.data?.errors;
        const msg = errors ? Object.values(errors)[0][0] : (err.response?.data?.message || 'Kļūda.');
        showToast(msg, 'error');
    } finally {
        isEditing.value = false;
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`${locale === 'lv' ? 'Kurjers' : 'Courier'}: ${courier.full_name}`" />
        <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

        <div class="courier-show">

            <!-- ─── HEADER ─── -->
            <div class="page-header">
                <div class="header-left">
                    <Link href="/admin/couriers" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        {{ locale === 'lv' ? 'Visi kurjeri' : 'All Couriers' }}
                    </Link>
                    <div class="courier-title">
                        <img
                            :src="courier.user?.profile_picture || '/img/default-avatar.png'"
                            class="courier-avatar"
                            :alt="courier.full_name"
                        />
                        <div>
                            <h1 class="page-title">{{ courier.full_name }}</h1>
                            <div class="courier-meta">
                                <span v-if="courier.user?.email" class="meta-item"><i class="fas fa-envelope"></i> {{ courier.user.email }}</span>
                                <span v-if="courier.user?.username" class="meta-item"><i class="fas fa-at"></i> {{ courier.user.username }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <span :class="['status-badge', courier.is_active ? 'active' : 'inactive']">
                        <i :class="courier.is_active ? 'fas fa-circle' : 'far fa-circle'"></i>
                        {{ courier.is_active ? (locale === 'lv' ? 'Aktīvs' : 'Active') : (locale === 'lv' ? 'Neaktīvs' : 'Inactive') }}
                    </span>
                    <button @click="showEditModal = true" class="btn btn-outline">
                        <i class="fas fa-edit"></i>
                        {{ locale === 'lv' ? 'Rediģēt' : 'Edit' }}
                    </button>
                    <button @click="toggleActive" :disabled="toggling" class="btn btn-outline">
                        <i v-if="toggling" class="fas fa-spinner fa-spin"></i>
                        <i v-else :class="courier.is_active ? 'fas fa-pause-circle' : 'fas fa-play-circle'"></i>
                        {{ courier.is_active ? (locale === 'lv' ? 'Deaktivizēt' : 'Deactivate') : (locale === 'lv' ? 'Aktivizēt' : 'Activate') }}
                    </button>
                    <button @click="confirmRemove = true" class="btn btn-danger-outline">
                        <i class="fas fa-user-minus"></i>
                        {{ locale === 'lv' ? 'Noņemt' : 'Remove' }}
                    </button>
                </div>
            </div>

            <!-- ─── STATS ─── -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706"><i class="fas fa-route"></i></div>
                    <div>
                        <div class="stat-val">{{ courier.active_assignments_count }}</div>
                        <div class="stat-lbl">{{ locale === 'lv' ? 'Aktīvās piegādes' : 'Active Deliveries' }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#d1fae5;color:#059669"><i class="fas fa-check-double"></i></div>
                    <div>
                        <div class="stat-val">{{ courier.completed_assignments_count }}</div>
                        <div class="stat-lbl">{{ locale === 'lv' ? 'Pabeigtas' : 'Completed' }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb"><i class="fas fa-boxes"></i></div>
                    <div>
                        <div class="stat-val">{{ courier.assignments_count }}</div>
                        <div class="stat-lbl">{{ locale === 'lv' ? 'Kopā piešķirtas' : 'Total Assigned' }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fce7f3;color:#db2777"><i class="fas fa-chart-pie"></i></div>
                    <div>
                        <div class="stat-val">
                            {{ courier.assignments_count > 0
                            ? Math.round((courier.completed_assignments_count / courier.assignments_count) * 100)
                            : 0 }}%
                        </div>
                        <div class="stat-lbl">{{ locale === 'lv' ? 'Izpildes līmenis' : 'Completion Rate' }}</div>
                    </div>
                </div>
            </div>

            <!-- ─── GRID ─── -->
            <div class="detail-grid">

                <!-- LEFT: courier details -->
                <div class="detail-main">

                    <!-- Profile card -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-id-card"></i> {{ locale === 'lv' ? 'Kurjera dati' : 'Courier Details' }}</h2>
                        <div class="info-list">
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-phone"></i> {{ locale === 'lv' ? 'Tālrunis' : 'Phone' }}</span>
                                <a :href="`tel:${courier.phone}`" class="phone-link">{{ courier.phone }}</a>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-truck"></i> {{ locale === 'lv' ? 'Transportlīdzeklis' : 'Vehicle' }}</span>
                                <span>{{ courier.vehicle_type || '—' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-map-marker-alt"></i> {{ locale === 'lv' ? 'Rajons' : 'Area' }}</span>
                                <span>{{ courier.delivery_area || '—' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-calendar-plus"></i> {{ locale === 'lv' ? 'Nodarbināts no' : 'Hired' }}</span>
                                <span>{{ formatDateShort(courier.hired_at) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-calendar"></i> {{ locale === 'lv' ? 'Pievienots' : 'Created' }}</span>
                                <span>{{ formatDateShort(courier.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Account card -->
                    <div class="card" v-if="courier.user">
                        <h2 class="card-title"><i class="fas fa-user-circle"></i> {{ locale === 'lv' ? 'Lietotāja konts' : 'User Account' }}</h2>
                        <div class="info-list">
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-at"></i> {{ locale === 'lv' ? 'Lietotājvārds' : 'Username' }}</span>
                                <span>{{ courier.user.username }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-envelope"></i> E-pasts</span>
                                <a :href="`mailto:${courier.user.email}`" class="phone-link">{{ courier.user.email }}</a>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-toggle-on"></i> {{ locale === 'lv' ? 'Konts aktīvs' : 'Account Active' }}</span>
                                <span :class="['bool-badge', courier.user.is_active ? 'yes' : 'no']">
                                    {{ courier.user.is_active ? (locale === 'lv' ? 'Jā' : 'Yes') : (locale === 'lv' ? 'Nē' : 'No') }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-hashtag"></i> ID</span>
                                <Link :href="`/admin/users/${courier.user.id}`" class="phone-link">#{{ courier.user.id }}</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: assignment history -->
                <div class="detail-sidebar">
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-history"></i> {{ locale === 'lv' ? 'Piešķīrumu vēsture' : 'Assignment History' }}</h2>

                        <div v-if="recentAssignments.length === 0" class="empty-history">
                            <i class="fas fa-box-open"></i>
                            <p>{{ locale === 'lv' ? 'Nav piešķīrumu' : 'No assignments yet' }}</p>
                        </div>

                        <div v-else class="history-list">
                            <div v-for="a in recentAssignments" :key="a.id" class="history-item">
                                <div class="history-head">
                                    <span class="order-num">#{{ a.order?.order_number }}</span>
                                    <span
                                        v-if="a.order"
                                        class="status-pill"
                                        :style="{ background: getStatus(a.order.status).bg, color: getStatus(a.order.status).color }"
                                    >
                                        {{ getStatusLabel(a.order?.status) }}
                                    </span>
                                    <span v-if="a.completed_at" class="done-check"><i class="fas fa-check-circle"></i></span>
                                </div>
                                <div class="history-body" v-if="a.order">
                                    <span class="history-customer"><i class="fas fa-user"></i> {{ a.order.customer_name }}</span>
                                    <span class="history-amount">{{ formatPrice(a.order.total_amount) }}€</span>
                                </div>
                                <div class="history-dates">
                                    <span><i class="fas fa-calendar-plus"></i> {{ formatDate(a.assigned_at) }}</span>
                                    <span v-if="a.completed_at" class="date-done"><i class="fas fa-flag-checkered"></i> {{ formatDate(a.completed_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── EDIT MODAL ─── -->
        <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
            <div class="modal">
                <div class="modal-header">
                    <h3><i class="fas fa-edit"></i> {{ locale === 'lv' ? 'Rediģēt kurjeru' : 'Edit Courier' }}</h3>
                    <button @click="showEditModal = false" class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Lietotājvārds' : 'Username' }}</label>
                        <input v-model="editForm.username" type="text" class="form-input" />
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Pilns vārds *' : 'Full Name *' }}</label>
                        <input v-model="editForm.full_name" type="text" class="form-input" />
                    </div>
                    <div class="form-grid2">
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Tālrunis *' : 'Phone *' }}</label>
                            <input v-model="editForm.phone" type="text" class="form-input" />
                        </div>
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Transportlīdzeklis' : 'Vehicle' }}</label>
                            <input v-model="editForm.vehicle_type" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Auto, Velosipēds...' : 'Car, Bicycle...'" />
                        </div>
                    </div>
                    <div class="form-grid2">
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Piegādes rajons' : 'Delivery Area' }}</label>
                            <input v-model="editForm.delivery_area" type="text" class="form-input" />
                        </div>
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Nodarbinātības datums' : 'Hire Date' }}</label>
                            <input v-model="editForm.hired_at" type="date" class="form-input" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showEditModal = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="submitEdit" :disabled="isEditing || !editForm.full_name || !editForm.phone" class="btn btn-primary">
                        <i v-if="isEditing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ locale === 'lv' ? 'Saglabāt' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── CONFIRM REMOVE ─── -->
        <div v-if="confirmRemove" class="modal-overlay" @click.self="confirmRemove = false">
            <div class="modal modal-sm">
                <div class="modal-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> {{ locale === 'lv' ? 'Noņemt kurjeru?' : 'Remove Courier?' }}</h3>
                </div>
                <div class="modal-body">
                    <p style="color:#374151;font-size:14px;line-height:1.6">
                        {{ locale === 'lv'
                        ? `Kurjers ${courier.full_name} tiks nopazemināts uz parasta lietotāja tiesībām. Visu piešķīrumu vēsture saglabāsies.`
                        : `Courier ${courier.full_name} will be demoted to regular user. Assignment history will be preserved.` }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button @click="confirmRemove = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="removeConfirmed" class="btn btn-danger">
                        <i class="fas fa-user-minus"></i>
                        {{ locale === 'lv' ? 'Noņemt' : 'Remove' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.courier-show { padding: 24px; max-width: 1300px; margin: 0 auto; }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
.header-left { display: flex; flex-direction: column; gap: 12px; }
.back-link { display: inline-flex; align-items: center; gap: 6px; color: #6b7280; text-decoration: none; font-size: 13px; }
.back-link:hover { color: #dc2626; }
.courier-title { display: flex; align-items: center; gap: 14px; }
.courier-avatar { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb; }
.page-title { font-size: 22px; font-weight: 700; color: #1f2937; margin: 0 0 4px 0; }
.courier-meta { display: flex; gap: 14px; flex-wrap: wrap; }
.meta-item { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 5px; }
.meta-item i { color: #9ca3af; font-size: 11px; }

.header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.status-badge.active { background: #d1fae5; color: #065f46; }
.status-badge.active i { color: #10b981; font-size: 8px; }
.status-badge.inactive { background: #f3f4f6; color: #6b7280; }
.status-badge.inactive i { color: #9ca3af; font-size: 8px; }

/* Stats */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
.stat-card { background: white; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 14px; border: 1px solid #f3f4f6; box-shadow: 0 1px 2px rgba(0,0,0,0.06); }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.stat-val { font-size: 24px; font-weight: 700; color: #1f2937; line-height: 1; }
.stat-lbl { font-size: 12px; color: #6b7280; margin-top: 3px; }

/* Grid */
.detail-grid { display: grid; grid-template-columns: 340px 1fr; gap: 20px; }
.detail-main { display: flex; flex-direction: column; gap: 16px; }
.detail-sidebar { display: flex; flex-direction: column; gap: 16px; }

/* Card */
.card { background: white; border-radius: 10px; padding: 18px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.06); }
.card-title { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px; }
.card-title i { color: #dc2626; }

/* Info list */
.info-list { display: flex; flex-direction: column; gap: 10px; }
.info-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; gap: 12px; }
.info-label { color: #6b7280; display: flex; align-items: center; gap: 6px; font-size: 12px; flex-shrink: 0; }
.info-label i { width: 12px; color: #9ca3af; }
.phone-link { color: #dc2626; text-decoration: none; font-size: 13px; }
.phone-link:hover { text-decoration: underline; }
.bool-badge { padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
.bool-badge.yes { background: #d1fae5; color: #065f46; }
.bool-badge.no  { background: #fee2e2; color: #991b1b; }

/* History */
.empty-history { text-align: center; padding: 30px; color: #9ca3af; }
.empty-history i { font-size: 28px; display: block; margin-bottom: 8px; }
.empty-history p { margin: 0; font-size: 13px; }

.history-list { display: flex; flex-direction: column; gap: 0; max-height: 520px; overflow-y: auto; }
.history-item { padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
.history-item:last-child { border-bottom: none; }
.history-head { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; }
.order-num { font-weight: 700; color: #1f2937; font-size: 13px; }
.status-pill { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
.done-check { color: #10b981; font-size: 13px; margin-left: auto; }
.history-body { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 12px; }
.history-customer { color: #374151; display: flex; align-items: center; gap: 5px; }
.history-customer i { color: #9ca3af; width: 12px; }
.history-amount { font-weight: 600; color: #dc2626; }
.history-dates { display: flex; flex-direction: column; gap: 2px; font-size: 11px; color: #9ca3af; }
.history-dates i { width: 12px; }
.date-done { color: #10b981; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; text-decoration: none; }
.btn-primary { background: #dc2626; color: white; border: none; }
.btn-primary:hover:not(:disabled) { background: #b91c1c; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f3f4f6; }
.btn-danger { background: #dc2626; color: white; border: none; }
.btn-danger:hover { background: #b91c1c; }
.btn-danger-outline { background: white; color: #dc2626; border: 1px solid #fecaca; }
.btn-danger-outline:hover { background: #fee2e2; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal { background: white; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.modal-sm { max-width: 400px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; border-bottom: 1px solid #e5e7eb; }
.modal-header h3 { margin: 0; font-size: 15px; font-weight: 600; color: #1f2937; display: flex; align-items: center; gap: 8px; }
.modal-header h3 i { color: #dc2626; }
.close-btn { background: none; border: none; cursor: pointer; font-size: 17px; color: #9ca3af; padding: 4px; line-height: 1; }
.close-btn:hover { color: #374151; }
.modal-body { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.modal-footer { padding: 14px 22px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px; }
.form-label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px; }
.form-input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 7px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 2px rgba(220,38,38,0.1); }
.form-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-row { display: flex; flex-direction: column; }

/* Responsive */
@media (max-width: 1024px) {
    .detail-grid { grid-template-columns: 1fr; }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .courier-show { padding: 16px; }
    .page-header { flex-direction: column; }
    .header-actions { width: 100%; }
    .header-actions .btn { flex: 1; justify-content: center; }
    .stats-row { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .form-grid2 { grid-template-columns: 1fr; }
    .modal { margin: 0 8px; }
    .courier-title { flex-wrap: wrap; }
}
</style>
