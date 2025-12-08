<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
    category: Object,
});

const isLoading = ref(true);
const products = ref([]);
const categoryData = ref(null);

// Get category slug from URL
const categorySlug = computed(() => {
    const path = window.location.pathname;
    return path.split('/').pop();
});

const fetchCategoryProducts = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/v1/categories/${categorySlug.value}`);
        categoryData.value = response.data;
        products.value = response.data.products || [];
    } catch (error) {
        console.error('Error fetching category:', error);
    } finally {
        isLoading.value = false;
    }
};

const getCategoryName = computed(() => {
    if (!categoryData.value) return '';
    return locale.value === 'lv' ? categoryData.value.name_lv : categoryData.value.name_en;
});

const getCategoryDescription = computed(() => {
    if (!categoryData.value) return '';
    return locale.value === 'lv' ? categoryData.value.description_lv : categoryData.value.description_en;
});

onMounted(() => {
    fetchCategoryProducts();
});
</script>

<template>
    <Head :title="getCategoryName || $t('shop.category')" />

    <ShopLayout>
        <!-- Category Hero -->
        <section class="category-hero">
            <div class="hero-container">
                <h1 class="hero-title">{{ getCategoryName }}</h1>
                <p v-if="getCategoryDescription" class="hero-subtitle">{{ getCategoryDescription }}</p>
            </div>
            <!-- Wave -->
            <div class="hero-wave">
                <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill="#ffffff"
                        d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,64C960,75,1056,85,1152,80C1248,75,1344,53,1392,42.7L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"
                    ></path>
                </svg>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">
                        {{ products.length }} {{ $t('shop.products_count') }}
                    </h2>
                    <div class="breadcrumb">
                        <a href="/shop" class="breadcrumb-link">{{ $t('nav.shop') }}</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-current">{{ getCategoryName }}</span>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div v-if="isLoading" class="loading-container">
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
                                <img :src="product.main_image_url" :alt="locale === 'lv' ? product.name_lv : product.name_en">
                                <span v-if="product.discount_percentage" class="product-badge">
                                    -{{ product.discount_percentage }}%
                                </span>
                                <span v-if="!product.is_in_stock" class="out-of-stock-badge">
                                    {{ $t('product.out_of_stock') }}
                                </span>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name">
                                    {{ locale === 'lv' ? product.name_lv : product.name_en }}
                                </h3>
                                <div class="product-price">
                                    <span v-if="product.discount_percentage" class="price-original">
                                        €{{ product.price }}
                                    </span>
                                    <span class="price-current">
                                        €{{ product.final_price || product.price }}
                                    </span>
                                </div>
                            </div>
                        </a>
                        <button class="add-to-cart-button" :disabled="!product.is_in_stock">
                            {{ product.is_in_stock ? $t('common.add_to_cart') : $t('product.out_of_stock') }}
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="products.length === 0" class="empty-state">
                        <i class="fas fa-box-open empty-icon"></i>
                        <p class="empty-text">{{ $t('shop.no_products_in_category') }}</p>
                        <a href="/shop" class="back-button">
                            {{ $t('shop.back_to_shop') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </ShopLayout>
</template>

<style scoped>
/* Hero Section */
.category-hero {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.category-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.1;
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
    font-size: 1.25rem;
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

/* Products Section */
.products-section {
    padding: 4rem 2rem;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.breadcrumb-link {
    color: #dc2626;
    text-decoration: none;
    font-weight: 500;
}

.breadcrumb-link:hover {
    text-decoration: underline;
}

.breadcrumb-separator {
    color: #9ca3af;
}

.breadcrumb-current {
    color: #6b7280;
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

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-text {
    font-size: 1.125rem;
    color: #9ca3af;
    margin-bottom: 2rem;
}

.back-button {
    display: inline-block;
    padding: 0.75rem 2rem;
    background-color: #dc2626;
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.back-button:hover {
    background-color: #b91c1c;
    transform: translateY(-2px);
}

@media (max-width: 640px) {
    .hero-title {
        font-size: 2rem;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>
