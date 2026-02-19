<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const { t } = useI18n();

defineOptions({
    layout: ShopLayout
});

// Shipping zones with pricing
const shippingZones = [
    {
        id: 'latvia',
        nameKey: 'shop_shipping.zones.latvia',
        countries: ['Latvija'],
        flag: '🇱🇻',
        standardPrice: 2.99,
        standardDays: '1-3',
        expressPrice: 5.99,
        expressDays: '1',
        freeThreshold: 35,
    },
    {
        id: 'baltics',
        nameKey: 'shop_shipping.zones.baltics',
        countries: ['Lietuva', 'Igaunija'],
        flag: '🇱🇹🇪🇪',
        standardPrice: 4.99,
        standardDays: '3-5',
        expressPrice: 9.99,
        expressDays: '1-2',
        freeThreshold: 50,
    },
    {
        id: 'europe',
        nameKey: 'shop_shipping.zones.europe',
        countries: ['Polija', 'Vācija', 'Somija', 'Zviedrija', 'u.c.'],
        flag: '🇪🇺',
        standardPrice: 9.99,
        standardDays: '5-10',
        expressPrice: 19.99,
        expressDays: '2-4',
        freeThreshold: 75,
    },
];

// Shipping features
const features = [
    { icon: 'fas fa-box', titleKey: 'shop_shipping.features.packaging', descKey: 'shop_shipping.features.packaging_desc' },
    { icon: 'fas fa-map-marker-alt', titleKey: 'shop_shipping.features.tracking', descKey: 'shop_shipping.features.tracking_desc' },
    { icon: 'fas fa-shield-alt', titleKey: 'shop_shipping.features.insurance', descKey: 'shop_shipping.features.insurance_desc' },
    { icon: 'fas fa-redo', titleKey: 'shop_shipping.features.returns', descKey: 'shop_shipping.features.returns_desc' },
];
</script>

<template>
    <Head :title="t('shop_shipping.title')" />

    <div class="shipping-page">
        <!-- Hero Section -->
        <div class="shipping-hero">
            <div class="hero-content">
                <div class="hero-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h1 class="hero-title">{{ t('shop_shipping.title') }}</h1>
                <p class="hero-subtitle">{{ t('shop_shipping.subtitle') }}</p>
            </div>
        </div>

        <div class="shipping-container">
            <!-- Free Shipping Banner -->
            <div class="free-shipping-banner">
                <i class="fas fa-gift"></i>
                <span>{{ t('shop_shipping.free_shipping_banner') }}</span>
            </div>

            <!-- Shipping Zones -->
            <section class="section">
                <h2 class="section-title">
                    <i class="fas fa-globe-europe"></i>
                    {{ t('shop_shipping.zones_title') }}
                </h2>

                <div class="zones-grid">
                    <div v-for="zone in shippingZones" :key="zone.id" class="zone-card">
                        <div class="zone-header">
                            <span class="zone-flag">{{ zone.flag }}</span>
                            <h3 class="zone-name">{{ t(zone.nameKey) }}</h3>
                        </div>

                        <div class="zone-countries">
                            {{ zone.countries.join(', ') }}
                        </div>

                        <div class="zone-options">
                            <!-- Standard -->
                            <div class="shipping-option">
                                <div class="option-header">
                                    <i class="fas fa-box"></i>
                                    <span>{{ t('shop_shipping.standard') }}</span>
                                </div>
                                <div class="option-details">
                                    <span class="option-price">€{{ zone.standardPrice.toFixed(2) }}</span>
                                    <span class="option-days">{{ zone.standardDays }} {{ t('shop_shipping.business_days') }}</span>
                                </div>
                            </div>

                            <!-- Express -->
                            <div class="shipping-option express">
                                <div class="option-header">
                                    <i class="fas fa-bolt"></i>
                                    <span>{{ t('shop_shipping.express') }}</span>
                                </div>
                                <div class="option-details">
                                    <span class="option-price">€{{ zone.expressPrice.toFixed(2) }}</span>
                                    <span class="option-days">{{ zone.expressDays }} {{ t('shop_shipping.business_days') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="zone-free">
                            <i class="fas fa-check-circle"></i>
                            {{ t('shop_shipping.free_over') }} <strong>€{{ zone.freeThreshold }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Shipping Features -->
            <section class="section">
                <h2 class="section-title">
                    <i class="fas fa-star"></i>
                    {{ t('shop_shipping.features_title') }}
                </h2>

                <div class="features-grid">
                    <div v-for="feature in features" :key="feature.titleKey" class="feature-card">
                        <div class="feature-icon">
                            <i :class="feature.icon"></i>
                        </div>
                        <h3 class="feature-title">{{ t(feature.titleKey) }}</h3>
                        <p class="feature-desc">{{ t(feature.descKey) }}</p>
                    </div>
                </div>
            </section>

            <!-- Delivery Process -->
            <section class="section">
                <h2 class="section-title">
                    <i class="fas fa-route"></i>
                    {{ t('shop_shipping.process_title') }}
                </h2>

                <div class="process-timeline">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>{{ t('shop_shipping.process.step1_title') }}</h4>
                            <p>{{ t('shop_shipping.process.step1_desc') }}</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>{{ t('shop_shipping.process.step2_title') }}</h4>
                            <p>{{ t('shop_shipping.process.step2_desc') }}</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>{{ t('shop_shipping.process.step3_title') }}</h4>
                            <p>{{ t('shop_shipping.process.step3_desc') }}</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>{{ t('shop_shipping.process.step4_title') }}</h4>
                            <p>{{ t('shop_shipping.process.step4_desc') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Questions CTA -->
            <div class="questions-cta">
                <h3>{{ t('shop_shipping.questions') }}</h3>
                <p>{{ t('shop_shipping.questions_text') }}</p>
                <div class="cta-buttons">
                    <Link href="/shop/faq" class="btn-secondary">
                        <i class="fas fa-question-circle"></i>
                        {{ t('shop_shipping.view_faq') }}
                    </Link>
                    <Link href="/shop/contact" class="btn-primary">
                        <i class="fas fa-envelope"></i>
                        {{ t('shop_shipping.contact_us') }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.shipping-page {
    min-height: 100vh;
    background: #f8fafc;
}

/* Hero */
.shipping-hero {
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
.shipping-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

/* Free Shipping Banner */
.free-shipping-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 1rem;
    color: white;
    font-weight: 600;
    font-size: 1.125rem;
    margin-bottom: 2rem;
}

.free-shipping-banner i {
    font-size: 1.5rem;
}

/* Sections */
.section {
    margin-bottom: 3rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
}

.section-title i {
    color: #dc2626;
}

/* Zones Grid */
.zones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.zone-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 2px solid #e5e7eb;
    transition: all 0.2s;
}

.zone-card:hover {
    border-color: #fca5a5;
    box-shadow: 0 4px 16px rgba(220, 38, 38, 0.1);
}

.zone-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.zone-flag {
    font-size: 1.5rem;
}

.zone-name {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.zone-countries {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1.25rem;
}

.zone-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.shipping-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}

.shipping-option.express {
    background: #fef3c7;
}

.option-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.shipping-option.express .option-header {
    color: #b45309;
}

.option-details {
    text-align: right;
}

.option-price {
    display: block;
    font-weight: 700;
    color: #111827;
}

.option-days {
    font-size: 0.75rem;
    color: #6b7280;
}

.zone-free {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    font-size: 0.875rem;
    color: #10b981;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.feature-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.feature-icon {
    width: 3.5rem;
    height: 3.5rem;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: #dc2626;
    font-size: 1.25rem;
}

.feature-title {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem;
}

.feature-desc {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

/* Process Timeline */
.process-timeline {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.process-step {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
}

.step-number {
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
    padding-bottom: 1.5rem;
    border-left: 2px solid #e5e7eb;
    margin-left: -1.25rem;
    padding-left: 2.5rem;
}

.process-step:last-child .step-content {
    border-left: none;
}

.step-content h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
}

.step-content p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Questions CTA */
.questions-cta {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-radius: 1rem;
    padding: 2.5rem;
    text-align: center;
    color: white;
}

.questions-cta h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
}

.questions-cta p {
    opacity: 0.8;
    margin: 0 0 1.5rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .shipping-hero {
        padding: 3rem 1.5rem;
    }

    .hero-title {
        font-size: 2rem;
    }

    .shipping-container {
        padding: 1.5rem;
    }

    .zones-grid {
        grid-template-columns: 1fr;
    }
}
</style>
