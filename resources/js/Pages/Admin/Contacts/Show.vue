<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    message: Object,
});

// Reply state
const showReplyForm = ref(false);
const replyText = ref('');
const isSubmitting = ref(false);

// Format date
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric',
        month: 'long',
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

// Get status info
const getStatusInfo = () => {
    if (props.message.is_replied) {
        return { class: 'replied', icon: 'fas fa-check-double', label: t('admin.contacts.status.replied') };
    }
    if (props.message.is_read) {
        return { class: 'read', icon: 'fas fa-envelope-open', label: t('admin.contacts.status.read') };
    }
    return { class: 'unread', icon: 'fas fa-envelope', label: t('admin.contacts.status.unread') };
};

// Submit reply
const submitReply = () => {
    if (!replyText.value.trim() || replyText.value.length < 10) {
        alert(t('admin.contacts.replyMinLength'));
        return;
    }

    isSubmitting.value = true;
    router.put(`/admin/contacts/${props.message.id}/reply`, {
        reply_text: replyText.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showReplyForm.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

// Delete message
const deleteMessage = () => {
    if (confirm(t('admin.contacts.confirmDelete'))) {
        router.delete(`/admin/contacts/${props.message.id}`);
    }
};

// Copy to clipboard
const copyToClipboard = (text, type) => {
    navigator.clipboard.writeText(text).then(() => {
        alert(t('admin.contacts.copied', { type }));
    });
};
</script>

<template>
    <Head :title="`${t('admin.contacts.message')} #${message.id}`" />

    <AdminLayout>
        <template #title>
            <Link href="/admin/contacts" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </Link>
            {{ t('admin.contacts.message') }} #{{ message.id }}
        </template>

        <div class="show-container">
            <!-- Status Bar -->
            <div class="status-bar" :class="getStatusInfo().class">
                <div class="status-info">
                    <i :class="getStatusInfo().icon"></i>
                    <span>{{ getStatusInfo().label }}</span>
                </div>
                <div class="status-date">
                    {{ t('admin.contacts.received') }}: {{ formatDate(message.created_at) }}
                </div>
            </div>

            <div class="content-grid">
                <!-- Main Content -->
                <div class="main-content">
                    <!-- Message Card -->
                    <div class="message-card">
                        <div class="message-header">
                            <h2>{{ message.subject }}</h2>
                        </div>

                        <div class="message-body">
                            <p>{{ message.message }}</p>
                        </div>

                        <div class="message-footer">
                            <span class="message-date">
                                <i class="fas fa-clock"></i>
                                {{ formatDate(message.created_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Reply Section -->
                    <div v-if="message.is_replied" class="reply-card">
                        <div class="reply-header">
                            <div class="reply-title">
                                <i class="fas fa-reply"></i>
                                {{ t('admin.contacts.yourReplyTitle') }}
                            </div>
                            <div class="reply-meta">
                                <span v-if="message.replied_by">
                                    {{ t('admin.contacts.repliedBy') }}: @{{ message.replied_by.username }}
                                </span>
                                <span>{{ formatDate(message.replied_at) }}</span>
                            </div>
                        </div>
                        <div class="reply-body">
                            <p>{{ message.reply_text }}</p>
                        </div>
                    </div>

                    <!-- Reply Form -->
                    <div v-else class="reply-form-card">
                        <div class="reply-form-header" @click="showReplyForm = !showReplyForm">
                            <h3>
                                <i class="fas fa-reply"></i>
                                {{ t('admin.contacts.writeReply') }}
                            </h3>
                            <i :class="showReplyForm ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                        </div>

                        <div v-if="showReplyForm" class="reply-form-body">
                            <div class="reply-to-info">
                                <i class="fas fa-paper-plane"></i>
                                {{ t('admin.contacts.willSendTo') }}: <strong>{{ message.email }}</strong>
                            </div>

                            <textarea
                                v-model="replyText"
                                class="reply-textarea"
                                rows="8"
                                :placeholder="t('admin.contacts.replyPlaceholder')"
                            ></textarea>

                            <div class="reply-form-actions">
                                <button @click="showReplyForm = false" class="btn btn-cancel">
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
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Sender Info -->
                    <div class="info-card">
                        <h3>
                            <i class="fas fa-user"></i>
                            {{ t('admin.contacts.senderInfo') }}
                        </h3>

                        <div class="sender-profile">
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
                                <span v-if="message.user" class="sender-username">
                                    <i class="fas fa-user-check"></i> @{{ message.user.username }}
                                </span>
                            </div>
                        </div>

                        <div class="info-list">
                            <div class="info-item" @click="copyToClipboard(message.email, 'E-pasts')">
                                <span class="info-label">
                                    <i class="fas fa-envelope"></i>
                                    {{ t('admin.contacts.email') }}
                                </span>
                                <span class="info-value copyable">
                                    {{ message.email }}
                                    <i class="fas fa-copy"></i>
                                </span>
                            </div>

                            <div v-if="message.full_phone" class="info-item" @click="copyToClipboard(message.full_phone, 'Tālrunis')">
                                <span class="info-label">
                                    <i class="fas fa-phone"></i>
                                    {{ t('admin.contacts.phone') }}
                                </span>
                                <span class="info-value copyable">
                                    {{ message.full_phone }}
                                    <i class="fas fa-copy"></i>
                                </span>
                            </div>

                            <div v-if="message.user" class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-id-badge"></i>
                                    {{ t('admin.contacts.userId') }}
                                </span>
                                <span class="info-value">
                                    <Link :href="`/admin/users/${message.user.id}`" class="user-link">
                                        #{{ message.user.id }}
                                        <i class="fas fa-external-link-alt"></i>
                                    </Link>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="actions-card">
                        <h3>
                            <i class="fas fa-bolt"></i>
                            {{ t('admin.contacts.quickActions') }}
                        </h3>

                        <div class="actions-list">
                            <a
                                :href="`mailto:${message.email}?subject=Re: ${message.subject}`"
                                class="action-btn email"
                                target="_blank"
                            >
                                <i class="fas fa-envelope"></i>
                                {{ t('admin.contacts.openInEmail') }}
                            </a>

                            <a
                                v-if="message.full_phone"
                                :href="`tel:${message.full_phone.replace(/\s/g, '')}`"
                                class="action-btn phone"
                            >
                                <i class="fas fa-phone"></i>
                                {{ t('admin.contacts.call') }}
                            </a>

                            <button @click="deleteMessage" class="action-btn delete">
                                <i class="fas fa-trash"></i>
                                {{ t('admin.contacts.delete') }}
                            </button>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="timeline-card">
                        <h3>
                            <i class="fas fa-history"></i>
                            {{ t('admin.contacts.timeline') }}
                        </h3>

                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-dot received"></div>
                                <div class="timeline-content">
                                    <span class="timeline-label">{{ t('admin.contacts.received') }}</span>
                                    <span class="timeline-date">{{ formatDate(message.created_at) }}</span>
                                </div>
                            </div>

                            <div v-if="message.is_read" class="timeline-item">
                                <div class="timeline-dot read"></div>
                                <div class="timeline-content">
                                    <span class="timeline-label">{{ t('admin.contacts.markedAsRead') }}</span>
                                    <span class="timeline-date">{{ formatDate(message.updated_at) }}</span>
                                </div>
                            </div>

                            <div v-if="message.is_replied" class="timeline-item">
                                <div class="timeline-dot replied"></div>
                                <div class="timeline-content">
                                    <span class="timeline-label">{{ t('admin.contacts.replySent') }}</span>
                                    <span class="timeline-date">{{ formatDate(message.replied_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.back-link {
    color: #6b7280;
    margin-right: 0.75rem;
    text-decoration: none;
}

.back-link:hover {
    color: #dc2626;
}

.show-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Status Bar */
.status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.status-bar.unread {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-left: 4px solid #dc2626;
}

.status-bar.read {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-left: 4px solid #f59e0b;
}

.status-bar.replied {
    background: linear-gradient(135deg, #f0fdf4, #d1fae5);
    border-left: 4px solid #10b981;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.9rem;
}

.status-bar.unread .status-info { color: #991b1b; }
.status-bar.read .status-info { color: #92400e; }
.status-bar.replied .status-info { color: #065f46; }

.status-date {
    font-size: 0.8rem;
    color: #6b7280;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
}

/* Main Content */
.main-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Message Card */
.message-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.message-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.message-header h2 {
    margin: 0;
    font-size: 1.25rem;
    color: #111827;
}

.message-body {
    padding: 1.5rem;
}

.message-body p {
    margin: 0;
    font-size: 0.95rem;
    color: #374151;
    line-height: 1.7;
    white-space: pre-wrap;
}

.message-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}

.message-date {
    font-size: 0.8rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

/* Reply Card */
.reply-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #10b981;
    overflow: hidden;
}

.reply-header {
    padding: 1rem 1.5rem;
    background: #f0fdf4;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.reply-title {
    font-weight: 600;
    color: #065f46;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reply-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.reply-body {
    padding: 1.5rem;
}

.reply-body p {
    margin: 0;
    font-size: 0.9rem;
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
}

/* Reply Form Card */
.reply-form-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.reply-form-header {
    padding: 1rem 1.5rem;
    background: #f9fafb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.2s;
}

.reply-form-header:hover {
    background: #f3f4f6;
}

.reply-form-header h3 {
    margin: 0;
    font-size: 1rem;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reply-form-header h3 i {
    color: #dc2626;
}

.reply-form-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.reply-to-info {
    font-size: 0.85rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f3f4f6;
    border-radius: 0.375rem;
}

.reply-to-info strong {
    color: #111827;
}

.reply-textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    resize: vertical;
    min-height: 150px;
    font-family: inherit;
}

.reply-textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.reply-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    transition: all 0.2s;
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

/* Sidebar */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Info Card */
.info-card, .actions-card, .timeline-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-card h3, .actions-card h3, .timeline-card h3 {
    margin: 0 0 1rem 0;
    font-size: 0.9rem;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.info-card h3 i, .actions-card h3 i, .timeline-card h3 i {
    color: #dc2626;
}

/* Sender Profile */
.sender-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.sender-avatar {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.sender-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sender-avatar i {
    font-size: 1.5rem;
    color: #9ca3af;
}

.sender-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.sender-name {
    font-weight: 600;
    color: #111827;
    font-size: 1rem;
}

.sender-username {
    font-size: 0.75rem;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.info-value {
    font-size: 0.85rem;
    color: #111827;
    word-break: break-all;
}

.info-value.copyable {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-value.copyable:hover {
    color: #dc2626;
}

.info-value.copyable i {
    font-size: 0.7rem;
    color: #9ca3af;
}

.user-link {
    color: #dc2626;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.user-link:hover {
    text-decoration: underline;
}

.user-link i {
    font-size: 0.65rem;
}

/* Actions List */
.actions-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn.email {
    background: #dbeafe;
    color: #1e40af;
}

.action-btn.email:hover {
    background: #bfdbfe;
}

.action-btn.phone {
    background: #d1fae5;
    color: #065f46;
}

.action-btn.phone:hover {
    background: #a7f3d0;
}

.action-btn.delete {
    background: #fee2e2;
    color: #991b1b;
}

.action-btn.delete:hover {
    background: #fecaca;
}

/* Timeline */
.timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.timeline-item {
    display: flex;
    gap: 0.75rem;
    position: relative;
    padding-bottom: 1rem;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 0.4375rem;
    top: 1.25rem;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-dot {
    width: 0.875rem;
    height: 0.875rem;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.timeline-dot.received {
    background: #60a5fa;
}

.timeline-dot.read {
    background: #fbbf24;
}

.timeline-dot.replied {
    background: #34d399;
}

.timeline-content {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.timeline-label {
    font-size: 0.8rem;
    color: #374151;
    font-weight: 500;
}

.timeline-date {
    font-size: 0.7rem;
    color: #9ca3af;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .sidebar {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .timeline-card {
        grid-column: span 2;
    }
}

@media (max-width: 768px) {
    .status-bar {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .sidebar {
        grid-template-columns: 1fr;
    }

    .timeline-card {
        grid-column: span 1;
    }

    .reply-form-actions {
        flex-direction: column;
    }

    .reply-form-actions .btn {
        width: 100%;
        justify-content: center;
    }

    .reply-meta {
        flex-direction: column;
        gap: 0.25rem;
    }
}

@media (max-width: 480px) {
    .message-header h2 {
        font-size: 1.1rem;
    }

    .message-body p {
        font-size: 0.875rem;
    }

    .sender-avatar {
        width: 3rem;
        height: 3rem;
    }

    .info-card, .actions-card, .timeline-card {
        padding: 1rem;
    }
}
</style>
