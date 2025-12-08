<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="profile-section">
        <header class="section-header">
            <div class="header-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <h2 class="section-title">{{ $t('profile.update_password') }}</h2>
                <p class="section-description">{{ $t('profile.update_password_description') }}</p>
            </div>
        </header>

        <form @submit.prevent="updatePassword" class="profile-form">
            <!-- Current Password -->
            <div class="form-row">
                <label for="current_password" class="form-label">
                    <i class="fas fa-key"></i>
                    {{ $t('profile.current_password') }}
                </label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="form-input"
                    :class="{ 'input-error': form.errors.current_password }"
                    autocomplete="current-password"
                />
                <div v-if="form.errors.current_password" class="error-message">
                    {{ form.errors.current_password }}
                </div>
            </div>

            <!-- New Password -->
            <div class="form-row">
                <label for="password" class="form-label">
                    <i class="fas fa-shield-alt"></i>
                    {{ $t('profile.new_password') }}
                </label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="form-input"
                    :class="{ 'input-error': form.errors.password }"
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password" class="error-message">
                    {{ form.errors.password }}
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-row">
                <label for="password_confirmation" class="form-label">
                    <i class="fas fa-shield-alt"></i>
                    {{ $t('profile.confirm_new_password') }}
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="form-input"
                    :class="{ 'input-error': form.errors.password_confirmation }"
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password_confirmation" class="error-message">
                    {{ form.errors.password_confirmation }}
                </div>
            </div>

            <!-- Success Message -->
            <Transition name="fade">
                <div v-if="form.recentlySuccessful" class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ $t('profile.password_updated') }}
                </div>
            </Transition>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <span v-if="!form.processing">
                        <i class="fas fa-save"></i>
                        {{ $t('common.save') }}
                    </span>
                    <span v-else>
                        <i class="fas fa-spinner fa-spin"></i>
                        {{ $t('common.saving') }}
                    </span>
                </button>
            </div>
        </form>
    </section>
</template>

<style scoped>
.profile-section { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #dc2626; }
.section-header { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 2rem; }
.header-icon { width: 3rem; height: 3rem; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
.section-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.25rem; }
.section-description { font-size: 0.875rem; color: #6b7280; }
.profile-form { display: flex; flex-direction: column; gap: 1.5rem; }
.form-row { display: flex; flex-direction: column; gap: 0.5rem; }
.form-label { font-size: 0.875rem; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 0.5rem; }
.form-label i { color: #dc2626; width: 1rem; }
.form-input { padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 1rem; transition: all 0.3s ease; }
.form-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
.form-input.input-error { border-color: #ef4444; }
.error-message { font-size: 0.875rem; color: #ef4444; }
.alert { padding: 1rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.75rem; }
.alert i { font-size: 1.25rem; }
.alert-success { background: #d1fae5; border: 1px solid #10b981; color: #065f46; }
.form-actions { display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb; }
.btn-primary { padding: 0.75rem 2rem; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem; }
.btn-primary:hover:not(:disabled) { background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
