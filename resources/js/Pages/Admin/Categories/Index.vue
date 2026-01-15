<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
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

// Get category name based on locale
const getCategoryName = (category) => {
    return locale.value === 'lv' ? category.name_lv : category.name_en;
};

// Get category description based on locale
const getCategoryDescription = (category) => {
    return locale.value === 'lv' ? category.description_lv : category.description_en;
};

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
    const name = getCategoryName(category);
    if (confirm(t('admin.categories.deleteConfirm', { name }))) {
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

// Modal title
const modalTitle = computed(() => {
    return isEditing.value
        ? t('admin.categories.modal.editTitle')
        : t('admin.categories.modal.createTitle');
});
</script>

<template>
    <Head :title="t('admin.categories.index.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.categories.index.title') }}</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">{{ t('admin.categories.index.subtitle') }}</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                {{ t('admin.categories.index.newCategory') }}
            </button>
        </div>

        <!-- Categories Grid -->
        <div class="categories-grid">
            <div v-for="category in structuredCategories" :key="category.id" class="category-card">
                <div class="category-header">
                    <div class="category-info">
                        <h3 class="category-name">{{ getCategoryName(category) }}</h3>
                        <span class="category-name-secondary">
                            {{ locale === 'lv' ? category.name_en : category.name_lv }}
                        </span>
                    </div>
                    <div class="category-actions">
                        <button
                            @click="openEditModal(category)"
                            class="btn-icon btn-icon-edit"
                            :title="t('admin.common.edit')"
                        >
                            <i class="fas fa-edit"></i>
                        </button>
                        <button
                            @click="deleteCategory(category)"
                            class="btn-icon btn-icon-delete"
                            :title="t('admin.common.delete')"
                        >
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
                        {{ t('admin.categories.form.sortOrder') }}: {{ category.sort_order }}
                    </span>
                    <span :class="['status-badge', category.is_active ? 'active' : 'inactive']">
                        {{ category.is_active ? t('admin.categories.status.active') : t('admin.categories.status.inactive') }}
                    </span>
                </div>

                <p v-if="getCategoryDescription(category)" class="category-description">
                    {{ getCategoryDescription(category) }}
                </p>

                <!-- Subcategories -->
                <div v-if="category.children.length > 0" class="subcategories">
                    <h4 class="subcategories-title">
                        {{ t('admin.categories.index.subcategories') }} ({{ category.children.length }})
                    </h4>
                    <div class="subcategory-list">
                        <div v-for="sub in category.children" :key="sub.id" class="subcategory-item">
                            <div class="subcategory-info">
                                <span class="subcategory-name">{{ getCategoryName(sub) }}</span>
                                <span class="subcategory-slug">{{ sub.slug }}</span>
                            </div>
                            <div class="subcategory-actions">
                                <button
                                    @click="openEditModal(sub)"
                                    class="btn-icon-sm"
                                    :title="t('admin.common.edit')"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    @click="deleteCategory(sub)"
                                    class="btn-icon-sm btn-icon-sm-delete"
                                    :title="t('admin.common.delete')"
                                >
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
                <p>{{ t('admin.categories.index.noCategories') }}</p>
                <button @click="openCreateModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    {{ t('admin.categories.index.addFirstCategory') }}
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
                            {{ modalTitle }}
                        </h3>
                        <button @click="closeModal" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    {{ t('admin.categories.form.nameLv') }} *
                                    <span class="lang-badge">LV</span>
                                </label>
                                <input
                                    v-model="form.name_lv"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'error': errors.name_lv }"
                                    :placeholder="t('admin.categories.form.nameLvPlaceholder')"
                                >
                                <span v-if="errors.name_lv" class="error-text">{{ errors.name_lv }}</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    {{ t('admin.categories.form.nameEn') }} *
                                    <span class="lang-badge lang-badge-en">EN</span>
                                </label>
                                <input
                                    v-model="form.name_en"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'error': errors.name_en }"
                                    :placeholder="t('admin.categories.form.nameEnPlaceholder')"
                                    @blur="generateSlug"
                                >
                                <span v-if="errors.name_en" class="error-text">{{ errors.name_en }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ t('admin.categories.form.slug') }} *</label>
                            <input
                                v-model="form.slug"
                                type="text"
                                class="form-input"
                                :class="{ 'error': errors.slug }"
                                :placeholder="t('admin.categories.form.slugPlaceholder')"
                            >
                            <span class="form-hint">{{ t('admin.categories.form.slugHint') }}</span>
                            <span v-if="errors.slug" class="error-text">{{ errors.slug }}</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    {{ t('admin.categories.form.descriptionLv') }}
                                    <span class="lang-badge">LV</span>
                                </label>
                                <textarea
                                    v-model="form.description_lv"
                                    class="form-textarea"
                                    rows="3"
                                    :placeholder="t('admin.categories.form.descriptionPlaceholder')"
                                ></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    {{ t('admin.categories.form.descriptionEn') }}
                                    <span class="lang-badge lang-badge-en">EN</span>
                                </label>
                                <textarea
                                    v-model="form.description_en"
                                    class="form-textarea"
                                    rows="3"
                                    :placeholder="t('admin.categories.form.descriptionPlaceholder')"
                                ></textarea>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">{{ t('admin.categories.form.parentCategory') }}</label>
                                <select v-model="form.parent_id" class="form-select">
                                    <option :value="null">— {{ t('admin.categories.form.mainCategory') }} —</option>
                                    <option
                                        v-for="cat in parentCategories"
                                        :key="cat.id"
                                        :value="cat.id"
                                        :disabled="editingCategory && cat.id === editingCategory.id"
                                    >
                                        {{ getCategoryName(cat) }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ t('admin.categories.form.sortOrder') }}</label>
                                <input
                                    v-model.number="form.sort_order"
                                    type="number"
                                    class="form-input"
                                    min="0"
                                >
                                <span class="form-hint">{{ t('admin.categories.form.sortOrderHint') }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input v-model="form.is_active" type="checkbox">
                                <span>{{ t('admin.categories.form.isActive') }}</span>
                            </label>
                            <span class="form-hint checkbox-hint">{{ t('admin.categories.form.isActiveHint') }}</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModal" class="btn btn-secondary">
                            {{ t('admin.common.cancel') }}
                        </button>
                        <button @click="saveCategory" class="btn btn-primary" :disabled="isLoading">
                            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            {{ isEditing ? t('admin.common.update') : t('admin.common.save') }}
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
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
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

.category-info {
    flex: 1;
}

.category-name {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
}

.category-name-secondary {
    font-size: 0.875rem;
    color: #6b7280;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
}

/* Category Meta */
.category-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.meta-item i {
    color: #9ca3af;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

/* Category Description */
.category-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 1rem;
    line-height: 1.5;
}

/* Subcategories */
.subcategories {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
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
    gap: 0.125rem;
}

.subcategory-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
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

.modal-title i {
    color: #dc2626;
}

.modal-close {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #111827;
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.375rem;
    font-size: 0.875rem;
}

.lang-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.125rem 0.375rem;
    background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%);
    color: white;
    border-radius: 0.25rem;
    font-size: 0.625rem;
    font-weight: 700;
}

.lang-badge-en {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
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
    font-weight: 500;
    color: #374151;
}

.checkbox-label input {
    width: 1.125rem;
    height: 1.125rem;
    accent-color: #dc2626;
}

.checkbox-hint {
    margin-left: 1.625rem;
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
