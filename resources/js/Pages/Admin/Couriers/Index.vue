<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    couriers:         { type: Object,  default: () => ({ data: [] }) },
    stats:            { type: Object,  default: () => ({}) },
    filters:          { type: Object,  default: () => ({}) },
    availableUsers:   { type: Array,   default: () => [] },
    assignableOrders: { type: Array,   default: () => [] },
});

const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (msg, type = 'success') => toast.value = { show: true, message: msg, type };

// ─── FILTERS ─────────────────────────────────────────────────────────────────
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const applyFilters = () => router.get('/admin/couriers', { search: search.value, status: status.value }, { preserveState: true });

// ─── ADD COURIER MODAL ────────────────────────────────────────────────────────
const showAddModal   = ref(false);
const isSubmitting   = ref(false);
const newCourier = ref({ user_id: null, full_name: '', phone: '', vehicle_type: '', delivery_area: '', hired_at: '' });

const submitAddCourier = () => {
    isSubmitting.value = true;
    router.post('/admin/couriers', newCourier.value, {
        onSuccess: () => { showAddModal.value = false; showToast('Kurjers pievienots!'); },
        onError: (e) => showToast(Object.values(e)[0] || 'Kļūda', 'error'),
        onFinish: () => isSubmitting.value = false,
    });
};

// ─── ASSIGN ORDER MODAL ───────────────────────────────────────────────────────
const showAssignModal = ref(false);
const assignData = ref({ courier_id: null, order_id: null, notes: '' });
const assigning = ref(false);

const openAssignModal = (courierId = null) => {
    assignData.value = { courier_id: courierId, order_id: null, notes: '' };
    showAssignModal.value = true;
};

const submitAssign = async () => {
    assigning.value = true;
    try {
        const { data } = await axios.post('/admin/couriers/assign', assignData.value);
        showToast(data.message);
        showAssignModal.value = false;
        setTimeout(() => router.reload(), 800);
    } catch (err) {
        showToast(err.response?.data?.message || 'Kļūda piešķirot pasūtījumu.', 'error');
    } finally {
        assigning.value = false;
    }
};

// ─── TOGGLE ACTIVE ────────────────────────────────────────────────────────────
const toggleCourier = async (courierId) => {
    try {
        const { data } = await axios.put(`/admin/couriers/${courierId}/toggle-active`);
        showToast(data.message);
        setTimeout(() => router.reload(), 600);
    } catch (err) {
        showToast(err.response?.data?.message || 'Kļūda.', 'error');
    }
};

// ─── REMOVE COURIER ───────────────────────────────────────────────────────────
const confirmRemove = ref(null);
const removeConfirmed = () => {
    router.delete(`/admin/couriers/${confirmRemove.value}`, {
        onSuccess: () => { confirmRemove.value = null; showToast('Kurjers noņemts!'); },
        onError: (e) => { confirmRemove.value = null; showToast(Object.values(e)[0] || 'Kļūda.', 'error'); },
    });
};

// ─── EDIT COURIER MODAL ───────────────────────────────────────────────────────
const showEditModal  = ref(false);
const isEditing      = ref(false);
const editCourier = ref({ id: null, full_name: '', phone: '', vehicle_type: '', delivery_area: '', hired_at: '', username: '' });

const openEditModal = (c) => {
    editCourier.value = {
        id:            c.id,
        full_name:     c.full_name,
        phone:         c.phone,
        vehicle_type:  c.vehicle_type || '',
        delivery_area: c.delivery_area || '',
        hired_at:      c.hired_at ? c.hired_at.substring(0, 10) : '',
        username:      c.user?.username || '',
    };
    showEditModal.value = true;
};

const submitEditCourier = async () => {
    isEditing.value = true;
    try {
        const { data } = await axios.put(`/admin/couriers/${editCourier.value.id}`, editCourier.value);
        showToast(data.message || 'Kurjers atjaunināts!');
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

// ─── HELPERS ─────────────────────────────────────────────────────────────────
const formatDate = (d) => d ? new Date(d).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US') : '—';
</script>

<template>
    <AdminLayout>
        <Head title="Kurjeri" />
        <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

        <div class="admin-couriers">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="fas fa-truck"></i> {{ locale === 'lv' ? 'Kurjeri' : 'Couriers' }}</h1>
                    <p class="page-subtitle">{{ locale === 'lv' ? 'Kurjeru pārvaldība un pasūtījumu piešķiršana' : 'Manage couriers and order assignments' }}</p>
                </div>
                <div class="header-actions">
                    <button @click="openAssignModal()" class="btn btn-outline">
                        <i class="fas fa-link"></i>
                        {{ locale === 'lv' ? 'Piešķirt pasūtījumu' : 'Assign Order' }}
                    </button>
                    <button @click="showAddModal = true" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        {{ locale === 'lv' ? 'Pievienot kurjeru' : 'Add Courier' }}
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-pill"><span class="sp-val">{{ stats.total }}</span><span class="sp-lbl">{{ locale === 'lv' ? 'Kopā' : 'Total' }}</span></div>
                <div class="stat-pill stat-green"><span class="sp-val">{{ stats.active }}</span><span class="sp-lbl">{{ locale === 'lv' ? 'Aktīvie' : 'Active' }}</span></div>
                <div class="stat-pill stat-gray"><span class="sp-val">{{ stats.inactive }}</span><span class="sp-lbl">{{ locale === 'lv' ? 'Neaktīvie' : 'Inactive' }}</span></div>
                <div class="stat-pill stat-orange"><span class="sp-val">{{ stats.total_active_deliveries }}</span><span class="sp-lbl">{{ locale === 'lv' ? 'Aktīvas piegādes' : 'Active Deliveries' }}</span></div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input v-model="search" @keyup.enter="applyFilters" type="text" :placeholder="locale === 'lv' ? 'Meklēt...' : 'Search...'" class="search-input" />
                <select v-model="status" @change="applyFilters" class="filter-select">
                    <option value="">{{ locale === 'lv' ? 'Visi' : 'All' }}</option>
                    <option value="active">{{ locale === 'lv' ? 'Aktīvie' : 'Active' }}</option>
                    <option value="inactive">{{ locale === 'lv' ? 'Neaktīvie' : 'Inactive' }}</option>
                </select>
            </div>

            <!-- Couriers table -->
            <div class="table-card">
                <div v-if="couriers.data.length === 0" class="empty-state">
                    <i class="fas fa-truck"></i>
                    <p>{{ locale === 'lv' ? 'Nav kurjeru' : 'No couriers found' }}</p>
                    <button @click="showAddModal = true" class="btn btn-primary">{{ locale === 'lv' ? 'Pievienot pirmo kurjeru' : 'Add First Courier' }}</button>
                </div>

                <table v-else class="data-table">
                    <thead>
                    <tr>
                        <th>{{ locale === 'lv' ? 'Kurjers' : 'Courier' }}</th>
                        <th>{{ locale === 'lv' ? 'Kontakti' : 'Contact' }}</th>
                        <th>{{ locale === 'lv' ? 'Rajons' : 'Area' }}</th>
                        <th>{{ locale === 'lv' ? 'Transportlīdzeklis' : 'Vehicle' }}</th>
                        <th>{{ locale === 'lv' ? 'Aktīvie' : 'Active' }}</th>
                        <th>{{ locale === 'lv' ? 'Pabeigti' : 'Done' }}</th>
                        <th>{{ locale === 'lv' ? 'Statuss' : 'Status' }}</th>
                        <th>{{ locale === 'lv' ? 'Darbības' : 'Actions' }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="c in couriers.data" :key="c.id">
                        <td>
                            <div class="courier-cell">
                                <img :src="c.user?.profile_picture || '/img/default-avatar.png'" class="avatar" :alt="c.full_name" />
                                <div>
                                    <div class="name">{{ c.full_name }}</div>
                                    <div class="sub">{{ c.user?.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><a :href="`tel:${c.phone}`" class="phone-link">{{ c.phone }}</a></td>
                        <td>{{ c.delivery_area || '—' }}</td>
                        <td>{{ c.vehicle_type || '—' }}</td>
                        <td>
                                <span :class="['count-badge', c.active_assignments_count > 0 ? 'active' : '']">
                                    {{ c.active_assignments_count }}
                                </span>
                        </td>
                        <td><span class="count-badge done">{{ c.completed_assignments_count }}</span></td>
                        <td>
                                <span :class="['status-pill', c.is_active ? 'active' : 'inactive']">
                                    {{ c.is_active ? (locale === 'lv' ? 'Aktīvs' : 'Active') : (locale === 'lv' ? 'Neaktīvs' : 'Inactive') }}
                                </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <Link :href="`/admin/couriers/${c.id}`" class="icon-btn" title="Skatīt"><i class="fas fa-eye"></i></Link>
                                <button @click="openEditModal(c)" class="icon-btn" title="Rediģēt"><i class="fas fa-edit"></i></button>
                                <button @click="openAssignModal(c.id)" class="icon-btn" title="Piešķirt pasūtījumu"><i class="fas fa-link"></i></button>
                                <button @click="toggleCourier(c.id)" class="icon-btn" :title="c.is_active ? 'Deaktivizēt' : 'Aktivizēt'">
                                    <i :class="c.is_active ? 'fas fa-pause-circle' : 'fas fa-play-circle'"></i>
                                </button>
                                <button @click="confirmRemove = c.id" class="icon-btn danger" title="Noņemt"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─── ADD COURIER MODAL ─── -->
        <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
            <div class="modal">
                <div class="modal-header">
                    <h3><i class="fas fa-user-plus"></i> {{ locale === 'lv' ? 'Pievienot kurjeru' : 'Add Courier' }}</h3>
                    <button @click="showAddModal = false" class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Lietotājs *' : 'User *' }}</label>
                        <select v-model="newCourier.user_id" class="form-select">
                            <option :value="null">{{ locale === 'lv' ? 'Izvēlēties lietotāju...' : 'Select user...' }}</option>
                            <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                                {{ u.username }} ({{ u.email }})
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Pilns vārds *' : 'Full Name *' }}</label>
                        <input v-model="newCourier.full_name" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Vārds Uzvārds' : 'First Last'" />
                    </div>
                    <div class="form-grid2">
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Tālrunis *' : 'Phone *' }}</label>
                            <input v-model="newCourier.phone" type="text" class="form-input" placeholder="+371 2000 0000" />
                        </div>
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Transportlīdzeklis' : 'Vehicle' }}</label>
                            <input v-model="newCourier.vehicle_type" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Auto, Velosipēds...' : 'Car, Bicycle...'" />
                        </div>
                    </div>
                    <div class="form-grid2">
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Piegādes rajons' : 'Delivery Area' }}</label>
                            <input v-model="newCourier.delivery_area" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Rīga, Zemgale...' : 'Riga, Central...'" />
                        </div>
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Nodarbinātības datums' : 'Hire Date' }}</label>
                            <input v-model="newCourier.hired_at" type="date" class="form-input" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showAddModal = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="submitAddCourier" :disabled="isSubmitting || !newCourier.user_id || !newCourier.full_name" class="btn btn-primary">
                        <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-user-plus"></i>
                        {{ locale === 'lv' ? 'Pievienot' : 'Add' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── ASSIGN ORDER MODAL ─── -->
        <div v-if="showAssignModal" class="modal-overlay" @click.self="showAssignModal = false">
            <div class="modal">
                <div class="modal-header">
                    <h3><i class="fas fa-link"></i> {{ locale === 'lv' ? 'Piešķirt pasūtījumu kurjeram' : 'Assign Order to Courier' }}</h3>
                    <button @click="showAssignModal = false" class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Kurjers *' : 'Courier *' }}</label>
                        <select v-model="assignData.courier_id" class="form-select">
                            <option :value="null">{{ locale === 'lv' ? 'Izvēlēties kurjeru...' : 'Select courier...' }}</option>
                            <option v-for="c in couriers.data.filter(x => x.is_active)" :key="c.id" :value="c.id">
                                {{ c.full_name }} — {{ c.delivery_area || '—' }} ({{ c.active_assignments_count }} {{ locale === 'lv' ? 'aktīvo' : 'active' }})
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Pasūtījums *' : 'Order *' }}</label>
                        <select v-model="assignData.order_id" class="form-select">
                            <option :value="null">{{ locale === 'lv' ? 'Izvēlēties pasūtījumu...' : 'Select order...' }}</option>
                            <option v-for="o in assignableOrders" :key="o.id" :value="o.id">
                                {{ o.order_number }} — {{ o.customer_name }}, {{ o.delivery_city }} ({{ o.status }})
                            </option>
                        </select>
                        <p v-if="assignableOrders.length === 0" class="form-hint warning">
                            <i class="fas fa-info-circle"></i>
                            {{ locale === 'lv' ? 'Nav nepiešķirtu pasūtījumu ar statusu "iepakots" vai "nosūtīts".' : 'No unassigned orders with packed/shipped status.' }}
                        </p>
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Piezīmes (neobligāti)' : 'Notes (optional)' }}</label>
                        <textarea v-model="assignData.notes" class="form-textarea" rows="3" :placeholder="locale === 'lv' ? 'Norādījumi kurjeram...' : 'Instructions for courier...'"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showAssignModal = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="submitAssign" :disabled="assigning || !assignData.courier_id || !assignData.order_id" class="btn btn-primary">
                        <i v-if="assigning" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-link"></i>
                        {{ locale === 'lv' ? 'Piešķirt' : 'Assign' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── CONFIRM REMOVE MODAL ─── -->
        <div v-if="confirmRemove" class="modal-overlay" @click.self="confirmRemove = null">
            <div class="modal modal-sm">
                <div class="modal-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> {{ locale === 'lv' ? 'Noņemt kurjeru?' : 'Remove Courier?' }}</h3>
                </div>
                <div class="modal-body">
                    <p>{{ locale === 'lv' ? 'Kurjera konts tiks nopazemināts uz parasta lietotāja tiesībām. Visu piešķīrumu vēsture saglabāsies.' : 'The user account will be demoted to regular user. Assignment history will be preserved.' }}</p>
                </div>
                <div class="modal-footer">
                    <button @click="confirmRemove = null" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="removeConfirmed" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        {{ locale === 'lv' ? 'Noņemt' : 'Remove' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── EDIT COURIER MODAL ─── -->
        <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
            <div class="modal">
                <div class="modal-header">
                    <h3><i class="fas fa-edit"></i> {{ locale === 'lv' ? 'Rediģēt kurjeru' : 'Edit Courier' }}</h3>
                    <button @click="showEditModal = false" class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Lietotājvārds' : 'Username' }}</label>
                        <input v-model="editCourier.username" type="text" class="form-input" placeholder="username" />
                    </div>
                    <div class="form-row">
                        <label class="form-label">{{ locale === 'lv' ? 'Pilns vārds *' : 'Full Name *' }}</label>
                        <input v-model="editCourier.full_name" type="text" class="form-input" />
                    </div>
                    <div class="form-grid2">
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Tālrunis *' : 'Phone *' }}</label>
                            <input v-model="editCourier.phone" type="text" class="form-input" placeholder="+371 2000 0000" />
                        </div>
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Transportlīdzeklis' : 'Vehicle' }}</label>
                            <input v-model="editCourier.vehicle_type" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Auto, Velosipēds...' : 'Car, Bicycle...'" />
                        </div>
                    </div>
                    <div class="form-grid2">
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Piegādes rajons' : 'Delivery Area' }}</label>
                            <input v-model="editCourier.delivery_area" type="text" class="form-input" :placeholder="locale === 'lv' ? 'Rīgas centrs...' : 'City center...'" />
                        </div>
                        <div class="form-row">
                            <label class="form-label">{{ locale === 'lv' ? 'Pieņemšanas datums' : 'Hire Date' }}</label>
                            <input v-model="editCourier.hired_at" type="date" class="form-input" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showEditModal = false" class="btn btn-outline">{{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}</button>
                    <button @click="submitEditCourier" :disabled="isEditing || !editCourier.full_name || !editCourier.phone" class="btn btn-primary">
                        <i v-if="isEditing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ locale === 'lv' ? 'Saglabāt' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-couriers { padding: 24px; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
.page-title { font-size: 24px; font-weight: 700; color: #1f2937; margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px; }
.page-title i { color: #dc2626; }
.page-subtitle { font-size: 14px; color: #6b7280; margin: 0; }
.header-actions { display: flex; gap: 10px; }

/* Stats */
.stats-row { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.stat-pill { display: flex; align-items: center; gap: 10px; background: white; border-radius: 8px; padding: 10px 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.06); }
.stat-pill.stat-green { border-color: #6ee7b7; }
.stat-pill.stat-gray { border-color: #d1d5db; }
.stat-pill.stat-orange { border-color: #fcd34d; }
.sp-val { font-size: 20px; font-weight: 700; color: #1f2937; }
.sp-lbl { font-size: 12px; color: #6b7280; }

/* Filters */
.filters-bar { display: flex; gap: 10px; margin-bottom: 16px; }
.search-input { flex: 1; padding: 9px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
.search-input:focus { outline: none; border-color: #dc2626; }
.filter-select { padding: 9px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer; }

/* Table */
.table-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #f9fafb; padding: 11px 14px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.data-table td { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #fafafa; }

.courier-cell { display: flex; align-items: center; gap: 10px; }
.avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; }
.name { font-weight: 600; color: #111827; }
.sub { font-size: 11px; color: #9ca3af; }
.phone-link { color: #dc2626; text-decoration: none; font-weight: 500; }
.phone-link:hover { text-decoration: underline; }

.count-badge { display: inline-block; background: #f3f4f6; color: #374151; font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
.count-badge.active { background: #fef3c7; color: #92400e; }
.count-badge.done { background: #d1fae5; color: #065f46; }

.status-pill { padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; }
.status-pill.active { background: #d1fae5; color: #065f46; }
.status-pill.inactive { background: #f3f4f6; color: #6b7280; }

.action-btns { display: flex; gap: 6px; }
.icon-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid #e5e7eb; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; color: #6b7280; text-decoration: none; transition: all 0.15s; }
.icon-btn:hover { background: #f9fafb; color: #1f2937; border-color: #d1d5db; }
.icon-btn.danger:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

.empty-state { text-align: center; padding: 60px; color: #9ca3af; }
.empty-state i { font-size: 40px; display: block; margin-bottom: 12px; }
.empty-state p { margin: 0 0 16px 0; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; }
.btn-primary { background: #dc2626; color: white; }
.btn-primary:hover:not(:disabled) { background: #b91c1c; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f3f4f6; }
.btn-danger { background: #dc2626; color: white; }
.btn-danger:hover { background: #b91c1c; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal { background: white; border-radius: 12px; width: 100%; max-width: 560px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.modal-sm { max-width: 400px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e5e7eb; }
.modal-header h3 { margin: 0; font-size: 16px; font-weight: 600; color: #1f2937; display: flex; align-items: center; gap: 8px; }
.modal-header h3 i { color: #dc2626; }
.close-btn { background: none; border: none; cursor: pointer; font-size: 18px; color: #9ca3af; padding: 4px; }
.close-btn:hover { color: #374151; }
.modal-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px; }

.form-label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
.form-input, .form-select, .form-textarea { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 7px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 2px rgba(220,38,38,0.1); }
.form-textarea { resize: vertical; }
.form-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-row { display: flex; flex-direction: column; }
.form-hint { font-size: 12px; color: #6b7280; margin: 6px 0 0 0; }
.form-hint.warning { color: #d97706; }

/* ─── RESPONSIVE ─── */
@media (max-width: 1024px) {
    .data-table th:nth-child(4),
    .data-table td:nth-child(4) { display: none; } /* Transportlīdzeklis */
}

@media (max-width: 768px) {
    .admin-couriers { padding: 16px; }
    .page-header { flex-direction: column; gap: 12px; }
    .header-actions { width: 100%; }
    .header-actions .btn { flex: 1; justify-content: center; }

    .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .stat-pill { padding: 8px 12px; }

    /* Hide less important table columns on mobile */
    .data-table th:nth-child(3),
    .data-table td:nth-child(3),
    .data-table th:nth-child(5),
    .data-table td:nth-child(5),
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { display: none; } /* Rajons, Aktīvie, Pabeigti */

    .form-grid2 { grid-template-columns: 1fr; }
    .modal { margin: 0 8px; }
    .modal-body { padding: 16px; }
    .modal-footer { padding: 12px 16px; flex-wrap: wrap; }
    .modal-footer .btn { flex: 1; justify-content: center; }
}

@media (max-width: 480px) {
    /* On very small screens switch to card layout */
    .table-card { overflow: visible; }
    .data-table, .data-table thead, .data-table tbody,
    .data-table th, .data-table td, .data-table tr { display: block; }
    .data-table thead { display: none; }
    .data-table tr { border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px; padding: 12px; background: white; }
    .data-table td { border: none; padding: 4px 0; font-size: 13px; display: flex; align-items: center; gap: 8px; }
    .data-table td:nth-child(3),
    .data-table td:nth-child(4),
    .data-table td:nth-child(5),
    .data-table td:nth-child(6) { display: flex; }
    .action-btns { flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .icon-btn { width: 36px; height: 36px; font-size: 15px; }
}
</style>
