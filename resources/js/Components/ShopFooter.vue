<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const currentYear = new Date().getFullYear();

const subscribe = () => {
    // Newsletter subscription logic will be implemented later
    alert(t('footer.subscribe_success'));
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
                            <Link href="/shop/category/apparel" class="footer-nav-link">{{ t('footer.apparel') }}</Link>
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
                            type="email"
                            :placeholder="t('footer.email_placeholder')"
                            class="newsletter-input"
                            required
                        >
                        <button type="submit" class="newsletter-submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

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
    grid-template-columns: 1.5fr 2fr 1fr;
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
    grid-template-columns: repeat(3, 1fr);
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
    margin-bottom: 1.5rem;
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

.newsletter-submit {
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    border-radius: 0.5rem;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
}

.newsletter-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
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

/* Responsive */
@media (max-width: 1024px) {
    .shop-footer-content {
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }

    .shop-footer-links {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
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
}
</style>
