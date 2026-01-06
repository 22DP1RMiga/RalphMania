<script setup>
import { ref, computed } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import axios from 'axios';

const { locale } = useI18n();

const props = defineProps({
    cart: Object,
    user: Object,
});

// Form data
const form = ref({
    customer_name: props.user?.name || '',
    customer_email: props.user?.email || '',
    customer_phone: props.user?.phone || '',
    delivery_country: props.user?.country || 'Latvia',
    delivery_city: props.user?.city || '',
    delivery_address: props.user?.address || '',
    delivery_postal_code: props.user?.postal_code || '',
    payment_method: 'card',
    notes: '',

    // CARD PAYMENT FIELDS
    card_number: '',
    card_name: '',
    card_expiry: '',
    card_cvv: '',
});

const errors = ref({});
const isSubmitting = ref(false);

// Toast notification state
const toast = ref({
    show: false,
    message: '',
    type: 'success',
});

// Check if sections have errors
const hasContactErrors = computed(() => {
    return !!(errors.value.customer_name || errors.value.customer_email || errors.value.customer_phone);
});

const hasDeliveryErrors = computed(() => {
    return !!(errors.value.delivery_country || errors.value.delivery_city || errors.value.delivery_address);
});

// Shipping cost calculation
const shippingCost = computed(() => {
    if (!props.cart?.total_amount) return 0;

    // Free shipping over 50€
    if (props.cart.total_amount >= 50) return 0;

    // Shipping costs by country
    const shipping = {
        'Latvia': 3.99,
        'Estonia': 5.99,
        'Lithuania': 5.99,
    };

    return shipping[form.value.delivery_country] || 5.99;
});

const totalWithShipping = computed(() => {
    const subtotal = props.cart?.total_amount || 0;
    const vat = subtotal * 0.21;
    return subtotal + vat + shippingCost.value;
});

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

// Show toast notification
const showToast = (message, type = 'success') => {
    toast.value = {
        show: true,
        message,
        type,
    };
};

// Clear specific error
const clearError = (field) => {
    if (errors.value[field]) {
        delete errors.value[field];
    }
};

// Validate single field
const validateField = (field) => {
    switch (field) {
        case 'customer_name':
            if (!form.value.customer_name.trim()) {
                errors.value.customer_name = locale.value === 'lv' ? 'Vārds ir obligāts' : 'Name is required';
            }
            break;
        case 'customer_email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(form.value.customer_email)) {
                errors.value.customer_email = locale.value === 'lv' ? 'Nederīgs e-pasts' : 'Invalid email';
            }
            break;
        case 'customer_phone':
            if (!form.value.customer_phone.trim()) {
                errors.value.customer_phone = locale.value === 'lv' ? 'Tālrunis ir obligāts' : 'Phone is required';
            }
            break;
        case 'delivery_city':
            if (!form.value.delivery_city.trim()) {
                errors.value.delivery_city = locale.value === 'lv' ? 'Pilsēta ir obligāta' : 'City is required';
            }
            break;
        case 'delivery_address':
            if (!form.value.delivery_address.trim()) {
                errors.value.delivery_address = locale.value === 'lv' ? 'Adrese ir obligāta' : 'Address is required';
            }
            break;
    }

    // CARD VALIDATIONS
    if (field === 'card_number' && form.value.payment_method === 'card') {
        const cardNumber = form.value.card_number.replace(/\s/g, '');
        if (!cardNumber) {
            errors.value.card_number = locale.value === 'lv'
                ? 'Kartes numurs ir obligāts'
                : 'Card number is required';
        } else if (cardNumber.length < 13) {
            errors.value.card_number = locale.value === 'lv'
                ? 'Nederīgs kartes numurs'
                : 'Invalid card number';
        }
    }

    if (field === 'card_name' && form.value.payment_method === 'card') {
        if (!form.value.card_name.trim()) {
            errors.value.card_name = locale.value === 'lv'
                ? 'Vārds uz kartes ir obligāts'
                : 'Name on card is required';
        }
    }

    if (field === 'card_expiry' && form.value.payment_method === 'card') {
        const expiryRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;
        if (!form.value.card_expiry) {
            errors.value.card_expiry = locale.value === 'lv'
                ? 'Derīguma termiņš ir obligāts'
                : 'Expiry date is required';
        } else if (!expiryRegex.test(form.value.card_expiry)) {
            errors.value.card_expiry = locale.value === 'lv'
                ? 'Nederīgs formāts (MM/YY)'
                : 'Invalid format (MM/YY)';
        }
    }

    if (field === 'card_cvv' && form.value.payment_method === 'card') {
        if (!form.value.card_cvv) {
            errors.value.card_cvv = locale.value === 'lv'
                ? 'CVV kods ir obligāts'
                : 'CVV is required';
        } else if (form.value.card_cvv.length < 3) {
            errors.value.card_cvv = locale.value === 'lv'
                ? 'CVV jābūt 3-4 cipariem'
                : 'CVV must be 3-4 digits';
        }
    }
};

// Format card number with spaces (1234 5678 9012 3456)
const formatCardNumber = () => {
    let value = form.value.card_number.replace(/\s/g, '');
    value = value.replace(/\D/g, ''); // Remove non-digits
    value = value.substring(0, 16); // Max 16 digits

    // Add space every 4 digits
    form.value.card_number = value.replace(/(\d{4})/g, '$1 ').trim();
};

// Format expiry date (MM/YY)
const formatExpiry = () => {
    let value = form.value.card_expiry.replace(/\D/g, '');

    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }

    form.value.card_expiry = value;
};

// Format CVV (3-4 digits only)
const formatCVV = () => {
    form.value.card_cvv = form.value.card_cvv.replace(/\D/g, '').substring(0, 4);
};

// Validate entire form
const validateForm = () => {
    errors.value = {};
    let isValid = true;
    const missingFields = [];

    if (!form.value.customer_name.trim()) {
        errors.value.customer_name = locale.value === 'lv' ? 'Vārds ir obligāts' : 'Name is required';
        missingFields.push(locale.value === 'lv' ? 'Vārds' : 'Name');
        isValid = false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.value.customer_email)) {
        errors.value.customer_email = locale.value === 'lv' ? 'Nederīgs e-pasts' : 'Invalid email';
        missingFields.push(locale.value === 'lv' ? 'E-pasts' : 'Email');
        isValid = false;
    }

    if (!form.value.customer_phone.trim()) {
        errors.value.customer_phone = locale.value === 'lv' ? 'Tālrunis ir obligāts' : 'Phone is required';
        missingFields.push(locale.value === 'lv' ? 'Tālrunis' : 'Phone');
        isValid = false;
    }

    if (!form.value.delivery_city.trim()) {
        errors.value.delivery_city = locale.value === 'lv' ? 'Pilsēta ir obligāta' : 'City is required';
        missingFields.push(locale.value === 'lv' ? 'Pilsēta' : 'City');
        isValid = false;
    }

    if (!form.value.delivery_address.trim()) {
        errors.value.delivery_address = locale.value === 'lv' ? 'Adrese ir obligāta' : 'Address is required';
        missingFields.push(locale.value === 'lv' ? 'Adrese' : 'Address');
        isValid = false;
    }

    // Show detailed error message with missing fields
    if (!isValid) {
        const fieldList = missingFields.join(', ');
        const message = locale.value === 'lv'
            ? `Lūdzu aizpildiet šos laukus: ${fieldList}`
            : `Please fill these fields: ${fieldList}`;
        showToast(message, 'error');

        // Scroll to first error
        setTimeout(() => {
            const firstError = document.querySelector('.has-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    }

    return isValid;
};

// Place order
const placeOrder = async () => {
    // Validate all fields
    if (!validateForm()) {
        showToast(
            locale.value === 'lv'
                ? 'Lūdzu aizpildiet visus obligātos laukus'
                : 'Please fill in all required fields',
            'error'
        );
        return;
    }

    // Validate card fields if card payment selected
    if (form.value.payment_method === 'card') {
        validateField('card_number');
        validateField('card_name');
        validateField('card_expiry');
        validateField('card_cvv');

        if (errors.value.card_number || errors.value.card_name ||
            errors.value.card_expiry || errors.value.card_cvv) {
            showToast(
                locale.value === 'lv'
                    ? 'Lūdzu aizpildiet kartes informāciju'
                    : 'Please fill in card information',
                'error'
            );
            return;
        }
    }

    isSubmitting.value = true;

    try {
        const orderData = {
            ...form.value,
            // For security: card data should be tokenized in production
            // This is just for demonstration
        };

        await axios.post('/orders', orderData);

        showToast(
            locale.value === 'lv'
                ? 'Pasūtījums veiksmīgi izveidots!'
                : 'Order placed successfully!',
            'success'
        );

        // Redirect to orders page
        setTimeout(() => {
            router.visit('/orders');
        }, 1500);

    } catch (error) {
        console.error('Order error:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }

        showToast(
            error.response?.data?.message ||
            (locale.value === 'lv'
                ? 'Kļūda izveidojot pasūtījumu'
                : 'Error placing order'),
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

        <!-- Toast Notification -->
        <ToastNotification
            :show="toast.show"
            :message="toast.message"
            :type="toast.type"
            @close="toast.show = false"
        />

        <div class="checkout-page">
            <div class="checkout-container">
                <!-- Header -->
                <div class="checkout-header">
                    <Link href="/cart" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        {{ locale === 'lv' ? 'Atpakaļ uz grozu' : 'Back to Cart' }}
                    </Link>
                    <h1 class="checkout-title">
                        {{ locale === 'lv' ? 'Noformēt pasūtījumu' : 'Checkout' }}
                    </h1>
                </div>

                <div class="checkout-content">
                    <!-- Left Column: Forms -->
                    <div class="checkout-forms">
                        <!-- Customer Information -->
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
                                    <input
                                        v-model="form.customer_email"
                                        type="email"
                                        placeholder="adam.brooke@example.com"
                                        readonly
                                        disabled
                                        style="background-color: #f3f4f6; cursor: not-allowed;"
                                    >
                                    <p class="field-note">
                                        <i class="fas fa-info-circle"></i>
                                        {{ locale === 'lv'
                                        ? 'E-pastu var mainīt tikai profila iestatījumos'
                                        : 'Email can only be changed in profile settings'
                                        }}
                                    </p>
                                </div>

                                <div class="form-group" :class="{ 'has-error': errors.customer_name }">
                                    <label>{{ locale === 'lv' ? 'Vārds, Uzvārds *' : 'Full name *' }}</label>
                                    <input
                                        v-model="form.customer_name"
                                        type="email"
                                        placeholder="Ādams Bruks"
                                        @focus="clearError('customer_name')"
                                        @blur="validateField('customer_name')"
                                        required
                                    >
                                    <span v-if="errors.customer_name" class="error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ errors.customer_name }}
                                    </span>
                                </div>

                                <div class="form-group" :class="{ 'has-error': errors.customer_phone }">
                                    <label>{{ locale === 'lv' ? 'Tālrunis *' : 'Phone *' }}</label>
                                    <input
                                        v-model="form.customer_phone"
                                        type="tel"
                                        placeholder="+371 20000000"
                                        @focus="clearError('customer_phone')"
                                        @blur="validateField('customer_phone')"
                                        required
                                    >
                                    <span v-if="errors.customer_phone" class="error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ errors.customer_phone }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Address -->
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
                                    <select
                                        v-model="form.delivery_country"
                                        @focus="clearError('delivery_country')"
                                        required
                                    >
                                        <option value="Afghanistan">{{ $t('country.afghanistan') }}</option>
                                        <option value="Albania">{{ $t('country.albania') }}</option>
                                        <option value="Algeria">{{ $t('country.algeria') }}</option>
                                        <option value="Andorra">{{ $t('country.andorra') }}</option>
                                        <option value="Angola">{{ $t('country.angola') }}</option>
                                        <option value="Antigua and Barbuda">{{ $t('country.antigua_and_barbuda') }}</option>
                                        <option value="Argentina">{{ $t('country.argentina') }}</option>
                                        <option value="Armenia">{{ $t('country.armenia') }}</option>
                                        <option value="Australia">{{ $t('country.australia') }}</option>
                                        <option value="Austria">{{ $t('country.austria') }}</option>
                                        <option value="Azerbaijan">{{ $t('country.azerbaijan') }}</option>
                                        <option value="Bahamas">{{ $t('country.bahamas') }}</option>
                                        <option value="Bahrain">{{ $t('country.bahrain') }}</option>
                                        <option value="Bangladesh">{{ $t('country.bangladesh') }}</option>
                                        <option value="Barbados">{{ $t('country.barbados') }}</option>
                                        <option value="Belarus">{{ $t('country.belarus') }}</option>
                                        <option value="Belgium">{{ $t('country.belgium') }}</option>
                                        <option value="Belize">{{ $t('country.belize') }}</option>
                                        <option value="Benin">{{ $t('country.benin') }}</option>
                                        <option value="Bhutan">{{ $t('country.bhutan') }}</option>
                                        <option value="Bolivia">{{ $t('country.bolivia') }}</option>
                                        <option value="Bosnia and Herzegovina">{{ $t('country.bosnia_and_herzegovina') }}</option>
                                        <option value="Botswana">{{ $t('country.botswana') }}</option>
                                        <option value="Brazil">{{ $t('country.brazil') }}</option>
                                        <option value="Brunei">{{ $t('country.brunei') }}</option>
                                        <option value="Bulgaria">{{ $t('country.bulgaria') }}</option>
                                        <option value="Burkina Faso">{{ $t('country.burkina_faso') }}</option>
                                        <option value="Burundi">{{ $t('country.burundi') }}</option>
                                        <option value="Cambodia">{{ $t('country.cambodia') }}</option>
                                        <option value="Cameroon">{{ $t('country.cameroon') }}</option>
                                        <option value="Canada">{{ $t('country.canada') }}</option>
                                        <option value="Chile">{{ $t('country.chile') }}</option>
                                        <option value="China">{{ $t('country.china') }}</option>
                                        <option value="Colombia">{{ $t('country.colombia') }}</option>
                                        <option value="Croatia">{{ $t('country.croatia') }}</option>
                                        <option value="Cuba">{{ $t('country.cuba') }}</option>
                                        <option value="Cyprus">{{ $t('country.cyprus') }}</option>
                                        <option value="Czechia (Czech Republic)">{{ $t('country.czechia') }}</option>
                                        <option value="Denmark">{{ $t('country.denmark') }}</option>
                                        <option value="Egypt">{{ $t('country.egypt') }}</option>
                                        <option value="Estonia">{{ $t('country.estonia') }}</option>
                                        <option value="Finland">{{ $t('country.finland') }}</option>
                                        <option value="France">{{ $t('country.france') }}</option>
                                        <option value="Germany">{{ $t('country.germany') }}</option>
                                        <option value="Greece">{{ $t('country.greece') }}</option>
                                        <option value="Hungary">{{ $t('country.hungary') }}</option>
                                        <option value="Iceland">{{ $t('country.iceland') }}</option>
                                        <option value="India">{{ $t('country.india') }}</option>
                                        <option value="Indonesia">{{ $t('country.indonesia') }}</option>
                                        <option value="Ireland">{{ $t('country.ireland') }}</option>
                                        <option value="Israel">{{ $t('country.israel') }}</option>
                                        <option value="Italy">{{ $t('country.italy') }}</option>
                                        <option value="Japan">{{ $t('country.japan') }}</option>
                                        <option value="Latvia">{{ $t('country.latvia') }}</option>
                                        <option value="Lithuania">{{ $t('country.lithuania') }}</option>
                                        <option value="Luxembourg">{{ $t('country.luxembourg') }}</option>
                                        <option value="Mexico">{{ $t('country.mexico') }}</option>
                                        <option value="Netherlands">{{ $t('country.netherlands') }}</option>
                                        <option value="Norway">{{ $t('country.norway') }}</option>
                                        <option value="Poland">{{ $t('country.poland') }}</option>
                                        <option value="Portugal">{{ $t('country.portugal') }}</option>
                                        <option value="Romania">{{ $t('country.romania') }}</option>
                                        <option value="Russia">{{ $t('country.russia') }}</option>
                                        <option value="Slovakia">{{ $t('country.slovakia') }}</option>
                                        <option value="Slovenia">{{ $t('country.slovenia') }}</option>
                                        <option value="Spain">{{ $t('country.spain') }}</option>
                                        <option value="Sweden">{{ $t('country.sweden') }}</option>
                                        <option value="Switzerland">{{ $t('country.switzerland') }}</option>
                                        <option value="Ukraine">{{ $t('country.ukraine') }}</option>
                                        <option value="United Kingdom">{{ $t('country.united_kingdom') }}</option>
                                        <option value="United States of America">{{ $t('country.united_states') }}</option>
                                        <option value="Uruguay">{{ $t('country.uruguay') }}</option>
                                        <option value="Uzbekistan">{{ $t('country.uzbekistan') }}</option>
                                        <option value="Vanuatu">{{ $t('country.vanuatu') }}</option>
                                        <option value="Venezuela">{{ $t('country.venezuela') }}</option>
                                        <option value="Vietnam">{{ $t('country.vietnam') }}</option>
                                        <option value="Yemen">{{ $t('country.yemen') }}</option>
                                        <option value="Zambia">{{ $t('country.zambia') }}</option>
                                        <option value="Zimbabwe">{{ $t('country.zimbabwe') }}</option>
                                        <option value="Other">{{ $t('country.other') }}</option>
                                    </select>
                                    <span v-if="errors.delivery_country" class="error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ errors.delivery_country }}
                                    </span>
                                </div>

                                <div class="form-group" :class="{ 'has-error': errors.delivery_city }">
                                    <label>{{ locale === 'lv' ? 'Pilsēta *' : 'City *' }}</label>
                                    <input
                                        v-model="form.delivery_city"
                                        type="text"
                                        :placeholder="locale === 'lv' ? 'Rīga' : 'Riga'"
                                        @focus="clearError('delivery_city')"
                                        @blur="validateField('delivery_city')"
                                        required
                                    >
                                    <span v-if="errors.delivery_city" class="error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ errors.delivery_city }}
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label>{{ locale === 'lv' ? 'Pasta indekss' : 'Postal Code' }}</label>
                                    <input
                                        v-model="form.delivery_postal_code"
                                        type="text"
                                        placeholder="LV-1001"
                                    >
                                </div>

                                <div class="form-group full-width" :class="{ 'has-error': errors.delivery_address }">
                                    <label>{{ locale === 'lv' ? 'Adrese *' : 'Address *' }}</label>
                                    <input
                                        v-model="form.delivery_address"
                                        type="text"
                                        :placeholder="locale === 'lv' ? 'Brīvības iela 1-23' : 'Freedom Street 1-23'"
                                        @focus="clearError('delivery_address')"
                                        @blur="validateField('delivery_address')"
                                        required
                                    >
                                    <span v-if="errors.delivery_address" class="error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ errors.delivery_address }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="checkout-section">
                            <h2 class="section-title">
                                <i class="fas fa-credit-card"></i>
                                {{ locale === 'lv' ? 'Maksājuma veids' : 'Payment Method' }}
                            </h2>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input
                                        type="radio"
                                        v-model="form.payment_method"
                                        value="card"
                                        checked
                                    >
                                    <div class="payment-content">
                                        <i class="fas fa-credit-card"></i>
                                        <span>{{ locale === 'lv' ? 'Kredītkarte / Debeta karte' : 'Credit / Debit Card' }}</span>
                                    </div>
                                </label>

                                <label class="payment-option">
                                    <input
                                        type="radio"
                                        v-model="form.payment_method"
                                        value="bank_transfer"
                                    >
                                    <div class="payment-content">
                                        <i class="fas fa-university"></i>
                                        <span>{{ locale === 'lv' ? 'Bankas pārskaitījums' : 'Bank Transfer' }}</span>
                                    </div>
                                </label>

                                <label class="payment-option">
                                    <input
                                        type="radio"
                                        v-model="form.payment_method"
                                        value="cash_on_delivery"
                                    >
                                    <div class="payment-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>{{ locale === 'lv' ? 'Skaidrā nauda piegādes brīdī' : 'Cash on Delivery' }}</span>
                                    </div>
                                </label>
                            </div>  <!-- .payment-methods -->

                            <!-- CARD INPUT FIELDS (show when card is selected) -->
                            <div v-if="form.payment_method === 'card'" class="card-details">
                                <h3 class="card-details-title">
                                    {{ locale === 'lv' ? 'Kartes informācija' : 'Card Information' }}
                                </h3>

                                <div class="card-form">
                                    <!-- Card Number -->
                                    <div class="form-group full-width" :class="{ 'has-error': errors.card_number }">
                                        <label>
                                            {{ locale === 'lv' ? 'Kartes numurs *' : 'Card Number *' }}
                                        </label>
                                        <input
                                            v-model="form.card_number"
                                            type="text"
                                            placeholder="1234 5678 9012 3456"
                                            maxlength="19"
                                            @input="formatCardNumber"
                                            @focus="clearError('card_number')"
                                            @blur="validateField('card_number')"
                                        >
                                        <span v-if="errors.card_number" class="error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ errors.card_number }}
                                        </span>
                                    </div>

                                    <!-- Card Name -->
                                    <div class="form-group full-width" :class="{ 'has-error': errors.card_name }">
                                        <label>
                                            {{ locale === 'lv' ? 'Vārds uz kartes *' : 'Name on Card *' }}
                                        </label>
                                        <input
                                            v-model="form.card_name"
                                            type="text"
                                            placeholder="JĀNIS BĒRZIŅŠ"
                                            @focus="clearError('card_name')"
                                            @blur="validateField('card_name')"
                                        >
                                        <span v-if="errors.card_name" class="error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ errors.card_name }}
                                        </span>
                                    </div>

                                    <div class="card-row">
                                        <!-- Expiry Date -->
                                        <div class="form-group" :class="{ 'has-error': errors.card_expiry }">
                                            <label>
                                                {{ locale === 'lv' ? 'Derīguma termiņš *' : 'Expiry Date *' }}
                                            </label>
                                            <input
                                                v-model="form.card_expiry"
                                                type="text"
                                                placeholder="MM/YY"
                                                maxlength="5"
                                                @input="formatExpiry"
                                                @focus="clearError('card_expiry')"
                                                @blur="validateField('card_expiry')"
                                            >
                                            <span v-if="errors.card_expiry" class="error">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ errors.card_expiry }}
                                            </span>
                                        </div>

                                        <!-- CVV -->
                                        <div class="form-group" :class="{ 'has-error': errors.card_cvv }">
                                            <label>
                                                CVV *
                                            </label>
                                            <input
                                                v-model="form.card_cvv"
                                                type="text"
                                                placeholder="123"
                                                maxlength="4"
                                                @input="formatCVV"
                                                @focus="clearError('card_cvv')"
                                                @blur="validateField('card_cvv')"
                                            >
                                            <span v-if="errors.card_cvv" class="error">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ errors.card_cvv }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Security Note -->
                                    <div class="security-note">
                                        <i class="fas fa-lock"></i>
                                        <span>
                                            {{ locale === 'lv'
                                            ? 'Jūsu maksājuma informācija ir droša un šifrēta'
                                            : 'Your payment information is secure and encrypted'
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>  <!-- .checkout-section -->

                        <!-- Additional Notes -->
                        <div class="checkout-section">
                            <h2 class="section-title">
                                <i class="fas fa-comment"></i>
                                {{ locale === 'lv' ? 'Papildus piezīmes' : 'Additional Notes' }}
                                <span class="optional">({{ locale === 'lv' ? 'neobligāti' : 'optional' }})</span>
                            </h2>
                            <textarea
                                v-model="form.notes"
                                :placeholder="locale === 'lv' ? 'Piegādāt darba laikā...' : 'Deliver during business hours...'"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="order-summary-sticky">
                        <div class="order-summary">
                            <h2 class="summary-title">
                                {{ locale === 'lv' ? 'Pasūtījuma kopsavilkums' : 'Order Summary' }}
                            </h2>

                            <!-- Products List -->
                            <div class="summary-products">
                                <div v-for="item in cart.items" :key="item.id" class="summary-product">
                                    <img :src="getProductImage(item.product)" :alt="getProductName(item.product)">
                                    <div class="product-info">
                                        <p class="product-name">{{ getProductName(item.product) }}</p>
                                        <p class="product-quantity">{{ locale === 'lv' ? 'Daudzums' : 'Qty' }}: {{ item.quantity }}</p>
                                    </div>
                                    <p class="product-price">{{ formatPrice(item.total) }}€</p>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="summary-divider"></div>

                            <div class="summary-row">
                                <span>{{ locale === 'lv' ? 'Starpsumma' : 'Subtotal' }}</span>
                                <span>{{ formatPrice(cart.total_amount) }}€</span>
                            </div>

                            <div class="summary-row">
                                <span>{{ locale === 'lv' ? 'PVN (21%)' : 'VAT (21%)' }}</span>
                                <span>{{ formatPrice(cart.total_amount * 0.21) }}€</span>
                            </div>

                            <div class="summary-row">
                                <span>{{ locale === 'lv' ? 'Piegāde' : 'Shipping' }}</span>
                                <span>{{ formatPrice(shippingCost) }}€</span>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-row summary-total">
                                <span>{{ locale === 'lv' ? 'Kopā' : 'Total' }}</span>
                                <span>{{ formatPrice(totalWithShipping) }}€</span>
                            </div>

                            <!-- Place Order Button -->
                            <button
                                @click="placeOrder"
                                class="place-order-btn"
                                :disabled="isSubmitting"
                            >
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
.checkout-page {
    background: #f9fafb;
    min-height: 100vh;
    padding: 40px 20px;
}

.checkout-container {
    max-width: 1400px;
    margin: 0 auto;
}

.checkout-header {
    margin-bottom: 40px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 16px;
    transition: color 0.2s;
}

.back-link:hover {
    color: #dc2626;
}

.checkout-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.checkout-content {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 40px;
}

.checkout-forms {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.checkout-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.checkout-section.has-errors {
    border: 2px solid #fca5a5;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    color: #dc2626;
}

.error-badge {
    margin-left: auto;
    padding: 4px 12px;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.optional {
    font-size: 14px;
    font-weight: 400;
    color: #9ca3af;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.3s;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group.has-error input,
.form-group.has-error select {
    border-color: #ef4444;
    background-color: #fef2f2;
}

.form-group.has-error label {
    color: #dc2626;
}

.form-group label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    transition: color 0.3s;
}

.form-group input,
.form-group select,
textarea {
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus,
textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.error {
    color: #dc2626;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
    animation: shake 0.3s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

textarea {
    width: 100%;
    resize: vertical;
}

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-option {
    display: flex;
    align-items: center;
    padding: 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.payment-option:hover {
    border-color: #dc2626;
    background: #fef2f2;
}

.payment-option input[type="radio"] {
    margin-right: 12px;
}

.payment-option input[type="radio"]:checked + .payment-content {
    color: #dc2626;
}

.payment-content {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.payment-content i {
    font-size: 20px;
}

/* Order Summary */
.order-summary-sticky {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.order-summary {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.summary-title {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px 0;
}

.summary-products {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.summary-product {
    display: grid;
    grid-template-columns: 60px 1fr auto;
    gap: 12px;
    align-items: center;
}

.summary-product img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-name {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    margin: 0;
}

.product-quantity {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.product-price {
    font-size: 14px;
    font-weight: 600;
    color: #dc2626;
    margin: 0;
}

.summary-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 16px 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    color: #6b7280;
}

.summary-total {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    padding: 16px 0 0 0;
}

.place-order-btn {
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

.place-order-btn:hover:not(:disabled) {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.place-order-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.secure-note {
    text-align: center;
    font-size: 12px;
    color: #6b7280;
    margin: 12px 0 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.secure-note i {
    color: #10b981;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .checkout-content {
        grid-template-columns: 1fr;
    }

    .order-summary-sticky {
        position: static;
    }
}

@media (max-width: 768px) {
    .checkout-title {
        font-size: 24px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}

/* ========== EMAIL READONLY FIELD ========== */
.field-note {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.field-note i {
    color: #3b82f6;
}

/* ========== CARD DETAILS SECTION ========== */
.card-details {
    margin-top: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
}

.card-details-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-details-title::before {
    content: "💳";
    font-size: 1.25rem;
}

.card-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.card-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 640px) {
    .card-row {
        grid-template-columns: 1fr;
    }
}

/* Security Note */
.security-note {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    color: #065f46;
    margin-top: 0.5rem;
}

.security-note i {
    color: #10b981;
    font-size: 1rem;
}
</style>
