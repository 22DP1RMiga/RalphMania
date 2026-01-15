<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

// Click outside directive
const vClickOutside = {
    beforeMount(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    },
};

const page = usePage();
const user = computed(() => page.props.auth.user);
const isSuperAdmin = computed(() => user.value?.is_super_admin || false);

// Get user avatar with correct path
const userAvatar = computed(() => {
    if (!user.value?.profile_picture) {
        return '/img/default-avatar.png';
    }
    if (user.value.profile_picture.startsWith('/')) {
        return user.value.profile_picture;
    }
    return `/storage/${user.value.profile_picture}`;
});

// i18n setup
const { t, locale } = useI18n({ useScope: 'global' });
const currentLocale = ref(localStorage.getItem('lang') || 'lv');
locale.value = currentLocale.value;

watch(currentLocale, (newLang) => {
    locale.value = newLang;
    localStorage.setItem('lang', newLang);
});

const toggleLocale = () => {
    currentLocale.value = currentLocale.value === 'lv' ? 'en' : 'lv';
};

// Sidebar state
const isSidebarOpen = ref(true);
const isMobileSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const toggleMobileSidebar = () => {
    isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
};

const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
};

// User dropdown
const isUserDropdownOpen = ref(false);

const toggleUserDropdown = () => {
    isUserDropdownOpen.value = !isUserDropdownOpen.value;
};

const closeUserDropdown = () => {
    isUserDropdownOpen.value = false;
};

const logout = () => {
    router.post(route('logout'));
};

// Navigation items with translation keys
const navItems = computed(() => {
    const items = [
        {
            nameKey: 'admin.nav.dashboard',
            icon: 'fas fa-tachometer-alt',
            route: 'admin.dashboard',
            url: '/admin/dashboard',
        },
        {
            nameKey: 'admin.nav.products',
            icon: 'fas fa-box',
            route: 'admin.products.index',
            url: '/admin/products',
        },
        {
            nameKey: 'admin.nav.categories',
            icon: 'fas fa-tags',
            route: 'admin.categories.index',
            url: '/admin/categories',
        },
        {
            nameKey: 'admin.nav.orders',
            icon: 'fas fa-shopping-cart',
            route: 'admin.orders.index',
            url: '/admin/orders',
        },
        {
            nameKey: 'admin.nav.content',
            icon: 'fas fa-newspaper',
            route: 'admin.content.index',
            url: '/admin/content',
        },
        {
            nameKey: 'admin.nav.reviews',
            icon: 'fas fa-star',
            route: 'admin.reviews.index',
            url: '/admin/reviews',
        },
        {
            nameKey: 'admin.nav.comments',
            icon: 'fas fa-comments',
            route: 'admin.comments.index',
            url: '/admin/comments',
        },
        {
            nameKey: 'admin.nav.contacts',
            icon: 'fas fa-envelope',
            route: 'admin.contacts.index',
            url: '/admin/contacts',
        },
        {
            nameKey: 'admin.nav.users',
            icon: 'fas fa-users',
            route: 'admin.users.index',
            url: '/admin/users',
        },
        {
            nameKey: 'admin.nav.settings',
            icon: 'fas fa-gear',
            route: 'admin.settings.index',
            url: '/admin/settings',
        },
        {
            nameKey: 'admin.nav.logs',
            icon: 'fas fa-history',
            route: 'admin.logs.index',
            url: '/admin/logs',
        },
    ];

    // Add admin management for super admins only
    if (isSuperAdmin.value) {
        items.push({
            nameKey: 'admin.nav.administrators',
            icon: 'fas fa-user-shield',
            route: 'admin.administrators.index',
            url: '/admin/administrators',
            superAdminOnly: true,
        });
    }

    return items;
});

// Check if current route is active
const isActiveRoute = (url) => {
    return page.url.startsWith(url);
};
</script>

<template>
    <div class="admin-layout" :class="{ 'sidebar-collapsed': !isSidebarOpen }">
        <!-- Sidebar -->
        <aside class="admin-sidebar" :class="{ 'mobile-open': isMobileSidebarOpen }">
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <Link href="/admin/dashboard" class="sidebar-brand">
                    <img src="/img/RoltonsLV_Icon.png" alt="RalphMania" class="sidebar-logo">
                    <span v-if="isSidebarOpen" class="sidebar-brand-text">Admin</span>
                </Link>
                <button @click="toggleSidebar" class="sidebar-toggle desktop-only">
                    <i :class="isSidebarOpen ? 'fas fa-chevron-left' : 'fas fa-chevron-right'"></i>
                </button>
                <button @click="closeMobileSidebar" class="sidebar-close mobile-only">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="item.url"
                    class="sidebar-nav-item"
                    :class="{
                        'active': isActiveRoute(item.url),
                        'super-admin-item': item.superAdminOnly
                    }"
                    @click="closeMobileSidebar"
                >
                    <i :class="item.icon"></i>
                    <span v-if="isSidebarOpen">{{ t(item.nameKey) }}</span>
                </Link>
            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <Link href="/" class="sidebar-nav-item sidebar-back">
                    <i class="fas fa-arrow-left"></i>
                    <span v-if="isSidebarOpen">{{ t('admin.nav.backToSite') }}</span>
                </Link>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <Transition name="fade">
            <div
                v-if="isMobileSidebarOpen"
                class="sidebar-overlay"
                @click="closeMobileSidebar"
            ></div>
        </Transition>

        <!-- Main Content Area -->
        <div class="admin-main">
            <!-- Top Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button @click="toggleMobileSidebar" class="mobile-menu-btn mobile-only">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="admin-page-title">
                        <slot name="title">{{ t('admin.dashboard.title') }}</slot>
                    </h1>
                </div>

                <div class="admin-header-right">
                    <!-- Super Admin Badge -->
                    <div v-if="isSuperAdmin" class="super-admin-badge">
                        <i class="fas fa-crown"></i>
                        <span>Super Admin</span>
                    </div>

                    <!-- User Dropdown -->
                    <div class="admin-user-dropdown" v-click-outside="closeUserDropdown">
                        <button @click="toggleUserDropdown" class="admin-user-btn">
                            <img :src="userAvatar" :alt="user.username" class="admin-user-avatar">
                            <span class="admin-username">{{ user.username }}</span>
                            <i class="fas fa-chevron-down" :class="{ 'rotate-180': isUserDropdownOpen }"></i>
                        </button>

                        <Transition name="dropdown">
                            <div v-if="isUserDropdownOpen" class="admin-dropdown-menu">
                                <Link href="/dashboard" class="admin-dropdown-item" @click="closeUserDropdown">
                                    <i class="fas fa-user"></i>
                                    <span>{{ t('admin.user.myProfile') }}</span>
                                </Link>
                                <button @click="logout" class="admin-dropdown-item admin-dropdown-logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>{{ t('admin.user.logout') }}</span>
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <!-- Locale Switcher -->
                    <button @click="toggleLocale" class="admin-locale-switcher" :title="t('admin.switchLanguage')">
                        <span class="locale-current">{{ currentLocale.toUpperCase() }}</span>
                        <span class="locale-divider">/</span>
                        <span class="locale-other">{{ currentLocale === 'lv' ? 'EN' : 'LV' }}</span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="admin-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Layout */
.admin-layout {
    display: flex;
    min-height: 100vh;
    background: #f1f5f9;
}

/* Sidebar */
.admin-sidebar {
    width: 260px;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 100;
    transition: width 0.3s ease;
}

.sidebar-collapsed .admin-sidebar {
    width: 70px;
}

/* Sidebar Header */
.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
}

.sidebar-logo {
    width: 2.5rem;
    height: 2.5rem;
    object-fit: contain;
}

.sidebar-brand-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
}

.sidebar-toggle {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
}

.sidebar-close {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Sidebar Navigation */
.sidebar-nav {
    flex: 1;
    padding: 1rem 0.75rem;
    overflow-y: auto;
}

.sidebar-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: 0.5rem;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.sidebar-nav-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.sidebar-nav-item.active {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.sidebar-nav-item i {
    width: 1.25rem;
    text-align: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.sidebar-collapsed .sidebar-nav-item span {
    display: none;
}

/* Super Admin Item */
.super-admin-item {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.2) 0%, rgba(245, 158, 11, 0.2) 100%);
    border: 1px solid rgba(251, 191, 36, 0.3);
}

.super-admin-item:hover {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.3) 0%, rgba(245, 158, 11, 0.3) 100%);
}

.super-admin-item i {
    color: #fbbf24;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 1rem 0.75rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: auto;
}

.sidebar-back {
    color: rgba(255, 255, 255, 0.5);
}

.sidebar-back:hover {
    color: white;
}

/* Main Content Area */
.admin-main {
    flex: 1;
    margin-left: 260px;
    transition: margin-left 0.3s ease;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.sidebar-collapsed .admin-main {
    margin-left: 70px;
}

/* Admin Header */
.admin-header {
    background: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 50;
    gap: 1rem;
}

.admin-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
    flex: 1;
}

.mobile-menu-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #374151;
    cursor: pointer;
    padding: 0.5rem;
    flex-shrink: 0;
}

.admin-page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

/* Super Admin Badge */
.super-admin-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 2rem;
    color: #92400e;
    font-weight: 600;
    font-size: 0.875rem;
}

.super-admin-badge i {
    color: #f59e0b;
}

/* User Dropdown */
.admin-user-dropdown {
    position: relative;
}

.admin-user-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #f3f4f6;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.admin-user-btn:hover {
    background: #e5e7eb;
}

.admin-user-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.admin-username {
    font-weight: 500;
    color: #374151;
}

.admin-user-btn i.fa-chevron-down {
    font-size: 0.75rem;
    color: #6b7280;
    transition: transform 0.3s;
}

.admin-user-btn i.fa-chevron-down.rotate-180 {
    transform: rotate(180deg);
}

/* Dropdown Menu */
.admin-dropdown-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 12rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    z-index: 100;
}

.admin-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    color: #374151;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.admin-dropdown-item:hover {
    background: #f3f4f6;
}

.admin-dropdown-item i {
    color: #6b7280;
}

.admin-dropdown-logout {
    border-top: 1px solid #e5e7eb;
    color: #dc2626;
}

.admin-dropdown-logout i {
    color: #dc2626;
}

/* Locale Switcher */
.admin-locale-switcher {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    background: #f3f4f6;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.admin-locale-switcher:hover {
    background: #e5e7eb;
}

.locale-current {
    color: #dc2626;
    font-weight: 600;
}

.locale-divider {
    color: #d1d5db;
}

.locale-other {
    color: #9ca3af;
}

/* Page Content */
.admin-content {
    padding: 1.5rem;
    flex: 1;
}

/* Animations */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-0.5rem);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Mobile Styles */
.mobile-only {
    display: none;
}

.desktop-only {
    display: flex;
}

.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 99;
}

/* Tablet - 1024px */
@media (max-width: 1024px) {
    .mobile-only {
        display: flex;
    }

    .desktop-only {
        display: none;
    }

    .admin-sidebar {
        transform: translateX(-100%);
        width: 280px;
    }

    .admin-sidebar.mobile-open {
        transform: translateX(0);
    }

    .sidebar-collapsed .admin-sidebar {
        width: 280px;
    }

    .admin-main {
        margin-left: 0;
    }

    .sidebar-collapsed .admin-main {
        margin-left: 0;
    }

    .admin-username {
        display: none;
    }

    .super-admin-badge span {
        display: none;
    }

    .super-admin-badge {
        padding: 0.5rem;
    }
}

/* Mobile - 768px */
@media (max-width: 768px) {
    .admin-header {
        padding: 0.75rem 1rem;
    }

    .admin-page-title {
        font-size: 1.25rem;
    }

    .admin-content {
        padding: 1rem;
    }

    .admin-header-right {
        gap: 0.5rem;
    }

    .admin-locale-switcher {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
}

/* Small Mobile - 480px */
@media (max-width: 480px) {
    .admin-header {
        padding: 0.5rem 0.75rem;
    }

    .admin-page-title {
        font-size: 1.125rem;
    }

    .admin-content {
        padding: 0.75rem;
    }

    .admin-user-btn {
        padding: 0.375rem;
    }

    .super-admin-badge {
        display: none;
    }
}
</style>
