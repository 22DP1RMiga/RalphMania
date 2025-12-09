<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <Head :title="$t('nav.about')" />

    <MainLayout>
        <!-- About Hero Section -->
        <section class="about-hero">
            <div class="hero-container">
                <h1 class="hero-title">{{ $t('about.hero.title') }}</h1>
                <p class="hero-subtitle">{{ $t('about.hero.subtitle') }}</p>
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

        <!-- Story Section -->
        <section class="story-section">
            <div class="section-container">
                <div class="story-content">
                    <div class="story-text">
                        <h2 class="section-title">{{ $t('about.story.title') }}</h2>
                        <p class="story-paragraph">{{ $t('about.story.paragraph_1') }}</p>
                        <p class="story-paragraph">{{ $t('about.story.paragraph_2') }}</p>
                        <p class="story-paragraph">{{ $t('about.story.paragraph_3') }}</p>
                    </div>
                    <div class="about-image">
                        <img src="/img/about-okay-logo.png" alt="RalphMania Okay Logo">
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission Section -->
        <section class="mission-section">
            <div class="section-container">
                <h2 class="section-title-center">{{ $t('about.mission.title') }}</h2>
                <div class="mission-grid">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="mission-card-title">{{ $t('about.mission.content.title') }}</h3>
                        <p class="mission-card-text">{{ $t('about.mission.content.text') }}</p>
                    </div>
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3 class="mission-card-title">{{ $t('about.mission.products.title') }}</h3>
                        <p class="mission-card-text">{{ $t('about.mission.products.text') }}</p>
                    </div>
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="mission-card-title">{{ $t('about.mission.community.title') }}</h3>
                        <p class="mission-card-text">{{ $t('about.mission.community.text') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values-section">
            <div class="section-container">
                <h2 class="section-title-center">{{ $t('about.values.title') }}</h2>
                <div class="values-grid">
                    <div class="value-item">
                        <div class="value-icon">✨</div>
                        <h3 class="value-title">{{ $t('about.values.quality.title') }}</h3>
                        <p class="value-text">{{ $t('about.values.quality.text') }}</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">🎯</div>
                        <h3 class="value-title">{{ $t('about.values.authenticity.title') }}</h3>
                        <p class="value-text">{{ $t('about.values.authenticity.text') }}</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">❤️</div>
                        <h3 class="value-title">{{ $t('about.values.passion.title') }}</h3>
                        <p class="value-text">{{ $t('about.values.passion.text') }}</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">🚀</div>
                        <h3 class="value-title">{{ $t('about.values.innovation.title') }}</h3>
                        <p class="value-text">{{ $t('about.values.innovation.text') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="section-container">
                <div class="cta-content">
                    <h2 class="cta-title">{{ $t('about.cta.title') }}</h2>
                    <p class="cta-text">{{ $t('about.cta.text') }}</p>
                    <div class="cta-buttons">
                        <a href="/shop" class="cta-button cta-button-primary">
                            {{ $t('about.cta.shop') }}
                        </a>
                        <a href="/contact" class="cta-button cta-button-secondary">
                            {{ $t('about.cta.contact') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
/* Hero Section */
.about-hero {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.about-hero::before {
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

/* Story Section */
.story-section {
    padding: 4rem 2rem;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
}

.story-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2rem;
}

.story-paragraph {
    font-size: 1.125rem;
    line-height: 1.8;
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.about-image img {
    width: 100%;
    height: auto;
    display: block;
    filter: drop-shadow(30px 10px 4px rgba(90, 90, 90, 0.5));
}

@media (max-width: 768px) {
    .story-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* Mission Section */
.mission-section {
    padding: 4rem 2rem;
    background-color: #f9fafb;
}

.section-title-center {
    font-size: 2.5rem;
    font-weight: 700;
    color: #111827;
    text-align: center;
    margin-bottom: 3rem;
}

.mission-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.mission-card {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.mission-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.mission-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.mission-card-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
}

.mission-card-text {
    font-size: 1rem;
    line-height: 1.6;
    color: #6b7280;
}

@media (max-width: 768px) {
    .mission-grid {
        grid-template-columns: 1fr;
    }
}

/* Values Section */
.values-section {
    padding: 4rem 2rem;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

.value-item {
    text-align: center;
}

.value-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.value-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.75rem;
}

.value-text {
    font-size: 0.875rem;
    line-height: 1.6;
    color: #6b7280;
}

@media (max-width: 1024px) {
    .values-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .values-grid {
        grid-template-columns: 1fr;
    }
}

/* CTA Section */
.cta-section {
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.cta-content {
    text-align: center;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cta-text {
    font-size: 1.25rem;
    opacity: 0.95;
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.cta-button {
    padding: 1rem 2.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.125rem;
}

.cta-button-primary {
    background-color: white;
    color: #dc2626;
}

.cta-button-primary:hover {
    background-color: #f3f4f6;
    transform: translateY(-2px);
}

.cta-button-secondary {
    background-color: transparent;
    color: white;
    border: 2px solid white;
}

.cta-button-secondary:hover {
    background-color: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

@media (max-width: 640px) {
    .hero-title, .cta-title {
        font-size: 2rem;
    }

    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }

    .cta-button {
        width: 100%;
        max-width: 300px;
    }
}
</style>
