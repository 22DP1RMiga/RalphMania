<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router, Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    cart:            Object,
    user:            Object,
    vat_rate:        { type: Number, default: 21 },
    vat_amount:      { type: Number, default: 0  },
    subtotal_ex_vat: { type: Number, default: 0  },
    shipping_zones:  { type: Array,  default: () => [] },
});

// Form data
const form = ref({
    customer_name:        props.user?.name || '',
    customer_email:       props.user?.email || '',
    customer_phone:       props.user?.phone || '',
    delivery_country:     props.user?.country || 'Latvia',
    delivery_city:        props.user?.city || '',
    delivery_address:     props.user?.address || '',
    delivery_postal_code: props.user?.postal_code || '',
    payment_method:       'card',
    notes:                '',
    card_number:          '',
    card_name:            '',
    card_expiry:          '',
    card_cvv:             '',
    coupon_code:          '',
});

const errors      = ref({});
const isSubmitting = ref(false);

const toast = ref({ show: false, message: '', type: 'success' });

// ── KUPONA STĀVOKLIS ─────────────────────────────────────────────
const couponInputCode    = ref('');
const couponApplied      = ref(null);   // { code, type, value, discount, description }
const couponDiscount     = ref(0);
const couponError        = ref('');
const couponSuccess      = ref('');
const isApplyingCoupon   = ref(false);

// Nolasīt kuponu no URL (padots no Cart lapas)
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const passedCode = urlParams.get('coupon_code');
    if (passedCode) {
        couponInputCode.value = passedCode.toUpperCase();
        form.value.coupon_code = passedCode.toUpperCase();
        // Auto-apply
        applyCoupon(passedCode.toUpperCase());
    }
});

// ── CENU APRĒĶINI ────────────────────────────────────────────────
// SVARĪGI: Datubāzē cenas ir BRUTO (ar PVN iekļautu).
// PVN nav pieskaitāms klāt — tas jau ir iekļauts subtotal.
// Gala cena = subtotal + shipping - discount  (BEZ vat pieskaitīšanas!)

const subtotal = computed(() => parseFloat(props.cart?.total_amount || 0));

// PVN "tai skaitā" — aprēķina no bruto summas (iekļauts subtotal)
// vat = subtotal * 21 / 121
const vatRate = computed(() => props.vat_rate || 21);
const vat = computed(() => {
    // Ja backend nodeva, izmanto to; citādi aprēķina pašs
    if (props.vat_amount > 0) return props.vat_amount;
    return Math.round(subtotal.value * vatRate.value / (100 + vatRate.value) * 100) / 100;
});
const subtotalExVat = computed(() => {
    if (props.subtotal_ex_vat > 0) return props.subtotal_ex_vat;
    return Math.round((subtotal.value - vat.value) * 100) / 100;
});

const discount = computed(() => couponDiscount.value);

// Piegādes izmaksu aprēķins — sakrīt ar OrderController::calculateShipping()
// Latvija: bezmaksas no €35, Baltija/ES: bezmaksas no €50
const shippingCost = computed(() => {
    const country = form.value.delivery_country;
    const amt = subtotal.value;

    if (country === 'Latvia') {
        return amt >= 35 ? 0 : 3.49;
    } else if (country === 'Estonia' || country === 'Lithuania') {
        return amt >= 50 ? 0 : 5.49;
    } else {
        // Pārējā ES
        return amt >= 50 ? 0 : 10.99;
    }
});

// Cik € vēl vajag bezmaksas piegādei
const shippingFreeRemaining = computed(() => {
    const country = form.value.delivery_country;
    const threshold = country === 'Latvia' ? 35 : 50;
    const remaining = threshold - subtotal.value;
    return remaining > 0 ? Math.round(remaining * 100) / 100 : 0;
});

// Gala summa: subtotal (ar PVN iekļautu) + piegāde - atlaide
// PVN NAV pieskaitāms klāt — tas jau IR iekļauts
const totalWithShipping = computed(() => {
    return Math.max(0, subtotal.value + shippingCost.value - discount.value);
});

const hasContactErrors  = computed(() => !!(errors.value.customer_name || errors.value.customer_email || errors.value.customer_phone));
const hasDeliveryErrors = computed(() => !!(errors.value.delivery_country || errors.value.delivery_city || errors.value.delivery_address));

// ── KUPONA LOĢIKA ────────────────────────────────────────────────
const applyCoupon = async (codeOverride = null) => {
    const code = (codeOverride || couponInputCode.value).trim().toUpperCase();
    if (!code) {
        couponError.value = locale.value === 'lv' ? 'Ievadi kupona kodu.' : 'Enter a coupon code.';
        return;
    }

    couponError.value   = '';
    couponSuccess.value = '';
    isApplyingCoupon.value = true;

    try {
        const res = await axios.post('/coupons/validate', {
            code,
            order_amount: subtotal.value,
        });

        couponApplied.value      = res.data;
        couponDiscount.value     = res.data.discount;
        couponInputCode.value    = res.data.code;
        form.value.coupon_code   = res.data.code;
        couponSuccess.value      = res.data.message;
    } catch (err) {
        couponError.value  = err.response?.data?.message
            || (locale.value === 'lv' ? 'Kupons nav derīgs.' : 'Invalid coupon.');
        removeCoupon();
    } finally {
        isApplyingCoupon.value = false;
    }
};

const removeCoupon = () => {
    couponApplied.value     = null;
    couponDiscount.value    = 0;
    couponError.value       = '';
    couponSuccess.value     = '';
    form.value.coupon_code  = '';
};

// ── HELPERS ──────────────────────────────────────────────────────
const formatPrice = (price) => parseFloat(price || 0).toFixed(2);

const getProductName = (product) => locale.value === 'lv' ? product.name_lv : product.name_en;
const getProductImage = (product) => product.image || '/img/Products/placeholder.png';

const showToast = (message, type = 'success') => { toast.value = { show: true, message, type }; };

const clearError = (field) => { if (errors.value[field]) delete errors.value[field]; };

const validateField = (field) => {
    switch (field) {
        case 'customer_name':
            if (!form.value.customer_name.trim())
                errors.value.customer_name = locale.value === 'lv' ? 'Vārds ir obligāts' : 'Name is required';
            break;
        case 'customer_email': {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(form.value.customer_email))
                errors.value.customer_email = locale.value === 'lv' ? 'Nederīgs e-pasts' : 'Invalid email';
            break;
        }
        case 'customer_phone':
            if (!form.value.customer_phone.trim())
                errors.value.customer_phone = locale.value === 'lv' ? 'Tālrunis ir obligāts' : 'Phone is required';
            break;
        case 'delivery_city':
            if (!form.value.delivery_city.trim())
                errors.value.delivery_city = locale.value === 'lv' ? 'Pilsēta ir obligāta' : 'City is required';
            break;
        case 'delivery_address':
            if (!form.value.delivery_address.trim())
                errors.value.delivery_address = locale.value === 'lv' ? 'Adrese ir obligāta' : 'Address is required';
            break;
        case 'card_number':
            if (form.value.payment_method === 'card') {
                const n = form.value.card_number.replace(/\s/g, '');
                if (!n) errors.value.card_number = locale.value === 'lv' ? 'Kartes numurs ir obligāts' : 'Card number is required';
                else if (n.length < 13) errors.value.card_number = locale.value === 'lv' ? 'Nederīgs kartes numurs' : 'Invalid card number';
            }
            break;
        case 'card_name':
            if (form.value.payment_method === 'card' && !form.value.card_name.trim())
                errors.value.card_name = locale.value === 'lv' ? 'Vārds uz kartes ir obligāts' : 'Name on card is required';
            break;
        case 'card_expiry':
            if (form.value.payment_method === 'card') {
                if (!form.value.card_expiry)
                    errors.value.card_expiry = locale.value === 'lv' ? 'Derīguma termiņš ir obligāts' : 'Expiry date is required';
                else if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(form.value.card_expiry))
                    errors.value.card_expiry = locale.value === 'lv' ? 'Nederīgs formāts (MM/YY)' : 'Invalid format (MM/YY)';
            }
            break;
        case 'card_cvv':
            if (form.value.payment_method === 'card') {
                if (!form.value.card_cvv)
                    errors.value.card_cvv = locale.value === 'lv' ? 'CVV kods ir obligāts' : 'CVV is required';
                else if (form.value.card_cvv.length < 3)
                    errors.value.card_cvv = locale.value === 'lv' ? 'CVV jābūt 3-4 cipariem' : 'CVV must be 3-4 digits';
            }
            break;
    }
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;
    const missing = [];
    const fields = [
        ['customer_name', locale.value === 'lv' ? 'Vārds' : 'Name'],
        ['customer_email', 'E-pasts'],
        ['customer_phone', locale.value === 'lv' ? 'Tālrunis' : 'Phone'],
        ['delivery_city', locale.value === 'lv' ? 'Pilsēta' : 'City'],
        ['delivery_address', locale.value === 'lv' ? 'Adrese' : 'Address'],
    ];
    fields.forEach(([f, label]) => {
        const before = { ...errors.value };
        validateField(f);
        if (errors.value[f]) { missing.push(label); isValid = false; }
    });
    if (!isValid) {
        showToast(`${locale.value === 'lv' ? 'Lūdzu aizpildiet' : 'Please fill'}: ${missing.join(', ')}`, 'error');
        setTimeout(() => {
            document.querySelector('.has-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
    return isValid;
};

const formatCardNumber = () => {
    let v = form.value.card_number.replace(/\D/g, '').substring(0, 16);
    form.value.card_number = v.replace(/(\d{4})/g, '$1 ').trim();
};
const formatExpiry = () => {
    let v = form.value.card_expiry.replace(/\D/g, '');
    if (v.length >= 2) v = v.substring(0, 2) + '/' + v.substring(2, 4);
    form.value.card_expiry = v;
};
const formatCVV = () => {
    form.value.card_cvv = form.value.card_cvv.replace(/\D/g, '').substring(0, 4);
};

// ── PLACE ORDER ──────────────────────────────────────────────────
const placeOrder = async () => {
    if (!validateForm()) return;

    if (form.value.payment_method === 'card') {
        ['card_number', 'card_name', 'card_expiry', 'card_cvv'].forEach(f => validateField(f));
        if (errors.value.card_number || errors.value.card_name || errors.value.card_expiry || errors.value.card_cvv) {
            showToast(locale.value === 'lv' ? 'Lūdzu aizpildiet kartes informāciju' : 'Please fill in card information', 'error');
            return;
        }
    }

    isSubmitting.value = true;
    try {
        // Nodod kupona kodu un aprēķināto atlaidi
        await axios.post('/orders', {
            ...form.value,
            coupon_code:     form.value.coupon_code || null,
            discount_amount: couponDiscount.value,
        });

        showToast(locale.value === 'lv' ? 'Pasūtījums veiksmīgi izveidots!' : 'Order placed successfully!', 'success');
        setTimeout(() => router.visit('/orders'), 1500);
    } catch (error) {
        if (error.response?.data?.errors) errors.value = error.response.data.errors;
        showToast(
            error.response?.data?.message ||
            (locale.value === 'lv' ? 'Kļūda izveidojot pasūtījumu' : 'Error placing order'),
            'error'
        );
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <ShopLayout>
        <Head :title="locale === 'lv' ? 'Noformēt pasūtījumu' : 'Checkout'" />

        <ToastNotification :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

        <div class="checkout-page">
            <div class="checkout-container">
                <div class="checkout-header">
                    <Link href="/cart" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        {{ locale === 'lv' ? 'Atpakaļ uz grozu' : 'Back to Cart' }}
                    </Link>
                    <h1 class="checkout-title">{{ locale === 'lv' ? 'Noformēt pasūtījumu' : 'Checkout' }}</h1>
                </div>

                <div class="checkout-content">
                    <!-- Left: Forms -->
                    <div class="checkout-forms">

                        <!-- Contact -->
                        <div class="checkout-section" :class="{ 'has-errors': hasContactErrors }">
                            <h2 class="section-title">
                                <i class="fas fa-user"></i>
                                {{ locale === 'lv' ? 'Kontaktinformācija' : 'Contact Information' }}
                                <span v-if="hasContactErrors" class="error-badge">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ locale === 'lv' ? 'Nepieciešams' : 'Required' }}
                                </span>
                            </h2>
                            <div class="form-grid">
                                <div class="form-group" :class="{ 'has-error': errors.customer_email }">
                                    <label>{{ locale === 'lv' ? 'E-pasts *' : 'Email *' }}</label>
                                    <input v-model="form.customer_email" type="email" readonly disabled style="background:#f3f4f6;cursor:not-allowed;">
                                    <p class="field-note"><i class="fas fa-info-circle"></i> {{ locale === 'lv' ? 'E-pastu var mainīt tikai profila iestatījumos' : 'Email can only be changed in profile settings' }}</p>
                                </div>
                                <div class="form-group" :class="{ 'has-error': errors.customer_name }">
                                    <label>{{ locale === 'lv' ? 'Vārds, Uzvārds *' : 'Full name *' }}</label>
                                    <input v-model="form.customer_name" type="text" placeholder="Ādams Bruks" @focus="clearError('customer_name')" @blur="validateField('customer_name')" required>
                                    <span v-if="errors.customer_name" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.customer_name }}</span>
                                </div>
                                <div class="form-group" :class="{ 'has-error': errors.customer_phone }">
                                    <label>{{ locale === 'lv' ? 'Tālrunis *' : 'Phone *' }}</label>
                                    <input v-model="form.customer_phone" type="tel" placeholder="+371 20000000" @focus="clearError('customer_phone')" @blur="validateField('customer_phone')" required>
                                    <span v-if="errors.customer_phone" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.customer_phone }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery -->
                        <div class="checkout-section" :class="{ 'has-errors': hasDeliveryErrors }">
                            <h2 class="section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ locale === 'lv' ? 'Piegādes adrese' : 'Delivery Address' }}
                                <span v-if="hasDeliveryErrors" class="error-badge">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ locale === 'lv' ? 'Nepieciešams' : 'Required' }}
                                </span>
                            </h2>
                            <div class="form-grid">
                                <div class="form-group full-width" :class="{ 'has-error': errors.delivery_country }">
                                    <label>{{ locale === 'lv' ? 'Valsts *' : 'Country *' }}</label>
                                    <select v-model="form.delivery_country" @focus="clearError('delivery_country')" required>
                                        <option value="Latvia">{{ $t('country.latvia') }}</option>
                                        <option value="Estonia">{{ $t('country.estonia') }}</option>
                                        <option value="Lithuania">{{ $t('country.lithuania') }}</option>
                                        <option value="Germany">{{ $t('country.germany') }}</option>
                                        <option value="France">{{ $t('country.france') }}</option>
                                        <option value="Sweden">{{ $t('country.sweden') }}</option>
                                        <option value="Finland">{{ $t('country.finland') }}</option>
                                        <option value="Norway">{{ $t('country.norway') }}</option>
                                        <option value="Denmark">{{ $t('country.denmark') }}</option>
                                        <option value="Netherlands">{{ $t('country.netherlands') }}</option>
                                        <option value="Belgium">{{ $t('country.belgium') }}</option>
                                        <option value="Poland">{{ $t('country.poland') }}</option>
                                        <option value="United Kingdom">{{ $t('country.united_kingdom') }}</option>
                                        <option value="United States of America">{{ $t('country.united_states') }}</option>
                                        <option value="Other">{{ $t('country.other') }}</option>
                                    </select>
                                </div>
                                <div class="form-group" :class="{ 'has-error': errors.delivery_city }">
                                    <label>{{ locale === 'lv' ? 'Pilsēta *' : 'City *' }}</label>
                                    <input v-model="form.delivery_city" type="text" :placeholder="locale === 'lv' ? 'Rīga' : 'Riga'" @focus="clearError('delivery_city')" @blur="validateField('delivery_city')" required>
                                    <span v-if="errors.delivery_city" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.delivery_city }}</span>
                                </div>
                                <div class="form-group">
                                    <label>{{ locale === 'lv' ? 'Pasta indekss' : 'Postal Code' }}</label>
                                    <input v-model="form.delivery_postal_code" type="text" placeholder="LV-1001">
                                </div>
                                <div class="form-group full-width" :class="{ 'has-error': errors.delivery_address }">
                                    <label>{{ locale === 'lv' ? 'Adrese *' : 'Address *' }}</label>
                                    <input v-model="form.delivery_address" type="text" :placeholder="locale === 'lv' ? 'Brīvības iela 1-23' : 'Freedom Street 1-23'" @focus="clearError('delivery_address')" @blur="validateField('delivery_address')" required>
                                    <span v-if="errors.delivery_address" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.delivery_address }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div class="checkout-section">
                            <h2 class="section-title">
                                <i class="fas fa-credit-card"></i>
                                {{ locale === 'lv' ? 'Maksājuma veids' : 'Payment Method' }}
                            </h2>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" v-model="form.payment_method" value="card">
                                    <div class="payment-content"><i class="fas fa-credit-card"></i><span>{{ locale === 'lv' ? 'Kredītkarte / Debeta karte' : 'Credit / Debit Card' }}</span></div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" v-model="form.payment_method" value="bank_transfer">
                                    <div class="payment-content"><i class="fas fa-university"></i><span>{{ locale === 'lv' ? 'Bankas pārskaitījums' : 'Bank Transfer' }}</span></div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" v-model="form.payment_method" value="cash_on_delivery">
                                    <div class="payment-content"><i class="fas fa-money-bill-wave"></i><span>{{ locale === 'lv' ? 'Skaidrā nauda piegādes brīdī' : 'Cash on Delivery' }}</span></div>
                                </label>
                            </div>

                            <div v-if="form.payment_method === 'card'" class="card-details">
                                <h3 class="card-details-title">{{ locale === 'lv' ? 'Kartes informācija' : 'Card Information' }}</h3>
                                <div class="card-form">
                                    <div class="form-group full-width" :class="{ 'has-error': errors.card_number }">
                                        <label>{{ locale === 'lv' ? 'Kartes numurs *' : 'Card Number *' }}</label>
                                        <input v-model="form.card_number" type="text" placeholder="1234 5678 9012 3456" maxlength="19" @input="formatCardNumber" @focus="clearError('card_number')" @blur="validateField('card_number')">
                                        <span v-if="errors.card_number" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.card_number }}</span>
                                    </div>
                                    <div class="form-group full-width" :class="{ 'has-error': errors.card_name }">
                                        <label>{{ locale === 'lv' ? 'Vārds uz kartes *' : 'Name on Card *' }}</label>
                                        <input v-model="form.card_name" type="text" placeholder="JĀNIS BĒRZIŅŠ" @focus="clearError('card_name')" @blur="validateField('card_name')">
                                        <span v-if="errors.card_name" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.card_name }}</span>
                                    </div>
                                    <div class="card-row">
                                        <div class="form-group" :class="{ 'has-error': errors.card_expiry }">
                                            <label>{{ locale === 'lv' ? 'Derīguma termiņš *' : 'Expiry Date *' }}</label>
                                            <input v-model="form.card_expiry" type="text" placeholder="MM/YY" maxlength="5" @input="formatExpiry" @focus="clearError('card_expiry')" @blur="validateField('card_expiry')">
                                            <span v-if="errors.card_expiry" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.card_expiry }}</span>
                                        </div>
                                        <div class="form-group" :class="{ 'has-error': errors.card_cvv }">
                                            <label>CVV *</label>
                                            <input v-model="form.card_cvv" type="text" placeholder="123" maxlength="4" @input="formatCVV" @focus="clearError('card_cvv')" @blur="validateField('card_cvv')">
                                            <span v-if="errors.card_cvv" class="error"><i class="fas fa-exclamation-circle"></i> {{ errors.card_cvv }}</span>
                                        </div>
                                    </div>
                                    <div class="security-note">
                                        <i class="fas fa-lock"></i>
                                        <span>{{ locale === 'lv' ? 'Jūsu maksājuma informācija ir droša un šifrēta' : 'Your payment information is secure and encrypted' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="checkout-section">
                            <h2 class="section-title">
                                <i class="fas fa-comment"></i>
                                {{ locale === 'lv' ? 'Papildus piezīmes' : 'Additional Notes' }}
                                <span class="optional">({{ locale === 'lv' ? 'neobligāti' : 'optional' }})</span>
                            </h2>
                            <textarea v-model="form.notes" :placeholder="locale === 'lv' ? 'Piegādāt darba laikā...' : 'Deliver during business hours...'" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Right: Order Summary -->
                    <div class="order-summary-sticky">
                        <div class="order-summary">
                            <h2 class="summary-title">{{ locale === 'lv' ? 'Pasūtījuma kopsavilkums' : 'Order Summary' }}</h2>

                            <!-- Products -->
                            <div class="summary-products">
                                <div v-for="item in cart.items" :key="item.id" class="summary-product">
                                    <img :src="getProductImage(item.product)" :alt="getProductName(item.product)">
                                    <div class="product-info">
                                        <p class="product-name">{{ getProductName(item.product) }}</p>
                                        <p class="product-quantity">
                                            {{ locale === 'lv' ? 'Daudzums' : 'Quantity' }}: {{ item.quantity }}
                                            <span v-if="item.size" class="summary-size-badge">{{ item.size }}</span>
                                        </p>
                                    </div>
                                    <p class="product-price">{{ formatPrice(item.total) }}€</p>
                                </div>
                            </div>

                            <div class="summary-divider"></div>

                            <!-- Pricing rows -->
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
                                <span>{{ locale === 'lv' ? 'Piegāde' : 'Shipping' }}</span>
                                <span>{{ shippingCost === 0 ? (locale === 'lv' ? 'Bezmaksas' : 'Free') : formatPrice(shippingCost) + '€' }}</span>
                            </div>
                            <!-- Bezmaksas piegādes progress -->
                            <div v-if="shippingFreeRemaining > 0" class="free-shipping-hint">
                                <i class="fas fa-truck"></i>
                                <span v-if="locale === 'lv'">Pievieno vēl <strong>{{ formatPrice(shippingFreeRemaining) }}€</strong> bezmaksas piegādei!</span>
                                <span v-else>Add <strong>{{ formatPrice(shippingFreeRemaining) }}€</strong> more for free shipping!</span>
                            </div>

                            <!-- Discount row -->
                            <div v-if="discount > 0" class="summary-row discount-row">
                                <span>
                                    <i class="fas fa-tag"></i>
                                    {{ locale === 'lv' ? 'Kupona atlaide' : 'Coupon Discount' }}
                                    <code class="coupon-badge">{{ couponApplied?.code }}</code>
                                </span>
                                <span class="discount-amount">-{{ formatPrice(discount) }}€</span>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-row summary-total">
                                <span>{{ locale === 'lv' ? 'Kopā' : 'Total' }}</span>
                                <span>{{ formatPrice(totalWithShipping) }}€</span>
                            </div>

                            <!-- ══ KUPONA SEKCIJA CHECKOUT ════════════════════ -->
                            <div class="coupon-section">
                                <p class="coupon-label">
                                    <i class="fas fa-ticket-alt"></i>
                                    {{ locale === 'lv' ? 'Atlaižu kupons' : 'Discount Coupon' }}
                                </p>

                                <!-- Applied -->
                                <div v-if="couponApplied" class="coupon-applied">
                                    <div class="coupon-applied-inner">
                                        <i class="fas fa-check-circle"></i>
                                        <div>
                                            <span class="coupon-code-text">{{ couponApplied.code }}</span>
                                            <span class="coupon-discount-badge">
                                                {{ couponApplied.type === 'percentage'
                                                ? `-${couponApplied.value}%`
                                                : `-€${formatPrice(couponApplied.value)}`
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <button @click="removeCoupon" class="coupon-remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Input -->
                                <div v-else class="coupon-row">
                                    <input
                                        v-model="couponInputCode"
                                        type="text"
                                        :placeholder="locale === 'lv' ? 'Ievadi kodu...' : 'Enter code...'"
                                        class="coupon-input"
                                        :class="{ 'has-error-input': couponError }"
                                        @keyup.enter="applyCoupon()"
                                        maxlength="64"
                                        autocomplete="off"
                                        spellcheck="false"
                                    >
                                    <button @click="applyCoupon()" class="coupon-btn" :disabled="isApplyingCoupon || !couponInputCode.trim()">
                                        <i v-if="isApplyingCoupon" class="fas fa-spinner fa-spin"></i>
                                        <span v-else>{{ locale === 'lv' ? 'Pielietot' : 'Apply' }}</span>
                                    </button>
                                </div>

                                <p v-if="couponError" class="coupon-msg error-msg">
                                    <i class="fas fa-exclamation-circle"></i> {{ couponError }}
                                </p>
                                <p v-if="couponSuccess && couponApplied" class="coupon-msg success-msg">
                                    <i class="fas fa-check-circle"></i> {{ couponSuccess }}
                                </p>
                            </div>
                            <!-- ══════════════════════════════════════════════ -->

                            <button @click="placeOrder" class="place-order-btn" :disabled="isSubmitting">
                                <i v-if="!isSubmitting" class="fas fa-lock"></i>
                                <i v-else class="fas fa-spinner fa-spin"></i>
                                {{ isSubmitting
                                ? (locale === 'lv' ? 'Apstrādā...' : 'Processing...')
                                : (locale === 'lv' ? 'Apstiprināt pasūtījumu' : 'Place Order')
                                }}
                            </button>

                            <p class="secure-note">
                                <i class="fas fa-shield-alt"></i>
                                {{ locale === 'lv' ? 'Droša apmaksa ar SSL šifrēšanu' : 'Secure payment with SSL encryption' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* Base */
.checkout-page { background: #f9fafb; min-height: 100vh; padding: 40px 20px; }
.checkout-container { max-width: 1400px; margin: 0 auto; }
.checkout-header { margin-bottom: 40px; }
.back-link { display: inline-flex; align-items: center; gap: 8px; color: #6b7280; text-decoration: none; font-size: 14px; margin-bottom: 16px; transition: color 0.2s; }
.back-link:hover { color: #dc2626; }
.checkout-title { font-size: 32px; font-weight: 700; color: #1f2937; margin: 0; }
.checkout-content { display: grid; grid-template-columns: 1fr 440px; gap: 40px; }
.checkout-forms { display: flex; flex-direction: column; gap: 24px; }

.checkout-section {
    background: white; border-radius: 12px; padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.3s;
}
.checkout-section.has-errors { border: 2px solid #fca5a5; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }

.section-title { font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 20px; display: flex; align-items: center; gap: 12px; }
.section-title i { color: #dc2626; }
.error-badge { margin-left: auto; padding: 4px 12px; background: #fee2e2; color: #dc2626; border-radius: 20px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 6px; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.7} }
.optional { font-size: 14px; font-weight: 400; color: #9ca3af; }

.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; transition: all 0.3s; }
.form-group.full-width { grid-column: 1 / -1; }
.form-group.has-error input, .form-group.has-error select { border-color: #ef4444; background-color: #fef2f2; }
.form-group.has-error label { color: #dc2626; }
.form-group label { font-size: 14px; font-weight: 500; color: #374151; }
.form-group input, .form-group select, textarea {
    padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;
}
.form-group input:focus, .form-group select:focus, textarea:focus {
    outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
}
.error { color: #dc2626; font-size: 12px; display: flex; align-items: center; gap: 4px; animation: shake 0.3s; }
@keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }
textarea { width: 100%; resize: vertical; }
.field-note { display: flex; align-items: center; gap: 0.5rem; margin-top: 4px; font-size: 12px; color: #6b7280; }
.field-note i { color: #3b82f6; }

/* Payment */
.payment-methods { display: flex; flex-direction: column; gap: 12px; }
.payment-option { display: flex; align-items: center; padding: 16px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
.payment-option:hover { border-color: #dc2626; background: #fef2f2; }
.payment-option input[type="radio"] { margin-right: 12px; }
.payment-content { display: flex; align-items: center; gap: 12px; font-weight: 500; }
.payment-content i { font-size: 20px; }

/* Card */
.card-details { margin-top: 1.5rem; padding: 1.5rem; background: linear-gradient(135deg,#f9fafb,#f3f4f6); border: 2px solid #e5e7eb; border-radius: 0.75rem; }
.card-details-title { font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
.card-details-title::before { content: "💳"; font-size: 1.25rem; }
.card-form { display: flex; flex-direction: column; gap: 1rem; }
.card-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.security-note { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 0.5rem; font-size: 0.875rem; color: #065f46; }
.security-note i { color: #10b981; }

/* Order summary */
.order-summary-sticky { position: sticky; top: 100px; height: fit-content; }
.order-summary { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.summary-title { font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 20px; }
.summary-products { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
.summary-product { display: grid; grid-template-columns: 60px 1fr auto; gap: 12px; align-items: center; }
.summary-product img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
.product-info { display: flex; flex-direction: column; gap: 4px; }
.product-name { font-size: 14px; font-weight: 500; color: #1f2937; margin: 0; }
.product-quantity { font-size: 12px; color: #6b7280; margin: 0; display: flex; align-items: center; gap: 6px; }
.summary-size-badge {
    background: #e5e7eb; color: #374151; padding: 1px 7px;
    border-radius: 10px; font-size: 11px; font-weight: 600;
}
.product-price { font-size: 14px; font-weight: 600; color: #dc2626; margin: 0; }
.summary-divider { height: 1px; background: #e5e7eb; margin: 16px 0; }
.summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #6b7280; }
.summary-row-vat { padding: 2px 0 8px; }
.vat-label { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #9ca3af; font-style: italic; }
.vat-label .fa-info-circle { color: #d1d5db; font-size: 11px; }
.vat-value { font-size: 12px; color: #9ca3af; }
.free-shipping-hint { display: flex; align-items: center; gap: 6px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 7px 10px; font-size: 12px; color: #92400e; margin: 4px 0 2px; }
.free-shipping-hint .fa-truck { color: #d97706; }
.discount-row { color: #059669; font-weight: 600; }
.discount-row i { margin-right: 4px; }
.discount-amount { color: #059669; font-weight: 700; }
.coupon-badge { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 6px; font-family: monospace; }
.summary-total { font-size: 20px; font-weight: 700; color: #1f2937; padding: 16px 0 0; }

/* Coupon in checkout */
.coupon-section { margin: 16px 0; padding: 14px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 10px; }
.coupon-label { font-size: 13px; font-weight: 600; color: #374151; margin: 0 0 8px; display: flex; align-items: center; gap: 5px; }
.coupon-label i { color: #dc2626; }

.coupon-row { display: flex; gap: 8px; }
.coupon-input {
    flex: 1; padding: 9px 11px; border: 1.5px solid #d1d5db; border-radius: 7px;
    font-size: 13px; font-family: monospace; letter-spacing: 1px; text-transform: uppercase;
    transition: border-color 0.2s;
}
.coupon-input:focus { outline: none; border-color: #dc2626; }
.coupon-input.has-error-input { border-color: #ef4444; background: #fff5f5; }
.coupon-input::placeholder { text-transform: none; letter-spacing: normal; font-family: inherit; color: #9ca3af; }

.coupon-btn {
    padding: 9px 16px; background: #dc2626; color: white; border: none;
    border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
}
.coupon-btn:hover:not(:disabled) { background: #b91c1c; }
.coupon-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.coupon-applied {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px; background: #ecfdf5; border: 1.5px solid #10b981;
    border-radius: 8px; gap: 8px;
}
.coupon-applied-inner { display: flex; align-items: center; gap: 8px; flex: 1; }
.coupon-applied-inner > i { color: #10b981; font-size: 16px; }
.coupon-code-text { font-family: monospace; font-weight: 700; font-size: 14px; color: #065f46; letter-spacing: 1px; }
.coupon-discount-badge { margin-left: 8px; background: #10b981; color: white; padding: 2px 7px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.coupon-remove { background: none; border: none; color: #6b7280; cursor: pointer; padding: 4px; border-radius: 4px; transition: all 0.2s; }
.coupon-remove:hover { color: #dc2626; background: #fee2e2; }

.coupon-msg { font-size: 12px; margin: 7px 0 0; display: flex; align-items: center; gap: 4px; }
.error-msg { color: #dc2626; }
.success-msg { color: #059669; }

/* Place order */
.place-order-btn {
    width: 100%; padding: 16px; background: #dc2626; color: white; border: none;
    border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;
    transition: all 0.3s; display: flex; align-items: center; justify-content: center;
    gap: 8px; margin-top: 20px;
}
.place-order-btn:hover:not(:disabled) { background: #b91c1c; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.place-order-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.secure-note { text-align: center; font-size: 12px; color: #6b7280; margin: 12px 0 0; display: flex; align-items: center; justify-content: center; gap: 6px; }
.secure-note i { color: #10b981; }

/* Responsive */
@media (max-width: 1024px) {
    .checkout-content { grid-template-columns: 1fr; }
    .order-summary-sticky { position: static; }
}
@media (max-width: 768px) {
    .checkout-title { font-size: 24px; }
    .form-grid { grid-template-columns: 1fr; }
    .card-row { grid-template-columns: 1fr; }
}
</style>
