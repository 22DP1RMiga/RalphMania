<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    status: String,
});

const { t } = useI18n();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head :title="$t('auth.verify_email')" />

    <div class="auth-container">
        <div class="auth-left">
            <div class="auth-brand">
                <img src="/img/RoltonsLV_Icon.png" alt="RalphMania" class="brand-icon">
                <img src="/img/name_logo.png" alt="RalphMania" class="brand-name">
            </div>
            <h2 class="auth-tagline">{{ $t('auth.verify_email_title') }}</h2>
            <p class="auth-description">{{ $t('auth.verify_email_description') }}</p>
        </div>

        <div class="auth-right">
            <div class="auth-form-container">
                <h1 class="auth-title">{{ $t('auth.verify_email') }}</h1>

                <p class="auth-help-text">{{ $t('auth.verify_email_help') }}</p>

                <div v-if="verificationLinkSent" class="alert alert-success">
                    {{ $t('auth.verification_link_sent') }}
                </div>

                <form @submit.prevent="submit" class="auth-form">
                    <button type="submit" class="btn-submit" :disabled="form.processing">
                        <span v-if="!form.processing">{{ $t('auth.resend_verification') }}</span>
                        <span v-else class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            {{ $t('common.sending') }}
                        </span>
                    </button>

                    <div class="form-footer">
                        <Link :href="route('logout')" method="post" as="button" class="footer-link-center">
                            {{ $t('auth.logout') }}
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-container { display: flex; min-height: 100vh; }
.auth-left {
    flex: 1;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 4rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.auth-left::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.1;
}
.auth-brand { display: flex; align-items: center; gap: 1.5rem; margin-bottom: 3rem; z-index: 1; }
.brand-icon { width: 80px; height: 80px; object-fit: contain; }
.brand-name { height: 50px; object-fit: contain; }
.auth-tagline { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; z-index: 1; }
.auth-description { font-size: 1.125rem; opacity: 0.95; max-width: 400px; z-index: 1; }
.auth-right { flex: 1; background: white; display: flex; align-items: center; justify-content: center; padding: 2rem; }
.auth-form-container { width: 100%; max-width: 450px; }
.auth-title { font-size: 2rem; font-weight: 800; color: #111827; margin-bottom: 1rem; text-align: center; }
.auth-help-text { font-size: 0.875rem; color: #6b7280; text-align: center; margin-bottom: 2rem; line-height: 1.6; }
.alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
.alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
.auth-form { display: flex; flex-direction: column; gap: 1.5rem; }
.btn-submit {
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-submit:hover:not(:disabled) { background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.loading-spinner { display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.form-footer { text-align: center; margin-top: 1rem; }
.footer-link-center {
    font-size: 0.875rem;
    color: #dc2626;
    text-decoration: none;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
}
.footer-link-center:hover { color: #b91c1c; }
@media (max-width: 768px) {
    .auth-container { flex-direction: column; }
    .auth-left { padding: 3rem 2rem; }
    .auth-tagline { font-size: 2rem; }
    .brand-icon { width: 60px; height: 60px; }
    .brand-name { height: 40px; }
}
</style>
