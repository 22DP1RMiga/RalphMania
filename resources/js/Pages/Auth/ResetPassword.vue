<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    email: String,
    token: String,
});

const { t } = useI18n();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="$t('auth.reset_password')" />

    <div class="auth-container">
        <!-- Left Side - Branding -->
        <div class="auth-left">
            <div class="auth-brand">
                <img src="/img/RoltonsLV_Icon.png" alt="RalphMania" class="brand-icon">
                <img src="/img/name_logo.png" alt="RalphMania" class="brand-name">
            </div>
            <h2 class="auth-tagline">{{ $t('auth.new_password_title') }}</h2>
            <p class="auth-description">{{ $t('auth.new_password_description') }}</p>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-right">
            <div class="auth-form-container">
                <h1 class="auth-title">{{ $t('auth.reset_password') }}</h1>

                <form @submit.prevent="submit" class="auth-form">
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            {{ $t('auth.email') }}
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="form-input"
                            :class="{ 'input-error': form.errors.email }"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <div v-if="form.errors.email" class="error-message">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            {{ $t('auth.new_password') }}
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="form-input"
                            :class="{ 'input-error': form.errors.password }"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="form.errors.password" class="error-message">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">
                            {{ $t('auth.confirm_new_password') }}
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            class="form-input"
                            :class="{ 'input-error': form.errors.password_confirmation }"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="form.errors.password_confirmation" class="error-message">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn-submit"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">{{ $t('auth.reset_password') }}</span>
                        <span v-else class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            {{ $t('auth.resetting') }}
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-container {
    display: flex;
    min-height: 100vh;
}

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
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.1;
}

.auth-brand {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 3rem;
    z-index: 1;
}

.brand-icon {
    width: 80px;
    height: 80px;
    object-fit: contain;
}

.brand-name {
    height: 50px;
    object-fit: contain;
}

.auth-tagline {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    z-index: 1;
}

.auth-description {
    font-size: 1.125rem;
    opacity: 0.95;
    max-width: 400px;
    z-index: 1;
}

.auth-right {
    flex: 1;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.auth-form-container {
    width: 100%;
    max-width: 450px;
}

.auth-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 2rem;
    text-align: center;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.form-input {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.input-error {
    border-color: #ef4444;
}

.error-message {
    font-size: 0.875rem;
    color: #ef4444;
}

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
    margin-top: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .auth-container {
        flex-direction: column;
    }

    .auth-left {
        padding: 3rem 2rem;
    }

    .auth-tagline {
        font-size: 2rem;
    }

    .brand-icon {
        width: 60px;
        height: 60px;
    }

    .brand-name {
        height: 40px;
    }
}
</style>
