<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    message: Object,
});

// ─── REPLY STATE ──────────────────────────────────────────────────────────────
const showReplyForm  = ref(false);
const replyText      = ref('');
const isSubmitting   = ref(false);

// Edit mode for an existing reply
const isEditingReply = ref(false);
const editReplyText  = ref(props.message?.reply_text || '');

// ─── HELPERS ─────────────────────────────────────────────────────────────────
const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const formatDateShort = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const getUserAvatar = (user) => {
    if (!user?.profile_picture) return null;
    if (user.profile_picture.startsWith('http')) return user.profile_picture;
    return `/storage/${user.profile_picture}`;
};

// Is this a courier problem report?
const isCourierReport = computed(() =>
    props.message?.subject?.includes('🚨') || props.message?.subject?.includes('[Kurjers]')
);

const getStatusInfo = () => {
    if (props.message.is_replied)
        return { class: 'replied', icon: 'fas fa-check-double', label: t('admin.contacts.status.replied') };
    if (props.message.is_read)
        return { class: 'read', icon: 'fas fa-envelope-open', label: t('admin.contacts.status.read') };
    return { class: 'unread', icon: 'fas fa-envelope', label: t('admin.contacts.status.unread') };
};

// ─── ACTIONS ─────────────────────────────────────────────────────────────────
const submitReply = () => {
    if (!replyText.value.trim() || replyText.value.length < 10) {
        alert(t('admin.contacts.replyMinLength'));
        return;
    }
    isSubmitting.value = true;
    router.put(`/admin/contacts/${props.message.id}/reply`, { reply_text: replyText.value }, {
        preserveScroll: true,
        onSuccess: () => { showReplyForm.value = false; replyText.value = ''; },
        onFinish: () => { isSubmitting.value = false; },
    });
};

const submitEditReply = () => {
    if (!editReplyText.value.trim() || editReplyText.value.length < 10) {
        alert(t('admin.contacts.replyMinLength'));
        return;
    }
    isSubmitting.value = true;
    router.put(`/admin/contacts/${props.message.id}/reply`, { reply_text: editReplyText.value }, {
        preserveScroll: true,
        onSuccess: () => { isEditingReply.value = false; },
        onFinish: () => { isSubmitting.value = false; },
    });
};

const deleteMessage = () => {
    if (confirm(t('admin.contacts.confirmDelete'))) {
        router.delete(`/admin/contacts/${props.message.id}`);
    }
};

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

                    <!-- Courier alert banner -->
                    <div v-if="isCourierReport" class="courier-banner">
                        <span class="courier-banner-icon"><i class="fas fa-truck"></i></span>
                        <div class="courier-banner-body">
                            <strong>{{ locale === 'lv' ? 'Kurjera problēmas ziņojums' : 'Courier Problem Report' }}</strong>
                            <span class="courier-banner-type">{{ message.subject.replace(/^\[.*?\]\s*/, '') }}</span>
                        </div>
                        <Link href="/admin/couriers" class="courier-banner-link">
                            <i class="fas fa-arrow-right"></i>
                            {{ locale === 'lv' ? 'Uz kurjeriem' : 'Couriers' }}
                        </Link>
                    </div>

                    <!-- ── CONVERSATION THREAD ───────────────────────────────── -->
                    <div class="thread">

                        <!-- Sender message (left) -->
                        <div class="thread-row thread-left">
                            <div class="thread-avatar">
                                <img
                                    v-if="getUserAvatar(message.user)"
                                    :src="getUserAvatar(message.user)"
                                    :alt="message.name"
                                    class="thread-avatar-img"
                                />
                                <div v-else class="thread-avatar-initials">
                                    {{ message.name?.charAt(0)?.toUpperCase() || '?' }}
                                </div>
                            </div>
                            <div class="thread-content">
                                <div class="thread-meta">
                                    <span class="thread-author">{{ message.name }}</span>
                                    <span v-if="message.user" class="thread-username">@{{ message.user.username }}</span>
                                    <span class="thread-time">{{ formatDateShort(message.created_at) }}</span>
                                </div>
                                <div class="thread-bubble bubble-user">
                                    <p class="bubble-subject">
                                        <i class="fas fa-envelope"></i>
                                        {{ message.subject }}
                                    </p>
                                    <p class="bubble-body">{{ message.message }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Thread divider with status -->
                        <div class="thread-divider">
                            <div class="thread-divider-line"></div>
                            <span class="thread-divider-label">
                                <i :class="getStatusInfo().icon"></i>
                                {{ getStatusInfo().label }}
                            </span>
                            <div class="thread-divider-line"></div>
                        </div>

                        <!-- Admin reply (right) — ALREADY REPLIED -->
                        <template v-if="message.is_replied">
                            <div class="thread-row thread-right">
                                <div class="thread-content thread-content-right">
                                    <div class="thread-meta thread-meta-right">
                                        <span class="thread-time">{{ formatDateShort(message.replied_at) }}</span>
                                        <span v-if="message.replied_by" class="thread-username">@{{ message.replied_by.username }}</span>
                                        <span class="thread-author">{{ locale === 'lv' ? 'Administrators' : 'Admin' }}</span>
                                    </div>

                                    <!-- VIEW mode -->
                                    <div v-if="!isEditingReply" class="thread-bubble bubble-admin">
                                        <div class="bubble-admin-header">
                                            <span class="bubble-admin-label">
                                                <i class="fas fa-reply"></i>
                                                {{ t('admin.contacts.yourReplyTitle') }}
                                            </span>
                                            <button
                                                class="btn-edit-reply"
                                                @click="isEditingReply = true; editReplyText = message.reply_text"
                                                :title="locale === 'lv' ? 'Rediģēt atbildi' : 'Edit reply'"
                                            >
                                                <i class="fas fa-edit"></i>
                                                {{ locale === 'lv' ? 'Rediģēt' : 'Edit' }}
                                            </button>
                                        </div>
                                        <p class="bubble-body">{{ message.reply_text }}</p>
                                    </div>

                                    <!-- EDIT mode -->
                                    <div v-else class="thread-bubble bubble-admin bubble-editing">
                                        <div class="bubble-admin-header editing">
                                            <span class="bubble-admin-label editing">
                                                <i class="fas fa-edit"></i>
                                                {{ locale === 'lv' ? 'Rediģē atbildi' : 'Editing reply' }}
                                            </span>
                                        </div>
                                        <textarea
                                            v-model="editReplyText"
                                            class="reply-textarea"
                                            rows="6"
                                            :placeholder="t('admin.contacts.replyPlaceholder')"
                                        ></textarea>
                                        <div class="reply-form-actions">
                                            <button @click="isEditingReply = false" class="btn btn-cancel">
                                                <i class="fas fa-times"></i>
                                                {{ t('admin.common.cancel') }}
                                            </button>
                                            <button
                                                @click="submitEditReply"
                                                :disabled="isSubmitting || editReplyText.length < 10"
                                                class="btn btn-submit"
                                            >
                                                <i :class="isSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                                                {{ isSubmitting
                                                ? t('admin.contacts.sending')
                                                : (locale === 'lv' ? 'Saglabāt' : 'Save') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="thread-avatar">
                                    <div class="thread-avatar-admin">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Admin reply (right) — NOT YET REPLIED -->
                        <template v-else>
                            <div class="thread-row thread-right">
                                <div class="thread-content thread-content-right" style="flex:1">

                                    <!-- Collapsed trigger -->
                                    <div v-if="!showReplyForm" class="reply-trigger" @click="showReplyForm = true">
                                        <i class="fas fa-reply"></i>
                                        {{ t('admin.contacts.writeReply') }}
                                        <i class="fas fa-chevron-down" style="margin-left:auto"></i>
                                    </div>

                                    <!-- Expanded form -->
                                    <div v-else class="thread-bubble bubble-admin bubble-editing">
                                        <div class="bubble-admin-header editing">
                                            <span class="bubble-admin-label editing">
                                                <i class="fas fa-reply"></i>
                                                {{ t('admin.contacts.writeReply') }}
                                            </span>
                                        </div>
                                        <div class="reply-to-info">
                                            <i class="fas fa-paper-plane"></i>
                                            {{ t('admin.contacts.willSendTo') }}: <strong>{{ message.email }}</strong>
                                        </div>
                                        <textarea
                                            v-model="replyText"
                                            class="reply-textarea"
                                            rows="7"
                                            :placeholder="t('admin.contacts.replyPlaceholder')"
                                        ></textarea>
                                        <div class="reply-form-actions">
                                            <button @click="showReplyForm = false" class="btn btn-cancel">
                                                {{ t('admin.common.cancel') }}
                                            </button>
                                            <button
                                                @click="submitReply"
                                                :disabled="isSubmitting || replyText.length < 10"
                                                class="btn btn-submit"
                                            >
                                                <i :class="isSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                                                {{ isSubmitting ? t('admin.contacts.sending') : t('admin.contacts.sendReply') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="thread-avatar">
                                    <div class="thread-avatar-admin">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div><!-- end .thread -->
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

/* Message Card — kept for legacy, hidden by new thread */
.message-card,
.reply-card,
.reply-form-card { display: none; }

/* ── COURIER BANNER ──────────────────────────────────────────────── */
.courier-banner {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1.25rem;
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    border: 1.5px solid #fed7aa;
    border-radius: 0.75rem;
    margin-bottom: 1.25rem;
}

.courier-banner-icon {
    width: 2.25rem;
    height: 2.25rem;
    background: #f97316;
    color: white;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.courier-banner-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.courier-banner-body strong {
    font-size: 0.875rem;
    color: #9a3412;
}

.courier-banner-type {
    font-size: 0.8rem;
    color: #c2410c;
}

.courier-banner-link {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    background: #f97316;
    color: white;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s;
}

.courier-banner-link:hover { background: #ea580c; }

/* ── THREAD (conversation view) ──────────────────────────────────── */
.thread {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.thread-row {
    display: flex;
    gap: 0.875rem;
    align-items: flex-start;
}

.thread-left  { flex-direction: row; }
.thread-right { flex-direction: row-reverse; }

/* Avatar */
.thread-avatar { flex-shrink: 0; }

.thread-avatar-img {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

.thread-avatar-initials {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: #e5e7eb;
    color: #6b7280;
    font-weight: 700;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.thread-avatar-admin {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Content area */
.thread-content {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    max-width: calc(100% - 3.5rem);
    flex: 1;
}

.thread-content-right { align-items: flex-end; }

/* Meta line */
.thread-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.thread-meta-right {
    flex-direction: row-reverse;
}

.thread-author {
    font-size: 0.85rem;
    font-weight: 600;
    color: #111827;
}

.thread-username {
    font-size: 0.75rem;
    color: #10b981;
}

.thread-time {
    font-size: 0.7rem;
    color: #9ca3af;
}

/* Bubbles */
.thread-bubble {
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    width: 100%;
}

/* User bubble — light grey, left-aligned */
.bubble-user {
    background: white;
    border: 1px solid #e5e7eb;
    border-top-left-radius: 0.125rem;
}

.bubble-subject {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.bubble-subject i { color: #9ca3af; font-size: 0.8rem; }

.bubble-body {
    padding: 1rem;
    margin: 0;
    font-size: 0.9rem;
    color: #374151;
    line-height: 1.7;
    white-space: pre-wrap;
}

/* Admin bubble — green tint, right-aligned */
.bubble-admin {
    background: white;
    border: 1.5px solid #bbf7d0;
    border-top-right-radius: 0.125rem;
}

.bubble-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.625rem 1rem;
    background: #f0fdf4;
    border-bottom: 1px solid #bbf7d0;
}

.bubble-admin-label {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #065f46;
}

.bubble-admin-label.editing { color: #d97706; }
.bubble-admin-header.editing { background: #fffbeb; border-bottom-color: #fde68a; }

.btn-edit-reply {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.625rem;
    background: white;
    border: 1px solid #bbf7d0;
    border-radius: 0.375rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #059669;
    cursor: pointer;
    transition: all 0.15s;
}

.btn-edit-reply:hover {
    background: #f0fdf4;
    border-color: #6ee7b7;
}

/* Editing bubble */
.bubble-editing {
    border-color: #fde68a;
}

/* Thread divider */
.thread-divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0 0.25rem;
}

.thread-divider-line {
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

.thread-divider-label {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    white-space: nowrap;
    color: #9ca3af;
}

/* Reply trigger (collapsed state) */
.reply-trigger {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.875rem 1.25rem;
    background: white;
    border: 1.5px dashed #d1d5db;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
}

.reply-trigger:hover {
    background: #fafafa;
    border-color: #dc2626;
    color: #dc2626;
}

.reply-trigger i:first-child { color: #dc2626; }

/* Reply form inside bubble */
.reply-to-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: #fffbeb;
    font-size: 0.8rem;
    color: #92400e;
    border-bottom: 1px solid #fde68a;
}

.reply-to-info strong { color: #451a03; }

.reply-textarea {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    font-size: 0.9rem;
    resize: vertical;
    min-height: 140px;
    font-family: inherit;
    box-sizing: border-box;
    display: block;
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
    padding: 0.875rem 1rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
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

    /* Thread on mobile: hide avatars to save space */
    .thread-avatar { display: none; }
    .thread-content { max-width: 100%; }
    .thread-right { flex-direction: row; }
    .thread-content-right { align-items: flex-start; }
    .thread-meta-right { flex-direction: row; }
    .bubble-admin { border-top-right-radius: 0.75rem; border-top-left-radius: 0.125rem; }

    .courier-banner { flex-wrap: wrap; }
    .courier-banner-link { width: 100%; justify-content: center; }
}

@media (max-width: 480px) {
    .info-card, .actions-card, .timeline-card {
        padding: 1rem;
    }
}
</style>
