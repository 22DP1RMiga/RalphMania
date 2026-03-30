<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const isLoadingProducts   = ref(true);
const isLoadingCategories = ref(true);
const products            = ref([]);
const allCategories       = ref([]);
const sortBy              = ref('newest');
const searchQuery         = ref('');
const searchTimeout       = ref(null);

// ── KATEGORIJU STĀVOKLIS ──────────────────────────────────────────
// Svarīgi: salīdzinām Number, nevis String (parent_id no API var būt string)
const selectedParent = ref(null);
const selectedChild  = ref(null);

const parentCategories = computed(() =>
    allCategories.value.filter(c => !c.parent_id) // null vai 0
);

const childCategories = computed(() => {
    if (!selectedParent.value) return [];
    // Pārveidojam uz Number, jo API var atgriezt parent_id kā string
    return allCategories.value.filter(c => Number(c.parent_id) === Number(selectedParent.value));
});

// Ikonas mapping pēc slug
const CATEGORY_ICONS = {
    'clothing':         'fas fa-tshirt',
    'accessories':      'fas fa-gem',
    'souvenirs':        'fas fa-star',
    'gift-cards':       'fas fa-gift',
    't-shirts':         'fas fa-tshirt',
    'sweatshirts':      'fas fa-user-tie',
    'hoodies':          'fas fa-vest',
    'caps':             'fas fa-hat-cowboy',
    'bags':             'fas fa-shopping-bag',
    'bracelets':        'fas fa-ring',
    'tech-accessories': 'fas fa-mobile-alt',
    'mugs':             'fas fa-mug-hot',
    'posters':          'fas fa-image',
    'keychains':        'fas fa-key',
    'stickers':         'fas fa-sticky-note',
};
const getCategoryIcon = (cat) => cat.icon || CATEGORY_ICONS[cat.slug] || 'fas fa-tag';

const activeCategoryName = computed(() => {
    if (selectedChild.value) {
        const c = allCategories.value.find(c => Number(c.id) === Number(selectedChild.value));
        return c ? (locale.value === 'lv' ? c.name_lv : c.name_en) : '';
    }
    if (selectedParent.value) {
        const c = allCategories.value.find(c => Number(c.id) === Number(selectedParent.value));
        return c ? (locale.value === 'lv' ? c.name_lv : c.name_en) : '';
    }
    return locale.value === 'lv' ? 'Visi produkti' : 'All Products';
});

// ── HELPERS ───────────────────────────────────────────────────────
const toNumber    = (v) => (v === null || v === undefined) ? 0 : (parseFloat(v) || 0);
const formatPrice = (p) => toNumber(p).toFixed(2);
const getProductName  = (p) => locale.value === 'lv' ? p.name_lv : p.name_en;
const getCategoryName = (c) => locale.value === 'lv' ? c.name_lv : c.name_en;
const getDiscountPct  = (p) => {
    const price = toNumber(p.price), comp = toNumber(p.compare_price);
    if (!comp || price >= comp) return 0;
    return Math.round((1 - price / comp) * 100);
};
const isInStock       = (p) => p.stock_quantity > 0 && p.is_active;
const getProductImage = (p) => p.image || '/img/Products/placeholder.png';

// ── FETCH ─────────────────────────────────────────────────────────
const fetchProducts = async () => {
    isLoadingProducts.value = true;
    try {
        const params = [];
        // Backend (ProductController) apstrādā parent kateogriju — atgriež arī bērnu produktus
        const catId = selectedChild.value || selectedParent.value;
        if (catId) params.push(`category=${catId}`);
        if (sortBy.value) params.push(`sort=${sortBy.value}`);
        if (searchQuery.value.trim())
            params.push(`search=${encodeURIComponent(searchQuery.value)}`);

        const res = await axios.get(`/api/v1/products${params.length ? '?' + params.join('&') : ''}`);
        products.value = res.data.data || res.data;
    } catch {
        products.value = [];
    } finally {
        isLoadingProducts.value = false;
    }
};

const fetchCategories = async () => {
    isLoadingCategories.value = true;
    try {
        const res = await axios.get('/api/v1/categories');
        // Pārveidojam parent_id uz Number lai salīdzināšana darbotos droši
        allCategories.value = res.data.map(c => ({
            ...c,
            id: Number(c.id),
            parent_id: c.parent_id ? Number(c.parent_id) : null,
        }));
    } catch {
        allCategories.value = [];
    } finally {
        isLoadingCategories.value = false;
    }
};

// ── FILTRI ────────────────────────────────────────────────────────
const selectParent = (id) => {
    const numId = Number(id);
    if (selectedParent.value === numId) {
        // Otreiz klikšķis atceļ filtru
        selectedParent.value = null;
        selectedChild.value  = null;
    } else {
        selectedParent.value = numId;
        selectedChild.value  = null;
    }
    fetchProducts();
};

const selectChild = (id) => {
    const numId = Number(id);
    selectedChild.value = selectedChild.value === numId ? null : numId;
    fetchProducts();
};

const clearAll = () => {
    selectedParent.value = null;
    selectedChild.value  = null;
    fetchProducts();
};

watch(searchQuery, () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(fetchProducts, 500);
});

// ── GROZS ─────────────────────────────────────────────────────────
const addToCart = async (product) => {
    if (product.has_sizes) {
        window.location.href = `/shop/product/${product.slug}`;
        return;
    }
    try {
        const res = await axios.post('/cart/add', { product_id: product.id, quantity: 1 });
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: res.data.cart.total_items }
        }));
        displayToast(
            `${getProductName(product)} ${locale.value === 'lv' ? 'pievienots grozam!' : 'added to cart!'}`,
            'success'
        );
    } catch (error) {
        displayToast(
            error.response?.data?.message ||
            (locale.value === 'lv' ? 'Kļūda! Lūdzu mēģiniet vēlreiz.' : 'Error! Please try again.'),
            'error'
        );
    }
};

onMounted(() => { fetchCategories(); fetchProducts(); });

// ── TOAST ─────────────────────────────────────────────────────────
const showToast    = ref(false);
const toastMessage = ref('');
const toastType    = ref('success');
const displayToast = (message, type = 'success') => {
    toastMessage.value = message; toastType.value = type; showToast.value = true;
};
const closeToast = () => { showToast.value = false; };
</script>

<template>
    <Head :title="$t('nav.shop')" />
    <ShopLayout>
        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="closeToast" />

        <!-- ── HERO ── -->
        <section class="shop-hero">
            <div class="hero-inner">
                <h1 class="hero-title">{{ $t('shop.hero.title') }}</h1>
                <p class="hero-subtitle">{{ $t('shop.hero.subtitle') }}</p>
            </div>
            <div class="hero-wave">
                <svg viewBox="0 0 1440 100" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path fill="#f9fafb" d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,64C960,75,1056,85,1152,80C1248,75,1344,53,1392,42.7L1440,32L1440,100L0,100Z"></path>
                </svg>
            </div>
        </section>

        <!-- ── KATEGORIJU SCROLLERIS ── -->
        <section class="cats-bar">
            <div class="wrap">
                <!-- Ielāde -->
                <div v-if="isLoadingCategories" class="cats-spinner">
                    <LoadingSpinner size="sm" />
                </div>

                <template v-else>
                    <!-- Parent pills -->
                    <div class="cats-scroll">
                        <button
                            @click="clearAll"
                            :class="['cat-pill', { 'cat-pill--active': !selectedParent && !selectedChild }]"
                        >
                            <i class="fas fa-th"></i>
                            <span>{{ locale === 'lv' ? 'Visi' : 'All' }}</span>
                        </button>

                        <button
                            v-for="cat in parentCategories"
                            :key="cat.id"
                            @click="selectParent(cat.id)"
                            :class="['cat-pill', { 'cat-pill--active': selectedParent === cat.id }]"
                        >
                            <i :class="getCategoryIcon(cat)"></i>
                            <span>{{ getCategoryName(cat) }}</span>
                            <span class="pill-count">{{ cat.product_count || 0 }}</span>
                        </button>
                    </div>

                    <!-- Apakškategoriju chips -->
                    <Transition name="subcats">
                        <div v-if="childCategories.length > 0" class="subcats-row">
                            <span class="subcats-indent"><i class="fas fa-level-down-alt"></i></span>
                            <button
                                v-for="child in childCategories"
                                :key="child.id"
                                @click="selectChild(child.id)"
                                :class="['subcat-chip', { 'subcat-chip--active': selectedChild === child.id }]"
                            >
                                <i :class="getCategoryIcon(child)"></i>
                                <span>{{ getCategoryName(child) }}</span>
                            </button>
                        </div>
                    </Transition>
                </template>
            </div>
        </section>

        <!-- ── PRODUKTI ── -->
        <section class="products-section">
            <div class="wrap">
                <!-- Header ar filtriem -->
                <div class="section-head">
                    <div class="section-head-left">
                        <h2 class="section-title">{{ activeCategoryName }}</h2>
                        <p v-if="!isLoadingProducts" class="products-count">
                            {{ products.length }} {{ $t('shop.products_count') }}
                        </p>
                    </div>

                    <div class="filters">
                        <!-- Meklēšana -->
                        <div class="search-wrap">
                            <i class="fas fa-search search-ico"></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="$t('common.search')"
                                class="search-inp"
                            >
                            <button v-if="searchQuery" @click="searchQuery = ''" class="search-clr">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Kārtošana -->
                        <select @change="fetchProducts" v-model="sortBy" class="sort-sel">
                            <option value="newest">{{ $t('shop.filter.newest') }}</option>
                            <option value="price-low">{{ $t('shop.filter.price_low') }}</option>
                            <option value="price-high">{{ $t('shop.filter.price_high') }}</option>
                            <option value="popular">{{ $t('shop.filter.popular') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Ielāde -->
                <div v-if="isLoadingProducts" class="loading-wrap">
                    <LoadingSpinner size="lg" :text="$t('common.loading')" />
                </div>

                <!-- Grid -->
                <div v-else class="products-grid">
                    <div v-for="product in products" :key="product.id" class="product-card">
                        <a :href="`/shop/product/${product.slug}`" class="card-link">
                            <div class="card-img">
                                <img
                                    :src="getProductImage(product)"
                                    :alt="getProductName(product)"
                                    @error="$event.target.src='/img/Products/placeholder.png'"
                                >
                                <!-- Badges -->
                                <span v-if="getDiscountPct(product) > 0" class="badge badge-sale">
                                    -{{ getDiscountPct(product) }}%
                                </span>
                                <span v-if="!isInStock(product)" class="badge badge-oos">
                                    {{ $t('product.out_of_stock') }}
                                </span>
                                <span v-else-if="product.stock_quantity <= product.low_stock_threshold" class="badge badge-low">
                                    {{ $t('product.low_stock') }}
                                </span>
                                <span v-if="product.has_sizes" class="badge badge-sizes">
                                    <i class="fas fa-ruler-horizontal"></i>
                                </span>
                            </div>

                            <div class="card-body">
                                <h3 class="card-name">{{ getProductName(product) }}</h3>
                                <div class="card-price">
                                    <span v-if="product.compare_price" class="price-old">
                                        €{{ formatPrice(product.compare_price) }}
                                    </span>
                                    <span class="price-now">€{{ formatPrice(product.price) }}</span>
                                </div>
                                <p v-if="product.vat_amount" class="price-vat">
                                    t.sk. PVN: €{{ formatPrice(product.vat_amount) }}
                                </p>
                            </div>
                        </a>

                        <button
                            class="card-btn"
                            :disabled="!isInStock(product)"
                            @click.prevent="addToCart(product)"
                        >
                            <i :class="product.has_sizes ? 'fas fa-ruler-horizontal' : 'fas fa-cart-plus'"></i>
                            {{ isInStock(product)
                            ? (product.has_sizes
                                ? (locale === 'lv' ? 'Izvēlies izmēru' : 'Select size')
                                : $t('common.add_to_cart'))
                            : $t('product.out_of_stock') }}
                        </button>
                    </div>

                    <!-- Tukšs -->
                    <div v-if="products.length === 0" class="empty-state">
                        <i class="fas fa-shopping-bag empty-icon"></i>
                        <p class="empty-text">{{ $t('shop.no_products') }}</p>
                        <button @click="clearAll" class="empty-btn">
                            {{ locale === 'lv' ? 'Rādīt visus produktus' : 'Show all products' }}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </ShopLayout>
</template>

<style scoped>
/* ── HERO ─────────────────────────────────────────────────────── */
.shop-hero {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    color: white;
    padding: 4.5rem 1.5rem 6rem;
    overflow: hidden;
    text-align: center;
}
.hero-inner { position: relative; z-index: 1; max-width: 720px; margin: 0 auto; }
.hero-title    { font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 800; margin-bottom: .75rem; }
.hero-subtitle { font-size: clamp(1rem, 2.5vw, 1.375rem); opacity: .9; }
.hero-wave     { position: absolute; bottom: -1px; left: 0; width: 100%; line-height: 0; }
.hero-wave svg { display: block; width: calc(100% + 4px); height: 80px; margin-left: -2px; }

/* ── KATEGORIJU BAR ───────────────────────────────────────────── */
.cats-bar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 64px; /* tieši zem navbar */
    z-index: 80;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.wrap { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

.cats-spinner { display: flex; justify-content: center; padding: 1rem; }

.cats-scroll {
    display: flex;
    gap: .5rem;
    overflow-x: auto;
    padding: .875rem 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.cats-scroll::-webkit-scrollbar { display: none; }

.cat-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .45rem .9rem;
    border-radius: 999px;
    border: 2px solid #e5e7eb;
    background: white; color: #374151;
    font-size: .875rem; font-weight: 600;
    white-space: nowrap; cursor: pointer;
    transition: all .2s; flex-shrink: 0;
}
.cat-pill:hover { border-color: #dc2626; color: #dc2626; background: #fff5f5; transform: translateY(-1px); }
.cat-pill--active {
    border-color: #dc2626; background: #dc2626; color: white;
    box-shadow: 0 4px 12px rgba(220,38,38,.3);
    transform: translateY(-1px);
}
.pill-count {
    font-size: .7rem; padding: .1rem .4rem;
    background: rgba(0,0,0,.1); border-radius: 999px;
}
.cat-pill--active .pill-count { background: rgba(255,255,255,.25); }

/* Apakškategorijas */
.subcats-row {
    display: flex; align-items: center; gap: .4rem;
    padding: .5rem 0 .875rem;
    overflow-x: auto;
    scrollbar-width: none;
}
.subcats-row::-webkit-scrollbar { display: none; }
.subcats-indent { color: #9ca3af; font-size: .8rem; flex-shrink: 0; padding-left: .25rem; }

.subcat-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .35rem .8rem;
    border-radius: 999px;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb; color: #6b7280;
    font-size: .8125rem; font-weight: 500;
    white-space: nowrap; cursor: pointer;
    transition: all .2s; flex-shrink: 0;
}
.subcat-chip:hover { border-color: #dc2626; color: #dc2626; background: #fff5f5; }
.subcat-chip--active {
    border-color: #dc2626; background: #fef2f2; color: #dc2626; font-weight: 700;
}

.subcats-enter-active, .subcats-leave-active { transition: all .25s ease; }
.subcats-enter-from, .subcats-leave-to { opacity: 0; transform: translateY(-6px); }

/* ── PRODUKTI ─────────────────────────────────────────────────── */
.products-section { padding: 2rem 1.5rem 4rem; background: #f9fafb; }

.section-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;
}
.section-head-left { display: flex; flex-direction: column; gap: .2rem; }
.section-title  { font-size: clamp(1.25rem, 3vw, 1.75rem); font-weight: 800; color: #111827; margin: 0; }
.products-count { font-size: .875rem; color: #9ca3af; margin: 0; }

.filters { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; }

.search-wrap { position: relative; }
.search-ico {
    position: absolute; left: .75rem; top: 50%;
    transform: translateY(-50%); color: #9ca3af; font-size: .875rem;
    pointer-events: none;
}
.search-inp {
    padding: .575rem 2.25rem .575rem 2.25rem;
    border: 2px solid #e5e7eb; border-radius: .5rem;
    font-size: .9375rem; width: 220px;
    transition: all .25s;
    background: white;
}
.search-inp:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
.search-clr {
    position: absolute; right: .625rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: #9ca3af; font-size: .8rem; transition: color .2s;
}
.search-clr:hover { color: #dc2626; }

.sort-sel {
    padding: .575rem 1rem; border: 2px solid #e5e7eb; border-radius: .5rem;
    font-size: .9375rem; font-weight: 600; background: white; cursor: pointer;
    transition: border-color .25s;
}
.sort-sel:focus { outline: none; border-color: #dc2626; }
.sort-sel:hover { border-color: #dc2626; }

.loading-wrap { display: flex; justify-content: center; align-items: center; min-height: 400px; }

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

/* Karte */
.product-card {
    background: white; border-radius: .875rem;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    transition: transform .3s, box-shadow .3s;
    display: flex; flex-direction: column;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0,0,0,.13);
}

.card-link { display: flex; flex-direction: column; text-decoration: none; color: inherit; flex: 1; }
.card-img {
    position: relative; height: 220px;
    overflow: hidden; background: #f9fafb; flex-shrink: 0;
}
.card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.product-card:hover .card-img img { transform: scale(1.06); }

/* Badges */
.badge {
    position: absolute;
    padding: .2rem .6rem; border-radius: .3rem;
    font-size: .75rem; font-weight: 700;
}
.badge-sale { top: .75rem; left: .75rem; background: #dc2626; color: white; }
.badge-oos  { top: .75rem; right: .75rem; background: #6b7280; color: white; }
.badge-low  { top: .75rem; right: .75rem; background: #f59e0b; color: white; }
.badge-sizes { bottom: .75rem; right: .75rem; background: rgba(0,0,0,.5); color: white; font-size: .7rem; }

.card-body { padding: 1rem 1rem .75rem; flex: 1; }
.card-name {
    font-size: .9375rem; font-weight: 600; color: #111827; margin-bottom: .5rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    min-height: 2.75rem;
}
.card-price { display: flex; align-items: center; gap: .5rem; }
.price-old  { text-decoration: line-through; color: #9ca3af; font-size: .8125rem; }
.price-now  { font-size: 1.175rem; font-weight: 800; color: #dc2626; }
.price-vat  { font-size: .7rem; color: #9ca3af; margin-top: .2rem; }

.card-btn {
    width: 100%; padding: .7rem;
    background: #dc2626; color: white;
    border: none; border-radius: 0 0 .875rem .875rem;
    font-weight: 600; font-size: .875rem;
    cursor: pointer; transition: background .2s;
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    flex-shrink: 0;
}
.card-btn:hover:not(:disabled) { background: #b91c1c; }
.card-btn:disabled { background: #9ca3af; cursor: not-allowed; }

/* Tukšs stāvoklis */
.empty-state { grid-column: 1/-1; text-align: center; padding: 4rem 1.5rem; }
.empty-icon  { font-size: 3.5rem; color: #d1d5db; margin-bottom: 1.25rem; display: block; }
.empty-text  { font-size: 1.125rem; color: #9ca3af; margin-bottom: 1.5rem; }
.empty-btn   {
    padding: .7rem 1.75rem; background: #dc2626; color: white;
    border: none; border-radius: .5rem; font-weight: 600; cursor: pointer; transition: background .2s;
}
.empty-btn:hover { background: #b91c1c; }

/* ── RESPONSĪVS ───────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .products-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .products-grid { grid-template-columns: repeat(2, 1fr); }
    .section-head  { align-items: flex-start; }
    .filters       { width: 100%; }
    .search-inp    { width: 100%; }
    .sort-sel      { width: 100%; }
    .cats-bar      { top: 64px; }
}
@media (max-width: 480px) {
    .products-grid { grid-template-columns: 1fr; }
    .hero-wave svg { height: 50px; }
    .wrap          { padding: 0 1rem; }
    .products-section { padding: 1.5rem 1rem 3rem; }
}
</style>
