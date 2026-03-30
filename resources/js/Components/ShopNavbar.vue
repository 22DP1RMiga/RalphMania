<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const page = usePage();
const user            = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);
const isAdministrator = computed(() => user.value?.is_administrator || false);
const isCourier       = computed(() => user.value?.is_courier || false);

const userAvatar = computed(() => {
    if (!user.value?.profile_picture) return '/img/default-avatar.png';
    if (user.value.profile_picture.startsWith('/')) return user.value.profile_picture;
    return `/storage/${user.value.profile_picture}`;
});

const { locale } = useI18n({ useScope: 'global' });
const currentLocale = ref(localStorage.getItem('lang') || 'lv');
locale.value = currentLocale.value;
watch(currentLocale, (newLang) => {
    locale.value = newLang;
    localStorage.setItem('lang', newLang);
});

const isCategoriesOpen   = ref(false);
const isSearchOpen       = ref(false);
const isUserDropdownOpen = ref(false);

// Navbar augstums (lai pareizi pozicionētu slideout paneļus)
const NAVBAR_H = 64; // px — atbilst navbar augstumam

const toggleLocale = () => { currentLocale.value = currentLocale.value === 'lv' ? 'en' : 'lv'; };

const toggleCategories = () => {
    isCategoriesOpen.value = !isCategoriesOpen.value;
    if (isCategoriesOpen.value) { isSearchOpen.value = false; isUserDropdownOpen.value = false; }
};
const toggleSearch = () => {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        isCategoriesOpen.value = false;
        isUserDropdownOpen.value = false;
        setTimeout(() => document.getElementById('navbar-search-input')?.focus(), 120);
    } else {
        searchQuery.value = '';
        searchResults.value = [];
    }
};
const toggleUserDropdown = () => {
    isUserDropdownOpen.value = !isUserDropdownOpen.value;
    if (isUserDropdownOpen.value) { isCategoriesOpen.value = false; isSearchOpen.value = false; }
};
const closeMenus = () => {
    isCategoriesOpen.value = false;
    isSearchOpen.value = false;
    isUserDropdownOpen.value = false;
    searchQuery.value = '';
    searchResults.value = [];
};

// ── MEKLĒŠANA ─────────────────────────────────────────────────────
const searchQuery   = ref('');
const searchResults = ref([]);
const isSearching   = ref(false);
const searchDebounce = ref(null);

const handleSearch = () => {
    const q = searchQuery.value.trim();
    if (!q) return;
    closeMenus();
    window.location.href = `/shop?search=${encodeURIComponent(q)}`;
};

watch(searchQuery, (q) => {
    clearTimeout(searchDebounce.value);
    if (!q.trim()) { searchResults.value = []; isSearching.value = false; return; }
    isSearching.value = true;
    searchDebounce.value = setTimeout(async () => {
        try {
            const res = await axios.get(`/api/v1/products?search=${encodeURIComponent(q)}&sort=newest`);
            const all = res.data.data || res.data;
            searchResults.value = all.slice(0, 5);
        } catch { searchResults.value = []; }
        finally { isSearching.value = false; }
    }, 350);
});

const goToProduct = (slug) => {
    closeMenus();
    window.location.href = `/shop/product/${slug}`;
};

const hasSearchDropdown = computed(() =>
    isSearching.value || searchResults.value.length > 0
);

const formatPrice    = (v) => parseFloat(v || 0).toFixed(2);
const getProductName = (p) => currentLocale.value === 'lv' ? p.name_lv : p.name_en;

// ── GROZS ─────────────────────────────────────────────────────────
const cartCount = ref(0);
const fetchCartCount = async () => {
    try { const res = await axios.get('/cart/count'); cartCount.value = res.data.count || 0; } catch { cartCount.value = 0; }
};
const handleCartUpdate = (e) => {
    cartCount.value = e.detail?.count ?? 0;
};

// ── KATEGORIJAS ───────────────────────────────────────────────────
const navCategories = ref([]);
const fetchNavCategories = async () => {
    try {
        const res = await axios.get('/api/v1/categories');
        navCategories.value = res.data.filter(c => !c.parent_id);
    } catch {}
};

const CATEGORY_ICONS = {
    'clothing': 'fas fa-tshirt', 'accessories': 'fas fa-gem',
    'souvenirs': 'fas fa-star',  'gift-cards': 'fas fa-gift',
};
const getCatIcon = (cat) => cat.icon || CATEGORY_ICONS[cat.slug] || 'fas fa-tag';
const getCatName = (cat) => currentLocale.value === 'lv' ? cat.name_lv : cat.name_en;

// ── NAVIGĀCIJA ────────────────────────────────────────────────────
const goToDashboard        = () => { closeMenus(); router.visit('/dashboard'); };
const goToAdminPanel       = () => { closeMenus(); router.visit('/admin/dashboard'); };
const goToCourierDashboard = () => { closeMenus(); router.visit('/courier/dashboard'); };
const logout = () => {
    closeMenus();
    router.post('/logout', {}, { onSuccess: () => window.location.href = '/' });
};

onMounted(() => {
    fetchCartCount();
    fetchNavCategories();
    window.addEventListener('cart-updated', handleCartUpdate);
});
onUnmounted(() => window.removeEventListener('cart-updated', handleCartUpdate));
</script>

<template>
    <nav class="shop-navbar">
        <div class="shop-navbar-container">
            <!-- ── KREISĀ PUSE ── -->
            <div class="shop-navbar-left">
                <Link href="/" class="shop-home-btn" title="Sākums">
                    <i class="fas fa-home"></i>
                </Link>

                <button
                    @click="toggleCategories"
                    class="shop-nav-btn"
                    :class="{ 'shop-nav-btn--active': isCategoriesOpen }"
                    :aria-expanded="isCategoriesOpen"
                >
                    <i class="fas fa-shopping-bag"></i>
                    <span class="nav-btn-label">{{ $t('nav.shop') }}</span>
                    <i class="fas fa-chevron-down nav-chevron" :class="{ 'nav-chevron--open': isCategoriesOpen }"></i>
                </button>

                <Link href="/shop/contact" class="shop-nav-btn">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-btn-label">{{ $t('nav.contact') }}</span>
                </Link>
            </div>

            <!-- ── CENTRS: Logo ── -->
            <div class="shop-navbar-center">
                <Link href="/shop" class="shop-brand">
                    <img src="/img/RoltonsLV_Icon.png" alt="RalphMania" class="shop-logo">
                    <img src="/img/name_logo.png" alt="RalphMania" class="shop-brand-name">
                </Link>
            </div>

            <!-- ── LABĀ PUSE ── -->
            <div class="shop-navbar-right">
                <!-- Meklēšana -->
                <button
                    @click="toggleSearch"
                    class="shop-icon-btn"
                    :class="{ 'shop-icon-btn--active': isSearchOpen }"
                    :title="currentLocale === 'lv' ? 'Meklēt' : 'Search'"
                >
                    <i :class="isSearchOpen ? 'fas fa-times' : 'fas fa-search'"></i>
                </button>

                <!-- Lietotāja dropdown -->
                <div v-if="isAuthenticated" class="user-dropdown-wrap">
                    <button @click.stop="toggleUserDropdown" class="user-avatar-btn">
                        <img :src="userAvatar" :alt="user.username" class="user-avatar">
                    </button>
                    <Transition name="dropdown">
                        <div v-if="isUserDropdownOpen" class="user-dropdown">
                            <div class="dropdown-head">
                                <img :src="userAvatar" :alt="user.username" class="dropdown-avatar">
                                <span class="dropdown-name">{{ user.username }}</span>
                            </div>
                            <div class="dropdown-sep"></div>
                            <button v-if="isAdministrator" @click="goToAdminPanel" class="dropdown-item item-admin">
                                <i class="fas fa-shield-alt"></i><span>{{ $t('dashboard.sections.profile.admin_title') }}</span>
                            </button>
                            <button v-if="isCourier" @click="goToCourierDashboard" class="dropdown-item item-courier">
                                <i class="fas fa-truck"></i>
                                <span>{{ currentLocale === 'lv' ? 'Kurjera panelis' : 'Courier Dashboard' }}</span>
                            </button>
                            <button @click="goToDashboard" class="dropdown-item">
                                <i class="fas fa-user"></i><span>{{ $t('dashboard.sections.profile.title') }}</span>
                            </button>
                            <div class="dropdown-sep"></div>
                            <button @click="logout" class="dropdown-item item-logout">
                                <i class="fas fa-sign-out-alt"></i><span>{{ $t('profile.logout') }}</span>
                            </button>
                        </div>
                    </Transition>
                </div>

                <!-- Grozs -->
                <Link href="/cart" class="shop-icon-btn cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
                </Link>

                <!-- Valoda -->
                <button @click="toggleLocale" class="locale-btn">
                    <span class="locale-active">{{ currentLocale.toUpperCase() }}</span>
                    <span class="locale-sep">/</span>
                    <span class="locale-other">{{ currentLocale === 'lv' ? 'EN' : 'LV' }}</span>
                </button>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════
             KATEGORIJU PANELIS — pilns augstums no kreisās malas
             ════════════════════════════════════════════════════════ -->
        <Transition name="slide-left">
            <aside v-if="isCategoriesOpen" class="cats-panel" role="dialog" aria-label="Kategorijas">
                <div class="cats-panel-inner">
                    <div class="cats-panel-header">
                        <h2 class="cats-panel-title">{{ $t('shop.categories') }}</h2>
                        <button @click="closeMenus" class="cats-close-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <nav class="cats-nav">
                        <!-- Visi produkti -->
                        <Link href="/shop" class="cat-row cat-row--all" @click="closeMenus">
                            <span class="cat-row-icon"><i class="fas fa-th"></i></span>
                            <span class="cat-row-name">{{ currentLocale === 'lv' ? 'Visi produkti' : 'All Products' }}</span>
                            <i class="fas fa-chevron-right cat-row-arrow"></i>
                        </Link>

                        <div class="cats-divider"></div>

                        <template v-if="navCategories.length">
                            <Link
                                v-for="cat in navCategories"
                                :key="cat.id"
                                :href="`/shop/category/${cat.slug}`"
                                class="cat-row"
                                @click="closeMenus"
                            >
                                <span class="cat-row-icon"><i :class="getCatIcon(cat)"></i></span>
                                <span class="cat-row-name">{{ getCatName(cat) }}</span>
                                <span v-if="cat.product_count" class="cat-row-count">{{ cat.product_count }}</span>
                                <i class="fas fa-chevron-right cat-row-arrow"></i>
                            </Link>
                        </template>
                        <template v-else>
                            <Link href="/shop/category/clothing"    class="cat-row" @click="closeMenus"><span class="cat-row-icon"><i class="fas fa-tshirt"></i></span><span class="cat-row-name">{{ currentLocale === 'lv' ? 'Apģērbi' : 'Clothing' }}</span><i class="fas fa-chevron-right cat-row-arrow"></i></Link>
                            <Link href="/shop/category/accessories" class="cat-row" @click="closeMenus"><span class="cat-row-icon"><i class="fas fa-gem"></i></span><span class="cat-row-name">{{ currentLocale === 'lv' ? 'Aksesuāri' : 'Accessories' }}</span><i class="fas fa-chevron-right cat-row-arrow"></i></Link>
                            <Link href="/shop/category/souvenirs"   class="cat-row" @click="closeMenus"><span class="cat-row-icon"><i class="fas fa-star"></i></span><span class="cat-row-name">{{ currentLocale === 'lv' ? 'Suvenīri' : 'Souvenirs' }}</span><i class="fas fa-chevron-right cat-row-arrow"></i></Link>
                            <Link href="/shop/category/gift-cards"  class="cat-row" @click="closeMenus"><span class="cat-row-icon"><i class="fas fa-gift"></i></span><span class="cat-row-name">{{ currentLocale === 'lv' ? 'Dāvanu kartes' : 'Gift Cards' }}</span><i class="fas fa-chevron-right cat-row-arrow"></i></Link>
                        </template>
                    </nav>
                </div>
            </aside>
        </Transition>

        <!-- MEKLĒŠANAS JOSLA -->
        <Transition name="search-drop">
            <div v-if="isSearchOpen" class="search-bar-wrap">
                <!-- Josla ar inputu -->
                <div class="search-bar">
                    <div class="search-field">
                        <i class="fas fa-search search-field-icon"></i>
                        <input
                            id="navbar-search-input"
                            v-model="searchQuery"
                            type="text"
                            :placeholder="currentLocale === 'lv' ? 'Meklēt produktus...' : 'Search products...'"
                            class="search-field-input"
                            @keyup.enter="handleSearch"
                            autocomplete="off"
                        >
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''; searchResults = []; document.getElementById('navbar-search-input')?.focus()"
                            class="search-field-clear"
                            type="button"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                        <button @click="handleSearch" class="search-field-submit" type="button">
                            <i class="fas fa-arrow-right"></i>
                            <span>{{ currentLocale === 'lv' ? 'Meklēt' : 'Search' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Live rezultāti -->
                <div v-if="hasSearchDropdown" class="search-results-wrap">
                    <div class="search-results-inner">
                        <div v-if="isSearching" class="search-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>{{ currentLocale === 'lv' ? 'Meklē...' : 'Searching...' }}</span>
                        </div>
                        <template v-else>
                            <div
                                v-for="p in searchResults"
                                :key="p.id"
                                class="result-item"
                                @click="goToProduct(p.slug)"
                                role="button"
                                tabindex="0"
                                @keyup.enter="goToProduct(p.slug)"
                            >
                                <img
                                    :src="p.image || '/img/Products/placeholder.png'"
                                    :alt="getProductName(p)"
                                    class="result-thumb"
                                    @error="$event.target.src='/img/Products/placeholder.png'"
                                >
                                <div class="result-info">
                                    <span class="result-name">{{ getProductName(p) }}</span>
                                    <span class="result-price">€{{ formatPrice(p.price) }}</span>
                                </div>
                                <i class="fas fa-chevron-right result-chevron"></i>
                            </div>

                            <div v-if="searchResults.length === 0 && searchQuery" class="search-empty">
                                <i class="fas fa-search-minus"></i>
                                <span>{{ currentLocale === 'lv' ? 'Nav atrasts neviens produkts' : 'No products found' }}</span>
                            </div>

                            <button v-if="searchResults.length > 0" class="see-all-btn" @click="handleSearch">
                                {{ currentLocale === 'lv' ? 'Rādīt visus rezultātus' : 'Show all results' }}
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Overlay (aizvērt noklikšķinot ārpus) -->
        <Transition name="fade">
            <div
                v-if="isCategoriesOpen || isSearchOpen || isUserDropdownOpen"
                class="navbar-overlay"
                @click="closeMenus"
                aria-hidden="true"
            ></div>
        </Transition>
    </nav>
</template>

<style scoped>
/* ══ NAVBAR PAMATS ══════════════════════════════════════════════ */
.shop-navbar {
    background: white;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    position: sticky;
    top: 0;
    z-index: 100;
    /* Svarīgi: navbar pats par sevi nav relative lai fixed bērni
       varētu pozicionēties pret viewport */
}

.shop-navbar-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ══ KREISĀ PUSE ════════════════════════════════════════════════ */
.shop-navbar-left { display: flex; align-items: center; gap: .5rem; }

.shop-home-btn {
    display: flex; align-items: center; justify-content: center;
    width: 2.5rem; height: 2.5rem;
    color: #374151; font-size: 1.125rem;
    border-radius: .5rem;
    transition: all .2s;
    text-decoration: none;
}
.shop-home-btn:hover { color: #dc2626; background: #fef2f2; }

.shop-nav-btn {
    display: flex; align-items: center; gap: .4rem;
    padding: .5rem .875rem;
    color: #374151; font-size: .9375rem; font-weight: 500;
    border: none; border-radius: .5rem;
    background: none; cursor: pointer;
    transition: all .2s;
    text-decoration: none;
    white-space: nowrap;
}
.shop-nav-btn:hover, .shop-nav-btn--active { color: #dc2626; background: #fef2f2; }

.nav-btn-label { display: none; }
@media (min-width: 640px) { .nav-btn-label { display: inline; } }

.nav-chevron { font-size: .75rem; transition: transform .25s ease; }
.nav-chevron--open { transform: rotate(180deg); }

/* ══ CENTRS ═════════════════════════════════════════════════════ */
.shop-navbar-center {
    position: absolute;
    left: 50%; transform: translateX(-50%);
    pointer-events: none;
}
.shop-brand {
    display: flex; align-items: center; gap: .75rem;
    text-decoration: none;
    pointer-events: all;
}
.shop-logo { height: 2.25rem; width: auto; }
.shop-brand-name { height: 1.75rem; width: auto; }
@media (max-width: 640px) { .shop-logo, .shop-brand-name { display: none; } }

/* ══ LABĀ PUSE ══════════════════════════════════════════════════ */
.shop-navbar-right { display: flex; align-items: center; gap: .5rem; }

.shop-icon-btn {
    display: flex; align-items: center; justify-content: center;
    width: 2.5rem; height: 2.5rem;
    color: #374151; font-size: 1.125rem;
    border: none; border-radius: .5rem;
    background: none; cursor: pointer;
    transition: all .2s; position: relative;
}
.shop-icon-btn:hover, .shop-icon-btn--active { color: #dc2626; background: #fef2f2; }

.cart-btn .cart-badge {
    position: absolute; top: -.2rem; right: -.2rem;
    background: #dc2626; color: white;
    font-size: .6875rem; font-weight: 700;
    min-width: 1.125rem; height: 1.125rem;
    display: flex; align-items: center; justify-content: center;
    border-radius: 999px; padding: 0 .2rem;
}

/* Lietotāja dropdown */
.user-dropdown-wrap { position: relative; }
.user-avatar-btn { background: none; border: none; cursor: pointer; padding: 0; display: flex; }
.user-avatar {
    width: 2.25rem; height: 2.25rem;
    border-radius: 50%;
    border: 2px solid #dc2626;
    object-fit: cover;
    transition: transform .2s;
}
.user-avatar:hover { transform: scale(1.08); }

.user-dropdown {
    position: absolute; top: calc(100% + .625rem); right: 0;
    min-width: 210px;
    background: white; border-radius: .75rem;
    box-shadow: 0 12px 32px rgba(0,0,0,.14);
    border: 1px solid #e5e7eb;
    overflow: hidden; z-index: 200;
}
.dropdown-head {
    display: flex; align-items: center; gap: .75rem;
    padding: 1rem 1.125rem;
    background: linear-gradient(135deg, #fef2f2, #fff);
}
.dropdown-avatar { width: 2.25rem; height: 2.25rem; border-radius: 50%; border: 2px solid #dc2626; object-fit: cover; }
.dropdown-name { font-weight: 700; color: #111827; font-size: .9375rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 130px; }
.dropdown-sep { height: 1px; background: #f3f4f6; }
.dropdown-item {
    display: flex; align-items: center; gap: .75rem;
    width: 100%; padding: .75rem 1.125rem;
    background: none; border: none;
    color: #374151; font-size: .9375rem; font-weight: 500;
    cursor: pointer; transition: background .15s; text-align: left;
}
.dropdown-item:hover { background: #f9fafb; }
.dropdown-item i { width: 1.25rem; text-align: center; color: #9ca3af; }
.item-admin  { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
.item-admin:hover  { background: linear-gradient(135deg, #fde68a, #fcd34d); }
.item-admin i  { color: #d97706; }
.item-courier { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; }
.item-courier:hover { background: linear-gradient(135deg, #bfdbfe, #93c5fd); }
.item-courier i { color: #2563eb; }
.item-logout { color: #dc2626; }
.item-logout:hover { background: #fef2f2; }
.item-logout i { color: #dc2626; }

/* Valoda */
.locale-btn {
    display: flex; align-items: center; gap: .2rem;
    padding: .4rem .75rem;
    border: 1.5px solid #e5e7eb; border-radius: .5rem;
    background: white; cursor: pointer;
    transition: all .2s;
    font-size: .875rem;
}
.locale-btn:hover { border-color: #dc2626; background: #fef2f2; }
.locale-active { color: #dc2626; font-weight: 700; }
.locale-sep    { color: #d1d5db; }
.locale-other  { color: #9ca3af; }

/* ══ KATEGORIJU PANELIS — PILNS AUGSTUMS ════════════════════════ */
.cats-panel {
    /* position: fixed lai segtu visu kreiso malu no augšas līdz apakšai */
    position: fixed;
    top: 0;       /* sākas no paša augšas */
    left: 0;
    bottom: 0;
    width: 22rem;
    max-width: 85vw;
    background: white;
    box-shadow: 4px 0 24px rgba(0,0,0,.12);
    z-index: 150; /* virs overlay (z:30) bet zem dropdown (z:200) */
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.cats-panel-inner {
    display: flex; flex-direction: column;
    height: 100%;
    padding-top: 64px; /* rezervē navbar augstumu augšā */
    overflow-y: auto;
}

.cats-panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    position: sticky; top: 0; background: white; z-index: 1;
}
.cats-panel-title { font-size: 1.375rem; font-weight: 800; color: #111827; margin: 0; }
.cats-close-btn {
    width: 2.25rem; height: 2.25rem;
    display: flex; align-items: center; justify-content: center;
    background: #f9fafb; border: none; border-radius: .5rem;
    color: #6b7280; cursor: pointer; font-size: 1rem;
    transition: all .2s;
}
.cats-close-btn:hover { background: #fef2f2; color: #dc2626; }

.cats-nav { padding: .75rem 1rem 2rem; flex: 1; }

.cat-row {
    display: flex; align-items: center; gap: .875rem;
    padding: .875rem 1rem;
    border-radius: .625rem;
    color: #374151; font-weight: 500; font-size: .9375rem;
    text-decoration: none; cursor: pointer;
    transition: all .2s;
    position: relative;
}
.cat-row:hover {
    background: #fef2f2;
    color: #dc2626;
    padding-left: 1.25rem;
}
.cat-row--all { font-weight: 700; color: #111827; margin-bottom: .25rem; }
.cat-row--all:hover { color: #dc2626; }

.cat-row-icon {
    width: 2.25rem; height: 2.25rem;
    display: flex; align-items: center; justify-content: center;
    background: #f3f4f6; border-radius: .5rem;
    font-size: 1rem; color: #6b7280;
    flex-shrink: 0;
    transition: all .2s;
}
.cat-row:hover .cat-row-icon { background: #dc2626; color: white; }

.cat-row-name { flex: 1; }
.cat-row-count {
    font-size: .75rem; color: #9ca3af;
    background: #f3f4f6; border-radius: 999px;
    padding: .1rem .5rem; flex-shrink: 0;
}
.cat-row-arrow { color: #d1d5db; font-size: .75rem; flex-shrink: 0; transition: all .2s; }
.cat-row:hover .cat-row-arrow { color: #dc2626; transform: translateX(3px); }

.cats-divider { height: 1px; background: #f3f4f6; margin: .5rem 0; }

/* MEKLĒŠANAS JOSLA */
.search-bar-wrap {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 140;
    background: white;
    box-shadow: 0 8px 32px rgba(0,0,0,.12);
    border-bottom: 1px solid #e5e7eb;
}

.search-bar {
    max-width: 860px;
    margin: 0 auto;
    padding: 1.125rem 1.5rem;
}

.search-field {
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 2px solid #e5e7eb;
    border-radius: .875rem;
    overflow: hidden;
    transition: all .25s;
}
.search-field:focus-within {
    border-color: #dc2626;
    background: white;
    box-shadow: 0 0 0 4px rgba(220,38,38,.08);
}

.search-field-icon {
    padding: 0 1rem;
    color: #9ca3af;
    font-size: 1rem;
    flex-shrink: 0;
    transition: color .2s;
}
.search-field:focus-within .search-field-icon { color: #dc2626; }

.search-field-input {
    flex: 1;
    padding: .9375rem 0;
    border: none;
    background: transparent;
    font-size: 1rem;
    color: #111827;
    outline: none;
    min-width: 0;
}
.search-field-input::placeholder { color: #9ca3af; }

.search-field-clear {
    padding: 0 .75rem;
    background: none; border: none;
    color: #9ca3af; font-size: .875rem;
    cursor: pointer; transition: color .2s;
    flex-shrink: 0;
}
.search-field-clear:hover { color: #374151; }

.search-field-submit {
    display: flex; align-items: center; gap: .5rem;
    padding: 0 1.375rem;
    height: 100%;
    background: #dc2626;
    color: white; border: none;
    font-size: .9375rem; font-weight: 600;
    cursor: pointer; transition: background .2s;
    white-space: nowrap; flex-shrink: 0;
    /* Malas — nav noapaļojuma jo parent ir overflow:hidden */
}
.search-field-submit:hover { background: #b91c1c; }
@media (max-width: 480px) {
    .search-field-submit span { display: none; }
    .search-field-submit { padding: 0 1rem; }
}

/* Live rezultāti */
.search-results-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 0 1.5rem 1rem;
}
.search-results-inner {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: .75rem;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
}

.search-spinner {
    display: flex; align-items: center; justify-content: center; gap: .75rem;
    padding: 1.25rem;
    color: #9ca3af; font-size: .9375rem;
}
.search-spinner i { color: #dc2626; }

.result-item {
    display: flex; align-items: center; gap: 1rem;
    padding: .875rem 1.125rem;
    cursor: pointer;
    transition: background .15s;
    border-bottom: 1px solid #f9fafb;
}
.result-item:last-of-type { border-bottom: none; }
.result-item:hover { background: #fef2f2; }

.result-thumb {
    width: 3.25rem; height: 3.25rem;
    object-fit: cover; border-radius: .5rem;
    background: #f3f4f6; flex-shrink: 0;
}
.result-info { flex: 1; min-width: 0; }
.result-name {
    display: block; font-weight: 600; color: #111827;
    font-size: .9375rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.result-price { display: block; font-size: .875rem; font-weight: 700; color: #dc2626; margin-top: .1rem; }
.result-chevron { color: #d1d5db; font-size: .75rem; flex-shrink: 0; transition: transform .15s; }
.result-item:hover .result-chevron { color: #dc2626; transform: translateX(2px); }

.search-empty {
    display: flex; align-items: center; justify-content: center; gap: .75rem;
    padding: 1.5rem; color: #9ca3af; font-size: .9375rem;
}

.see-all-btn {
    width: 100%; padding: .875rem 1.125rem;
    background: #f9fafb; border: none; border-top: 1px solid #f3f4f6;
    color: #dc2626; font-weight: 600; font-size: .9375rem;
    cursor: pointer; transition: background .15s;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
}
.see-all-btn:hover { background: #fef2f2; }

/* ══ OVERLAY ════════════════════════════════════════════════════ */
.navbar-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 130;
    backdrop-filter: blur(2px);
}

/* ══ ANIMĀCIJAS ═════════════════════════════════════════════════ */
.dropdown-enter-active, .dropdown-leave-active { transition: all .2s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-8px) scale(.97); }

.slide-left-enter-active, .slide-left-leave-active { transition: transform .3s cubic-bezier(.4,0,.2,1); }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }

.search-drop-enter-active, .search-drop-leave-active { transition: all .25s ease; }
.search-drop-enter-from, .search-drop-leave-to { opacity: 0; transform: translateY(-100%); }

.fade-enter-active, .fade-leave-active { transition: opacity .25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ══ RESPONSIVS ═════════════════════════════════════════════════ */
@media (max-width: 480px) {
    .shop-navbar-container { padding: 0 1rem; }
    .cats-panel { width: 100%; max-width: 100%; }
    .locale-btn { display: none; } /* Par mazu ekrānam - pa tiešo poga*/
}
</style>
