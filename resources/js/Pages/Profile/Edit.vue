<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('profile.profile')" />

        <div class="profile-page">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-cog"></i>
                    {{ $t('profile.profile_settings') }}
                </h1>
                <p class="page-subtitle">{{ $t('profile.manage_your_account') }}</p>
            </div>

            <div class="profile-grid">
                <!-- Profile Information -->
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />

                <!-- Update Password -->
                <UpdatePasswordForm />

                <!-- Delete Account -->
                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.profile-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-title i {
    color: #dc2626;
}

.page-subtitle {
    font-size: 1rem;
    color: #6b7280;
}

.profile-grid {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

@media (max-width: 768px) {
    .profile-page {
        padding: 1rem;
    }

    .page-title {
        font-size: 1.5rem;
    }
}
</style>
