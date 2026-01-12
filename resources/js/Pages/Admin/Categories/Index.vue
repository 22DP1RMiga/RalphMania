<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

// Modal states
const showModal = ref(false);
const isEditing = ref(false);
const editingCategory = ref(null);

const form = ref({
    name_lv: '',
    name_en: '',
    slug: '',
    description_lv: '',
    description_en: '',
    parent_id: null,
    sort_order: 0,
    is_active: true,
});

const errors = ref({});
const isLoading = ref(false);

// Open create modal
const openCreateModal = () => {
    isEditing.value = false;
    editingCategory.value = null;
    form.value = {
        name_lv: '',
        name_en: '',
        slug: '',
        description_lv: '',
        description_en: '',
        parent_id: null,
        sort_order: 0,
        is_active: true,
    };
    errors.value = {};
    showModal.value = true;
};

// Open edit modal
const openEditModal = (category) => {
    isEditing.value = true;
    editingCategory.value = category;
    form.value = {
        name_lv: category.name_lv,
        name_en: category.name_en,
        slug: category.slug,
        description_lv: category.description_lv || '',
        description_en: category.description_en || '',
        parent_id: category.parent_id,
        sort_order: category.sort_order,
        is_active: category.is_active,
    };
    errors.value = {};
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
};

// Auto-generate slug
const generateSlug = () => {
    form.value.slug = form.value.name_en
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

// Save category
const saveCategory = () => {
    isLoading.value = true;
    errors.value = {};

    const url = isEditing.value
        ? `/admin/categories/${editingCategory.value.id}`
        : '/admin/categories';

    const method = isEditing.value ? 'put' : 'post';

    router[method](url, form.value, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (errs) => {
            errors.value = errs;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

// Delete category
const deleteCategory = (category) => {
    if (confirm(`Vai tiešām vēlaties dzēst kategoriju "${category.name_lv}"?`)) {
        router.delete(`/admin/categories/${category.id}`, {
            preserveScroll: true,
        });
    }
};

// Get parent categories (for dropdown)
const parentCategories = computed(() => {
    return props.categories.filter(c => !c.parent_id);
});

// Get subcategories
const getSubcategories = (parentId) => {
    return props.categories.filter(c => c.parent_id === parentId);
};

// Structured categories (parent with children)
const structuredCategories = computed(() => {
    const parents = props.categories.filter(c => !c.parent_id);
    return parents.map(parent => ({
        ...parent,
        children: getSubcategories(parent.id),
    }));
});
</script>

<template>
    <Head title="Kategorijas - Admin" />

    <AdminLayout>
        <template #title>{{ t('admin.categories.index.title') }}</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">{{ t('admin.categories.index.subtitle') }}</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Jauna kategorija
            </button>
        </div>

        <!-- Categories Grid -->
        <div class="categories-grid">
            <div v-for="category in structuredCategories" :key="category.id" class="category-card">
                <div class="category-header">
                    <div class="category-info">
                        <h3 class="category-name">{{ category.name_lv }}</h3>
                        <span class="category-name-en">{{ category.name_en }}</span>
                    </div>
                    <div class="category-actions">
                        <button @click="openEditModal(category)" class="btn-icon btn-icon-edit" title="Rediģēt">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button @click="deleteCategory(category)" class="btn-icon btn-icon-delete" title="Dzēst">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="category-meta">
                    <span class="meta-item">
                        <i class="fas fa-link"></i>
                        {{ category.slug }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-sort"></i>
                        Secība: {{ category.sort_order }}
                    </span>
                    <span :class="['status-badge', category.is_active ? 'active' : 'inactive']">
                        {{ category.is_active ? 'Aktīva' : 'Neaktīva' }}
                    </span>
                </div>

                <p v-if="category.description_lv" class="category-description">
                    {{ category.description_lv }}
                </p>

                <!-- Subcategories -->
                <div v-if="category.children.length > 0" class="subcategories">
                    <h4 class="subcategories-title">Apakškategorijas ({{ category.children.length }})</h4>
                    <div class="subcategory-list">
                        <div v-for="sub in category.children" :key="sub.id" class="subcategory-item">
                            <div class="subcategory-info">
                                <span class="subcategory-name">{{ sub.name_lv }}</span>
                                <span class="subcategory-slug">{{ sub.slug }}</span>
                            </div>
                            <div class="subcategory-actions">
                                <button @click="openEditModal(sub)" class="btn-icon-sm" title="Rediģēt">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="deleteCategory(sub)" class="btn-icon-sm btn-icon-sm-delete" title="Dzēst">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="categories.length === 0" class="empty-state">
                <i class="fas fa-tags"></i>
                <p>Nav pievienota neviena kategorija</p>
                <button @click="openCreateModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Pievienot pirmo kategoriju
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Transition name="modal">
            <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i :class="isEditing ? 'fas fa-edit' : 'fas fa-plus'"></i>
                            {{ isEditing ? 'Rediģēt kategoriju' : 'Jauna kategorija' }}
                        </h3>
                        <button @click="closeModal" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nosaukums (LV) *</label>
                                <input v-model="form.name_lv" type="text" class="form-input" :class="{ 'error': errors.name_lv }">
                                <span v-if="errors.name_lv" class="error-text">{{ errors.name_lv }}</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nosaukums (EN) *</label>
                                <input v-model="form.name_en" type="text" class="form-input" :class="{ 'error': errors.name_en }" @blur="generateSlug">
                                <span v-if="errors.name_en" class="error-text">{{ errors.name_en }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Slug *</label>
                            <input v-model="form.slug" type="text" class="form-input" :class="{ 'error': errors.slug }">
                            <span class="form-hint">URL draudzīgs nosaukums (piem. "t-shirts")</span>
                            <span v-if="errors.slug" class="error-text">{{ errors.slug }}</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Apraksts (LV)</label>
                                <textarea v-model="form.description_lv" class="form-textarea" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Apraksts (EN)</label>
                                <textarea v-model="form.description_en" class="form-textarea" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Vecākkategorija</label>
                                <select v-model="form.parent_id" class="form-select">
                                    <option :value="null">— Galvenā kategorija —</option>
                                    <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id" :disabled="editingCategory && cat.id === editingCategory.id">
                                        {{ cat.name_lv }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kārtošanas secība</label>
                                <input v-model.number="form.sort_order" type="number" class="form-input" min="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input v-model="form.is_active" type="checkbox">
                                <span>Kategorija ir aktīva</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModal" class="btn btn-secondary">Atcelt</button>
                        <button @click="saveCategory" class="btn btn-primary" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <span>{{ isEditing ? 'Saglabāt' : 'Izveidot' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
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

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

@media (max-width: 640px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
}

/* Category Card */
.category-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.category-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.category-name-en {
    font-size: 0.875rem;
    color: #6b7280;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
}

.category-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.meta-item i {
    color: #9ca3af;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.category-description {
    color: #4b5563;
    font-size: 0.875rem;
    margin: 0 0 1rem;
    line-height: 1.5;
}

/* Subcategories */
.subcategories {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
    margin-top: 1rem;
}

.subcategories-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.75rem;
}

.subcategory-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.subcategory-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: #f9fafb;
    border-radius: 0.375rem;
}

.subcategory-info {
    display: flex;
    flex-direction: column;
}

.subcategory-name {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.subcategory-slug {
    font-size: 0.75rem;
    color: #9ca3af;
}

.subcategory-actions {
    display: flex;
    gap: 0.25rem;
}

.btn-icon-sm {
    width: 1.5rem;
    height: 1.5rem;
    border: none;
    border-radius: 0.25rem;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon-sm:hover {
    background: #e5e7eb;
    color: #374151;
}

.btn-icon-sm-delete:hover {
    background: #fee2e2;
    color: #dc2626;
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

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 1rem;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-close {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.25rem;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* Form */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.375rem;
    font-size: 0.875rem;
}

.form-input,
.form-textarea,
.form-select {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.error,
.form-textarea.error {
    border-color: #dc2626;
}

.form-hint {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.error-text {
    display: block;
    font-size: 0.75rem;
    color: #dc2626;
    margin-top: 0.25rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input {
    width: 1rem;
    height: 1rem;
    accent-color: #dc2626;
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
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    transform: translateY(-1px);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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

/* Modal Animation */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
    transform: scale(0.9);
}
</style>
