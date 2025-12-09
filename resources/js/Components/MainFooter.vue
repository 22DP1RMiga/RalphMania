<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="main-footer">
        <div class="footer-container">
            <!-- Footer Top -->
            <div class="footer-top">
                <!-- About Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">{{ t('footer.about_title') }}</h3>
                    <p class="footer-description">
                        {{ t('footer.about_description') }}
                    </p>
                    <div class="footer-social">
                        <a href="https://www.youtube.com/@RoltonsLV" target="_blank" class="social-link social-youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.tiktok.com/@realroltonslv" target="_blank" class="social-link social-tiktok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="https://www.instagram.com/ralfsmigals/" target="_blank" class="social-link social-instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://x.com/RealRoltonsLV" target="_blank" class="social-link social-x-twitter">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/ralfs.migals.3/" target="_blank" class="social-link social-facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">{{ t('footer.quick_links') }}</h3>
                    <nav class="footer-links">
                        <Link href="/" class="footer-link">{{ t('nav.home') }}</Link>
                        <Link href="/about" class="footer-link">{{ t('nav.about') }}</Link>
                        <Link href="/contact" class="footer-link">{{ t('nav.contact') }}</Link>
                        <Link href="/shop" class="footer-link">{{ t('nav.shop') }}</Link>
                        <Link href="/content" class="footer-link">{{ t('nav.content') }}</Link>
                    </nav>
                </div>

                <!-- Categories Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">{{ t('footer.categories') }}</h3>
                    <nav class="footer-links">
                        <Link href="/content?type=video" class="footer-link">{{ t('footer.video') }}</Link>
                        <Link href="/content?type=blog" class="footer-link">{{ t('footer.blog') }}</Link>
                        <Link href="/shop/category/apparel" class="footer-link">{{ t('footer.apparel') }}</Link>
                        <Link href="/shop/category/souvenirs" class="footer-link">{{ t('footer.souvenirs') }}</Link>
                        <Link href="/shop/category/gift-cards" class="footer-link">{{ t('footer.gift_cards') }}</Link>
                    </nav>
                </div>

                <!-- Contact Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">{{ t('footer.contact_info') }}</h3>
                    <div class="footer-contact">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:info@ralphmania.lv" class="contact-link">info@ralphmania.lv</a>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:+37120000000" class="contact-link">+371 20 000 000</a>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ t('footer.location') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>{{ t('footer.copyright', { year: currentYear }) }}</p>
                </div>
                <div class="footer-legal">
                    <Link href="/privacy" class="legal-link">{{ t('footer.privacy_policy') }}</Link>
                    <span class="legal-divider">|</span>
                    <Link href="/terms" class="legal-link">{{ t('footer.terms_of_use') }}</Link>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* ========== MAIN FOOTER ========== */
.main-footer {
    background-color: #1f2937;
    color: #e5e7eb;
    padding: 3rem 2rem 1.5rem;
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Footer Top */
.footer-top {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 3rem;
    margin-bottom: 3rem;
}

.footer-column {
    display: flex;
    flex-direction: column;
}

.footer-heading {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1.25rem;
}

.footer-description {
    color: #9ca3af;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

/* Social Links */
.footer-social {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s;
    color: white;
}

.social-link:hover {
    transform: translateY(-3px);
}

.social-youtube {
    background: #FF0000;
}

.social-youtube:hover {
    box-shadow: 0 4px 12px rgba(255, 0, 0, 0.4);
}

.social-tiktok {
    background: #000000;
}

.social-tiktok:hover {
    box-shadow: 0 4px 12px rgba(255, 0, 80, 0.4);
}

.social-instagram {
    background: linear-gradient(135deg, #405DE6 0%, #E1306C 50%, #FCAF45 100%);
}

.social-instagram:hover {
    box-shadow: 0 4px 12px rgba(225, 48, 108, 0.4);
}

.social-x-twitter {
    background: #181a1c;
}

.social-x-twitter:hover {
    background-color: #222629;
}

.social-facebook {
    background: #1877F2;
}

.social-facebook:hover {
    box-shadow: 0 4px 12px rgba(24, 119, 242, 0.4);
}

/* Footer Links */
.footer-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.footer-link {
    color: #9ca3af;
    transition: all 0.2s;
    text-decoration: none;
}

.footer-link:hover {
    color: #dc2626;
    padding-left: 0.5rem;
}

/* Contact Info */
.footer-contact {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #9ca3af;
}

.contact-item i {
    color: #dc2626;
    width: 1.25rem;
}

.contact-link {
    color: #9ca3af;
    transition: color 0.2s;
    text-decoration: none;
}

.contact-link:hover {
    color: #dc2626;
}

/* Footer Bottom */
.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 2rem;
    border-top: 1px solid #374151;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-copyright {
    color: #9ca3af;
}

.footer-legal {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.legal-link {
    color: #9ca3af;
    transition: color 0.2s;
    text-decoration: none;
}

.legal-link:hover {
    color: #dc2626;
}

.legal-divider {
    color: #4b5563;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-top {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }

    .footer-social {
        justify-content: center;
    }
}
</style>
