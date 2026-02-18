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
    <Head :title="t('privacy.title')" />

    <div class="privacy-page">
        <div class="privacy-container">
            <!-- Header -->
            <div class="privacy-header">
                <h1 class="privacy-title">{{ t('privacy.title') }}</h1>
                <p class="privacy-updated">{{ t('privacy.last_updated') }}: 2025-01-15</p>
            </div>

            <!-- Content -->
            <div class="privacy-content">
                <!-- Introduction -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.introduction.title') }}</h2>
                    <p>{{ t('privacy.introduction.text') }}</p>
                </section>

                <!-- Data Collection -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.data_collection.title') }}</h2>
                    <p>{{ t('privacy.data_collection.intro') }}</p>

                    <h3>{{ t('privacy.data_collection.personal_data.title') }}</h3>
                    <ul>
                        <li>{{ t('privacy.data_collection.personal_data.name') }}</li>
                        <li>{{ t('privacy.data_collection.personal_data.email') }}</li>
                        <li>{{ t('privacy.data_collection.personal_data.phone') }}</li>
                        <li>{{ t('privacy.data_collection.personal_data.address') }}</li>
                        <li>{{ t('privacy.data_collection.personal_data.birth_date') }}</li>
                    </ul>

                    <h3>{{ t('privacy.data_collection.technical_data.title') }}</h3>
                    <ul>
                        <li>{{ t('privacy.data_collection.technical_data.ip') }}</li>
                        <li>{{ t('privacy.data_collection.technical_data.browser') }}</li>
                        <li>{{ t('privacy.data_collection.technical_data.device') }}</li>
                        <li>{{ t('privacy.data_collection.technical_data.cookies') }}</li>
                    </ul>
                </section>

                <!-- Data Usage -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.data_usage.title') }}</h2>
                    <p>{{ t('privacy.data_usage.intro') }}</p>
                    <ul>
                        <li>{{ t('privacy.data_usage.account') }}</li>
                        <li>{{ t('privacy.data_usage.orders') }}</li>
                        <li>{{ t('privacy.data_usage.communication') }}</li>
                        <li>{{ t('privacy.data_usage.improvement') }}</li>
                        <li>{{ t('privacy.data_usage.security') }}</li>
                    </ul>
                </section>

                <!-- Data Sharing -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.data_sharing.title') }}</h2>
                    <p>{{ t('privacy.data_sharing.intro') }}</p>
                    <ul>
                        <li>{{ t('privacy.data_sharing.payment') }}</li>
                        <li>{{ t('privacy.data_sharing.delivery') }}</li>
                        <li>{{ t('privacy.data_sharing.legal') }}</li>
                    </ul>
                    <p>{{ t('privacy.data_sharing.no_sell') }}</p>
                </section>

                <!-- Data Security -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.data_security.title') }}</h2>
                    <p>{{ t('privacy.data_security.text') }}</p>
                    <ul>
                        <li>{{ t('privacy.data_security.encryption') }}</li>
                        <li>{{ t('privacy.data_security.ssl') }}</li>
                        <li>{{ t('privacy.data_security.access') }}</li>
                        <li>{{ t('privacy.data_security.backup') }}</li>
                    </ul>
                </section>

                <!-- Cookies -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.cookies.title') }}</h2>
                    <p>{{ t('privacy.cookies.text') }}</p>
                    <ul>
                        <li>{{ t('privacy.cookies.essential') }}</li>
                        <li>{{ t('privacy.cookies.functional') }}</li>
                        <li>{{ t('privacy.cookies.analytics') }}</li>
                    </ul>
                </section>

                <!-- User Rights -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.user_rights.title') }}</h2>
                    <p>{{ t('privacy.user_rights.intro') }}</p>
                    <ul>
                        <li>{{ t('privacy.user_rights.access') }}</li>
                        <li>{{ t('privacy.user_rights.rectification') }}</li>
                        <li>{{ t('privacy.user_rights.erasure') }}</li>
                        <li>{{ t('privacy.user_rights.portability') }}</li>
                        <li>{{ t('privacy.user_rights.objection') }}</li>
                    </ul>
                </section>

                <!-- Data Retention -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.data_retention.title') }}</h2>
                    <p>{{ t('privacy.data_retention.text') }}</p>
                </section>

                <!-- Children -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.children.title') }}</h2>
                    <p>{{ t('privacy.children.text') }}</p>
                </section>

                <!-- Changes -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.changes.title') }}</h2>
                    <p>{{ t('privacy.changes.text') }}</p>
                </section>

                <!-- Contact -->
                <section class="privacy-section">
                    <h2>{{ t('privacy.contact.title') }}</h2>
                    <p>{{ t('privacy.contact.text') }}</p>
                    <div class="contact-info">
                        <p><strong>{{ t('privacy.contact.email_label') }}:</strong> ralphmania.roltonslv@gmail.com</p>
                        <p><strong>{{ t('privacy.contact.address_label') }}:</strong> Rīga, Latvija</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.privacy-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 2rem 1rem 4rem;
}

.privacy-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.privacy-header {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    padding: 3rem 2rem;
    text-align: center;
}

.privacy-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.privacy-updated {
    opacity: 0.9;
    font-size: 0.95rem;
}

.privacy-content {
    padding: 2.5rem;
}

.privacy-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.privacy-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.privacy-section h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.privacy-section h2::before {
    content: '';
    width: 4px;
    height: 1.5rem;
    background: #dc2626;
    border-radius: 2px;
}

.privacy-section h3 {
    font-size: 1.15rem;
    font-weight: 600;
    color: #374151;
    margin: 1.5rem 0 0.75rem;
}

.privacy-section p {
    color: #4b5563;
    line-height: 1.7;
    margin-bottom: 1rem;
}

.privacy-section ul {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.privacy-section ul li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
    color: #4b5563;
    line-height: 1.6;
}

.privacy-section ul li::before {
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
    .privacy-page {
        padding: 1rem;
    }

    .privacy-header {
        padding: 2rem 1.5rem;
    }

    .privacy-title {
        font-size: 1.75rem;
    }

    .privacy-content {
        padding: 1.5rem;
    }

    .privacy-section h2 {
        font-size: 1.25rem;
    }
}
</style>
