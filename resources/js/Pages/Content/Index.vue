<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
    content: Object,
    filters: Object,
});

const activeType = ref(props.filters.type || 'all');

const setType = (type) => {
    activeType.value = type;
    router.get('/content', { type: type === 'all' ? null : type }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Get localized title
const getTitle = (item) => {
    return locale.value === 'lv' ? item.title_lv : (item.title_en || item.title_lv);
};

// Get localized description
const getDescription = (item) => {
    return locale.value === 'lv' ? item.description_lv : (item.description_en || item.description_lv);
};

// Get YouTube thumbnail
const getThumbnail = (item) => {
    if (item.thumbnail) {
        return item.thumbnail;
    }

    // Extract YouTube video ID and get thumbnail
    if (item.video_url && item.video_platform === 'YouTube') {
        const match = item.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (match) {
            return `https://img.youtube.com/vi/${match[1]}/mqdefault.jpg`;
        }
    }

    return '/img/default-content.jpg';
};

// Format view count
const formatViews = (count) => {
    if (count >= 1000000) {
        return (count / 1000000).toFixed(1) + 'M';
    } else if (count >= 1000) {
        return (count / 1000).toFixed(1) + 'K';
    }
    return count;
};
</script>

<template>
    <Head :title="t('content.title')" />

    <MainLayout>
        <div class="content-page">
            <!-- Header -->
            <div class="content-header">
                <h1 class="content-title">{{ t('content.title') }}</h1>
                <p class="content-subtitle">{{ t('content.subtitle') }}</p>
            </div>

            <!-- Filter Tabs -->
            <div class="content-filters">
                <button
                    @click="setType('all')"
                    class="filter-tab"
                    :class="{ 'filter-tab-active': activeType === 'all' }"
                >
                    <i class="fas fa-th"></i>
                    <span>{{ t('content.all') }}</span>
                </button>
                <button
                    @click="setType('video')"
                    class="filter-tab"
                    :class="{ 'filter-tab-active': activeType === 'video' }"
                >
                    <i class="fas fa-play-circle"></i>
                    <span>{{ t('content.videos') }}</span>
                </button>
                <button
                    @click="setType('blog')"
                    class="filter-tab"
                    :class="{ 'filter-tab-active': activeType === 'blog' }"
                >
                    <i class="fas fa-newspaper"></i>
                    <span>{{ t('content.blogs') }}</span>
                </button>
            </div>

            <!-- Content Grid -->
            <div v-if="content.data.length > 0" class="content-grid">
                <Link
                    v-for="item in content.data"
                    :key="item.id"
                    :href="`/content/${item.slug}`"
                    class="content-card"
                >
                    <!-- Thumbnail -->
                    <div class="content-thumbnail">
                        <img :src="getThumbnail(item)" :alt="getTitle(item)">

                        <!-- Video Badge -->
                        <div v-if="item.type === 'video'" class="video-badge">
                            <i class="fas fa-play"></i>
                        </div>

                        <!-- Category Badge -->
                        <div v-if="item.category" class="category-badge">
                            {{ item.category }}
                        </div>
                    </div>

                    <!-- Content Info -->
                    <div class="content-info">
                        <h3 class="content-name">{{ getTitle(item) }}</h3>
                        <p class="content-description">{{ getDescription(item) }}</p>

                        <!-- Meta -->
                        <div class="content-meta">
                            <span class="meta-item">
                                <i class="fas fa-eye"></i>
                                {{ formatViews(item.view_count) }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-heart"></i>
                                {{ item.like_count }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-calendar"></i>
                                {{ new Date(item.published_at).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US') }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>{{ t('content.no_content') }}</h3>
                <p>{{ t('content.no_content_description') }}</p>
            </div>

            <!-- Pagination -->
            <div v-if="content.last_page > 1" class="pagination">
                <Link
                    v-for="link in content.links"
                    :key="link.label"
                    :href="link.url"
                    class="pagination-link"
                    :class="{
                        'pagination-link-active': link.active,
                        'pagination-link-disabled': !link.url
                    }"
                    v-html="link.label"
                ></Link>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.content-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 3rem 2rem;
}

.content-header {
    text-align: center;
    margin-bottom: 3rem;
}

.content-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.content-subtitle {
    font-size: 1.125rem;
    color: #6b7280;
    margin: 0;
}

.content-filters {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.filter-tab {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    color: #6b7280;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-tab:hover {
    border-color: #dc2626;
    color: #dc2626;
    transform: translateY(-2px);
}

.filter-tab-active {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-color: #dc2626;
    color: white;
}

.filter-tab i {
    font-size: 1.125rem;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
}

.content-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
}

.content-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(220, 38, 38, 0.2);
}

.content-thumbnail {
    position: relative;
    width: 100%;
    padding-top: 56.25%;
    overflow: hidden;
    background: #f3f4f6;
}

.content-thumbnail img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.content-card:hover .content-thumbnail img {
    transform: scale(1.05);
}

.video-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    width: 3rem;
    height: 3rem;
    background: rgba(220, 38, 38, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.category-badge {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    padding: 0.375rem 0.75rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.content-info {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.content-name {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.content-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.content-meta {
    display: flex;
    gap: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    color: #9ca3af;
}

.meta-item i {
    color: #dc2626;
    font-size: 0.875rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #374151;
    margin: 0 0 0.5rem 0;
}

.empty-state p {
    font-size: 1rem;
    color: #6b7280;
    margin: 0;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 3rem;
}

.pagination-link {
    padding: 0.625rem 1rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    color: #6b7280;
    font-weight: 600;
    transition: all 0.2s;
}

.pagination-link:hover:not(.pagination-link-disabled) {
    border-color: #dc2626;
    color: #dc2626;
}

.pagination-link-active {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-color: #dc2626;
    color: white;
}

.pagination-link-disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .content-page {
        padding: 2rem 1rem;
    }

    .content-title {
        font-size: 2rem;
    }

    .content-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .filter-tab span {
        display: none;
    }

    .filter-tab {
        padding: 0.75rem 1rem;
    }
}
</style>
