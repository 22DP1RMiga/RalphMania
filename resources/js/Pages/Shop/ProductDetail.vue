<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const props = defineProps({ slug: String });

const isLoading       = ref(true);
const productData     = ref(null);
const quantity        = ref(1);
const relatedProducts = ref([]);
const isAddingToCart  = ref(false);

// ── IZMĒRU LOĢIKA ────────────────────────────────────────────────
const SIZES            = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
const selectedSize     = ref(null);
const sizeError        = ref(false);  // Vai mēģināja pievienot bez izmēra

const hasSizes = computed(() => productData.value?.has_sizes === true);

const selectSize = (size) => {
    selectedSize.value = size;
    sizeError.value    = false;
};

// ── PALĪGFUNKCIJAS ───────────────────────────────────────────────
const toNumber    = (v) => (v === null || v === undefined) ? 0 : (parseFloat(v) || 0);
const formatPrice = (p) => toNumber(p).toFixed(2);

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
    const comp  = toNumber(productData.value?.compare_price);
    if (!comp || !price || price >= comp) return 0;
    return Math.round((1 - price / comp) * 100);
});

const isInStock = computed(() =>
    productData.value?.stock_quantity > 0 && productData.value?.is_active
);

const productImage = computed(() =>
    productData.value?.image || '/img/Products/placeholder.png'
);

// ── FETCH ────────────────────────────────────────────────────────
const fetchProduct = async () => {
    const slug = props.slug || window.location.pathname.split('/').pop();
    isLoading.value = true;
    try {
        const response  = await axios.get(`/api/v1/products/${slug}`);
        productData.value = response.data;
        fetchRelatedProducts();
    } catch (error) {
        productData.value = null;
    } finally {
        isLoading.value = false;
    }
};

const fetchRelatedProducts = async () => {
    if (!productData.value?.category_id) return;
    try {
        const res = await axios.get(`/api/v1/products?category=${productData.value.category_id}`);
        const all = res.data.data || res.data;
        relatedProducts.value = all.filter(p => p.id !== productData.value.id).slice(0, 4);
    } catch {}
};

// ── QUANTITY ─────────────────────────────────────────────────────
const increaseQuantity = () => {
    if (quantity.value < productData.value.stock_quantity) quantity.value++;
};
const decreaseQuantity = () => {
    if (quantity.value > 1) quantity.value--;
};

// ── ADD TO CART ──────────────────────────────────────────────────
const addToCart = async () => {
    if (!isInStock.value) return;

    // Ja produktam ir izmēri un neviens nav izvēlēts — rādi kļūdu
    if (hasSizes.value && !selectedSize.value) {
        sizeError.value = true;
        displayToast(
            locale.value === 'lv' ? 'Lūdzu izvēlies izmēru!' : 'Please select a size!',
            'error'
        );
        // Scroll to size selector
        document.querySelector('.size-selector')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    isAddingToCart.value = true;
    try {
        const response = await axios.post('/cart/add', {
            product_id: productData.value.id,
            quantity:   quantity.value,
            size:       selectedSize.value || null,  // Nodod izmēru uz backend
        });

        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: response.data.cart.total_items }
        }));

        const sizeText = selectedSize.value ? ` (${selectedSize.value})` : '';
        displayToast(
            `${quantity.value}x ${getProductName.value}${sizeText} ${locale.value === 'lv' ? 'pievienots grozam!' : 'added to cart!'}`,
            'success'
        );
    } catch (error) {
        displayToast(
            error.response?.data?.message ||
            (locale.value === 'lv' ? 'Kļūda! Lūdzu mēģiniet vēlreiz.' : 'Error! Please try again.'),
            'error'
        );
    } finally {
        isAddingToCart.value = false;
    }
};

const getRelatedName = (p) => locale.value === 'lv' ? p.name_lv : p.name_en;

onMounted(() => fetchProduct());

// Toast
const showToast   = ref(false);
const toastMessage = ref('');
const toastType    = ref('success');

const displayToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value    = type;
    showToast.value    = true;
};
const closeToast = () => { showToast.value = false; };
</script>

<template>
    <Head :title="getProductName || 'Product'" />
    <ShopLayout>
        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="closeToast" />

        <!-- Loading -->
        <div v-if="isLoading" class="loading-container">
            <LoadingSpinner size="lg" :text="$t('common.loading')" />
        </div>

        <!-- Product -->
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

                        <!-- Image -->
                        <div class="product-images">
                            <div class="main-image">
                                <img :src="productImage" :alt="getProductName" @error="$event.target.src = '/img/Products/placeholder.png'">
                                <span v-if="discountPercentage > 0" class="discount-badge">-{{ discountPercentage }}%</span>
                                <span v-if="!isInStock" class="out-of-stock-badge">{{ $t('product.out_of_stock') }}</span>
                                <span v-else-if="productData.stock_quantity <= productData.low_stock_threshold" class="low-stock-badge">{{ $t('product.low_stock') }}</span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="product-info-section">
                            <h1 class="product-title">{{ getProductName }}</h1>

                            <p v-if="productData.sku" class="product-sku">SKU: {{ productData.sku }}</p>

                            <!-- Price -->
                            <div class="product-pricing">
                                <span v-if="productData.compare_price" class="price-original">€{{ formatPrice(productData.compare_price) }}</span>
                                <span class="price-current">€{{ formatPrice(productData.price) }}</span>
                                <span v-if="discountPercentage > 0" class="save-badge">{{ $t('product.save') }} {{ discountPercentage }}%</span>
                            </div>
                            <!-- t.sk. PVN -->
                            <div v-if="productData.vat_amount" class="price-vat-note">
                                <span v-if="locale === 'lv'">
                                    t.sk. PVN ({{ productData.vat_rate || 21 }}%):
                                    <strong>€{{ formatPrice(productData.vat_amount) }}</strong>
                                    &nbsp;·&nbsp; bez PVN: <strong>€{{ formatPrice(productData.price_ex_vat) }}</strong>
                                </span>
                                <span v-else>
                                    incl. VAT ({{ productData.vat_rate || 21 }}%):
                                    <strong>€{{ formatPrice(productData.vat_amount) }}</strong>
                                    &nbsp;·&nbsp; excl. VAT: <strong>€{{ formatPrice(productData.price_ex_vat) }}</strong>
                                </span>
                            </div>

                            <!-- Stock status -->
                            <div :class="['stock-status', { 'out-of-stock': !isInStock }]">
                                <i :class="isInStock ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                                <span>
                                    <template v-if="isInStock">
                                        {{ $t('product.in_stock') }} ({{ productData.stock_quantity }} {{ locale === 'lv' ? 'gab.' : 'pcs' }})
                                    </template>
                                    <template v-else>{{ $t('product.out_of_stock') }}</template>
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="product-description">
                                <p>{{ getProductDescription }}</p>
                            </div>

                            <!-- ══ IZMĒRU IZVĒLNE ═══════════════════════════════════ -->
                            <div v-if="hasSizes && isInStock" class="size-selector" :class="{ 'size-error': sizeError }">
                                <div class="size-header">
                                    <span class="size-label">
                                        {{ locale === 'lv' ? 'Izvēlies izmēru' : 'Select Size' }}
                                        <span class="size-required">*</span>
                                    </span>
                                    <span v-if="selectedSize" class="size-selected-indicator">
                                        {{ locale === 'lv' ? 'Izvēlēts:' : 'Selected:' }}
                                        <strong>{{ selectedSize }}</strong>
                                    </span>
                                </div>
                                <div class="size-buttons">
                                    <button
                                        v-for="size in SIZES"
                                        :key="size"
                                        @click="selectSize(size)"
                                        :class="['size-btn', { 'size-btn-active': selectedSize === size }]"
                                        type="button"
                                    >
                                        {{ size }}
                                    </button>
                                </div>
                                <p v-if="sizeError" class="size-error-msg">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ locale === 'lv' ? 'Lūdzu izvēlies izmēru pirms pievienošanas grozam' : 'Please select a size before adding to cart' }}
                                </p>
                            </div>
                            <!-- ══════════════════════════════════════════════════════ -->

                            <!-- Quantity -->
                            <div v-if="isInStock" class="quantity-section">
                                <label class="quantity-label">{{ $t('product.quantity') }}:</label>
                                <div class="quantity-controls">
                                    <button @click="decreaseQuantity" :disabled="quantity <= 1" class="qty-btn">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input v-model.number="quantity" type="number" min="1" :max="productData.stock_quantity" class="qty-input">
                                    <button @click="increaseQuantity" :disabled="quantity >= productData.stock_quantity" class="qty-btn">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Add to Cart -->
                            <button @click="addToCart" :disabled="!isInStock || isAddingToCart" class="add-to-cart-btn">
                                <i v-if="!isAddingToCart" class="fas fa-shopping-cart"></i>
                                <i v-else class="fas fa-spinner fa-spin"></i>
                                <template v-if="isAddingToCart">{{ $t('common.adding') }}</template>
                                <template v-else-if="isInStock">{{ $t('common.add_to_cart') }}</template>
                                <template v-else>{{ $t('product.out_of_stock') }}</template>
                            </button>

                            <!-- Product Details -->
                            <div class="product-details">
                                <h3 class="details-title">{{ $t('product.details') }}</h3>
                                <ul class="details-list">
                                    <li v-if="productData.category">
                                        <strong>{{ $t('product.category') }}:</strong>
                                        <span>{{ getCategoryName }}</span>
                                    </li>
                                    <li v-if="hasSizes">
                                        <strong>{{ locale === 'lv' ? 'Pieejamie izmēri:' : 'Available sizes:' }}</strong>
                                        <span>{{ SIZES.join(', ') }}</span>
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
                        <a v-for="related in relatedProducts" :key="related.id" :href="`/shop/product/${related.slug}`" class="related-card">
                            <div class="related-image">
                                <img :src="related.image || '/img/Products/placeholder.png'" :alt="getRelatedName(related)" @error="$event.target.src = '/img/Products/placeholder.png'">
                            </div>
                            <h3 class="related-name">{{ getRelatedName(related) }}</h3>
                            <p class="related-price">€{{ formatPrice(related.price) }}</p>
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <!-- Error -->
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
.loading-container { display: flex; justify-content: center; align-items: center; min-height: 60vh; padding: 4rem 2rem; }

/* Breadcrumb */
.breadcrumb-section { background-color: #f9fafb; padding: 1.5rem 2rem; border-bottom: 1px solid #e5e7eb; }
.section-container { max-width: 1200px; margin: 0 auto; }
.breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
.breadcrumb-link { color: #dc2626; text-decoration: none; font-weight: 500; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-separator { color: #9ca3af; }
.breadcrumb-current { color: #6b7280; }

/* Product */
.product-section { padding: 4rem 2rem; }
.product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; }
@media (max-width: 1024px) { .product-grid { grid-template-columns: 1fr; gap: 2rem; } }

.product-images { position: sticky; top: 2rem; height: fit-content; }
.main-image { position: relative; width: 100%; aspect-ratio: 1; background-color: #f9fafb; border-radius: 1rem; overflow: hidden; }
.main-image img { width: 100%; height: 100%; object-fit: cover; }
.discount-badge { position: absolute; top: 1rem; left: 1rem; background-color: #dc2626; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 1rem; font-weight: 700; }
.out-of-stock-badge { position: absolute; top: 1rem; right: 1rem; background-color: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; }
.low-stock-badge { position: absolute; top: 1rem; right: 1rem; background-color: #f59e0b; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; }

.product-info-section { display: flex; flex-direction: column; gap: 1.5rem; }
.product-title { font-size: 2.5rem; font-weight: 800; color: #111827; line-height: 1.2; }
.product-sku { font-size: 0.875rem; color: #6b7280; }
.product-pricing { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.price-original { font-size: 1.5rem; color: #9ca3af; text-decoration: line-through; }
.price-current { font-size: 2.5rem; font-weight: 800; color: #dc2626; }
.save-badge { background-color: #fee2e2; color: #dc2626; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 700; }
.price-vat-note { font-size: 0.8rem; color: #9ca3af; margin-top: 0.25rem; }
.stock-status { display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background-color: #f0fdf4; border-radius: 0.5rem; }
.stock-status.out-of-stock { background-color: #fef2f2; }
.stock-status i { font-size: 1.25rem; color: #16a34a; }
.stock-status.out-of-stock i { color: #dc2626; }
.stock-status span { font-weight: 600; color: #166534; }
.stock-status.out-of-stock span { color: #991b1b; }
.product-description { font-size: 1.125rem; line-height: 1.75; color: #4b5563; }

/* ══ IZMĒRU IZVĒLNE ════════════════════════════════════════════ */
.size-selector {
    padding: 1.25rem;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 0.875rem;
    transition: border-color 0.2s;
}
.size-selector.size-error {
    border-color: #ef4444;
    background: #fff5f5;
    animation: shake 0.3s;
}
@keyframes shake {
    0%,100% { transform: translateX(0); }
    25%      { transform: translateX(-6px); }
    75%      { transform: translateX(6px); }
}

.size-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.875rem;
}
.size-label {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
}
.size-required { color: #dc2626; margin-left: 2px; }
.size-selected-indicator {
    font-size: 0.875rem;
    color: #059669;
    font-weight: 500;
}
.size-selected-indicator strong {
    font-size: 1rem;
    color: #065f46;
    margin-left: 4px;
}

.size-buttons {
    display: flex;
    gap: 0.625rem;
    flex-wrap: wrap;
}
.size-btn {
    min-width: 3rem;
    padding: 0.5rem 0.875rem;
    border: 2px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.3px;
}
.size-btn:hover {
    border-color: #dc2626;
    color: #dc2626;
    background: #fff5f5;
}
.size-btn-active {
    border-color: #dc2626 !important;
    background: #dc2626 !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
    transform: translateY(-1px);
}
.size-error-msg {
    margin: 0.625rem 0 0;
    font-size: 0.8125rem;
    color: #dc2626;
    display: flex;
    align-items: center;
    gap: 5px;
}
/* ════════════════════════════════════════════════════════════════ */

/* Quantity */
.quantity-section { display: flex; align-items: center; gap: 1rem; }
.quantity-label { font-weight: 600; color: #111827; }
.quantity-controls { display: flex; align-items: center; border: 2px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; }
.qty-btn { width: 3rem; height: 3rem; border: none; background: white; cursor: pointer; transition: all 0.3s; font-size: 1rem; }
.qty-btn:hover:not(:disabled) { background-color: #f9fafb; }
.qty-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.qty-input { width: 4rem; height: 3rem; border: none; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; text-align: center; font-size: 1.125rem; font-weight: 600; }
.qty-input:focus { outline: none; }

/* Add to cart */
.add-to-cart-btn {
    width: 100%; padding: 1.25rem; background-color: #dc2626; color: white;
    border: none; border-radius: 0.75rem; font-size: 1.125rem; font-weight: 700;
    cursor: pointer; transition: all 0.3s; display: flex; align-items: center;
    justify-content: center; gap: 0.75rem;
}
.add-to-cart-btn:hover:not(:disabled) { background-color: #b91c1c; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(220,38,38,0.3); }
.add-to-cart-btn:disabled { background-color: #9ca3af; cursor: not-allowed; transform: none; }

/* Details */
.product-details { padding: 2rem; background-color: #f9fafb; border-radius: 0.75rem; }
.details-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 1rem; }
.details-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.details-list li { display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb; }
.details-list li:last-child { border-bottom: none; padding-bottom: 0; }
.details-list strong { color: #6b7280; }
.details-list span { color: #111827; font-weight: 500; }

/* Related */
.related-section { padding: 4rem 2rem; background-color: #f9fafb; }
.section-title { font-size: 2rem; font-weight: 800; color: #111827; margin-bottom: 2rem; }
.related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }
@media (max-width: 1024px) { .related-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px)  { .related-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .related-grid { grid-template-columns: 1fr; } }

.related-card { background: white; border-radius: 0.75rem; overflow: hidden; text-decoration: none; transition: all 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.related-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
.related-image { aspect-ratio: 1; background-color: #f9fafb; overflow: hidden; }
.related-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.related-card:hover .related-image img { transform: scale(1.05); }
.related-name { padding: 1rem 1rem 0.5rem; font-size: 1rem; font-weight: 600; color: #111827; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.related-price { padding: 0 1rem 1rem; font-size: 1.125rem; font-weight: 700; color: #dc2626; }

/* Error */
.error-state { text-align: center; padding: 6rem 2rem; }
.error-state i { font-size: 5rem; color: #d1d5db; margin-bottom: 1.5rem; }
.error-state h2 { font-size: 2rem; font-weight: 700; color: #111827; margin-bottom: 1rem; }
.error-state p { font-size: 1.125rem; color: #6b7280; margin-bottom: 2rem; }
.back-btn { display: inline-block; padding: 1rem 2rem; background-color: #dc2626; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 600; transition: all 0.3s; }
.back-btn:hover { background-color: #b91c1c; transform: translateY(-2px); }

@media (max-width: 640px) {
    .product-title { font-size: 1.875rem; }
    .price-current { font-size: 2rem; }
}
</style>
