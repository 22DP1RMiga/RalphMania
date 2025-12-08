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
</script>

<template>
    <div class="authenticated-layout">
        <!-- Header -->
        <header class="auth-header">
            <div class="auth-header-container">
                <!-- Left: Logo -->
                <Link href="/" class="auth-brand">
                    <img src="/img/RoltonsLV_Icon.png" alt="RalphMania Logo" class="auth-logo">
                    <img src="/img/name_logo.png" alt="RalphMania" class="auth-brand-name">
                </Link>

                <!-- Center: Dashboard Button -->
                <div class="auth-nav">
                    <Link
                        :href="route('dashboard')"
                        class="auth-nav-btn"
                        :class="{ 'auth-nav-btn-active': $page.url === '/dashboard' }"
                    >
                        <i class="fas fa-th-large"></i>
                        <span>{{ t('nav.dashboard') }}</span>
                    </Link>
                </div>

                <!-- Right: User Menu & Locale -->
                <div class="auth-right">
                    <!-- User Dropdown -->
                    <div class="auth-user-dropdown" v-click-outside="closeUserDropdown">
                        <button @click="toggleUserDropdown" class="auth-user-btn">
                            <img
                                :src="userAvatar"
                                :alt="user.username"
                                class="auth-user-avatar"
                            >
                            <span class="auth-username">{{ user.username }}</span>
                            <i class="fas fa-chevron-down" :class="{ 'rotate-180': isUserDropdownOpen }"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <Transition name="dropdown">
                            <div v-if="isUserDropdownOpen" class="auth-dropdown-menu">
                                <Link
                                    :href="route('profile.edit')"
                                    class="auth-dropdown-item"
                                    @click="closeUserDropdown"
                                >
                                    <i class="fas fa-user"></i>
                                    <span>{{ t('nav.profile') }}</span>
                                </Link>
                                <button
                                    @click="logout"
                                    class="auth-dropdown-item auth-dropdown-logout"
                                >
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>{{ t('auth.logout') }}</span>
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <!-- Locale Switcher -->
                    <button @click="toggleLocale" class="auth-locale-switcher">
                        <span class="locale-current">{{ currentLocale.toUpperCase() }}</span>
                        <span class="locale-divider">/</span>
                        <span class="locale-other">{{ currentLocale === 'lv' ? 'EN' : 'LV' }}</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="auth-main">
            <slot />
        </main>
    </div>
</template>

<style scoped>
/* Layout */
.authenticated-layout {
    min-height: 100vh;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

/* Header */
.auth-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.auth-header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0.75rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
}

/* Logo */
.auth-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.2s;
}

.auth-brand:hover {
    transform: translateY(-2px);
}

.auth-logo {
    height: 2.5rem;
    width: auto;
}

.auth-brand-name {
    height: 1.75rem;
    width: auto;
}

/* Hide brand name on mobile */
@media (max-width: 768px) {
    .auth-brand-name {
        display: none;
    }
}

/* Navigation */
.auth-nav {
    flex: 1;
    display: flex;
    justify-content: center;
}

.auth-nav-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.auth-nav-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.auth-nav-btn-active {
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.auth-nav-btn i {
    font-size: 1.125rem;
}

/* Right Section */
.auth-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* User Dropdown */
.auth-user-dropdown {
    position: relative;
}

.auth-user-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 0.5rem;
    color: white;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.auth-user-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.auth-user-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.auth-username {
    font-size: 0.95rem;
}

.auth-user-btn i.fa-chevron-down {
    font-size: 0.75rem;
    transition: transform 0.3s;
}

.auth-user-btn i.fa-chevron-down.rotate-180 {
    transform: rotate(180deg);
}

/* Dropdown Menu */
.auth-dropdown-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 12rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    z-index: 1001;
}

.auth-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.875rem 1.25rem;
    background: white;
    border: none;
    color: #374151;
    font-weight: 500;
    font-size: 0.95rem;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
}

.auth-dropdown-item:hover {
    background: #fee2e2;
    color: #dc2626;
}

.auth-dropdown-item i {
    font-size: 1rem;
    color: #dc2626;
}

.auth-dropdown-logout {
    border-top: 1px solid #f3f4f6;
}

.auth-dropdown-logout:hover {
    background: #fef2f2;
}

/* Locale Switcher */
.auth-locale-switcher {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.875rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 0.5rem;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.auth-locale-switcher:hover {
    background: rgba(255, 255, 255, 0.2);
}

.locale-current {
    color: white;
}

.locale-divider {
    color: rgba(255, 255, 255, 0.5);
}

.locale-other {
    color: rgba(255, 255, 255, 0.6);
}

/* Main Content */
.auth-main {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

/* Dropdown Animation */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-0.5rem);
}

/* Responsive */
@media (max-width: 768px) {
    .auth-header-container {
        padding: 0.625rem 1rem;
        gap: 0.75rem;
    }

    .auth-username {
        display: none;
    }

    .auth-nav {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .auth-nav-btn span {
        display: none;
    }

    .auth-nav-btn {
        padding: 0.625rem 1rem;
    }

    .auth-main {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .auth-locale-switcher {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
