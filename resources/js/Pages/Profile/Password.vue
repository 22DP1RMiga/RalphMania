<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="t('profile.update_password')" />

        <div class="password-page">
            <!-- Breadcrumbs -->
            <div class="breadcrumbs">
                <Link href="/dashboard" class="breadcrumb-item">
                    <i class="fas fa-th-large"></i>
                    {{ t('nav.dashboard') }}
                </Link>
                <i class="fas fa-chevron-right breadcrumb-separator"></i>
                <span class="breadcrumb-item breadcrumb-current">
                    <i class="fas fa-key"></i>
                    {{ t('profile.update_password') }}
                </span>
            </div>

            <!-- Password Form -->
            <UpdatePasswordForm />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.password-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

/* Breadcrumbs */
.breadcrumbs {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 2rem;
    padding: 1rem 1.5rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.breadcrumb-item i {
    font-size: 0.875rem;
}

.breadcrumb-item:hover:not(.breadcrumb-current) {
    color: #dc2626;
}

.breadcrumb-separator {
    color: #d1d5db;
    font-size: 0.75rem;
}

.breadcrumb-current {
    color: #dc2626;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .password-page {
        padding: 1rem;
    }

    .breadcrumbs {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }

    .breadcrumb-item span {
        display: none;
    }
}
</style>
