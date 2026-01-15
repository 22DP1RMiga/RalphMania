<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const page = usePage();

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    settingGroups: { type: Array, default: () => ['general', 'shop', 'email', 'social', 'seo'] },
});

// Toast
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// State
const activeTab = ref('general');
const isLoading = ref(false);
const hasChanges = ref(false);

// Form data - organized by groups
const form = ref({
    // General
    site_name: '',
    site_description_lv: '',
    site_description_en: '',
    admin_email: '',
    timezone: 'Europe/Riga',
    date_format: 'd.m.Y',

    // Shop
    currency: 'EUR',
    currency_symbol: '€',
    tax_rate: 21,
    free_shipping_threshold: 50,
    low_stock_threshold: 5,
    items_per_page: 20,

    // Email
    mail_from_name: '',
    mail_from_address: '',
    smtp_host: '',
    smtp_port: 587,
    smtp_username: '',
    smtp_password: '',

    // Social
    facebook_url: '',
    instagram_url: '',
    twitter_url: '',
    youtube_url: '',
    tiktok_url: '',

    // SEO
    meta_title_lv: '',
    meta_title_en: '',
    meta_description_lv: '',
    meta_description_en: '',
    google_analytics_id: '',
    facebook_pixel_id: '',
});

// Load settings on mount
onMounted(() => {
    if (props.settings) {
        Object.keys(form.value).forEach(key => {
            if (props.settings[key] !== undefined) {
                form.value[key] = props.settings[key];
            }
        });
    }

    const flash = page.props.flash;
    if (flash?.success) { toastMessage.value = flash.success; toastType.value = 'success'; showToast.value = true; }
    if (flash?.error) { toastMessage.value = flash.error; toastType.value = 'error'; showToast.value = true; }
});

// Watch for changes
watch(form, () => { hasChanges.value = true; }, { deep: true });

// Tab configurations
const tabs = computed(() => [
    { key: 'general', icon: 'fas fa-cog', labelKey: 'admin.settings.tabs.general' },
    { key: 'shop', icon: 'fas fa-shopping-cart', labelKey: 'admin.settings.tabs.shop' },
    { key: 'email', icon: 'fas fa-envelope', labelKey: 'admin.settings.tabs.email' },
    { key: 'social', icon: 'fas fa-share-alt', labelKey: 'admin.settings.tabs.social' },
    { key: 'seo', icon: 'fas fa-search', labelKey: 'admin.settings.tabs.seo' },
]);

// Save settings
const saveSettings = () => {
    isLoading.value = true;
    router.post('/admin/settings', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = t('admin.settings.saved');
            toastType.value = 'success';
            showToast.value = true;
            hasChanges.value = false;
        },
        onError: () => {
            toastMessage.value = t('admin.settings.saveError');
            toastType.value = 'error';
            showToast.value = true;
        },
        onFinish: () => { isLoading.value = false; },
    });
};

// Test email
const testEmail = () => {
    router.post('/admin/settings/test-email', {}, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = t('admin.settings.emailSent');
            toastType.value = 'success';
            showToast.value = true;
        },
        onError: () => {
            toastMessage.value = t('admin.settings.emailError');
            toastType.value = 'error';
            showToast.value = true;
        },
    });
};

// Clear cache
const clearCache = () => {
    router.post('/admin/settings/clear-cache', {}, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = t('admin.settings.cacheCleared');
            toastType.value = 'success';
            showToast.value = true;
        },
    });
};
</script>

<template>
    <Head :title="t('admin.settings.title')" />
    <AdminLayout>
        <template #title>{{ t('admin.settings.title') }}</template>

        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />

        <div class="settings-layout">
            <!-- Tabs Sidebar -->
            <div class="settings-tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="['tab-btn', { active: activeTab === tab.key }]"
                >
                    <i :class="tab.icon"></i>
                    <span>{{ t(tab.labelKey) }}</span>
                </button>

                <div class="tabs-divider"></div>

                <button @click="clearCache" class="tab-btn tab-action">
                    <i class="fas fa-broom"></i>
                    <span>{{ t('admin.settings.clearCache') }}</span>
                </button>
            </div>

            <!-- Settings Content -->
            <div class="settings-content">
                <!-- General Settings -->
                <div v-show="activeTab === 'general'" class="settings-panel">
                    <h2 class="panel-title"><i class="fas fa-cog"></i> {{ t('admin.settings.tabs.general') }}</h2>

                    <div class="form-group">
                        <label class="form-label">{{ t('admin.settings.fields.siteName') }}</label>
                        <input v-model="form.site_name" type="text" class="form-input">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.descriptionLv') }}</label>
                            <textarea v-model="form.site_description_lv" class="form-textarea" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.descriptionEn') }}</label>
                            <textarea v-model="form.site_description_en" class="form-textarea" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.adminEmail') }}</label>
                            <input v-model="form.admin_email" type="email" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.timezone') }}</label>
                            <select v-model="form.timezone" class="form-select">
                                <option value="Europe/Riga">Europe/Riga (UTC+2/+3)</option>
                                <option value="Europe/London">Europe/London (UTC+0/+1)</option>
                                <option value="Europe/Berlin">Europe/Berlin (UTC+1/+2)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Shop Settings -->
                <div v-show="activeTab === 'shop'" class="settings-panel">
                    <h2 class="panel-title"><i class="fas fa-shopping-cart"></i> {{ t('admin.settings.tabs.shop') }}</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.currency') }}</label>
                            <select v-model="form.currency" class="form-select">
                                <option value="EUR">EUR - Euro</option>
                                <option value="USD">USD - US Dollar</option>
                                <option value="GBP">GBP - British Pound</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.taxRate') }} (%)</label>
                            <input v-model.number="form.tax_rate" type="number" min="0" max="100" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.freeShipping') }} (€)</label>
                            <input v-model.number="form.free_shipping_threshold" type="number" min="0" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.lowStock') }}</label>
                            <input v-model.number="form.low_stock_threshold" type="number" min="0" class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ t('admin.settings.fields.itemsPerPage') }}</label>
                        <select v-model.number="form.items_per_page" class="form-select">
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Email Settings -->
                <div v-show="activeTab === 'email'" class="settings-panel">
                    <h2 class="panel-title"><i class="fas fa-envelope"></i> {{ t('admin.settings.tabs.email') }}</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.mailFromName') }}</label>
                            <input v-model="form.mail_from_name" type="text" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.mailFromAddress') }}</label>
                            <input v-model="form.mail_from_address" type="email" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.smtpHost') }}</label>
                            <input v-model="form.smtp_host" type="text" class="form-input" placeholder="smtp.example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.smtpPort') }}</label>
                            <input v-model.number="form.smtp_port" type="number" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.smtpUsername') }}</label>
                            <input v-model="form.smtp_username" type="text" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.smtpPassword') }}</label>
                            <input v-model="form.smtp_password" type="password" class="form-input">
                        </div>
                    </div>

                    <button @click="testEmail" class="btn btn-secondary">
                        <i class="fas fa-paper-plane"></i>
                        {{ t('admin.settings.testEmail') }}
                    </button>
                </div>

                <!-- Social Settings -->
                <div v-show="activeTab === 'social'" class="settings-panel">
                    <h2 class="panel-title"><i class="fas fa-share-alt"></i> {{ t('admin.settings.tabs.social') }}</h2>

                    <div class="social-links">
                        <div class="form-group social-input">
                            <label class="form-label"><i class="fab fa-facebook"></i> Facebook</label>
                            <input v-model="form.facebook_url" type="url" class="form-input" placeholder="https://facebook.com/...">
                        </div>
                        <div class="form-group social-input">
                            <label class="form-label"><i class="fab fa-instagram"></i> Instagram</label>
                            <input v-model="form.instagram_url" type="url" class="form-input" placeholder="https://instagram.com/...">
                        </div>
                        <div class="form-group social-input">
                            <label class="form-label"><i class="fab fa-twitter"></i> Twitter/X</label>
                            <input v-model="form.twitter_url" type="url" class="form-input" placeholder="https://twitter.com/...">
                        </div>
                        <div class="form-group social-input">
                            <label class="form-label"><i class="fab fa-youtube"></i> YouTube</label>
                            <input v-model="form.youtube_url" type="url" class="form-input" placeholder="https://youtube.com/...">
                        </div>
                        <div class="form-group social-input">
                            <label class="form-label"><i class="fab fa-tiktok"></i> TikTok</label>
                            <input v-model="form.tiktok_url" type="url" class="form-input" placeholder="https://tiktok.com/...">
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div v-show="activeTab === 'seo'" class="settings-panel">
                    <h2 class="panel-title"><i class="fas fa-search"></i> {{ t('admin.settings.tabs.seo') }}</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.metaTitleLv') }}</label>
                            <input v-model="form.meta_title_lv" type="text" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.metaTitleEn') }}</label>
                            <input v-model="form.meta_title_en" type="text" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.metaDescriptionLv') }}</label>
                            <textarea v-model="form.meta_description_lv" class="form-textarea" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.settings.fields.metaDescriptionEn') }}</label>
                            <textarea v-model="form.meta_description_en" class="form-textarea" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Google Analytics ID</label>
                            <input v-model="form.google_analytics_id" type="text" class="form-input" placeholder="G-XXXXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Facebook Pixel ID</label>
                            <input v-model="form.facebook_pixel_id" type="text" class="form-input" placeholder="XXXXXXXXXXXXXXX">
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="settings-footer">
                    <div v-if="hasChanges" class="unsaved-indicator">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ t('admin.settings.unsavedChanges') }}
                    </div>
                    <button @click="saveSettings" class="btn btn-primary" :disabled="isLoading">
                        <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ t('admin.settings.save') }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.settings-layout { display: flex; gap: 1.5rem; }

/* Tabs */
.settings-tabs { width: 240px; flex-shrink: 0; background: white; border-radius: 0.75rem; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); height: fit-content; position: sticky; top: 80px; }
.tab-btn { display: flex; align-items: center; gap: 0.75rem; width: 100%; padding: 0.75rem 1rem; border: none; background: none; border-radius: 0.5rem; cursor: pointer; color: #6b7280; font-weight: 500; transition: all 0.2s; text-align: left; }
.tab-btn:hover { background: #f3f4f6; color: #374151; }
.tab-btn.active { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
.tab-btn i { width: 1.25rem; text-align: center; }
.tabs-divider { height: 1px; background: #e5e7eb; margin: 1rem 0; }
.tab-action { color: #f59e0b; }
.tab-action:hover { background: #fef3c7; }

/* Content */
.settings-content { flex: 1; }
.settings-panel { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.panel-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0 0 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
.panel-title i { color: #dc2626; }

/* Form */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 0.5rem; font-size: 0.875rem; }
.form-label i { margin-right: 0.5rem; color: #6b7280; }
.form-input, .form-select, .form-textarea { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }

.social-links { display: flex; flex-direction: column; gap: 0.5rem; }
.social-input .form-label i { font-size: 1rem; }

/* Footer */
.settings-footer { display: flex; justify-content: flex-end; align-items: center; gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; }
.unsaved-indicator { display: flex; align-items: center; gap: 0.5rem; color: #f59e0b; font-size: 0.875rem; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; font-size: 0.875rem; }
.btn-primary { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
.btn-primary:hover:not(:disabled) { box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }

/* Responsive */
@media (max-width: 1024px) {
    .settings-layout { flex-direction: column; }
    .settings-tabs { width: 100%; position: static; display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem; }
    .tab-btn { flex: 1; min-width: 120px; justify-content: center; }
    .tabs-divider { display: none; }
}

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .settings-footer { flex-direction: column; align-items: stretch; }
    .btn-primary { width: 100%; justify-content: center; }
    .tab-btn span { display: none; }
    .tab-btn { min-width: auto; padding: 0.75rem; }
}
</style>
