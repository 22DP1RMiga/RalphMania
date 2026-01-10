<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    messages: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Filters
const search = ref(props.filters.search || '');
const readFilter = ref(props.filters.is_read || '');
const repliedFilter = ref(props.filters.is_replied || '');

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get('/admin/contacts', {
        search: search.value || undefined,
        is_read: readFilter.value || undefined,
        is_replied: repliedFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    readFilter.value = '';
    repliedFilter.value = '';
    router.get('/admin/contacts');
};

// Mark as read
const markAsRead = (id) => {
    router.put(`/admin/contacts/${id}/read`, {}, {
        preserveScroll: true,
    });
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('lv-LV', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Truncate text
const truncate = (text, length = 100) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<template>
    <Head title="Kontaktu ziņojumi - Admin" />

    <AdminLayout>
        <template #title>Kontaktu ziņojumi</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">Pārvaldiet saņemtos kontaktu ziņojumus</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-mini unread">
                <i class="fas fa-envelope"></i>
                <span class="stat-count">{{ messages.data?.filter(m => !m.is_read).length || 0 }}</span>
                <span class="stat-label">Neizlasīti</span>
            </div>
            <div class="stat-mini read">
                <i class="fas fa-envelope-open"></i>
                <span class="stat-count">{{ messages.data?.filter(m => m.is_read).length || 0 }}</span>
                <span class="stat-label">Izlasīti</span>
            </div>
            <div class="stat-mini replied">
                <i class="fas fa-reply"></i>
                <span class="stat-count">{{ messages.data?.filter(m => m.is_replied).length || 0 }}</span>
                <span class="stat-label">Atbildēti</span>
            </div>
            <div class="stat-mini total">
                <i class="fas fa-inbox"></i>
                <span class="stat-count">{{ messages.data?.length || 0 }}</span>
                <span class="stat-label">Kopā</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Meklēt pēc vārda, e-pasta, tēmas..."
                        class="search-input"
                    >
                </div>

                <select v-model="readFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi</option>
                    <option value="0">Neizlasīti</option>
                    <option value="1">Izlasīti</option>
                </select>

                <select v-model="repliedFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi</option>
                    <option value="0">Nav atbildēts</option>
                    <option value="1">Atbildēts</option>
                </select>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Notīrīt
                </button>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="table-card">
            <table class="data-table">
                <thead>
                <tr>
                    <th width="30"></th>
                    <th>Sūtītājs</th>
                    <th>Tēma</th>
                    <th>Ziņojums</th>
                    <th>Datums</th>
                    <th>Statuss</th>
                    <th>Darbības</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="message in messages.data"
                    :key="message.id"
                    :class="{ unread: !message.is_read }"
                >
                    <td>
                        <span v-if="!message.is_read" class="unread-dot"></span>
                    </td>
                    <td>
                        <div class="sender-info">
                            <span class="sender-name">{{ message.name }}</span>
                            <span class="sender-email">{{ message.email }}</span>
                            <span class="sender-phone">{{ message.country_code }} {{ message.phone }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="subject">{{ message.subject }}</span>
                    </td>
                    <td>
                        <span class="message-preview">{{ truncate(message.message, 80) }}</span>
                    </td>
                    <td>
                        <span class="date">{{ formatDate(message.created_at) }}</span>
                    </td>
                    <td>
                        <div class="status-badges">
                                <span :class="['mini-badge', message.is_read ? 'read' : 'unread']">
                                    {{ message.is_read ? 'Izlasīts' : 'Jauns' }}
                                </span>
                            <span v-if="message.is_replied" class="mini-badge replied">
                                    Atbildēts
                                </span>
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <Link :href="`/admin/contacts/${message.id}`" class="btn-icon btn-icon-view" title="Skatīt">
                                <i class="fas fa-eye"></i>
                            </Link>
                            <button
                                v-if="!message.is_read"
                                @click="markAsRead(message.id)"
                                class="btn-icon btn-icon-read"
                                title="Atzīmēt kā izlasītu"
                            >
                                <i class="fas fa-check"></i>
                            </button>
                            <a
                                :href="`mailto:${message.email}?subject=Re: ${message.subject}`"
                                class="btn-icon btn-icon-reply"
                                title="Atbildēt"
                            >
                                <i class="fas fa-reply"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <tr v-if="messages.data?.length === 0">
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Nav atrasts neviens ziņojums</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="messages.links && messages.links.length > 3" class="pagination">
                <Link
                    v-for="link in messages.links"
                    :key="link.label"
                    :href="link.url"
                    :class="['page-link', { active: link.active, disabled: !link.url }]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.header-subtitle {
    color: #6b7280;
    margin: 0;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

.stat-mini {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-mini i {
    font-size: 1.5rem;
}

.stat-mini.unread i { color: #dc2626; }
.stat-mini.read i { color: #6b7280; }
.stat-mini.replied i { color: #059669; }
.stat-mini.total i { color: #6366f1; }

.stat-count {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
}

/* Filters */
.filters-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.95rem;
}

.search-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.filter-select {
    padding: 0.625rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
}

/* Table */
.table-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.data-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.data-table tr:hover {
    background: #f9fafb;
}

.data-table tr.unread {
    background: #fef2f2;
}

.data-table tr.unread:hover {
    background: #fee2e2;
}

/* Unread Dot */
.unread-dot {
    display: inline-block;
    width: 0.5rem;
    height: 0.5rem;
    background: #dc2626;
    border-radius: 50%;
}

/* Sender Info */
.sender-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.sender-name {
    font-weight: 600;
    color: #111827;
}

.sender-email {
    font-size: 0.75rem;
    color: #2563eb;
}

.sender-phone {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Subject & Message */
.subject {
    font-weight: 500;
    color: #111827;
}

.message-preview {
    font-size: 0.875rem;
    color: #6b7280;
}

.date {
    font-size: 0.875rem;
    color: #6b7280;
    white-space: nowrap;
}

/* Status Badges */
.status-badges {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.mini-badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
}

.mini-badge.unread {
    background: #fee2e2;
    color: #dc2626;
}

.mini-badge.read {
    background: #f3f4f6;
    color: #6b7280;
}

.mini-badge.replied {
    background: #d1fae5;
    color: #065f46;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-icon-view {
    background: #dbeafe;
    color: #2563eb;
}

.btn-icon-view:hover {
    background: #2563eb;
    color: white;
}

.btn-icon-read {
    background: #d1fae5;
    color: #059669;
}

.btn-icon-read:hover {
    background: #059669;
    color: white;
}

.btn-icon-reply {
    background: #fef3c7;
    color: #d97706;
}

.btn-icon-reply:hover {
    background: #d97706;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem !important;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.page-link:hover:not(.disabled):not(.active) {
    background: #f3f4f6;
}

.page-link.active {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}

.page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Responsive */
@media (max-width: 1024px) {
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: 100%;
    }

    .data-table {
        display: block;
        overflow-x: auto;
    }
}
</style>
