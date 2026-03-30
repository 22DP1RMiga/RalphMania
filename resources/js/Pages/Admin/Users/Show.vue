<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const props = defineProps({
    user: { type: Object, required: true },
    newsletterStatus: { type: Object, default: null }, // { subscribed, expires_at, is_active }
});

// ─── HELPERS ─────────────────────────────────────────────────────────────────
const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const formatPrice = (p) => parseFloat(p || 0).toFixed(2);

const getUserAvatar = () => {
    if (!props.user.profile_picture) return '/img/default-avatar.png';
    if (props.user.profile_picture.startsWith('http')) return props.user.profile_picture;
    return `/storage/${props.user.profile_picture}`;
};

const getRoleBadgeClass = (roleName) =>
    ({ administrator: 'role-admin', user: 'role-user', courier: 'role-courier' }[roleName] || 'role-default');

const canModify = computed(() => props.user.id !== currentUser.value?.id);

// ─── ACTIONS ─────────────────────────────────────────────────────────────────
const isLoading = ref(false);
const successMsg = ref('');
const errorMsg = ref('');

const showBanConfirm = ref(false);
const showDeleteConfirm = ref(false);
const banReason = ref('');

const toggleActive = () => {
    isLoading.value = true;
    router.put(`/admin/users/${props.user.id}/toggle-active`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showBanConfirm.value = false;
            successMsg.value = props.user.is_active
                ? (locale.value === 'lv' ? 'Lietotājs bloķēts.' : 'User banned.')
                : (locale.value === 'lv' ? 'Lietotājs atbloķēts.' : 'User unbanned.');
            setTimeout(() => successMsg.value = '', 3000);
        },
        onError: (e) => { errorMsg.value = Object.values(e)[0] || 'Kļūda.'; },
        onFinish: () => { isLoading.value = false; },
    });
};

const deleteUser = () => {
    isLoading.value = true;
    router.delete(`/admin/users/${props.user.id}`, {
        onSuccess: () => router.visit('/admin/users'),
        onError: (e) => { errorMsg.value = Object.values(e)[0] || 'Kļūda.'; isLoading.value = false; },
    });
};

const openMailto = () => {
    window.open(`mailto:${props.user.email}`, '_blank');
};

// ─── TABS ─────────────────────────────────────────────────────────────────────
const activeTab = ref('overview');
</script>

<template>
    <Head :title="`${locale === 'lv' ? 'Lietotājs' : 'User'}: ${user.username}`" />

    <AdminLayout>
        <template #title>
            <Link href="/admin/users" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </Link>
            {{ user.username }}
        </template>

        <!-- Flash messages -->
        <Transition name="slide-down">
            <div v-if="successMsg" class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ successMsg }}
            </div>
        </Transition>
        <Transition name="slide-down">
            <div v-if="errorMsg" class="alert alert-error">
                <i class="fas fa-times-circle"></i> {{ errorMsg }}
            </div>
        </Transition>

        <div class="user-show">

            <!-- ─── PROFILE HERO ─── -->
            <div class="profile-hero">
                <div class="hero-left">
                    <div class="avatar-wrapper">
                        <img :src="getUserAvatar()" :alt="user.username" class="profile-avatar" />
                        <span class="avatar-status" :class="user.is_active ? 'online' : 'offline'"></span>
                    </div>
                    <div class="hero-info">
                        <div class="hero-name">
                            {{ user.first_name || user.last_name
                            ? `${user.first_name || ''} ${user.last_name || ''}`.trim()
                            : user.username }}
                        </div>
                        <div class="hero-username">@{{ user.username }}</div>
                        <div class="hero-meta">
                            <span :class="['role-badge', getRoleBadgeClass(user.role?.name)]">
                                {{ user.role?.name || 'user' }}
                            </span>
                            <span v-if="user.email_verified_at" class="verified-tag">
                                <i class="fas fa-check-circle"></i>
                                {{ locale === 'lv' ? 'Verificēts' : 'Verified' }}
                            </span>
                            <span v-else class="unverified-tag">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ locale === 'lv' ? 'Nav verificēts' : 'Unverified' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="hero-right">
                    <div class="hero-stats">
                        <div class="hstat">
                            <span class="hstat-val">{{ user.orders_count ?? 0 }}</span>
                            <span class="hstat-lbl">{{ locale === 'lv' ? 'Pasūtījumi' : 'Orders' }}</span>
                        </div>
                        <div class="hstat">
                            <span class="hstat-val">{{ formatPrice(user.total_spent) }}€</span>
                            <span class="hstat-lbl">{{ locale === 'lv' ? 'Iztērēts' : 'Spent' }}</span>
                        </div>
                        <div class="hstat">
                            <span class="hstat-val">{{ user.reviews_count ?? 0 }}</span>
                            <span class="hstat-lbl">{{ locale === 'lv' ? 'Atsauksmes' : 'Reviews' }}</span>
                        </div>
                        <div class="hstat">
                            <span class="hstat-val">{{ user.comments_count ?? 0 }}</span>
                            <span class="hstat-lbl">{{ locale === 'lv' ? 'Komentāri' : 'Comments' }}</span>
                        </div>
                    </div>

                    <div class="hero-actions" v-if="canModify">
                        <Link :href="`/admin/users/${user.id}/edit`" class="btn btn-outline">
                            <i class="fas fa-edit"></i>
                            {{ locale === 'lv' ? 'Rediģēt' : 'Edit' }}
                        </Link>
                        <button @click="openMailto" class="btn btn-outline">
                            <i class="fas fa-envelope"></i>
                            {{ locale === 'lv' ? 'Rakstīt e-pastu' : 'Send Email' }}
                        </button>
                        <button
                            @click="showBanConfirm = true"
                            :class="['btn', user.is_active ? 'btn-warn' : 'btn-success-sm']"
                        >
                            <i :class="user.is_active ? 'fas fa-ban' : 'fas fa-check'"></i>
                            {{ user.is_active
                            ? (locale === 'lv' ? 'Bloķēt' : 'Ban')
                            : (locale === 'lv' ? 'Atbloķēt' : 'Unban') }}
                        </button>
                        <button @click="showDeleteConfirm = true" class="btn btn-danger-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ─── TABS ─── -->
            <div class="tabs-bar">
                <button
                    v-for="tab in [
                        { id: 'overview', icon: 'fas fa-user', lv: 'Pārskats', en: 'Overview' },
                        { id: 'address',  icon: 'fas fa-map-marker-alt', lv: 'Adrese', en: 'Address' },
                        { id: 'activity', icon: 'fas fa-history', lv: 'Aktivitāte', en: 'Activity' },
                        { id: 'newsletter', icon: 'fas fa-envelope', lv: 'Abonements', en: 'Newsletter' },
                    ]"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="['tab-btn', activeTab === tab.id ? 'active' : '']"
                >
                    <i :class="tab.icon"></i>
                    {{ locale === 'lv' ? tab.lv : tab.en }}
                </button>
            </div>

            <!-- ─── OVERVIEW TAB ─── -->
            <div v-if="activeTab === 'overview'" class="tab-content">
                <div class="detail-grid">

                    <!-- Personal info -->
                    <div class="info-card">
                        <h3 class="card-heading">
                            <i class="fas fa-id-card"></i>
                            {{ locale === 'lv' ? 'Personas dati' : 'Personal Info' }}
                        </h3>
                        <div class="info-rows">
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Lietotājvārds' : 'Username' }}</span>
                                <span class="ivalue">{{ user.username }}</span>
                            </div>
                            <div class="info-row" v-if="user.first_name || user.last_name">
                                <span class="ilabel">{{ locale === 'lv' ? 'Vārds Uzvārds' : 'Full Name' }}</span>
                                <span class="ivalue">{{ user.first_name }} {{ user.last_name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">E-pasts</span>
                                <span class="ivalue">
                                    <a :href="`mailto:${user.email}`" class="link-red">{{ user.email }}</a>
                                    <i v-if="user.email_verified_at" class="fas fa-check-circle verified-icon" :title="formatDate(user.email_verified_at)"></i>
                                </span>
                            </div>
                            <div class="info-row" v-if="user.phone">
                                <span class="ilabel">{{ locale === 'lv' ? 'Tālrunis' : 'Phone' }}</span>
                                <span class="ivalue">
                                    <a :href="`tel:${user.phone}`" class="link-red">{{ user.phone }}</a>
                                </span>
                            </div>
                            <div class="info-row" v-if="user.birth_date">
                                <span class="ilabel">{{ locale === 'lv' ? 'Dzimšanas datums' : 'Birth Date' }}</span>
                                <span class="ivalue">{{ formatDate(user.birth_date) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Loma' : 'Role' }}</span>
                                <span class="ivalue">
                                    <span :class="['role-badge', getRoleBadgeClass(user.role?.name)]">
                                        {{ user.role?.name || '—' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Account info -->
                    <div class="info-card">
                        <h3 class="card-heading">
                            <i class="fas fa-shield-alt"></i>
                            {{ locale === 'lv' ? 'Konta dati' : 'Account Details' }}
                        </h3>
                        <div class="info-rows">
                            <div class="info-row">
                                <span class="ilabel">ID</span>
                                <span class="ivalue mono">#{{ user.id }}</span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Statuss' : 'Status' }}</span>
                                <span :class="['status-pill', user.is_active ? 'status-active' : 'status-inactive']">
                                    <i :class="user.is_active ? 'fas fa-check' : 'fas fa-ban'"></i>
                                    {{ user.is_active ? (locale === 'lv' ? 'Aktīvs' : 'Active') : (locale === 'lv' ? 'Bloķēts' : 'Banned') }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'E-pasta verifikācija' : 'Email Verified' }}</span>
                                <span class="ivalue">
                                    <span v-if="user.email_verified_at" class="text-green">
                                        <i class="fas fa-check-circle"></i> {{ formatDate(user.email_verified_at) }}
                                    </span>
                                    <span v-else class="text-red">
                                        <i class="fas fa-times-circle"></i> {{ locale === 'lv' ? 'Nav verificēts' : 'Not verified' }}
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Reģistrācija' : 'Registered' }}</span>
                                <span class="ivalue">{{ formatDate(user.created_at) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Pēdējais pieslēgums' : 'Last Login' }}</span>
                                <span class="ivalue">{{ formatDateTime(user.last_login_at) }}</span>
                            </div>
                        </div>

                        <div class="quick-actions" v-if="canModify">
                            <a :href="`/admin/users/${user.id}/edit`" class="qa-btn">
                                <i class="fas fa-edit"></i>
                                {{ locale === 'lv' ? 'Rediģēt kontu' : 'Edit Account' }}
                            </a>
                            <a :href="`mailto:${user.email}`" class="qa-btn">
                                <i class="fas fa-envelope"></i>
                                E-pasts
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ─── ADDRESS TAB ─── -->
            <div v-if="activeTab === 'address'" class="tab-content">
                <div v-if="user.addresses && user.addresses.length > 0" class="address-grid">
                    <div v-for="(addr, i) in user.addresses" :key="i" class="address-card">
                        <div class="addr-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="addr-body">
                            <div class="addr-line main">{{ addr.address }}</div>
                            <div class="addr-line">{{ addr.city }}<span v-if="addr.postal_code">, {{ addr.postal_code }}</span></div>
                            <div class="addr-line muted">{{ addr.country }}</div>
                        </div>
                    </div>
                </div>
                <div v-else class="empty-tab">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>{{ locale === 'lv' ? 'Nav pievienotu adrešu' : 'No addresses on file' }}</p>
                </div>
            </div>

            <!-- ─── ACTIVITY TAB ─── -->
            <div v-if="activeTab === 'activity'" class="tab-content">
                <div class="activity-stats">
                    <div class="astat-card astat-orders">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="astat-val">{{ user.orders_count ?? 0 }}</span>
                        <span class="astat-lbl">{{ locale === 'lv' ? 'Pasūtījumi' : 'Orders' }}</span>
                    </div>
                    <div class="astat-card astat-spent">
                        <i class="fas fa-euro-sign"></i>
                        <span class="astat-val">{{ formatPrice(user.total_spent) }}€</span>
                        <span class="astat-lbl">{{ locale === 'lv' ? 'Kopā iztērēts' : 'Total Spent' }}</span>
                    </div>
                    <div class="astat-card astat-reviews">
                        <i class="fas fa-star"></i>
                        <span class="astat-val">{{ user.reviews_count ?? 0 }}</span>
                        <span class="astat-lbl">{{ locale === 'lv' ? 'Atsauksmes' : 'Reviews' }}</span>
                    </div>
                    <div class="astat-card astat-comments">
                        <i class="fas fa-comment"></i>
                        <span class="astat-val">{{ user.comments_count ?? 0 }}</span>
                        <span class="astat-lbl">{{ locale === 'lv' ? 'Komentāri' : 'Comments' }}</span>
                    </div>
                </div>

                <div class="timeline-card">
                    <h3 class="card-heading"><i class="fas fa-clock"></i> {{ locale === 'lv' ? 'Laika līnija' : 'Timeline' }}</h3>
                    <div class="timeline">
                        <div class="tl-item" v-if="user.created_at">
                            <div class="tl-dot dot-blue"></div>
                            <div class="tl-content">
                                <span class="tl-label">{{ locale === 'lv' ? 'Konts izveidots' : 'Account created' }}</span>
                                <span class="tl-date">{{ formatDateTime(user.created_at) }}</span>
                            </div>
                        </div>
                        <div class="tl-item" v-if="user.email_verified_at">
                            <div class="tl-dot dot-green"></div>
                            <div class="tl-content">
                                <span class="tl-label">{{ locale === 'lv' ? 'E-pasts verificēts' : 'Email verified' }}</span>
                                <span class="tl-date">{{ formatDateTime(user.email_verified_at) }}</span>
                            </div>
                        </div>
                        <div class="tl-item" v-if="user.last_login_at">
                            <div class="tl-dot dot-orange"></div>
                            <div class="tl-content">
                                <span class="tl-label">{{ locale === 'lv' ? 'Pēdējais pieslēgums' : 'Last login' }}</span>
                                <span class="tl-date">{{ formatDateTime(user.last_login_at) }}</span>
                            </div>
                        </div>
                        <div class="tl-item" v-if="!user.is_active">
                            <div class="tl-dot dot-red"></div>
                            <div class="tl-content">
                                <span class="tl-label">{{ locale === 'lv' ? 'Konts bloķēts' : 'Account banned' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── NEWSLETTER TAB ─── -->
            <div v-if="activeTab === 'newsletter'" class="tab-content">
                <div class="info-card" style="max-width: 540px;">
                    <h3 class="card-heading">
                        <i class="fas fa-envelope"></i>
                        {{ locale === 'lv' ? 'Jaunumu abonements' : 'Newsletter Subscription' }}
                    </h3>

                    <div v-if="newsletterStatus" class="info-rows">
                        <!-- Subscribed status -->
                        <div class="info-row">
                            <span class="ilabel">{{ locale === 'lv' ? 'Statuss' : 'Status' }}</span>
                            <span class="ivalue">
                                <span v-if="newsletterStatus.subscribed" class="nl-badge nl-active">
                                    <i class="fas fa-check-circle"></i>
                                    {{ locale === 'lv' ? 'Abonēts' : 'Subscribed' }}
                                </span>
                                <span v-else class="nl-badge nl-inactive">
                                    <i class="fas fa-times-circle"></i>
                                    {{ locale === 'lv' ? 'Nav abonēts' : 'Not subscribed' }}
                                </span>
                            </span>
                        </div>

                        <!-- Verified -->
                        <div class="info-row" v-if="newsletterStatus.subscribed">
                            <span class="ilabel">{{ locale === 'lv' ? 'Verificēts' : 'Verified' }}</span>
                            <span class="ivalue">
                                <span v-if="newsletterStatus.is_verified" class="text-green">
                                    <i class="fas fa-check-circle"></i>
                                    {{ locale === 'lv' ? 'Jā' : 'Yes' }}
                                </span>
                                <span v-else class="text-red">
                                    <i class="fas fa-times-circle"></i>
                                    {{ locale === 'lv' ? 'Nē' : 'No' }}
                                </span>
                            </span>
                        </div>

                        <!-- Expiry -->
                        <div class="info-row" v-if="newsletterStatus.subscribed">
                            <span class="ilabel">{{ locale === 'lv' ? 'Termiņš' : 'Expires' }}</span>
                            <span class="ivalue">
                                <span v-if="newsletterStatus.expires_at">
                                    {{ newsletterStatus.expires_at }}
                                    <span v-if="newsletterStatus.days_remaining !== null" style="margin-left:.375rem;" :style="{ color: newsletterStatus.days_remaining <= 7 ? '#dc2626' : '#6b7280' }">
                                        ({{ newsletterStatus.days_remaining }}d)
                                    </span>
                                </span>
                                <span v-else class="text-green">
                                    <i class="fas fa-infinity"></i>
                                    {{ locale === 'lv' ? 'Bez termiņa' : 'No expiry' }}
                                </span>
                            </span>
                        </div>

                        <!-- Preferences -->
                        <template v-if="newsletterStatus.subscribed && newsletterStatus.preferences">
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Jaunumi' : 'News' }}</span>
                                <span class="ivalue">
                                    <i :class="newsletterStatus.preferences.receive_news ? 'fas fa-check text-green-icon' : 'fas fa-times text-red-icon'"></i>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Akcijas' : 'Promotions' }}</span>
                                <span class="ivalue">
                                    <i :class="newsletterStatus.preferences.receive_promotions ? 'fas fa-check text-green-icon' : 'fas fa-times text-red-icon'"></i>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="ilabel">{{ locale === 'lv' ? 'Paziņojumi' : 'Announcements' }}</span>
                                <span class="ivalue">
                                    <i :class="newsletterStatus.preferences.receive_announcements ? 'fas fa-check text-green-icon' : 'fas fa-times text-red-icon'"></i>
                                </span>
                            </div>
                        </template>
                    </div>

                    <div v-else class="empty-tab" style="padding: 2rem;">
                        <i class="fas fa-envelope" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        <p>{{ locale === 'lv' ? 'Nav datu par abonementu' : 'No subscription data available' }}</p>
                    </div>
                </div>
            </div>

        </div>
        <Transition name="modal">
            <div v-if="showBanConfirm" class="modal-overlay" @click.self="showBanConfirm = false">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i :class="user.is_active ? 'fas fa-ban' : 'fas fa-check-circle'"></i>
                            {{ user.is_active
                            ? (locale === 'lv' ? 'Bloķēt lietotāju' : 'Ban User')
                            : (locale === 'lv' ? 'Atbloķēt lietotāju' : 'Unban User') }}
                        </h3>
                        <button @click="showBanConfirm = false" class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="user-preview">
                            <img :src="getUserAvatar()" class="preview-avatar" />
                            <div class="preview-info">
                                <span class="preview-name">{{ user.username }}</span>
                                <span class="preview-email">{{ user.email }}</span>
                            </div>
                            <span :class="['status-badge', user.is_active ? 'status-active' : 'status-inactive']">
                                {{ user.is_active ? (locale === 'lv' ? 'Aktīvs' : 'Active') : (locale === 'lv' ? 'Bloķēts' : 'Banned') }}
                            </span>
                        </div>
                        <div :class="user.is_active ? 'warning-box' : 'success-box'">
                            <i :class="user.is_active ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle'"></i>
                            <div>
                                <strong>{{ user.is_active
                                    ? (locale === 'lv' ? 'Lietotājs tiks bloķēts' : 'User will be banned')
                                    : (locale === 'lv' ? 'Lietotājs tiks atbloķēts' : 'User will be unbanned') }}</strong>
                                <p>{{ user.is_active
                                    ? (locale === 'lv' ? 'Viņš/viņa nevarēs pieslēgties sistēmai.' : 'They will no longer be able to log in.')
                                    : (locale === 'lv' ? 'Viņš/viņa atkal varēs pieslēgties.' : 'They will be able to log in again.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="showBanConfirm = false" class="btn btn-secondary">
                            {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                        </button>
                        <button @click="toggleActive" :disabled="isLoading"
                                :class="['btn', user.is_active ? 'btn-danger' : 'btn-success']">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <i v-else :class="user.is_active ? 'fas fa-ban' : 'fas fa-check'"></i>
                            {{ user.is_active
                            ? (locale === 'lv' ? 'Bloķēt' : 'Ban')
                            : (locale === 'lv' ? 'Atbloķēt' : 'Unban') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ─── DELETE CONFIRM MODAL ─── -->
        <Transition name="modal">
            <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-trash"></i>
                            {{ locale === 'lv' ? 'Dzēst lietotāju' : 'Delete User' }}
                        </h3>
                        <button @click="showDeleteConfirm = false" class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="user-preview">
                            <img :src="getUserAvatar()" class="preview-avatar" />
                            <div class="preview-info">
                                <span class="preview-name">{{ user.username }}</span>
                                <span class="preview-email">{{ user.email }}</span>
                            </div>
                        </div>
                        <div class="danger-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>{{ locale === 'lv' ? 'Šo darbību nevar atsaukt!' : 'This action cannot be undone!' }}</strong>
                                <p>{{ locale === 'lv'
                                    ? 'Tiks dzēsts konts un visas ar to saistītās darbības.'
                                    : 'The account and all associated data will be permanently deleted.' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="showDeleteConfirm = false" class="btn btn-secondary">
                            {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                        </button>
                        <button @click="deleteUser" :disabled="isLoading" class="btn btn-danger">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-trash"></i>
                            {{ locale === 'lv' ? 'Dzēst' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
/* ── LAYOUT ── */
.user-show { padding: 0; }

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    text-decoration: none;
    margin-right: 0.5rem;
    font-size: 0.9rem;
}
.back-link:hover { color: #dc2626; }

/* ── ALERTS ── */
.alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 500; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

/* ── HERO ── */
.profile-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.75rem 2rem;
    margin-bottom: 1.5rem;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.hero-left { display: flex; align-items: center; gap: 1.25rem; }

.avatar-wrapper { position: relative; flex-shrink: 0; }
.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e5e7eb;
}
.avatar-status {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid white;
}
.avatar-status.online  { background: #10b981; }
.avatar-status.offline { background: #9ca3af; }

.hero-name { font-size: 1.4rem; font-weight: 700; color: #111827; }
.hero-username { font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem; }
.hero-meta { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }

.role-badge { padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.role-admin   { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
.role-user    { background: #dbeafe; color: #1e40af; }
.role-courier { background: #d1fae5; color: #065f46; }
.role-default { background: #f3f4f6; color: #374151; }

.verified-tag   { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; color: #059669; font-weight: 600; background: #d1fae5; padding: 0.2rem 0.6rem; border-radius: 9999px; }
.unverified-tag { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; color: #b45309; font-weight: 600; background: #fef3c7; padding: 0.2rem 0.6rem; border-radius: 9999px; }

.hero-right { display: flex; flex-direction: column; align-items: flex-end; gap: 1rem; }

.hero-stats { display: flex; gap: 2rem; }
.hstat { display: flex; flex-direction: column; align-items: center; gap: 2px; }
.hstat-val { font-size: 1.3rem; font-weight: 700; color: #1f2937; }
.hstat-lbl { font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; }

.hero-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; justify-content: flex-end; }

/* ── TABS ── */
.tabs-bar {
    display: flex;
    gap: 0.25rem;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 1.5rem;
}
.tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    cursor: pointer;
    transition: all 0.2s;
}
.tab-btn:hover { color: #dc2626; }
.tab-btn.active { color: #dc2626; border-bottom-color: #dc2626; font-weight: 600; }
.tab-btn i { font-size: 0.8rem; }

/* ── TAB CONTENT ── */
.tab-content { animation: fadeIn 0.2s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

/* ── DETAIL GRID ── */
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

.info-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.875rem;
    padding: 1.5rem;
}

.card-heading {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 1.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}
.card-heading i { color: #dc2626; font-size: 0.85rem; }

.info-rows { display: flex; flex-direction: column; gap: 0.875rem; }
.info-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; gap: 1rem; }
.ilabel { color: #6b7280; font-size: 0.8rem; flex-shrink: 0; }
.ivalue { color: #111827; font-weight: 500; text-align: right; display: flex; align-items: center; gap: 0.375rem; }
.mono { font-family: monospace; font-size: 0.875rem; }
.link-red { color: #dc2626; text-decoration: none; }
.link-red:hover { text-decoration: underline; }
.verified-icon { color: #10b981; font-size: 0.8rem; }
.text-green { color: #059669; display: flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; }
.text-red   { color: #dc2626; display: flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; }

.status-pill { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.status-active   { background: #d1fae5; color: #065f46; }
.status-inactive { background: #fee2e2; color: #991b1b; }

.quick-actions { display: flex; gap: 0.5rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #f3f4f6; }
.qa-btn { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.8rem; color: #374151; text-decoration: none; transition: all 0.15s; }
.qa-btn:hover { background: #f3f4f6; border-color: #d1d5db; }

/* ── ADDRESS TAB ── */
.address-grid { display: flex; flex-direction: column; gap: 1rem; max-width: 600px; }
.address-card { display: flex; gap: 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 0.875rem; padding: 1.25rem; }
.addr-icon { width: 2.5rem; height: 2.5rem; background: #fee2e2; color: #dc2626; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.addr-body { display: flex; flex-direction: column; gap: 0.2rem; }
.addr-line { font-size: 0.9rem; color: #111827; }
.addr-line.main { font-weight: 600; }
.addr-line.muted { color: #6b7280; font-size: 0.8rem; }

/* ── ACTIVITY TAB ── */
.activity-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.astat-card { background: white; border: 1px solid #e5e7eb; border-radius: 0.875rem; padding: 1.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; text-align: center; }
.astat-card i { font-size: 1.5rem; }
.astat-orders  i { color: #dc2626; }
.astat-spent   i { color: #059669; }
.astat-reviews i { color: #d97706; }
.astat-comments i { color: #7c3aed; }
.astat-val { font-size: 1.5rem; font-weight: 700; color: #111827; }
.astat-lbl { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; }

.timeline-card { background: white; border: 1px solid #e5e7eb; border-radius: 0.875rem; padding: 1.5rem; }
.timeline { display: flex; flex-direction: column; gap: 0; }
.tl-item { display: flex; gap: 1rem; padding-bottom: 1.25rem; position: relative; }
.tl-item:not(:last-child)::after { content: ''; position: absolute; left: 0.4375rem; top: 1.25rem; bottom: 0; width: 2px; background: #e5e7eb; }
.tl-dot { width: 1rem; height: 1rem; border-radius: 50%; flex-shrink: 0; margin-top: 0.125rem; }
.dot-blue   { background: #60a5fa; }
.dot-green  { background: #34d399; }
.dot-orange { background: #fbbf24; }
.dot-red    { background: #f87171; }
.tl-content { display: flex; flex-direction: column; gap: 0.125rem; }
.tl-label { font-size: 0.875rem; color: #374151; font-weight: 500; }
.tl-date { font-size: 0.75rem; color: #9ca3af; }

/* ── EMPTY ── */
.empty-tab { text-align: center; padding: 4rem; color: #9ca3af; background: white; border: 1px solid #e5e7eb; border-radius: 0.875rem; }
.empty-tab i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; }
.empty-tab p { margin: 0; font-size: 0.9rem; }

/* ── BUTTONS ── */
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.15s; border: none; text-decoration: none; }
.btn-outline    { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f3f4f6; }
.btn-secondary  { background: #f3f4f6; color: #374151; border: none; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-danger     { background: #dc2626; color: white; }
.btn-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-danger-sm  { background: #fee2e2; color: #dc2626; border: none; padding: 0.5rem 0.65rem; }
.btn-danger-sm:hover { background: #fecaca; }
.btn-warn       { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.btn-warn:hover { background: #ffedd5; }
.btn-success    { background: #059669; color: white; }
.btn-success:hover:not(:disabled) { background: #047857; }
.btn-success:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-success-sm { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.btn-success-sm:hover { background: #a7f3d0; }

/* ── MODAL ── */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal-container { background: white; border-radius: 1rem; width: 100%; max-width: 500px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-title { font-size: 1.1rem; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.modal-title i { color: #dc2626; }
.modal-close { background: none; border: none; color: #6b7280; font-size: 1.1rem; cursor: pointer; padding: 0.25rem; }
.modal-close:hover { color: #111827; }
.modal-body { padding: 1.5rem; overflow-y: auto; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; }

.user-preview { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f9fafb; border-radius: 0.75rem; margin-bottom: 1.25rem; }
.preview-avatar { width: 3rem; height: 3rem; border-radius: 50%; object-fit: cover; }
.preview-info { flex: 1; display: flex; flex-direction: column; }
.preview-name  { font-weight: 600; color: #111827; font-size: 0.9rem; }
.preview-email { font-size: 0.8rem; color: #6b7280; }

.status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }

.warning-box, .danger-box, .success-box { display: flex; gap: 1rem; padding: 1rem; border-radius: 0.75rem; }
.warning-box { background: #fef3c7; color: #92400e; }
.danger-box  { background: #fee2e2; color: #991b1b; }
.success-box { background: #d1fae5; color: #065f46; }
.warning-box i, .danger-box i, .success-box i { font-size: 1.4rem; flex-shrink: 0; margin-top: 0.125rem; }
.warning-box strong, .danger-box strong, .success-box strong { display: block; margin-bottom: 0.25rem; font-size: 0.9rem; }
.warning-box p, .danger-box p, .success-box p { margin: 0; font-size: 0.8rem; opacity: 0.9; }

/* ── Newsletter badges ── */
.nl-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.nl-active { background: #d1fae5; color: #065f46; }
.nl-inactive { background: #fee2e2; color: #991b1b; }
.text-green-icon { color: #059669; }
.text-red-icon { color: #dc2626; }

/* ── TRANSITIONS ── */
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-container, .modal-leave-to .modal-container { transform: scale(0.95); }

/* ── RESPONSIVE ── */
@media (max-width: 1024px) {
    .detail-grid { grid-template-columns: 1fr; }
    .activity-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .profile-hero { flex-direction: column; padding: 1.25rem; }
    .hero-right { align-items: flex-start; width: 100%; }
    .hero-stats { gap: 1.25rem; }
    .hero-actions { width: 100%; }
    .hero-actions .btn { flex: 1; justify-content: center; }
    .tabs-bar { overflow-x: auto; }
    .activity-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .hero-stats { gap: 0.75rem; }
    .hstat-val { font-size: 1.1rem; }
    .tabs-bar { gap: 0; }
    .tab-btn { padding: 0.625rem 0.875rem; font-size: 0.8rem; }
    .modal-footer { flex-direction: column; }
    .modal-footer .btn { width: 100%; justify-content: center; }
}
</style>
