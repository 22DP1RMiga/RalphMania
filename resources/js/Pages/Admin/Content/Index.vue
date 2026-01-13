<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    content: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Filters
const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');

// Debounced search
let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get('/admin/content', {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    typeFilter.value = '';
    statusFilter.value = '';
    router.get('/admin/content');
};

// Content types with translation keys
const contentTypes = computed(() => [
    { value: 'video', labelKey: 'admin.content.types.video', icon: 'fas fa-video' },
    { value: 'blog', labelKey: 'admin.content.types.blog', icon: 'fas fa-blog' },
    { value: 'news', labelKey: 'admin.content.types.news', icon: 'fas fa-newspaper' },
    { value: 'announcement', labelKey: 'admin.content.types.announcement', icon: 'fas fa-bullhorn' },
]);

const getTypeInfo = (type) => {
    const found = contentTypes.value.find(t => t.value === type);
    return found ? { label: t(found.labelKey), icon: found.icon } : { label: type, icon: 'fas fa-file' };
};

/**
 * Get thumbnail URL based on content type
 * - Videos: /img/thumbnails/{thumbnail} or placeholder
 * - Blogs: /img/Blogs/{featured_image} or placeholder
 * - Others: /img/thumbnails/{thumbnail} or placeholder
 */
const getThumbnailUrl = (item) => {
    const placeholder = '/img/thumbnails/no-content-placeholder.png';

    if (item.type === 'blog') {
        // Blogs use featured_image from /img/Blogs/
        if (item.featured_image) {
            // Check if it's already a full path or URL
            if (item.featured_image.startsWith('http') || item.featured_image.startsWith('/')) {
                return item.featured_image;
            }
            return `/img/Blogs/${item.featured_image}`;
        }
        return '/img/Blogs/no-content-placeholder.png';
    }

    if (item.type === 'video') {
        // Videos use thumbnail from /img/thumbnails/
        if (item.thumbnail) {
            if (item.thumbnail.startsWith('http') || item.thumbnail.startsWith('/')) {
                return item.thumbnail;
            }
            return `/img/thumbnails/${item.thumbnail}`;
        }
        return placeholder;
    }

    // News, announcements, etc. - use thumbnail or featured_image
    if (item.thumbnail) {
        if (item.thumbnail.startsWith('http') || item.thumbnail.startsWith('/')) {
            return item.thumbnail;
        }
        return `/img/thumbnails/${item.thumbnail}`;
    }
    if (item.featured_image) {
        if (item.featured_image.startsWith('http') || item.featured_image.startsWith('/')) {
            return item.featured_image;
        }
        return `/img/thumbnails/${item.featured_image}`;
    }

    return placeholder;
};

/**
 * Get content title based on current locale
 */
const getTitle = (item) => {
    return locale.value === 'lv' ? (item.title_lv || item.title_en) : (item.title_en || item.title_lv);
};

/**
 * Get secondary title (opposite locale)
 */
const getSecondaryTitle = (item) => {
    return locale.value === 'lv' ? item.title_en : item.title_lv;
};

// Delete content
const deleteContent = (id, title) => {
    if (confirm(t('admin.content.deleteConfirm', { name: title }))) {
        router.delete(`/admin/content/${id}`, {
            preserveScroll: true,
        });
    }
};

// Toggle publish status
const togglePublish = (content) => {
    router.put(`/admin/content/${content.id}`, {
        ...content,
        is_published: !content.is_published,
    }, {
        preserveScroll: true,
    });
};

// Format date based on locale
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Stats by type
const getTypeCount = (type) => {
    return props.content.data?.filter(c => c.type === type).length || 0;
};
</script>

<template>
    <Head :title="t('admin.content.index.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.content.index.title') }}</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">{{ t('admin.content.index.subtitle') }}</p>
            </div>
            <Link href="/admin/content/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span class="btn-text">{{ t('admin.content.index.newContent') }}</span>
            </Link>
        </div>

        <!-- Quick Stats -->
        <div class="stats-row">
            <div v-for="type in contentTypes" :key="type.value" class="stat-mini">
                <i :class="type.icon"></i>
                <span class="stat-count">{{ getTypeCount(type.value) }}</span>
                <span class="stat-label">{{ t(type.labelKey) }}</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('admin.content.searchPlaceholder')"
                        class="search-input"
                    >
                </div>

                <select v-model="typeFilter" @change="applyFilters" class="filter-select">
                    <option value="">{{ t('admin.content.allTypes') }}</option>
                    <option v-for="type in contentTypes" :key="type.value" :value="type.value">
                        {{ t(type.labelKey) }}
                    </option>
                </select>

                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">{{ t('admin.content.allStatuses') }}</option>
                    <option value="published">{{ t('admin.content.status.published') }}</option>
                    <option value="draft">{{ t('admin.content.status.draft') }}</option>
                </select>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <span class="btn-text">{{ t('admin.common.clear') }}</span>
                </button>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <div v-for="item in content.data" :key="item.id" class="content-card">
                <!-- Thumbnail -->
                <div class="content-thumb">
                    <img
                        :src="getThumbnailUrl(item)"
                        :alt="getTitle(item)"
                        @error="$event.target.src = '/img/thumbnails/no-content-placeholder.png'"
                    >
                    <!-- Video duration badge -->
                    <span v-if="item.type === 'video' && item.duration" class="duration-badge">
                        {{ Math.floor(item.duration / 60) }}:{{ String(item.duration % 60).padStart(2, '0') }}
                    </span>
                    <!-- Type badge -->
                    <span :class="['type-badge', `type-${item.type}`]">
                        <i :class="getTypeInfo(item.type).icon"></i>
                        {{ getTypeInfo(item.type).label }}
                    </span>
                    <!-- Featured badge -->
                    <span v-if="item.is_featured" class="featured-badge">
                        <i class="fas fa-star"></i>
                    </span>
                </div>

                <!-- Content Info -->
                <div class="content-body">
                    <h3 class="content-title">{{ getTitle(item) }}</h3>
                    <p class="content-title-secondary" v-if="getSecondaryTitle(item)">
                        {{ getSecondaryTitle(item) }}
                    </p>

                    <div class="content-meta">
                        <span class="meta-item" :title="t('admin.content.views')">
                            <i class="fas fa-eye"></i>
                            {{ item.view_count || 0 }}
                        </span>
                        <span class="meta-item" :title="t('admin.content.likes')">
                            <i class="fas fa-heart"></i>
                            {{ item.like_count || 0 }}
                        </span>
                        <span class="meta-item" :title="t('admin.content.date')">
                            <i class="fas fa-calendar"></i>
                            {{ formatDate(item.published_at || item.created_at) }}
                        </span>
                    </div>

                    <!-- Category if exists -->
                    <div v-if="item.category" class="content-category">
                        <i class="fas fa-folder"></i>
                        {{ item.category }}
                    </div>

                    <div class="content-footer">
                        <button
                            @click="togglePublish(item)"
                            :class="['status-toggle', item.is_published ? 'published' : 'draft']"
                            :title="item.is_published ? t('admin.content.clickToDraft') : t('admin.content.clickToPublish')"
                        >
                            <i :class="item.is_published ? 'fas fa-check' : 'fas fa-edit'"></i>
                            {{ item.is_published ? t('admin.content.status.published') : t('admin.content.status.draft') }}
                        </button>

                        <div class="content-actions">
                            <Link
                                :href="`/content/${item.slug}`"
                                class="btn-icon btn-icon-view"
                                :title="t('admin.common.view')"
                                target="_blank"
                            >
                                <i class="fas fa-external-link-alt"></i>
                            </Link>
                            <Link
                                :href="`/admin/content/${item.id}/edit`"
                                class="btn-icon btn-icon-edit"
                                :title="t('admin.common.edit')"
                            >
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button
                                @click="deleteContent(item.id, getTitle(item))"
                                class="btn-icon btn-icon-delete"
                                :title="t('admin.common.delete')"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="content.data.length === 0" class="empty-state">
                <i class="fas fa-newspaper"></i>
                <p>{{ t('admin.content.noContent') }}</p>
                <Link href="/admin/content/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    {{ t('admin.content.addFirstContent') }}
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="content.links && content.links.length > 3" class="pagination">
            <Link
                v-for="link in content.links"
                :key="link.label"
                :href="link.url"
                :class="['page-link', { active: link.active, disabled: !link.url }]"
                v-html="link.label"
            />
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-subtitle {
    color: #6b7280;
    margin: 0;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-mini {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-mini i {
    font-size: 1.5rem;
    color: #dc2626;
}

.stat-count {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
}

/* Filters */
.filters-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 200px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.95rem;
}

.search-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.filter-select {
    padding: 0.625rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
    min-width: 140px;
}

.filter-select:focus {
    outline: none;
    border-color: #dc2626;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

/* Content Card */
.content-card {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
}

.content-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.content-thumb {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: #f3f4f6;
}

.content-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.content-card:hover .content-thumb img {
    transform: scale(1.05);
}

/* Badges */
.type-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    backdrop-filter: blur(4px);
}

.type-video { background: rgba(239, 68, 68, 0.9); color: white; }
.type-blog { background: rgba(59, 130, 246, 0.9); color: white; }
.type-news { background: rgba(16, 185, 129, 0.9); color: white; }
.type-announcement { background: rgba(245, 158, 11, 0.9); color: white; }

.duration-badge {
    position: absolute;
    bottom: 0.75rem;
    right: 0.75rem;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.featured-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 2rem;
    height: 2rem;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Content Body */
.content-body {
    padding: 1.25rem;
}

.content-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.content-title-secondary {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0 0 0.75rem;
    font-style: italic;
}

.content-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.meta-item i {
    color: #9ca3af;
}

.content-category {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    color: #6b7280;
    margin-bottom: 0.75rem;
}

.content-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

/* Status Toggle */
.status-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border: none;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.status-toggle.published {
    background: #d1fae5;
    color: #065f46;
}

.status-toggle.published:hover {
    background: #a7f3d0;
}

.status-toggle.draft {
    background: #fef3c7;
    color: #92400e;
}

.status-toggle.draft:hover {
    background: #fde68a;
}

.content-actions {
    display: flex;
    gap: 0.5rem;
}

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 0.75rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state p {
    margin: 0 0 1.5rem;
    font-size: 1.125rem;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.page-link:hover:not(.disabled):not(.active) {
    background: #f3f4f6;
}

.page-link.active {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}

.page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover {
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-icon-view {
    background: #dbeafe;
    color: #2563eb;
}

.btn-icon-view:hover {
    background: #2563eb;
    color: white;
}

.btn-icon-edit {
    background: #fef3c7;
    color: #d97706;
}

.btn-icon-edit:hover {
    background: #d97706;
    color: white;
}

.btn-icon-delete {
    background: #fee2e2;
    color: #dc2626;
}

.btn-icon-delete:hover {
    background: #dc2626;
    color: white;
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-select {
        width: 100%;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .btn-text {
        display: none;
    }

    .stat-mini {
        padding: 0.75rem 1rem;
    }

    .stat-count {
        font-size: 1.25rem;
    }

    .stat-label {
        font-size: 0.625rem;
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .stat-mini {
        flex-direction: column;
        text-align: center;
        gap: 0.25rem;
    }

    .content-thumb {
        height: 160px;
    }

    .content-footer {
        flex-direction: column;
        gap: 0.75rem;
    }

    .content-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
