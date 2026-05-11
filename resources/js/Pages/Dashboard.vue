<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import SubscriberOffers from '@/Components/SubscriberOffers.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';

// ── Toast helper ─────────────────────────────────────────────────
const toast = ref({ show: false, message: '', type: 'success' });

const showToast = (message, type = 'success') => {
    toast.value = { show: false, message, type };
    setTimeout(() => { toast.value = { show: true, message, type }; }, 10);
};

const closeToast = () => { toast.value.show = false; };

const props = defineProps({
    auth: Object,
    stats: Object,              // Real stats from backend
    recentOrders: Array,        // Last 5 orders
    recentReviews: Array,       // Last 5 reviews
    recentComments: Array,      // Last 5 comments
    hasAddress: Boolean,        // User has address filled
    newsletterStatus: Object,   // Real newsletter stats from backend
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

    showToast(
        currentLocale.value === 'lv'
            ? 'Valoda nomainīta uz latviešu'
            : 'Language changed to English',
        'success'
    );

    window.axios.put('/profile/locale', { locale: currentLocale.value });
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

// ── E-pasta verifikācija ──────────────────────────────────────────
const isSendingVerification = ref(false);
const verificationSent = ref(false);

const resendVerification = async () => {
    isSendingVerification.value = true;
    try {
        await axios.post(route('verification.send'));
        verificationSent.value = true;
        showToast(
            currentLocale.value === 'lv'
                ? 'Apstiprinājuma e-pasts nosūtīts! Pārbaudiet savu pastkasti.'
                : 'Verification email sent! Please check your inbox.',
            'success'
        );
    } catch (error) {
        showToast(
            currentLocale.value === 'lv'
                ? 'Neizdevās nosūtīt e-pastu. Mēģiniet vēlreiz.'
                : 'Failed to send email. Please try again.',
            'error'
        );
    } finally {
        isSendingVerification.value = false;
    }
};

// ── Komentāru noskaņojuma helpers ────────────────────────────────
const getMoodEmoji = (score) => {
    if (score === null || score === undefined) return '😶';
    if (score <= 10)  return '😡';
    if (score <= 25)  return '😠';
    if (score <= 40)  return '😕';
    if (score <= 55)  return '😐';
    if (score <= 70)  return '🙂';
    if (score <= 85)  return '😊';
    return '🤩';
};

const getMoodColor = (score) => {
    if (score === null || score === undefined) return '#9ca3af';
    const r = score < 50 ? 220 : Math.round(220 - (score - 50) / 50 * 180);
    const g = score < 50 ? Math.round(score / 50 * 185) : 185;
    return `rgb(${r}, ${g}, 38)`;
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

        showToast(
            currentLocale.value === 'lv' ? 'Iestatījumi veiksmīgi saglabāti!' : 'Settings saved successfully!',
            'success'
        );
    } catch (error) {
        console.error('Failed to save settings:', error);
        showToast(
            currentLocale.value === 'lv' ? 'Neizdevās saglabāt iestatījumus' : 'Failed to save settings',
            'error'
        );
    } finally {
        savingSettings.value = false;
    }
};
</script>

<template>
    <Head :title="$t('auth.dashboard')" />

    <!-- Toast Notification -->
    <ToastNotification
        :show="toast.show"
        :message="toast.message"
        :type="toast.type"
        @close="closeToast"
    />

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

        <!-- ── E-pasta verifikācijas brīdinājums ── -->
        <Transition name="verify-banner">
            <div v-if="user.email_verified_at === null" class="verify-banner">
                <div class="verify-banner-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="verify-banner-content">
                    <strong>{{ currentLocale === 'lv' ? 'E-pasts nav apstiprināts!' : 'Email not verified!' }}</strong>
                    <span>{{ currentLocale === 'lv'
                        ? 'Apstipriniet savu e-pasta adresi, lai piekļūtu visām funkcijām.'
                        : 'Verify your email address to unlock all features.' }}</span>
                </div>
                <div class="verify-banner-actions">
                    <span v-if="verificationSent" class="verify-sent-msg">
                        <i class="fas fa-check-circle"></i>
                        {{ currentLocale === 'lv' ? 'E-pasts nosūtīts!' : 'Email sent!' }}
                    </span>
                    <button
                        v-else
                        @click="resendVerification"
                        class="verify-banner-btn"
                        :disabled="isSendingVerification"
                    >
                        <i :class="isSendingVerification ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                        {{ isSendingVerification
                        ? (currentLocale === 'lv' ? 'Sūta...' : 'Sending...')
                        : (currentLocale === 'lv' ? 'Nosūtīt apstiprinājumu' : 'Send Verification') }}
                    </button>
                    <Link href="/profile/edit" class="verify-banner-link">
                        <i class="fas fa-user-cog"></i>
                        {{ currentLocale === 'lv' ? 'Konta iestatījumi' : 'Account Settings' }}
                    </Link>
                </div>
            </div>
        </Transition>

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
                <div class="stat-icon stat-icon-comments">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.comments }}</div>
                    <div class="stat-label">{{ $t('dashboard.stats.comments') }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-replies">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.replies_received }}</div>
                    <div class="stat-label">{{ currentLocale === 'lv' ? 'Manas atbildes' : 'My replies' }}</div>
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
                        @click="setActiveSection('newsletter')"
                        class="sidebar-link"
                        :class="{ active: activeSection === 'newsletter' }"
                    >
                        <i class="fas fa-envelope"></i>
                        <span>{{ currentLocale === 'lv' ? 'Jaunumi' : 'Newsletter' }}</span>
                        <span v-if="props.newsletterStatus?.subscribed" class="nav-badge nav-badge-green">✓</span>
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
                                <Link href="/orders" class="card-link">
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
                                <div class="profile-badges">
                                    <span class="profile-role">{{ user.role?.display_name_lv || 'Customer' }}</span>
                                    <span v-if="isPrivateProfile" class="profile-privacy-badge profile-privacy-badge--private">
                                        <i class="fas fa-lock"></i>
                                        {{ currentLocale === 'lv' ? 'Privāts' : 'Private' }}
                                    </span>
                                    <span v-else class="profile-privacy-badge profile-privacy-badge--public">
                                        <i class="fas fa-globe"></i>
                                        {{ currentLocale === 'lv' ? 'Publisks' : 'Public' }}
                                    </span>
                                </div>
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

                        <!-- Privātuma statusa paziņojums -->
                        <div class="profile-privacy-notice" :class="isPrivateProfile ? 'notice-private' : 'notice-public'">
                            <div class="notice-icon">
                                <i :class="isPrivateProfile ? 'fas fa-user-lock' : 'fas fa-user-check'"></i>
                            </div>
                            <div class="notice-body">
                                <strong>
                                    {{ isPrivateProfile
                                    ? (currentLocale === 'lv' ? 'Privāts profils' : 'Private profile')
                                    : (currentLocale === 'lv' ? 'Publisks profils' : 'Public profile') }}
                                </strong>
                                <span>
                                    {{ isPrivateProfile
                                    ? (currentLocale === 'lv'
                                        ? 'Citi lietotāji nevar redzēt jūsu profilu, komentārus un aktivitātes.'
                                        : 'Other users cannot see your profile, comments or activity.')
                                    : (currentLocale === 'lv'
                                        ? 'Jūsu profils un aktivitātes ir redzamas citiem lietotājiem.'
                                        : 'Your profile and activity are visible to other users.') }}
                                </span>
                            </div>
                            <button @click="setActiveSection('settings')" class="notice-settings-btn">
                                <i class="fas fa-cog"></i>
                                {{ currentLocale === 'lv' ? 'Mainīt' : 'Change' }}
                            </button>
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

                    <div v-if="recentReviews && recentReviews.length > 0" class="reviews-full-list">
                        <div v-for="review in recentReviews" :key="review.id" class="review-card">

                            <!-- Header: zvaigznes + apstiprināšanas statuss + datums -->
                            <div class="review-card-header">
                                <div class="review-rating-stars">
                                    <i v-for="n in 5" :key="n"
                                       :class="['fas fa-star', n <= review.rating ? 'star-filled' : 'star-empty']">
                                    </i>
                                </div>
                                <div class="review-header-right">
                                    <span v-if="!review.is_approved" class="review-pending-badge">
                                        <i class="fas fa-clock"></i>
                                        {{ currentLocale === 'lv' ? 'Gaida apstiprinājumu' : 'Pending approval' }}
                                    </span>
                                    <span class="review-card-date">{{ formatDate(review.created_at) }}</span>
                                </div>
                            </div>

                            <!-- Atsauksmes teksts -->
                            <p class="review-card-text">
                                {{ currentLocale === 'lv'
                                ? (review.review_text_lv || review.review_text_en || '—')
                                : (review.review_text_en || review.review_text_lv || '—') }}
                            </p>

                            <!-- Produkts vai saturs — ar linku un tipa badge -->
                            <div v-if="review.reviewable" class="review-card-item">
                                <i :class="review.reviewable.type === 'Product'
                                    ? 'fas fa-box'
                                    : 'fas fa-file-alt'">
                                </i>
                                <span class="review-item-name">
                                    <a
                                        v-if="review.reviewable.link"
                                        :href="review.reviewable.link"
                                        class="review-item-link"
                                    >
                                        {{ currentLocale === 'lv'
                                        ? review.reviewable.name_lv
                                        : review.reviewable.name_en }}
                                    </a>
                                    <span v-else>
                                        {{ currentLocale === 'lv'
                                        ? review.reviewable.name_lv
                                        : review.reviewable.name_en }}
                                    </span>
                                </span>
                                <span
                                    class="review-item-type"
                                    :class="review.reviewable.type === 'Product'
                                        ? 'review-type-product'
                                        : 'review-type-content'"
                                >
                                    {{ review.reviewable.type === 'Product'
                                    ? (currentLocale === 'lv' ? 'Produkts' : 'Product')
                                    : (currentLocale === 'lv' ? 'Saturs' : 'Content') }}
                                </span>
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
                            : "You haven't written any reviews for products or content yet." }}
                        </p>
                        <div class="empty-state-actions">
                            <Link href="/shop" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i>
                                {{ currentLocale === 'lv' ? 'Doties uz veikalu' : 'Visit Shop' }}
                            </Link>
                            <Link href="/content" class="btn btn-secondary">
                                <i class="fas fa-book"></i>
                                {{ currentLocale === 'lv' ? 'Skatīt saturu' : 'View Content' }}
                            </Link>
                        </div>
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
                            <!-- Header -->
                            <div class="comment-card-header">
                                <div class="comment-author">
                                    <div class="comment-avatar">
                                        <img
                                            :src="comment.is_my_comment ? userAvatar : (comment.author?.profile_picture || '/img/default-avatar.png')"
                                            :alt="comment.is_my_comment ? user.username : comment.author?.username"
                                            @error="$event.target.src='/img/default-avatar.png'"
                                        >
                                    </div>
                                    <div class="comment-author-info">
                                        <span class="comment-username">
                                            {{ comment.is_my_comment ? user.username : comment.author?.username }}
                                            <span v-if="!comment.is_my_comment" class="comment-participated-tag">
                                                <i class="fas fa-reply"></i>
                                                {{ currentLocale === 'lv' ? 'Tu atbildēji' : 'You replied' }}
                                            </span>
                                        </span>
                                        <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
                                    </div>
                                </div>
                                <!-- Atbildes skaits badge -->
                                <div v-if="comment.replies_count > 0" class="comment-replies-badge">
                                    <i class="fas fa-reply"></i>
                                    {{ comment.replies_count }}
                                    {{ currentLocale === 'lv'
                                    ? (comment.replies_count === 1 ? 'atbilde' : 'atbildes')
                                    : (comment.replies_count === 1 ? 'reply' : 'replies') }}
                                </div>
                            </div>

                            <!-- Teksts -->
                            <div class="comment-card-body">
                                <p class="comment-text">{{ comment.comment_text }}</p>

                                <!-- Noskaņojuma sliders (tikai rādīšanai) -->
                                <div class="comment-mood-display"
                                     :class="{ 'mood-unset': comment.my_mood_score === null || comment.my_mood_score === undefined }">
                                    <span class="mood-emoji">{{ getMoodEmoji(comment.my_mood_score) }}</span>
                                    <div class="mood-bar-track">
                                        <div
                                            class="mood-bar-fill"
                                            :style="{
                                                width: (comment.my_mood_score ?? 0) + '%',
                                                background: getMoodColor(comment.my_mood_score),
                                                opacity: (comment.my_mood_score !== null && comment.my_mood_score !== undefined) ? 1 : 0
                                            }"
                                        ></div>
                                    </div>
                                    <span class="mood-pct"
                                          :style="{ color: getMoodColor(comment.my_mood_score) }">
                                        <template v-if="comment.my_mood_score !== null && comment.my_mood_score !== undefined">
                                            {{ comment.my_mood_score }}%
                                        </template>
                                        <template v-else>
                                            {{ currentLocale === 'lv' ? 'Nav vērtēts' : 'Not rated' }}
                                        </template>
                                    </span>

                                    <!-- Vidējais (ja ir) -->
                                    <span v-if="comment.avg_mood_score !== null && comment.avg_mood_score !== undefined"
                                          class="mood-avg-chip"
                                          :style="{ color: getMoodColor(comment.avg_mood_score) }">
                                        {{ currentLocale === 'lv' ? 'Vid.' : 'Avg' }}
                                        {{ comment.avg_mood_score }}%
                                        <span class="mood-avg-count">({{ comment.mood_count ?? 0 }})</span>
                                    </span>
                                </div>

                                <!-- Atbildes — pilnas, visas redzamas -->
                                <div v-if="comment.replies && comment.replies.length > 0" class="comment-replies-full">
                                    <div class="replies-full-label">
                                        <i class="fas fa-reply"></i>
                                        {{ comment.replies_count }}
                                        {{ currentLocale === 'lv'
                                        ? (comment.replies_count === 1 ? 'atbilde' : 'atbildes')
                                        : (comment.replies_count === 1 ? 'reply' : 'replies') }}
                                    </div>
                                    <div class="replies-full-list">
                                        <div v-for="reply in comment.replies" :key="reply.id" class="reply-full-item">
                                            <div class="reply-full-connector">
                                                <div class="reply-full-line"></div>
                                            </div>
                                            <img
                                                :src="reply.user?.profile_picture || '/img/default-avatar.png'"
                                                class="reply-full-avatar"
                                                @error="$event.target.src='/img/default-avatar.png'"
                                            >
                                            <div class="reply-full-body">
                                                <div class="reply-full-header">
                                                    <span class="reply-full-author"
                                                          :class="{ 'reply-author-me': reply.user?.username === user.username }">
                                                        {{ reply.user?.username }}
                                                        <span v-if="reply.user?.username === user.username" class="reply-me-tag">
                                                            {{ currentLocale === 'lv' ? 'Tu' : 'You' }}
                                                        </span>
                                                    </span>
                                                    <span class="reply-full-date">{{ formatDate(reply.created_at) }}</span>
                                                    <span class="reply-full-mood"
                                                          :class="{ 'mood-unset-icon': reply.my_mood_score === null || reply.my_mood_score === undefined }"
                                                          :title="reply.my_mood_score !== null && reply.my_mood_score !== undefined ? reply.my_mood_score + '%' : (currentLocale === 'lv' ? 'Nav vērtēts' : 'Not rated')">
                                                        {{ getMoodEmoji(reply.my_mood_score) }}
                                                    </span>
                                                </div>
                                                <p class="reply-full-text">{{ reply.comment_text }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <Link
                                        :href="`/content/${comment.content?.slug}`"
                                        class="replies-view-all-link"
                                    >
                                        <i class="fas fa-external-link-alt"></i>
                                        {{ currentLocale === 'lv' ? 'Skatīt pilnu diskusiju' : 'View full discussion' }}
                                    </Link>
                                </div>
                            </div>

                            <!-- Footer: satura links -->
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

                <!-- Newsletter section -->
                <div v-if="activeSection === 'newsletter'" class="section-content">
                    <h2 class="section-title">
                        {{ currentLocale === 'lv' ? 'Jaunumu abonēšana' : 'Newsletter Subscription' }}
                    </h2>

                    <!-- Status Card -->
                    <div
                        class="nl-status-card"
                        :class="props.newsletterStatus?.subscribed ? 'nl-status-active' : 'nl-status-inactive'"
                    >
                        <div class="nl-status-icon">
                            <i :class="props.newsletterStatus?.subscribed
                ? 'fas fa-envelope-open-text'
                : 'fas fa-envelope'">
                            </i>
                        </div>
                        <div class="nl-status-body">
                            <h3 class="nl-status-title">
                                {{ props.newsletterStatus?.subscribed
                                ? (currentLocale === 'lv' ? '🎉 Tu esi aktīvs abonents!' : '🎉 You are an active subscriber!')
                                : (currentLocale === 'lv' ? 'Tu neesi abonējis jaunumus' : 'You are not subscribed')
                                }}
                            </h3>

                            <!-- Expiry details -->
                            <template v-if="props.newsletterStatus?.subscribed">
                                <div
                                    v-if="props.newsletterStatus?.days_remaining !== null"
                                    class="nl-expiry-row"
                                    :class="{
                        'nl-expiry-warning': props.newsletterStatus.days_remaining <= 30,
                        'nl-expiry-ok': props.newsletterStatus.days_remaining > 30
                    }"
                                >
                                    <i class="fas fa-clock"></i>
                                    <span v-if="props.newsletterStatus.days_remaining <= 7" class="nl-expiry-urgent">
                        ⚠️ {{ currentLocale === 'lv'
                                        ? `Abonēšana beidzas pēc ${props.newsletterStatus.days_remaining} dienām!`
                                        : `Subscription expires in ${props.newsletterStatus.days_remaining} days!`
                                        }}
                    </span>
                                    <span v-else-if="props.newsletterStatus.days_remaining <= 30">
                        {{ currentLocale === 'lv'
                                        ? `Beidzas drīz — ${props.newsletterStatus.days_remaining} dienas (${props.newsletterStatus.expires_at})`
                                        : `Expiring soon — ${props.newsletterStatus.days_remaining} days (${props.newsletterStatus.expires_at})`
                                        }}
                    </span>
                                    <span v-else>
                        {{ currentLocale === 'lv'
                                        ? `Derīgs līdz: ${props.newsletterStatus.expires_at}`
                                        : `Valid until: ${props.newsletterStatus.expires_at}`
                                        }}
                    </span>
                                </div>
                                <div v-else class="nl-expiry-row nl-expiry-ok">
                                    <i class="fas fa-infinity"></i>
                                    <span>{{ currentLocale === 'lv' ? 'Abonēšana bez termiņa' : 'No expiry date' }}</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- SubscriberOffers component -->
                    <SubscriberOffers :userEmail="user.email" />
                </div>

            </main>
        </div>
    </div>
</template>

<style scoped>
/* ── E-pasta verifikācijas brīdinājums ─────────────────────────── */
.verify-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border: 1px solid #fcd34d;
    border-left: 4px solid #f59e0b;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.verify-banner-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: #fef3c7;
    border: 2px solid #fcd34d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d97706;
    font-size: 1rem;
    flex-shrink: 0;
}

.verify-banner-content {
    flex: 1;
    min-width: 200px;
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.verify-banner-content strong {
    color: #92400e;
    font-size: 0.9rem;
}

.verify-banner-content span {
    color: #78350f;
    font-size: 0.8rem;
    opacity: 0.9;
}

.verify-banner-actions {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    flex-wrap: wrap;
}

.verify-banner-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.125rem;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.verify-banner-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #d97706, #b45309);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
}

.verify-banner-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.verify-banner-link {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    background: rgba(245, 158, 11, 0.12);
    color: #92400e;
    border: 1px solid #fcd34d;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}

.verify-banner-link:hover {
    background: rgba(245, 158, 11, 0.22);
    color: #78350f;
}

.verify-sent-msg {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    color: #059669;
    font-size: 0.8rem;
    font-weight: 600;
}

.verify-sent-msg i { color: #10b981; }

/* Banner transition */
.verify-banner-enter-active, .verify-banner-leave-active { transition: all 0.3s ease; }
.verify-banner-enter-from, .verify-banner-leave-to { opacity: 0; transform: translateY(-8px); }

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
    position: sticky;
    top: 0;
    z-index: 100;
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
        grid-template-columns: repeat(2, 1fr);  /* WAS: 1fr */
        padding: 1rem;
        gap: 0.75rem;
    }
    .stat-card {
        padding: 1rem;
    }
    .stat-value {
        font-size: 1.5rem;
    }
    .dashboard-content {
        padding: 1rem;
        gap: 1rem;
    }
    .dashboard-main {
        padding: 1rem;
    }
    .welcome-title {
        font-size: 1.25rem;
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

.stat-icon-comments {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.stat-icon-replies {
    background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%);
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

/* Profila badges rinda */
.profile-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.375rem;
}

.profile-privacy-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 700;
}
.profile-privacy-badge--private {
    background: #1e1b4b;
    color: #a5b4fc;
    border: 1px solid #4338ca;
}
.profile-privacy-badge--public {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

/* Privātuma paziņojuma bloks */
.profile-privacy-notice {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.notice-private {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    border: 1px solid #4338ca;
    color: #c7d2fe;
}
.notice-public {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #86efac;
    color: #166534;
}
.notice-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
}
.notice-private .notice-icon {
    background: rgba(99, 102, 241, 0.25);
    color: #a5b4fc;
}
.notice-public .notice-icon {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
}
.notice-body {
    flex: 1;
    min-width: 150px;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}
.notice-private .notice-body strong { color: #e0e7ff; font-size: 0.875rem; }
.notice-private .notice-body span   { color: #a5b4fc; font-size: 0.78rem; opacity: 0.85; }
.notice-public  .notice-body strong { color: #14532d; font-size: 0.875rem; }
.notice-public  .notice-body span   { color: #166534; font-size: 0.78rem; opacity: 0.85; }
.notice-settings-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.875rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}
.notice-private .notice-settings-btn {
    background: rgba(99, 102, 241, 0.3);
    color: #e0e7ff;
}
.notice-private .notice-settings-btn:hover {
    background: rgba(99, 102, 241, 0.55);
}
.notice-public .notice-settings-btn {
    background: rgba(16, 185, 129, 0.2);
    color: #065f46;
}
.notice-public .notice-settings-btn:hover {
    background: rgba(16, 185, 129, 0.35);
}

@media (max-width: 480px) {
    .profile-privacy-notice { gap: 0.75rem; }
    .notice-settings-btn { width: 100%; justify-content: center; }
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
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.review-header-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.review-pending-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.2rem 0.6rem;
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 0.375rem;
    font-size: 0.7rem;
    color: #92400e;
    font-weight: 600;
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
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.review-item-link {
    color: #1e40af;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: color 0.2s;
}

.review-item-link:hover {
    color: #3b82f6;
    text-decoration: underline;
}

.review-type-content {
    background: #dcfce7;
    border: 1px solid #86efac;
    color: #166534;
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
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.comment-participated-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    border-radius: 9999px;
}
.comment-participated-tag i { font-size: 0.55rem; }

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

/* ── Komentāru noskaņojums (Dashboard) ─────────────────────────── */
.comment-replies-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #1d4ed8;
    white-space: nowrap;
}
.comment-replies-badge i { font-size: 0.6rem; }

.comment-mood-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}
.comment-mood-display.mood-unset {
    opacity: 0.5;
    border-style: dashed;
}
.mood-emoji { font-size: 1.1rem; flex-shrink: 0; }
.mood-bar-track {
    flex: 1;
    height: 5px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
    min-width: 40px;
}
.mood-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s;
}
.mood-pct {
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    color: #9ca3af;
}
.mood-avg-chip {
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.4rem;
    background: white;
    border: 1px solid currentColor;
    border-radius: 9999px;
    white-space: nowrap;
    flex-shrink: 0;
    opacity: 0.8;
}
.mood-avg-count { font-weight: 400; color: #9ca3af; }

/* Replies — pilns skats */
.comment-replies-full {
    margin-top: 0.75rem;
    border-top: 1px dashed #e5e7eb;
    padding-top: 0.75rem;
}
.replies-full-label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 0.6rem;
    background: #eff6ff;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    border: 1px solid #bfdbfe;
}
.replies-full-list {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
}
.reply-full-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    position: relative;
    padding-bottom: 0.5rem;
}
.reply-full-item:last-child { padding-bottom: 0; }
.reply-full-connector {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 1rem;
    flex-shrink: 0;
    padding-top: 0.75rem;
}
.reply-full-line {
    width: 2px;
    flex: 1;
    min-height: 1.5rem;
    background: #bfdbfe;
    border-radius: 1px;
}
.reply-full-item:last-child .reply-full-line { display: none; }
.reply-full-avatar {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid #bfdbfe;
    margin-top: 0.15rem;
}
.reply-full-body {
    flex: 1;
    min-width: 0;
    background: #f8faff;
    border: 1px solid #dbeafe;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
}
.reply-full-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.3rem;
    flex-wrap: wrap;
}
.reply-full-author {
    font-size: 0.75rem;
    font-weight: 700;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.reply-author-me { color: #dc2626; }
.reply-me-tag {
    font-size: 0.6rem;
    font-weight: 700;
    background: #dc2626;
    color: white;
    padding: 0.05rem 0.35rem;
    border-radius: 9999px;
    text-transform: uppercase;
}
.reply-full-date {
    font-size: 0.65rem;
    color: #9ca3af;
    margin-left: auto;
}
.reply-full-mood {
    font-size: 0.85rem;
    flex-shrink: 0;
}
.reply-full-text {
    font-size: 0.82rem;
    color: #374151;
    line-height: 1.5;
    margin: 0;
    word-break: break-word;
}
.replies-view-all-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.6rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #3b82f6;
    text-decoration: none;
    transition: color 0.15s;
}
.replies-view-all-link:hover { color: #1d4ed8; text-decoration: underline; }
.mood-unset-icon { filter: grayscale(1); opacity: 0.4; }

</style>
