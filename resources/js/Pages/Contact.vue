<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head :title="$t('nav.contact')" />

    <MainLayout>
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

                        <form @submit.prevent="submit" class="contact-form">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name" class="form-label">{{ $t('contact.form.name') }}</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="form-input"
                                    required
                                />
                                <span v-if="form.errors.name" class="form-error">{{ form.errors.name }}</span>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="form-label">{{ $t('contact.form.email') }}</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-input"
                                    required
                                />
                                <span v-if="form.errors.email" class="form-error">{{ form.errors.email }}</span>
                            </div>

                            <!-- Subject -->
                            <div class="form-group">
                                <label for="subject" class="form-label">{{ $t('contact.form.subject') }}</label>
                                <input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    class="form-input"
                                    required
                                />
                                <span v-if="form.errors.subject" class="form-error">{{ form.errors.subject }}</span>
                            </div>

                            <!-- Message -->
                            <div class="form-group">
                                <label for="message" class="form-label">{{ $t('contact.form.message') }}</label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    class="form-textarea"
                                    rows="5"
                                    required
                                ></textarea>
                                <span v-if="form.errors.message" class="form-error">{{ form.errors.message }}</span>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="submit-button" :disabled="form.processing">
                                <span v-if="form.processing">{{ $t('contact.form.sending') }}</span>
                                <span v-else>{{ $t('contact.form.send') }}</span>
                            </button>

                            <!-- Success Message -->
                            <div v-if="form.recentlySuccessful" class="success-message">
                                {{ $t('contact.form.success') }}
                            </div>
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
                                    <p class="info-value">info@ralphmania.lv</p>
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
                                    <p class="info-value">Rīga, Latvija</p>
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
                                        <a href="#" class="social-link youtube">
                                            <i class="fab fa-youtube"></i>
                                        </a>
                                        <a href="#" class="social-link tiktok">
                                            <i class="fab fa-tiktok"></i>
                                        </a>
                                        <a href="#" class="social-link instagram">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                        <a href="#" class="social-link twitter">
                                            <i class="fab fa-twitter"></i>
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
    margin-bottom: 2rem;
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

.form-input,
.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.form-error {
    color: #dc2626;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

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
}

.success-message {
    padding: 1rem;
    background-color: #d1fae5;
    color: #065f46;
    border-radius: 0.5rem;
    text-align: center;
    font-weight: 500;
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

.social-link.twitter {
    background-color: #1DA1F2;
}

@media (max-width: 640px) {
    .hero-title {
        font-size: 2rem;
    }

    .contact-form-container {
        padding: 1.5rem;
    }
}
</style>
