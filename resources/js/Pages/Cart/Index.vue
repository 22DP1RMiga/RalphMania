<script setup>
import { ref, computed } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    cart:            Object,
    items:           Array,
    vat_rate:        { type: Number, default: 21 },
    vat_amount:      { type: Number, default: 0  },
    subtotal_ex_vat: { type: Number, default: 0  },
    shipping_zones:  { type: Array,  default: () => [] },
});

// Modal state
const showRemoveModal = ref(false);
const showClearModal  = ref(false);
const itemToRemove    = ref(null);

// ── KUPONA STĀVOKLIS ─────────────────────────────────────────────
const couponCode         = ref('');
const couponError        = ref('');
const couponSuccess      = ref('');
const couponDiscount     = ref(0);      // € atlaide
const couponDescription  = ref('');
const couponApplied      = ref(null);   // { code, type, value, discount }
const isApplyingCoupon   = ref(false);

// ── CENU APRĒĶINI ────────────────────────────────────────────────
// SVARĪGI: Datubāzē cenas ir BRUTO (ar PVN iekļautu).
// PVN NAV pieskaitāms klāt — tas jau IR subtotal iekšā.
// Gala cena grozā = subtotal - discount  (piegāde aprēķinās checkout)
const subtotal = computed(() => parseFloat(props.cart?.total_amount || 0));

// PVN "tai skaitā" — izvelk no bruto: vat = subtotal * 21 / 121
const vatRate = computed(() => props.vat_rate || 21);
const vat = computed(() => {
    if (props.vat_amount > 0) return props.vat_amount;
    return Math.round(subtotal.value * vatRate.value / (100 + vatRate.value) * 100) / 100;
});

const discount = computed(() => couponDiscount.value);

// Kopā grozā (bez piegādes — aprēķinās checkout pēc valsts izvēles)
const total = computed(() => Math.max(0, subtotal.value - discount.value));

// Helper functions
const formatPrice = (price) => parseFloat(price || 0).toFixed(2);

const getProductName = (product) => {
    return locale.value === 'lv' ? product.name_lv : product.name_en;
};

const getProductImage = (product) => {
    return product.image || '/img/Products/placeholder.png';
};

// ── KUPONA LOĢIKA ────────────────────────────────────────────────
const applyCoupon = async () => {
    const code = couponCode.value.trim().toUpperCase();
    if (!code) {
        couponError.value = locale.value === 'lv' ? 'Ievadi kupona kodu.' : 'Enter a coupon code.';
        return;
    }

    couponError.value   = '';
    couponSuccess.value = '';
    isApplyingCoupon.value = true;

    try {
        const res = await axios.post('/coupons/validate', {
            code:         code,
            order_amount: subtotal.value,
        });

        couponApplied.value    = res.data;
        couponDiscount.value   = res.data.discount;
        couponDescription.value = res.data.description || '';
        couponSuccess.value    = res.data.message;
        couponCode.value       = res.data.code; // normalize
    } catch (err) {
        couponError.value      = err.response?.data?.message
            || (locale.value === 'lv' ? 'Kupons nav derīgs.' : 'Invalid coupon.');
        couponApplied.value    = null;
        couponDiscount.value   = 0;
    } finally {
        isApplyingCoupon.value = false;
    }
};

const removeCoupon = () => {
    couponApplied.value   = null;
    couponDiscount.value  = 0;
    couponCode.value      = '';
    couponError.value     = '';
    couponSuccess.value   = '';
    couponDescription.value = '';
};

// Nodot kupona kodu uz Checkout
const checkout = () => {
    const params = couponApplied.value
        ? { coupon_code: couponApplied.value.code }
        : {};
    router.visit('/checkout', { data: params });
};

// ── GROZA DARBĪBAS ───────────────────────────────────────────────
const updateQuantity = async (itemId, quantity) => {
    const qty = parseInt(quantity);
    if (qty < 1) return;
    try {
        const response = await axios.patch(`/cart/item/${itemId}`, { quantity: qty });
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: response.data.cart.total_items }
        }));
        router.reload({ only: ['cart', 'items'] });
    } catch (error) {
        alert(locale.value === 'lv' ? 'Kļūda atjauninot daudzumu!' : 'Error updating quantity!');
    }
};

const increaseQuantity = (item) => {
    if (item.quantity < item.product.stock_quantity) updateQuantity(item.id, item.quantity + 1);
};

const decreaseQuantity = (item) => {
    if (item.quantity > 1) updateQuantity(item.id, item.quantity - 1);
};

const showRemoveConfirm = (itemId) => {
    itemToRemove.value   = itemId;
    showRemoveModal.value = true;
};

const confirmRemoveItem = async () => {
    if (!itemToRemove.value) return;
    try {
        const response = await axios.delete(`/cart/item/${itemToRemove.value}`);
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: response.data.cart.total_items }
        }));
        router.reload({ only: ['cart', 'items'] });
    } catch (error) {
        alert(locale.value === 'lv' ? 'Kļūda izņemot produktu!' : 'Error removing item!');
    } finally {
        itemToRemove.value = null;
    }
};

const cancelRemove = () => {
    showRemoveModal.value = false;
    itemToRemove.value   = null;
};

const showClearConfirm = () => { showClearModal.value = true; };

const confirmClearCart = async () => {
    try {
        await axios.delete('/cart/clear');
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: 0 } }));
        router.reload({ only: ['cart', 'items'] });
    } catch (error) {
        alert(locale.value === 'lv' ? 'Kļūda iztukšojot grozu!' : 'Error clearing cart!');
    }
};

const cancelClear = () => { showClearModal.value = false; };
</script>

<template>
    <ShopLayout>
        <Head :title="$t('nav.cart')" />

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

        <ConfirmModal
            :show="showClearModal"
            :title="locale === 'lv' ? 'Iztukšot grozu?' : 'Clear cart?'"
            :message="locale === 'lv' ? 'Vai tiešām vēlaties iztukšot visu grozu?' : 'Are you sure you want to clear the entire cart?'"
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
                    {{ $t('nav.cart') }}
                </h1>
                <Link href="/shop" class="continue-shopping-btn">
                    <i class="fas fa-arrow-left"></i>
                    {{ $t('cart.continue_shopping') }}
                </Link>
            </div>

            <div v-if="items && items.length > 0" class="cart-content">

                <!-- Cart Items -->
                <div class="cart-items-section">
                    <div v-for="item in items" :key="item.id" class="cart-item">
                        <!-- Image -->
                        <div class="cart-item-image">
                            <img :src="getProductImage(item.product)" :alt="getProductName(item.product)">
                        </div>

                        <!-- Info -->
                        <div class="cart-item-details">
                            <h3 class="product-name">{{ getProductName(item.product) }}</h3>
                            <p class="product-price">{{ formatPrice(item.price) }}€</p>
                            <!-- Izmērs (ja ir) -->
                            <span v-if="item.size" class="item-size-badge">
                                {{ locale === 'lv' ? 'Izmērs:' : 'Size:' }} <strong>{{ item.size }}</strong>
                            </span>
                            <Link :href="`/shop/product/${item.product.slug}`" class="view-product-link">
                                {{ $t('cart.view_product') }}
                            </Link>
                        </div>

                        <!-- Quantity -->
                        <div class="cart-item-quantity">
                            <label>{{ $t('cart.quantity') }}</label>
                            <div class="quantity-controls">
                                <button @click="decreaseQuantity(item)" :disabled="item.quantity <= 1" class="qty-btn">
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
                                <button @click="increaseQuantity(item)" :disabled="item.quantity >= item.product.stock_quantity" class="qty-btn">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <p class="stock-info">{{ $t('cart.in_stock') }}: {{ item.product.stock_quantity }}</p>
                        </div>

                        <!-- Total -->
                        <div class="cart-item-total">
                            <p class="total-label">{{ $t('cart.total') }}</p>
                            <p class="total-amount">{{ formatPrice(item.quantity * item.price) }}€</p>
                        </div>

                        <!-- Remove -->
                        <button @click="showRemoveConfirm(item.id)" class="remove-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Summary -->
                <div class="cart-summary">
                    <h2 class="summary-title">{{ $t('cart.summary') }}</h2>

                    <!-- Price rows -->
                    <div class="summary-row">
                        <span>{{ locale === 'lv' ? 'Starpsumma' : 'Subtotal' }}</span>
                        <span>{{ formatPrice(subtotal) }}€</span>
                    </div>
                    <div class="summary-row summary-row-vat">
                        <span class="vat-label">
                            <i class="fas fa-info-circle"></i>
                            {{ locale === 'lv' ? `t.sk. PVN (${vatRate}%)` : `incl. VAT (${vatRate}%)` }}
                        </span>
                        <span class="vat-value">{{ formatPrice(vat) }}€</span>
                    </div>
                    <div class="summary-row">
                        <span>{{ $t('cart.shipping') }}</span>
                        <span class="text-gray">{{ $t('cart.calculated_at_checkout') }}</span>
                    </div>

                    <!-- Applied coupon discount row -->
                    <div v-if="discount > 0" class="summary-row discount-row">
                        <span>
                            <i class="fas fa-tag"></i>
                            {{ locale === 'lv' ? 'Atlaide' : 'Discount' }}
                            <code class="coupon-code-tag">{{ couponApplied?.code }}</code>
                        </span>
                        <span class="discount-value">-{{ formatPrice(discount) }}€</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row summary-total">
                        <span>{{ $t('cart.total') }}</span>
                        <span>{{ formatPrice(total) }}€</span>
                    </div>

                    <!-- ══ KUPONA IEVADES LAUKS ══════════════════════════════ -->
                    <div class="coupon-section">
                        <p class="coupon-label">
                            <i class="fas fa-ticket-alt"></i>
                            {{ locale === 'lv' ? 'Atlaižu kupons' : 'Discount Coupon' }}
                        </p>

                        <!-- Aktīvs kupons -->
                        <div v-if="couponApplied" class="coupon-applied">
                            <div class="coupon-applied-info">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <span class="coupon-applied-code">{{ couponApplied.code }}</span>
                                    <span class="coupon-applied-value">
                                        {{ couponApplied.type === 'percentage'
                                        ? `-${couponApplied.value}%`
                                        : `-€${formatPrice(couponApplied.value)}`
                                        }}
                                    </span>
                                    <p v-if="couponDescription" class="coupon-desc">{{ couponDescription }}</p>
                                </div>
                            </div>
                            <button @click="removeCoupon" class="coupon-remove-btn" :title="locale === 'lv' ? 'Noņemt kuponu' : 'Remove coupon'">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Ievades forma -->
                        <div v-else class="coupon-input-row">
                            <input
                                v-model="couponCode"
                                type="text"
                                :placeholder="locale === 'lv' ? 'Ievadi kodu...' : 'Enter code...'"
                                class="coupon-input"
                                :class="{ 'coupon-input-error': couponError }"
                                @keyup.enter="applyCoupon"
                                maxlength="64"
                                autocomplete="off"
                                spellcheck="false"
                            >
                            <button
                                @click="applyCoupon"
                                class="coupon-apply-btn"
                                :disabled="isApplyingCoupon || !couponCode.trim()"
                            >
                                <i v-if="isApplyingCoupon" class="fas fa-spinner fa-spin"></i>
                                <span v-else>{{ locale === 'lv' ? 'Pielietot' : 'Apply' }}</span>
                            </button>
                        </div>

                        <!-- Kļūda / Veiksme -->
                        <p v-if="couponError" class="coupon-msg coupon-msg-error">
                            <i class="fas fa-exclamation-circle"></i> {{ couponError }}
                        </p>
                        <p v-if="couponSuccess && !couponApplied" class="coupon-msg coupon-msg-success">
                            <i class="fas fa-check-circle"></i> {{ couponSuccess }}
                        </p>
                    </div>
                    <!-- ═════════════════════════════════════════════════════ -->

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

            <!-- Empty cart -->
            <div v-else class="empty-cart">
                <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
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
.cart-page { max-width: 1400px; margin: 0 auto; padding: 40px 20px; }

.cart-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 40px;
}
.cart-title { font-size: 32px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 12px; }
.cart-title i { color: #dc2626; }
.continue-shopping-btn {
    display: inline-flex; align-items: center; gap: 8px;
    color: #6b7280; text-decoration: none; font-size: 14px;
    padding: 10px 16px; border: 1px solid #e5e7eb;
    border-radius: 8px; transition: all 0.2s;
}
.continue-shopping-btn:hover { color: #dc2626; border-color: #dc2626; }

.cart-content {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 32px;
    align-items: start;
}

/* Cart items */
.cart-items-section { display: flex; flex-direction: column; gap: 16px; }

.cart-item {
    background: white; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 20px; display: grid;
    grid-template-columns: 120px 1fr auto auto auto;
    gap: 20px; align-items: center;
    transition: box-shadow 0.2s;
}
.cart-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }

.cart-item-image { width: 120px; height: 120px; border-radius: 8px; overflow: hidden; background: #f3f4f6; }
.cart-item-image img { width: 100%; height: 100%; object-fit: cover; }

.cart-item-details { display: flex; flex-direction: column; gap: 6px; }
.product-name { font-size: 17px; font-weight: 600; color: #1f2937; margin: 0; }
.product-price { font-size: 15px; color: #dc2626; font-weight: 600; margin: 0; }
.item-size-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; background: #f3f4f6; border: 1px solid #e5e7eb;
    border-radius: 20px; font-size: 12px; color: #374151; width: fit-content;
}
.item-size-badge strong { color: #111827; }
.view-product-link { color: #3b82f6; text-decoration: none; font-size: 13px; }
.view-product-link:hover { text-decoration: underline; }

/* Quantity */
.cart-item-quantity { display: flex; flex-direction: column; gap: 8px; }
.cart-item-quantity label { font-size: 12px; color: #6b7280; font-weight: 500; }
.quantity-controls { display: flex; align-items: center; gap: 4px; }
.qty-btn {
    width: 32px; height: 32px; border: 1px solid #e5e7eb; background: white;
    border-radius: 6px; cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; color: #6b7280;
}
.qty-btn:hover:not(:disabled) { background: #f3f4f6; border-color: #dc2626; color: #dc2626; }
.qty-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.qty-input { width: 56px; height: 32px; text-align: center; border: 1px solid #e5e7eb; border-radius: 6px; font-weight: 600; }
.stock-info { font-size: 12px; color: #10b981; margin: 0; }

/* Total */
.cart-item-total { text-align: right; }
.total-label { font-size: 12px; color: #6b7280; margin: 0 0 4px 0; }
.total-amount { font-size: 20px; font-weight: 700; color: #dc2626; margin: 0; }

/* Remove */
.remove-btn {
    background: none; border: none; color: #ef4444; font-size: 18px;
    cursor: pointer; padding: 8px; transition: all 0.2s; border-radius: 6px;
}
.remove-btn:hover { background: #fee2e2; }

/* ── SUMMARY ─────────────────────────────────────────────────── */
.cart-summary {
    background: white; border: 1px solid #e5e7eb;
    border-radius: 14px; padding: 28px;
    position: sticky; top: 100px;
}
.summary-title { font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 20px 0; }
.summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; font-size: 14px; color: #6b7280;
}
.summary-row-vat { padding: 2px 0 10px; }
.vat-label { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #9ca3af; font-style: italic; }
.vat-label .fa-info-circle { font-size: 11px; color: #d1d5db; }
.vat-value { font-size: 12px; color: #9ca3af; }
.text-gray { color: #9ca3af; }
.discount-row { color: #059669; font-weight: 600; }
.discount-row i { margin-right: 4px; }
.discount-value { color: #059669; font-weight: 700; }
.coupon-code-tag {
    background: #d1fae5; color: #065f46; padding: 2px 6px;
    border-radius: 4px; font-size: 11px; margin-left: 6px;
    font-family: monospace; letter-spacing: 0.5px;
}
.summary-divider { height: 1px; background: #e5e7eb; margin: 14px 0; }
.summary-total { font-size: 20px; font-weight: 700; color: #1f2937; padding: 14px 0; }

/* ── KUPONA SEKCIJA ─────────────────────────────────────────── */
.coupon-section {
    margin: 16px 0;
    padding: 16px;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    border-radius: 10px;
}
.coupon-label {
    font-size: 13px; font-weight: 600; color: #374151;
    margin: 0 0 10px; display: flex; align-items: center; gap: 6px;
}
.coupon-label i { color: #dc2626; }

/* Input row */
.coupon-input-row { display: flex; gap: 8px; }
.coupon-input {
    flex: 1; padding: 10px 12px; border: 1.5px solid #d1d5db;
    border-radius: 8px; font-size: 14px; font-family: monospace;
    letter-spacing: 1px; text-transform: uppercase;
    transition: border-color 0.2s;
}
.coupon-input:focus { outline: none; border-color: #dc2626; }
.coupon-input-error { border-color: #ef4444; background: #fff5f5; }
.coupon-input::placeholder { text-transform: none; letter-spacing: normal; font-family: inherit; color: #9ca3af; }

.coupon-apply-btn {
    padding: 10px 18px; background: #dc2626; color: white; border: none;
    border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
    display: flex; align-items: center; gap: 6px;
}
.coupon-apply-btn:hover:not(:disabled) { background: #b91c1c; }
.coupon-apply-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Applied coupon */
.coupon-applied {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 12px; background: #ecfdf5; border: 1.5px solid #10b981;
    border-radius: 8px; gap: 8px;
}
.coupon-applied-info { display: flex; align-items: flex-start; gap: 10px; flex: 1; }
.coupon-applied-info > i { color: #10b981; font-size: 18px; margin-top: 2px; flex-shrink: 0; }
.coupon-applied-code {
    font-family: monospace; font-weight: 700; font-size: 15px;
    color: #065f46; letter-spacing: 1px;
}
.coupon-applied-value {
    margin-left: 8px; background: #10b981; color: white;
    padding: 2px 8px; border-radius: 20px; font-size: 12px; font-weight: 700;
}
.coupon-desc { font-size: 12px; color: #047857; margin: 4px 0 0; }

.coupon-remove-btn {
    background: none; border: none; color: #6b7280; cursor: pointer;
    padding: 4px; border-radius: 4px; transition: all 0.2s; flex-shrink: 0;
}
.coupon-remove-btn:hover { color: #dc2626; background: #fee2e2; }

/* Messages */
.coupon-msg {
    font-size: 12px; margin: 8px 0 0;
    display: flex; align-items: center; gap: 5px;
}
.coupon-msg i { font-size: 13px; }
.coupon-msg-error { color: #dc2626; }
.coupon-msg-success { color: #059669; }

/* Buttons */
.checkout-btn {
    width: 100%; padding: 15px; background: #dc2626; color: white;
    border: none; border-radius: 8px; font-size: 16px; font-weight: 600;
    cursor: pointer; transition: all 0.3s; display: flex; align-items: center;
    justify-content: center; gap: 8px; margin-top: 20px;
}
.checkout-btn:hover { background: #b91c1c; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }

.clear-cart-btn {
    width: 100%; padding: 11px; background: #f3f4f6; color: #6b7280;
    border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;
    font-weight: 500; cursor: pointer; transition: all 0.3s;
    display: flex; align-items: center; justify-content: center;
    gap: 8px; margin-top: 10px;
}
.clear-cart-btn:hover { background: #fee2e2; color: #ef4444; border-color: #fecaca; }

/* Empty */
.empty-cart { text-align: center; padding: 80px 20px; }
.empty-cart-icon { font-size: 80px; color: #d1d5db; margin-bottom: 20px; }
.empty-cart h2 { font-size: 28px; color: #1f2937; margin-bottom: 12px; }
.empty-cart p { font-size: 16px; color: #6b7280; margin-bottom: 32px; }
.shop-now-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 16px 32px; background: #dc2626; color: white;
    text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s;
}
.shop-now-btn:hover { background: #b91c1c; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }

/* Responsive */
@media (max-width: 1024px) {
    .cart-content { grid-template-columns: 1fr; }
    .cart-summary { position: static; }
}
@media (max-width: 768px) {
    .cart-header { flex-direction: column; gap: 16px; align-items: flex-start; }
    .cart-title { font-size: 24px; }
    .cart-item { grid-template-columns: 1fr; gap: 16px; }
    .cart-item-image { width: 100%; height: 200px; }
    .cart-item-total { text-align: left; }
}
</style>
