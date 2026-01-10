<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    comments: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Filters
const statusFilter = ref(props.filters.status || '');
const search = ref(props.filters.search || '');

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get('/admin/comments', {
        status: statusFilter.value || undefined,
        search: search.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    statusFilter.value = '';
    search.value = '';
    router.get('/admin/comments');
};

// Approve comment
const approveComment = (id) => {
    router.put(`/admin/comments/${id}/approve`, {}, {
        preserveScroll: true,
    });
};

// Reject (delete) comment
const rejectComment = (id) => {
    if (confirm('Vai tiešām vēlaties noraidīt un dzēst šo komentāru?')) {
        router.put(`/admin/comments/${id}/reject`, {}, {
            preserveScroll: true,
        });
    }
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
</script>

<template>
    <Head title="Komentāri - Admin" />

    <AdminLayout>
        <template #title>Komentāri</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">Moderējiet satura komentārus</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-mini pending">
                <i class="fas fa-clock"></i>
                <span class="stat-count">{{ comments.data?.filter(c => !c.is_approved).length || 0 }}</span>
                <span class="stat-label">Gaida</span>
            </div>
            <div class="stat-mini approved">
                <i class="fas fa-check"></i>
                <span class="stat-count">{{ comments.data?.filter(c => c.is_approved).length || 0 }}</span>
                <span class="stat-label">Apstiprināti</span>
            </div>
            <div class="stat-mini total">
                <i class="fas fa-comments"></i>
                <span class="stat-count">{{ comments.data?.length || 0 }}</span>
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
                        placeholder="Meklēt komentārus..."
                        class="search-input"
                    >
                </div>

                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi statusi</option>
                    <option value="pending">Gaida apstiprinājumu</option>
                    <option value="approved">Apstiprināti</option>
                </select>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Notīrīt
                </button>
            </div>
        </div>

        <!-- Comments List -->
        <div class="comments-list">
            <div v-for="comment in comments.data" :key="comment.id" :class="['comment-card', { pending: !comment.is_approved }]">
                <!-- Comment Header -->
                <div class="comment-header">
                    <div class="comment-user">
                        <img
                            :src="comment.user?.profile_picture ? `/storage/${comment.user.profile_picture}` : '/img/default-avatar.png'"
                            class="user-avatar"
                        >
                        <div class="user-info">
                            <span class="user-name">{{ comment.user?.username || 'Nezināms' }}</span>
                            <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
                        </div>
                    </div>
                    <span :class="['status-badge', comment.is_approved ? 'approved' : 'pending']">
                        <i :class="comment.is_approved ? 'fas fa-check' : 'fas fa-clock'"></i>
                        {{ comment.is_approved ? 'Apstiprināts' : 'Gaida' }}
                    </span>
                </div>

                <!-- Content Reference -->
                <div class="comment-content-ref" v-if="comment.content">
                    <i class="fas fa-newspaper"></i>
                    <Link :href="`/content/${comment.content.slug}`" class="content-link" target="_blank">
                        {{ comment.content.title_lv }}
                    </Link>
                </div>

                <!-- Comment Text -->
                <div class="comment-body">
                    <p class="comment-text">{{ comment.comment_text }}</p>
                </div>

                <!-- Reply indicator -->
                <div v-if="comment.parent_id" class="reply-indicator">
                    <i class="fas fa-reply"></i>
                    Atbilde uz citu komentāru
                </div>

                <!-- Comment Footer -->
                <div class="comment-footer">
                    <div class="comment-actions">
                        <button
                            v-if="!comment.is_approved"
                            @click="approveComment(comment.id)"
                            class="btn btn-success"
                        >
                            <i class="fas fa-check"></i>
                            Apstiprināt
                        </button>
                        <button
                            @click="rejectComment(comment.id)"
                            class="btn btn-danger"
                        >
                            <i class="fas fa-trash"></i>
                            Noraidīt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="comments.data?.length === 0" class="empty-state">
                <i class="fas fa-comments"></i>
                <p>Nav atrasts neviens komentārs</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="comments.links && comments.links.length > 3" class="pagination">
            <Link
                v-for="link in comments.links"
                :key="link.label"
                :href="link.url"
                :class="['page-link', { active: link.active, disabled: !link.url }]"
                v-html="link.label"
            />
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
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 640px) {
    .stats-row {
        grid-template-columns: 1fr;
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

.stat-mini.pending i { color: #d97706; }
.stat-mini.approved i { color: #059669; }
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
    min-width: 200px;
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
    min-width: 180px;
}

/* Comments List */
.comments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.comment-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #10b981;
}

.comment-card.pending {
    border-left-color: #f59e0b;
    background: #fffbeb;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.comment-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #111827;
    font-size: 0.95rem;
}

.comment-date {
    font-size: 0.75rem;
    color: #6b7280;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.approved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

/* Content Reference */
.comment-content-ref {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #f3f4f6;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.comment-content-ref i {
    color: #6b7280;
}

.content-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.content-link:hover {
    text-decoration: underline;
}

/* Comment Body */
.comment-body {
    margin-bottom: 1rem;
}

.comment-text {
    color: #374151;
    line-height: 1.6;
    margin: 0;
}

/* Reply Indicator */
.reply-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.reply-indicator i {
    transform: scaleX(-1);
}

/* Comment Footer */
.comment-footer {
    display: flex;
    justify-content: flex-end;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.comment-actions {
    display: flex;
    gap: 0.5rem;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-danger {
    background: #fee2e2;
    color: #dc2626;
}

.btn-danger:hover {
    background: #dc2626;
    color: white;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 0.75rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state p {
    margin: 0;
    font-size: 1.125rem;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    margin-top: 2rem;
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

@media (max-width: 640px) {
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: 100%;
    }

    .comment-header {
        flex-direction: column;
        gap: 0.75rem;
    }
}
</style>
