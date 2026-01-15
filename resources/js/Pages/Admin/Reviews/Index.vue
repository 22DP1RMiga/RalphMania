<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    reviews: Object,
    filters: Object,
    stats: Object,
});

// Local filter state
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const ratingFilter = ref(props.filters?.rating || '');
const typeFilter = ref(props.filters?.type || '');

// Processing states
const processingId = ref(null);

// Apply filters with debounce
const applyFilters = debounce(() => {
    router.get('/admin/reviews', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        rating: ratingFilter.value || undefined,
        type: typeFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

// Watch for filter changes
watch([search, statusFilter, ratingFilter, typeFilter], applyFilters);

// Clear all filters
const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    ratingFilter.value = '';
    typeFilter.value = '';
};

// Has active filters
const hasFilters = computed(() => {
    return search.value || statusFilter.value || ratingFilter.value || typeFilter.value;
});

// Approve review
const approveReview = (id) => {
    if (confirm(t('admin.reviews.confirmApprove'))) {
        processingId.value = id;
        router.put(`/admin/reviews/${id}/approve`, {}, {
            preserveScroll: true,
            onFinish: () => processingId.value = null,
        });
    }
};

// Reject review
const rejectReview = (id) => {
    if (confirm(t('admin.reviews.confirmReject'))) {
        processingId.value = id;
        router.put(`/admin/reviews/${id}/reject`, {}, {
            preserveScroll: true,
            onFinish: () => processingId.value = null,
        });
    }
};

// Get reviewable name based on locale
const getReviewableName = (review) => {
    if (!review.reviewable) return t('admin.reviews.deleted');

    if (review.reviewable_type === 'product') {
        return locale.value === 'lv'
            ? (review.reviewable.name_lv || review.reviewable.name_en)
            : (review.reviewable.name_en || review.reviewable.name_lv);
    } else if (review.reviewable_type === 'content') {
        return locale.value === 'lv'
            ? (review.reviewable.title_lv || review.reviewable.title_en)
            : (review.reviewable.title_en || review.reviewable.title_lv);
    }
    return '-';
};

// Get review text based on locale
const getReviewText = (review) => {
    if (locale.value === 'lv') {
        return review.review_text_lv || review.review_text_en || null;
    }
    return review.review_text_en || review.review_text_lv || null;
};

// Get reviewable link
const getReviewableLink = (review) => {
    if (!review.reviewable) return null;

    if (review.reviewable_type === 'product') {
        return `/shop/product/${review.reviewable.slug}`;
    } else if (review.reviewable_type === 'content') {
        return `/content/${review.reviewable.slug}`;
    }
    return null;
};

// Get reviewable image
const getReviewableImage = (review) => {
    if (!review.reviewable) return '/img/no-content-placeholder.png';

    if (review.reviewable_type === 'product') {
        const img = review.reviewable.image;
        if (!img) return '/img/Products/placeholder-product.png';
        if (img.startsWith('http') || img.startsWith('/')) return img;
        return `/img/Products/${img}`;
    } else if (review.reviewable_type === 'content') {
        const img = review.reviewable.thumbnail || review.reviewable.featured_image;
        if (!img) return '/img/thumbnails/no-content-placeholder.png';
        if (img.startsWith('http') || img.startsWith('/')) return img;
        return review.reviewable.type === 'video'
            ? `/img/thumbnails/${img}`
            : `/img/Blogs/${img}`;
    }
    return '/img/no-content-placeholder.png';
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
</script>

<template>
    <Head :title="t('admin.reviews.index.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.reviews.index.title') }}</template>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.total || 0 }}</span>
                    <span class="stat-label">{{ t('admin.reviews.stats.total') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.pending || 0 }}</span>
                    <span class="stat-label">{{ t('admin.reviews.stats.pending') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.approved || 0 }}</span>
                    <span class="stat-label">{{ t('admin.reviews.stats.approved') }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon rating">
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ stats?.average_rating || 0 }}</span>
                    <span class="stat-label">{{ t('admin.reviews.stats.averageRating') }}</span>
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
                            :placeholder="t('admin.reviews.searchPlaceholder')"
                        >
                        <button v-if="search" @click="search = ''" class="clear-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <select v-model="statusFilter" class="filter-select">
                        <option value="">{{ t('admin.reviews.allStatuses') }}</option>
                        <option value="pending">{{ t('admin.reviews.status.pending') }}</option>
                        <option value="approved">{{ t('admin.reviews.status.approved') }}</option>
                    </select>
                </div>

                <!-- Rating Filter -->
                <div class="filter-group">
                    <select v-model="ratingFilter" class="filter-select">
                        <option value="">{{ t('admin.reviews.allRatings') }}</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4">⭐⭐⭐⭐ (4)</option>
                        <option value="3">⭐⭐⭐ (3)</option>
                        <option value="2">⭐⭐ (2)</option>
                        <option value="1">⭐ (1)</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="filter-group">
                    <select v-model="typeFilter" class="filter-select">
                        <option value="">{{ t('admin.reviews.allTypes') }}</option>
                        <option value="product">{{ t('admin.reviews.type.product') }}</option>
                        <option value="content">{{ t('admin.reviews.type.content') }}</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <button v-if="hasFilters" @click="clearFilters" class="btn btn-clear">
                    <i class="fas fa-times"></i>
                    {{ t('admin.common.clearFilters') }}
                </button>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="reviews-container">
            <!-- Empty State -->
            <div v-if="reviews.data.length === 0" class="empty-state">
                <i class="fas fa-star"></i>
                <h3>{{ t('admin.reviews.noReviews') }}</h3>
                <p>{{ t('admin.reviews.noReviewsDesc') }}</p>
            </div>

            <!-- Reviews Grid -->
            <div v-else class="reviews-grid">
                <div
                    v-for="review in reviews.data"
                    :key="review.id"
                    class="review-card"
                    :class="{ 'pending': !review.is_approved }"
                >
                    <!-- Status Badge -->
                    <div class="review-status-badge" :class="review.is_approved ? 'approved' : 'pending'">
                        <i :class="review.is_approved ? 'fas fa-check' : 'fas fa-clock'"></i>
                        {{ review.is_approved ? t('admin.reviews.status.approved') : t('admin.reviews.status.pending') }}
                    </div>

                    <!-- Header -->
                    <div class="review-header">
                        <!-- User Info -->
                        <div class="user-info">
                            <div class="user-avatar">
                                <img
                                    v-if="getUserAvatar(review.user)"
                                    :src="getUserAvatar(review.user)"
                                    :alt="review.user?.username"
                                >
                                <i v-else class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <span class="username">{{ review.user?.username || t('admin.reviews.deletedUser') }}</span>
                                <span class="date">{{ formatDate(review.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div class="rating-display">
                            <div class="stars">
                                <i
                                    v-for="star in 5"
                                    :key="star"
                                    class="fas fa-star"
                                    :class="{ 'filled': star <= review.rating }"
                                ></i>
                            </div>
                            <span class="rating-number">{{ review.rating }}/5</span>
                        </div>
                    </div>

                    <!-- Reviewable Item -->
                    <div class="reviewable-item">
                        <img
                            :src="getReviewableImage(review)"
                            :alt="getReviewableName(review)"
                            class="reviewable-image"
                            @error="$event.target.src = '/img/no-content-placeholder.png'"
                        >
                        <div class="reviewable-info">
                            <span class="reviewable-type">
                                <i :class="review.reviewable_type === 'product' ? 'fas fa-shopping-bag' : contentTypeIcons[review.reviewable?.type] || 'fas fa-file'"></i>
                                {{ review.reviewable_type === 'product' ? t('admin.reviews.type.product') : t('admin.reviews.type.content') }}
                                <span v-if="review.reviewable?.type" class="content-subtype">
                                    ({{ t(`admin.content.types.${review.reviewable.type}`) }})
                                </span>
                            </span>
                            <a
                                v-if="getReviewableLink(review)"
                                :href="getReviewableLink(review)"
                                target="_blank"
                                class="reviewable-name"
                            >
                                {{ getReviewableName(review) }}
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <span v-else class="reviewable-name deleted">
                                {{ getReviewableName(review) }}
                            </span>
                        </div>
                    </div>

                    <!-- Review Text -->
                    <div v-if="getReviewText(review)" class="review-text">
                        <p>{{ getReviewText(review) }}</p>
                    </div>
                    <div v-else class="review-text empty">
                        <p><em>{{ t('admin.reviews.noText') }}</em></p>
                    </div>

                    <!-- Actions -->
                    <div class="review-actions">
                        <button
                            v-if="!review.is_approved"
                            @click="approveReview(review.id)"
                            class="btn btn-approve"
                            :disabled="processingId === review.id"
                        >
                            <i :class="processingId === review.id ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
                            {{ t('admin.reviews.approve') }}
                        </button>
                        <button
                            @click="rejectReview(review.id)"
                            class="btn btn-reject"
                            :disabled="processingId === review.id"
                        >
                            <i :class="processingId === review.id ? 'fas fa-spinner fa-spin' : 'fas fa-trash'"></i>
                            {{ t('admin.reviews.reject') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="reviews.links && reviews.links.length > 3" class="pagination-wrapper">
                <div class="pagination-info">
                    {{ t('admin.common.showing') }} {{ reviews.from }}-{{ reviews.to }} {{ t('admin.common.of') }} {{ reviews.total }}
                </div>
                <div class="pagination">
                    <template v-for="link in reviews.links" :key="link.label">
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
    background: linear-gradient(135deg, #fef3c7, #fcd34d);
    color: #92400e;
}

.stat-icon.pending {
    background: linear-gradient(135deg, #fef3c7, #fbbf24);
    color: #92400e;
}

.stat-icon.approved {
    background: linear-gradient(135deg, #d1fae5, #34d399);
    color: #065f46;
}

.stat-icon.rating {
    background: linear-gradient(135deg, #fee2e2, #f87171);
    color: #991b1b;
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

.search-input-wrapper i {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
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

/* Reviews Container */
.reviews-container {
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

/* Reviews Grid */
.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

/* Review Card */
.review-card {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.25rem;
    position: relative;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.review-card.pending {
    border-color: #fbbf24;
    background: #fffbeb;
}

.review-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Status Badge */
.review-status-badge {
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

.review-status-badge.approved {
    background: #d1fae5;
    color: #065f46;
}

.review-status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

/* Review Header */
.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-right: 5rem;
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

/* Rating */
.rating-display {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.stars {
    display: flex;
    gap: 0.125rem;
}

.stars i {
    font-size: 0.875rem;
    color: #d1d5db;
}

.stars i.filled {
    color: #fbbf24;
}

.rating-number {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
}

/* Reviewable Item */
.reviewable-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.reviewable-image {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 0.375rem;
    object-fit: cover;
    flex-shrink: 0;
}

.reviewable-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.reviewable-type {
    font-size: 0.625rem;
    text-transform: uppercase;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.reviewable-type i {
    color: #dc2626;
}

.content-subtype {
    font-weight: 400;
    text-transform: none;
}

.reviewable-name {
    font-weight: 600;
    color: #111827;
    font-size: 0.875rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.reviewable-name:hover {
    color: #dc2626;
}

.reviewable-name i {
    font-size: 0.625rem;
    color: #9ca3af;
}

.reviewable-name.deleted {
    color: #9ca3af;
    font-style: italic;
}

/* Review Text */
.review-text {
    margin-bottom: 0.75rem;
}

.review-text p {
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.5;
    margin: 0;
}

.review-text.empty p {
    color: #9ca3af;
}

/* Bilingual Badge */
.bilingual-badge {
    display: flex;
    gap: 0.375rem;
    margin-bottom: 1rem;
}

.lang-badge {
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    background: #f3f4f6;
    border-radius: 0.25rem;
    color: #6b7280;
}

/* Actions */
.review-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    flex: 1;
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

/* Responsive */

/* Tablet Landscape */
@media (max-width: 1024px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .reviews-grid {
        grid-template-columns: 1fr;
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

    .btn-clear {
        width: 100%;
        justify-content: center;
    }

    .reviews-container {
        padding: 1rem;
    }

    .review-card {
        padding: 1rem;
    }

    .review-status-badge {
        top: 0.75rem;
        right: 0.75rem;
        font-size: 0.5rem;
        padding: 0.2rem 0.5rem;
    }

    .review-header {
        flex-direction: column;
        gap: 0.75rem;
        padding-right: 0;
        margin-bottom: 0.75rem;
    }

    .user-info {
        width: 100%;
    }

    .rating-display {
        align-items: flex-start;
        flex-direction: row;
        gap: 0.5rem;
    }

    .reviewable-item {
        padding: 0.625rem;
    }

    .reviewable-image {
        width: 3rem;
        height: 3rem;
    }

    .reviewable-name {
        font-size: 0.8rem;
    }

    .review-text p {
        font-size: 0.8rem;
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

    .pagination-link {
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
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

    .reviews-container {
        padding: 0.75rem;
        border-radius: 0.5rem;
    }

    .empty-state {
        padding: 2rem 1rem;
    }

    .empty-state i {
        font-size: 3rem;
    }

    .empty-state h3 {
        font-size: 1rem;
    }

    .empty-state p {
        font-size: 0.875rem;
    }

    .review-card {
        padding: 0.875rem;
        border-radius: 0.5rem;
    }

    .review-status-badge {
        position: relative;
        top: unset;
        right: unset;
        align-self: flex-start;
        margin-bottom: 0.75rem;
    }

    .review-header {
        padding-right: 0;
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

    .stars i {
        font-size: 0.75rem;
    }

    .rating-number {
        font-size: 0.65rem;
    }

    .reviewable-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .reviewable-image {
        width: 100%;
        height: 120px;
        border-radius: 0.375rem;
    }

    .reviewable-info {
        width: 100%;
    }

    .reviewable-type {
        font-size: 0.6rem;
    }

    .reviewable-name {
        font-size: 0.85rem;
        white-space: normal;
    }

    .review-text {
        margin-bottom: 0.5rem;
    }

    .review-text p {
        font-size: 0.75rem;
        line-height: 1.4;
    }

    .bilingual-badge {
        margin-bottom: 0.75rem;
    }

    .lang-badge {
        font-size: 0.55rem;
        padding: 0.1rem 0.3rem;
    }

    .review-actions {
        flex-direction: column;
        gap: 0.5rem;
        padding-top: 0.625rem;
    }

    .btn {
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
}
</style>
