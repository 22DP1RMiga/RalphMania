<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);

// Check if user is administrator
const isAdministrator = computed(() => user.value?.is_administrator || false);

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
const { locale, t } = useI18n({ useScope: 'global' });
const currentLocale = ref(localStorage.getItem('lang') || 'lv');

// Sync Vue i18n locale with stored value on mount
locale.value = currentLocale.value;

// Reactively change locale when button clicked
watch(currentLocale, (newLang) => {
    locale.value = newLang;
    localStorage.setItem('lang', newLang);
});

const isCategoriesOpen = ref(false);
const isSearchOpen = ref(false);
const isUserDropdownOpen = ref(false);

const toggleLocale = () => {
    currentLocale.value = currentLocale.value === 'lv' ? 'en' : 'lv';
};

const toggleCategories = () => {
    isCategoriesOpen.value = !isCategoriesOpen.value;
    if (isCategoriesOpen.value) {
        isSearchOpen.value = false;
        isUserDropdownOpen.value = false;
    }
};

const toggleSearch = () => {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        isCategoriesOpen.value = false;
        isUserDropdownOpen.value = false;
    }
};

const toggleUserDropdown = () => {
    isUserDropdownOpen.value = !isUserDropdownOpen.value;
    if (isUserDropdownOpen.value) {
        isCategoriesOpen.value = false;
        isSearchOpen.value = false;
    }
};

const closeMenus = () => {
    isCategoriesOpen.value = false;
    isSearchOpen.value = false;
    isUserDropdownOpen.value = false;
};

const searchQuery = ref('');

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        // TODO: Implement search functionality
        console.log('Searching for:', searchQuery.value);
    }
};

// Cart count state
const cartCount = ref(0);

// Fetch cart count from API
const fetchCartCount = async () => {
    try {
        const response = await axios.get('/cart/count');
        cartCount.value = response.data.count || 0;
    } catch (error) {
        console.error('Error fetching cart count:', error);
        cartCount.value = 0;
    }
};

// Listen for cart updates
const handleCartUpdate = (event) => {
    if (event.detail?.count !== undefined) {
        cartCount.value = event.detail.count;
    } else {
        fetchCartCount();
    }
};

// Navigation functions
const goToDashboard = () => {
    closeMenus();
    router.visit('/dashboard');
};

const goToAdminPanel = () => {
    closeMenus();
    router.visit('/admin/dashboard');
};

const logout = () => {
    closeMenus();
    router.post('/logout', {}, {
        onSuccess: () => {
            window.location.href = '/';
        }
    });
};

// On mount, fetch cart count and setup listener
onMounted(() => {
    fetchCartCount();
    window.addEventListener('cart-updated', handleCartUpdate);
});

// Cleanup listener on unmount
onUnmounted(() => {
    window.removeEventListener('cart-updated', handleCartUpdate);
});
</script>

<template>
    <nav class="shop-navbar">
        <div class="shop-navbar-container">
            <!-- Left Section -->
            <div class="shop-navbar-left">
                <!-- Home Button -->
                <Link href="/" class="shop-home-btn">
                    <i class="fas fa-home"></i>
                </Link>

                <!-- Shop Button with Categories Dropdown -->
                <div class="shop-dropdown">
                    <button @click="toggleCategories" class="shop-nav-btn">
                        <i class="fas fa-shopping-bag"></i>
                        <span>{{ $t('nav.shop') }}</span>
                        <i class="fas fa-chevron-down" :class="{ 'rotate-180': isCategoriesOpen }"></i>
                    </button>
                </div>

                <!-- Contact Button -->
                <Link href="/shop/contact" class="shop-nav-btn">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $t('nav.contact') }}</span>
                </Link>
            </div>

            <!-- Center: Logo & Brand -->
            <div class="shop-navbar-center">
                <Link href="/shop" class="shop-brand">
                    <img src="/img/RoltonsLV_Icon.png" alt="RalphMania Logo" class="shop-logo">
                    <img src="/img/name_logo.png" alt="RalphMania" class="shop-brand-name">
                </Link>
            </div>

            <!-- Right Section -->
            <div class="shop-navbar-right">
                <!-- Search Button -->
                <button @click="toggleSearch" class="shop-icon-btn">
                    <i class="fas fa-search"></i>
                </button>

                <!-- User Profile with Dropdown -->
                <div v-if="isAuthenticated" class="shop-user-dropdown-container">
                    <button @click.stop="toggleUserDropdown" class="shop-user-profile">
                        <img
                            :src="userAvatar"
                            :alt="user.username"
                            class="shop-user-avatar"
                        >
                    </button>

                    <!-- User Dropdown Menu -->
                    <Transition name="dropdown">
                        <div v-if="isUserDropdownOpen" class="shop-user-dropdown">
                            <div class="dropdown-header">
                                <img :src="userAvatar" :alt="user.username" class="dropdown-avatar">
                                <span class="dropdown-username">{{ user.username }}</span>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- Admin Panel Link (if administrator) -->
                            <button v-if="isAdministrator" @click="goToAdminPanel" class="dropdown-item dropdown-item-admin">
                                <i class="fas fa-shield-alt"></i>
                                <span>{{ $t('dashboard.sections.profile.admin_title') }}</span>
                            </button>

                            <!-- Dashboard Link -->
                            <button @click="goToDashboard" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>{{ $t('dashboard.sections.profile.title') }}</span>
                            </button>

                            <div class="dropdown-divider"></div>

                            <!-- Logout -->
                            <button @click="logout" class="dropdown-item dropdown-item-logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>{{ $t('profile.logout') }}</span>
                            </button>
                        </div>
                    </Transition>
                </div>

                <!-- Cart Button -->
                <Link href="/cart" class="shop-icon-btn shop-cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span v-if="cartCount > 0" class="shop-cart-badge">{{ cartCount }}</span>
                </Link>

                <!-- Locale Switcher -->
                <button @click="toggleLocale" class="shop-locale-switcher">
                    <span class="locale-current">{{ currentLocale.toUpperCase() }}</span>
                    <span class="locale-divider">/</span>
                    <span class="locale-other">{{ currentLocale === 'lv' ? 'EN' : 'LV' }}</span>
                </button>
            </div>
        </div>

        <!-- Categories Slide Panel -->
        <Transition name="slide-left">
            <div v-if="isCategoriesOpen" class="categories-panel">
                <div class="categories-content">
                    <h3 class="categories-title">{{ $t('shop.categories') }}</h3>

                    <div class="categories-list">
                        <Link href="/shop/category/clothing" class="category-item" @click="closeMenus">
                            <i class="fas fa-tshirt"></i>
                            <span>{{ currentLocale === 'lv' ? 'Apģērbi' : 'Clothing' }}</span>
                        </Link>
                        <Link href="/shop/category/accessories" class="category-item" @click="closeMenus">
                            <i class="fas fa-watch"></i>
                            <span>{{ currentLocale === 'lv' ? 'Aksesuāri' : 'Accessories' }}</span>
                        </Link>
                        <Link href="/shop/category/souvenirs" class="category-item" @click="closeMenus">
                            <i class="fas fa-gift"></i>
                            <span>{{ currentLocale === 'lv' ? 'Suvenīri' : 'Souvenirs' }}</span>
                        </Link>
                        <Link href="/shop/category/gift-cards" class="category-item" @click="closeMenus">
                            <i class="fas fa-credit-card"></i>
                            <span>{{ currentLocale === 'lv' ? 'Dāvanu kartes' : 'Gift Cards' }}</span>
                        </Link>
                    </div>

                    <!-- Promo Banner -->
                    <div class="categories-promo">
                        <img src="/images/shop-promo.jpg" alt="Akcija" class="promo-image">
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Search Slide Panel -->
        <Transition name="slide-right">
            <div v-if="isSearchOpen" class="search-panel">
                <div class="search-content">
                    <div class="search-input-wrapper">
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="$t('common.search')"
                            class="search-input"
                            @keyup.enter="handleSearch"
                        >
                        <button @click="handleSearch" class="search-submit-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <button @click="toggleSearch" class="search-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Overlay -->
        <Transition name="fade">
            <div
                v-if="isCategoriesOpen || isSearchOpen || isUserDropdownOpen"
                class="navbar-overlay"
                @click="closeMenus"
            ></div>
        </Transition>
    </nav>
</template>

<style scoped>
/* ========== SHOP NAVBAR ========== */
.shop-navbar {
    background-color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 50;
}

.shop-navbar-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ========== LEFT SECTION ========== */
.shop-navbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.shop-home-btn {
    padding: 0.5rem 1rem;
    color: #374151;
    font-size: 1.25rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.shop-home-btn:hover {
    color: #dc2626;
    background-color: #fef2f2;
}

.shop-dropdown {
    position: relative;
}

.shop-nav-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: #374151;
    font-weight: 500;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    background: none;
    border: none;
    cursor: pointer;
}

.shop-nav-btn:hover {
    color: #dc2626;
    background-color: #fef2f2;
}

.shop-nav-btn i.fa-chevron-down {
    transition: transform 0.3s ease;
}

.shop-nav-btn i.fa-chevron-down.rotate-180 {
    transform: rotate(180deg);
}

/* ========== CENTER SECTION ========== */
.shop-navbar-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.shop-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.shop-logo {
    height: 2.5rem;
    width: auto;
}

.shop-brand-name {
    height: 2rem;
    width: auto;
}

@media (max-width: 768px) {
    .shop-logo,
    .shop-brand-name {
        display: none;
    }
}

/* ========== RIGHT SECTION ========== */
.shop-navbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.shop-icon-btn {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    font-size: 1.25rem;
    border-radius: 0.375rem;
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.shop-icon-btn:hover {
    color: #dc2626;
    background-color: #fef2f2;
}

.shop-cart-btn .shop-cart-badge {
    position: absolute;
    top: -0.25rem;
    right: -0.25rem;
    background-color: #dc2626;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    width: 1.25rem;
    height: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

/* ========== USER DROPDOWN ========== */
.shop-user-dropdown-container {
    position: relative;
}

.shop-user-profile {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.shop-user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    border: 2px solid #dc2626;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.shop-user-avatar:hover {
    transform: scale(1.1);
}

.shop-user-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 220px;
    background-color: white;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    z-index: 100;
}

.dropdown-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
}

.dropdown-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    border: 2px solid #dc2626;
    object-fit: cover;
}

.dropdown-username {
    font-weight: 600;
    color: #111827;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-divider {
    height: 1px;
    background-color: #e5e7eb;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    color: #374151;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
}

.dropdown-item:hover {
    background-color: #f3f4f6;
    color: #111827;
}

.dropdown-item i {
    font-size: 1rem;
    width: 1.25rem;
    text-align: center;
    color: #6b7280;
}

.dropdown-item:hover i {
    color: #374151;
}

/* Admin item styling */
.dropdown-item-admin {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.dropdown-item-admin:hover {
    background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%);
}

.dropdown-item-admin i {
    color: #d97706;
}

/* Logout item styling */
.dropdown-item-logout {
    color: #dc2626;
}

.dropdown-item-logout:hover {
    background-color: #fef2f2;
}

.dropdown-item-logout i {
    color: #dc2626;
}

/* Dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Locale Switcher */
.shop-locale-switcher {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.shop-locale-switcher:hover {
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

/* ========== CATEGORIES PANEL ========== */
.categories-panel {
    position: fixed;
    left: 0;
    top: 4rem;
    bottom: 0;
    width: 20rem;
    background-color: white;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
    z-index: 40;
    overflow-y: auto;
}

.categories-content {
    padding: 2rem;
}

.categories-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
}

.categories-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 2rem;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #374151;
    font-weight: 500;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.category-item:hover {
    background-color: #fef2f2;
    color: #dc2626;
}

.category-item i {
    font-size: 1.25rem;
}

.categories-promo {
    margin-top: 2rem;
    border-radius: 0.5rem;
    overflow: hidden;
}

.promo-image {
    width: 100%;
    height: auto;
}

/* ========== SEARCH PANEL ========== */
.search-panel {
    position: fixed;
    right: 0;
    top: 4rem;
    width: 24rem;
    background-color: white;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
    z-index: 40;
    padding: 2rem;
}

.search-content {
    display: flex;
    gap: 0.5rem;
}

.search-input-wrapper {
    flex: 1;
    display: flex;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
    transition: border-color 0.3s ease;
}

.search-input-wrapper:focus-within {
    border-color: #dc2626;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    outline: none;
    font-size: 1rem;
}

.search-submit-btn {
    padding: 0 1rem;
    background-color: #dc2626;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.search-submit-btn:hover {
    background-color: #b91c1c;
}

.search-close-btn {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    background: none;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-close-btn:hover {
    color: #dc2626;
    border-color: #dc2626;
    background-color: #fef2f2;
}

/* ========== OVERLAY ========== */
.navbar-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 30;
}

/* ========== ANIMATIONS ========== */
.slide-left-enter-active,
.slide-left-leave-active {
    transition: transform 0.3s ease;
}

.slide-left-enter-from,
.slide-left-leave-to {
    transform: translateX(-100%);
}

.slide-right-enter-active,
.slide-right-leave-active {
    transition: transform 0.3s ease;
}

.slide-right-enter-from,
.slide-right-leave-to {
    transform: translateX(100%);
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
