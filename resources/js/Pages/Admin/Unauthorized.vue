<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n({ useScope: 'global' });
const page = usePage();

const props = defineProps({
    requiredPermission: { type: String, default: null },
    returnUrl:          { type: String, default: null },
    // 'admin' | 'courier' | 'none' — padod middleware
    // ja nav padots, automātiski nosaka pēc lomas
    context:            { type: String, default: null },
});

// Nosaka lietotāja lomu
const userRole = computed(() => page.props.auth?.user?.role?.name || null);

// Vai lietotājam ir admin vai kurjera loma
const isAdmin   = computed(() => userRole.value === 'administrator');
const isCourier = computed(() => userRole.value === 'courier');
const hasRole   = computed(() => isAdmin.value || isCourier.value);

// Konteksts (no kura middleware nāk pieprasījums)
const ctx = computed(() => {
    if (props.context) return props.context;
    if (isAdmin.value)   return 'admin';
    if (isCourier.value) return 'courier';
    return 'none';
});

// Teksti latviski
const lv = computed(() => {
    if (ctx.value === 'admin' && props.requiredPermission) {
        return {
            pageTitle: 'Piekļuve liegta',
            title:     'Piekļuve liegta',
            subtitle:  'Jums nav atļaujas veikt šo darbību.',
            detail:    'Jūsu administratora konts šobrīd neietver šo atļauju. Ja uzskatāt, ka tas ir kļūda, lūdzu sazinieties ar Super Administratoru.',
            permission: 'Nepieciešamā atļauja',
            goBack:     'Atpakaļ',
            goDashboard: 'Uz admin paneli',
            dashboardUrl: '/admin/dashboard',
        };
    }
    if (ctx.value === 'admin') {
        return {
            pageTitle: 'Piekļuve liegta',
            title:     'Piekļuve liegta',
            subtitle:  'Jums nav administratora tiesību.',
            detail:    'Šī sadaļa ir pieejama tikai administratoriem. Ja uzskatāt, ka tas ir kļūda, lūdzu sazinieties ar platformas pārvaldnieku.',
            permission: null,
            goBack:     'Atpakaļ',
            goDashboard: 'Uz administratoru paneli',
            dashboardUrl: '/admin/dashboard',
        };
    }
    if (ctx.value === 'courier') {
        return {
            pageTitle: 'Piekļuve liegta',
            title:     'Piekļuve liegta',
            subtitle:  'Jums nav kurjera tiesību.',
            detail:    'Šī sadaļa ir pieejama tikai kurjeriem. Ja uzskatāt, ka tas ir kļūda, lūdzu sazinieties ar administratoru.',
            permission: null,
            goBack:     'Atpakaļ',
            goDashboard: 'Uz kurjera paneli',
            dashboardUrl: '/courier/dashboard',
        };
    }
    // Parasts lietotājs mēģina piekļūt aizsargātai sadaļai
    return {
        pageTitle: 'Piekļuve liegta',
        title:     'Piekļuve liegta',
        subtitle:  'Jums nav tiesību piekļūt šai lapai.',
        detail:    'Šī sadaļa ir pieejama tikai autorizētiem lietotājiem ar atbilstošu lomu. Lūdzu atgriezieties sākumlapā vai piesakieties ar atbilstošu kontu.',
        permission: null,
        goBack:     'Atpakaļ',
        goDashboard: null,
        dashboardUrl: null,
    };
});

// Teksti angliski
const en = computed(() => {
    if (ctx.value === 'admin' && props.requiredPermission) {
        return {
            pageTitle: 'Access Denied',
            title:     'Access Denied',
            subtitle:  'You do not have permission to perform this action.',
            detail:    'Your administrator account does not currently include this permission. If you believe this is a mistake, please contact the Super Administrator.',
            permission: 'Required permission',
            goBack:     'Go Back',
            goDashboard: 'Admin Dashboard',
            dashboardUrl: '/admin/dashboard',
        };
    }
    if (ctx.value === 'admin') {
        return {
            pageTitle: 'Access Denied',
            title:     'Access Denied',
            subtitle:  'You do not have administrator privileges.',
            detail:    'This section is only accessible to administrators. If you believe this is a mistake, please contact the platform manager.',
            permission: null,
            goBack:     'Go Back',
            goDashboard: 'Admin Dashboard',
            dashboardUrl: '/admin/dashboard',
        };
    }
    if (ctx.value === 'courier') {
        return {
            pageTitle: 'Access Denied',
            title:     'Access Denied',
            subtitle:  'You do not have courier privileges.',
            detail:    'This section is only accessible to couriers. If you believe this is a mistake, please contact an administrator.',
            permission: null,
            goBack:     'Go Back',
            goDashboard: 'Courier Dashboard',
            dashboardUrl: '/courier/dashboard',
        };
    }
    return {
        pageTitle: 'Access Denied',
        title:     'Access Denied',
        subtitle:  'You do not have access to this page.',
        detail:    'This section is only available to authorised users with the appropriate role. Please go back or sign in with a suitable account.',
        permission: null,
        goBack:     'Go Back',
        goDashboard: null,
        dashboardUrl: null,
    };
});

const tx = computed(() => locale.value === 'lv' ? lv.value : en.value);

// "Atpakaļ" — browser history vai returnUrl
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else if (props.returnUrl) {
        router.visit(props.returnUrl);
    } else {
        router.visit('/');
    }
};
</script>

<template>
    <Head :title="tx.pageTitle" />
    <div class="unauth-page">
        <div class="bg-grid"></div>
        <div class="bg-orb"></div>
        <div class="unauth-panel">
            <div class="unauth-glow-bar"></div>
            <div class="unauth-brand">
                <img src="/img/RoltonsLV_Icon.png" alt="RalphMania Icon" class="brand-icon">
                <img src="/img/name_logo.png" alt="RalphMania" class="brand-name">
            </div>
            <div class="lock-ring">
                <div class="lock-inner"><i class="fas fa-lock"></i></div>
            </div>
            <h1 class="unauth-title">{{ tx.title }}</h1>
            <p class="unauth-subtitle">{{ tx.subtitle }}</p>
            <p class="unauth-detail">{{ tx.detail }}</p>
            <div v-if="requiredPermission && tx.permission" class="perm-chip">
                <i class="fas fa-key"></i>
                <span class="perm-label">{{ tx.permission }}:</span>
                <code class="perm-code">{{ requiredPermission }}</code>
            </div>
            <div class="unauth-actions">
                <!-- "Atpakaļ" — vienmēr rāda, izmanto browser history -->
                <button @click="goBack" class="btn-back">
                    <i class="fas fa-arrow-left"></i> {{ tx.goBack }}
                </button>
                <!-- "Uz vadības paneli" — tikai admin vai kurjeram -->
                <Link
                    v-if="hasRole && tx.dashboardUrl"
                    :href="tx.dashboardUrl"
                    class="btn-dashboard"
                >
                    <i class="fas fa-tachometer-alt"></i> {{ tx.goDashboard }}
                </Link>
            </div>
            <div class="corner corner-tl"></div>
            <div class="corner corner-br"></div>
        </div>
    </div>
</template>

<style scoped>
.unauth-page { min-height:100vh; background:#020617; display:flex; align-items:center; justify-content:center; padding:1rem; position:relative; overflow:hidden; }
.bg-grid { position:absolute; inset:0; background-image:radial-gradient(rgba(220,38,38,0.12) 1px,transparent 1px); background-size:32px 32px; mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black,transparent); }
.bg-orb { position:absolute; width:600px; height:600px; border-radius:50%; background:radial-gradient(circle,rgba(220,38,38,0.08) 0%,transparent 70%); top:50%; left:50%; transform:translate(-50%,-50%); pointer-events:none; }
.unauth-panel { position:relative; width:100%; max-width:540px; background:#0f172a; border:1px solid rgba(220,38,38,0.3); border-radius:1.25rem; padding:2.5rem 2.5rem 2rem; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.7),0 0 100px rgba(220,38,38,0.06); overflow:hidden; animation:panel-in 0.4s cubic-bezier(0.34,1.56,0.64,1) both; }
@keyframes panel-in { from{opacity:0;transform:scale(0.92) translateY(20px)} to{opacity:1;transform:scale(1) translateY(0)} }
.unauth-glow-bar { position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,transparent,#dc2626,#ef4444,#dc2626,transparent); border-radius:1.25rem 1.25rem 0 0; }
.corner { position:absolute; width:2rem; height:2rem; border-color:rgba(220,38,38,0.3); border-style:solid; }
.corner-tl { top:1rem; left:1rem; border-width:2px 0 0 2px; border-radius:4px 0 0 0; }
.corner-br { bottom:1rem; right:1rem; border-width:0 2px 2px 0; border-radius:0 0 4px 0; }
.unauth-brand { display:flex; align-items:center; justify-content:center; gap:0.875rem; margin-bottom:1.75rem; }
.brand-icon { width:2.5rem; height:2.5rem; object-fit:contain; filter:drop-shadow(0 0 6px rgba(220,38,38,0.4)); }
.brand-name { height:1.5rem; width:auto; filter:brightness(0) invert(1) drop-shadow(0 0 4px rgba(220,38,38,0.3)); }
.lock-ring { width:5rem; height:5rem; margin:0 auto 1.5rem; border-radius:50%; background:rgba(220,38,38,0.1); border:2px solid rgba(220,38,38,0.3); display:flex; align-items:center; justify-content:center; animation:pulse-ring 2.5s ease-in-out infinite; }
.lock-inner { width:3.25rem; height:3.25rem; border-radius:50%; background:rgba(220,38,38,0.15); display:flex; align-items:center; justify-content:center; }
.lock-inner i { font-size:1.375rem; color:#ef4444; }
@keyframes pulse-ring { 0%,100%{box-shadow:0 0 0 0 rgba(220,38,38,0.3)} 50%{box-shadow:0 0 0 8px rgba(220,38,38,0)} }
.unauth-title { font-size:1.75rem; font-weight:800; color:#f1f5f9; margin:0 0 0.5rem; letter-spacing:-0.02em; }
.unauth-subtitle { font-size:1rem; color:#94a3b8; margin:0 0 0.875rem; font-weight:500; }
.unauth-detail { font-size:0.85rem; color:#64748b; margin:0 0 1.5rem; line-height:1.6; max-width:400px; margin-left:auto; margin-right:auto; }
.perm-chip { display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); border-radius:0.5rem; margin-bottom:1.5rem; font-size:0.8rem; }
.perm-chip i { color:#fca5a5; }
.perm-label { color:#94a3b8; }
.perm-code { font-family:'Courier New',monospace; color:#fca5a5; font-weight:700; background:rgba(239,68,68,0.15); padding:0.125rem 0.375rem; border-radius:0.25rem; }
.unauth-actions { display:flex; gap:0.875rem; justify-content:center; flex-wrap:wrap; }
.btn-back,.btn-dashboard { display:inline-flex; align-items:center; gap:0.5rem; padding:0.7rem 1.5rem; border-radius:0.625rem; font-size:0.875rem; font-weight:700; cursor:pointer; text-decoration:none; transition:all 0.2s; border:none; }
.btn-back { background:rgba(255,255,255,0.06); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); }
.btn-back:hover { background:rgba(255,255,255,0.1); color:white; }
.btn-dashboard { background:linear-gradient(135deg,#dc2626,#b91c1c); color:white; box-shadow:0 4px 14px rgba(220,38,38,0.35); }
.btn-dashboard:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(220,38,38,0.45); }
@media(max-width:600px){ .unauth-panel{padding:2rem 1.25rem 1.5rem} .unauth-title{font-size:1.375rem} .unauth-actions{flex-direction:column} .btn-back,.btn-dashboard{width:100%;justify-content:center} }
</style>
