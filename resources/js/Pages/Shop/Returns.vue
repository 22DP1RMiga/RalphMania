<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Return steps
const returnSteps = [
    { number: 1, icon: 'fas fa-envelope', title: 'Sazinieties ar mums', desc: 'Nosūtiet e-pastu uz returns@ralphmania.lv ar pasūtījuma numuru.' },
    { number: 2, icon: 'fas fa-check-circle', title: 'Saņemiet apstiprinājumu', desc: 'Mēs nosūtīsim jums atgriešanas instrukcijas un adresi.' },
    { number: 3, icon: 'fas fa-box', title: 'Nosūtiet preci', desc: 'Droši iepakojiet preci un nosūtiet to uz norādīto adresi.' },
    { number: 4, icon: 'fas fa-undo', title: 'Saņemiet atmaksu', desc: 'Pēc preces saņemšanas atmaksa tiks veikta 14 dienu laikā.' },
];

// Return conditions
const conditions = [
    { icon: 'fas fa-calendar-check', text: '14 dienu laikā no saņemšanas' },
    { icon: 'fas fa-tag', text: 'Visas etiķetes ir neskartas' },
    { icon: 'fas fa-tshirt', text: 'Prece nav valkāta vai izmantota' },
    { icon: 'fas fa-box-open', text: 'Oriģinālais iepakojums' },
    { icon: 'fas fa-receipt', text: 'Pirkuma apliecinājums' },
];

// Non-returnable items
const nonReturnable = [
    { icon: 'fas fa-gift', text: 'Dāvanu kartes' },
    { icon: 'fas fa-tshirt', text: 'Personalizētas preces' },
    { icon: 'fas fa-percent', text: 'Izpārdošanas preces (ar 50%+ atlaidi)' },
    { icon: 'fas fa-box-open', text: 'Atvērtas higiēnas preces' },
];
</script>

<template>
    <ShopLayout>
        <Head title="Atgriešana un atmaksa" />

        <div class="returns-page">
            <!-- Hero Section -->
            <div class="returns-hero">
                <div class="hero-content">
                    <div class="hero-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h1 class="hero-title">Atgriešana un atmaksa</h1>
                    <p class="hero-subtitle">Vienkārša un ātra atgriešanas process</p>
                </div>
            </div>

            <div class="returns-container">
                <!-- Guarantee Banner -->
                <div class="guarantee-banner">
                    <div class="guarantee-badge">
                        <span class="days">14</span>
                        <span class="label">dienas</span>
                    </div>
                    <div class="guarantee-content">
                        <h3>14 dienu atgriešanas garantija</h3>
                        <p>Ja neesat apmierināts ar savu pirkumu, varat to atgriezt 14 dienu laikā.</p>
                    </div>
                </div>

                <!-- Return Process -->
                <section class="section">
                    <h2 class="section-title">
                        <i class="fas fa-route"></i>
                        Atgriešanas process
                    </h2>

                    <div class="steps-grid">
                        <div v-for="step in returnSteps" :key="step.number" class="step-card">
                            <div class="step-header">
                                <div class="step-number">{{ step.number }}</div>
                                <div class="step-icon">
                                    <i :class="step.icon"></i>
                                </div>
                            </div>
                            <h3 class="step-title">{{ step.title }}</h3>
                            <p class="step-desc">{{ step.desc }}</p>
                        </div>
                    </div>
                </section>

                <!-- Return Conditions -->
                <section class="section">
                    <h2 class="section-title">
                        <i class="fas fa-clipboard-check"></i>
                        Atgriešanas nosacījumi
                    </h2>

                    <div class="conditions-card">
                        <div class="conditions-list">
                            <div v-for="condition in conditions" :key="condition.text" class="condition-item">
                                <div class="condition-icon success">
                                    <i :class="condition.icon"></i>
                                </div>
                                <span>{{ condition.text }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Non-Returnable Items -->
                <section class="section">
                    <h2 class="section-title">
                        <i class="fas fa-times-circle"></i>
                        Preces, kuras nevar atgriezt
                    </h2>

                    <div class="non-returnable-card">
                        <div class="non-returnable-list">
                            <div v-for="item in nonReturnable" :key="item.text" class="non-returnable-item">
                                <div class="condition-icon danger">
                                    <i :class="item.icon"></i>
                                </div>
                                <span>{{ item.text }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Refund Info -->
                <section class="section">
                    <h2 class="section-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Atmaksas informācija
                    </h2>

                    <div class="refund-card">
                        <div class="refund-grid">
                            <div class="refund-item">
                                <div class="refund-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="refund-content">
                                    <h4>Atmaksa uz karti</h4>
                                    <p>Nauda tiek atgriezta uz to pašu maksājuma metodi, kas izmantota pirkumam.</p>
                                </div>
                            </div>
                            <div class="refund-item">
                                <div class="refund-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="refund-content">
                                    <h4>Apstrādes laiks</h4>
                                    <p>Atmaksa tiek veikta 14 dienu laikā pēc preces saņemšanas.</p>
                                </div>
                            </div>
                            <div class="refund-item">
                                <div class="refund-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="refund-content">
                                    <h4>Piegādes izmaksas</h4>
                                    <p>Sākotnējās piegādes izmaksas netiek atmaksātas, izņemot bojātu preču gadījumā.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Contact CTA -->
                <div class="contact-cta">
                    <div class="cta-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Nepieciešama palīdzība?</h3>
                    <p>Mūsu klientu apkalpošanas komanda ir gatava palīdzēt.</p>
                    <div class="cta-actions">
                        <Link href="/shop/contact" class="btn-primary">
                            <i class="fas fa-envelope"></i>
                            Sazināties ar mums
                        </Link>
                        <a href="mailto:ralphmania.roltonslv@gmail.com" class="btn-secondary">
                            <i class="fas fa-at"></i>
                            ralphmania.roltonslv@gmail.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.returns-page {
    min-height: 100vh;
    background: #f8fafc;
}

.returns-hero {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    padding: 4rem 2rem;
    text-align: center;
    color: white;
}

.hero-icon {
    width: 5rem;
    height: 5rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.hero-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
}

.returns-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem;
}

.guarantee-banner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 2px solid #10b981;
    border-radius: 1rem;
    margin-bottom: 2rem;
}

.guarantee-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 5rem;
    height: 5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    color: white;
    flex-shrink: 0;
}

.guarantee-badge .days {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1;
}

.guarantee-badge .label {
    font-size: 0.625rem;
    text-transform: uppercase;
    font-weight: 600;
}

.guarantee-content h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #065f46;
    margin: 0 0 0.25rem;
}

.guarantee-content p {
    color: #047857;
    margin: 0;
    font-size: 0.95rem;
}

.section {
    margin-bottom: 2.5rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
}

.section-title i {
    color: #dc2626;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.step-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    text-align: center;
    transition: all 0.2s;
}

.step-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.step-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.step-number {
    width: 2rem;
    height: 2rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
}

.step-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: #fee2e2;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc2626;
    font-size: 1rem;
}

.step-title {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem;
}

.step-desc {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

.conditions-card,
.non-returnable-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.conditions-list,
.non-returnable-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.condition-item,
.non-returnable-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}

.condition-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.condition-icon.success {
    background: #dcfce7;
    color: #15803d;
}

.condition-icon.danger {
    background: #fee2e2;
    color: #dc2626;
}

.condition-item span,
.non-returnable-item span {
    font-size: 0.9rem;
    color: #374151;
}

.refund-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.refund-grid {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.refund-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.refund-icon {
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.refund-content h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
}

.refund-content p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.contact-cta {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-radius: 1rem;
    padding: 2.5rem;
    text-align: center;
    color: white;
}

.cta-icon {
    width: 4rem;
    height: 4rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.contact-cta h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
}

.contact-cta p {
    opacity: 0.8;
    margin: 0 0 1.5rem;
}

.cta-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
    .returns-hero {
        padding: 3rem 1.5rem;
    }

    .hero-title {
        font-size: 2rem;
    }

    .returns-container {
        padding: 1.5rem;
    }

    .guarantee-banner {
        flex-direction: column;
        text-align: center;
    }

    .steps-grid {
        grid-template-columns: 1fr;
    }

    .conditions-list,
    .non-returnable-list {
        grid-template-columns: 1fr;
    }
}
</style>
