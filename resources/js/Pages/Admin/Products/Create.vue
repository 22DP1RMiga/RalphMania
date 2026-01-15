<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

// Form data
const form = useForm({
    name_lv: '',
    name_en: '',
    sku: '',
    category_id: '',
    description_lv: '',
    description_en: '',
    price: '',
    compare_price: '',
    image: null,
    stock_quantity: 0,
    low_stock_threshold: 5,
    is_active: true,
    is_featured: false,
});

// Image handling
const imagePreview = ref(null);
const isDragging = ref(false);

// Handle file selection (click)
const handleImageChange = (event) => {
    const file = event.target.files[0];
    processImage(file);
};

// Handle drag & drop
const handleDragOver = (event) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (event) => {
    event.preventDefault();
    isDragging.value = false;
};

const handleDrop = (event) => {
    event.preventDefault();
    isDragging.value = false;

    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        // Check if it's an image
        if (file.type.startsWith('image/Products/')) {
            processImage(file);
        }
    }
};

// Process image file
const processImage = (file) => {
    if (file && file.type.startsWith('image/Products/')) {
        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert(t('admin.products.form.imageTooLarge'));
            return;
        }

        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    const input = document.getElementById('product-image');
    if (input) input.value = '';
};

// Auto-generate SKU from name
const generateSku = () => {
    if (form.name_en) {
        form.sku = form.name_en
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '-')
                .replace(/-+/g, '-')
                .substring(0, 20)
            + '-' + Math.random().toString(36).substring(2, 6).toUpperCase();
    }
};

// Submit form
const submitForm = () => {
    form.post('/admin/products', {
        preserveScroll: true,
        forceFormData: true,
    });
};

// Get category name based on locale
const getCategoryName = (category) => {
    return locale.value === 'lv' ? category.name_lv : category.name_en;
};
</script>

<template>
    <Head :title="t('admin.products.create.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.products.create.title') }}</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">{{ t('admin.products.create.subtitle') }}</p>
            </div>
            <Link href="/admin/products" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                {{ t('admin.common.back') }}
            </Link>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="product-form">
            <div class="form-grid">
                <!-- Left Column - Main Info -->
                <div class="form-column">
                    <div class="form-card">
                        <h3 class="form-card-title">
                            <i class="fas fa-info-circle"></i>
                            {{ t('admin.products.form.basicInfo') }}
                        </h3>

                        <!-- Name LV -->
                        <div class="form-group">
                            <label class="form-label">
                                {{ t('admin.products.form.nameLv') }} *
                                <span class="lang-badge">LV</span>
                            </label>
                            <input
                                v-model="form.name_lv"
                                type="text"
                                class="form-input"
                                :class="{ 'error': form.errors.name_lv }"
                                :placeholder="t('admin.products.form.nameLvPlaceholder')"
                            >
                            <span v-if="form.errors.name_lv" class="error-text">{{ form.errors.name_lv }}</span>
                        </div>

                        <!-- Name EN -->
                        <div class="form-group">
                            <label class="form-label">
                                {{ t('admin.products.form.nameEn') }} *
                                <span class="lang-badge lang-badge-en">EN</span>
                            </label>
                            <input
                                v-model="form.name_en"
                                type="text"
                                class="form-input"
                                :class="{ 'error': form.errors.name_en }"
                                :placeholder="t('admin.products.form.nameEnPlaceholder')"
                                @blur="generateSku"
                            >
                            <span v-if="form.errors.name_en" class="error-text">{{ form.errors.name_en }}</span>
                        </div>

                        <!-- SKU -->
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.products.form.sku') }} *</label>
                            <div class="input-with-button">
                                <input
                                    v-model="form.sku"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'error': form.errors.sku }"
                                    :placeholder="t('admin.products.form.skuPlaceholder')"
                                >
                                <button type="button" @click="generateSku" class="btn-generate" :title="t('admin.products.form.generateSku')">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                            <span v-if="form.errors.sku" class="error-text">{{ form.errors.sku }}</span>
                        </div>

                        <!-- Category -->
                        <div class="form-group">
                            <label class="form-label">{{ t('admin.products.form.category') }}</label>
                            <select v-model="form.category_id" class="form-select">
                                <option value="">{{ t('admin.products.form.selectCategory') }}</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ getCategoryName(cat) }}
                                </option>
                            </select>
                        </div>

                        <!-- Description LV -->
                        <div class="form-group">
                            <label class="form-label">
                                {{ t('admin.products.form.descriptionLv') }}
                                <span class="lang-badge">LV</span>
                            </label>
                            <textarea
                                v-model="form.description_lv"
                                class="form-textarea"
                                rows="4"
                                :placeholder="t('admin.products.form.descriptionPlaceholder')"
                            ></textarea>
                        </div>

                        <!-- Description EN -->
                        <div class="form-group">
                            <label class="form-label">
                                {{ t('admin.products.form.descriptionEn') }}
                                <span class="lang-badge lang-badge-en">EN</span>
                            </label>
                            <textarea
                                v-model="form.description_en"
                                class="form-textarea"
                                rows="4"
                                :placeholder="t('admin.products.form.descriptionPlaceholder')"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Pricing, Stock, Image -->
                <div class="form-column">
                    <!-- Pricing -->
                    <div class="form-card">
                        <h3 class="form-card-title">
                            <i class="fas fa-euro-sign"></i>
                            {{ t('admin.products.form.pricing') }}
                        </h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">{{ t('admin.products.form.price') }} *</label>
                                <div class="input-with-icon">
                                    <span class="input-icon">€</span>
                                    <input
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-input input-with-prefix"
                                        :class="{ 'error': form.errors.price }"
                                        placeholder="0.00"
                                    >
                                </div>
                                <span v-if="form.errors.price" class="error-text">{{ form.errors.price }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ t('admin.products.form.comparePrice') }}</label>
                                <div class="input-with-icon">
                                    <span class="input-icon">€</span>
                                    <input
                                        v-model="form.compare_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-input input-with-prefix"
                                        placeholder="0.00"
                                    >
                                </div>
                                <span class="form-hint">{{ t('admin.products.form.comparePriceHint') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stock -->
                    <div class="form-card">
                        <h3 class="form-card-title">
                            <i class="fas fa-warehouse"></i>
                            {{ t('admin.products.form.inventory') }}
                        </h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">{{ t('admin.products.form.stockQuantity') }} *</label>
                                <input
                                    v-model="form.stock_quantity"
                                    type="number"
                                    min="0"
                                    class="form-input"
                                    :class="{ 'error': form.errors.stock_quantity }"
                                >
                                <span v-if="form.errors.stock_quantity" class="error-text">{{ form.errors.stock_quantity }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ t('admin.products.form.lowStockThreshold') }}</label>
                                <input
                                    v-model="form.low_stock_threshold"
                                    type="number"
                                    min="0"
                                    class="form-input"
                                >
                                <span class="form-hint">{{ t('admin.products.form.lowStockHint') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Image with Drag & Drop -->
                    <div class="form-card">
                        <h3 class="form-card-title">
                            <i class="fas fa-image"></i>
                            {{ t('admin.products.form.image') }}
                        </h3>

                        <div class="image-upload-area">
                            <!-- Image Preview -->
                            <div v-if="imagePreview" class="image-preview">
                                <img :src="imagePreview" alt="Preview">
                                <button type="button" @click="removeImage" class="remove-image-btn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- Drag & Drop Zone -->
                            <label
                                v-else
                                class="image-upload-label"
                                :class="{ 'dragging': isDragging }"
                                for="product-image"
                                @dragover="handleDragOver"
                                @dragleave="handleDragLeave"
                                @drop="handleDrop"
                            >
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <span class="upload-text">{{ t('admin.products.form.dragDropImage') }}</span>
                                <span class="upload-or">{{ t('admin.products.form.or') }}</span>
                                <span class="upload-browse">{{ t('admin.products.form.browseFiles') }}</span>
                                <span class="upload-hint">{{ t('admin.products.form.uploadHint') }}</span>
                            </label>

                            <input
                                type="file"
                                id="product-image"
                                accept="image/*"
                                class="hidden-input"
                                @change="handleImageChange"
                            >
                        </div>
                        <span v-if="form.errors.image" class="error-text">{{ form.errors.image }}</span>
                    </div>

                    <!-- Status -->
                    <div class="form-card">
                        <h3 class="form-card-title">
                            <i class="fas fa-toggle-on"></i>
                            {{ t('admin.products.form.status') }}
                        </h3>

                        <div class="toggle-group">
                            <label class="toggle-label">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="toggle-input"
                                >
                                <span class="toggle-switch"></span>
                                <span class="toggle-text">{{ t('admin.products.form.isActive') }}</span>
                            </label>
                            <p class="toggle-hint">{{ t('admin.products.form.isActiveHint') }}</p>
                        </div>

                        <div class="toggle-group">
                            <label class="toggle-label">
                                <input
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="toggle-input"
                                >
                                <span class="toggle-switch"></span>
                                <span class="toggle-text">{{ t('admin.products.form.isFeatured') }}</span>
                            </label>
                            <p class="toggle-hint">{{ t('admin.products.form.isFeaturedHint') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <Link href="/admin/products" class="btn btn-secondary">
                    {{ t('admin.common.cancel') }}
                </Link>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-save"></i>
                    {{ t('admin.products.form.save') }}
                </button>
            </div>
        </form>
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

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

.form-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Form Card */
.form-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-card-title i {
    color: #dc2626;
}

/* Form Group */
.form-group {
    margin-bottom: 1rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
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

/* Form Inputs */
.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.error,
.form-select.error,
.form-textarea.error {
    border-color: #dc2626;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
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

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Input with Icon */
.input-with-icon {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-weight: 600;
}

.input-with-prefix {
    padding-left: 2rem;
}

/* Input with Button */
.input-with-button {
    display: flex;
    gap: 0.5rem;
}

.input-with-button .form-input {
    flex: 1;
}

.btn-generate {
    padding: 0.75rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-generate:hover {
    background: #e5e7eb;
    color: #374151;
}

/* Image Upload - Enhanced Drag & Drop */
.image-upload-area {
    position: relative;
}

.image-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2.5rem 2rem;
    border: 2px dashed #d1d5db;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    background: #fafafa;
}

.image-upload-label:hover {
    border-color: #dc2626;
    background: #fef2f2;
}

/* Dragging state */
.image-upload-label.dragging {
    border-color: #dc2626;
    background: #fee2e2;
    border-style: solid;
    transform: scale(1.02);
}

.upload-icon {
    width: 4rem;
    height: 4rem;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.upload-icon i {
    font-size: 1.75rem;
    color: #dc2626;
}

.image-upload-label.dragging .upload-icon {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.image-upload-label.dragging .upload-icon i {
    color: white;
}

.upload-text {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
}

.upload-or {
    color: #9ca3af;
    font-size: 0.875rem;
}

.upload-browse {
    color: #dc2626;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: underline;
}

.upload-hint {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.5rem;
}

.hidden-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

/* Image Preview */
.image-preview {
    position: relative;
    width: 100%;
    max-width: 250px;
    margin: 0 auto;
}

.image-preview img {
    width: 100%;
    height: auto;
    border-radius: 0.75rem;
    border: 2px solid #e5e7eb;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.remove-image-btn {
    position: absolute;
    top: -0.75rem;
    right: -0.75rem;
    width: 2rem;
    height: 2rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border: 3px solid white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.remove-image-btn:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    transform: scale(1.1);
}

/* Toggle */
.toggle-group {
    margin-bottom: 1rem;
}

.toggle-group:last-child {
    margin-bottom: 0;
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
}

.toggle-input {
    position: absolute;
    opacity: 0;
}

.toggle-switch {
    position: relative;
    width: 3rem;
    height: 1.5rem;
    background: #d1d5db;
    border-radius: 1rem;
    transition: all 0.3s;
}

.toggle-switch::after {
    content: '';
    position: absolute;
    top: 0.125rem;
    left: 0.125rem;
    width: 1.25rem;
    height: 1.25rem;
    background: white;
    border-radius: 50%;
    transition: all 0.3s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-input:checked + .toggle-switch {
    background: #10b981;
}

.toggle-input:checked + .toggle-switch::after {
    transform: translateX(1.5rem);
}

.toggle-text {
    font-weight: 500;
    color: #374151;
}

.toggle-hint {
    margin: 0.25rem 0 0 3.75rem;
    font-size: 0.75rem;
    color: #6b7280;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
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
</style>
