<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    auth: Object,
    stats: Object,              // Real stats from backend
    recentOrders: Array,        // Last 5 orders
    recentReviews: Array,       // Last 5 reviews
    recentComments: Array,      // Last 5 comments
    hasAddress: Boolean,        // User has address filled
});

// Get user avatar with correct path
const userAvatar = computed(() => {
    if (!user.value?.profile_picture) {
        return '/img/default-avatar.png'; // Default avatar
    }
    // If profile_picture already starts with /, use it as is
    if (user.value.profile_picture.startsWith('/')) {
        return user.value.profile_picture;
    }
    // Otherwise add /storage/ prefix
    return `/storage/${user.value.profile_picture}`;
});

const { t, locale } = useI18n({ useScope: 'global' });
const user = computed(() => props.auth.user);

// Locale switcher
const currentLocale = ref(localStorage.getItem('lang') || 'lv');
locale.value = currentLocale.value;

watch(currentLocale, (newLang) => {
    locale.value = newLang;
    localStorage.setItem('lang', newLang);
});

const toggleLocale = () => {
    currentLocale.value = currentLocale.value === 'lv' ? 'en' : 'lv';
};

// Active section
const activeSection = ref('overview');

const setActiveSection = (section) => {
    activeSection.value = section;
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat(currentLocale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

// Format price helper
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

// Get status label
const getStatusLabel = (status) => {
    const labels = {
        lv: {
            pending: 'Gaida',
            confirmed: 'Apstiprināts',
            processing: 'Apstrādē',
            packed: 'Iepakots',
            shipped: 'Nosūtīts',
            in_transit: 'Ceļā',
            delivered: 'Piegādāts',
            cancelled: 'Atcelts',
            refunded: 'Atmaksāts',
        },
        en: {
            pending: 'Pending',
            confirmed: 'Confirmed',
            processing: 'Processing',
            packed: 'Packed',
            shipped: 'Shipped',
            in_transit: 'In Transit',
            delivered: 'Delivered',
            cancelled: 'Cancelled',
            refunded: 'Refunded',
        }
    };

    return labels[currentLocale.value][status] || status;
};

// Privacy settings
const isPrivateProfile = ref(!user.value.is_public); // Invert is_public
const savingSettings = ref(false);

const savePrivacySettings = async () => {
    savingSettings.value = true;

    try {
        await axios.put('/profile/privacy', {
            is_private: isPrivateProfile.value
        });

        // Success notification (if you have toast)
        alert(currentLocale.value === 'lv'
            ? 'Iestatījumi veiksmīgi saglabāti!'
            : 'Settings saved successfully!'
        );
    } catch (error) {
        console.error('Failed to save settings:', error);
        alert(currentLocale.value === 'lv'
            ? 'Neizdevās saglabāt iestatījumus'
            : 'Failed to save settings'
        );
    } finally {
        savingSettings.value = false;
    }
};
</script>

<template>
    <Head :title="$t('auth.dashboard')" />

    <div class="dashboard">
        <!-- Dashboard Header with Logo -->
        <div class="dashboard-header">
            <Link href="/" class="dashboard-brand">
                <img src="/img/RoltonsLV_Icon.png" alt="RalphMania Logo" class="dashboard-logo">
                <img src="/img/name_logo.png" alt="RalphMania" class="dashboard-brand-name">
            </Link>
            <div class="dashboard-welcome">
                <h1 class="welcome-title">{{ $t('dashboard.welcome') }}, {{ user.username }}!</h1>
                <p class="welcome-subtitle">{{ $t('dashboard.subtitle') }}</p>
            </div>

            <!-- Locale Switcher -->
            <button @click="toggleLocale" class="header-locale-switcher">
                <span class="locale-current">{{ currentLocale.toUpperCase() }}</span>
                <span class="locale-divider">/</span>
                <span class="locale-other">{{ currentLocale === 'lv' ? 'EN' : 'LV' }}</span>
            </button>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon stat-icon-orders">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.orders }}</div>
                    <div class="stat-label">{{ $t('dashboard.stats.orders') }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-reviews">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.reviews }}</div>
                    <div class="stat-label">{{ $t('dashboard.stats.reviews') }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-favorites">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.favorites }}</div>
                    <div class="stat-label">{{ $t('dashboard.stats.favorites') }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-cart">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.cart_items }}</div>
                    <div class="stat-label">{{ $t('dashboard.stats.cart') }}</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Sidebar Navigation -->
            <aside class="dashboard-sidebar">
                <nav class="sidebar-nav">
                    <button
                        @click="setActiveSection('overview')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'overview' }"
                    >
                        <i class="fas fa-th-large"></i>
                        <span>{{ $t('dashboard.nav.overview') }}</span>
                    </button>

                    <button
                        @click="setActiveSection('profile')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'profile' }"
                    >
                        <i class="fas fa-user"></i>
                        <span>{{ $t('dashboard.nav.profile') }}</span>
                    </button>

                    <button
                        @click="setActiveSection('orders')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'orders' }"
                    >
                        <i class="fas fa-shopping-bag"></i>
                        <span>{{ $t('dashboard.nav.orders') }}</span>
                    </button>

                    <button
                        @click="setActiveSection('addresses')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'addresses' }"
                    >
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $t('dashboard.nav.addresses') }}</span>
                    </button>

                    <button
                        @click="setActiveSection('comments')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'comments' }"
                    >
                        <i class="fas fa-comments"></i>
                        <span>{{ currentLocale === 'lv' ? 'Komentāri' : 'Comments' }}</span>
                    </button>

                    <button
                        @click="setActiveSection('reviews')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'reviews' }"
                    >
                        <i class="fas fa-star"></i>
                        <span>{{ $t('dashboard.nav.reviews') }}</span>
                    </button>

                    <button
                        @click="setActiveSection('settings')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'settings' }"
                    >
                        <i class="fas fa-cog"></i>
                        <span>{{ $t('dashboard.nav.settings') }}</span>
                    </button>

                    <Link href="/" class="sidebar-link sidebar-link-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>{{ $t('dashboard.nav.back_home') }}</span>
                    </Link>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="dashboard-main">
                <!-- Overview Section -->
                <div v-if="activeSection === 'overview'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.overview.title') }}</h2>

                    <div class="overview-grid">
                        <!-- Recent Orders -->
                        <div class="overview-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-shopping-bag"></i>
                                    {{ $t('dashboard.sections.overview.recent_orders') }}
                                </h3>
                                <Link href="/dashboard/orders" class="card-link">
                                    {{ $t('common.view_all') }}
                                </Link>
                            </div>
                            <div class="card-body">
                                <!-- REAL ORDERS DATA -->
                                <div v-if="recentOrders && recentOrders.length > 0" class="orders-preview-list">
                                    <div v-for="order in recentOrders" :key="order.id" class="order-preview-item">
                                        <div class="order-preview-header">
                                            <span class="order-preview-number">#{{ order.order_number }}</span>
                                            <span :class="['order-preview-status', `status-${order.status}`]">
                {{ getStatusLabel(order.status) }}
            </span>
                                        </div>
                                        <div class="order-preview-details">
                                            <span class="order-preview-date">{{ formatDate(order.created_at) }}</span>
                                            <span class="order-preview-total">€{{ formatPrice(order.total_amount) }}</span>
                                        </div>
                                        <Link :href="`/orders/${order.id}`" class="order-preview-link">
                                            {{ currentLocale === 'lv' ? 'Skatīt detaļas' : 'View details' }} →
                                        </Link>
                                    </div>
                                </div>
                                <p v-else class="empty-state">{{ $t('dashboard.sections.overview.no_orders') }}</p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="overview-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt"></i>
                                    {{ $t('dashboard.sections.overview.quick_actions') }}
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <Link href="/shop" class="action-btn">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>{{ $t('dashboard.actions.shop') }}</span>
                                    </Link>
                                    <Link href="/cart" class="action-btn">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span>{{ $t('dashboard.actions.cart') }}</span>
                                    </Link>
                                    <button @click="setActiveSection('profile')" class="action-btn">
                                        <i class="fas fa-user-edit"></i>
                                        <span>{{ $t('dashboard.actions.edit_profile') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div v-if="activeSection === 'profile'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.profile.title') }}</h2>

                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <img
                                    :src="userAvatar"
                                    :alt="user.username"
                                >
                            </div>
                            <div class="profile-info">
                                <h3 class="profile-username">{{ user.username }}</h3>
                                <p class="profile-email">{{ user.email }}</p>
                                <span class="profile-role">{{ user.role?.display_name_lv || 'Customer' }}</span>
                            </div>
                        </div>

                        <div class="profile-details">
                            <div class="detail-row">
                                <span class="detail-label">{{ $t('dashboard.profile.phone') }}:</span>
                                <span class="detail-value">{{ user.phone || '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ $t('dashboard.profile.birth_date') }}:</span>
                                <span class="detail-value">{{ user.birth_date || '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ $t('dashboard.profile.member_since') }}:</span>
                                <span class="detail-value">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">{{ $t('dashboard.profile.last_login') }}:</span>
                                <span class="detail-value">
                                    {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="profile-actions">
                            <Link href="/profile/edit" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                                {{ $t('dashboard.actions.edit_profile') }}
                            </Link>
                            <Link href="/profile/password" class="btn btn-secondary">
                                <i class="fas fa-key"></i>
                                {{ $t('dashboard.actions.change_password') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Orders Section -->
                <div v-if="activeSection === 'orders'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.orders.title') }}</h2>
                    <!-- REAL ORDERS DATA -->
                    <div v-if="recentOrders && recentOrders.length > 0" class="orders-preview-list">
                        <div v-for="order in recentOrders" :key="order.id" class="order-preview-item">
                            <div class="order-preview-header">
                                <span class="order-preview-number">#{{ order.order_number }}</span>
                                <span :class="['order-preview-status', `status-${order.status}`]">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </div>
                            <div class="order-preview-details">
                                <span class="order-preview-date">{{ formatDate(order.created_at) }}</span>
                                <span class="order-preview-total">€{{ formatPrice(order.total_amount) }}</span>
                            </div>
                            <Link :href="`/orders/${order.id}`" class="order-preview-link">
                                {{ currentLocale === 'lv' ? 'Skatīt detaļas' : 'View details' }} →
                            </Link>
                        </div>
                    </div>
                    <p v-else class="empty-state">{{ $t('dashboard.sections.overview.no_orders') }}</p>
                </div>

                <!-- Addresses Section -->
                <div v-if="activeSection === 'addresses'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.addresses.title') }}</h2>

                    <!-- Current Address if exists -->
                    <div v-if="hasAddress" class="current-address-card">
                        <div class="address-card-header">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ currentLocale === 'lv' ? 'Mana piegādes adrese' : 'My Delivery Address' }}</span>
                            <span class="address-badge">
                {{ currentLocale === 'lv' ? 'Aktīva' : 'Active' }}
            </span>
                        </div>
                        <div class="address-card-body">
                            <p class="address-line">
                                <i class="fas fa-home"></i>
                                {{ user.address }}
                            </p>
                            <p class="address-line">
                                <i class="fas fa-city"></i>
                                {{ user.city }}{{ user.postal_code ? ', ' + user.postal_code : '' }}
                            </p>
                            <p class="address-line">
                                <i class="fas fa-globe"></i>
                                {{ user.country }}
                            </p>
                        </div>
                        <div class="address-card-actions">
                            <Link href="/profile/edit" class="btn btn-secondary">
                                <i class="fas fa-edit"></i>
                                {{ currentLocale === 'lv' ? 'Rediģēt adresi' : 'Edit Address' }}
                            </Link>
                        </div>
                    </div>

                    <!-- No Address - Add Button -->
                    <div v-else class="no-address-state">
                        <div class="no-address-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h3 class="no-address-title">
                            {{ currentLocale === 'lv' ? 'Nav pievienota adrese' : 'No Address Added' }}
                        </h3>
                        <p class="no-address-text">
                            {{ currentLocale === 'lv'
                            ? 'Pievienojiet savu piegādes adresi, lai varētu veikt pasūtījumus.'
                            : 'Add your delivery address to be able to place orders.'
                            }}
                        </p>
                        <Link href="/profile/edit" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            {{ currentLocale === 'lv' ? 'Pievienot adresi' : 'Add Address' }}
                        </Link>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div v-if="activeSection === 'reviews'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.reviews.title') }}</h2>

                    <!-- Reviews List -->
                    <div v-if="recentReviews && recentReviews.length > 0" class="reviews-full-list">
                        <div v-for="review in recentReviews" :key="review.id" class="review-card">
                            <div class="review-card-header">
                                <div class="review-rating-stars">
                                    <i v-for="n in 5" :key="n"
                                       :class="['fas fa-star', n <= review.rating ? 'star-filled' : 'star-empty']">
                                    </i>
                                </div>
                                <span class="review-card-date">{{ formatDate(review.created_at) }}</span>
                            </div>

                            <p class="review-card-text">
                                {{ currentLocale === 'lv' ? review.review_text_lv : review.review_text_en }}
                            </p>

                            <div v-if="review.reviewable" class="review-card-item">
                                <i :class="review.reviewable.type === 'Product' ? 'fas fa-box' : 'fas fa-file-alt'"></i>
                                <span class="review-item-name">
                                    <span v-if="review.reviewable.type === 'Product'">
                                        {{ currentLocale === 'lv' ? review.reviewable.name_lv : review.reviewable.name_en }}
                                    </span>
                                    <span v-else>
                                        {{ currentLocale === 'lv' ? review.reviewable.title_lv : review.reviewable.title_en }}
                                    </span>
                                </span>
                                <span class="review-item-type">{{ review.reviewable.type }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state-large">
                        <div class="empty-state-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="empty-state-title">
                            {{ currentLocale === 'lv' ? 'Nav atsauksmju' : 'No Reviews Yet' }}
                        </h3>
                        <p class="empty-state-text">
                            {{ currentLocale === 'lv'
                            ? 'Jūs vēl neesat atstājis nevienu atsauksmi par produktiem vai saturu.'
                            : 'You haven\'t written any reviews for products or content yet.'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Comments Section -->
                <div v-if="activeSection === 'comments'" class="section-content">
                    <h2 class="section-title">
                        {{ currentLocale === 'lv' ? 'Mani komentāri' : 'My Comments' }}
                    </h2>

                    <!-- Comments List -->
                    <div v-if="recentComments && recentComments.length > 0" class="comments-full-list">
                        <div v-for="comment in recentComments" :key="comment.id" class="comment-card">
                            <div class="comment-card-header">
                                <div class="comment-author">
                                    <div class="comment-avatar">
                                        <img :src="userAvatar" :alt="user.username">
                                    </div>
                                    <div class="comment-author-info">
                                        <span class="comment-username">{{ user.username }}</span>
                                        <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="comment-card-body">
                                <p class="comment-text">{{ comment.comment_text }}</p>
                            </div>

                            <div v-if="comment.content" class="comment-card-footer">
                                <div class="comment-item-info">
                                    <i class="fas fa-file-alt"></i>
                                    <span class="comment-item-label">
                        {{ currentLocale === 'lv' ? 'Komentēts pie:' : 'Commented on:' }}
                    </span>
                                    <Link
                                        :href="`/content/${comment.content.slug}`"
                                        class="comment-item-link"
                                    >
                                        {{ currentLocale === 'lv' ? comment.content.title_lv : comment.content.title_en }}
                                    </Link>
                                    <span class="comment-content-badge">
                        {{ currentLocale === 'lv' ? 'Saturs' : 'Content' }}
                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="empty-state-large">
                        <div class="empty-state-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="empty-state-title">
                            {{ currentLocale === 'lv' ? 'Nav komentāru' : 'No Comments Yet' }}
                        </h3>
                        <p class="empty-state-text">
                            {{ currentLocale === 'lv'
                            ? 'Jūs vēl neesat atstājis nevienu komentāru pie satura.'
                            : 'You haven\'t written any comments on content yet.'
                            }}
                        </p>
                        <div class="empty-state-actions">
                            <Link href="/content" class="btn btn-primary">
                                <i class="fas fa-book"></i>
                                {{ currentLocale === 'lv' ? 'Skatīt saturu' : 'View Content' }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Settings Section -->
                <div v-if="activeSection === 'settings'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.settings.title') }}</h2>

                    <div class="settings-card">
                        <h3 class="settings-heading">{{ $t('dashboard.settings.notifications') }}</h3>
                        <div class="settings-option">
                            <label class="setting-label">
                                <input type="checkbox" checked>
                                <span>{{ $t('dashboard.settings.email_notifications') }}</span>
                            </label>
                        </div>
                        <div class="settings-option">
                            <label class="setting-label">
                                <input type="checkbox" checked>
                                <span>{{ $t('dashboard.settings.order_updates') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-card">
                        <h3 class="settings-heading">{{ $t('dashboard.settings.privacy') }}</h3>

                        <div class="settings-option">
                            <label class="setting-label">
                                <input type="checkbox" v-model="isPrivateProfile">
                                <span>{{ currentLocale === 'lv' ? 'Privāts profils' : 'Private Profile' }}</span>
                            </label>
                            <p class="setting-description">
                                {{ currentLocale === 'lv'
                                ? 'Citi lietotāji nevarēs redzēt jūsu profilu un aktivitātes'
                                : 'Other users will not be able to view your profile and activities'
                                }}
                            </p>
                        </div>

                        <div class="settings-save">
                            <button @click="savePrivacySettings" class="btn btn-primary" :disabled="savingSettings">
                                <i :class="savingSettings ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                                {{ savingSettings
                                ? (currentLocale === 'lv' ? 'Saglabā...' : 'Saving...')
                                : (currentLocale === 'lv' ? 'Saglabāt iestatījumus' : 'Save Settings')
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* ========== DASHBOARD CONTAINER ========== */
.dashboard {
    min-height: 100vh;
    background-color: #f9fafb;
}

/* ========== DASHBOARD HEADER ========== */
.dashboard-header {
    background-color: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
}

.dashboard-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
}

.dashboard-logo {
    height: 3rem;
    width: auto;
}

.dashboard-brand-name {
    height: 1.75rem;
    width: auto;
}

@media (max-width: 768px) {
    .dashboard-brand-name {
        display: none;
    }
}

.dashboard-welcome {
    flex: 1;
}

.welcome-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.welcome-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0.25rem 0 0;
}

/* Locale Switcher */
.header-locale-switcher {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 1rem;
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.875rem;
}

.header-locale-switcher:hover {
    background-color: #fef2f2;
    border-color: #dc2626;
}

.locale-current {
    color: #dc2626;
    font-weight: 700;
}

.locale-divider {
    color: #d1d5db;
}

.locale-other {
    color: #9ca3af;
    font-weight: 500;
}

@media (max-width: 640px) {
    .header-locale-switcher {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }
}

/* ========== DASHBOARD STATS ========== */
.dashboard-stats {
    padding: 2rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    font-size: 1.5rem;
    color: white;
}

.stat-icon-orders {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon-reviews {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon-favorites {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.stat-icon-cart {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

/* ========== DASHBOARD CONTENT ========== */
.dashboard-content {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    padding: 2rem;
}

@media (max-width: 1024px) {
    .dashboard-content {
        grid-template-columns: 1fr;
    }
}

/* ========== SIDEBAR ========== */
.dashboard-sidebar {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

@media (max-width: 1024px) {
    .dashboard-sidebar {
        position: static;
    }
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #6b7280;
    font-weight: 500;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    text-decoration: none;
}

.sidebar-link:hover {
    background-color: #fef2f2;
    color: #dc2626;
}

.sidebar-link.active {
    background-color: #dc2626;
    color: white;
}

.sidebar-link i {
    font-size: 1.125rem;
}

.sidebar-link-back {
    margin-top: 1rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1.5rem;
}

/* ========== MAIN CONTENT ========== */
.dashboard-main {
    background-color: white;
    border-radius: 0.5rem;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-content {
    /* Content styling */
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
}

/* ========== OVERVIEW GRID ========== */
.overview-grid {
    display: grid;
    gap: 1.5rem;
}

.overview-card {
    background-color: #f9fafb;
    border-radius: 0.5rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.card-link {
    font-size: 0.875rem;
    color: #dc2626;
    text-decoration: none;
    font-weight: 500;
}

.card-link:hover {
    text-decoration: underline;
}

.card-body {
    /* Body styling */
}

.empty-state {
    text-align: center;
    color: #9ca3af;
    padding: 2rem;
}

/* ========== QUICK ACTIONS ========== */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

@media (max-width: 768px) {
    .quick-actions {
        grid-template-columns: 1fr;
    }
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background-color: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #6b7280;
}

.action-btn:hover {
    border-color: #dc2626;
    background-color: #fef2f2;
    color: #dc2626;
}

.action-btn i {
    font-size: 1.5rem;
}

/* ========== PROFILE CARD ========== */
.profile-card {
    background-color: #f9fafb;
    border-radius: 0.5rem;
    padding: 2rem;
    border: 1px solid #e5e7eb;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.profile-avatar {
    flex-shrink: 0;
}

.profile-avatar img {
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
    border: 3px solid #dc2626;
    object-fit: cover;
}

.profile-info {
    flex: 1;
}

.profile-username {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
}

.profile-email {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.5rem;
}

.profile-role {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background-color: #dc2626;
    color: white;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.profile-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-label {
    font-weight: 600;
    color: #6b7280;
}

.detail-value {
    color: #111827;
}

.profile-actions {
    display: flex;
    gap: 1rem;
}

@media (max-width: 640px) {
    .profile-actions {
        flex-direction: column;
    }
}

/* ========== BUTTONS ========== */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background-color: #dc2626;
    color: white;
}

.btn-primary:hover {
    background-color: #b91c1c;
}

.btn-secondary {
    background-color: white;
    color: #6b7280;
    border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
    border-color: #dc2626;
    color: #dc2626;
}

/* ========== SETTINGS ========== */
.settings-card {
    background-color: #f9fafb;
    border-radius: 0.5rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}

.settings-heading {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1rem;
}

.settings-option {
    margin-bottom: 1rem;
}

.setting-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    color: #374151;
}

.setting-label input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
}
/* Orders Preview */
.orders-preview-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.order-preview-item {
    padding: 1rem;
    background: white;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}

.order-preview-item:hover {
    border-color: #dc2626;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
}

.order-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.order-preview-number {
    font-weight: 700;
    color: #111827;
    font-size: 0.875rem;
}

.order-preview-status {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-confirmed { background: #dbeafe; color: #1e40af; }
.status-processing { background: #e0e7ff; color: #4338ca; }
.status-packed { background: #e9d5ff; color: #6b21a8; }
.status-shipped { background: #fed7aa; color: #9a3412; }
.status-in_transit { background: #ccfbf1; color: #115e59; }
.status-delivered { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-refunded { background: #f3f4f6; color: #374151; }

.order-preview-details {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.order-preview-total {
    font-weight: 700;
    color: #dc2626;
    font-size: 0.875rem;
}

.order-preview-link {
    color: #dc2626;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
}

.order-preview-link:hover {
    text-decoration: underline;
}

/* Current Address Card */
.current-address-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.address-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    color: #111827;
}

.address-card-header i {
    color: #dc2626;
    font-size: 1.25rem;
}

.address-badge {
    margin-left: auto;
    padding: 0.25rem 0.75rem;
    background: #d1fae5;
    color: #065f46;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.address-card-body {
    padding: 1.5rem;
}

.address-line {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: #374151;
}

.address-line:last-child {
    margin-bottom: 0;
}

.address-line i {
    color: #9ca3af;
    width: 1.25rem;
}

.address-card-actions {
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

/* No Address State */
.no-address-state {
    text-align: center;
    padding: 3rem 2rem;
}

.no-address-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.no-address-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.no-address-text {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

/* Reviews Full List */
.reviews-full-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.review-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.5rem;
    transition: all 0.2s;
}

.review-card:hover {
    border-color: #fca5a5;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.05);
}

.review-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.review-rating-stars {
    display: flex;
    gap: 0.25rem;
}

.star-filled {
    color: #fbbf24;
}

.star-empty {
    color: #e5e7eb;
}

.review-card-date {
    font-size: 0.75rem;
    color: #9ca3af;
}

.review-card-text {
    color: #374151;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.review-card-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.375rem;
}

.review-card-item i {
    color: #dc2626;
    font-size: 1rem;
}

.review-item-name {
    flex: 1;
    font-weight: 500;
    color: #111827;
    font-size: 0.875rem;
}

.review-item-type {
    padding: 0.25rem 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 600;
}

/* Empty State Large */
.empty-state-large {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-state-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.empty-state-text {
    color: #6b7280;
    max-width: 500px;
    margin: 0 auto;
}

/* Setting Description */
.setting-description {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
    margin-left: 2rem;
}

.settings-save {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.comments-full-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.comment-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
    transition: all 0.2s;
}

.comment-card:hover {
    border-color: #93c5fd;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
}

/* Comment Header */
.comment-card-header {
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-bottom: 1px solid #e5e7eb;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.comment-avatar {
    flex-shrink: 0;
}

.comment-avatar img {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    border: 2px solid #dc2626;
    object-fit: cover;
}

.comment-author-info {
    display: flex;
    flex-direction: column;
}

.comment-username {
    font-weight: 600;
    color: #111827;
    font-size: 0.875rem;
}

.comment-date {
    font-size: 0.75rem;
    color: #9ca3af;
}

/* Comment Body */
.comment-card-body {
    padding: 1.5rem;
}

.comment-text {
    color: #374151;
    line-height: 1.7;
    margin: 0;
    font-size: 0.9375rem;
    word-wrap: break-word;
}

/* Comment Footer */
.comment-card-footer {
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.comment-item-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.comment-item-info i {
    color: #3b82f6;
    font-size: 1rem;
}

.comment-item-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
}

.comment-item-link {
    flex: 1;
    font-weight: 600;
    color: #1e40af;
    font-size: 0.875rem;
    text-decoration: none;
    transition: color 0.2s;
}

.comment-item-link:hover {
    color: #3b82f6;
    text-decoration: underline;
}

.comment-content-badge {
    padding: 0.25rem 0.625rem;
    background: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    color: #1e40af;
    font-weight: 600;
}

/* Empty State Actions */
.empty-state-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}

@media (max-width: 640px) {
    .empty-state-actions {
        flex-direction: column;
        align-items: center;
    }

    .empty-state-actions .btn {
        width: 100%;
        max-width: 300px;
    }
}

/* Nav Badge */
.nav-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #dc2626;
    color: white;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    margin-left: auto;
}

</style>
