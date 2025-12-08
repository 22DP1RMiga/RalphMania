<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value?.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <section class="profile-section danger-section">
        <header class="section-header">
            <div class="header-icon danger-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h2 class="section-title">{{ $t('profile.delete_account') }}</h2>
                <p class="section-description">{{ $t('profile.delete_account_description') }}</p>
            </div>
        </header>

        <div class="danger-warning">
            <i class="fas fa-info-circle"></i>
            <p>{{ $t('profile.delete_warning') }}</p>
        </div>

        <div class="form-actions">
            <button @click="confirmUserDeletion" class="btn-danger">
                <i class="fas fa-trash-alt"></i>
                {{ $t('profile.delete_account') }}
            </button>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="confirmingUserDeletion" class="modal-overlay" @click="closeModal">
                <div class="modal-content" @click.stop>
                    <div class="modal-header">
                        <div class="modal-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="modal-title">{{ $t('profile.confirm_deletion') }}</h3>
                        <button @click="closeModal" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p class="modal-text">{{ $t('profile.deletion_confirmation_text') }}</p>

                        <div class="form-row">
                            <label for="password" class="form-label">
                                {{ $t('profile.password') }}
                            </label>
                            <input
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="form-input"
                                :class="{ 'input-error': form.errors.password }"
                                :placeholder="$t('profile.password')"
                                @keyup.enter="deleteUser"
                            />
                            <div v-if="form.errors.password" class="error-message">
                                {{ form.errors.password }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModal" class="btn-secondary">
                            {{ $t('common.cancel') }}
                        </button>
                        <button @click="deleteUser" class="btn-danger" :disabled="form.processing">
                            <span v-if="!form.processing">
                                <i class="fas fa-trash-alt"></i>
                                {{ $t('profile.delete_account') }}
                            </span>
                            <span v-else>
                                <i class="fas fa-spinner fa-spin"></i>
                                {{ $t('common.deleting') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.profile-section { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.danger-section { border-left: 4px solid #ef4444; }
.section-header { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 2rem; }
.header-icon { width: 3rem; height: 3rem; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
.danger-icon { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.section-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.25rem; }
.section-description { font-size: 0.875rem; color: #6b7280; }
.danger-warning { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 1rem; display: flex; align-items: flex-start; gap: 0.75rem; color: #991b1b; margin-bottom: 1.5rem; }
.danger-warning i { font-size: 1.25rem; margin-top: 0.125rem; }
.form-actions { display: flex; justify-content: flex-end; }
.btn-danger { padding: 0.75rem 2rem; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem; }
.btn-danger:hover:not(:disabled) { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239,68,68,0.3); }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.modal-content { background: white; border-radius: 1rem; max-width: 500px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.modal-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-icon { width: 3rem; height: 3rem; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
.modal-title { flex: 1; font-size: 1.25rem; font-weight: 700; color: #111827; }
.modal-close { background: none; border: none; color: #6b7280; cursor: pointer; padding: 0.5rem; border-radius: 0.375rem; transition: all 0.3s ease; }
.modal-close:hover { background: #f3f4f6; color: #111827; }
.modal-body { padding: 1.5rem; }
.modal-text { color: #6b7280; margin-bottom: 1.5rem; line-height: 1.6; }
.form-row { display: flex; flex-direction: column; gap: 0.5rem; }
.form-label { font-size: 0.875rem; font-weight: 600; color: #374151; }
.form-input { padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 1rem; transition: all 0.3s ease; }
.form-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
.form-input.input-error { border-color: #ef4444; }
.error-message { font-size: 0.875rem; color: #ef4444; }
.modal-footer { display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; border-top: 1px solid #e5e7eb; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
.btn-secondary:hover { background: #e5e7eb; }

/* Modal Transition */
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-content, .modal-leave-active .modal-content { transition: transform 0.3s ease; }
.modal-enter-from .modal-content, .modal-leave-to .modal-content { transform: scale(0.9); }
</style>
