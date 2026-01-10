<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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

// Content types
const contentTypes = [
    { value: 'video', label: 'Video', icon: 'fas fa-video' },
    { value: 'blog', label: 'Blogs', icon: 'fas fa-blog' },
    { value: 'news', label: 'Ziņas', icon: 'fas fa-newspaper' },
    { value: 'announcement', label: 'Paziņojumi', icon: 'fas fa-bullhorn' },
];

const getTypeInfo = (type) => {
    return contentTypes.find(t => t.value === type) || { label: type, icon: 'fas fa-file' };
};

// Delete content
const deleteContent = (id, title) => {
    if (confirm(`Vai tiešām vēlaties dzēst "${title}"?`)) {
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

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('lv-LV', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Saturs - Admin" />

    <AdminLayout>
        <template #title>Saturs</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">Pārvaldiet video, blogus un ziņas</p>
            </div>
            <Link href="/admin/content/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Jauns saturs
            </Link>
        </div>

        <!-- Quick Stats -->
        <div class="stats-row">
            <div v-for="type in contentTypes" :key="type.value" class="stat-mini">
                <i :class="type.icon"></i>
                <span class="stat-count">{{ props.content.data?.filter(c => c.type === type.value).length || 0 }}</span>
                <span class="stat-label">{{ type.label }}</span>
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
                        placeholder="Meklēt saturu..."
                        class="search-input"
                    >
                </div>

                <select v-model="typeFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi veidi</option>
                    <option v-for="type in contentTypes" :key="type.value" :value="type.value">
                        {{ type.label }}
                    </option>
                </select>

                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi statusi</option>
                    <option value="published">Publicēts</option>
                    <option value="draft">Melnraksts</option>
                </select>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Notīrīt
                </button>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <div v-for="item in content.data" :key="item.id" class="content-card">
                <!-- Thumbnail -->
                <div class="content-thumb">
                    <img
                        :src="item.thumbnail ? `/storage/${item.thumbnail}` : '/img/content-placeholder.png'"
                        :alt="item.title_lv"
                    >
                    <span :class="['type-badge', `type-${item.type}`]">
                        <i :class="getTypeInfo(item.type).icon"></i>
                        {{ getTypeInfo(item.type).label }}
                    </span>
                </div>

                <!-- Content Info -->
                <div class="content-body">
                    <h3 class="content-title">{{ item.title_lv }}</h3>
                    <p class="content-title-en">{{ item.title_en }}</p>

                    <div class="content-meta">
                        <span class="meta-item">
                            <i class="fas fa-eye"></i>
                            {{ item.view_count || 0 }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-heart"></i>
                            {{ item.like_count || 0 }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-calendar"></i>
                            {{ formatDate(item.published_at || item.created_at) }}
                        </span>
                    </div>

                    <div class="content-footer">
                        <button
                            @click="togglePublish(item)"
                            :class="['status-toggle', item.is_published ? 'published' : 'draft']"
                        >
                            <i :class="item.is_published ? 'fas fa-check' : 'fas fa-edit'"></i>
                            {{ item.is_published ? 'Publicēts' : 'Melnraksts' }}
                        </button>

                        <div class="content-actions">
                            <Link :href="`/content/${item.slug}`" class="btn-icon btn-icon-view" title="Skatīt" target="_blank">
                                <i class="fas fa-eye"></i>
                            </Link>
                            <Link :href="`/admin/content/${item.id}/edit`" class="btn-icon btn-icon-edit" title="Rediģēt">
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button @click="deleteContent(item.id, item.title_lv)" class="btn-icon btn-icon-delete" title="Dzēst">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="content.data.length === 0" class="empty-state">
                <i class="fas fa-newspaper"></i>
                <p>Nav atrasts neviens saturs</p>
                <Link href="/admin/content/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Pievienot pirmo saturu
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

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
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
}

.content-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

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

.content-title-en {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0 0 0.75rem;
}

.content-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
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

.status-toggle.draft {
    background: #fef3c7;
    color: #92400e;
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
@media (max-width: 768px) {
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: 100%;
    }
}
</style>
