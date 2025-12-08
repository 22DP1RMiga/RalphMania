<script setup>
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="main-footer">
        <div class="footer-container">
            <!-- Footer Top -->
            <div class="footer-top">
                <!-- About Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">Par RalphMania</h3>
                    <p class="footer-description">
                        Tavs uzticamākais avots unikālam saturam un ekskluzīviem produktiem.
                        Pievienojies mūsu kopienai!
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
                    <h3 class="footer-heading">Ātrās saites</h3>
                    <nav class="footer-links">
                        <Link href="/" class="footer-link">Sākumlapa</Link>
                        <Link href="/about" class="footer-link">Par Mums</Link>
                        <Link href="/contact" class="footer-link">Kontakti</Link>
                        <Link href="/shop" class="footer-link">Veikals</Link>
                        <Link href="/content" class="footer-link">Saturs</Link>
                    </nav>
                </div>

                <!-- Categories Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">Kategorijas</h3>
                    <nav class="footer-links">
                        <Link href="/content?type=video" class="footer-link">Video</Link>
                        <Link href="/content?type=blog" class="footer-link">Blogs</Link>
                        <Link href="/shop/category/apparel" class="footer-link">Apģērbi</Link>
                        <Link href="/shop/category/souvenirs" class="footer-link">Suvenīri</Link>
                        <Link href="/shop/category/gift-cards" class="footer-link">Dāvanu kartes</Link>
                    </nav>
                </div>

                <!-- Contact Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">Kontaktinformācija</h3>
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
                            <span>Rīga, Latvija</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; {{ currentYear }} RalphMania. Visas tiesības aizsargātas.</p>
                </div>
                <div class="footer-legal">
                    <Link href="/privacy" class="legal-link">Privātuma politika</Link>
                    <span class="legal-divider">|</span>
                    <Link href="/terms" class="legal-link">Lietošanas noteikumi</Link>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* ========== MAIN FOOTER ========== */
.main-footer {
    background-color: #1f2937;
    color: #d1d5db;
}

.footer-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 3rem 1rem 1.5rem;
}

/* ========== FOOTER TOP ========== */
.footer-top {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #374151;
}

@media (min-width: 640px) {
    .footer-top {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .footer-top {
        grid-template-columns: repeat(4, 1fr);
    }
}

.footer-column {
    /* Column styling */
}

.footer-heading {
    font-size: 1.125rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
}

.footer-description {
    font-size: 0.875rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

/* Social Links */
.footer-social {
    display: flex;
    gap: 0.75rem;
}

.social-link {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.social-youtube {
    background-color: #FF0000;
    color: white;
}

.social-youtube:hover {
    background-color: #CC0000;
    transform: translateY(-2px);
}

.social-tiktok {
    background-color: #000000;
    color: white;
}

.social-tiktok:hover {
    background-color: #69C9D0;
    transform: translateY(-2px);
}

.social-instagram {
    background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF);
    color: white;
}

.social-instagram:hover {
    opacity: 0.8;
    transform: translateY(-2px);
}

.social-x-twitter {
    background-color: #181a1c;
    color: white;
}

.social-x-twitter:hover {
    background-color: #222629;
    transform: translateY(-2px);
}

.social-facebook {
    background-color: #4267B2;
    color: white;
}

.social-facebook:hover {
    background-color: #365899;
    transform: translateY(-2px);
}

/* Footer Links */
.footer-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.footer-link {
    color: #d1d5db;
    font-size: 0.875rem;
    transition: color 0.3s ease;
    width: fit-content;
}

.footer-link:hover {
    color: #dc2626;
}

/* Contact Items */
.footer-contact {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
}

.contact-item i {
    color: #dc2626;
    width: 1.25rem;
}

.contact-link {
    color: #d1d5db;
    transition: color 0.3s ease;
}

.contact-link:hover {
    color: #dc2626;
}

/* ========== FOOTER BOTTOM ========== */
.footer-bottom {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding-top: 2rem;
    font-size: 0.875rem;
}

@media (min-width: 768px) {
    .footer-bottom {
        flex-direction: row;
        justify-content: space-between;
    }
}

.footer-copyright {
    color: #9ca3af;
}

.footer-legal {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.legal-link {
    color: #d1d5db;
    transition: color 0.3s ease;
}

.legal-link:hover {
    color: #dc2626;
}

.legal-divider {
    color: #4b5563;
}
</style>
