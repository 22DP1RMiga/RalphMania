<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t, locale } = useI18n();

const props = defineProps({
    userEmail: {
        type: String,
        default: ''
    }
});

// State
const isLoading = ref(true);
const isSubscribed = ref(false);
const offers = ref([]);
const preferences = ref({
    receive_news: true,
    receive_promotions: true,
    receive_announcements: true,
});
const copiedCode = ref(null);
const savingPreferences = ref(false);
const showPreferences = ref(false);

// Fetch subscription status and offers
const fetchData = async () => {
    isLoading.value = true;
    try {
        // Get status
        const statusRes = await axios.get('/newsletter/status');
        isSubscribed.value = statusRes.data.subscribed;
        if (statusRes.data.preferences) {
            preferences.value = statusRes.data.preferences;
        }

        // Get offers if subscribed
        if (isSubscribed.value) {
            const offersRes = await axios.get('/newsletter/offers');
            offers.value = offersRes.data.offers || [];
        }
    } catch (error) {
        console.error('Failed to fetch newsletter data:', error);
    } finally {
        isLoading.value = false;
    }
};

// Subscribe
const subscribe = async () => {
    try {
        const res = await axios.post('/newsletter/subscribe', {
            email: props.userEmail
        });
        if (res.data.success) {
            isSubscribed.value = true;
            fetchData();
        }
    } catch (error) {
        console.error('Subscribe error:', error);
    }
};

// Save preferences
const savePreferences = async () => {
    savingPreferences.value = true;
    try {
        await axios.put('/newsletter/preferences', preferences.value);
        showPreferences.value = false;
    } catch (error) {
        console.error('Save preferences error:', error);
    } finally {
        savingPreferences.value = false;
    }
};

// Copy discount code
const copyCode = (code) => {
    navigator.clipboard.writeText(code);
    copiedCode.value = code;
    setTimeout(() => {
        copiedCode.value = null;
    }, 2000);
};

onMounted(() => {
    fetchData();
});
</script>

<template>
    <div class="subscriber-section">
        <!-- Header -->
        <div class="section-header">
            <div class="header-info">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i>
                    {{ t('newsletter.title') }}
                </h3>
                <span v-if="isSubscribed" class="status-badge status-subscribed">
                    <i class="fas fa-check-circle"></i>
                    {{ t('newsletter.subscribed') }}
                </span>
                <span v-else class="status-badge status-not-subscribed">
                    <i class="fas fa-times-circle"></i>
                    {{ t('newsletter.not_subscribed') }}
                </span>
            </div>
            <button
                v-if="isSubscribed"
                @click="showPreferences = !showPreferences"
                class="btn-preferences"
            >
                <i class="fas fa-cog"></i>
                {{ t('newsletter.manage_preferences') }}
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
        </div>

        <!-- Not Subscribed State -->
        <div v-else-if="!isSubscribed" class="not-subscribed-card">
            <div class="card-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="card-content">
                <h4>{{ t('newsletter.offers.title') }}</h4>
                <p>{{ t('dashboard.sections.newsletter.description') }}</p>
                <button @click="subscribe" class="btn-subscribe">
                    <i class="fas fa-paper-plane"></i>
                    {{ t('newsletter.subscribe_now') }}
                </button>
            </div>
        </div>

        <!-- Subscribed Content -->
        <template v-else>
            <!-- Preferences Panel -->
            <Transition name="slide">
                <div v-if="showPreferences" class="preferences-panel">
                    <h4 class="preferences-title">
                        <i class="fas fa-sliders-h"></i>
                        {{ t('newsletter.preferences.title') }}
                    </h4>
                    <div class="preferences-list">
                        <label class="preference-item">
                            <input
                                type="checkbox"
                                v-model="preferences.receive_news"
                                class="preference-checkbox"
                            >
                            <span class="preference-label">
                                <i class="fas fa-newspaper"></i>
                                {{ t('newsletter.preferences.receive_news') }}
                            </span>
                        </label>
                        <label class="preference-item">
                            <input
                                type="checkbox"
                                v-model="preferences.receive_promotions"
                                class="preference-checkbox"
                            >
                            <span class="preference-label">
                                <i class="fas fa-percent"></i>
                                {{ t('newsletter.preferences.receive_promotions') }}
                            </span>
                        </label>
                        <label class="preference-item">
                            <input
                                type="checkbox"
                                v-model="preferences.receive_announcements"
                                class="preference-checkbox"
                            >
                            <span class="preference-label">
                                <i class="fas fa-bullhorn"></i>
                                {{ t('newsletter.preferences.receive_announcements') }}
                            </span>
                        </label>
                    </div>
                    <button
                        @click="savePreferences"
                        class="btn-save"
                        :disabled="savingPreferences"
                    >
                        <i v-if="savingPreferences" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ savingPreferences ? '...' : t('common.save') }}
                    </button>
                </div>
            </Transition>

            <!-- Offers Section -->
            <div class="offers-section">
                <h4 class="offers-title">
                    <i class="fas fa-gift"></i>
                    {{ t('newsletter.offers.title') }}
                    <span class="offers-badge">{{ t('newsletter.offers.subtitle') }}</span>
                </h4>

                <!-- No Offers -->
                <div v-if="offers.length === 0" class="no-offers">
                    <i class="fas fa-tags"></i>
                    <p>{{ t('newsletter.offers.no_offers') }}</p>
                </div>

                <!-- Offers Grid -->
                <div v-else class="offers-grid">
                    <div
                        v-for="offer in offers"
                        :key="offer.id"
                        class="offer-card"
                    >
                        <div class="offer-discount">
                            <span class="discount-value">{{ offer.discount }}</span>
                            <span class="discount-label">{{ t('common.discount') }}</span>
                        </div>
                        <div class="offer-content">
                            <h5 class="offer-title">{{ offer.title }}</h5>
                            <p v-if="offer.description" class="offer-description">
                                {{ offer.description }}
                            </p>
                            <div class="offer-details">
                                <span v-if="offer.min_order" class="offer-detail">
                                    <i class="fas fa-shopping-cart"></i>
                                    {{ t('newsletter.offers.min_order') }}: €{{ offer.min_order }}
                                </span>
                                <span v-if="offer.expires_at" class="offer-detail">
                                    <i class="fas fa-clock"></i>
                                    {{ t('newsletter.offers.expires') }}: {{ offer.expires_at }}
                                </span>
                            </div>
                        </div>
                        <div class="offer-code-section">
                            <span class="code-label">{{ t('newsletter.offers.code') }}:</span>
                            <div class="code-wrapper">
                                <code class="offer-code">{{ offer.code }}</code>
                                <button
                                    @click="copyCode(offer.code)"
                                    class="btn-copy"
                                    :class="{ 'copied': copiedCode === offer.code }"
                                >
                                    <i :class="copiedCode === offer.code ? 'fas fa-check' : 'fas fa-copy'"></i>
                                    {{ copiedCode === offer.code ? t('newsletter.offers.copied') : t('newsletter.offers.copy_code') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.subscriber-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Header */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.section-title i {
    color: #dc2626;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-subscribed {
    background: #dcfce7;
    color: #15803d;
}

.status-not-subscribed {
    background: #fef2f2;
    color: #dc2626;
}

.btn-preferences {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    border: none;
    border-radius: 0.5rem;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-preferences:hover {
    background: #e5e7eb;
}

/* Loading */
.loading-state {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
    font-size: 1.5rem;
}

/* Not Subscribed Card */
.not-subscribed-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 2rem;
    background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
    border: 2px dashed #fca5a5;
    border-radius: 1rem;
}

.card-icon {
    flex-shrink: 0;
    width: 4rem;
    height: 4rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.card-content h4 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem;
}

.card-content p {
    color: #6b7280;
    margin: 0 0 1rem;
    font-size: 0.95rem;
}

.btn-subscribe {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    border-radius: 0.5rem;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-subscribe:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Preferences Panel */
.preferences-panel {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}

.preferences-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 1rem;
}

.preferences-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.preference-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
}

.preference-checkbox {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: #dc2626;
}

.preference-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #374151;
    font-size: 0.95rem;
}

.preference-label i {
    color: #6b7280;
    width: 1rem;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    background: #dc2626;
    border: none;
    border-radius: 0.375rem;
    color: white;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
    background: #b91c1c;
}

.btn-save:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Offers Section */
.offers-section {
    margin-top: 1rem;
}

.offers-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1rem;
}

.offers-title i {
    color: #f59e0b;
}

.offers-badge {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 1rem;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
}

.no-offers {
    text-align: center;
    padding: 2rem;
    background: #f9fafb;
    border-radius: 0.75rem;
}

.no-offers i {
    font-size: 2rem;
    color: #d1d5db;
    margin-bottom: 0.5rem;
}

.no-offers p {
    color: #6b7280;
    margin: 0;
}

/* Offers Grid */
.offers-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.offer-card {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1.25rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, #fffbeb 0%, #fff 100%);
    border: 1px solid #fde68a;
    border-radius: 0.75rem;
    align-items: center;
}

.offer-discount {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 5rem;
    height: 5rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 0.75rem;
    color: white;
    text-align: center;
}

.discount-value {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
}

.discount-label {
    font-size: 0.625rem;
    text-transform: uppercase;
    opacity: 0.9;
    margin-top: 0.25rem;
}

.offer-content {
    flex: 1;
}

.offer-title {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.375rem;
}

.offer-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.5rem;
}

.offer-details {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.offer-detail {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #9ca3af;
}

.offer-detail i {
    color: #f59e0b;
}

.offer-code-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}

.code-label {
    font-size: 0.6875rem;
    color: #9ca3af;
    text-transform: uppercase;
    font-weight: 600;
}

.code-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.offer-code {
    padding: 0.5rem 0.75rem;
    background: white;
    border: 2px dashed #fcd34d;
    border-radius: 0.375rem;
    font-family: monospace;
    font-size: 0.95rem;
    font-weight: 700;
    color: #b45309;
    letter-spacing: 0.05em;
}

.btn-copy {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    background: #fef3c7;
    border: none;
    border-radius: 0.375rem;
    color: #b45309;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-copy:hover {
    background: #fde68a;
}

.btn-copy.copied {
    background: #dcfce7;
    color: #15803d;
}

/* Slide Animation */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Responsive */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .not-subscribed-card {
        flex-direction: column;
        text-align: center;
    }

    .offer-card {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .offer-discount {
        margin: 0 auto;
    }

    .offer-code-section {
        align-items: center;
    }

    .offer-details {
        justify-content: center;
    }
}
</style>
