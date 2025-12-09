<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t, locale } = useI18n();

const props = defineProps({
    content: Object,
    filters: Object,
});

// State
const activeType = ref(props.filters.type || null);
const activePlatform = ref(null);
const activeCategory = ref(null);
const sortBy = ref('newest');
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const displayedItems = ref(3);
const allContent = ref(props.content.data || []);

// Available platforms & categories (dynamic from data)
const platforms = computed(() => {
    const unique = [...new Set(allContent.value.map(item => item.video_platform).filter(Boolean))];
    return unique;
});

const categories = computed(() => {
    const unique = [...new Set(allContent.value.map(item => item.category).filter(Boolean))];
    return unique;
});

// Filtered & Sorted Content
const filteredContent = computed(() => {
    let filtered = [...allContent.value];

    // Filter by type
    if (activeType.value) {
        filtered = filtered.filter(item => item.type === activeType.value);
    }

    // Filter by platform
    if (activePlatform.value) {
        filtered = filtered.filter(item => item.video_platform === activePlatform.value);
    }

    // Filter by category
    if (activeCategory.value) {
        filtered = filtered.filter(item => item.category === activeCategory.value);
    }

    // Sort
    switch (sortBy.value) {
        case 'newest':
            filtered.sort((a, b) => new Date(b.published_at) - new Date(a.published_at));
            break;
        case 'oldest':
            filtered.sort((a, b) => new Date(a.published_at) - new Date(b.published_at));
            break;
        case 'most_liked':
            filtered.sort((a, b) => b.like_count - a.like_count);
            break;
        case 'most_viewed':
            filtered.sort((a, b) => b.view_count - a.view_count);
            break;
    }

    return filtered;
});

// Displayed content (lazy load)
const displayedContent = computed(() => {
    return filteredContent.value.slice(0, displayedItems.value);
});

const hasMore = computed(() => {
    return displayedItems.value < filteredContent.value.length;
});

// Methods
const setType = (type) => {
    activeType.value = type;
    displayedItems.value = 3;
};

const setPlatform = (platform) => {
    activePlatform.value = activePlatform.value === platform ? null : platform;
    displayedItems.value = 3;
};

const setCategory = (category) => {
    activeCategory.value = activeCategory.value === category ? null : category;
    displayedItems.value = 3;
};

const loadMore = () => {
    displayedItems.value += 6;
};

const getTitle = (item) => {
    return locale.value === 'lv' ? item.title_lv : (item.title_en || item.title_lv);
};

const getDescription = (item) => {
    return locale.value === 'lv' ? item.description_lv : (item.description_en || item.description_lv);
};

const getThumbnail = (item) => {
    if (item.thumbnail && !item.thumbnail.includes('img.thumbnails')) {
        return item.thumbnail;
    }

    // YouTube thumbnail fallback
    if (item.video_url && item.video_platform === 'YouTube') {
        const match = item.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (match) {
            return `https://img.youtube.com/vi/${match[1]}/mqdefault.jpg`;
        }
    }

    return '/img/default-content.jpg';
};

const formatViews = (count) => {
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(1) + 'K';
    return count;
};

const openSource = (item) => {
    if (item.video_url) {
        window.open(item.video_url, '_blank');
    }
};

// Search with debounce
let searchTimeout;
const handleSearch = () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        isSearching.value = true;

        const query = searchQuery.value.toLowerCase();
        const results = allContent.value.filter(item => {
            const titleLv = item.title_lv.toLowerCase();
            const titleEn = (item.title_en || '').toLowerCase();
            const descLv = (item.description_lv || '').toLowerCase();
            const descEn = (item.description_en || '').toLowerCase();

            return titleLv.includes(query) ||
                titleEn.includes(query) ||
                descLv.includes(query) ||
                descEn.includes(query);
        });

        searchResults.value = results.slice(0, 5);
        isSearching.value = false;
    }, 300);
};

const selectSearchResult = (item) => {
    router.visit(`/content/${item.slug}`);
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
};

// Watch for search changes
watch(searchQuery, handleSearch);

// Reset displayed items when filters change
watch([activeType, activePlatform, activeCategory, sortBy], () => {
    displayedItems.value = 3;
});
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

            <!-- Search Bar -->
            <div class="search-section">
                <div class="search-bar">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="t('content.search_placeholder')"
                        class="search-input"
                    />
                    <button v-if="searchQuery" @click="clearSearch" class="search-clear">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Search Dropdown -->
                <Transition name="dropdown">
                    <div v-if="searchResults.length > 0" class="search-dropdown">
                        <button
                            v-for="result in searchResults"
                            :key="result.id"
                            @click="selectSearchResult(result)"
                            class="search-result"
                        >
                            <img :src="getThumbnail(result)" :alt="getTitle(result)" class="result-thumb">
                            <div class="result-info">
                                <div class="result-title">{{ getTitle(result) }}</div>
                                <div class="result-meta">
                                    <span class="result-type">
                                        <i :class="result.type === 'video' ? 'fas fa-play-circle' : 'fas fa-newspaper'"></i>
                                        {{ result.type === 'video' ? t('content.video') : t('content.blog') }}
                                    </span>
                                    <span v-if="result.category" class="result-category">{{ result.category }}</span>
                                </div>
                            </div>
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- Main Filter Tabs -->
            <div class="content-filters">
                <button
                    @click="setType(null)"
                    class="filter-tab"
                    :class="{ 'filter-tab-active': activeType === null }"
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

            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <!-- Platform Filter -->
                <div v-if="platforms.length > 0 && (activeType === 'video' || !activeType)" class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-video"></i>
                        {{ t('content.platform') }}
                    </label>
                    <div class="filter-chips">
                        <button
                            v-for="platform in platforms"
                            :key="platform"
                            @click="setPlatform(platform)"
                            class="filter-chip"
                            :class="{ 'filter-chip-active': activePlatform === platform }"
                        >
                            {{ platform }}
                        </button>
                    </div>
                </div>

                <!-- Category Filter -->
                <div v-if="categories.length > 0" class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-tag"></i>
                        {{ t('content.category') }}
                    </label>
                    <div class="filter-chips">
                        <button
                            v-for="category in categories"
                            :key="category"
                            @click="setCategory(category)"
                            class="filter-chip"
                            :class="{ 'filter-chip-active': activeCategory === category }"
                        >
                            {{ category }}
                        </button>
                    </div>
                </div>

                <!-- Sort Filter -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-sort"></i>
                        {{ t('content.sort_by') }}
                    </label>
                    <select v-model="sortBy" class="filter-select">
                        <option value="newest">{{ t('content.newest') }}</option>
                        <option value="oldest">{{ t('content.oldest') }}</option>
                        <option value="most_liked">{{ t('content.most_liked') }}</option>
                        <option value="most_viewed">{{ t('content.most_viewed') }}</option>
                    </select>
                </div>
            </div>

            <!-- Results Count -->
            <div class="results-info">
                <span>{{ filteredContent.length }} {{ t('content.results') }}</span>
            </div>

            <!-- Content Grid -->
            <div v-if="displayedContent.length > 0" class="content-grid">
                <div
                    v-for="item in displayedContent"
                    :key="item.id"
                    class="content-card"
                >
                    <!-- Thumbnail -->
                    <Link :href="`/content/${item.slug}`" class="content-thumbnail">
                        <img :src="getThumbnail(item)" :alt="getTitle(item)">

                        <!-- Video Badge -->
                        <div v-if="item.type === 'video'" class="video-badge">
                            <i class="fas fa-play"></i>
                        </div>

                        <!-- Category Badge -->
                        <div v-if="item.category" class="category-badge">
                            {{ item.category }}
                        </div>
                    </Link>

                    <!-- Content Info -->
                    <div class="content-info">
                        <Link :href="`/content/${item.slug}`" class="content-name">
                            {{ getTitle(item) }}
                        </Link>
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
                                {{ new Date(item.published_at).toLocaleDateString(locale === 'lv' ? 'lv-LV' : 'en-US') }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="content-actions">
                            <Link
                                :href="`/content/${item.slug}`"
                                class="btn-action btn-primary"
                            >
                                <i class="fas fa-eye"></i>
                                {{ t('content.view') }}
                            </Link>
                            <button
                                v-if="item.video_url"
                                @click="openSource(item)"
                                class="btn-action btn-secondary"
                            >
                                <i class="fab" :class="`fa-${item.video_platform?.toLowerCase()}`"></i>
                                {{ t('content.watch_on') }} {{ item.video_platform }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>{{ t('content.no_content') }}</h3>
                <p>{{ t('content.no_content_description') }}</p>
            </div>

            <!-- Load More Button -->
            <div v-if="hasMore" class="load-more-section">
                <button @click="loadMore" class="btn-load-more">
                    <i class="fas fa-plus-circle"></i>
                    {{ t('content.load_more') }}
                </button>
                <p class="load-more-info">
                    {{ t('content.showing') }} {{ displayedItems }} {{ t('content.of') }} {{ filteredContent.length }}
                </p>
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

/* Header */
.content-header {
    text-align: center;
    margin-bottom: 2rem;
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

/* Search */
.search-section {
    max-width: 600px;
    margin: 0 auto 2rem;
    position: relative;
}

.search-bar {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 1.25rem;
    color: #9ca3af;
    font-size: 1.125rem;
}

.search-input {
    width: 100%;
    padding: 1rem 3.5rem 1rem 3.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 9999px;
    font-size: 1rem;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.search-clear {
    position: absolute;
    right: 1.25rem;
    width: 2rem;
    height: 2rem;
    background: #f3f4f6;
    border: none;
    border-radius: 50%;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.search-clear:hover {
    background: #dc2626;
    color: white;
}

/* Search Dropdown */
.search-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    right: 0;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
}

.search-result {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    background: white;
    border: none;
    border-bottom: 1px solid #f3f4f6;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
}

.search-result:hover {
    background: #fef2f2;
}

.result-thumb {
    width: 80px;
    height: 45px;
    border-radius: 0.5rem;
    object-fit: cover;
    flex-shrink: 0;
}

.result-info {
    flex: 1;
    min-width: 0;
}

.result-title {
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.result-meta {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.25rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.result-type i {
    color: #dc2626;
}

/* Filters */
.content-filters {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
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

/* Advanced Filters */
.advanced-filters {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.filter-group {
    margin-bottom: 1.5rem;
}

.filter-group:last-child {
    margin-bottom: 0;
}

.filter-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
}

.filter-label i {
    color: #dc2626;
}

.filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-chip {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    border: 2px solid transparent;
    border-radius: 0.5rem;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-chip:hover {
    background: #fee2e2;
    color: #dc2626;
}

.filter-chip-active {
    background: #fee2e2;
    border-color: #dc2626;
    color: #dc2626;
}

.filter-select {
    width: 100%;
    max-width: 300px;
    padding: 0.625rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-select:focus {
    outline: none;
    border-color: #dc2626;
}

/* Results Info */
.results-info {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #6b7280;
    font-weight: 500;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
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
    transition: color 0.2s;
}

.content-name:hover {
    color: #dc2626;
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
}

.content-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.75rem;
}

.btn-action {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    padding: 0.625rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Load More */
.load-more-section {
    text-align: center;
}

.btn-load-more {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    border-radius: 0.75rem;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-load-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(220, 38, 38, 0.3);
}

.load-more-info {
    margin-top: 1rem;
    color: #6b7280;
    font-size: 0.875rem;
}

/* Empty State */
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

/* Animations */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-0.5rem);
}

/* Responsive */
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

    .advanced-filters {
        padding: 1rem;
    }

    .filter-select {
        max-width: 100%;
    }

    .content-actions {
        flex-direction: column;
    }
}
</style>
