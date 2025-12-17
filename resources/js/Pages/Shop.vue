<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const isLoadingProducts = ref(true);
const isLoadingCategories = ref(true);
const products = ref([]);
const categories = ref([]);
const selectedCategory = ref(null);
const sortBy = ref('newest');
const searchQuery = ref('');
const searchTimeout = ref(null);

// SAFE price conversion
const toNumber = (value) => {
    if (value === null || value === undefined) return 0;
    return parseFloat(value) || 0;
};

const formatPrice = (price) => {
    return toNumber(price).toFixed(2);
};

// Bilingual helpers
const getProductName = (product) => {
    return locale.value === 'lv' ? product.name_lv : product.name_en;
};

const getCategoryName = (category) => {
    return locale.value === 'lv' ? category.name_lv : category.name_en;
};

const getDiscountPercentage = (product) => {
    const price = toNumber(product.price);
    const comparePrice = toNumber(product.compare_price);
    if (!comparePrice || !price || price >= comparePrice) return 0;
    return Math.round((1 - price / comparePrice) * 100);
};

const isInStock = (product) => {
    return product.stock_quantity > 0 && product.is_active;
};

const getProductImage = (product) => {
    return product.image || '/img/Products/placeholder.png';
};

// Fetch products
const fetchProducts = async () => {
    isLoadingProducts.value = true;
    try {
        const params = [];

        if (selectedCategory.value) {
            params.push(`category=${selectedCategory.value}`);
        }

        if (sortBy.value) {
            params.push(`sort=${sortBy.value}`);
        }

        if (searchQuery.value.trim()) {
            params.push(`search=${encodeURIComponent(searchQuery.value)}`);
        }

        const url = `/api/v1/products${params.length > 0 ? '?' + params.join('&') : ''}`;
        const response = await axios.get(url);
        products.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
        products.value = [];
    } finally {
        isLoadingProducts.value = false;
    }
};

// Fetch categories
const fetchCategories = async () => {
    isLoadingCategories.value = true;
    try {
        const response = await axios.get('/api/v1/categories');
        categories.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
        categories.value = [];
    } finally {
        isLoadingCategories.value = false;
    }
};

// Filter by category
const filterByCategory = (categoryId) => {
    selectedCategory.value = categoryId;
    fetchProducts();
};

// Sort products
const handleSort = () => {
    fetchProducts();
};

// Search with debounce
watch(searchQuery, () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        fetchProducts();
    }, 500); // 500ms debounce
});

// Add to cart with count update
const addToCart = async (product) => {
    try {
        const response = await axios.post('/cart/add', {
            product_id: product.id,
            quantity: 1,
        });

        // Update cart count
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: response.data.cart.total_items }
        }));

        // Show success toast
        displayToast(
            `${getProductName(product)} ${locale.value === 'lv' ? 'pievienots grozam!' : 'added to cart!'}`,
            'success'
        );
    } catch (error) {
        console.error('Error adding to cart:', error);

        // Show error toast
        const errorMessage = error.response?.data?.message ||
            (locale.value === 'lv' ? 'Kļūda! Lūdzu mēģiniet vēlreiz.' : 'Error! Please try again.');

        displayToast(errorMessage, 'error');
    }
};

onMounted(() => {
    fetchProducts();
    fetchCategories();
});

// Toast notification state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const displayToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

const closeToast = () => {
    showToast.value = false;
};
</script>

<template>
    <Head :title="$t('nav.shop')" />

    <ShopLayout>
        <!-- Toast Notification -->
        <ToastNotification
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="closeToast"
        />

        <!-- Shop Hero -->
        <section class="shop-hero">
            <div class="hero-container">
                <h1 class="hero-title">{{ $t('shop.hero.title') }}</h1>
                <p class="hero-subtitle">{{ $t('shop.hero.subtitle') }}</p>
            </div>
            <!-- Wave -->
            <div class="hero-wave">
                <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path
                        fill="#ffffff"
                        d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,64C960,75,1056,85,1152,80C1248,75,1344,53,1392,42.7L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"
                    ></path>
                </svg>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories-section">
            <div class="section-container">
                <h2 class="section-title">{{ $t('shop.categories') }}</h2>

                <!-- Loading Categories -->
                <div v-if="isLoadingCategories" class="loading-container-small">
                    <LoadingSpinner size="md" />
                </div>

                <!-- Categories Grid -->
                <div v-else class="categories-grid">
                    <!-- All Products Button -->
                    <button
                        @click="filterByCategory(null)"
                        :class="['category-card', { active: selectedCategory === null }]"
                    >
                        <div class="category-icon">
                            <i class="fas fa-th"></i>
                        </div>
                        <h3 class="category-name">{{ $t('shop.all_products') }}</h3>
                        <p class="category-count">{{ products.length }} {{ $t('shop.products_count') }}</p>
                    </button>

                    <!-- Category Buttons -->
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        @click="filterByCategory(category.id)"
                        :class="['category-card', { active: selectedCategory === category.id }]"
                    >
                        <div class="category-icon">
                            <i :class="category.icon || 'fas fa-tag'"></i>
                        </div>
                        <h3 class="category-name">{{ getCategoryName(category) }}</h3>
                        <p class="category-count">{{ category.product_count || 0 }} {{ $t('shop.products_count') }}</p>
                    </button>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">
                        {{ selectedCategory ? getCategoryName(categories.find(c => c.id === selectedCategory)) : $t('shop.all_products') }}
                    </h2>
                    <div class="filters">
                        <!-- Search Bar -->
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="$t('common.search')"
                                class="search-input"
                            >
                        </div>

                        <!-- Sort Dropdown -->
                        <select @change="handleSort" v-model="sortBy" class="filter-select">
                            <option value="newest">{{ $t('shop.filter.newest') }}</option>
                            <option value="price-low">{{ $t('shop.filter.price_low') }}</option>
                            <option value="price-high">{{ $t('shop.filter.price_high') }}</option>
                            <option value="popular">{{ $t('shop.filter.popular') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div v-if="isLoadingProducts" class="loading-container">
                    <LoadingSpinner size="lg" :text="$t('common.loading')" />
                </div>

                <!-- Products Grid -->
                <div v-else class="products-grid">
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="product-card"
                    >
                        <a :href="`/shop/product/${product.slug}`" class="product-link">
                            <div class="product-image">
                                <img
                                    :src="getProductImage(product)"
                                    :alt="getProductName(product)"
                                    @error="$event.target.src = '/img/Products/placeholder.png'"
                                >
                                <!-- Discount Badge -->
                                <span v-if="getDiscountPercentage(product) > 0" class="product-badge">
                                    -{{ getDiscountPercentage(product) }}%
                                </span>
                                <!-- Out of Stock Badge -->
                                <span v-if="!isInStock(product)" class="out-of-stock-badge">
                                    {{ $t('product.out_of_stock') }}
                                </span>
                                <!-- Low Stock Warning -->
                                <span v-else-if="product.stock_quantity <= product.low_stock_threshold" class="low-stock-badge">
                                    {{ $t('product.low_stock') }}
                                </span>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name">{{ getProductName(product) }}</h3>
                                <div class="product-price">
                                    <span v-if="product.compare_price" class="price-original">
                                        €{{ formatPrice(product.compare_price) }}
                                    </span>
                                    <span class="price-current">
                                        €{{ formatPrice(product.price) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                        <button
                            class="add-to-cart-button"
                            :disabled="!isInStock(product)"
                            @click.prevent="addToCart(product)"
                        >
                            {{ isInStock(product) ? $t('common.add_to_cart') : $t('product.out_of_stock') }}
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="products.length === 0" class="empty-state">
                        <i class="fas fa-shopping-bag empty-icon"></i>
                        <p class="empty-text">{{ $t('shop.no_products') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </ShopLayout>
</template>

<style scoped>
/* [Previous CSS remains exactly the same until section-header] */

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 2rem;
}

.filters {
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* Search Container */
.search-container {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1rem;
}

.search-input {
    padding: 0.75rem 1.5rem 0.75rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    width: 250px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.filter-select {
    padding: 0.75rem 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 200px;
}

.filter-select:hover {
    border-color: #dc2626;
}

.filter-select:focus {
    outline: none;
    border-color: #dc2626;
}

/* [All other CSS remains the same] */

/* Hero Section */
.shop-hero {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.hero-container {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
    z-index: 1;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.5rem;
    opacity: 0.95;
}

.hero-wave {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}

.hero-wave svg {
    position: relative;
    display: block;
    width: calc(100% + 4px);
    height: 120px;
    margin-left: -2px;
}

.categories-section {
    padding: 4rem 2rem;
    background-color: #f9fafb;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 2rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .categories-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 640px) {
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.category-card {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    border-color: #dc2626;
}

.category-card.active {
    border-color: #dc2626;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.category-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.category-card.active .category-icon {
    background: white;
    color: #dc2626;
}

.category-name {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.category-count {
    font-size: 0.875rem;
    opacity: 0.7;
}

.products-section {
    padding: 4rem 2rem;
}

.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

@media (max-width: 1024px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .filters {
        width: 100%;
        flex-direction: column;
    }

    .search-input {
        width: 100%;
    }

    .filter-select {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
}

.product-card {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.product-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.product-image {
    position: relative;
    height: 250px;
    overflow: hidden;
    background-color: #f9fafb;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    background-color: #dc2626;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 700;
}

.out-of-stock-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background-color: #6b7280;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.low-stock-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background-color: #f59e0b;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.product-info {
    padding: 1.5rem;
}

.product-name {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 3rem;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-original {
    text-decoration: line-through;
    color: #9ca3af;
    font-size: 0.875rem;
}

.price-current {
    font-size: 1.25rem;
    font-weight: 700;
    color: #dc2626;
}

.add-to-cart-button {
    width: 100%;
    padding: 0.75rem;
    background-color: #dc2626;
    color: white;
    border: none;
    border-radius: 0 0 0.75rem 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.add-to-cart-button:hover:not(:disabled) {
    background-color: #b91c1c;
}

.add-to-cart-button:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 5rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-text {
    font-size: 1.125rem;
    color: #9ca3af;
}

@media (max-width: 640px) {
    .hero-title {
        font-size: 2rem;
    }
}
</style>
