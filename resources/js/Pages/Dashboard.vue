<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    auth: Object,
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

// Stats (mockup - replace with real data from backend)
const stats = ref({
    orders: 5,
    reviews: 3,
    favorites: 12,
    cart_items: 2,
});
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
                                <p class="empty-state">{{ $t('dashboard.sections.overview.no_orders') }}</p>
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
                    <p class="empty-state">{{ $t('dashboard.sections.orders.no_orders') }}</p>
                </div>

                <!-- Addresses Section -->
                <div v-if="activeSection === 'addresses'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.addresses.title') }}</h2>
                    <p class="empty-state">{{ $t('dashboard.sections.addresses.no_addresses') }}</p>
                    <Link href="/profile/addresses/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        {{ $t('dashboard.actions.add_address') }}
                    </Link>
                </div>

                <!-- Reviews Section -->
                <div v-if="activeSection === 'reviews'" class="section-content">
                    <h2 class="section-title">{{ $t('dashboard.sections.reviews.title') }}</h2>
                    <p class="empty-state">{{ $t('dashboard.sections.reviews.no_reviews') }}</p>
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
                                <input type="checkbox">
                                <span>{{ $t('dashboard.settings.show_profile') }}</span>
                            </label>
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
</style>
