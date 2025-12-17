<script setup>
import { ref, computed } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    cart: Object,
    items: Array,
});

// Modal state
const showRemoveModal = ref(false);
const showClearModal = ref(false);
const itemToRemove = ref(null);

// Helper functions
const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const getProductName = (product) => {
    return locale.value === 'lv' ? product.name_lv : product.name_en;
};

const getProductImage = (product) => {
    return product.image || '/img/Products/placeholder.png';
};

// Groza darbības
const updateQuantity = async (itemId, quantity) => {
    const qty = parseInt(quantity);
    if (qty < 1) return;

    try {
        const response = await axios.patch(`/cart/item/${itemId}`, { quantity: qty });

        // Update cart count
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: response.data.cart.total_items }
        }));

        router.reload({ only: ['cart', 'items'] });
    } catch (error) {
        console.error('Error updating quantity:', error);
        alert(locale.value === 'lv' ? 'Kļūda atjauninot daudzumu!' : 'Error updating quantity!');
    }
};

const increaseQuantity = (item) => {
    if (item.quantity < item.product.stock_quantity) {
        updateQuantity(item.id, item.quantity + 1);
    }
};

const decreaseQuantity = (item) => {
    if (item.quantity > 1) {
        updateQuantity(item.id, item.quantity - 1);
    }
};

// Remove item with confirmation
const showRemoveConfirm = (itemId) => {
    itemToRemove.value = itemId;
    showRemoveModal.value = true;
};

const confirmRemoveItem = async () => {
    if (!itemToRemove.value) return;

    try {
        const response = await axios.delete(`/cart/item/${itemToRemove.value}`);

        // Update cart count
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: response.data.cart.total_items }
        }));

        router.reload({ only: ['cart', 'items'] });
    } catch (error) {
        console.error('Error removing item:', error);
        alert(locale.value === 'lv' ? 'Kļūda izņemot produktu!' : 'Error removing item!');
    } finally {
        itemToRemove.value = null;
    }
};

const cancelRemove = () => {
    showRemoveModal.value = false;
    itemToRemove.value = null;
};

// Clear cart with confirmation
const showClearConfirm = () => {
    showClearModal.value = true;
};

const confirmClearCart = async () => {
    try {
        await axios.delete('/cart/clear');

        // Update cart count
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: 0 }
        }));

        router.reload({ only: ['cart', 'items'] });
    } catch (error) {
        console.error('Error clearing cart:', error);
        alert(locale.value === 'lv' ? 'Kļūda iztukšojot grozu!' : 'Error clearing cart!');
    }
};

const cancelClear = () => {
    showClearModal.value = false;
};

const checkout = () => {
    router.visit('/checkout');
};
</script>

<template>
    <ShopLayout>
        <Head :title="$t('nav.cart')" />

        <!-- Confirm Modal for Remove Item -->
        <ConfirmModal
            :show="showRemoveModal"
            :title="locale === 'lv' ? 'Izņemt produktu?' : 'Remove product?'"
            :message="locale === 'lv' ? 'Vai tiešām vēlaties izņemt šo produktu no groza?' : 'Are you sure you want to remove this product from cart?'"
            :confirmText="locale === 'lv' ? 'Jā, izņemt' : 'Yes, remove'"
            :cancelText="locale === 'lv' ? 'Atcelt' : 'Cancel'"
            type="danger"
            @confirm="confirmRemoveItem"
            @cancel="cancelRemove"
        />

        <!-- Confirm Modal for Clear Cart -->
        <ConfirmModal
            :show="showClearModal"
            :title="locale === 'lv' ? 'Iztukšot grozu?' : 'Clear cart?'"
            :message="locale === 'lv' ? 'Vai tiešām vēlaties iztukšot visu grozu? Šī darbība ir neatgriezeniska.' : 'Are you sure you want to clear the entire cart? This action cannot be undone.'"
            :confirmText="locale === 'lv' ? 'Jā, iztukšot' : 'Yes, clear'"
            :cancelText="locale === 'lv' ? 'Atcelt' : 'Cancel'"
            type="danger"
            @confirm="confirmClearCart"
            @cancel="cancelClear"
        />

        <div class="cart-page">
            <!-- Header -->
            <div class="cart-header">
                <h1 class="cart-title">
                    <i class="fas fa-shopping-cart"></i>
                    {{ $t('cart.title') }}
                </h1>
                <Link href="/shop" class="continue-shopping-btn">
                    <i class="fas fa-arrow-left"></i>
                    {{ $t('cart.continue_shopping') }}
                </Link>
            </div>

            <!-- Cart Items -->
            <div v-if="items && items.length > 0" class="cart-content">
                <div class="cart-items-section">
                    <div v-for="item in items" :key="item.id" class="cart-item">
                        <!-- Product Image -->
                        <div class="cart-item-image">
                            <img
                                :src="getProductImage(item.product)"
                                :alt="getProductName(item.product)"
                            >
                        </div>

                        <!-- Product Info -->
                        <div class="cart-item-details">
                            <h3 class="product-name">{{ getProductName(item.product) }}</h3>
                            <p class="product-price">{{ formatPrice(item.price) }}€</p>
                            <Link
                                :href="`/shop/product/${item.product.slug}`"
                                class="view-product-link"
                            >
                                {{ $t('cart.view_product') }}
                            </Link>
                        </div>

                        <!-- Quantity Controls -->
                        <div class="cart-item-quantity">
                            <label>{{ $t('cart.quantity') }}</label>
                            <div class="quantity-controls">
                                <button
                                    @click="decreaseQuantity(item)"
                                    :disabled="item.quantity <= 1"
                                    class="qty-btn"
                                >
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input
                                    type="number"
                                    :value="item.quantity"
                                    @change="updateQuantity(item.id, $event.target.value)"
                                    min="1"
                                    :max="item.product.stock_quantity"
                                    class="qty-input"
                                >
                                <button
                                    @click="increaseQuantity(item)"
                                    :disabled="item.quantity >= item.product.stock_quantity"
                                    class="qty-btn"
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <p class="stock-info">
                                {{ $t('cart.in_stock') }}: {{ item.product.stock_quantity }}
                            </p>
                        </div>

                        <!-- Item Total -->
                        <div class="cart-item-total">
                            <p class="total-label">{{ $t('cart.total') }}</p>
                            <p class="total-amount">{{ formatPrice(item.quantity * item.price) }}€</p>
                        </div>

                        <!-- Remove Button -->
                        <button @click="showRemoveConfirm(item.id)" class="remove-btn" :title="locale === 'lv' ? 'Izņemt no groza' : 'Remove from cart'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2 class="summary-title">{{ $t('cart.summary') }}</h2>

                    <div class="summary-row">
                        <span>{{ $t('cart.subtotal') }}</span>
                        <span>{{ formatPrice(cart?.total_amount || 0) }}€</span>
                    </div>

                    <div class="summary-row">
                        <span>{{ locale === 'lv' ? 'PVN (21%)' : 'VAT (21%)' }}</span>
                        <span>{{ formatPrice((cart?.total_amount || 0) * 0.21) }}€</span>
                    </div>

                    <div class="summary-row">
                        <span>{{ $t('cart.shipping') }}</span>
                        <span>{{ $t('cart.calculated_at_checkout') }}</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row summary-total">
                        <span>{{ $t('cart.total') }}</span>
                        <span>{{ formatPrice((cart?.total_amount || 0) * 1.21) }}€</span>
                    </div>

                    <button @click="checkout" class="checkout-btn">
                        <i class="fas fa-lock"></i>
                        {{ $t('cart.proceed_to_checkout') }}
                    </button>

                    <button @click="showClearConfirm" class="clear-cart-btn">
                        <i class="fas fa-trash-alt"></i>
                        {{ $t('cart.clear_cart') }}
                    </button>
                </div>
            </div>

            <!-- Empty Cart -->
            <div v-else class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>{{ $t('cart.your_cart_is_empty') }}</h2>
                <p>{{ $t('cart.add_products_to_start_shopping') }}</p>
                <Link href="/shop" class="shop-now-btn">
                    <i class="fas fa-shopping-bag"></i>
                    {{ $t('cart.shop_now') }}
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.cart-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.cart-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 12px;
}

.cart-title i {
    color: #dc2626;
}

.continue-shopping-btn {
    padding: 12px 24px;
    background: #f3f4f6;
    color: #1f2937;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.continue-shopping-btn:hover {
    background: #e5e7eb;
}

/* Cart Content Layout */
.cart-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
}

.cart-items-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Cart Item */
.cart-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    display: grid;
    grid-template-columns: 120px 1fr auto auto auto;
    gap: 20px;
    align-items: center;
}

.cart-item-image {
    width: 120px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-name {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.product-price {
    font-size: 16px;
    color: #dc2626;
    font-weight: 600;
    margin: 0;
}

.view-product-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
}

.view-product-link:hover {
    text-decoration: underline;
}

.cart-item-quantity {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.cart-item-quantity label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 4px;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}

.qty-btn:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #dc2626;
    color: #dc2626;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qty-input {
    width: 60px;
    height: 32px;
    text-align: center;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-weight: 600;
}

.stock-info {
    font-size: 12px;
    color: #10b981;
    margin: 0;
}

.cart-item-total {
    text-align: right;
}

.total-label {
    font-size: 12px;
    color: #6b7280;
    margin: 0 0 4px 0;
}

.total-amount {
    font-size: 20px;
    font-weight: 700;
    color: #dc2626;
    margin: 0;
}

.remove-btn {
    background: none;
    border: none;
    color: #ef4444;
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    transition: all 0.2s;
    border-radius: 6px;
}

.remove-btn:hover {
    background: #fee2e2;
}

/* Cart Summary */
.cart-summary {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 30px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.summary-title {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    font-size: 14px;
    color: #6b7280;
}

.summary-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 16px 0;
}

.summary-total {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    padding: 16px 0;
}

.checkout-btn {
    width: 100%;
    padding: 16px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
}

.checkout-btn:hover {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.clear-cart-btn {
    width: 100%;
    padding: 12px;
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 12px;
}

.clear-cart-btn:hover {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fecaca;
}

/* Empty Cart */
.empty-cart {
    text-align: center;
    padding: 80px 20px;
}

.empty-cart-icon {
    font-size: 80px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-cart h2 {
    font-size: 28px;
    color: #1f2937;
    margin-bottom: 12px;
}

.empty-cart p {
    font-size: 16px;
    color: #6b7280;
    margin-bottom: 32px;
}

.shop-now-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 32px;
    background: #dc2626;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.shop-now-btn:hover {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .cart-content {
        grid-template-columns: 1fr;
    }

    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-header {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }

    .cart-title {
        font-size: 24px;
    }

    .cart-item {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .cart-item-image {
        width: 100%;
        height: 200px;
    }

    .cart-item-total {
        text-align: left;
    }
}
</style>
