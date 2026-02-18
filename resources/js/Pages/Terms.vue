<script setup>
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';

const { t, locale } = useI18n();

defineOptions({
    layout: MainLayout
});
</script>

<template>
    <Head :title="t('terms.title')" />

    <div class="terms-page">
        <div class="terms-container">
            <!-- Header -->
            <div class="terms-header">
                <h1 class="terms-title">{{ t('terms.title') }}</h1>
                <p class="terms-updated">{{ t('terms.last_updated') }}: 2025-01-15</p>
            </div>

            <!-- Content -->
            <div class="terms-content">
                <!-- Introduction -->
                <section class="terms-section">
                    <h2>{{ t('terms.introduction.title') }}</h2>
                    <p>{{ t('terms.introduction.text') }}</p>
                </section>

                <!-- Definitions -->
                <section class="terms-section">
                    <h2>{{ t('terms.definitions.title') }}</h2>
                    <ul>
                        <li><strong>{{ t('terms.definitions.platform') }}:</strong> {{ t('terms.definitions.platform_desc') }}</li>
                        <li><strong>{{ t('terms.definitions.user') }}:</strong> {{ t('terms.definitions.user_desc') }}</li>
                        <li><strong>{{ t('terms.definitions.content') }}:</strong> {{ t('terms.definitions.content_desc') }}</li>
                        <li><strong>{{ t('terms.definitions.products') }}:</strong> {{ t('terms.definitions.products_desc') }}</li>
                        <li><strong>{{ t('terms.definitions.administrator') }}:</strong> {{ t('terms.definitions.administrator_desc') }}</li>
                    </ul>
                </section>

                <!-- Account Registration -->
                <section class="terms-section">
                    <h2>{{ t('terms.account.title') }}</h2>
                    <p>{{ t('terms.account.intro') }}</p>
                    <ul>
                        <li>{{ t('terms.account.accurate_info') }}</li>
                        <li>{{ t('terms.account.password_security') }}</li>
                        <li>{{ t('terms.account.one_account') }}</li>
                        <li>{{ t('terms.account.age_requirement') }}</li>
                        <li>{{ t('terms.account.responsibility') }}</li>
                    </ul>
                </section>

                <!-- User Conduct -->
                <section class="terms-section">
                    <h2>{{ t('terms.conduct.title') }}</h2>
                    <p>{{ t('terms.conduct.intro') }}</p>
                    <ul>
                        <li>{{ t('terms.conduct.no_illegal') }}</li>
                        <li>{{ t('terms.conduct.no_harmful') }}</li>
                        <li>{{ t('terms.conduct.no_spam') }}</li>
                        <li>{{ t('terms.conduct.no_impersonation') }}</li>
                        <li>{{ t('terms.conduct.no_hacking') }}</li>
                        <li>{{ t('terms.conduct.respect_others') }}</li>
                    </ul>
                </section>

                <!-- Content Guidelines -->
                <section class="terms-section">
                    <h2>{{ t('terms.content_guidelines.title') }}</h2>
                    <p>{{ t('terms.content_guidelines.intro') }}</p>
                    <ul>
                        <li>{{ t('terms.content_guidelines.no_offensive') }}</li>
                        <li>{{ t('terms.content_guidelines.no_copyright') }}</li>
                        <li>{{ t('terms.content_guidelines.no_false_info') }}</li>
                        <li>{{ t('terms.content_guidelines.moderation') }}</li>
                    </ul>
                </section>

                <!-- Shop Terms -->
                <section class="terms-section">
                    <h2>{{ t('terms.shop.title') }}</h2>

                    <h3>{{ t('terms.shop.orders.title') }}</h3>
                    <p>{{ t('terms.shop.orders.text') }}</p>

                    <h3>{{ t('terms.shop.pricing.title') }}</h3>
                    <p>{{ t('terms.shop.pricing.text') }}</p>

                    <h3>{{ t('terms.shop.payment.title') }}</h3>
                    <p>{{ t('terms.shop.payment.text') }}</p>

                    <h3>{{ t('terms.shop.delivery.title') }}</h3>
                    <p>{{ t('terms.shop.delivery.text') }}</p>
                </section>

                <!-- Returns and Refunds -->
                <section class="terms-section">
                    <h2>{{ t('terms.returns.title') }}</h2>
                    <p>{{ t('terms.returns.intro') }}</p>
                    <ul>
                        <li>{{ t('terms.returns.period') }}</li>
                        <li>{{ t('terms.returns.condition') }}</li>
                        <li>{{ t('terms.returns.process') }}</li>
                        <li>{{ t('terms.returns.refund_time') }}</li>
                    </ul>
                </section>

                <!-- Intellectual Property -->
                <section class="terms-section">
                    <h2>{{ t('terms.intellectual_property.title') }}</h2>
                    <p>{{ t('terms.intellectual_property.text') }}</p>
                    <ul>
                        <li>{{ t('terms.intellectual_property.logo') }}</li>
                        <li>{{ t('terms.intellectual_property.content') }}</li>
                        <li>{{ t('terms.intellectual_property.design') }}</li>
                    </ul>
                </section>

                <!-- Limitation of Liability -->
                <section class="terms-section">
                    <h2>{{ t('terms.liability.title') }}</h2>
                    <p>{{ t('terms.liability.text') }}</p>
                    <ul>
                        <li>{{ t('terms.liability.availability') }}</li>
                        <li>{{ t('terms.liability.accuracy') }}</li>
                        <li>{{ t('terms.liability.third_party') }}</li>
                        <li>{{ t('terms.liability.damages') }}</li>
                    </ul>
                </section>

                <!-- Account Termination -->
                <section class="terms-section">
                    <h2>{{ t('terms.termination.title') }}</h2>
                    <p>{{ t('terms.termination.text') }}</p>
                    <ul>
                        <li>{{ t('terms.termination.user_right') }}</li>
                        <li>{{ t('terms.termination.admin_right') }}</li>
                        <li>{{ t('terms.termination.consequences') }}</li>
                    </ul>
                </section>

                <!-- Changes to Terms -->
                <section class="terms-section">
                    <h2>{{ t('terms.changes.title') }}</h2>
                    <p>{{ t('terms.changes.text') }}</p>
                </section>

                <!-- Governing Law -->
                <section class="terms-section">
                    <h2>{{ t('terms.governing_law.title') }}</h2>
                    <p>{{ t('terms.governing_law.text') }}</p>
                </section>

                <!-- Contact -->
                <section class="terms-section">
                    <h2>{{ t('terms.contact.title') }}</h2>
                    <p>{{ t('terms.contact.text') }}</p>
                    <div class="contact-info">
                        <p><strong>{{ t('terms.contact.email_label') }}:</strong> ralphmania.roltonslv@gmail.com</p>
                        <p><strong>{{ t('terms.contact.address_label') }}:</strong> Rīga, Latvija</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.terms-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 2rem 1rem 4rem;
}

.terms-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.terms-header {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    padding: 3rem 2rem;
    text-align: center;
}

.terms-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.terms-updated {
    opacity: 0.9;
    font-size: 0.95rem;
}

.terms-content {
    padding: 2.5rem;
}

.terms-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.terms-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.terms-section h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.terms-section h2::before {
    content: '';
    width: 4px;
    height: 1.5rem;
    background: #dc2626;
    border-radius: 2px;
}

.terms-section h3 {
    font-size: 1.15rem;
    font-weight: 600;
    color: #374151;
    margin: 1.5rem 0 0.75rem;
}

.terms-section p {
    color: #4b5563;
    line-height: 1.7;
    margin-bottom: 1rem;
}

.terms-section ul {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.terms-section ul li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
    color: #4b5563;
    line-height: 1.6;
}

.terms-section ul li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: #dc2626;
    font-weight: bold;
}

.contact-info {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-top: 1rem;
}

.contact-info p {
    margin-bottom: 0.5rem;
}

.contact-info p:last-child {
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .terms-page {
        padding: 1rem;
    }

    .terms-header {
        padding: 2rem 1.5rem;
    }

    .terms-title {
        font-size: 1.75rem;
    }

    .terms-content {
        padding: 1.5rem;
    }

    .terms-section h2 {
        font-size: 1.25rem;
    }
}
</style>
