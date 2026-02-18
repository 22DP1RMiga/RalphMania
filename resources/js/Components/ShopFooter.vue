<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t } = useI18n();
const currentYear = new Date().getFullYear();
const page = usePage();

// Newsletter state
const email = ref('');
const isLoading = ref(false);

// Toast state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success'); // success, error, info

const showToastNotification = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;

    // Auto-hide after 4 seconds
    setTimeout(() => {
        showToast.value = false;
    }, 4000);
};

const closeToast = () => {
    showToast.value = false;
};

const subscribe = async () => {
    if (!email.value || !email.value.includes('@')) {
        showToastNotification(t('newsletter.invalid_email'), 'error');
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.post('/newsletter/subscribe', {
            email: email.value
        });

        if (response.data.success) {
            showToastNotification(t('newsletter.success'), 'success');
            email.value = '';
        } else if (response.data.already_subscribed) {
            showToastNotification(t('newsletter.already_subscribed'), 'info');
        }
    } catch (error) {
        console.error('Newsletter subscription error:', error);
        showToastNotification(t('newsletter.error'), 'error');
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <footer class="shop-footer">
        <div class="shop-footer-container">
            <!-- Footer Content -->
            <div class="shop-footer-content">
                <!-- Brand Section -->
                <div class="shop-footer-brand">
                    <Link href="/shop" class="footer-logo-wrapper">
                        <img src="/img/RoltonsLV_Icon.png" alt="RalphMania Logo" class="footer-logo">
                        <img src="/img/name_logo.png" alt="RalphMania" class="footer-brand-name">
                    </Link>
                    <p class="footer-tagline">
                        {{ t('footer.shop_tagline') }}
                    </p>
                </div>

                <!-- Shop Links -->
                <div class="shop-footer-links">
                    <div class="footer-links-column">
                        <h4 class="footer-links-heading">{{ t('footer.shop') }}</h4>
                        <nav class="footer-nav">
                            <Link href="/shop" class="footer-nav-link">{{ t('footer.all_products') }}</Link>
                            <Link href="/shop/category/clothing" class="footer-nav-link">{{ t('footer.clothing') }}</Link>
                            <Link href="/shop/category/souvenirs" class="footer-nav-link">{{ t('footer.souvenirs') }}</Link>
                            <Link href="/shop/category/gift-cards" class="footer-nav-link">{{ t('footer.gift_cards') }}</Link>
                        </nav>
                    </div>

                    <div class="footer-links-column">
                        <h4 class="footer-links-heading">{{ t('footer.customers') }}</h4>
                        <nav class="footer-nav">
                            <Link href="/shop/faq" class="footer-nav-link">{{ t('footer.faq') }}</Link>
                            <Link href="/shop/shipping" class="footer-nav-link">{{ t('footer.shipping') }}</Link>
                            <Link href="/shop/returns" class="footer-nav-link">{{ t('footer.returns') }}</Link>
                            <Link href="/shop/contact" class="footer-nav-link">{{ t('footer.contact') }}</Link>
                        </nav>
                    </div>

                    <div class="footer-links-column">
                        <h4 class="footer-links-heading">{{ t('footer.content') }}</h4>
                        <nav class="footer-nav">
                            <Link href="/content?type=video" class="footer-nav-link">{{ t('footer.videos') }}</Link>
                            <Link href="/content?type=blog" class="footer-nav-link">{{ t('footer.blogs') }}</Link>
                            <Link href="/content?type=news" class="footer-nav-link">{{ t('footer.news') }}</Link>
                            <Link href="/content?type=announcement" class="footer-nav-link">{{ t('footer.announcements') }}</Link>
                        </nav>
                    </div>

                    <div class="footer-links-column">
                        <h4 class="footer-links-heading">{{ t('footer.information') }}</h4>
                        <nav class="footer-nav">
                            <Link href="/about" class="footer-nav-link">{{ t('footer.about_us') }}</Link>
                            <Link href="/privacy" class="footer-nav-link">{{ t('footer.privacy_policy') }}</Link>
                            <Link href="/terms" class="footer-nav-link">{{ t('footer.terms_of_use') }}</Link>
                        </nav>
                    </div>
                </div>

                <!-- Newsletter & Social -->
                <div class="shop-footer-subscribe">
                    <h4 class="footer-links-heading">{{ t('footer.follow_us') }}</h4>
                    <p class="subscribe-text">
                        {{ t('footer.subscribe_description') }}
                    </p>

                    <!-- Newsletter Form -->
                    <form class="newsletter-form" @submit.prevent="subscribe">
                        <input
                            v-model="email"
                            type="email"
                            :placeholder="t('footer.email_placeholder')"
                            class="newsletter-input"
                            :disabled="isLoading"
                            required
                        >
                        <button type="submit" class="newsletter-submit" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-paper-plane"></i>
                        </button>
                    </form>

                    <!-- Subscriber Benefits Badge -->
                    <div class="subscriber-benefits">
                        <i class="fas fa-gift"></i>
                        <span>{{ t('newsletter.benefits_hint') }}</span>
                    </div>

                    <!-- Social Links -->
                    <div class="shop-footer-social">
                        <a href="https://www.youtube.com/@RoltonsLV" target="_blank" class="shop-social-link shop-social-youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.instagram.com/ralfsmigals/" target="_blank" class="shop-social-link shop-social-instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.facebook.com/ralfs.migals.3/" target="_blank" class="shop-social-link shop-social-facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="shop-footer-payment">
                <p class="payment-label">{{ t('footer.payment_methods') }}</p>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa payment-icon"></i>
                    <i class="fab fa-cc-mastercard payment-icon"></i>
                    <i class="fab fa-cc-paypal payment-icon"></i>
                    <i class="fab fa-cc-apple-pay payment-icon"></i>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="shop-footer-bottom">
                <p class="shop-copyright">{{ t('footer.copyright', { year: currentYear }) }}</p>
            </div>
        </div>

        <!-- Toast Notification -->
        <Transition name="toast">
            <div v-if="showToast" :class="['toast-notification', `toast-${toastType}`]" @click="closeToast">
                <div class="toast-icon">
                    <i v-if="toastType === 'success'" class="fas fa-check-circle"></i>
                    <i v-else-if="toastType === 'error'" class="fas fa-exclamation-circle"></i>
                    <i v-else class="fas fa-info-circle"></i>
                </div>
                <div class="toast-message">{{ toastMessage }}</div>
                <button class="toast-close" @click.stop="closeToast">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </Transition>
    </footer>
</template>

<style scoped>
/* ========== SHOP FOOTER ========== */
.shop-footer {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    color: #e5e7eb;
    padding: 3rem 2rem 1.5rem;
}

.shop-footer-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Footer Content */
.shop-footer-content {
    display: grid;
    grid-template-columns: 1.2fr 2.5fr 1.3fr;
    gap: 3rem;
    margin-bottom: 2.5rem;
}

/* Brand Section */
.shop-footer-brand {
    display: flex;
    flex-direction: column;
}

.footer-logo-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    text-decoration: none;
}

.footer-logo {
    width: 3.5rem;
    height: 3.5rem;
    object-fit: contain;
}

.footer-brand-name {
    height: 2rem;
    object-fit: contain;
}

.footer-tagline {
    color: #9ca3af;
    line-height: 1.6;
    font-size: 0.95rem;
}

/* Shop Links */
.shop-footer-links {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

.footer-links-column {
    display: flex;
    flex-direction: column;
}

.footer-links-heading {
    font-size: 1.125rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
}

.footer-nav {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.footer-nav-link {
    color: #9ca3af;
    transition: all 0.2s;
    text-decoration: none;
    font-size: 0.95rem;
}

.footer-nav-link:hover {
    color: #dc2626;
    padding-left: 0.375rem;
}

/* Subscribe Section */
.shop-footer-subscribe {
    display: flex;
    flex-direction: column;
}

.subscribe-text {
    color: #9ca3af;
    font-size: 0.95rem;
    margin-bottom: 1.25rem;
    line-height: 1.5;
}

/* Newsletter Form */
.newsletter-form {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.newsletter-input {
    flex: 1;
    padding: 0.75rem 1rem;
    background: #374151;
    border: 2px solid #4b5563;
    border-radius: 0.5rem;
    color: white;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.newsletter-input:focus {
    outline: none;
    border-color: #dc2626;
    background: #4b5563;
}

.newsletter-input::placeholder {
    color: #9ca3af;
}

.newsletter-input:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.newsletter-submit {
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    border-radius: 0.5rem;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
}

.newsletter-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.newsletter-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Subscriber Benefits */
.subscriber-benefits {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.subscriber-benefits i {
    color: #dc2626;
    font-size: 0.875rem;
}

.subscriber-benefits span {
    color: #fca5a5;
    font-size: 0.8125rem;
}

/* Social Links */
.shop-footer-social {
    display: flex;
    gap: 0.75rem;
}

.shop-social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s;
    color: white;
    font-size: 1.125rem;
}

.shop-social-link:hover {
    transform: translateY(-3px);
}

.shop-social-youtube {
    background: #FF0000;
}

.shop-social-youtube:hover {
    box-shadow: 0 4px 12px rgba(255, 0, 0, 0.4);
}

.shop-social-instagram {
    background: linear-gradient(135deg, #405DE6 0%, #E1306C 50%, #FCAF45 100%);
}

.shop-social-instagram:hover {
    box-shadow: 0 4px 12px rgba(225, 48, 108, 0.4);
}

.shop-social-facebook {
    background: #1877F2;
}

.shop-social-facebook:hover {
    box-shadow: 0 4px 12px rgba(24, 119, 242, 0.4);
}

/* Payment Methods */
.shop-footer-payment {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem 0;
    border-top: 1px solid #374151;
    border-bottom: 1px solid #374151;
    margin-bottom: 1.5rem;
}

.payment-label {
    color: #9ca3af;
    font-weight: 600;
}

.payment-icons {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.payment-icon {
    font-size: 2rem;
    color: #9ca3af;
    transition: color 0.2s;
}

.payment-icon:hover {
    color: white;
}

/* Footer Bottom */
.shop-footer-bottom {
    text-align: center;
}

.shop-copyright {
    color: #9ca3af;
    font-size: 0.95rem;
}

/* ========== TOAST NOTIFICATION ========== */
.toast-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    min-width: 300px;
    max-width: 450px;
    padding: 16px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 9999;
    cursor: pointer;
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

.toast-info {
    border-left-color: #3b82f6;
    background: linear-gradient(to right, #eff6ff, white);
}

.toast-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.toast-success .toast-icon {
    color: #10b981;
}

.toast-error .toast-icon {
    color: #ef4444;
}

.toast-info .toast-icon {
    color: #3b82f6;
}

.toast-message {
    flex: 1;
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.toast-close {
    background: none;
    border: none;
    cursor: pointer;
    color: #6b7280;
    font-size: 16px;
    padding: 4px;
    transition: color 0.2s;
    flex-shrink: 0;
}

.toast-close:hover {
    color: #1f2937;
}

/* Toast Animation */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100px);
}

/* Responsive */
@media (max-width: 1200px) {
    .shop-footer-content {
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }

    .shop-footer-links {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .shop-footer-links {
        grid-template-columns: 1fr;
    }

    .shop-footer-payment {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .payment-icons {
        justify-content: center;
    }

    .shop-footer-social {
        justify-content: center;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .toast-notification {
        top: 70px;
        right: 10px;
        left: 10px;
        min-width: auto;
        max-width: none;
    }
}
</style>
