<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';

// Loading states
const isLoadingProducts = ref(true);
const isLoadingContent = ref(true);

// Data
const featuredProducts = ref([]);
const featuredContent = ref([]);

// Fetch featured products
const fetchFeaturedProducts = async () => {
    isLoadingProducts.value = true;
    try {
        const response = await axios.get('/api/v1/products/featured');
        featuredProducts.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        isLoadingProducts.value = false;
    }
};

// Fetch featured content
const fetchFeaturedContent = async () => {
    isLoadingContent.value = true;
    try {
        const response = await axios.get('/api/v1/content/featured');
        featuredContent.value = response.data;
    } catch (error) {
        console.error('Error fetching content:', error);
    } finally {
        isLoadingContent.value = false;
    }
};

onMounted(() => {
    fetchFeaturedProducts();
    fetchFeaturedContent();
});
</script>

<template>
    <Head title="Home" />

    <MainLayout>
        <!-- Hero Section with Wave -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">{{ $t('hero.title') }}</h1>
                    <p class="hero-subtitle">{{ $t('hero.subtitle') }}</p>
                    <div class="hero-cta">
                        <a href="/content" class="cta-button cta-button-primary">
                            {{ $t('hero.cta_content') }}
                        </a>
                        <a href="/shop" class="cta-button cta-button-secondary">
                            {{ $t('hero.cta_shop') }}
                        </a>
                    </div>
                </div>
            </div>
            <!-- Wave SVG -->
            <div class="hero-wave">
                <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path
                        fill="#ffffff"
                        d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,64C960,75,1056,85,1152,80C1248,75,1344,53,1392,42.7L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"
                    ></path>
                </svg>
            </div>
        </section>

        <!-- Featured Content Section -->
        <section class="featured-content-section">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">{{ $t('sections.featured_content') }}</h2>
                    <a href="/content" class="section-link">{{ $t('common.view_more') }}</a>
                </div>

                <!-- Loading Spinner for Content -->
                <div v-if="isLoadingContent" class="loading-container">
                    <LoadingSpinner size="lg" :text="$t('common.loading')" />
                </div>

                <!-- Content Grid -->
                <div v-else class="content-grid">
                    <div
                        v-for="content in featuredContent"
                        :key="content.id"
                        class="content-card"
                    >
                        <div class="content-thumbnail">
                            <img :src="content.thumbnail_url" :alt="content.title">
                            <span class="content-type-badge">{{ content.type }}</span>
                        </div>
                        <div class="content-info">
                            <h3 class="content-title">{{ content.title }}</h3>
                            <p class="content-description">{{ content.description }}</p>
                            <div class="content-meta">
                                <span class="content-views">
                                    <i class="fas fa-eye"></i>
                                    {{ content.view_count }}
                                </span>
                                <span class="content-date">{{ content.published_date }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="featuredContent.length === 0" class="empty-state">
                        <p>{{ $t('content.latest') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="featured-products-section">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">{{ $t('sections.featured_products') }}</h2>
                    <a href="/shop" class="section-link">{{ $t('common.view_more') }}</a>
                </div>

                <!-- Loading Spinner for Products -->
                <div v-if="isLoadingProducts" class="loading-container">
                    <LoadingSpinner size="lg" :text="$t('common.loading')" />
                </div>

                <!-- Products Grid -->
                <div v-else class="products-grid">
                    <div
                        v-for="product in featuredProducts"
                        :key="product.id"
                        class="product-card"
                    >
                        <div class="product-image">
                            <img :src="product.main_image_url" :alt="product.name_lv">
                            <span v-if="product.discount_percentage" class="product-badge">
                                -{{ product.discount_percentage }}%
                            </span>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">{{ product.name_lv }}</h3>
                            <div class="product-price">
                                <span v-if="product.discount_percentage" class="price-original">
                                    €{{ product.price }}
                                </span>
                                <span class="price-current">
                                    €{{ product.final_price || product.price }}
                                </span>
                            </div>
                            <button class="add-to-cart-button">
                                {{ $t('common.add_to_cart') }}
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="featuredProducts.length === 0" class="empty-state">
                        <p>{{ $t('sections.noProductsAvailable') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="section-container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">1M+</div>
                        <div class="stat-label">{{ $t('sections.stats.views') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">50K+</div>
                        <div class="stat-label">{{ $t('sections.stats.followers') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">200+</div>
                        <div class="stat-label">{{ $t('sections.stats.videos') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">100+</div>
                        <div class="stat-label">{{ $t('sections.stats.products') }}</div>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
/* ========== HERO SECTION WITH RED GRADIENT ========== */
.hero-section {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.hero-section::before {
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
    z-index: 1;
}

.hero-content {
    text-align: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    opacity: 0.95;
    font-weight: 300;
}

.hero-cta {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.cta-button {
    padding: 1rem 2.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.125rem;
}

.cta-button-primary {
    background-color: white;
    color: #dc2626;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cta-button-primary:hover {
    background-color: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.cta-button-secondary {
    background-color: transparent;
    color: white;
    border: 2px solid white;
}

.cta-button-secondary:hover {
    background-color: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

/* Wave SVG - FIXED FULL WIDTH */
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

@media (max-width: 768px) {
    .hero-section {
        padding: 4rem 2rem 6rem;
    }

    .hero-title {
        font-size: 2.5rem;
    }

    .hero-subtitle {
        font-size: 1.125rem;
    }

    .hero-cta {
        flex-direction: column;
        align-items: center;
    }

    .cta-button {
        width: 100%;
        max-width: 300px;
        text-align: center;
    }

    .hero-wave svg {
        height: 60px;
    }
}

/* ========== SECTIONS ========== */
.featured-content-section,
.featured-products-section {
    padding: 4rem 2rem;
}

.featured-products-section {
    background-color: #f9fafb;
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
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

.section-link {
    color: #dc2626;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.section-link:hover {
    color: #b91c1c;
    text-decoration: underline;
}

/* ========== LOADING CONTAINER ========== */
.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

/* ========== CONTENT GRID ========== */
.content-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

.content-card {
    background-color: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.content-thumbnail {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.content-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.content-card:hover .content-thumbnail img {
    transform: scale(1.05);
}

.content-type-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background-color: #dc2626;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.content-info {
    padding: 1.5rem;
}

.content-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.content-description {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.content-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: #9ca3af;
}

.content-views {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* ========== PRODUCTS GRID ========== */
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
    background-color: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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
    margin-bottom: 1rem;
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
    border-radius: 0.375rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.add-to-cart-button:hover {
    background-color: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
}

/* ========== STATS SECTION ========== */
.stats-section {
    background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
    color: white;
    padding: 4rem 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    text-align: center;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.stat-item {
    padding: 1rem;
}

.stat-value {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-label {
    font-size: 1rem;
    color: #9ca3af;
}

/* ========== EMPTY STATE ========== */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    color: #9ca3af;
}
</style>
