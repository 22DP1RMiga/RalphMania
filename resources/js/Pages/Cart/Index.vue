<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t, locale } = useI18n();

const props = defineProps({
    cart: Object,
});

const cartData = ref(props.cart);

const updateQuantity = async (itemId, newQuantity) => {
    try {
        const response = await axios.put(`/cart/${itemId}`, {
            quantity: newQuantity,
        });
        cartData.value = response.data.cart;
    } catch (error) {
        console.error('Error updating cart:', error);
    }
};

const removeItem = async (itemId) => {
    try {
        const response = await axios.delete(`/cart/${itemId}`);
        cartData.value = response.data.cart;
    } catch (error) {
        console.error('Error removing item:', error);
    }
};

const getProductName = (product) => {
    return locale.value === 'lv' ? product.name_lv : product.name_en;
};

const proceedToCheckout = () => {
    router.visit('/checkout');
};
</script>

<template>
    <Head :title="$t('cart.title')" />

    <MainLayout>
        <!-- Cart Hero -->
        <section class="cart-hero">
            <div class="hero-container">
                <h1 class="hero-title">{{ $t('cart.title') }}</h1>
                <p class="hero-subtitle">{{ cartData.item_count }} {{ $t('cart.items_in_cart') }}</p>
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

        <!-- Cart Content -->
        <section class="cart-section">
            <div class="section-container">
                <!-- Empty Cart -->
                <div v-if="cartData.items.length === 0" class="empty-cart">
                    <i class="fas fa-shopping-cart empty-icon"></i>
                    <h2 class="empty-title">{{ $t('cart.empty') }}</h2>
                    <p class="empty-text">{{ $t('cart.empty_text') }}</p>
                    <a href="/shop" class="continue-shopping-btn">
                        {{ $t('cart.continue_shopping') }}
                    </a>
                </div>

                <!-- Cart with Items -->
                <div v-else class="cart-grid">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div
                            v-for="item in cartData.items"
                            :key="item.id"
                            class="cart-item"
                        >
                            <div class="item-image">
                                <img :src="item.product.image" :alt="getProductName(item.product)">
                            </div>
                            <div class="item-details">
                                <h3 class="item-name">{{ getProductName(item.product) }}</h3>
                                <p class="item-price">€{{ item.price.toFixed(2) }}</p>
                            </div>
                            <div class="item-quantity">
                                <button
                                    @click="updateQuantity(item.id, item.quantity - 1)"
                                    :disabled="item.quantity <= 1"
                                    class="qty-btn"
                                >
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="qty-value">{{ item.quantity }}</span>
                                <button
                                    @click="updateQuantity(item.id, item.quantity + 1)"
                                    class="qty-btn"
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="item-total">
                                €{{ item.total.toFixed(2) }}
                            </div>
                            <button
                                @click="removeItem(item.id)"
                                class="item-remove"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h3 class="summary-title">{{ $t('cart.order_summary') }}</h3>

                        <div class="summary-row">
                            <span>{{ $t('cart.subtotal') }}</span>
                            <span>€{{ cartData.subtotal.toFixed(2) }}</span>
                        </div>

                        <div class="summary-row">
                            <span>{{ $t('cart.shipping') }}</span>
                            <span>€{{ cartData.shipping.toFixed(2) }}</span>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-row summary-total">
                            <span>{{ $t('cart.total') }}</span>
                            <span>€{{ cartData.total.toFixed(2) }}</span>
                        </div>

                        <button
                            @click="proceedToCheckout"
                            class="checkout-btn"
                        >
                            {{ $t('cart.checkout') }}
                        </button>

                        <a href="/shop" class="continue-shopping-link">
                            {{ $t('cart.continue_shopping') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
/* Hero Section */
.cart-hero {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.cart-hero::before {
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
    margin-bottom: 0.5rem;
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

/* Cart Section */
.cart-section {
    padding: 4rem 2rem;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Empty Cart */
.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 5rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.empty-text {
    font-size: 1.125rem;
    color: #6b7280;
    margin-bottom: 2rem;
}

.continue-shopping-btn {
    display: inline-block;
    padding: 1rem 2.5rem;
    background-color: #dc2626;
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.continue-shopping-btn:hover {
    background-color: #b91c1c;
    transform: translateY(-2px);
}

/* Cart Grid */
.cart-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
}

@media (max-width: 1024px) {
    .cart-grid {
        grid-template-columns: 1fr;
    }
}

/* Cart Items */
.cart-items {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto auto;
    gap: 1.5rem;
    align-items: center;
    padding: 1.5rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 1rem;
    }

    .item-quantity,
    .item-total {
        grid-column: 2;
    }

    .item-remove {
        grid-column: 2;
        justify-self: end;
    }
}

.item-image {
    width: 100px;
    height: 100px;
    border-radius: 0.5rem;
    overflow: hidden;
    background-color: #f9fafb;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.item-price {
    font-size: 0.875rem;
    color: #6b7280;
}

.item-quantity {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.qty-btn {
    width: 2rem;
    height: 2rem;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qty-btn:hover:not(:disabled) {
    border-color: #dc2626;
    color: #dc2626;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qty-value {
    font-weight: 600;
    min-width: 2rem;
    text-align: center;
}

.item-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: #dc2626;
}

.item-remove {
    width: 2.5rem;
    height: 2.5rem;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.item-remove:hover {
    background: #dc2626;
    color: white;
}

/* Cart Summary */
.cart-summary {
    background: white;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.summary-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    font-size: 1rem;
    color: #6b7280;
}

.summary-divider {
    height: 1px;
    background-color: #e5e7eb;
    margin: 1rem 0;
}

.summary-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.checkout-btn {
    width: 100%;
    padding: 1rem;
    margin-top: 1.5rem;
    background-color: #dc2626;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.checkout-btn:hover {
    background-color: #b91c1c;
    transform: translateY(-2px);
}

.continue-shopping-link {
    display: block;
    text-align: center;
    margin-top: 1rem;
    color: #dc2626;
    text-decoration: none;
    font-weight: 500;
}

.continue-shopping-link:hover {
    text-decoration: underline;
}

@media (max-width: 640px) {
    .hero-title {
        font-size: 2rem;
    }
}
</style>
