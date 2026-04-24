<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// ════════════════════════════════════════════════════════════════
// [JAUNS] Importē atļauju composable un modāli
// Logs lapā nav darbību pogu, bet eksports prasa logs.view
// ════════════════════════════════════════════════════════════════
import { useAdminPermission } from '@/Composables/useAdminPermission.js';
import UnauthorizedModal from '@/Components/UnauthorizedModal.vue';

const { locale } = useI18n({ useScope: 'global' });

// ════════════════════════════════════════════════════════════════
// [JAUNS] Inicializē — logs lapai vajag tikai can('logs.view')
// ════════════════════════════════════════════════════════════════
const {
    can,
    showUnauthorized,
    requiredPermission,
    openUnauthorized,
    closeUnauthorized,
    actionBtnStyle,
    noPermTitle,
} = useAdminPermission();

const props = defineProps({
    logs: Object,
    filters: Object,
    activityTypes: Array,
    users: Array,
});

// ── Filters ──────────────────────────────────────────────────────
const search        = ref(props.filters?.search || '');
const typeFilter    = ref(props.filters?.activity_type || '');
const userFilter    = ref(props.filters?.user_id || '');
const dateFrom      = ref(props.filters?.date_from || '');
const dateTo        = ref(props.filters?.date_to || '');

let searchTimeout = null;
const applyFilters = () => {
    router.get('/admin/logs', {
        search:        search.value || undefined,
        activity_type: typeFilter.value || undefined,
        user_id:       userFilter.value || undefined,
        date_from:     dateFrom.value || undefined,
        date_to:       dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

watch([typeFilter, userFilter, dateFrom, dateTo], applyFilters);
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 350);
});

const clearFilters = () => {
    search.value = ''; typeFilter.value = ''; userFilter.value = '';
    dateFrom.value = ''; dateTo.value = '';
};

const hasFilters = computed(() =>
    search.value || typeFilter.value || userFilter.value || dateFrom.value || dateTo.value
);

// ── Helpers ───────────────────────────────────────────────────────
const formatDateTime = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    });
};

// ── Activity type styling & labelling ────────────────────────────
const typeConfig = {
    login:              { icon: 'fas fa-sign-in-alt',   cls: 'type-login',    lv: 'Pieslēgšanās',     en: 'Login' },
    logout:             { icon: 'fas fa-sign-out-alt',  cls: 'type-logout',   lv: 'Atslēgšanās',      en: 'Logout' },
    register:           { icon: 'fas fa-user-plus',     cls: 'type-register', lv: 'Reģistrācija',     en: 'Register' },
    profile_update:     { icon: 'fas fa-user-edit',     cls: 'type-profile',  lv: 'Profils atjaunots',en: 'Profile Updated' },
    privacy_update:     { icon: 'fas fa-user-shield',   cls: 'type-privacy',  lv: 'Privātums mainīts',en: 'Privacy Changed' },
    password_change:    { icon: 'fas fa-key',           cls: 'type-password', lv: 'Parole mainīta',   en: 'Password Changed' },
    order_placed:       { icon: 'fas fa-shopping-bag',  cls: 'type-order',    lv: 'Pasūtījums',       en: 'Order Placed' },
    order_cancelled:    { icon: 'fas fa-times-circle',  cls: 'type-cancel',   lv: 'Pasūtījums atcelts',en: 'Order Cancelled' },
    review_added:       { icon: 'fas fa-star',          cls: 'type-review',   lv: 'Atsauksme',        en: 'Review Added' },
    comment_added:      { icon: 'fas fa-comment',       cls: 'type-comment',  lv: 'Komentārs',        en: 'Comment Added' },
    newsletter_subscribed: { icon: 'fas fa-envelope',    cls: 'type-newsletter-subscribed',    lv: 'Abonēšana',    en: 'Subscription' },
    newsletter_unsubscribe: { icon: 'fas fa-envelope-open',cls:'type-unsub',  lv: 'Atmelots',         en: 'Unsubscribed' },
    admin_action:       { icon: 'fas fa-shield-alt',    cls: 'type-admin',    lv: 'Administratora darbība',    en: 'Admin Action' },

    order_created: { icon: 'fas fa-shopping-cart',    cls: 'type-order',    lv: 'Pasūtījums izveidots',    en: 'Order Created' },
    order_updated: { icon: 'fas fa-edit',    cls: 'type-order',    lv: 'Pasūtījums atjaunināts',    en: 'Order Updated' },
    profile_updated: { icon: 'fas fa-user-edit',    cls: 'type-profile',    lv: 'Profils atjaunināts',    en: 'Profile Updated' },
    password_changed: { icon: 'fas fa-key',    cls: 'type-password',    lv: 'Parole nomainīta',    en: 'Password Changed' },
    review_created: { icon: 'fas fa-star',    cls: 'type-review',    lv: 'Atsauksme pievienota',    en: 'Review Added' },
    comment_created: { icon: 'fas fa-comment',    cls: 'type-comment',    lv: 'Komentārs pievienots',    en: 'Comment Added' },
    contact_sent: { icon: 'fas fa-envelope',    cls: 'type-contact',    lv: 'Ziņojums nosūtīts',    en: 'Message Sent' },
    product_viewed: { icon: 'fas fa-eye',    cls: 'type-product',    lv: 'Produkts apskatīts',    en: 'Product Viewed' },
    cart_updated: { icon: 'fas fa-shopping-basket',    cls: 'type-cart',    lv: 'Grozs atjaunināts',    en: 'Cart Updated' },
    settings_updated: { icon: 'fas fa-cog',    cls: 'type-default',    lv: 'Iestatījumu atjaunināšana',    en: 'Settings Updated' },
    test_email_sent: { icon: 'fas fa-envelope',    cls: 'type-test-email-sent',    lv: 'Testa e-pasta nosūtīšana',    en: 'Test Email Sent' },
    cache_cleared: { icon: 'fas fa-database',    cls: 'type-password',    lv: 'Kešatmiņa notīrīta',    en: 'Cache Cleared' },
    // Jaunie tipi
    comment_reply:    { icon: 'fas fa-reply',        cls: 'type-comment',  lv: 'Atbilde uz komentāru', en: 'Comment Reply' },
    content_liked:    { icon: 'fas fa-thumbs-up',    cls: 'type-like',     lv: 'Patīk',                en: 'Content Liked' },
    content_unliked:  { icon: 'far fa-thumbs-up',    cls: 'type-unlike',   lv: 'Noņemts patīk',        en: 'Content Unliked' },
};

const getTypeInfo = (type) => typeConfig[type] ?? {
    icon: 'fas fa-circle', cls: 'type-default',
    lv: type, en: type,
};

// ── Privacy change highlight ──────────────────────────────────────
const isPrivacyChange = (log) =>
    log.activity_type === 'privacy_update' ||
    (log.description && /privāt|is_public|privacy/i.test(log.description));

// ── Export ────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════
// [IZMAINĪTS] Eksports pārbauda logs.view atļauju.
// Parasti logs.view jau ir nodrošināts ar Laravel middleware,
// bet frontend arī bloķē pogu vizuāli.
// ════════════════════════════════════════════════════════════════
const exportLogs = () => {
    if (!can('logs.view')) {
        openUnauthorized('logs.view');
        return;
    }
    const params = new URLSearchParams();
    if (typeFilter.value)  params.set('activity_type', typeFilter.value);
    if (userFilter.value)  params.set('user_id', userFilter.value);
    if (dateFrom.value)    params.set('date_from', dateFrom.value);
    if (dateTo.value)      params.set('date_to', dateTo.value);
    if (search.value)      params.set('search', search.value);
    window.location.href = `/admin/logs/export?${params.toString()}`;
};
</script>

<template>
    <Head :title="locale === 'lv' ? 'Aktivitāšu žurnāls' : 'Activity Log'" />

    <AdminLayout>
        <template #title>
            <i class="fas fa-history"></i>
            {{ locale === 'lv' ? 'Aktivitāšu žurnāls' : 'Activity Log' }}
        </template>

        <!-- ── FILTERS ── -->
        <div class="filters-card">
            <div class="filters-grid">
                <!-- Search -->
                <div class="search-wrap">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        v-model="search"
                        type="text"
                        class="search-input"
                        :placeholder="locale === 'lv' ? 'Meklēt aprakstā, IP...' : 'Search description, IP...'"
                    >
                    <button v-if="search" @click="search = ''" class="clear-btn"><i class="fas fa-times"></i></button>
                </div>

                <!-- Activity type -->
                <select v-model="typeFilter" class="filter-select">
                    <option value="">{{ locale === 'lv' ? 'Visas darbības' : 'All activity types' }}</option>
                    <option v-for="type in activityTypes" :key="type" :value="type">
                        {{ getTypeInfo(type)[locale === 'lv' ? 'lv' : 'en'] }}
                    </option>
                </select>

                <!-- User -->
                <select v-model="userFilter" class="filter-select">
                    <option value="">{{ locale === 'lv' ? 'Visi lietotāji' : 'All users' }}</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.username }}</option>
                </select>

                <!-- Date from -->
                <input v-model="dateFrom" type="date" class="filter-select" :max="dateTo || undefined">

                <!-- Date to -->
                <input v-model="dateTo" type="date" class="filter-select" :min="dateFrom || undefined">

                <div class="filter-actions">
                    <button v-if="hasFilters" @click="clearFilters" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        {{ locale === 'lv' ? 'Notīrīt' : 'Clear' }}
                    </button>
                    <!--
                        ════════════════════════════════════════════════
                        [IZMAINĪTS] CSV eksporta poga:
                        - Ja nav logs.view → pelēcīga, kursors not-allowed
                        - actionBtnStyle() → opacity + grayscale
                        ════════════════════════════════════════════════
                    -->
                    <button
                        @click="exportLogs"
                        class="btn btn-export"
                        :class="!can('logs.view') ? 'btn-no-permission' : ''"
                        :style="actionBtnStyle(can('logs.view'))"
                        :title="!can('logs.view') ? noPermTitle : 'Eksportēt CSV'"
                    >
                        <i class="fas fa-download"></i>
                        CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- ── TABLE ── -->
        <div class="table-card">
            <!-- Empty -->
            <div v-if="logs.data.length === 0" class="empty-state">
                <i class="fas fa-history"></i>
                <p>{{ locale === 'lv' ? 'Nav atrasto ierakstu' : 'No log entries found' }}</p>
            </div>

            <!-- Desktop table -->
            <table v-else class="log-table">
                <thead>
                <tr>
                    <th>{{ locale === 'lv' ? 'Laiks' : 'Time' }}</th>
                    <th>{{ locale === 'lv' ? 'Lietotājs' : 'User' }}</th>
                    <th>{{ locale === 'lv' ? 'Darbība' : 'Activity' }}</th>
                    <th>{{ locale === 'lv' ? 'Apraksts' : 'Description' }}</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="log in logs.data"
                    :key="log.id"
                    :class="{ 'row-privacy': isPrivacyChange(log) }"
                >
                    <td class="td-time">{{ formatDateTime(log.created_at) }}</td>

                    <td class="td-user">
                            <span v-if="log.user" class="user-chip">
                                <Link :href="`/admin/users/${log.user.id}`" class="user-link">
                                    {{ log.user.username }}
                                </Link>
                            </span>
                        <span v-else class="system-chip">
                                <i class="fas fa-robot"></i> Sistēma
                            </span>
                    </td>

                    <td class="td-type">
                            <span :class="['type-badge', getTypeInfo(log.activity_type).cls]">
                                <i :class="getTypeInfo(log.activity_type).icon"></i>
                                {{ getTypeInfo(log.activity_type)[locale === 'lv' ? 'lv' : 'en'] }}
                            </span>
                        <span v-if="isPrivacyChange(log)" class="privacy-flag">
                                <i class="fas fa-user-shield"></i>
                            </span>
                    </td>

                    <td class="td-desc">
                        <span class="desc-text">{{ log.description || '—' }}</span>
                    </td>

                    <td class="td-ip">
                        <span class="ip-mono">{{ log.ip_address || '—' }}</span>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Mobile cards -->
            <div v-if="logs.data.length > 0" class="mobile-log-cards">
                <div
                    v-for="log in logs.data"
                    :key="log.id"
                    class="log-card"
                    :class="{ 'log-card-privacy': isPrivacyChange(log) }"
                >
                    <div class="lc-top">
                        <span :class="['type-badge', getTypeInfo(log.activity_type).cls]">
                            <i :class="getTypeInfo(log.activity_type).icon"></i>
                            {{ getTypeInfo(log.activity_type)[locale === 'lv' ? 'lv' : 'en'] }}
                        </span>
                        <span v-if="isPrivacyChange(log)" class="privacy-flag">
                            <i class="fas fa-user-shield"></i>
                        </span>
                        <span class="lc-time">{{ formatDateTime(log.created_at) }}</span>
                    </div>
                    <div class="lc-user">
                        <i class="fas fa-user"></i>
                        <Link v-if="log.user" :href="`/admin/users/${log.user.id}`" class="user-link">
                            {{ log.user.username }}
                        </Link>
                        <span v-else class="system-chip"><i class="fas fa-robot"></i> Sistēma</span>
                    </div>
                    <div v-if="log.description" class="lc-desc">{{ log.description }}</div>
                    <div class="lc-ip">
                        <i class="fas fa-network-wired"></i>
                        {{ log.ip_address || '—' }}
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links && logs.links.length > 3" class="pagination-wrapper">
                <div class="pagination-info">
                    {{ logs.from }}–{{ logs.to }}
                    {{ locale === 'lv' ? 'no' : 'of' }}
                    {{ logs.total }}
                </div>
                <div class="pagination">
                    <template v-for="link in logs.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="page-link"
                            :class="{ active: link.active }"
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span v-else class="page-link disabled" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
        <!-- ════════════════════════════════════════════════════════════
             [JAUNS] UnauthorizedModal — pievieno PIRMS </AdminLayout>
             ════════════════════════════════════════════════════════════ -->
        <UnauthorizedModal
            :show="showUnauthorized"
            :required-permission="requiredPermission"
            @close="closeUnauthorized"
        />

    </AdminLayout>
</template>

<style scoped>
/* ── Filters ── */
.filters-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
    gap: 0.75rem;
    align-items: center;
}
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.search-icon { position: absolute; left: 0.75rem; color: #9ca3af; font-size: 0.8rem; pointer-events: none; }
.search-input {
    width: 100%;
    padding: 0.6rem 2.25rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}
.search-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.08); }
.clear-btn { position: absolute; right: 0.5rem; background: none; border: none; color: #9ca3af; cursor: pointer; }
.filter-select {
    padding: 0.6rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background: white;
    width: 100%;
}
.filter-actions { display: flex; gap: 0.5rem; }

/* ── Buttons ── */
.btn { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.575rem 1rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; border: none; white-space: nowrap; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-export { background: #d1fae5; color: #065f46; }
.btn-export:hover:not(.btn-no-permission) { background: #a7f3d0; }

/* ════════════════════════════════════════════════════════════════
   [JAUNS] Disabled/no-permission stils (tāds pats kā Products)
   ════════════════════════════════════════════════════════════════ */
.btn-no-permission {
    cursor: not-allowed !important;
    background-image: repeating-linear-gradient(
        -45deg,
        transparent,
        transparent 3px,
        rgba(0, 0, 0, 0.04) 3px,
        rgba(0, 0, 0, 0.04) 6px
    ) !important;
}
.btn-no-permission:hover { transform: none !important; }

/* ── Table ── */
.table-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
}
.log-table { width: 100%; border-collapse: collapse; }
.log-table thead tr { background: #f9fafb; }
.log-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e5e7eb;
}
.log-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.log-table tbody tr:hover { background: #fafafa; }
.log-table tbody tr:last-child { border-bottom: none; }

/* Privacy row highlight */
.row-privacy { background: #fffbeb !important; border-left: 3px solid #f59e0b; }
.row-privacy:hover { background: #fef3c7 !important; }

.td-time { padding: 0.75rem 1rem; font-size: 0.75rem; color: #6b7280; white-space: nowrap; }
.td-user { padding: 0.75rem 1rem; }
.td-type { padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.5rem; }
.td-desc { padding: 0.75rem 1rem; max-width: 280px; }
.td-ip   { padding: 0.75rem 1rem; }

.user-chip { display: inline-flex; align-items: center; }
.user-link { color: #1e40af; font-size: 0.875rem; font-weight: 500; text-decoration: none; }
.user-link:hover { text-decoration: underline; }
.system-chip { font-size: 0.8rem; color: #6b7280; display: flex; align-items: center; gap: 0.25rem; }

/* Type badges */
.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
}
.type-login    { background: #dbeafe; color: #1e40af; }
.type-logout   { background: #f3f4f6; color: #374151; }
.type-register { background: #d1fae5; color: #065f46; }
.type-profile  { background: #ede9fe; color: #5b21b6; }
.type-privacy  { background: #fef3c7; color: #92400e; }
.type-password { background: #fee2e2; color: #991b1b; }
.type-order    { background: #dbeafe; color: #1d4ed8; }
.type-product    { background: #ebdbfe; color: #841dd8; }
.type-cart    { background: #fedbf8; color: #d81dcf; }
.type-cancel   { background: #fee2e2; color: #dc2626; }
.type-review   { background: #fef3c7; color: #d97706; }
.type-comment  { background: #ede9fe; color: #7c3aed; }
.type-newsletter-subscribed { background: #d1fae5; color: #047857; }
.type-unsub    { background: #fee2e2; color: #b91c1c; }
.type-admin    { background: #fee2e2; color: #991b1b; }
.type-test-email-sent    { background: #dbeafe; color: #1d4ed8; }
.type-contact   { background: #d1fad6; color: #047817; }
.type-default  { background: #f3f4f6; color: #6b7280; }

.privacy-flag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    background: #fef3c7;
    color: #d97706;
    border-radius: 50%;
    font-size: 0.7rem;
}

.desc-text { font-size: 0.8rem; color: #374151; line-height: 1.4; }
.ip-mono   { font-family: monospace; font-size: 0.8rem; color: #6b7280; }

/* ── Pagination ── */
.pagination-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; }
.pagination-info { font-size: 0.8rem; color: #6b7280; }
.pagination { display: flex; gap: 0.25rem; }
.page-link { padding: 0.4rem 0.7rem; border-radius: 0.375rem; font-size: 0.8rem; color: #374151; text-decoration: none; transition: all 0.15s; }
.page-link:hover:not(.disabled):not(.active) { background: #f3f4f6; }
.page-link.active { background: #dc2626; color: white; }
.page-link.disabled { color: #d1d5db; cursor: not-allowed; }

/* ── Empty ── */
.empty-state { text-align: center; padding: 4rem 2rem; color: #9ca3af; }
.empty-state i { font-size: 3rem; display: block; margin-bottom: 1rem; }

/* ── Mobile log cards (hidden on desktop) ── */
.mobile-log-cards { display: none; }

/* ══════════════════════ RESPONSIVE ══════════════════════ */
@media (max-width: 1200px) {
    .filters-grid { grid-template-columns: 1fr 1fr 1fr; }
}

@media (max-width: 1024px) {
    .log-table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
}

@media (max-width: 768px) {
    .filters-grid { grid-template-columns: 1fr 1fr; }
    .search-wrap { grid-column: span 2; }
    .filter-actions { grid-column: span 2; justify-content: flex-start; }

    /* Hide table, show cards */
    .log-table { display: none; }
    .mobile-log-cards {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1rem;
    }
    .log-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .log-card-privacy { background: #fffbeb; border-color: #f59e0b; border-left: 3px solid #f59e0b; }
    .lc-top { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .lc-time { font-size: 0.7rem; color: #9ca3af; margin-left: auto; }
    .lc-user { font-size: 0.8rem; color: #374151; display: flex; align-items: center; gap: 0.375rem; }
    .lc-desc { font-size: 0.8rem; color: #4b5563; padding: 0.5rem; background: white; border-radius: 0.375rem; }
    .lc-ip { font-size: 0.75rem; color: #9ca3af; font-family: monospace; }
    .pagination-wrapper { flex-direction: column; gap: 0.75rem; padding: 1rem; }
    .pagination { flex-wrap: wrap; justify-content: center; }
}

@media (max-width: 480px) {
    .filters-grid { grid-template-columns: 1fr; }
    .search-wrap { grid-column: span 1; }
    .filter-actions { grid-column: span 1; }
}
</style>
