<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { locale } = useI18n({ useScope: 'global' });
const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const props = defineProps({
    user:  { type: Object, required: true },
    roles: { type: Array,  default: () => [] },
});

// ── Main form ─────────────────────────────────────────────────────
const form = useForm({
    username:   props.user.username   || '',
    email:      props.user.email      || '',
    first_name: props.user.first_name || '',
    last_name:  props.user.last_name  || '',
    phone:      props.user.phone      || '',
    role_id:    props.user.role?.id   || '',
    is_active:  props.user.is_active  ?? true,
});

const submit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => successMsg.value = locale.value === 'lv' ? 'Izmaiņas saglabātas!' : 'Changes saved!',
    });
};

// ── Password reset form ───────────────────────────────────────────
const pwForm = useForm({ password: '', password_confirmation: '' });
const showPwForm = ref(false);
const pwSuccess = ref('');

const submitPassword = () => {
    pwForm.put(`/admin/users/${props.user.id}/reset-password`, {
        preserveScroll: true,
        onSuccess: () => {
            pwSuccess.value = locale.value === 'lv' ? 'Parole nomainīta!' : 'Password reset!';
            pwForm.reset();
            showPwForm.value = false;
            setTimeout(() => pwSuccess.value = '', 3000);
        },
    });
};

// ── Avatar ────────────────────────────────────────────────────────
const getUserAvatar = () => {
    if (!props.user.profile_picture) return '/img/default-avatar.png';
    if (props.user.profile_picture.startsWith('http')) return props.user.profile_picture;
    return `/storage/${props.user.profile_picture}`;
};

// ── Role helpers ──────────────────────────────────────────────────
const getRoleBadgeClass = (name) =>
    ({ administrator: 'role-admin', customer: 'role-user', courier: 'role-courier' }[name] || 'role-default');

// ── Messages ──────────────────────────────────────────────────────
const successMsg = ref('');
setTimeout(() => {
    if (page.props.flash?.success) successMsg.value = page.props.flash.success;
}, 50);

// ── Can modify ────────────────────────────────────────────────────
const canModify = computed(() => props.user.id !== currentUser.value?.id);
</script>

<template>
    <Head :title="`${locale === 'lv' ? 'Rediģēt' : 'Edit'}: ${user.username}`" />

    <AdminLayout>
        <template #title>
            <Link href="/admin/users" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </Link>
            {{ locale === 'lv' ? 'Rediģēt lietotāju' : 'Edit User' }}: {{ user.username }}
        </template>

        <!-- Flash -->
        <Transition name="slide-down">
            <div v-if="successMsg" class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ successMsg }}
            </div>
        </Transition>
        <Transition name="slide-down">
            <div v-if="pwSuccess" class="alert alert-success">
                <i class="fas fa-key"></i> {{ pwSuccess }}
            </div>
        </Transition>

        <div class="edit-layout">

            <!-- ── LEFT: Profile card ── -->
            <div class="edit-sidebar">
                <div class="profile-card">
                    <img :src="getUserAvatar()" class="profile-avatar" :alt="user.username">
                    <div class="profile-name">{{ user.username }}</div>
                    <div class="profile-email">{{ user.email }}</div>
                    <span :class="['role-badge', getRoleBadgeClass(user.role?.name)]">
                        {{ user.role?.name || '—' }}
                    </span>

                    <div class="profile-meta">
                        <div class="meta-row">
                            <i class="fas fa-calendar"></i>
                            <span>{{ locale === 'lv' ? 'Reģistrēts' : 'Registered' }}: {{ user.created_at ? new Date(user.created_at).toLocaleDateString() : '—' }}</span>
                        </div>
                        <div class="meta-row">
                            <i class="fas fa-clock"></i>
                            <span>{{ locale === 'lv' ? 'Pieslēdzies' : 'Last login' }}: {{ user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : '—' }}</span>
                        </div>
                        <div class="meta-row">
                            <i :class="user.email_verified_at ? 'fas fa-check-circle text-green' : 'fas fa-exclamation-circle text-red'"></i>
                            <span>{{ user.email_verified_at
                                ? (locale === 'lv' ? 'E-pasts verificēts' : 'Email verified')
                                : (locale === 'lv' ? 'Nav verificēts' : 'Unverified') }}</span>
                        </div>
                    </div>

                    <div class="sidebar-links">
                        <Link :href="`/admin/users/${user.id}`" class="sidebar-btn">
                            <i class="fas fa-eye"></i>
                            {{ locale === 'lv' ? 'Skatīt profilu' : 'View Profile' }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT: Forms ── -->
            <div class="edit-main">

                <!-- General info form -->
                <div class="form-card">
                    <h2 class="form-card-title">
                        <i class="fas fa-user-edit"></i>
                        {{ locale === 'lv' ? 'Vispārīgā informācija' : 'General Information' }}
                    </h2>

                    <form @submit.prevent="submit">
                        <div class="form-grid">
                            <!-- Username -->
                            <div class="form-group">
                                <label class="form-label">
                                    {{ locale === 'lv' ? 'Lietotājvārds' : 'Username' }}
                                    <span class="required">*</span>
                                </label>
                                <input
                                    v-model="form.username"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'input-error': form.errors.username }"
                                    maxlength="50"
                                    required
                                >
                                <span v-if="form.errors.username" class="error-msg">{{ form.errors.username }}</span>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label class="form-label">
                                    E-pasts <span class="required">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-input"
                                    :class="{ 'input-error': form.errors.email }"
                                    required
                                >
                                <span v-if="form.errors.email" class="error-msg">{{ form.errors.email }}</span>
                            </div>

                            <!-- First name -->
                            <div class="form-group">
                                <label class="form-label">{{ locale === 'lv' ? 'Vārds' : 'First Name' }}</label>
                                <input v-model="form.first_name" type="text" class="form-input" maxlength="50">
                            </div>

                            <!-- Last name -->
                            <div class="form-group">
                                <label class="form-label">{{ locale === 'lv' ? 'Uzvārds' : 'Last Name' }}</label>
                                <input v-model="form.last_name" type="text" class="form-input" maxlength="50">
                            </div>

                            <!-- Phone -->
                            <div class="form-group">
                                <label class="form-label">{{ locale === 'lv' ? 'Tālrunis' : 'Phone' }}</label>
                                <input v-model="form.phone" type="tel" class="form-input" maxlength="20">
                            </div>

                            <!-- Role -->
                            <div class="form-group">
                                <label class="form-label">
                                    {{ locale === 'lv' ? 'Loma' : 'Role' }}
                                    <span class="required">*</span>
                                </label>
                                <select
                                    v-model="form.role_id"
                                    class="form-input"
                                    :class="{ 'input-error': form.errors.role_id }"
                                    :disabled="!canModify"
                                    required
                                >
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <span v-if="form.errors.role_id" class="error-msg">{{ form.errors.role_id }}</span>
                                <span v-if="!canModify" class="hint-msg">
                                    <i class="fas fa-info-circle"></i>
                                    {{ locale === 'lv' ? 'Nevar mainīt savas lomas' : 'Cannot change your own role' }}
                                </span>
                            </div>
                        </div>

                        <!-- Active toggle -->
                        <div class="toggle-row" v-if="canModify">
                            <label class="toggle-label">
                                <div class="toggle-switch" :class="{ on: form.is_active }" @click="form.is_active = !form.is_active">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div>
                                    <span class="toggle-text">{{ locale === 'lv' ? 'Konta statuss' : 'Account Status' }}</span>
                                    <span :class="['toggle-status', form.is_active ? 'status-on' : 'status-off']">
                                        {{ form.is_active
                                            ? (locale === 'lv' ? 'Aktīvs' : 'Active')
                                            : (locale === 'lv' ? 'Bloķēts' : 'Banned') }}
                                    </span>
                                </div>
                            </label>
                        </div>

                        <div class="form-footer">
                            <Link href="/admin/users" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                            </Link>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <i :class="form.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                                {{ form.processing
                                    ? (locale === 'lv' ? 'Saglabā...' : 'Saving...')
                                    : (locale === 'lv' ? 'Saglabāt izmaiņas' : 'Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password reset card -->
                <div class="form-card">
                    <div class="pw-header" @click="showPwForm = !showPwForm">
                        <h2 class="form-card-title" style="margin:0">
                            <i class="fas fa-key"></i>
                            {{ locale === 'lv' ? 'Paroles nomaiņa' : 'Reset Password' }}
                        </h2>
                        <i :class="['fas', showPwForm ? 'fa-chevron-up' : 'fa-chevron-down', 'pw-chevron']"></i>
                    </div>

                    <Transition name="slide-down">
                        <form v-if="showPwForm" @submit.prevent="submitPassword" class="pw-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        {{ locale === 'lv' ? 'Jaunā parole' : 'New Password' }}
                                        <span class="required">*</span>
                                    </label>
                                    <input
                                        v-model="pwForm.password"
                                        type="password"
                                        class="form-input"
                                        :class="{ 'input-error': pwForm.errors.password }"
                                        minlength="8"
                                        required
                                    >
                                    <span v-if="pwForm.errors.password" class="error-msg">{{ pwForm.errors.password }}</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        {{ locale === 'lv' ? 'Apstiprināt paroli' : 'Confirm Password' }}
                                        <span class="required">*</span>
                                    </label>
                                    <input
                                        v-model="pwForm.password_confirmation"
                                        type="password"
                                        class="form-input"
                                        :class="{ 'input-error': pwForm.errors.password_confirmation }"
                                        minlength="8"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="danger-box" style="margin-bottom: 1rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>{{ locale === 'lv' ? 'Uzmanību' : 'Warning' }}</strong>
                                    <p>{{ locale === 'lv'
                                        ? 'Lietotājs tiks izrakstīts un viņam būs jāpiesakās ar jauno paroli.'
                                        : 'The user will be logged out and must use the new password to sign in.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button type="button" @click="showPwForm = false" class="btn btn-secondary">
                                    {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                                </button>
                                <button type="submit" class="btn btn-danger" :disabled="pwForm.processing">
                                    <i :class="pwForm.processing ? 'fas fa-spinner fa-spin' : 'fas fa-key'"></i>
                                    {{ pwForm.processing
                                        ? (locale === 'lv' ? 'Nomaina...' : 'Resetting...')
                                        : (locale === 'lv' ? 'Nomainīt paroli' : 'Reset Password') }}
                                </button>
                            </div>
                        </form>
                    </Transition>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* ── Layout ── */
.edit-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
    align-items: start;
}

/* ── Sidebar profile card ── */
.edit-sidebar { position: sticky; top: 1.5rem; }
.profile-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.625rem;
    text-align: center;
}
.profile-avatar { width: 5rem; height: 5rem; border-radius: 50%; object-fit: cover; border: 3px solid #fee2e2; }
.profile-name  { font-size: 1rem; font-weight: 700; color: #111827; }
.profile-email { font-size: 0.8rem; color: #6b7280; word-break: break-all; }

.profile-meta { width: 100%; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
.meta-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #6b7280; }
.meta-row i { width: 1rem; text-align: center; }
.text-green { color: #059669; }
.text-red   { color: #dc2626; }

.sidebar-links { width: 100%; margin-top: 0.5rem; }
.sidebar-btn {
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    width: 100%; padding: 0.5rem; border-radius: 0.5rem;
    background: #f9fafb; border: 1px solid #e5e7eb;
    font-size: 0.8rem; color: #374151; text-decoration: none;
    transition: all 0.15s;
}
.sidebar-btn:hover { background: #f3f4f6; }

/* Role badge */
.role-badge { padding: 0.25rem 0.875rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; }
.role-admin   { background: #fef3c7; color: #92400e; }
.role-user    { background: #dbeafe; color: #1e40af; }
.role-courier { background: #d1fae5; color: #065f46; }
.role-default { background: #f3f4f6; color: #374151; }

/* ── Form card ── */
.form-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.form-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}
.form-card-title i { color: #dc2626; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }

.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-label { font-size: 0.8rem; font-weight: 600; color: #374151; }
.required   { color: #dc2626; }
.form-input {
    padding: 0.625rem 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
    background: white;
    width: 100%;
    box-sizing: border-box;
}
.form-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.08); }
.form-input:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; }
.input-error { border-color: #dc2626; }
.error-msg  { font-size: 0.75rem; color: #dc2626; }
.hint-msg   { font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 0.25rem; }

/* Toggle switch */
.toggle-row { margin-bottom: 1.25rem; }
.toggle-label { display: flex; align-items: center; gap: 0.875rem; cursor: pointer; }
.toggle-switch {
    width: 2.75rem; height: 1.5rem; border-radius: 9999px;
    background: #d1d5db; position: relative; transition: background 0.3s;
    flex-shrink: 0;
}
.toggle-switch.on { background: #059669; }
.toggle-knob {
    width: 1.125rem; height: 1.125rem; background: white; border-radius: 50%;
    position: absolute; top: 0.1875rem; left: 0.1875rem;
    transition: left 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.toggle-switch.on .toggle-knob { left: calc(100% - 1.3125rem); }
.toggle-text   { display: block; font-size: 0.875rem; font-weight: 600; color: #111827; }
.toggle-status { display: inline-block; padding: 0.15rem 0.625rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; margin-top: 0.125rem; }
.status-on  { background: #d1fae5; color: #065f46; }
.status-off { background: #fee2e2; color: #991b1b; }

/* Form footer */
.form-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid #f3f4f6; margin-top: 0.5rem; }

/* Password section */
.pw-header { display: flex; justify-content: space-between; align-items: center; cursor: pointer; margin-bottom: 0; }
.pw-chevron { color: #9ca3af; transition: transform 0.2s; }
.pw-form { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #f3f4f6; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; }
.btn-primary { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
.btn-primary:hover:not(:disabled) { box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-danger { background: #dc2626; color: white; }
.btn-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }

/* Alert */
.alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 0.75rem; margin-bottom: 1.25rem; font-weight: 500; }
.alert-success { background: #d1fae5; color: #065f46; }
.alert-error   { background: #fee2e2; color: #991b1b; }

/* Danger box */
.danger-box { display: flex; gap: 1rem; padding: 1rem; border-radius: 0.75rem; background: #fff7ed; color: #92400e; }
.danger-box i { font-size: 1.25rem; flex-shrink: 0; margin-top: 0.125rem; }
.danger-box strong { display: block; margin-bottom: 0.25rem; font-size: 0.875rem; }
.danger-box p { margin: 0; font-size: 0.8rem; opacity: 0.9; }

/* Back link */
.back-link { color: #6b7280; text-decoration: none; margin-right: 0.5rem; }
.back-link:hover { color: #111827; }

/* Transitions */
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease; max-height: 500px; overflow: hidden; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; max-height: 0; transform: translateY(-8px); }

/* ══════════ RESPONSIVE ══════════ */
@media (max-width: 1024px) {
    .edit-layout { grid-template-columns: 240px 1fr; }
}

@media (max-width: 768px) {
    .edit-layout { grid-template-columns: 1fr; }
    .edit-sidebar { position: static; }
    .profile-card { flex-direction: row; text-align: left; align-items: flex-start; flex-wrap: wrap; }
    .profile-avatar { width: 4rem; height: 4rem; }
    .profile-meta { margin-top: 0; }
    .sidebar-links { margin-top: 0; }
    .form-grid { grid-template-columns: 1fr; }
    .form-footer { flex-direction: column; }
    .form-footer .btn { width: 100%; justify-content: center; }
}

@media (max-width: 480px) {
    .profile-card { flex-direction: column; align-items: center; text-align: center; }
    .form-card { padding: 1rem; }
}
</style>
