<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

// ─────────────────────────────────────────────────────────────────
// Category.vue ielādē datus pats caur axios (nevis Inertia props),
// tādejādi novēršot "plain JSON response" kļūdu, ko radīja
// /shop/category/{slug} → CategoryController::show() kas atgrieza JSON.
// Lapas URL slug tiek nolasīts no window.location.pathname.
// ─────────────────────────────────────────────────────────────────

const isLoading      = ref(true);
const products       = ref([]);
const categoryData   = ref(null);
const allCategories  = ref([]);
const sortBy         = ref('newest');
const searchQuery    = ref('');
const searchTimeout  = ref(null);
const selectedChild  = ref(null); // apakškategorija

const categorySlug = computed(() => {
    const parts = window.location.pathname.split('/');
    return parts[parts.length - 1];
});

// Apakškategorijas šai parent
const childCategories = computed(() => {
    if (!categoryData.value) return [];
    return allCategories.value.filter(c => c.parent_id === categoryData.value.id);
});

// ── HELPERS ───────────────────────────────────────────────────────
const toNumber    = (v) => (v === null || v === undefined) ? 0 : (parseFloat(v) || 0);
const formatPrice = (p) => toNumber(p).toFixed(2);
const getName     = (obj, lv, en) => obj ? (locale.value === 'lv' ? obj[lv] : obj[en]) : '';
const getProductName = (p) => getName(p, 'name_lv', 'name_en');
const getCatName     = (c) => getName(c, 'name_lv', 'name_en');
const getDiscountPct = (p) => {
    const price = toNumber(p.price), comp = toNumber(p.compare_price);
    if (!comp || price >= comp) return 0;
    return Math.round((1 - price / comp) * 100);
};
const isInStock = (p) => p.stock_quantity > 0;
const getImage  = (p) => p.image || '/img/Products/placeholder.png';

const CATEGORY_ICONS = {
    'clothing': 'fas fa-tshirt', 'accessories': 'fas fa-gem',
    'souvenirs': 'fas fa-star', 'gift-cards': 'fas fa-gift',
    't-shirts': 'fas fa-tshirt', 'sweatshirts': 'fas fa-user-tie',
    'hoodies': 'fas fa-vest', 'caps': 'fas fa-hat-cowboy',
    'bags': 'fas fa-shopping-bag', 'bracelets': 'fas fa-ring',
    'tech-accessories': 'fas fa-mobile-alt', 'mugs': 'fas fa-mug-hot',
};
const getCatIcon = (cat) => cat.icon || CATEGORY_ICONS[cat.slug] || 'fas fa-tag';

// ── FETCH ─────────────────────────────────────────────────────────
const fetchCategoryAndProducts = async () => {
    isLoading.value = true;
    try {
        // Nolasa kategorijas metadatus
        const catRes = await axios.get(`/api/v1/categories/${categorySlug.value}`);
        categoryData.value = catRes.data;

        // Nolasa produktus ar filtriem
        await fetchProducts();
    } catch {
        categoryData.value = null;
        products.value = [];
    } finally {
        isLoading.value = false;
    }
};

const fetchProducts = async () => {
    if (!categoryData.value) return;
    try {
        const catId = selectedChild.value || categoryData.value.id;
        const params = [`category=${catId}`];
        if (sortBy.value) params.push(`sort=${sortBy.value}`);
        if (searchQuery.value.trim()) params.push(`search=${encodeURIComponent(searchQuery.value)}`);

        const res = await axios.get(`/api/v1/products?${params.join('&')}`);
        products.value = res.data.data || res.data;
    } catch {
        products.value = [];
    }
};

const fetchAllCategories = async () => {
    try {
        const res = await axios.get('/api/v1/categories');
        allCategories.value = res.data;
    } catch {}
};

// ── FILTRI ────────────────────────────────────────────────────────
const selectChild = (id) => {
    selectedChild.value = selectedChild.value === id ? null : id;
    fetchProducts();
};

watch(sortBy, fetchProducts);

watch(searchQuery, () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(fetchProducts, 400);
});

// ── GROZS ─────────────────────────────────────────────────────────
const showToast    = ref(false);
const toastMessage = ref('');
const toastType    = ref('success');
const displayToast = (m, t = 'success') => { toastMessage.value = m; toastType.value = t; showToast.value = true; };
const closeToast   = () => { showToast.value = false; };

const addToCart = async (product) => {
    if (product.has_sizes) {
        window.location.href = `/shop/product/${product.slug}`;
        return;
    }
    if (!isInStock(product)) return;
    try {
        const res = await axios.post('/cart/add', { product_id: product.id, quantity: 1 });
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: res.data.cart.total_items } }));
        displayToast(`${getProductName(product)} ${locale.value === 'lv' ? 'pievienots grozam!' : 'added to cart!'}`, 'success');
    } catch (error) {
        displayToast(error.response?.data?.message || (locale.value === 'lv' ? 'Kļūda!' : 'Error!'), 'error');
    }
};

onMounted(() => {
    fetchAllCategories();
    fetchCategoryAndProducts();
});
</script>

<template>
    <Head :title="getCatName(categoryData) || $t('shop.category')" />
    <ShopLayout>
        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="closeToast" />

        <!-- Ielāde -->
        <div v-if="isLoading" class="page-loading">
            <LoadingSpinner size="lg" :text="$t('common.loading')" />
        </div>

        <!-- Nav atrasts -->
        <div v-else-if="!categoryData" class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>{{ locale === 'lv' ? 'Kategorija nav atrasta' : 'Category not found' }}</h2>
            <a href="/shop" class="back-btn">{{ $t('shop.back_to_shop') }}</a>
        </div>

        <template v-else>
            <!-- HERO -->
            <section class="category-hero">
                <div class="hero-container">
                    <div class="hero-icon">
                        <i :class="getCatIcon(categoryData)"></i>
                    </div>
                    <h1 class="hero-title">{{ getCatName(categoryData) }}</h1>
                    <p v-if="locale === 'lv' ? categoryData.description_lv : categoryData.description_en" class="hero-subtitle">
                        {{ locale === 'lv' ? categoryData.description_lv : categoryData.description_en }}
                    </p>
                </div>
                <div class="hero-wave">
                    <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                        <path fill="#ffffff" d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,64C960,75,1056,85,1152,80C1248,75,1344,53,1392,42.7L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
                    </svg>
                </div>
            </section>

            <!-- Apakškategoriju chips (ja ir) -->
            <div v-if="childCategories.length > 0" class="subcats-bar">
                <div class="section-container">
                    <div class="subcats-row">
                        <button
                            @click="selectedChild = null; fetchProducts()"
                            :class="['subcat-chip', { active: !selectedChild }]"
                        >
                            {{ locale === 'lv' ? 'Visi' : 'All' }}
                        </button>
                        <button
                            v-for="child in childCategories"
                            :key="child.id"
                            @click="selectChild(child.id)"
                            :class="['subcat-chip', { active: selectedChild === child.id }]"
                        >
                            <i :class="getCatIcon(child)"></i>
                            {{ getCatName(child) }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Produkti -->
            <section class="products-section">
                <div class="section-container">
                    <!-- Header ar filtriem -->
                    <div class="section-header">
                        <div class="header-left">
                            <div class="breadcrumb">
                                <a href="/shop" class="breadcrumb-link">
                                    <i class="fas fa-shopping-bag"></i>
                                    {{ $t('nav.shop') }}
                                </a>
                                <span class="breadcrumb-sep">/</span>
                                <span class="breadcrumb-current">{{ getCatName(categoryData) }}</span>
                            </div>
                            <p class="products-count">{{ products.length }} {{ $t('shop.products_count') }}</p>
                        </div>
                        <div class="filters">
                            <div class="search-wrap">
                                <i class="fas fa-search search-ico"></i>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    :placeholder="$t('common.search')"
                                    class="search-input"
                                >
                            </div>
                            <select v-model="sortBy" class="filter-select">
                                <option value="newest">{{ $t('shop.filter.newest') }}</option>
                                <option value="price-low">{{ $t('shop.filter.price_low') }}</option>
                                <option value="price-high">{{ $t('shop.filter.price_high') }}</option>
                                <option value="popular">{{ $t('shop.filter.popular') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Grid -->
                    <div class="products-grid">
                        <div v-for="product in products" :key="product.id" class="product-card">
                            <a :href="`/shop/product/${product.slug}`" class="product-link">
                                <div class="product-image">
                                    <img :src="getImage(product)" :alt="getProductName(product)"
                                         @error="$event.target.src = '/img/Products/placeholder.png'">
                                    <span v-if="getDiscountPct(product) > 0" class="badge-discount">-{{ getDiscountPct(product) }}%</span>
                                    <span v-if="!isInStock(product)" class="badge-oos">{{ $t('product.out_of_stock') }}</span>
                                    <span v-else-if="product.stock_quantity <= product.low_stock_threshold" class="badge-low">{{ $t('product.low_stock') }}</span>
                                    <span v-if="product.has_sizes" class="badge-sizes"><i class="fas fa-ruler-horizontal"></i></span>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name">{{ getProductName(product) }}</h3>
                                    <div class="product-price">
                                        <span v-if="product.compare_price" class="price-old">€{{ formatPrice(product.compare_price) }}</span>
                                        <span class="price-now">€{{ formatPrice(product.price) }}</span>
                                    </div>
                                    <div v-if="product.vat_amount" class="price-vat">t.sk. PVN: €{{ formatPrice(product.vat_amount) }}</div>
                                </div>
                            </a>
                            <button class="add-to-cart-btn" :disabled="!isInStock(product)" @click.prevent="addToCart(product)">
                                <i :class="product.has_sizes ? 'fas fa-ruler-horizontal' : 'fas fa-cart-plus'"></i>
                                {{ isInStock(product)
                                ? (product.has_sizes
                                    ? (locale === 'lv' ? 'Izvēlies izmēru' : 'Select size')
                                    : $t('common.add_to_cart'))
                                : $t('product.out_of_stock') }}
                            </button>
                        </div>

                        <div v-if="products.length === 0 && !isLoading" class="empty-state">
                            <i class="fas fa-box-open empty-icon"></i>
                            <p class="empty-text">{{ $t('shop.no_products_in_category') }}</p>
                            <a href="/shop" class="back-btn-sm">{{ $t('shop.back_to_shop') }}</a>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </ShopLayout>
</template>

<style scoped>
.page-loading { display: flex; justify-content: center; align-items: center; min-height: 60vh; }

/* Hero */
.category-hero { position: relative; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%); color: white; padding: 5rem 2rem 7rem; overflow: hidden; text-align: center; }
.hero-container { position: relative; max-width: 1200px; margin: 0 auto; z-index: 1; }
.hero-icon { font-size: 3rem; margin-bottom: 1rem; opacity: .9; }
.hero-title { font-size: 3rem; font-weight: 800; margin-bottom: 1rem; }
.hero-subtitle { font-size: 1.25rem; opacity: .9; max-width: 600px; margin: 0 auto; }
.hero-wave { position: absolute; bottom: -1px; left: 0; width: 100%; overflow: hidden; line-height: 0; }
.hero-wave svg { display: block; width: calc(100% + 4px); height: 100px; margin-left: -2px; }

/* Apakškategoriju bar */
.subcats-bar { background: #f9fafb; padding: .875rem 2rem; border-bottom: 1px solid #e5e7eb; }
.subcats-row { display: flex; gap: .5rem; overflow-x: auto; scrollbar-width: none; flex-wrap: wrap; }
.subcat-chip { display: inline-flex; align-items: center; gap: .375rem; padding: .4rem .875rem; border-radius: 999px; border: 1.5px solid #d1d5db; background: white; color: #6b7280; font-size: .875rem; font-weight: 500; cursor: pointer; transition: all .2s; white-space: nowrap; }
.subcat-chip:hover { border-color: #dc2626; color: #dc2626; background: #fff5f5; }
.subcat-chip.active { border-color: #dc2626; background: #dc2626; color: white; }

/* Produkti */
.products-section { padding: 2rem 2rem 4rem; }
.section-container { max-width: 1200px; margin: 0 auto; }
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; gap: 1.5rem; flex-wrap: wrap; }
.header-left { display: flex; flex-direction: column; gap: .375rem; }
.breadcrumb { display: flex; align-items: center; gap: .5rem; font-size: .875rem; }
.breadcrumb-link { color: #dc2626; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: .375rem; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-sep { color: #9ca3af; }
.breadcrumb-current { color: #6b7280; }
.products-count { font-size: .875rem; color: #9ca3af; margin: 0; }
.filters { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; }
.search-wrap { position: relative; }
.search-ico { position: absolute; left: .875rem; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: .875rem; }
.search-input { padding: .575rem 1rem .575rem 2.375rem; border: 2px solid #e5e7eb; border-radius: .5rem; font-size: .875rem; width: 200px; transition: all .3s; }
.search-input:focus { outline: none; border-color: #dc2626; }
.filter-select { padding: .575rem 1rem; border: 2px solid #e5e7eb; border-radius: .5rem; font-size: .875rem; font-weight: 600; cursor: pointer; }
.filter-select:focus { outline: none; border-color: #dc2626; }

.products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.75rem; }

.product-card { background: white; border-radius: .875rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); transition: all .3s; }
.product-card:hover { transform: translateY(-5px); box-shadow: 0 12px 28px rgba(0,0,0,.14); }
.product-link { display: block; text-decoration: none; color: inherit; }
.product-image { position: relative; height: 240px; overflow: hidden; background: #f9fafb; }
.product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.product-card:hover .product-image img { transform: scale(1.06); }

.badge-discount { position: absolute; top: .75rem; left: .75rem; background: #dc2626; color: white; padding: .2rem .6rem; border-radius: .25rem; font-size: .8rem; font-weight: 700; }
.badge-oos  { position: absolute; top: .75rem; right: .75rem; background: #6b7280; color: white; padding: .2rem .6rem; border-radius: .25rem; font-size: .75rem; font-weight: 600; }
.badge-low  { position: absolute; top: .75rem; right: .75rem; background: #f59e0b; color: white; padding: .2rem .6rem; border-radius: .25rem; font-size: .75rem; font-weight: 600; }
.badge-sizes { position: absolute; bottom: .75rem; right: .75rem; background: rgba(0,0,0,.5); color: white; padding: .2rem .5rem; border-radius: .25rem; font-size: .7rem; }

.product-info { padding: 1.25rem 1.25rem .75rem; }
.product-name { font-size: .9375rem; font-weight: 600; color: #111827; margin-bottom: .625rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.8rem; }
.product-price { display: flex; align-items: center; gap: .5rem; }
.price-old { text-decoration: line-through; color: #9ca3af; font-size: .8125rem; }
.price-now { font-size: 1.2rem; font-weight: 700; color: #dc2626; }
.price-vat { font-size: .7rem; color: #9ca3af; margin-top: .2rem; }

.add-to-cart-btn { width: 100%; padding: .7rem; background: #dc2626; color: white; border: none; border-radius: 0 0 .875rem .875rem; font-weight: 600; font-size: .875rem; cursor: pointer; transition: all .3s; display: flex; align-items: center; justify-content: center; gap: .4rem; }
.add-to-cart-btn:hover:not(:disabled) { background: #b91c1c; }
.add-to-cart-btn:disabled { background: #9ca3af; cursor: not-allowed; }

.empty-state { grid-column: 1/-1; text-align: center; padding: 4rem 2rem; }
.empty-icon  { font-size: 4rem; color: #d1d5db; margin-bottom: 1.5rem; display: block; }
.empty-text  { font-size: 1.125rem; color: #9ca3af; margin-bottom: 1.5rem; }
.back-btn-sm { display: inline-block; padding: .625rem 1.5rem; background: #dc2626; color: white; text-decoration: none; border-radius: .5rem; font-weight: 600; transition: all .3s; }
.back-btn-sm:hover { background: #b91c1c; }

.error-state { text-align: center; padding: 6rem 2rem; }
.error-state i { font-size: 4rem; color: #d1d5db; display: block; margin-bottom: 1.5rem; }
.error-state h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 1.5rem; color: #111827; }
.back-btn { display: inline-block; padding: .75rem 2rem; background: #dc2626; color: white; text-decoration: none; border-radius: .5rem; font-weight: 600; transition: all .3s; }
.back-btn:hover { background: #b91c1c; }

@media (max-width: 1024px) { .products-grid { grid-template-columns: repeat(3, 1fr); } .section-header { align-items: flex-start; } }
@media (max-width: 768px)  { .products-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .products-grid { grid-template-columns: 1fr; } .hero-title { font-size: 2rem; } }
</style>
