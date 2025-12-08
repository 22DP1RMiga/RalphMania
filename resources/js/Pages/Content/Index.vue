<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';

const isLoading = ref(true);
const content = ref([]);
const activeFilter = ref('all');

const fetchContent = async (type = null) => {
    isLoading.value = true;
    try {
        const url = type
            ? `/api/v1/content?type=${type}`
            : '/api/v1/content';
        const response = await axios.get(url);
        content.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching content:', error);
    } finally {
        isLoading.value = false;
    }
};

const filterContent = (type) => {
    activeFilter.value = type;
    fetchContent(type === 'all' ? null : type);
};

onMounted(() => {
    fetchContent();
});
</script>

<template>
    <Head :title="$t('nav.content')" />

    <MainLayout>
        <!-- Content Hero -->
        <section class="content-hero">
            <div class="hero-container">
                <h1 class="hero-title">{{ $t('content.hero.title') }}</h1>
                <p class="hero-subtitle">{{ $t('content.hero.subtitle') }}</p>
            </div>
            <!-- Wave -->
            <div class="hero-wave">
                <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill="#ffffff"
                        d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,64C960,75,1056,85,1152,80C1248,75,1344,53,1392,42.7L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"
                    ></path>
                </svg>
            </div>
        </section>

        <!-- Filter Tabs -->
        <section class="filter-section">
            <div class="section-container">
                <div class="filter-tabs">
                    <button
                        @click="filterContent('all')"
                        :class="['filter-tab', { active: activeFilter === 'all' }]"
                    >
                        {{ $t('content.filter.all') }}
                    </button>
                    <button
                        @click="filterContent('video')"
                        :class="['filter-tab', { active: activeFilter === 'video' }]"
                    >
                        {{ $t('content.filter.videos') }}
                    </button>
                    <button
                        @click="filterContent('blog')"
                        :class="['filter-tab', { active: activeFilter === 'blog' }]"
                    >
                        {{ $t('content.filter.blogs') }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Content Grid -->
        <section class="content-section">
            <div class="section-container">
                <!-- Loading Spinner -->
                <div v-if="isLoading" class="loading-container">
                    <LoadingSpinner size="lg" :text="$t('common.loading')" />
                </div>

                <!-- Content Grid -->
                <div v-else class="content-grid">
                    <a
                        v-for="item in content"
                        :key="item.id"
                        :href="`/content/${item.slug}`"
                        class="content-card"
                    >
                        <div class="content-thumbnail">
                            <img :src="item.thumbnail" :alt="item.title_lv">
                            <span class="content-type-badge">{{ item.type }}</span>
                            <div class="content-overlay">
                                <i class="fas fa-play-circle play-icon"></i>
                            </div>
                        </div>
                        <div class="content-info">
                            <h3 class="content-title">{{ item.title_lv }}</h3>
                            <p class="content-description">{{ item.description_lv }}</p>
                            <div class="content-meta">
                                <span class="content-views">
                                    <i class="fas fa-eye"></i>
                                    {{ item.view_count || 0 }}
                                </span>
                                <span class="content-date">
                                    {{ new Date(item.published_at).toLocaleDateString('lv') }}
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Empty State -->
                    <div v-if="content.length === 0" class="empty-state">
                        <i class="fas fa-video empty-icon"></i>
                        <p class="empty-text">{{ $t('content.no_content') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
/* Hero Section */
.content-hero {
    position: relative;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    color: white;
    padding: 6rem 2rem 8rem;
    overflow: hidden;
}

.content-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.1;
}

.hero-container {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
    z-index: 1;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.5rem;
    opacity: 0.95;
}

.hero-wave {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}

.hero-wave svg {
    position: relative;
    display: block;
    width: calc(100% + 4px);
    height: 120px;
    margin-left: -2px;
}

/* Filter Section */
.filter-section {
    padding: 2rem 2rem 0;
    background-color: white;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
}

.filter-tabs {
    display: flex;
    gap: 1rem;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0;
}

.filter-tab {
    padding: 1rem 2rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 1rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: -2px;
}

.filter-tab:hover {
    color: #dc2626;
}

.filter-tab.active {
    color: #dc2626;
    border-bottom-color: #dc2626;
}

/* Content Section */
.content-section {
    padding: 4rem 2rem;
}

.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

.content-card {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.content-thumbnail {
    position: relative;
    height: 200px;
    overflow: hidden;
    background-color: #f9fafb;
}

.content-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.content-card:hover .content-thumbnail img {
    transform: scale(1.05);
}

.content-type-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background-color: #dc2626;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.content-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.content-card:hover .content-overlay {
    opacity: 1;
}

.play-icon {
    font-size: 3rem;
    color: white;
}

.content-info {
    padding: 1.5rem;
}

.content-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.content-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.content-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: #9ca3af;
}

.content-views {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-text {
    font-size: 1.125rem;
    color: #9ca3af;
}

@media (max-width: 640px) {
    .hero-title {
        font-size: 2rem;
    }

    .filter-tabs {
        gap: 0.5rem;
    }

    .filter-tab {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}
</style>
