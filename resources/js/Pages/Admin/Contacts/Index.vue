<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    messages: Object,
    filters: Object,
    stats: Object,
});

// Local filter state
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

// Processing states
const processingId = ref(null);

// Reply modal state
const showReplyModal = ref(false);
const selectedMessage = ref(null);
const replyText = ref('');
const isSubmitting = ref(false);

// Debounce helper
let searchTimeout = null;
const debounceSearch = (fn, delay = 300) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fn, delay);
};

// Apply filters
const applyFilters = () => {
    router.get('/admin/contacts', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Watch for filter changes
watch(statusFilter, applyFilters);
watch(search, () => debounceSearch(applyFilters));

// Clear all filters
const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
};

// Has active filters
const hasFilters = computed(() => {
    return search.value || statusFilter.value;
});

// Mark as read
const markAsRead = (id) => {
    processingId.value = id;
    router.put(`/admin/contacts/${id}/read`, {}, {
        preserveScroll: true,
        onFinish: () => processingId.value = null,
    });
};

// Delete message
const deleteMessage = (id) => {
    if (confirm(t('admin.contacts.confirmDelete'))) {
        processingId.value = id;
        router.delete(`/admin/contacts/${id}`, {
            preserveScroll: true,
            onFinish: () => processingId.value = null,
        });
    }
};

// Open reply modal
const openReplyModal = (message) => {
    selectedMessage.value = message;
    replyText.value = '';
    showReplyModal.value = true;
};

// Close reply modal
const closeReplyModal = () => {
    showReplyModal.value = false;
    selectedMessage.value = null;
    replyText.value = '';
};

// Submit reply
const submitReply = () => {
    if (!replyText.value.trim() || replyText.value.length < 10) {
        alert(t('admin.contacts.replyMinLength'));
        return;
    }

    isSubmitting.value = true;
    router.put(`/admin/contacts/${selectedMessage.value.id}/reply`, {
        reply_text: replyText.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeReplyModal();
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

// Format date
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Get user avatar
const getUserAvatar = (user) => {
    if (!user?.profile_picture) return null;
    if (user.profile_picture.startsWith('http')) return user.profile_picture;
    return `/storage/${user.profile_picture}`;
};

// Truncate text
const truncateText = (text, length = 100) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};

// Get status info
const getStatusInfo = (message) => {
    if (message.is_replied) {
        return { class: 'replied', icon: 'fas fa-check-double', label: t('admin.contacts.status.replied') };
    }
    if (message.is_read) {
        return { class: 'read', icon: 'fas fa-envelope-open', label: t('admin.contacts.status.read') };
    }
    return { class: 'unread', icon: 'fas fa-envelope', label: t('admin.contacts.status.unread') };
};
</script>

<template>
    <Head :title="t('admin.contacts.index.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.contacts.index.title') }}</template>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.total || 0 }}</span>
                    <span class="stat-label">{{ t('admin.contacts.stats.total') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon unread">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.unread || 0 }}</span>
                    <span class="stat-label">{{ t('admin.contacts.stats.unread') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon read">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.read || 0 }}</span>
                    <span class="stat-label">{{ t('admin.contacts.stats.read') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon replied">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.replied || 0 }}</span>
                    <span class="stat-label">{{ t('admin.contacts.stats.replied') }}</span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <!-- Search -->
                <div class="filter-group search-group">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input
                            v-model="search"
                            type="text"
                            class="search-input"
                            :placeholder="t('admin.contacts.searchPlaceholder')"
                        >
                        <button v-if="search" @click="search = ''" class="clear-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <select v-model="statusFilter" class="filter-select">
                        <option value="">{{ t('admin.contacts.allStatuses') }}</option>
                        <option value="unread">{{ t('admin.contacts.status.unread') }}</option>
                        <option value="read">{{ t('admin.contacts.status.read') }}</option>
                        <option value="replied">{{ t('admin.contacts.status.replied') }}</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <button v-if="hasFilters" @click="clearFilters" class="btn btn-clear">
                    <i class="fas fa-times"></i>
                    {{ t('admin.common.clearFilters') }}
                </button>
            </div>
        </div>

        <!-- Messages List -->
        <div class="messages-container">
            <!-- Empty State -->
            <div v-if="messages.data.length === 0" class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>{{ t('admin.contacts.noMessages') }}</h3>
                <p>{{ t('admin.contacts.noMessagesDesc') }}</p>
            </div>

            <!-- Messages List -->
            <div v-else class="messages-list">
                <div
                    v-for="message in messages.data"
                    :key="message.id"
                    class="message-card"
                    :class="{
                        'unread': !message.is_read,
                        'replied': message.is_replied
                    }"
                >
                    <!-- Status Badge -->
                    <div class="message-status-badge" :class="getStatusInfo(message).class">
                        <i :class="getStatusInfo(message).icon"></i>
                        {{ getStatusInfo(message).label }}
                    </div>

                    <!-- Header -->
                    <div class="message-header">
                        <div class="sender-info">
                            <div class="sender-avatar">
                                <img
                                    v-if="getUserAvatar(message.user)"
                                    :src="getUserAvatar(message.user)"
                                    :alt="message.name"
                                >
                                <i v-else class="fas fa-user"></i>
                            </div>
                            <div class="sender-details">
                                <span class="sender-name">{{ message.name }}</span>
                                <span class="sender-email">{{ message.email }}</span>
                                <span v-if="message.user" class="sender-username">
                                    <i class="fas fa-user-check"></i> @{{ message.user.username }}
                                </span>
                            </div>
                        </div>
                        <div class="message-meta">
                            <span class="message-date">{{ formatDate(message.created_at) }}</span>
                            <span v-if="message.full_phone" class="message-phone">
                                <i class="fas fa-phone"></i> {{ message.full_phone }}
                            </span>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="message-subject">
                        <strong>{{ message.subject }}</strong>
                    </div>

                    <!-- Preview -->
                    <div class="message-preview">
                        <p>{{ truncateText(message.message, 150) }}</p>
                    </div>

                    <!-- Replied indicator -->
                    <div v-if="message.is_replied" class="replied-indicator">
                        <i class="fas fa-check-double"></i>
                        {{ t('admin.contacts.repliedAt') }}: {{ formatDate(message.replied_at) }}
                    </div>

                    <!-- Actions -->
                    <div class="message-actions">
                        <Link
                            :href="`/admin/contacts/${message.id}`"
                            class="btn btn-view"
                        >
                            <i class="fas fa-eye"></i>
                            {{ t('admin.common.view') }}
                        </Link>

                        <button
                            v-if="!message.is_replied"
                            @click="openReplyModal(message)"
                            class="btn btn-reply"
                        >
                            <i class="fas fa-reply"></i>
                            {{ t('admin.contacts.reply') }}
                        </button>

                        <button
                            v-if="!message.is_read"
                            @click="markAsRead(message.id)"
                            class="btn btn-mark-read"
                            :disabled="processingId === message.id"
                        >
                            <i :class="processingId === message.id ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
                            {{ t('admin.contacts.markAsRead') }}
                        </button>

                        <button
                            @click="deleteMessage(message.id)"
                            class="btn btn-delete"
                            :disabled="processingId === message.id"
                        >
                            <i :class="processingId === message.id ? 'fas fa-spinner fa-spin' : 'fas fa-trash'"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="messages.links && messages.links.length > 3" class="pagination-wrapper">
                <div class="pagination-info">
                    {{ t('admin.common.showing') }} {{ messages.from }}-{{ messages.to }} {{ t('admin.common.of') }} {{ messages.total }}
                </div>
                <div class="pagination">
                    <template v-for="link in messages.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="pagination-link"
                            :class="{ active: link.active }"
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span v-else class="pagination-link disabled" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Reply Modal -->
        <Teleport to="body">
            <div v-if="showReplyModal" class="modal-overlay" @click.self="closeReplyModal">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3>
                            <i class="fas fa-reply"></i>
                            {{ t('admin.contacts.replyTo') }}: {{ selectedMessage?.name }}
                        </h3>
                        <button @click="closeReplyModal" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Original Message Info -->
                        <div class="original-message">
                            <div class="original-header">
                                <span class="original-label">{{ t('admin.contacts.originalMessage') }}</span>
                                <span class="original-date">{{ formatDate(selectedMessage?.created_at) }}</span>
                            </div>
                            <div class="original-subject">
                                <strong>{{ selectedMessage?.subject }}</strong>
                            </div>
                            <div class="original-text">
                                {{ selectedMessage?.message }}
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div class="reply-form">
                            <label class="reply-label">
                                {{ t('admin.contacts.yourReply') }}
                                <span class="reply-to-email">→ {{ selectedMessage?.email }}</span>
                            </label>
                            <textarea
                                v-model="replyText"
                                class="reply-textarea"
                                rows="8"
                                :placeholder="t('admin.contacts.replyPlaceholder')"
                            ></textarea>
                            <div class="reply-hint">
                                {{ t('admin.contacts.replyHint') }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeReplyModal" class="btn btn-cancel">
                            {{ t('admin.common.cancel') }}
                        </button>
                        <button
                            @click="submitReply"
                            class="btn btn-submit"
                            :disabled="isSubmitting || replyText.length < 10"
                        >
                            <i :class="isSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                            {{ isSubmitting ? t('admin.contacts.sending') : t('admin.contacts.sendReply') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-icon.total {
    background: linear-gradient(135deg, #dbeafe, #60a5fa);
    color: #1e40af;
}

.stat-icon.unread {
    background: linear-gradient(135deg, #fee2e2, #f87171);
    color: #991b1b;
}

.stat-icon.read {
    background: linear-gradient(135deg, #fef3c7, #fbbf24);
    color: #92400e;
}

.stat-icon.replied {
    background: linear-gradient(135deg, #d1fae5, #34d399);
    color: #065f46;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Filters */
.filters-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
}

.filter-group {
    flex-shrink: 0;
}

.search-group {
    flex: 1;
    min-width: 250px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input-wrapper > i {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 0.625rem 2.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.875rem;
}

.search-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.clear-search {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
}

.clear-search:hover {
    color: #dc2626;
}

.filter-select {
    padding: 0.625rem 2rem 0.625rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
    min-width: 150px;
}

.filter-select:focus {
    outline: none;
    border-color: #dc2626;
}

.btn-clear {
    padding: 0.625rem 1rem;
    background: #f3f4f6;
    border: none;
    border-radius: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-clear:hover {
    background: #e5e7eb;
    color: #374151;
}

/* Messages Container */
.messages-container {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
}

/* Messages List */
.messages-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Message Card */
.message-card {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.25rem;
    position: relative;
    border-left: 4px solid #e5e7eb;
    transition: all 0.2s;
}

.message-card.unread {
    border-left-color: #dc2626;
    background: #fef2f2;
}

.message-card.replied {
    border-left-color: #10b981;
    background: #f0fdf4;
}

.message-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Status Badge */
.message-status-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.message-status-badge.unread {
    background: #fee2e2;
    color: #991b1b;
}

.message-status-badge.read {
    background: #fef3c7;
    color: #92400e;
}

.message-status-badge.replied {
    background: #d1fae5;
    color: #065f46;
}

/* Message Header */
.message-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
    padding-right: 6rem;
}

.sender-info {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.sender-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.sender-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sender-avatar i {
    color: #9ca3af;
    font-size: 1.25rem;
}

.sender-details {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.sender-name {
    font-weight: 600;
    color: #111827;
    font-size: 1rem;
}

.sender-email {
    font-size: 0.8rem;
    color: #6b7280;
}

.sender-username {
    font-size: 0.7rem;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.message-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.message-date {
    font-size: 0.75rem;
    color: #6b7280;
}

.message-phone {
    font-size: 0.7rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Subject */
.message-subject {
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
    color: #111827;
}

/* Preview */
.message-preview {
    margin-bottom: 0.75rem;
}

.message-preview p {
    font-size: 0.85rem;
    color: #4b5563;
    line-height: 1.5;
    margin: 0;
}

/* Replied Indicator */
.replied-indicator {
    font-size: 0.75rem;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    background: #ecfdf5;
    border-radius: 0.375rem;
}

/* Actions */
.message-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 0.875rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-view {
    background: #dbeafe;
    color: #1e40af;
}

.btn-view:hover {
    background: #bfdbfe;
}

.btn-reply {
    background: #d1fae5;
    color: #065f46;
}

.btn-reply:hover {
    background: #a7f3d0;
}

.btn-mark-read {
    background: #fef3c7;
    color: #92400e;
}

.btn-mark-read:hover:not(:disabled) {
    background: #fde68a;
}

.btn-delete {
    background: #fee2e2;
    color: #991b1b;
    padding: 0.5rem 0.625rem;
}

.btn-delete:hover:not(:disabled) {
    background: #fecaca;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.pagination-info {
    font-size: 0.875rem;
    color: #6b7280;
}

.pagination {
    display: flex;
    gap: 0.25rem;
}

.pagination-link {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination-link:hover:not(.disabled):not(.active) {
    background: #f3f4f6;
}

.pagination-link.active {
    background: #dc2626;
    color: white;
}

.pagination-link.disabled {
    color: #d1d5db;
    cursor: not-allowed;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 1rem;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.modal-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.modal-header h3 i {
    color: #dc2626;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
}

.modal-close:hover {
    color: #dc2626;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

/* Original Message */
.original-message {
    background: #f3f4f6;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-left: 3px solid #9ca3af;
}

.original-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.original-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    font-weight: 600;
}

.original-date {
    font-size: 0.7rem;
    color: #9ca3af;
}

.original-subject {
    font-size: 0.9rem;
    color: #111827;
    margin-bottom: 0.5rem;
}

.original-text {
    font-size: 0.85rem;
    color: #4b5563;
    line-height: 1.5;
    white-space: pre-wrap;
}

/* Reply Form */
.reply-form {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.reply-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reply-to-email {
    font-weight: 400;
    color: #6b7280;
    font-size: 0.8rem;
}

.reply-textarea {
    width: 100%;
    padding: 0.875rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    resize: vertical;
    min-height: 150px;
    font-family: inherit;
}

.reply-textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.reply-hint {
    font-size: 0.7rem;
    color: #9ca3af;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-submit {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
}

.btn-submit:hover:not(:disabled) {
    background: linear-gradient(135deg, #b91c1c, #991b1b);
}

.btn-submit:disabled {
    background: #d1d5db;
    cursor: not-allowed;
}

/* ========================================
   RESPONSIVE STYLES
   ======================================== */

@media (max-width: 1024px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .search-group {
        min-width: 200px;
    }
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1rem;
    }

    .stat-value {
        font-size: 1.25rem;
    }

    .filters-card {
        padding: 1rem;
    }

    .filters-row {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }

    .filter-group {
        width: 100%;
    }

    .search-group {
        min-width: unset;
    }

    .filter-select {
        width: 100%;
    }

    .btn-clear {
        width: 100%;
        justify-content: center;
    }

    .messages-container {
        padding: 1rem;
    }

    .message-card {
        padding: 1rem;
    }

    .message-status-badge {
        position: relative;
        top: unset;
        right: unset;
        align-self: flex-start;
        margin-bottom: 0.75rem;
    }

    .message-header {
        flex-direction: column;
        gap: 0.75rem;
        padding-right: 0;
    }

    .message-meta {
        align-items: flex-start;
        flex-direction: row;
        gap: 1rem;
    }

    .message-actions {
        flex-wrap: wrap;
    }

    .btn {
        flex: 1;
        min-width: calc(50% - 0.25rem);
    }

    .btn-delete {
        min-width: auto;
        flex: 0;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Modal */
    .modal-container {
        max-height: 95vh;
        margin: 0.5rem;
    }

    .modal-header {
        padding: 1rem;
    }

    .modal-header h3 {
        font-size: 1rem;
    }

    .modal-body {
        padding: 1rem;
    }

    .modal-footer {
        padding: 1rem;
        flex-direction: column;
    }

    .modal-footer .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .stat-card {
        padding: 0.75rem;
        gap: 0.5rem;
    }

    .stat-icon {
        width: 2rem;
        height: 2rem;
        font-size: 0.875rem;
    }

    .stat-value {
        font-size: 1.1rem;
    }

    .stat-label {
        font-size: 0.6rem;
    }

    .messages-container {
        padding: 0.75rem;
    }

    .message-card {
        padding: 0.875rem;
    }

    .sender-avatar {
        width: 2.5rem;
        height: 2.5rem;
    }

    .sender-name {
        font-size: 0.9rem;
    }

    .sender-email {
        font-size: 0.75rem;
    }

    .message-subject {
        font-size: 0.875rem;
    }

    .message-preview p {
        font-size: 0.8rem;
    }

    .message-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        min-width: unset;
    }

    .pagination-link {
        padding: 0.35rem 0.5rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 360px) {
    .stats-row {
        grid-template-columns: 1fr;
    }

    .search-input {
        font-size: 0.8rem;
    }

    .filter-select {
        font-size: 0.8rem;
    }
}
</style>
