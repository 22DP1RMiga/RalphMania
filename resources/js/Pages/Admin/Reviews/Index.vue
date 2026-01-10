<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    reviews: {
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
const ratingFilter = ref(props.filters.rating || '');

const applyFilters = () => {
    router.get('/admin/reviews', {
        status: statusFilter.value || undefined,
        rating: ratingFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    statusFilter.value = '';
    ratingFilter.value = '';
    router.get('/admin/reviews');
};

// Approve review
const approveReview = (id) => {
    router.put(`/admin/reviews/${id}/approve`, {}, {
        preserveScroll: true,
    });
};

// Reject (delete) review
const rejectReview = (id) => {
    if (confirm('Vai tiešām vēlaties noraidīt un dzēst šo atsauksmi?')) {
        router.put(`/admin/reviews/${id}/reject`, {}, {
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

// Render stars
const renderStars = (rating) => {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
};
</script>

<template>
    <Head title="Atsauksmes - Admin" />

    <AdminLayout>
        <template #title>Atsauksmes</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">Moderējiet produktu atsauksmes</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-mini pending">
                <i class="fas fa-clock"></i>
                <span class="stat-count">{{ reviews.data?.filter(r => !r.is_approved).length || 0 }}</span>
                <span class="stat-label">Gaida</span>
            </div>
            <div class="stat-mini approved">
                <i class="fas fa-check"></i>
                <span class="stat-count">{{ reviews.data?.filter(r => r.is_approved).length || 0 }}</span>
                <span class="stat-label">Apstiprinātas</span>
            </div>
            <div class="stat-mini stars">
                <i class="fas fa-star"></i>
                <span class="stat-count">{{ (reviews.data?.reduce((a, r) => a + r.rating, 0) / (reviews.data?.length || 1)).toFixed(1) }}</span>
                <span class="stat-label">Vid. vērtējums</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi statusi</option>
                    <option value="pending">Gaida apstiprinājumu</option>
                    <option value="approved">Apstiprinātas</option>
                </select>

                <select v-model="ratingFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi vērtējumi</option>
                    <option value="5">★★★★★ (5)</option>
                    <option value="4">★★★★☆ (4)</option>
                    <option value="3">★★★☆☆ (3)</option>
                    <option value="2">★★☆☆☆ (2)</option>
                    <option value="1">★☆☆☆☆ (1)</option>
                </select>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Notīrīt
                </button>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="reviews-list">
            <div v-for="review in reviews.data" :key="review.id" :class="['review-card', { pending: !review.is_approved }]">
                <!-- Review Header -->
                <div class="review-header">
                    <div class="review-user">
                        <img
                            :src="review.user?.profile_picture ? `/storage/${review.user.profile_picture}` : '/img/default-avatar.png'"
                            class="user-avatar"
                        >
                        <div class="user-info">
                            <span class="user-name">{{ review.user?.username || 'Nezināms' }}</span>
                            <span class="review-date">{{ formatDate(review.created_at) }}</span>
                        </div>
                    </div>
                    <div class="review-rating">
                        <span class="stars">{{ renderStars(review.rating) }}</span>
                        <span class="rating-number">{{ review.rating }}/5</span>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="review-product" v-if="review.product">
                    <img
                        :src="review.product.image ? `/storage/${review.product.image}` : '/img/no-image.png'"
                        class="product-thumb"
                    >
                    <div class="product-info">
                        <span class="product-name">{{ review.product.name_lv }}</span>
                        <span class="product-sku">SKU: {{ review.product.sku }}</span>
                    </div>
                </div>

                <!-- Review Content -->
                <div class="review-content">
                    <h4 v-if="review.title" class="review-title">{{ review.title }}</h4>
                    <p class="review-text">{{ review.comment }}</p>
                </div>

                <!-- Review Footer -->
                <div class="review-footer">
                    <span :class="['status-badge', review.is_approved ? 'approved' : 'pending']">
                        <i :class="review.is_approved ? 'fas fa-check' : 'fas fa-clock'"></i>
                        {{ review.is_approved ? 'Apstiprināta' : 'Gaida' }}
                    </span>

                    <div class="review-actions">
                        <button
                            v-if="!review.is_approved"
                            @click="approveReview(review.id)"
                            class="btn btn-success"
                        >
                            <i class="fas fa-check"></i>
                            Apstiprināt
                        </button>
                        <button
                            @click="rejectReview(review.id)"
                            class="btn btn-danger"
                        >
                            <i class="fas fa-trash"></i>
                            Noraidīt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="reviews.data?.length === 0" class="empty-state">
                <i class="fas fa-star"></i>
                <p>Nav atrasta neviena atsauksme</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="reviews.links && reviews.links.length > 3" class="pagination">
            <Link
                v-for="link in reviews.links"
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
.stat-mini.stars i { color: #eab308; }

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

.filter-select {
    padding: 0.625rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
    min-width: 180px;
}

/* Reviews List */
.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.review-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #10b981;
}

.review-card.pending {
    border-left-color: #f59e0b;
    background: #fffbeb;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.review-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 3rem;
    height: 3rem;
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
}

.review-date {
    font-size: 0.75rem;
    color: #6b7280;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stars {
    color: #eab308;
    font-size: 1.125rem;
    letter-spacing: 2px;
}

.rating-number {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

/* Product Info */
.review-product {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.product-thumb {
    width: 3rem;
    height: 3rem;
    border-radius: 0.375rem;
    object-fit: cover;
}

.product-info {
    display: flex;
    flex-direction: column;
}

.product-name {
    font-weight: 500;
    color: #111827;
    font-size: 0.875rem;
}

.product-sku {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Review Content */
.review-content {
    margin-bottom: 1rem;
}

.review-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.5rem;
}

.review-text {
    color: #4b5563;
    line-height: 1.6;
    margin: 0;
}

/* Review Footer */
.review-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
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

.review-actions {
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
</style>
