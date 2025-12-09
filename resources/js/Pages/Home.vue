<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
    featuredProducts: {
        type: Array,
        default: () => []
    },
    featuredContent: {
        type: Array,
        default: () => []
    }
});

// Helper functions
const getProductName = (product) => {
    return locale.value === 'lv' ? product.name_lv : (product.name_en || product.name_lv);
};

const getProductDescription = (product) => {
    return locale.value === 'lv' ? product.description_lv : (product.description_en || product.description_lv);
};

const getContentTitle = (content) => {
    return locale.value === 'lv' ? content.title_lv : (content.title_en || content.title_lv);
};

const getContentDescription = (content) => {
    return locale.value === 'lv' ? content.description_lv : (content.description_en || content.description_lv);
};

const getContentThumbnail = (content) => {
    // Fix thumbnail path
    if (content.thumbnail && !content.thumbnail.includes('img.thumbnails')) {
        return content.thumbnail;
    }

    // YouTube fallback
    if (content.type === 'video' && content.video_platform === 'YouTube') {
        // Try to extract video ID from common YouTube URL patterns
        return '/img/default-content.jpg';
    }

    return '/img/default-content.jpg';
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('lv-LV', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString(
        locale.value === 'lv' ? 'lv-LV' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
};
</script>

<template>
    <Head :title="t('nav.home')" />

    <MainLayout>
        <!-- Hero Section with Wave -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">{{ t('hero.title') }}</h1>
                    <p class="hero-subtitle">{{ t('hero.subtitle') }}</p>
                    <div class="hero-cta">
                        <Link href="/content" class="cta-button cta-button-primary">
                            {{ t('hero.cta_content') }}
                        </Link>
                        <Link href="/shop" class="cta-button cta-button-secondary">
                            {{ t('hero.cta_shop') }}
                        </Link>
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
                    <h2 class="section-title">{{ t('sections.featured_content') }}</h2>
                    <Link href="/content" class="section-link">{{ t('common.view_more') }}</Link>
                </div>

                <!-- Empty State -->
                <div v-if="featuredContent.length === 0" class="empty-state">
                    <i class="fas fa-video"></i>
                    <p>{{ t('home.no_featured_content') }}</p>
                </div>

                <!-- Content Grid -->
                <div v-else class="content-grid">
                    <Link
                        v-for="content in featuredContent.slice(0, 3)"
                        :key="content.id"
                        :href="`/content/${content.slug}`"
                        class="content-card"
                    >
                        <!-- Thumbnail -->
                        <div class="content-thumbnail">
                            <img :src="getContentThumbnail(content)" :alt="getContentTitle(content)">
                            <div class="content-overlay">
                                <i :class="content.type === 'video' ? 'fas fa-play-circle' : 'fas fa-newspaper'"></i>
                            </div>
                            <div class="content-badge" :class="`badge-${content.type}`">
                                {{ content.type === 'video' ? t('content.video') : t('content.blog') }}
                            </div>
                        </div>

                        <!-- Content Info -->
                        <div class="content-info">
                            <h3 class="content-title">{{ getContentTitle(content) }}</h3>
                            <p class="content-description">{{ getContentDescription(content) }}</p>

                            <!-- Meta -->
                            <div class="content-meta">
                                <span class="meta-item">
                                    <i class="fas fa-eye"></i>
                                    {{ content.view_count }}
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-heart"></i>
                                    {{ content.like_count }}
                                </span>
                                <span v-if="content.category" class="meta-item">
                                    <i class="fas fa-tag"></i>
                                    {{ content.category }}
                                </span>
                            </div>

                            <!-- Date -->
                            <div class="content-date">
                                <i class="fas fa-calendar"></i>
                                {{ formatDate(content.published_at) }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="featured-products-section">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">{{ t('sections.featured_products') }}</h2>
                    <Link href="/shop" class="section-link">{{ t('common.view_more') }}</Link>
                </div>

                <!-- Empty State -->
                <div v-if="featuredProducts.length === 0" class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>{{ t('home.no_featured_products') }}</p>
                </div>

                <!-- Products Grid -->
                <div v-else class="products-grid">
                    <Link
                        v-for="product in featuredProducts.slice(0, 3)"
                        :key="product.id"
                        :href="`/shop/product/${product.slug}`"
                        class="product-card"
                    >
                        <!-- Product Image -->
                        <div class="product-image">
                            <img :src="product.image || '/img/default-product.jpg'" :alt="getProductName(product)">
                            <div v-if="product.sale_price" class="sale-badge">
                                {{ t('shop.sale') }}
                            </div>
                            <div class="product-overlay">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="product-info">
                            <h3 class="product-title">{{ getProductName(product) }}</h3>
                            <p class="product-description">{{ getProductDescription(product) }}</p>

                            <!-- Rating -->
                            <div v-if="product.rating" class="product-rating">
                                <i v-for="star in 5" :key="star"
                                   class="fas fa-star"
                                   :class="{ 'star-filled': star <= product.rating }">
                                </i>
                                <span class="rating-text">{{ product.rating }}/5</span>
                            </div>

                            <!-- Price -->
                            <div class="product-price">
                                <span v-if="product.sale_price" class="price-original">{{ formatPrice(product.price) }}</span>
                                <span class="price-current">{{ formatPrice(product.sale_price || product.price) }}</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section">
            <div class="section-container">
                <div class="about-content">
                    <div class="about-text">
                        <h2 class="about-title">{{ t('home.about_title') }}</h2>
                        <p class="about-description">{{ t('home.about_description') }}</p>
                        <Link href="/about" class="btn-about">
                            {{ t('home.about_button') }}
                        </Link>
                    </div>
                    <div class="about-image">
                        <img src="/img/about-logo.png" alt="About RalphMania">
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
/* Hero Section */
.hero-section {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
}

.hero-content {
    text-align: center;
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 900;
    margin-bottom: 1.5rem;
    text-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.hero-subtitle {
    font-size: 1.5rem;
    font-weight: 500;
    margin-bottom: 2.5rem;
    opacity: 0.95;
}

.hero-cta {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-button {
    padding: 1rem 2.5rem;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 1.125rem;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.cta-button-primary {
    background: white;
    color: #dc2626;
}

.cta-button-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(255, 255, 255, 0.3);
}

.cta-button-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.cta-button-secondary:hover {
    background: white;
    color: #dc2626;
    transform: translateY(-3px);
}

.hero-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    line-height: 0;
}

.hero-wave svg {
    display: block;
    width: 100%;
    height: auto;
}

/* Section Common Styles */
.featured-content-section,
.featured-products-section {
    padding: 4rem 2rem;
    background: #f9fafb;
}

.featured-products-section {
    background: white;
}

.section-container {
    max-width: 1400px;
    margin: 0 auto;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
}

.section-link {
    color: #dc2626;
    font-weight: 700;
    font-size: 1.125rem;
    transition: all 0.2s;
}

.section-link:hover {
    color: #b91c1c;
    transform: translateX(4px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    font-size: 1.25rem;
    font-weight: 600;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.content-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
}

.content-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(220, 38, 38, 0.15);
}

.content-thumbnail {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
}

.content-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.content-card:hover .content-thumbnail img {
    transform: scale(1.1);
}

.content-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 4rem;
    height: 4rem;
    background: rgba(220, 38, 38, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.content-card:hover .content-overlay {
    opacity: 1;
}

.content-overlay i {
    color: white;
    font-size: 2rem;
}

.content-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-weight: 700;
    font-size: 0.875rem;
    color: white;
}

.badge-video {
    background: #dc2626;
}

.badge-blog {
    background: #3b82f6;
}

.content-info {
    padding: 1.5rem;
}

.content-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.content-description {
    color: #6b7280;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.content-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.meta-item i {
    color: #dc2626;
}

.content-date {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #9ca3af;
    font-size: 0.875rem;
}

.content-date i {
    color: #dc2626;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
}

.product-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(220, 38, 38, 0.15);
}

.product-image {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: #f3f4f6;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.sale-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #ef4444;
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-weight: 700;
    font-size: 0.875rem;
}

.product-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 4rem;
    height: 4rem;
    background: rgba(220, 38, 38, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-overlay i {
    color: white;
    font-size: 1.5rem;
}

.product-info {
    padding: 1.5rem;
}

.product-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-description {
    color: #6b7280;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.product-rating i {
    color: #d1d5db;
    font-size: 0.875rem;
}

.product-rating .star-filled {
    color: #fbbf24;
}

.rating-text {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 600;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.price-original {
    color: #9ca3af;
    text-decoration: line-through;
    font-size: 1rem;
}

.price-current {
    color: #dc2626;
    font-size: 1.5rem;
    font-weight: 800;
}

/* About Section */
.about-section {
    padding: 6rem 2rem;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.about-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.about-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 1.5rem;
}

.about-description {
    font-size: 1.125rem;
    color: #6b7280;
    line-height: 1.75;
    margin-bottom: 2rem;
}

.btn-about {
    display: inline-block;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border-radius: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-about:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(220, 38, 38, 0.3);
}

.about-image img {
    width: 200px;
    height: auto;
    justify-self: right;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1.125rem;
    }

    .section-title {
        font-size: 1.75rem;
    }

    .content-grid,
    .products-grid {
        grid-template-columns: 1fr;
    }

    .about-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .about-image {
        order: -1;
    }
}
</style>
