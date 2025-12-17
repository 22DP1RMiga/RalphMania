<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);

// Get user avatar with correct path
const userAvatar = computed(() => {
    if (!user.value?.profile_picture) {
        return '/img/default-avatar.png';
    }
    // If profile_picture already starts with /, use it as is
    if (user.value.profile_picture.startsWith('/')) {
        return user.value.profile_picture;
    }
    // Otherwise add /storage/ prefix
    return `/storage/${user.value.profile_picture}`;
});

// i18n setup
const { locale } = useI18n({ useScope: 'global' });
const currentLocale = ref(localStorage.getItem('lang') || 'lv');

// Sync Vue i18n locale with stored value on mount
locale.value = currentLocale.value;

// Reactively change locale when button clicked
watch(currentLocale, (newLang) => {
    locale.value = newLang;
    localStorage.setItem('lang', newLang);
});

// Toggle locale function
const toggleLocale = () => {
    currentLocale.value = currentLocale.value === 'lv' ? 'en' : 'lv';
};

// Mobile menu
const isMenuActive = ref(false);

const toggleNav = () => {
    isMenuActive.value = !isMenuActive.value;
};

const closeMenu = () => {
    isMenuActive.value = false;
};

// Logout function
const logout = () => {
    router.post('/logout', {}, {
        onSuccess: () => {
            window.location.href = '/';
        }
    });
};

const goToDashboard = () => {
    router.visit('/dashboard');
    closeMenu();
};
</script>

<template>
    <nav class="main-navbar">
        <div class="navbar-container">
            <!-- Left: Logo & Name -->
            <div class="navbar-left">
                <Link href="/" class="navbar-brand" @click="closeMenu">
                    <img src="/img/RoltonsLV_Icon.png" alt="RalphMania Logo" class="navbar-logo">
                    <img src="/img/name_logo.png" alt="RalphMania" class="navbar-brand-name">
                </Link>
            </div>

            <!-- Center: Navigation Links (Desktop) -->
            <ul class="navbar-center">
                <li>
                    <Link
                        href="/"
                        class="nav-link"
                        :class="{ 'nav-link-active': $page.url === '/' }"
                    >
                        {{ $t('nav.home') }}
                    </Link>
                </li>
                <li>
                    <Link
                        href="/about"
                        class="nav-link"
                        :class="{ 'nav-link-active': $page.url === '/about' }"
                    >
                        {{ $t('nav.about') }}
                    </Link>
                </li>
                <li>
                    <Link
                        href="/contact"
                        class="nav-link"
                        :class="{ 'nav-link-active': $page.url === '/contact' }"
                    >
                        {{ $t('nav.contact') }}
                    </Link>
                </li>
                <li>
                    <Link
                        href="/shop"
                        class="nav-link"
                        :class="{ 'nav-link-active': $page.url.startsWith('/shop') }"
                    >
                        {{ $t('nav.shop') }}
                    </Link>
                </li>
            </ul>

            <!-- Right: User & Locale (Desktop) -->
            <div class="navbar-right">
                <!-- Authenticated User -->
                <div v-if="isAuthenticated" class="navbar-user">
                    <button @click="goToDashboard" class="user-profile">
                        <img
                            :src="userAvatar"
                            :alt="user.username"
                            class="user-avatar"
                        >
                        <span class="user-tooltip">{{ user.username }}</span>
                    </button>
                    <button @click="logout" class="logout-btn" :title="$t('auth.logout')">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>

                <!-- Guest Buttons -->
                <div v-else class="navbar-guest">
                    <Link href="/login" class="guest-btn guest-btn-login">
                        {{ $t('auth.login') }}
                    </Link>
                    <Link href="/register" class="guest-btn guest-btn-register">
                        {{ $t('auth.register') }}
                    </Link>
                </div>

                <!-- Locale Switcher (Toggle Button) -->
                <button @click="toggleLocale" class="locale-switcher">
                    <span class="locale-current">{{ currentLocale.toUpperCase() }}</span>
                    <span class="locale-divider">/</span>
                    <span class="locale-other">{{ currentLocale === 'lv' ? 'EN' : 'LV' }}</span>
                </button>
            </div>

            <!-- Hamburger Menu (Mobile) -->
            <button
                class="hamburger"
                :class="{ 'hamburger-active': isMenuActive }"
                @click="toggleNav"
            >
                <span class="line"></span>
                <span class="line"></span>
                <span class="line"></span>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="menubar" :class="{ active: isMenuActive }">
        <ul class="menubar-list">
            <!-- Mobile Navigation Links -->
            <li>
                <Link href="/" class="menubar-link" @click="closeMenu">
                    {{ $t('nav.home') }}
                </Link>
            </li>
            <li>
                <Link href="/about" class="menubar-link" @click="closeMenu">
                    {{ $t('nav.about') }}
                </Link>
            </li>
            <li>
                <Link href="/contact" class="menubar-link" @click="closeMenu">
                    {{ $t('nav.contact') }}
                </Link>
            </li>
            <li>
                <Link href="/shop" class="menubar-link" @click="closeMenu">
                    {{ $t('nav.shop') }}
                </Link>
            </li>

            <!-- Mobile User Section -->
            <li v-if="isAuthenticated" class="menubar-user-section">
                <button @click="goToDashboard" class="menubar-user-btn">
                    <i class="fas fa-user"></i>
                    <span>{{ user.username }}</span>
                </button>
                <button @click="logout" class="menubar-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>{{ $t('auth.logout') }}</span>
                </button>
            </li>

            <!-- Mobile Guest Buttons -->
            <li v-else class="menubar-guest-section">
                <Link href="/login" class="menubar-guest-btn" @click="closeMenu">
                    {{ $t('auth.login') }}
                </Link>
                <Link href="/register" class="menubar-guest-btn menubar-guest-btn-register" @click="closeMenu">
                    {{ $t('auth.register') }}
                </Link>
            </li>

            <!-- Mobile Locale Switcher (Toggle Button) -->
            <li class="menubar-locale">
                <button @click="toggleLocale" class="menubar-locale-toggle">
                    <span :class="{ 'locale-active': currentLocale === 'lv' }">🇱🇻 LV</span>
                    <span class="locale-divider">/</span>
                    <span :class="{ 'locale-active': currentLocale === 'en' }">🇬🇧 EN</span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Overlay when mobile menu is open -->
    <Transition name="fade">
        <div
            v-if="isMenuActive"
            class="menubar-overlay"
            @click="closeMenu"
        ></div>
    </Transition>
</template>

<style scoped>
/* ========== MAIN NAVBAR ========== */
.main-navbar {
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 50;
}

.navbar-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    height: 55px;
}

/* ========== LEFT SECTION ========== */
.navbar-left {
    flex-shrink: 0;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.navbar-logo {
    height: 2.5rem;
    width: auto;
}

.navbar-brand-name {
    height: 1.5rem;
    width: auto;
}

@media (max-width: 790px) {
    .navbar-brand-name {
        display: none;
    }
}

/* ========== CENTER SECTION (Desktop) ========== */
.navbar-center {
    display: none;
    list-style: none;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
}

@media (min-width: 790px) {
    .navbar-center {
        display: flex;
    }
}

.nav-link {
    color: #374151;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    position: relative;
    text-decoration: none;
}

.nav-link:hover {
    color: #dc2626;
    background-color: #fef2f2;
}

.nav-link-active {
    color: #dc2626;
    font-weight: 600;
}

.nav-link-active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 1rem;
    right: 1rem;
    height: 2px;
    background-color: #dc2626;
}

/* ========== RIGHT SECTION (Desktop) ========== */
.navbar-right {
    display: none;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

@media (min-width: 790px) {
    .navbar-right {
        display: flex;
    }
}

/* User Profile */
.navbar-user {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-profile {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    border: 2px solid #dc2626;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.user-avatar:hover {
    transform: scale(1.1);
}

.user-tooltip {
    position: absolute;
    bottom: -2.5rem;
    left: 50%;
    transform: translateX(-50%);
    background-color: #1f2937;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.user-profile:hover .user-tooltip {
    opacity: 1;
}

.logout-btn {
    background: none;
    border: none;
    color: #374151;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    color: #dc2626;
    background-color: #fef2f2;
}

/* Guest Buttons */
.navbar-guest {
    display: flex;
    gap: 0.5rem;
}

.guest-btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
}

.guest-btn-login {
    color: #dc2626;
    border: 1px solid #dc2626;
}

.guest-btn-login:hover {
    background-color: #fef2f2;
}

.guest-btn-register {
    background-color: #dc2626;
    color: white;
    border: 1px solid #dc2626;
}

.guest-btn-register:hover {
    background-color: #b91c1c;
}

/* Locale Switcher (Toggle Button Style) */
.locale-switcher {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: white;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.locale-switcher:hover {
    border-color: #dc2626;
    background-color: #fef2f2;
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

/* ========== HAMBURGER MENU (Mobile) ========== */
.hamburger {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
}

@media (max-width: 790px) {
    .hamburger {
        display: flex;
    }
}

.line {
    width: 25px;
    height: 2px;
    background-color: #374151;
    display: block;
    margin: 4px 0;
    transition: all 0.3s ease-in-out;
}

.hamburger-active .line:nth-child(1) {
    transform: translateY(10px) rotate(45deg);
}

.hamburger-active .line:nth-child(2) {
    opacity: 0;
}

.hamburger-active .line:nth-child(3) {
    transform: translateY(-10px) rotate(-45deg);
}

/* ========== MOBILE MENU ========== */
.menubar {
    position: fixed;
    top: 0;
    left: -100%;
    width: 70%;
    max-width: 300px;
    height: 100vh;
    background-color: white;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
    transition: left 0.3s ease;
    z-index: 100;
    overflow-y: auto;
}

.menubar.active {
    left: 0;
}

.menubar-list {
    list-style: none;
    padding: 2rem 1rem;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.menubar-link {
    display: block;
    color: #374151;
    font-weight: 500;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.menubar-link:hover {
    background-color: #fef2f2;
    color: #dc2626;
}

/* Mobile User Section */
.menubar-user-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.menubar-user-btn,
.menubar-logout-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    color: #374151;
    font-weight: 500;
    cursor: pointer;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    text-align: left;
}

.menubar-user-btn:hover {
    background-color: #fef2f2;
    color: #dc2626;
}

.menubar-logout-btn:hover {
    background-color: #fee2e2;
    color: #dc2626;
}

.menubar-user-btn i,
.menubar-logout-btn i {
    font-size: 1.2rem;
}

/* Mobile Guest Section */
.menubar-guest-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.menubar-guest-btn {
    display: block;
    text-align: center;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    border: 1px solid #dc2626;
    color: #dc2626;
}

.menubar-guest-btn:hover {
    background-color: #fef2f2;
}

.menubar-guest-btn-register {
    background-color: #dc2626;
    color: white;
}

.menubar-guest-btn-register:hover {
    background-color: #b91c1c;
}

/* Mobile Locale (Toggle Button Style) */
.menubar-locale {
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.menubar-locale-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.menubar-locale-toggle:hover {
    border-color: #dc2626;
    background-color: #fef2f2;
}

.menubar-locale-toggle span {
    color: #9ca3af;
    transition: color 0.3s ease;
}

.menubar-locale-toggle .locale-active {
    color: #dc2626;
    font-weight: 600;
}

.menubar-locale-toggle .locale-divider {
    color: #d1d5db;
}

/* ========== OVERLAY ========== */
.menubar-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 99;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
