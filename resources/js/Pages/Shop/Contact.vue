<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const { t } = useI18n();

defineOptions({
    layout: ShopLayout
});

// Form
const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    order_number: '',
    message: '',
});

// Toast state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const showToastNotification = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 4000);
};

const submitForm = () => {
    form.post('/shop/contact', {
        onSuccess: () => {
            showToastNotification(t('shop_contact.success'), 'success');
            form.reset();
        },
        onError: () => {
            showToastNotification(t('shop_contact.error'), 'error');
        },
    });
};

// Contact info
const contactInfo = [
    { icon: 'fas fa-envelope', label: 'E-pasts', value: 'info@ralphmania.lv', href: 'mailto:info@ralphmania.lv' },
    { icon: 'fas fa-phone', label: 'Telefons', value: '+371 20 000 000', href: 'tel:+37120000000' },
    { icon: 'fas fa-clock', label: 'Darba laiks', value: 'P-Pk: 9:00 - 18:00', href: null },
    { icon: 'fas fa-map-marker-alt', label: 'Adrese', value: 'Rīga, Latvija', href: null },
];

// Subject options
const subjects = [
    { value: 'order', labelKey: 'shop_contact.subjects.order' },
    { value: 'shipping', labelKey: 'shop_contact.subjects.shipping' },
    { value: 'returns', labelKey: 'shop_contact.subjects.returns' },
    { value: 'product', labelKey: 'shop_contact.subjects.product' },
    { value: 'payment', labelKey: 'shop_contact.subjects.payment' },
    { value: 'other', labelKey: 'shop_contact.subjects.other' },
];
</script>

<template>
    <Head :title="t('shop_contact.title')" />

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

                        <form @submit.prevent="submitForm" class="contact-form">
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
                                        :class="{ 'input-error': form.errors.name }"
                                        :placeholder="t('shop_contact.name_placeholder')"
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
                                        :class="{ 'input-error': form.errors.email }"
                                        :placeholder="t('shop_contact.email_placeholder')"
                                        required
                                    >
                                    <span v-if="form.errors.email" class="error-text">{{ form.errors.email }}</span>
                                </div>
                            </div>

                            <!-- Phone & Subject Row -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">{{ t('shop_contact.phone') }}</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="form-input"
                                        :placeholder="t('shop_contact.phone_placeholder')"
                                    >
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
                                            {{ t(subject.labelKey) }}
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
                                    :placeholder="t('shop_contact.order_number_placeholder')"
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
                            <a href="/shop/faq" class="quick-link">
                                <i class="fas fa-question-circle"></i>
                                {{ t('shop_contact.faq_link') }}
                            </a>
                            <a href="/shop/shipping" class="quick-link">
                                <i class="fas fa-truck"></i>
                                {{ t('shop_contact.shipping_link') }}
                            </a>
                            <a href="/shop/returns" class="quick-link">
                                <i class="fas fa-undo"></i>
                                {{ t('shop_contact.returns_link') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Transition name="toast">
            <div v-if="showToast" :class="['toast-notification', `toast-${toastType}`]">
                <div class="toast-icon">
                    <i v-if="toastType === 'success'" class="fas fa-check-circle"></i>
                    <i v-else class="fas fa-exclamation-circle"></i>
                </div>
                <div class="toast-message">{{ toastMessage }}</div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.contact-page {
    min-height: 100vh;
    background: #f8fafc;
}

/* Hero */
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

/* Container */
.contact-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2rem;
}

/* Grid */
.contact-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 2rem;
}

/* Form Section */
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

/* Form Styles */
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

/* Info Section */
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

/* Response Card */
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

/* Quick Links */
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

/* Toast */
.toast-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    min-width: 300px;
    padding: 16px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 9999;
    border-left: 4px solid;
}

.toast-success {
    border-left-color: #10b981;
    background: linear-gradient(to right, #ecfdf5, white);
}

.toast-error {
    border-left-color: #ef4444;
    background: linear-gradient(to right, #fef2f2, white);
}

.toast-icon {
    font-size: 24px;
}

.toast-success .toast-icon {
    color: #10b981;
}

.toast-error .toast-icon {
    color: #ef4444;
}

.toast-message {
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(100px);
}

/* Responsive */
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

    .toast-notification {
        right: 10px;
        left: 10px;
        min-width: auto;
    }
}
</style>
