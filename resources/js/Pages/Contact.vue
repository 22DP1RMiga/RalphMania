<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

// Get authenticated user data
const user = computed(() => page.props.auth?.user);

// Country codes list
const countryCodes = [
    { code: '+371', country: 'LV', flag: '🇱🇻', name: 'Latvija' },
    { code: '+370', country: 'LT', flag: '🇱🇹', name: 'Lietuva' },
    { code: '+372', country: 'EE', flag: '🇪🇪', name: 'Igaunija' },
    { code: '+7', country: 'RU', flag: '🇷🇺', name: 'Krievija' },
    { code: '+380', country: 'UA', flag: '🇺🇦', name: 'Ukraina' },
    { code: '+48', country: 'PL', flag: '🇵🇱', name: 'Polija' },
    { code: '+49', country: 'DE', flag: '🇩🇪', name: 'Vācija' },
    { code: '+44', country: 'GB', flag: '🇬🇧', name: 'Apvienotā Karaliste' },
    { code: '+1', country: 'US', flag: '🇺🇸', name: 'ASV' },
    { code: '+33', country: 'FR', flag: '🇫🇷', name: 'Francija' },
    { code: '+34', country: 'ES', flag: '🇪🇸', name: 'Spānija' },
    { code: '+39', country: 'IT', flag: '🇮🇹', name: 'Itālija' },
    { code: '+46', country: 'SE', flag: '🇸🇪', name: 'Zviedrija' },
    { code: '+47', country: 'NO', flag: '🇳🇴', name: 'Norvēģija' },
    { code: '+45', country: 'DK', flag: '🇩🇰', name: 'Dānija' },
    { code: '+358', country: 'FI', flag: '🇫🇮', name: 'Somija' },
    { code: '+31', country: 'NL', flag: '🇳🇱', name: 'Nīderlande' },
    { code: '+32', country: 'BE', flag: '🇧🇪', name: 'Beļģija' },
    { code: '+43', country: 'AT', flag: '🇦🇹', name: 'Austrija' },
    { code: '+41', country: 'CH', flag: '🇨🇭', name: 'Šveice' },
];

const showCountryDropdown = ref(false);
const selectedCountry = ref(countryCodes[0]); // Default to Latvia

// Toast notification state
const toast = ref({
    show: false,
    message: '',
    type: 'success',
});

// Form with pre-filled user data
const form = useForm({
    username: '',
    email: '',
    country_code: '+371',
    phone: '',
    subject: '',
    message: '',
});

// Pre-fill form with user data on mount
onMounted(() => {
    if (user.value) {
        form.username = user.value.username || '';
        form.email = user.value.email || '';
        // If user has phone saved in profile, could use it here
        // form.phone = user.value.phone || '';
    }
});

// Select country code
const selectCountry = (country) => {
    selectedCountry.value = country;
    form.country_code = country.code;
    showCountryDropdown.value = false;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
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
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('subject', 'message', 'phone');
            showToast('Paldies! Jūsu ziņojums ir veiksmīgi nosūtīts. Mēs ar jums sazināsimies pēc iespējas ātrāk!', 'success');
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            showToast(firstError || 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz.', 'error');
        },
    });
};
</script>

<template>
    <Head :title="$t('nav.contact')" />

    <MainLayout>
        <!-- Toast Notification -->
        <ToastNotification
            :show="toast.show"
            :message="toast.message"
            :type="toast.type"
            @close="closeToast"
        />

        <!-- Loading Overlay -->
        <LoadingSpinner
            v-if="form.processing"
            :fullscreen="true"
            size="lg"
            :text="$t('contact.form.sending') || 'Nosūta...'"
        />

        <!-- Contact Hero -->
        <section class="contact-hero">
            <div class="hero-container">
                <h1 class="hero-title">{{ $t('contact.hero.title') }}</h1>
                <p class="hero-subtitle">{{ $t('contact.hero.subtitle') }}</p>
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

        <!-- Contact Content -->
        <section class="contact-section">
            <div class="section-container">
                <div class="contact-grid">
                    <!-- Contact Form -->
                    <div class="contact-form-container">
                        <h2 class="form-title">{{ $t('contact.form.title') }}</h2>
                        <p class="form-subtitle">{{ $t('contact.form.subtitle') }}</p>

                        <!-- Auth Notice -->
                        <div class="auth-notice">
                            <i class="fas fa-user-check"></i>
                            <span>{{ $t('contact.hero.youAreLoggedInAs') }} <strong>{{ user.username }}</strong></span>
                        </div>

                        <form @submit.prevent="submit" class="contact-form">
                            <!-- Username (readonly - from user) -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    {{ $t('contact.form.username') }}
                                    <span class="required">*</span>
                                </label>
                                <input
                                    id="username"
                                    v-model="form.username"
                                    type="text"
                                    class="form-input readonly"
                                    readonly
                                    required
                                />
                                <span class="form-hint">{{ $t('contact.form.usernameHint') }}</span>
                                <span v-if="form.errors.username" class="form-error">{{ form.errors.username }}</span>
                            </div>

                            <!-- Email (readonly - from user) -->
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    {{ $t('contact.form.email') }}
                                    <span class="required">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-input readonly"
                                    readonly
                                    required
                                />
                                <span class="form-hint">{{ $t('contact.form.emailHint') }}</span>
                                <span v-if="form.errors.email" class="form-error">{{ form.errors.email }}</span>
                            </div>

                            <!-- Phone with Country Code -->
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    {{ $t('contact.form.phone') }}
                                    <span class="required">*</span>
                                </label>
                                <div class="phone-input-wrapper">
                                    <!-- Country Code Selector -->
                                    <div class="country-selector" @click.stop>
                                        <button
                                            type="button"
                                            class="country-button"
                                            @click="showCountryDropdown = !showCountryDropdown"
                                        >
                                            <span class="country-flag">{{ selectedCountry.flag }}</span>
                                            <span class="country-code">{{ selectedCountry.code }}</span>
                                            <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': showCountryDropdown }"></i>
                                        </button>

                                        <!-- Dropdown -->
                                        <div v-if="showCountryDropdown" class="country-dropdown" @click.stop>
                                            <div
                                                v-for="country in countryCodes"
                                                :key="country.code"
                                                class="country-option"
                                                :class="{ 'selected': selectedCountry.code === country.code }"
                                                @click="selectCountry(country)"
                                            >
                                                <span class="country-flag">{{ country.flag }}</span>
                                                <span class="country-name">{{ country.name }}</span>
                                                <span class="country-code">{{ country.code }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Phone Number Input -->
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="form-input phone-input"
                                        placeholder="20 000 000"
                                        required
                                        @focus="showCountryDropdown = false"
                                    />
                                </div>
                                <span v-if="form.errors.phone" class="form-error">{{ form.errors.phone }}</span>
                                <span v-if="form.errors.country_code" class="form-error">{{ form.errors.country_code }}</span>
                            </div>

                            <!-- Subject -->
                            <div class="form-group">
                                <label for="subject" class="form-label">
                                    {{ $t('contact.form.subject') }}
                                    <span class="required">*</span>
                                </label>
                                <input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    class="form-input"
                                    :placeholder="$t('contact.form.subjectPlaceholder') || 'Par ko jūs vēlaties runāt?'"
                                    required
                                />
                                <span v-if="form.errors.subject" class="form-error">{{ form.errors.subject }}</span>
                            </div>

                            <!-- Message -->
                            <div class="form-group">
                                <label for="message" class="form-label">
                                    {{ $t('contact.form.message') }}
                                    <span class="required">*</span>
                                </label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    class="form-textarea"
                                    rows="5"
                                    :placeholder="$t('contact.form.messagePlaceholder') || 'Jūsu ziņojums...'"
                                    required
                                    maxlength="1000"
                                ></textarea>
                                <div class="textarea-footer">
                                    <span v-if="form.errors.message" class="form-error">{{ form.errors.message }}</span>
                                    <span class="char-count" :class="{ 'near-limit': form.message.length > 900 }">
                                        {{ form.message.length }}/1000
                                    </span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="submit-button" :disabled="form.processing">
                                <span v-if="form.processing" class="button-content">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    {{ $t('contact.form.sending') || 'Nosūta...' }}
                                </span>
                                <span v-else class="button-content">
                                    <i class="fas fa-paper-plane"></i>
                                    {{ $t('contact.form.send') || 'Nosūtīt ziņojumu' }}
                                </span>
                            </button>
                        </form>
                    </div>

                    <!-- Contact Info -->
                    <div class="contact-info-container">
                        <h2 class="info-title">{{ $t('contact.info.title') }}</h2>

                        <div class="info-items">
                            <!-- Email -->
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info-content">
                                    <h3 class="info-label">{{ $t('contact.info.email') }}</h3>
                                    <p class="info-value">
                                        <a href="mailto:ralphmania.roltonslv@gmail.com">ralphmania.roltonslv@gmail.com</a>
                                    </p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-content">
                                    <h3 class="info-label">{{ $t('contact.info.phone') }}</h3>
                                    <p class="info-value">+371 20 000 000</p>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h3 class="info-label">{{ $t('contact.info.address') }}</h3>
                                    <p class="info-value">{{ $t('contact.contact_info.place') }}</p>
                                </div>
                            </div>

                            <!-- Response Time -->
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-content">
                                    <h3 class="info-label">{{ $t('contact.info.responseTime') }}</h3>
                                    <p class="info-value">{{ $t('contact.contact_info.responseTimePrompt') }}</p>
                                </div>
                            </div>

                            <!-- Social Media -->
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-share-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h3 class="info-label">{{ $t('contact.info.social') }}</h3>
                                    <div class="social-links">
                                        <a href="https://www.youtube.com/@RoltonsLV" class="social-link youtube" title="YouTube">
                                            <i class="fab fa-youtube"></i>
                                        </a>
                                        <a href="https://www.tiktok.com/@realroltonslv" class="social-link tiktok" title="TikTok">
                                            <i class="fab fa-tiktok"></i>
                                        </a>
                                        <a href="https://www.instagram.com/ralfsmigals/" class="social-link instagram" title="Instagram">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                        <a href="https://x.com/RealRoltonsLV" class="social-link social-x-twitter" title="Twitter/X">
                                            <i class="fab fa-x-twitter"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
/* Hero Section */
.contact-hero {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.contact-hero::before {
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
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.5rem;
    opacity: 0.95;
}

.hero-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}

.hero-wave svg {
    position: relative;
    display: block;
    width: calc(100% + 1.3px);
    height: 120px;
}

/* Contact Section */
.contact-section {
    padding: 4rem 2rem;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 4rem;
}

@media (max-width: 1024px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
}

/* Contact Form */
.contact-form-container {
    background: white;
    padding: 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.form-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.form-subtitle {
    font-size: 1rem;
    color: #6b7280;
    margin-bottom: 1.5rem;
}

/* Auth Notice */
.auth-notice {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 1px solid #10b981;
    border-radius: 0.75rem;
    margin-bottom: 2rem;
    color: #065f46;
    font-size: 0.95rem;
}

.auth-notice i {
    font-size: 1.25rem;
    color: #10b981;
}

.auth-notice strong {
    color: #047857;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.required {
    color: #dc2626;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.readonly {
    background-color: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

.form-hint {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.textarea-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.25rem;
}

.char-count {
    font-size: 0.75rem;
    color: #9ca3af;
}

.char-count.near-limit {
    color: #f59e0b;
    font-weight: 600;
}

.form-error {
    color: #dc2626;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Phone Input with Country Code */
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
    border: 1px solid #d1d5db;
    border-right: none;
    border-radius: 0.5rem 0 0 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
}

.country-button:hover {
    background: #e5e7eb;
}

.country-flag {
    font-size: 1.25rem;
}

.country-code {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.dropdown-icon {
    font-size: 0.75rem;
    color: #6b7280;
    transition: transform 0.2s ease;
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
    transition: background 0.2s ease;
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

.country-option .country-code {
    color: #6b7280;
    font-size: 0.875rem;
}

.phone-input {
    flex: 1;
    border-radius: 0 0.5rem 0.5rem 0 !important;
}

/* Submit Button */
.submit-button {
    width: 100%;
    padding: 1rem;
    background-color: #dc2626;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-button:hover:not(:disabled) {
    background-color: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.submit-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.button-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

/* Contact Info */
.contact-info-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.info-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
}

.info-items {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.info-icon {
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1.125rem;
    color: #111827;
    font-weight: 500;
}

.info-value a {
    color: #dc2626;
    text-decoration: none;
    transition: color 0.2s ease;
}

.info-value a:hover {
    color: #b91c1c;
    text-decoration: underline;
}

.social-links {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.social-link {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    transform: translateY(-2px);
}

.social-link.youtube {
    background-color: #FF0000;
}

.social-link.tiktok {
    background-color: #000000;
}

.social-link.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.social-x-twitter {
    background: #181a1c;
}

.social-x-twitter:hover {
    background-color: #222629;
}

@media (max-width: 640px) {
    .hero-title {
        font-size: 2.5rem;
    }

    .hero-subtitle {
        font-size: 1.125rem;
    }

    .contact-form-container {
        padding: 1.5rem;
    }

    .form-title,
    .info-title {
        font-size: 1.5rem;
    }

    .country-dropdown {
        width: 100%;
        left: 0;
        right: 0;
    }
}
</style>
