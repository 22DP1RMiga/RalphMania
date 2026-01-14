<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    comments: Object,
    filters: Object,
    stats: Object,
});

// Local filter state
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const contentTypeFilter = ref(props.filters?.content_type || '');
const repliesOnlyFilter = ref(props.filters?.replies_only === 'true');

// Processing states
const processingId = ref(null);

// Debounce helper
let searchTimeout = null;
const debounceSearch = (fn, delay = 300) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fn, delay);
};

// Apply filters
const applyFilters = () => {
    router.get('/admin/comments', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        content_type: contentTypeFilter.value || undefined,
        replies_only: repliesOnlyFilter.value ? 'true' : undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Watch for filter changes
watch([statusFilter, contentTypeFilter, repliesOnlyFilter], applyFilters);
watch(search, () => debounceSearch(applyFilters));

// Clear all filters
const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    contentTypeFilter.value = '';
    repliesOnlyFilter.value = false;
};

// Has active filters
const hasFilters = computed(() => {
    return search.value || statusFilter.value || contentTypeFilter.value || repliesOnlyFilter.value;
});

// Approve comment
const approveComment = (id) => {
    if (confirm(t('admin.comments.confirmApprove'))) {
        processingId.value = id;
        router.put(`/admin/comments/${id}/approve`, {}, {
            preserveScroll: true,
            onFinish: () => processingId.value = null,
        });
    }
};

// Reject comment
const rejectComment = (id) => {
    if (confirm(t('admin.comments.confirmReject'))) {
        processingId.value = id;
        router.put(`/admin/comments/${id}/reject`, {}, {
            preserveScroll: true,
            onFinish: () => processingId.value = null,
        });
    }
};

// Get content title based on locale
const getContentTitle = (content) => {
    if (!content) return t('admin.comments.deleted');
    return locale.value === 'lv'
        ? (content.title_lv || content.title_en)
        : (content.title_en || content.title_lv);
};

// Get content link
const getContentLink = (content) => {
    if (!content) return null;
    return `/content/${content.slug}`;
};

// Get content image
const getContentImage = (content) => {
    if (!content) return '/img/thumbnails/no-content-placeholder.png';

    const img = content.thumbnail || content.featured_image;
    if (!img) return '/img/thumbnails/no-content-placeholder.png';
    if (img.startsWith('http') || img.startsWith('/')) return img;

    return content.type === 'video'
        ? `/img/thumbnails/${img}`
        : `/img/Blogs/${img}`;
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

// Content type icons
const contentTypeIcons = {
    video: 'fas fa-video',
    blog: 'fas fa-blog',
    news: 'fas fa-newspaper',
    announcement: 'fas fa-bullhorn',
};

// Truncate text
const truncateText = (text, length = 150) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};
</script>

<template>
    <Head :title="t('admin.comments.index.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.comments.index.title') }}</template>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.total || 0 }}</span>
                    <span class="stat-label">{{ t('admin.comments.stats.total') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.pending || 0 }}</span>
                    <span class="stat-label">{{ t('admin.comments.stats.pending') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.approved || 0 }}</span>
                    <span class="stat-label">{{ t('admin.comments.stats.approved') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon replies">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.replies || 0 }}</span>
                    <span class="stat-label">{{ t('admin.comments.stats.replies') }}</span>
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
                            :placeholder="t('admin.comments.searchPlaceholder')"
                        >
                        <button v-if="search" @click="search = ''" class="clear-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <select v-model="statusFilter" class="filter-select">
                        <option value="">{{ t('admin.comments.allStatuses') }}</option>
                        <option value="pending">{{ t('admin.comments.status.pending') }}</option>
                        <option value="approved">{{ t('admin.comments.status.approved') }}</option>
                    </select>
                </div>

                <!-- Content Type Filter -->
                <div class="filter-group">
                    <select v-model="contentTypeFilter" class="filter-select">
                        <option value="">{{ t('admin.comments.allContentTypes') }}</option>
                        <option value="video">{{ t('admin.content.types.video') }}</option>
                        <option value="blog">{{ t('admin.content.types.blog') }}</option>
                        <option value="news">{{ t('admin.content.types.news') }}</option>
                        <option value="announcement">{{ t('admin.content.types.announcement') }}</option>
                    </select>
                </div>

                <!-- Replies Only Toggle -->
                <div class="filter-group">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="repliesOnlyFilter">
                        <span class="checkbox-text">{{ t('admin.comments.repliesOnly') }}</span>
                    </label>
                </div>

                <!-- Clear Filters -->
                <button v-if="hasFilters" @click="clearFilters" class="btn btn-clear">
                    <i class="fas fa-times"></i>
                    {{ t('admin.common.clearFilters') }}
                </button>
            </div>
        </div>

        <!-- Comments List -->
        <div class="comments-container">
            <!-- Empty State -->
            <div v-if="comments.data.length === 0" class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>{{ t('admin.comments.noComments') }}</h3>
                <p>{{ t('admin.comments.noCommentsDesc') }}</p>
            </div>

            <!-- Comments Grid -->
            <div v-else class="comments-list">
                <div
                    v-for="comment in comments.data"
                    :key="comment.id"
                    class="comment-card"
                    :class="{ 'pending': !comment.is_approved, 'is-reply': comment.parent_id }"
                >
                    <!-- Status Badge -->
                    <div class="comment-status-badge" :class="comment.is_approved ? 'approved' : 'pending'">
                        <i :class="comment.is_approved ? 'fas fa-check' : 'fas fa-clock'"></i>
                        {{ comment.is_approved ? t('admin.comments.status.approved') : t('admin.comments.status.pending') }}
                    </div>

                    <!-- Reply Indicator -->
                    <div v-if="comment.parent" class="reply-indicator">
                        <i class="fas fa-reply"></i>
                        <span>
                            {{ t('admin.comments.replyTo') }}
                            <strong>@{{ comment.parent.user?.username || t('admin.comments.deletedUser') }}</strong>
                        </span>
                        <span class="parent-preview">"{{ comment.parent.comment_text }}"</span>
                    </div>

                    <!-- Main Content -->
                    <div class="comment-main">
                        <!-- Left: User & Comment -->
                        <div class="comment-content">
                            <!-- User Info -->
                            <div class="user-info">
                                <div class="user-avatar">
                                    <img
                                        v-if="getUserAvatar(comment.user)"
                                        :src="getUserAvatar(comment.user)"
                                        :alt="comment.user?.username"
                                    >
                                    <i v-else class="fas fa-user"></i>
                                </div>
                                <div class="user-details">
                                    <span class="username">{{ comment.user?.username || t('admin.comments.deletedUser') }}</span>
                                    <span class="date">{{ formatDate(comment.created_at) }}</span>
                                </div>
                            </div>

                            <!-- Comment Text -->
                            <div class="comment-text">
                                <p>{{ comment.comment_text }}</p>
                            </div>
                        </div>

                        <!-- Right: Content Info -->
                        <div class="content-info">
                            <img
                                :src="getContentImage(comment.content)"
                                :alt="getContentTitle(comment.content)"
                                class="content-image"
                                @error="$event.target.src = '/img/thumbnails/no-content-placeholder.png'"
                            >
                            <div class="content-details">
                                <span class="content-type">
                                    <i :class="contentTypeIcons[comment.content?.type] || 'fas fa-file'"></i>
                                    {{ comment.content?.type ? t(`admin.content.types.${comment.content.type}`) : '-' }}
                                </span>
                                <a
                                    v-if="getContentLink(comment.content)"
                                    :href="getContentLink(comment.content)"
                                    target="_blank"
                                    class="content-title"
                                >
                                    {{ truncateText(getContentTitle(comment.content), 40) }}
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <span v-else class="content-title deleted">
                                    {{ getContentTitle(comment.content) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="comment-actions">
                        <button
                            v-if="!comment.is_approved"
                            @click="approveComment(comment.id)"
                            class="btn btn-approve"
                            :disabled="processingId === comment.id"
                        >
                            <i :class="processingId === comment.id ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
                            {{ t('admin.comments.approve') }}
                        </button>
                        <button
                            @click="rejectComment(comment.id)"
                            class="btn btn-reject"
                            :disabled="processingId === comment.id"
                        >
                            <i :class="processingId === comment.id ? 'fas fa-spinner fa-spin' : 'fas fa-trash'"></i>
                            {{ t('admin.comments.reject') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="comments.links && comments.links.length > 3" class="pagination-wrapper">
                <div class="pagination-info">
                    {{ t('admin.common.showing') }} {{ comments.from }}-{{ comments.to }} {{ t('admin.common.of') }} {{ comments.total }}
                </div>
                <div class="pagination">
                    <template v-for="link in comments.links" :key="link.label">
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

.stat-icon.pending {
    background: linear-gradient(135deg, #fef3c7, #fbbf24);
    color: #92400e;
}

.stat-icon.approved {
    background: linear-gradient(135deg, #d1fae5, #34d399);
    color: #065f46;
}

.stat-icon.replies {
    background: linear-gradient(135deg, #e0e7ff, #818cf8);
    color: #3730a3;
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

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.checkbox-label input {
    width: 1rem;
    height: 1rem;
    accent-color: #dc2626;
}

.checkbox-text {
    font-size: 0.875rem;
    color: #374151;
    white-space: nowrap;
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

/* Comments Container */
.comments-container {
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

/* Comments List */
.comments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Comment Card */
.comment-card {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.25rem;
    position: relative;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.comment-card.pending {
    border-color: #fbbf24;
    background: #fffbeb;
}

.comment-card.is-reply {
    margin-left: 1.5rem;
    border-left: 3px solid #818cf8;
}

.comment-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Status Badge */
.comment-status-badge {
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

.comment-status-badge.approved {
    background: #d1fae5;
    color: #065f46;
}

.comment-status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

/* Reply Indicator */
.reply-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px dashed #e5e7eb;
    flex-wrap: wrap;
}

.reply-indicator i {
    color: #818cf8;
}

.reply-indicator strong {
    color: #4f46e5;
}

.parent-preview {
    font-style: italic;
    color: #9ca3af;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Comment Main */
.comment-main {
    display: grid;
    grid-template-columns: 1fr 250px;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

/* Comment Content */
.comment-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar i {
    color: #9ca3af;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.username {
    font-weight: 600;
    color: #111827;
    font-size: 0.9rem;
}

.date {
    font-size: 0.75rem;
    color: #6b7280;
}

.comment-text {
    padding-right: 5rem;
}

.comment-text p {
    font-size: 0.9rem;
    color: #374151;
    line-height: 1.6;
    margin: 0;
    word-break: break-word;
}

/* Content Info */
.content-info {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    align-self: flex-start;
}

.content-image {
    width: 4rem;
    height: 4rem;
    border-radius: 0.375rem;
    object-fit: cover;
    flex-shrink: 0;
}

.content-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.content-type {
    font-size: 0.625rem;
    text-transform: uppercase;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.content-type i {
    color: #dc2626;
}

.content-title {
    font-weight: 600;
    color: #111827;
    font-size: 0.8rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.content-title:hover {
    color: #dc2626;
}

.content-title i {
    font-size: 0.625rem;
    color: #9ca3af;
}

.content-title.deleted {
    color: #9ca3af;
    font-style: italic;
}

/* Actions */
.comment-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 0.5rem 1rem;
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
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-approve {
    background: #d1fae5;
    color: #065f46;
}

.btn-approve:hover:not(:disabled) {
    background: #a7f3d0;
}

.btn-reject {
    background: #fee2e2;
    color: #991b1b;
}

.btn-reject:hover:not(:disabled) {
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

/* ========================================
   RESPONSIVE STYLES
   ======================================== */

/* Tablet Landscape */
@media (max-width: 1024px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .comment-main {
        grid-template-columns: 1fr;
    }

    .content-info {
        align-self: stretch;
    }

    .search-group {
        min-width: 200px;
    }
}

/* Tablet Portrait */
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

    .checkbox-label {
        justify-content: center;
    }

    .btn-clear {
        width: 100%;
        justify-content: center;
    }

    .comments-container {
        padding: 1rem;
    }

    .comment-card {
        padding: 1rem;
    }

    .comment-card.is-reply {
        margin-left: 0.75rem;
    }

    .comment-status-badge {
        position: relative;
        top: unset;
        right: unset;
        align-self: flex-start;
        margin-bottom: 0.75rem;
    }

    .comment-text {
        padding-right: 0;
    }

    .content-info {
        flex-direction: row;
        align-items: center;
    }

    .content-image {
        width: 3rem;
        height: 3rem;
    }

    .reply-indicator {
        flex-direction: column;
        align-items: flex-start;
    }

    .parent-preview {
        max-width: 100%;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .pagination-info {
        text-align: center;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* Mobile */
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
        border-radius: 0.5rem;
    }

    .stat-value {
        font-size: 1.1rem;
    }

    .stat-label {
        font-size: 0.625rem;
    }

    .comments-container {
        padding: 0.75rem;
        border-radius: 0.5rem;
    }

    .empty-state {
        padding: 2rem 1rem;
    }

    .empty-state i {
        font-size: 3rem;
    }

    .comment-card {
        padding: 0.875rem;
        border-radius: 0.5rem;
    }

    .comment-card.is-reply {
        margin-left: 0.5rem;
        border-left-width: 2px;
    }

    .user-avatar {
        width: 2rem;
        height: 2rem;
    }

    .username {
        font-size: 0.8rem;
    }

    .date {
        font-size: 0.65rem;
    }

    .comment-text p {
        font-size: 0.8rem;
    }

    .content-info {
        padding: 0.5rem;
    }

    .content-image {
        width: 2.5rem;
        height: 2.5rem;
    }

    .content-type {
        font-size: 0.55rem;
    }

    .content-title {
        font-size: 0.7rem;
    }

    .comment-actions {
        flex-direction: column;
        gap: 0.5rem;
    }

    .btn {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.8rem;
    }

    .pagination-link {
        padding: 0.35rem 0.5rem;
        font-size: 0.75rem;
    }
}

/* Extra Small Mobile */
@media (max-width: 360px) {
    .stats-row {
        grid-template-columns: 1fr;
    }

    .stat-card {
        flex-direction: row;
        justify-content: flex-start;
    }

    .filters-card {
        padding: 0.75rem;
    }

    .search-input {
        font-size: 0.8rem;
        padding: 0.5rem 2rem;
    }

    .filter-select {
        font-size: 0.8rem;
        padding: 0.5rem 1.5rem 0.5rem 0.75rem;
    }

    .checkbox-text {
        font-size: 0.75rem;
    }
}
</style>
