<script setup>
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
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
                        Tavs uzticamākais avots ekskluzīviem RalphMania produktiem
                    </p>
                </div>

                <!-- Shop Links -->
                <div class="shop-footer-links">
                    <div class="footer-links-column">
                        <h4 class="footer-links-heading">Veikals</h4>
                        <nav class="footer-nav">
                            <Link href="/shop" class="footer-nav-link">Visi produkti</Link>
                            <Link href="/shop/category/apparel" class="footer-nav-link">Apģērbi</Link>
                            <Link href="/shop/category/souvenirs" class="footer-nav-link">Suvenīri</Link>
                            <Link href="/shop/category/gift-cards" class="footer-nav-link">Dāvanu kartes</Link>
                        </nav>
                    </div>

                    <div class="footer-links-column">
                        <h4 class="footer-links-heading">Klientiem</h4>
                        <nav class="footer-nav">
                            <Link href="/shop/faq" class="footer-nav-link">Biežāk uzdotie jautājumi</Link>
                            <Link href="/shop/shipping" class="footer-nav-link">Piegāde</Link>
                            <Link href="/shop/returns" class="footer-nav-link">Atgriešana</Link>
                            <Link href="/shop/contact" class="footer-nav-link">Kontakti</Link>
                        </nav>
                    </div>

                    <div class="footer-links-column">
                        <h4 class="footer-links-heading">Informācija</h4>
                        <nav class="footer-nav">
                            <Link href="/about" class="footer-nav-link">Par mums</Link>
                            <Link href="/privacy" class="footer-nav-link">Privātuma politika</Link>
                            <Link href="/terms" class="footer-nav-link">Lietošanas noteikumi</Link>
                        </nav>
                    </div>
                </div>

                <!-- Newsletter & Social -->
                <div class="shop-footer-subscribe">
                    <h4 class="footer-links-heading">Seko mums</h4>
                    <p class="subscribe-text">
                        Uzzini pirmais par jauniem produktiem un akcijām!
                    </p>

                    <!-- Newsletter Form -->
                    <form class="newsletter-form" @submit.prevent="subscribe">
                        <input
                            type="email"
                            placeholder="Tavs e-pasts"
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
                <p class="payment-label">Pieņemam:</p>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa payment-icon"></i>
                    <i class="fab fa-cc-mastercard payment-icon"></i>
                    <i class="fab fa-cc-paypal payment-icon"></i>
                    <i class="fab fa-cc-apple-pay payment-icon"></i>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="shop-footer-bottom">
                <p class="footer-copyright">
                    &copy; {{ currentYear }} RalphMania Shop. Visas tiesības aizsargātas.
                </p>
                <Link href="/" class="footer-back-home">
                    <i class="fas fa-home"></i>
                    Atpakaļ uz sākumlapu
                </Link>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* ========== SHOP FOOTER ========== */
.shop-footer {
    background-color: #111827;
    color: #e5e7eb;
}

.shop-footer-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 3rem 1rem 1.5rem;
}

/* ========== FOOTER CONTENT ========== */
.shop-footer-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #374151;
}

@media (min-width: 768px) {
    .shop-footer-content {
        grid-template-columns: 1fr 2fr 1fr;
    }
}

/* Brand Section */
.shop-footer-brand {
    /* Styling */
}

.footer-logo-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.footer-logo {
    height: 3rem;
    width: auto;
}

.footer-brand-name {
    height: 1.75rem;
    width: auto;
}

.footer-tagline {
    font-size: 0.875rem;
    color: #9ca3af;
    line-height: 1.6;
}

/* Shop Links */
.shop-footer-links {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

@media (max-width: 768px) {
    .shop-footer-links {
        grid-template-columns: 1fr;
    }
}

.footer-links-column {
    /* Column styling */
}

.footer-links-heading {
    font-size: 1rem;
    font-weight: 600;
    color: white;
    margin-bottom: 1rem;
}

.footer-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.footer-nav-link {
    color: #d1d5db;
    font-size: 0.875rem;
    transition: color 0.3s ease;
    width: fit-content;
}

.footer-nav-link:hover {
    color: #dc2626;
}

/* Subscribe Section */
.shop-footer-subscribe {
    /* Styling */
}

.subscribe-text {
    font-size: 0.875rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.newsletter-form {
    display: flex;
    margin-bottom: 1.5rem;
}

.newsletter-input {
    flex: 1;
    padding: 0.75rem 1rem;
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.375rem 0 0 0.375rem;
    color: white;
    outline: none;
    transition: border-color 0.3s ease;
}

.newsletter-input:focus {
    border-color: #dc2626;
}

.newsletter-submit {
    padding: 0.75rem 1.5rem;
    background-color: #dc2626;
    color: white;
    border: none;
    border-radius: 0 0.375rem 0.375rem 0;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.newsletter-submit:hover {
    background-color: #b91c1c;
}

/* Social Links */
.shop-footer-social {
    display: flex;
    gap: 0.75rem;
}

.shop-social-link {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.shop-social-youtube {
    background-color: #FF0000;
    color: white;
}

.shop-social-youtube:hover {
    background-color: #CC0000;
    transform: translateY(-2px);
}

.shop-social-instagram {
    background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF);
    color: white;
}

.shop-social-instagram:hover {
    opacity: 0.8;
    transform: translateY(-2px);
}

.shop-social-facebook {
    background-color: #4267B2;
    color: white;
}

.shop-social-facebook:hover {
    background-color: #365899;
    transform: translateY(-2px);
}

/* ========== PAYMENT METHODS ========== */
.shop-footer-payment {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid #374151;
}

.payment-label {
    font-size: 0.875rem;
    color: #9ca3af;
}

.payment-icons {
    display: flex;
    gap: 1rem;
}

.payment-icon {
    font-size: 2rem;
    color: #d1d5db;
}

/* ========== FOOTER BOTTOM ========== */
.shop-footer-bottom {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 2rem;
}

@media (min-width: 768px) {
    .shop-footer-bottom {
        flex-direction: row;
    }
}

.footer-copyright {
    font-size: 0.875rem;
    color: #9ca3af;
}

.footer-back-home {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #d1d5db;
    font-size: 0.875rem;
    transition: color 0.3s ease;
}

.footer-back-home:hover {
    color: #dc2626;
}

.footer-back-home i {
    font-size: 1rem;
}
</style>
