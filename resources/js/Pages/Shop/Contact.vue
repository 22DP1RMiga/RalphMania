<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const { t } = useI18n();

// Get authenticated user data
const user = computed(() => page.props.auth?.user);

// Country codes list
const countryCodes = [
    { code: '+371', country: 'LV', flag: '🇱🇻', name: t('country.latvia') },
    { code: '+370', country: 'LT', flag: '🇱🇹', name: t('country.lithuania') },
    { code: '+372', country: 'EE', flag: '🇪🇪', name: t('country.estonia') },
    { code: '+7', country: 'RU', flag: '🇷🇺', name: t('country.russia') },
    { code: '+380', country: 'UA', flag: '🇺🇦', name: t('country.ukraine') },
    { code: '+48', country: 'PL', flag: '🇵🇱', name: t('country.poland') },
    { code: '+49', country: 'DE', flag: '🇩🇪', name: t('country.germany') },
    { code: '+44', country: 'GB', flag: '🇬🇧', name: t('country.united_kingdom') },
    { code: '+1', country: 'US', flag: '🇺🇸', name: t('country.united_states') },
    { code: '+33', country: 'FR', flag: '🇫🇷', name: t('country.france') },
    { code: '+34', country: 'ES', flag: '🇪🇸', name: t('country.spain') },
    { code: '+39', country: 'IT', flag: '🇮🇹', name: t('country.italy') },
    { code: '+46', country: 'SE', flag: '🇸🇪', name: t('country.sweden') },
    { code: '+47', country: 'NO', flag: '🇳🇴', name: t('country.norway') },
    { code: '+45', country: 'DK', flag: '🇩🇰', name: t('country.denmark') },
    { code: '+358', country: 'FI', flag: '🇫🇮', name: t('country.finland') },
    { code: '+31', country: 'NL', flag: '🇳🇱', name: t('country.netherlands') },
    { code: '+32', country: 'BE', flag: '🇧🇪', name: t('country.belgium') },
    { code: '+43', country: 'AT', flag: '🇦🇹', name: t('country.austria') },
    { code: '+41', country: 'CH', flag: '🇨🇭', name: t('country.switzerland') },
];

const showCountryDropdown = ref(false);
const selectedCountry = ref(countryCodes[0]);

// Toast notification state
const toast = ref({
    show: false,
    message: '',
    type: 'success',
});

// Subject options
const subjects = computed(() => [
    { value: 'order', label: t('shop_contact.subjects.order') },
    { value: 'shipping', label: t('shop_contact.subjects.shipping') },
    { value: 'returns', label: t('shop_contact.subjects.returns') },
    { value: 'product', label: t('shop_contact.subjects.product') },
    { value: 'payment', label: t('shop_contact.subjects.payment') },
    { value: 'other', label: t('shop_contact.subjects.other') },
]);

// Form
const form = useForm({
    name: '',
    email: '',
    country_code: '+371',
    phone: '',
    subject: '',
    order_number: '',
    message: '',
});

// Aizpilda formu ar lietotāja datiem (ja pieteicies)
onMounted(() => {
    if (user.value) {
        form.name  = user.value.username || user.value.first_name || '';
        form.email = user.value.email || '';
    }
});

// Select country code
const selectCountry = (country) => {
    selectedCountry.value = country;
    form.country_code = country.code;
    showCountryDropdown.value = false;
};

// Show toast notification
const showToast = (message, type = 'success') => {
    toast.value = {
        show: true,
        message,
        type,
    };
};

// Close toast
const closeToast = () => {
    toast.value.show = false;
};

// Submit form
const submit = () => {
    form.post('/shop/contact', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('subject', 'message', 'phone', 'order_number');
            showToast(t('shop_contact.success'), 'success');
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            showToast(firstError || t('shop_contact.error'), 'error');
        },
    });
};

// Contact info
const contactInfo = computed(() => [
    { icon: 'fas fa-envelope', label: t('shop_contact.email'), value: 'ralphmania.roltonslv@gmail.com', href: 'mailto:ralphmania.roltonslv@gmail.com' },
    { icon: 'fas fa-phone', label: t('shop_contact.phone'), value: '+371 20 000 000', href: 'tel:+37120000000' },
    { icon: 'fas fa-clock', label: t('shop_contact.worktime'), value: t('shop_contact.working_hours'), href: null },
    { icon: 'fas fa-map-marker-alt', label: t('shop_contact.address'), value: t('shop_contact.riga_latvia'), href: null },
]);
</script>

<template>
    <ShopLayout>
        <Head title="Sazināties ar mums" />

        <!-- Toast Notification -->
        <ToastNotification
            :show="toast.show"
            :message="toast.message"
            :type="toast.type"
            @close="closeToast"
        />

        <div class="contact-page">
            <!-- Hero Section -->
            <div class="contact-hero">
                <div class="hero-content">
                    <div class="hero-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h1 class="hero-title">{{ t('shop_contact.title') }}</h1>
                    <p class="hero-subtitle">{{ t('shop_contact.subtitle') }}</p>
                </div>
            </div>

            <div class="contact-container">
                <div class="contact-grid">
                    <!-- Contact Form -->
                    <div class="form-section">
                        <div class="form-card">
                            <h2 class="form-title">
                                <i class="fas fa-paper-plane"></i>
                                {{ t('shop_contact.form_title') }}
                            </h2>

                            <!-- Auth Notice (if logged in) -->
                            <div v-if="user" class="auth-notice">
                                <i class="fas fa-user-check"></i>
                                <span>{{ t('shop_contact.youAreLoggedInAs') }} <strong>{{ user.username }}</strong></span>
                            </div>

                            <form @submit.prevent="submit" class="contact-form">
                                <!-- Name & Email Row -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">
                                            {{ t('shop_contact.name') }} <span class="required">*</span>
                                        </label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            class="form-input"
                                            :class="{ 'input-error': form.errors.name, 'readonly': user }"
                                            placeholder="Jūsu vārds"
                                            :readonly="!!user"
                                            required
                                        >
                                        <span v-if="form.errors.name" class="error-text">{{ form.errors.name }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">
                                            {{ t('shop_contact.email') }} <span class="required">*</span>
                                        </label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            class="form-input"
                                            :class="{ 'input-error': form.errors.email, 'readonly': user }"
                                            placeholder="jusu@epasts.lv"
                                            :readonly="!!user"
                                            required
                                        >
                                        <span v-if="form.errors.email" class="error-text">{{ form.errors.email }}</span>
                                    </div>
                                </div>

                                <!-- Phone with Country Code & Subject -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">{{ t('shop_contact.phone') }} <span class="required">*</span></label>
                                        <div class="phone-input-wrapper">
                                            <!-- Country Code Selector -->
                                            <div class="country-selector" @click.stop>
                                                <button
                                                    type="button"
                                                    class="country-button"
                                                    @click="showCountryDropdown = !showCountryDropdown"
                                                >
                                                    <span class="country-flag">{{ selectedCountry.flag }}</span>
                                                    <span class="country-code-text">{{ selectedCountry.code }}</span>
                                                    <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': showCountryDropdown }"></i>
                                                </button>

                                                <!-- Dropdown -->
                                                <div v-if="showCountryDropdown" class="country-dropdown">
                                                    <div
                                                        v-for="country in countryCodes"
                                                        :key="country.code"
                                                        class="country-option"
                                                        :class="{ 'selected': selectedCountry.code === country.code }"
                                                        @click="selectCountry(country)"
                                                    >
                                                        <span class="country-flag">{{ country.flag }}</span>
                                                        <span class="country-name">{{ country.name }}</span>
                                                        <span class="country-code-text">{{ country.code }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Phone Number Input -->
                                            <input
                                                v-model="form.phone"
                                                type="tel"
                                                class="form-input phone-input"
                                                placeholder="20 000 000"
                                                @focus="showCountryDropdown = false"
                                            >
                                        </div>
                                        <span v-if="form.errors.phone" class="error-text">{{ form.errors.phone }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">
                                            {{ t('shop_contact.subject') }} <span class="required">*</span>
                                        </label>
                                        <select
                                            v-model="form.subject"
                                            class="form-input"
                                            :class="{ 'input-error': form.errors.subject }"
                                            required
                                        >
                                            <option value="">{{ t('shop_contact.select_subject') }}</option>
                                            <option v-for="subject in subjects" :key="subject.value" :value="subject.value">
                                                {{ subject.label }}
                                            </option>
                                        </select>
                                        <span v-if="form.errors.subject" class="error-text">{{ form.errors.subject }}</span>
                                    </div>
                                </div>

                                <!-- Order Number (conditional) -->
                                <div v-if="form.subject === 'order' || form.subject === 'shipping' || form.subject === 'returns'" class="form-group">
                                    <label class="form-label">{{ t('shop_contact.order_number') }}</label>
                                    <input
                                        v-model="form.order_number"
                                        type="text"
                                        class="form-input"
                                        placeholder="RM-2025-XXXXX"
                                    >
                                </div>

                                <!-- Message -->
                                <div class="form-group">
                                    <label class="form-label">
                                        {{ t('shop_contact.message') }} <span class="required">*</span>
                                    </label>
                                    <textarea
                                        v-model="form.message"
                                        class="form-textarea"
                                        :class="{ 'input-error': form.errors.message }"
                                        :placeholder="t('shop_contact.message_placeholder')"
                                        rows="5"
                                        required
                                    ></textarea>
                                    <span v-if="form.errors.message" class="error-text">{{ form.errors.message }}</span>
                                </div>

                                <!-- Submit Button -->
                                <button
                                    type="submit"
                                    class="btn-submit"
                                    :disabled="form.processing"
                                >
                                    <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                                    <i v-else class="fas fa-paper-plane"></i>
                                    {{ form.processing ? t('shop_contact.sending') : t('shop_contact.send') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Contact Info Sidebar -->
                    <div class="info-section">
                        <!-- Contact Cards -->
                        <div class="info-card">
                            <h3 class="info-title">{{ t('shop_contact.contact_info') }}</h3>
                            <div class="info-list">
                                <div v-for="info in contactInfo" :key="info.label" class="info-item">
                                    <div class="info-icon">
                                        <i :class="info.icon"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">{{ info.label }}</span>
                                        <a v-if="info.href" :href="info.href" class="info-value link">
                                            {{ info.value }}
                                        </a>
                                        <span v-else class="info-value">{{ info.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Response Time -->
                        <div class="response-card">
                            <div class="response-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h4>{{ t('shop_contact.response_time') }}</h4>
                            <p>{{ t('shop_contact.response_time_text') }}</p>
                        </div>

                        <!-- Quick Links -->
                        <div class="quick-links-card">
                            <h4>{{ t('shop_contact.quick_links') }}</h4>
                            <div class="quick-links">
                                <Link href="/shop/faq" class="quick-link">
                                    <i class="fas fa-question-circle"></i>
                                    {{ t('shop_contact.faq_link') }}
                                </Link>
                                <Link href="/shop/shipping" class="quick-link">
                                    <i class="fas fa-truck"></i>
                                    {{ t('shop_contact.shipping_link') }}
                                </Link>
                                <Link href="/shop/returns" class="quick-link">
                                    <i class="fas fa-undo"></i>
                                    {{ t('shop_contact.returns_link') }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.contact-page {
    min-height: 100vh;
    background: #f8fafc;
}

.contact-hero {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    padding: 4rem 2rem;
    text-align: center;
    color: white;
}

.hero-icon {
    width: 5rem;
    height: 5rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.hero-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
}

.contact-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2rem;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 2rem;
}

.form-card {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.form-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1.5rem;
}

.form-title i {
    color: #dc2626;
}

.auth-notice {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 1px solid #10b981;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    color: #065f46;
    font-size: 0.9rem;
}

.auth-notice i {
    color: #10b981;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
}

.required {
    color: #dc2626;
}

.form-input,
.form-textarea {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s;
    width: 100%;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.readonly {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

.form-input.input-error,
.form-textarea.input-error {
    border-color: #ef4444;
}

.error-text {
    font-size: 0.75rem;
    color: #ef4444;
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.phone-input-wrapper {
    display: flex;
    gap: 0;
}

.country-selector {
    position: relative;
}

.country-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
    border-right: none;
    border-radius: 0.5rem 0 0 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    height: 100%;
}

.country-button:hover {
    background: #e5e7eb;
}

.country-flag {
    font-size: 1.25rem;
}

.country-code-text {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.dropdown-icon {
    font-size: 0.75rem;
    color: #6b7280;
    transition: transform 0.2s;
}

.dropdown-icon.rotated {
    transform: rotate(180deg);
}

.country-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 280px;
    max-height: 300px;
    overflow-y: auto;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 100;
    margin-top: 4px;
}

.country-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background 0.2s;
}

.country-option:hover {
    background: #f3f4f6;
}

.country-option.selected {
    background: #fef2f2;
}

.country-option .country-name {
    flex: 1;
    color: #374151;
}

.phone-input {
    flex: 1;
    border-radius: 0 0.5rem 0.5rem 0 !important;
}

.btn-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    border-radius: 0.75rem;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.info-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.info-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1.25rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.info-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc2626;
    flex-shrink: 0;
}

.info-content {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.75rem;
    color: #9ca3af;
    font-weight: 500;
}

.info-value {
    font-weight: 600;
    color: #111827;
}

.info-value.link {
    color: #dc2626;
    text-decoration: none;
    transition: color 0.2s;
}

.info-value.link:hover {
    color: #b91c1c;
}

.response-card {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 1px solid #10b981;
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
}

.response-icon {
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    color: white;
    font-size: 1.25rem;
}

.response-card h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #065f46;
    margin: 0 0 0.25rem;
}

.response-card p {
    font-size: 0.875rem;
    color: #047857;
    margin: 0;
}

.quick-links-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.quick-links-card h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1rem;
}

.quick-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.quick-link i {
    color: #dc2626;
}

.quick-link:hover {
    background: #fee2e2;
    color: #dc2626;
}

@media (max-width: 900px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .contact-hero {
        padding: 3rem 1.5rem;
    }

    .hero-title {
        font-size: 2rem;
    }

    .contact-container {
        padding: 1.5rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}

/* ── AUTH SIENA (nepieteicies lietotājiem) ── */
.auth-wall {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    padding: 3rem 1.5rem;
    background: #f9fafb;
}
.auth-wall-inner {
    text-align: center;
    max-width: 420px;
    background: white;
    border-radius: 1.25rem;
    padding: 3rem 2rem;
    box-shadow: 0 4px 24px rgba(0,0,0,.10);
}
.auth-wall-icon {
    font-size: 3rem;
    color: #dc2626;
    margin-bottom: 1.25rem;
}
.auth-wall-title {
    font-size: 1.625rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: .875rem;
}
.auth-wall-text {
    font-size: 1rem;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 2rem;
}
.auth-wall-btns {
    display: flex;
    gap: .875rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.auth-btn-login {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .75rem 1.75rem;
    background: #dc2626; color: white;
    border-radius: .625rem; font-weight: 700; font-size: 1rem;
    text-decoration: none; transition: all .25s;
}
.auth-btn-login:hover { background: #b91c1c; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(220,38,38,.3); }
.auth-btn-register {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .75rem 1.75rem;
    background: white; color: #374151;
    border: 2px solid #e5e7eb;
    border-radius: .625rem; font-weight: 600; font-size: 1rem;
    text-decoration: none; transition: all .25s;
}
.auth-btn-register:hover { border-color: #dc2626; color: #dc2626; background: #fef2f2; }
.auth-back-link {
    display: inline-flex; align-items: center; gap: .4rem;
    color: #9ca3af; font-size: .9375rem; text-decoration: none;
    transition: color .2s;
}
.auth-back-link:hover { color: #dc2626; }

</style>
