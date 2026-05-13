<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, watch, computed } from 'vue';

const { locale } = useI18n({ useScope: 'global' });
const currentLocale = ref(localStorage.getItem('lang') || 'lv');

// Sync Vue i18n locale with stored value on mount
locale.value = currentLocale.value;

// Reactively change locale when button clicked
watch(currentLocale, (newLang) => {
    locale.value = newLang;
    localStorage.setItem('lang', newLang);
});

// Toggle locale function
const toggleLocale = () => {
    currentLocale.value = currentLocale.value === 'lv' ? 'en' : 'lv';
};

// forma aizpildīšanai
const form = useForm({
    first_name: '',
    last_name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    locale: currentLocale.value,
});

// ── Paroles prasību pārbaude reāllaikā ───────────────────────────
const passwordChecks = computed(() => ({
    length:    form.password.length >= 8,
    lower:     /[a-z]/.test(form.password),
    upper:     /[A-Z]/.test(form.password),
    number:    /[0-9]/.test(form.password),
    symbol:    /[^a-zA-Z0-9]/.test(form.password),
    match:     form.password.length > 0 && form.password === form.password_confirmation,
}));

const passwordStrength = computed(() => {
    const checks = Object.values(passwordChecks.value);
    const passed = checks.filter(Boolean).length;
    if (passed <= 2) return 'weak';
    if (passed <= 4) return 'medium';
    return 'strong';
});

const showPasswordHints = computed(() => form.password.length > 0);

// ── E-pasta domēna pārbaude klientpusē ──────────────────────────
const ALLOWED_EMAIL_REGEX = /^[a-zA-Z0-9._%+\-]+@(gmail\.com|outlook\.com|outlook\.lv|hotmail\.com|hotmail\.lv|hotmail\.co\.uk|live\.com|live\.lv|yahoo\.com|yahoo\.co\.uk|yahoo\.lv|icloud\.com|me\.com|mac\.com|proton\.me|protonmail\.com|protonmail\.ch|zoho\.com|tutanota\.com|tuta\.io|gmx\.com|gmx\.net|gmx\.de|inbox\.com|aol\.com|yandex\.com|yandex\.ru|ya\.ru|inbox\.lv|apollo\.lv|tvnet\.lv|one\.lv|e-apollo\.lv)$/i;

const emailDomainValid = computed(() => {
    if (!form.email.includes('@')) return null; // nav ievadīts domēns vēl
    return ALLOWED_EMAIL_REGEX.test(form.email);
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="$t('auth.register')" />

    <div class="auth-container">
        <!-- Augšējā navigācijas josla -->
        <div class="auth-topbar">
            <!-- Home Button (Top Left) -->
            <Link href="/" class="home-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="home-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>{{ currentLocale === 'lv' ? 'Sākums' : 'Home' }}</span>
            </Link>

            <!-- Language Switcher (Top Right) -->
            <button @click="toggleLocale" class="locale-switcher">
                <span class="locale-current">{{ currentLocale.toUpperCase() }}</span>
                <span class="locale-divider">/</span>
                <span class="locale-other">{{ currentLocale === 'lv' ? 'EN' : 'LV' }}</span>
            </button>
        </div>

        <!-- Left Side - Branding -->
        <div class="auth-left">
            <div class="auth-brand">
                <img src="/img/RoltonsLV_Icon.png" alt="RalphMania" class="brand-icon">
                <img src="/img/name_logo2.png" alt="RalphMania" class="brand-name">
            </div>
            <h2 class="auth-tagline">{{ $t('auth.join_us') }}</h2>
            <p class="auth-description">{{ $t('auth.register_description') }}</p>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-right">
            <div class="auth-form-container">
                <h1 class="auth-title">{{ $t('auth.create_account') }}</h1>

                <form @submit.prevent="submit" class="auth-form">
                    <!-- ✅ First Name Field -->
                    <div class="form-group">
                        <label for="first_name" class="form-label">
                            {{ currentLocale === 'lv' ? 'Vārds' : 'First Name' }}
                        </label>
                        <input
                            id="first_name"
                            type="text"
                            v-model="form.first_name"
                            class="form-input"
                            :class="{ 'input-error': form.errors.first_name }"
                            :placeholder="currentLocale === 'lv' ? 'Piem., Alberts...' : 'E.g., Albert...'"
                            required
                            autofocus
                            autocomplete="given-name"
                        />
                        <div v-if="form.errors.first_name" class="error-message">
                            {{ form.errors.first_name }}
                        </div>
                    </div>

                    <!-- ✅ Last Name Field -->
                    <div class="form-group">
                        <label for="last_name" class="form-label">
                            {{ currentLocale === 'lv' ? 'Uzvārds' : 'Last Name' }}
                        </label>
                        <input
                            id="last_name"
                            type="text"
                            v-model="form.last_name"
                            class="form-input"
                            :class="{ 'input-error': form.errors.last_name }"
                            :placeholder="currentLocale === 'lv' ? 'Piem., Einšteins...' : 'E.g., Einstein...'"
                            required
                            autocomplete="family-name"
                        />
                        <div v-if="form.errors.last_name" class="error-message">
                            {{ form.errors.last_name }}
                        </div>
                    </div>

                    <!-- Username Field -->
                    <div class="form-group">
                        <label for="username" class="form-label">
                            {{ $t('auth.username') }}
                        </label>
                        <input
                            id="username"
                            type="text"
                            v-model="form.username"
                            class="form-input"
                            :class="{ 'input-error': form.errors.username }"
                            :placeholder="currentLocale === 'lv' ? 'Piem., MasterAlbert310...' : 'E.g., MasterAlbert310 ....'"
                            required
                            autocomplete="username"
                        />
                        <div v-if="form.errors.username" class="error-message">
                            {{ form.errors.username }}
                        </div>
                    </div>

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
                            :class="{
                                'input-error': form.errors.email || emailDomainValid === false,
                                'input-success': emailDomainValid === true
                            }"
                            :placeholder="currentLocale === 'lv' ? 'Piem., alberts.einsteins@gmail.com...' : 'E.g., albert.einstein@gmail.com ....'"
                            required
                            autocomplete="email"
                        />
                        <!-- E-pasta domēna indikators -->
                        <div v-if="emailDomainValid === false && !form.errors.email" class="hint-row hint-fail">
                            <i class="fas fa-times-circle"></i>
                            {{ currentLocale === 'lv'
                            ? 'Lūdzu izmantojiet atpazīstamu e-pasta pakalpojumu (Gmail, Outlook, iCloud u.c.)'
                            : 'Please use a recognised email provider (Gmail, Outlook, iCloud, etc.)' }}
                        </div>
                        <div v-else-if="emailDomainValid === true" class="hint-row hint-pass">
                            <i class="fas fa-check-circle"></i>
                            {{ currentLocale === 'lv' ? 'E-pasta formāts ir derīgs' : 'Email format is valid' }}
                        </div>
                        <div v-if="form.errors.email" class="error-message">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            {{ $t('auth.password') }}
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="form-input"
                            :class="{ 'input-error': form.errors.password }"
                            :placeholder="currentLocale === 'lv' ? 'Ievadiet paroli' : 'Enter password'"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="form.errors.password" class="error-message">
                            {{ form.errors.password }}
                        </div>

                        <!-- Paroles stipruma josla -->
                        <div v-if="showPasswordHints" class="password-strength-bar">
                            <div class="strength-track">
                                <div class="strength-fill" :class="`strength-${passwordStrength}`"></div>
                            </div>
                            <span class="strength-label" :class="`strength-${passwordStrength}`">
                                {{
                                    passwordStrength === 'weak'
                                        ? (currentLocale === 'lv' ? 'Vāja' : 'Weak')
                                        : passwordStrength === 'medium'
                                            ? (currentLocale === 'lv' ? 'Vidēja' : 'Medium')
                                            : (currentLocale === 'lv' ? 'Stipra' : 'Strong')
                                }}
                            </span>
                        </div>

                        <!-- Prasību saraksts -->
                        <ul v-if="showPasswordHints" class="password-requirements">
                            <li :class="passwordChecks.length ? 'req-pass' : 'req-fail'">
                                <i :class="passwordChecks.length ? 'fas fa-check-circle' : 'fas fa-circle'"></i>
                                {{ currentLocale === 'lv' ? 'Vismaz 8 simboli' : 'At least 8 characters' }}
                            </li>
                            <li :class="passwordChecks.lower ? 'req-pass' : 'req-fail'">
                                <i :class="passwordChecks.lower ? 'fas fa-check-circle' : 'fas fa-circle'"></i>
                                {{ currentLocale === 'lv' ? 'Vismaz 1 mazais burts (a–z)' : 'At least 1 lowercase letter (a–z)' }}
                            </li>
                            <li :class="passwordChecks.upper ? 'req-pass' : 'req-fail'">
                                <i :class="passwordChecks.upper ? 'fas fa-check-circle' : 'fas fa-circle'"></i>
                                {{ currentLocale === 'lv' ? 'Vismaz 1 lielais burts (A–Z)' : 'At least 1 uppercase letter (A–Z)' }}
                            </li>
                            <li :class="passwordChecks.number ? 'req-pass' : 'req-fail'">
                                <i :class="passwordChecks.number ? 'fas fa-check-circle' : 'fas fa-circle'"></i>
                                {{ currentLocale === 'lv' ? 'Vismaz 1 cipars (0–9)' : 'At least 1 number (0–9)' }}
                            </li>
                            <li :class="passwordChecks.symbol ? 'req-pass' : 'req-fail'">
                                <i :class="passwordChecks.symbol ? 'fas fa-check-circle' : 'fas fa-circle'"></i>
                                {{ currentLocale === 'lv' ? 'Vismaz 1 speciālais simbols (!, @, #, $ …)' : 'At least 1 special character (!, @, #, $ …)' }}
                            </li>
                        </ul>
                    </div>

                    <!-- Password Confirmation Field -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">
                            {{ $t('auth.confirm_password') }}
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            class="form-input"
                            :class="{
                                'input-error': form.errors.password_confirmation || (form.password_confirmation.length > 0 && !passwordChecks.match),
                                'input-success': passwordChecks.match
                            }"
                            :placeholder="currentLocale === 'lv' ? 'Atkārtoti ievadiet paroli...' : 'Re-enter password...'"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="form.password_confirmation.length > 0 && !passwordChecks.match && !form.errors.password_confirmation" class="hint-row hint-fail">
                            <i class="fas fa-times-circle"></i>
                            {{ currentLocale === 'lv' ? 'Paroles nesakrīt' : 'Passwords do not match' }}
                        </div>
                        <div v-else-if="passwordChecks.match" class="hint-row hint-pass">
                            <i class="fas fa-check-circle"></i>
                            {{ currentLocale === 'lv' ? 'Paroles sakrīt' : 'Passwords match' }}
                        </div>
                        <div v-if="form.errors.password_confirmation" class="error-message">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn-primary"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? $t('auth.registering') : $t('auth.register') }}
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="form-footer">
                        <p class="text-center">
                            {{ $t('auth.already_registered') }}
                            <Link href="/login" class="auth-link">
                                {{ $t('auth.login') }}
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-container {
    display: flex;
    min-height: 100vh;
    position: relative;
}

/* Augšējā navigācijas josla */
.auth-topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 70px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 40px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    z-index: 1000;
}

/* Home Button (Top Left) */
.home-button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #f3f4f6;
    border-radius: 8px;
    text-decoration: none;
    color: #374151;
    font-weight: 500;
    transition: all 0.3s ease;
}

.home-button:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.home-icon {
    width: 20px;
    height: 20px;
}

/* Language Switcher (Top Right) */
.locale-switcher {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 20px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.locale-switcher:hover {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.locale-current {
    color: white;
}

.locale-divider {
    color: rgba(255, 255, 255, 0.5);
}

.locale-other {
    color: rgba(255, 255, 255, 0.7);
}

/* Left Side - Branding */
.auth-left {
    flex: 1;
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    padding: 120px 80px 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
}

.auth-brand {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
}

.brand-icon {
    width: 80px;
    height: 80px;
}

.brand-name {
    height: 60px;
}

.auth-tagline {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 16px;
}

.auth-description {
    font-size: 18px;
    opacity: 0.9;
    max-width: 400px;
}

/* Right Side - Form */
.auth-right {
    flex: 1;
    padding: 120px 80px 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
}

.auth-form-container {
    width: 100%;
    max-width: 500px;
}

.auth-title {
    font-size: 28px;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 32px;
    text-align: center;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.form-input {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.input-error {
    border-color: #ef4444;
}

.input-success {
    border-color: #10b981;
}

.error-message {
    color: #ef4444;
    font-size: 13px;
    margin-top: 4px;
}

/* ── E-pasta / lauka hints ── */
.hint-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    margin-top: 5px;
    font-weight: 500;
}
.hint-pass { color: #059669; }
.hint-fail { color: #dc2626; }

/* ── Paroles stipruma josla ── */
.password-strength-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}
.strength-track {
    flex: 1;
    height: 5px;
    background: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
}
.strength-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.3s ease, background 0.3s ease;
}
.strength-fill.strength-weak   { width: 33%;  background: #ef4444; }
.strength-fill.strength-medium { width: 66%;  background: #f59e0b; }
.strength-fill.strength-strong { width: 100%; background: #10b981; }
.strength-label { font-size: 11px; font-weight: 700; white-space: nowrap; }
.strength-label.strength-weak   { color: #ef4444; }
.strength-label.strength-medium { color: #f59e0b; }
.strength-label.strength-strong { color: #10b981; }

/* ── Paroles prasību saraksts ── */
.password-requirements {
    list-style: none;
    padding: 8px 0 0 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.password-requirements li {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    transition: color 0.2s;
}
.req-pass { color: #059669; }
.req-fail { color: #9ca3af; }
.req-pass i { color: #10b981; }
.req-fail i { color: #d1d5db; font-size: 10px; }

.form-actions {
    margin-top: 8px;
}

.btn-primary {
    width: 100%;
    padding: 14px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.form-footer {
    margin-top: 24px;
    text-align: center;
}

.text-center {
    color: #6b7280;
    font-size: 14px;
}

.auth-link {
    color: #dc2626;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.auth-link:hover {
    color: #b91c1c;
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 1024px) {
    .auth-container {
        flex-direction: column;
    }

    .auth-left,
    .auth-right {
        padding: 100px 40px 60px;
    }

    .auth-topbar {
        padding: 0 20px;
    }
}

@media (max-width: 640px) {
    .auth-topbar {
        height: 60px;
    }

    .home-button,
    .locale-switcher {
        padding: 8px 16px;
        font-size: 13px;
    }

    .home-icon {
        width: 18px;
        height: 18px;
    }

    .home-button span {
        display: none;
    }

    .auth-left {
        padding: 80px 24px 40px;
    }

    .auth-right {
        padding: 40px 24px;
    }

    .brand-icon {
        width: 60px;
        height: 60px;
    }

    .brand-name {
        height: 45px;
    }

    .auth-tagline {
        font-size: 24px;
    }

    .auth-description {
        font-size: 16px;
    }
}
</style>
