<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const props = defineProps({
    slug: String,
});

const isLoading = ref(true);
const productData = ref(null);
const quantity = ref(1);
const relatedProducts = ref([]);
const isAddingToCart = ref(false);

// SAFE: Convert string to number
const toNumber = (value) => {
    if (value === null || value === undefined) return 0;
    return parseFloat(value) || 0;
};

// SAFE: Format price
const formatPrice = (price) => {
    return toNumber(price).toFixed(2);
};

// Bilingual helpers
const getProductName = computed(() => {
    if (!productData.value) return '';
    return locale.value === 'lv' ? productData.value.name_lv : productData.value.name_en;
});

const getProductDescription = computed(() => {
    if (!productData.value) return '';
    return locale.value === 'lv' ? productData.value.description_lv : productData.value.description_en;
});

const getCategoryName = computed(() => {
    if (!productData.value?.category) return '';
    return locale.value === 'lv' ? productData.value.category.name_lv : productData.value.category.name_en;
});

const discountPercentage = computed(() => {
    const price = toNumber(productData.value?.price);
    const comparePrice = toNumber(productData.value?.compare_price);

    if (!comparePrice || !price || price >= comparePrice) return 0;
    return Math.round((1 - price / comparePrice) * 100);
});

const isInStock = computed(() => {
    return productData.value?.stock_quantity > 0 && productData.value?.is_active;
});

const productImage = computed(() => {
    return productData.value?.image || '/img/placeholder.png';
});

// Fetch product from API
const fetchProduct = async () => {
    const slug = props.slug || window.location.pathname.split('/').pop();

    isLoading.value = true;
    try {
        const response = await axios.get(`/api/v1/products/${slug}`);
        productData.value = response.data;

        // Fetch related products after getting product
        fetchRelatedProducts();
    } catch (error) {
        console.error('Error fetching product:', error);
        productData.value = null;
    } finally {
        isLoading.value = false;
    }
};

// Fetch related products
const fetchRelatedProducts = async () => {
    if (!productData.value?.category_id) return;

    try {
        const response = await axios.get(`/api/v1/products?category=${productData.value.category_id}`);
        const allProducts = response.data.data || response.data;
        relatedProducts.value = allProducts
            .filter(p => p.id !== productData.value.id)
            .slice(0, 4);
    } catch (error) {
        console.error('Error fetching related products:', error);
    }
};

// Quantity controls
const increaseQuantity = () => {
    if (quantity.value < productData.value.stock_quantity) {
        quantity.value++;
    }
};

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

// Add to cart
const addToCart = async () => {
    if (!isInStock.value) return;

    isAddingToCart.value = true;
    try {
        await axios.post('/cart/add', {
            product_id: productData.value.id,
            quantity: quantity.value,
        });

        alert(`${quantity.value}x ${getProductName.value} ${locale.value === 'lv' ? 'pievienots grozam!' : 'added to cart!'}`);
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert(locale.value === 'lv' ? 'Kļūda! Lūdzu mēģiniet vēlreiz.' : 'Error! Please try again.');
    } finally {
        isAddingToCart.value = false;
    }
};

// Get related product name
const getRelatedName = (product) => {
    return locale.value === 'lv' ? product.name_lv : product.name_en;
};

onMounted(() => {
    fetchProduct();
});
</script>

<template>
    <Head :title="getProductName || 'Product'" />

    <ShopLayout>
        <!-- Loading State -->
        <div v-if="isLoading" class="loading-container">
            <LoadingSpinner size="lg" :text="$t('common.loading')" />
        </div>

        <!-- Product Detail -->
        <div v-else-if="productData" class="product-detail">
            <!-- Breadcrumb -->
            <div class="breadcrumb-section">
                <div class="section-container">
                    <div class="breadcrumb">
                        <a href="/" class="breadcrumb-link">{{ $t('nav.home') }}</a>
                        <span class="breadcrumb-separator">/</span>
                        <a href="/shop" class="breadcrumb-link">{{ $t('nav.shop') }}</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-current">{{ getProductName }}</span>
                    </div>
                </div>
            </div>

            <!-- Product Content -->
            <section class="product-section">
                <div class="section-container">
                    <div class="product-grid">
                        <!-- Product Image -->
                        <div class="product-images">
                            <div class="main-image">
                                <img
                                    :src="productImage"
                                    :alt="getProductName"
                                    @error="$event.target.src = '/img/placeholder.png'"
                                >
                                <span v-if="discountPercentage > 0" class="discount-badge">
                                    -{{ discountPercentage }}%
                                </span>
                                <span v-if="!isInStock" class="out-of-stock-badge">
                                    {{ $t('product.out_of_stock') }}
                                </span>
                                <span v-else-if="productData.stock_quantity <= productData.low_stock_threshold" class="low-stock-badge">
                                    {{ $t('product.low_stock') }}
                                </span>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="product-info-section">
                            <h1 class="product-title">{{ getProductName }}</h1>

                            <!-- SKU -->
                            <p v-if="productData.sku" class="product-sku">
                                SKU: {{ productData.sku }}
                            </p>

                            <!-- Price -->
                            <div class="product-pricing">
                                <span v-if="productData.compare_price" class="price-original">
                                    €{{ formatPrice(productData.compare_price) }}
                                </span>
                                <span class="price-current">
                                    €{{ formatPrice(productData.price) }}
                                </span>
                                <span v-if="discountPercentage > 0" class="save-badge">
                                    {{ $t('product.save') }} {{ discountPercentage }}%
                                </span>
                            </div>

                            <!-- Stock Status -->
                            <div :class="['stock-status', { 'out-of-stock': !isInStock }]">
                                <i :class="isInStock ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                                <span>
                                    <template v-if="isInStock">
                                        {{ $t('product.in_stock') }} ({{ productData.stock_quantity }} {{ locale === 'lv' ? 'gab.' : 'pcs' }})
                                    </template>
                                    <template v-else>
                                        {{ $t('product.out_of_stock') }}
                                    </template>
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="product-description">
                                <p>{{ getProductDescription }}</p>
                            </div>

                            <!-- Quantity Selector -->
                            <div v-if="isInStock" class="quantity-section">
                                <label class="quantity-label">{{ $t('product.quantity') }}:</label>
                                <div class="quantity-controls">
                                    <button @click="decreaseQuantity" :disabled="quantity <= 1" class="qty-btn">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input
                                        v-model.number="quantity"
                                        type="number"
                                        min="1"
                                        :max="productData.stock_quantity"
                                        class="qty-input"
                                    >
                                    <button @click="increaseQuantity" :disabled="quantity >= productData.stock_quantity" class="qty-btn">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Add to Cart Button -->
                            <button
                                @click="addToCart"
                                :disabled="!isInStock || isAddingToCart"
                                class="add-to-cart-btn"
                            >
                                <i v-if="!isAddingToCart" class="fas fa-shopping-cart"></i>
                                <i v-else class="fas fa-spinner fa-spin"></i>
                                <template v-if="isAddingToCart">
                                    {{ $t('common.adding') }}
                                </template>
                                <template v-else-if="isInStock">
                                    {{ $t('common.add_to_cart') }}
                                </template>
                                <template v-else>
                                    {{ $t('product.out_of_stock') }}
                                </template>
                            </button>

                            <!-- Product Details -->
                            <div class="product-details">
                                <h3 class="details-title">{{ $t('product.details') }}</h3>
                                <ul class="details-list">
                                    <li v-if="productData.category">
                                        <strong>{{ $t('product.category') }}:</strong>
                                        <span>{{ getCategoryName }}</span>
                                    </li>
                                    <li>
                                        <strong>{{ $t('product.availability') }}:</strong>
                                        <span>{{ isInStock ? $t('product.in_stock') : $t('product.out_of_stock') }}</span>
                                    </li>
                                    <li v-if="productData.sku">
                                        <strong>SKU:</strong>
                                        <span>{{ productData.sku }}</span>
                                    </li>
                                    <li>
                                        <strong>{{ $t('product.stock_quantity') }}:</strong>
                                        <span>{{ productData.stock_quantity }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Related Products -->
            <section v-if="relatedProducts.length > 0" class="related-section">
                <div class="section-container">
                    <h2 class="section-title">{{ $t('product.related_products') }}</h2>
                    <div class="related-grid">
                        <a
                            v-for="related in relatedProducts"
                            :key="related.id"
                            :href="`/shop/product/${related.slug}`"
                            class="related-card"
                        >
                            <div class="related-image">
                                <img
                                    :src="related.image || '/img/placeholder.png'"
                                    :alt="getRelatedName(related)"
                                    @error="$event.target.src = '/img/placeholder.png'"
                                >
                            </div>
                            <h3 class="related-name">{{ getRelatedName(related) }}</h3>
                            <p class="related-price">€{{ formatPrice(related.price) }}</p>
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <!-- Error State -->
        <div v-else class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>{{ $t('product.not_found') }}</h2>
            <p>{{ locale === 'lv' ? 'Šis produkts neeksistē vai nav pieejams' : 'This product does not exist or is not available' }}</p>
            <a href="/shop" class="back-btn">{{ $t('shop.back_to_shop') }}</a>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* Loading */
.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
    padding: 4rem 2rem;
}

/* Breadcrumb */
.breadcrumb-section {
    background-color: #f9fafb;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
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

/* Product Section */
.product-section {
    padding: 4rem 2rem;
}

.product-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
}

@media (max-width: 1024px) {
    .product-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* Product Images */
.product-images {
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.main-image {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    background-color: #f9fafb;
    border-radius: 1rem;
    overflow: hidden;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.discount-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background-color: #dc2626;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 700;
}

.out-of-stock-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background-color: #6b7280;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.low-stock-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background-color: #f59e0b;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Product Info */
.product-info-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    line-height: 1.2;
}

.product-sku {
    font-size: 0.875rem;
    color: #6b7280;
}

.product-pricing {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.price-original {
    font-size: 1.5rem;
    color: #9ca3af;
    text-decoration: line-through;
}

.price-current {
    font-size: 2.5rem;
    font-weight: 800;
    color: #dc2626;
}

.save-badge {
    background-color: #fee2e2;
    color: #dc2626;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 700;
}

.stock-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background-color: #f0fdf4;
    border-radius: 0.5rem;
}

.stock-status.out-of-stock {
    background-color: #fef2f2;
}

.stock-status i {
    font-size: 1.25rem;
    color: #16a34a;
}

.stock-status.out-of-stock i {
    color: #dc2626;
}

.stock-status span {
    font-weight: 600;
    color: #166534;
}

.stock-status.out-of-stock span {
    color: #991b1b;
}

.product-description {
    font-size: 1.125rem;
    line-height: 1.75;
    color: #4b5563;
}

/* Quantity Section */
.quantity-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.quantity-label {
    font-weight: 600;
    color: #111827;
}

.quantity-controls {
    display: flex;
    align-items: center;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.qty-btn {
    width: 3rem;
    height: 3rem;
    border: none;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.qty-btn:hover:not(:disabled) {
    background-color: #f9fafb;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qty-input {
    width: 4rem;
    height: 3rem;
    border: none;
    border-left: 1px solid #e5e7eb;
    border-right: 1px solid #e5e7eb;
    text-align: center;
    font-size: 1.125rem;
    font-weight: 600;
}

.qty-input:focus {
    outline: none;
}

/* Add to Cart Button */
.add-to-cart-btn {
    width: 100%;
    padding: 1.25rem;
    background-color: #dc2626;
    color: white;
    border: none;
    border-radius: 0.75rem;
    font-size: 1.125rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.add-to-cart-btn:hover:not(:disabled) {
    background-color: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
}

.add-to-cart-btn:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

/* Product Details */
.product-details {
    padding: 2rem;
    background-color: #f9fafb;
    border-radius: 0.75rem;
}

.details-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
}

.details-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.details-list li {
    display: flex;
    justify-content: space-between;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.details-list li:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.details-list strong {
    color: #6b7280;
}

.details-list span {
    color: #111827;
    font-weight: 500;
}

/* Related Products */
.related-section {
    padding: 4rem 2rem;
    background-color: #f9fafb;
}

.section-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 2rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

@media (max-width: 1024px) {
    .related-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .related-grid {
        grid-template-columns: 1fr;
    }
}

.related-card {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.related-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.related-image {
    aspect-ratio: 1;
    background-color: #f9fafb;
    overflow: hidden;
}

.related-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.related-card:hover .related-image img {
    transform: scale(1.05);
}

.related-name {
    padding: 1rem 1rem 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-price {
    padding: 0 1rem 1rem;
    font-size: 1.125rem;
    font-weight: 700;
    color: #dc2626;
}

/* Error State */
.error-state {
    text-align: center;
    padding: 6rem 2rem;
}

.error-state i {
    font-size: 5rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.error-state h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
}

.error-state p {
    font-size: 1.125rem;
    color: #6b7280;
    margin-bottom: 2rem;
}

.back-btn {
    display: inline-block;
    padding: 1rem 2rem;
    background-color: #dc2626;
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background-color: #b91c1c;
    transform: translateY(-2px);
}

@media (max-width: 640px) {
    .product-title {
        font-size: 1.875rem;
    }

    .price-current {
        font-size: 2rem;
    }
}
</style>
