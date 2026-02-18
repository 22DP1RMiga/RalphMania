<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const { t, locale } = useI18n();

defineOptions({
    layout: ShopLayout
});

// FAQ categories and questions
const faqCategories = [
    {
        id: 'orders',
        icon: 'fas fa-shopping-bag',
        titleKey: 'shop_faq.categories.orders',
        questions: [
            { questionKey: 'shop_faq.orders.q1', answerKey: 'shop_faq.orders.a1' },
            { questionKey: 'shop_faq.orders.q2', answerKey: 'shop_faq.orders.a2' },
            { questionKey: 'shop_faq.orders.q3', answerKey: 'shop_faq.orders.a3' },
            { questionKey: 'shop_faq.orders.q4', answerKey: 'shop_faq.orders.a4' },
        ]
    },
    {
        id: 'shipping',
        icon: 'fas fa-truck',
        titleKey: 'shop_faq.categories.shipping',
        questions: [
            { questionKey: 'shop_faq.shipping.q1', answerKey: 'shop_faq.shipping.a1' },
            { questionKey: 'shop_faq.shipping.q2', answerKey: 'shop_faq.shipping.a2' },
            { questionKey: 'shop_faq.shipping.q3', answerKey: 'shop_faq.shipping.a3' },
        ]
    },
    {
        id: 'payment',
        icon: 'fas fa-credit-card',
        titleKey: 'shop_faq.categories.payment',
        questions: [
            { questionKey: 'shop_faq.payment.q1', answerKey: 'shop_faq.payment.a1' },
            { questionKey: 'shop_faq.payment.q2', answerKey: 'shop_faq.payment.a2' },
            { questionKey: 'shop_faq.payment.q3', answerKey: 'shop_faq.payment.a3' },
        ]
    },
    {
        id: 'returns',
        icon: 'fas fa-undo',
        titleKey: 'shop_faq.categories.returns',
        questions: [
            { questionKey: 'shop_faq.returns.q1', answerKey: 'shop_faq.returns.a1' },
            { questionKey: 'shop_faq.returns.q2', answerKey: 'shop_faq.returns.a2' },
            { questionKey: 'shop_faq.returns.q3', answerKey: 'shop_faq.returns.a3' },
        ]
    },
    {
        id: 'account',
        icon: 'fas fa-user',
        titleKey: 'shop_faq.categories.account',
        questions: [
            { questionKey: 'shop_faq.account.q1', answerKey: 'shop_faq.account.a1' },
            { questionKey: 'shop_faq.account.q2', answerKey: 'shop_faq.account.a2' },
        ]
    },
];

// Track open questions
const openQuestions = ref({});

const toggleQuestion = (categoryId, questionIndex) => {
    const key = `${categoryId}-${questionIndex}`;
    openQuestions.value[key] = !openQuestions.value[key];
};

const isOpen = (categoryId, questionIndex) => {
    return openQuestions.value[`${categoryId}-${questionIndex}`] || false;
};

// Active category filter
const activeCategory = ref(null);

const filteredCategories = ref(faqCategories);

const setCategory = (categoryId) => {
    activeCategory.value = activeCategory.value === categoryId ? null : categoryId;
};
</script>

<template>
    <Head :title="t('shop_faq.title')" />

    <div class="faq-page">
        <!-- Hero Section -->
        <div class="faq-hero">
            <div class="hero-content">
                <h1 class="hero-title">{{ t('shop_faq.title') }}</h1>
                <p class="hero-subtitle">{{ t('shop_faq.subtitle') }}</p>
            </div>
        </div>

        <div class="faq-container">
            <!-- Category Filters -->
            <div class="category-filters">
                <button
                    @click="setCategory(null)"
                    class="category-btn"
                    :class="{ active: activeCategory === null }"
                >
                    <i class="fas fa-th"></i>
                    {{ t('shop_faq.all_categories') }}
                </button>
                <button
                    v-for="category in faqCategories"
                    :key="category.id"
                    @click="setCategory(category.id)"
                    class="category-btn"
                    :class="{ active: activeCategory === category.id }"
                >
                    <i :class="category.icon"></i>
                    {{ t(category.titleKey) }}
                </button>
            </div>

            <!-- FAQ Categories -->
            <div class="faq-categories">
                <div
                    v-for="category in faqCategories"
                    :key="category.id"
                    v-show="!activeCategory || activeCategory === category.id"
                    class="faq-category"
                >
                    <div class="category-header">
                        <div class="category-icon">
                            <i :class="category.icon"></i>
                        </div>
                        <h2 class="category-title">{{ t(category.titleKey) }}</h2>
                    </div>

                    <div class="questions-list">
                        <div
                            v-for="(question, index) in category.questions"
                            :key="index"
                            class="question-item"
                            :class="{ open: isOpen(category.id, index) }"
                        >
                            <button
                                @click="toggleQuestion(category.id, index)"
                                class="question-header"
                            >
                                <span class="question-text">{{ t(question.questionKey) }}</span>
                                <i class="fas fa-chevron-down question-icon"></i>
                            </button>
                            <Transition name="accordion">
                                <div v-if="isOpen(category.id, index)" class="answer-content">
                                    <p>{{ t(question.answerKey) }}</p>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Have Questions -->
            <div class="still-questions">
                <div class="still-questions-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h3>{{ t('shop_faq.still_questions') }}</h3>
                <p>{{ t('shop_faq.still_questions_text') }}</p>
                <Link href="/shop/contact" class="btn-contact">
                    <i class="fas fa-envelope"></i>
                    {{ t('shop_faq.contact_us') }}
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.faq-page {
    min-height: 100vh;
    background: #f8fafc;
}

/* Hero */
.faq-hero {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    padding: 4rem 2rem;
    text-align: center;
    color: white;
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

/* Container */
.faq-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem;
}

/* Category Filters */
.category-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 2rem;
    justify-content: center;
}

.category-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.category-btn:hover {
    border-color: #dc2626;
    color: #dc2626;
}

.category-btn.active {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}

/* FAQ Categories */
.faq-category {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.category-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.category-icon {
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.category-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

/* Questions */
.questions-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.question-item {
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
    transition: all 0.2s;
}

.question-item:hover {
    border-color: #fca5a5;
}

.question-item.open {
    border-color: #dc2626;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 1rem 1.25rem;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
}

.question-text {
    font-weight: 600;
    color: #111827;
    font-size: 0.95rem;
    flex: 1;
    padding-right: 1rem;
}

.question-icon {
    color: #9ca3af;
    transition: transform 0.3s;
    flex-shrink: 0;
}

.question-item.open .question-icon {
    transform: rotate(180deg);
    color: #dc2626;
}

.answer-content {
    padding: 0 1.25rem 1.25rem;
}

.answer-content p {
    color: #6b7280;
    line-height: 1.7;
    margin: 0;
}

/* Still Questions */
.still-questions {
    background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
    border: 2px solid #fecaca;
    border-radius: 1rem;
    padding: 2.5rem;
    text-align: center;
    margin-top: 2rem;
}

.still-questions-icon {
    width: 4rem;
    height: 4rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.5rem;
}

.still-questions h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem;
}

.still-questions p {
    color: #6b7280;
    margin: 0 0 1.5rem;
}

.btn-contact {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 0.75rem;
    color: white;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-contact:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Accordion Animation */
.accordion-enter-active,
.accordion-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
    opacity: 0;
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
}

.accordion-enter-to,
.accordion-leave-from {
    max-height: 500px;
}

/* Responsive */
@media (max-width: 768px) {
    .faq-hero {
        padding: 3rem 1.5rem;
    }

    .hero-title {
        font-size: 2rem;
    }

    .faq-container {
        padding: 1.5rem;
    }

    .category-filters {
        justify-content: flex-start;
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 0.5rem;
    }

    .category-btn {
        white-space: nowrap;
        flex-shrink: 0;
    }
}
</style>
