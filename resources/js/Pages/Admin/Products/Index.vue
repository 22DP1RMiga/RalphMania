<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const page = usePage();

const props = defineProps({
    products: { type: Object, default: () => ({ data: [], links: [], meta: {} }) },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

// Search and filters
const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');
const statusFilter = ref(props.filters.status || '');

// Toast notification
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Delete modal
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const isDeleting = ref(false);

// Check for flash messages
onMounted(() => {
    const flash = page.props.flash;
    if (flash?.success) {
        toastMessage.value = flash.success;
        toastType.value = 'success';
        showToast.value = true;
    }
    if (flash?.error) {
        toastMessage.value = flash.error;
        toastType.value = 'error';
        showToast.value = true;
    }
});

// Watch for flash messages during navigation
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toastMessage.value = flash.success;
        toastType.value = 'success';
        showToast.value = true;
    }
    if (flash?.error) {
        toastMessage.value = flash.error;
        toastType.value = 'error';
        showToast.value = true;
    }
}, { deep: true });

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

const applyFilters = () => {
    router.get('/admin/products', {
        search: search.value || undefined,
        category: categoryFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    statusFilter.value = '';
    router.get('/admin/products');
};

const hasFilters = computed(() => search.value || categoryFilter.value || statusFilter.value);

// Open delete modal
const openDeleteModal = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

// Close delete modal
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};

// Confirm delete
const confirmDelete = () => {
    if (!productToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/admin/products/${productToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = t('admin.products.deleteSuccess');
            toastType.value = 'success';
            showToast.value = true;
            closeDeleteModal();
        },
        onError: () => {
            toastMessage.value = t('admin.products.deleteError');
            toastType.value = 'error';
            showToast.value = true;
        },
        onFinish: () => isDeleting.value = false,
    });
};

// Toggle product status
const toggleStatus = (product) => {
    router.put(`/admin/products/${product.id}/toggle-status`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = t('admin.products.statusChanged');
            toastType.value = 'success';
            showToast.value = true;
        },
    });
};

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat(locale.value === 'lv' ? 'lv-LV' : 'en-US', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

// Get product name based on locale
const getProductName = (product) => locale.value === 'lv' ? product.name_lv : product.name_en;

// Get category name based on locale
const getCategoryName = (category) => {
    if (!category) return null;
    return locale.value === 'lv' ? category.name_lv : category.name_en;
};

// Stock status
const getStockStatus = (product) => {
    if (product.stock_quantity <= 0) {
        return { class: 'stock-out', text: t('admin.products.stock.outOfStock') };
    }
    if (product.stock_quantity <= product.low_stock_threshold) {
        return { class: 'stock-low', text: t('admin.products.stock.lowStock') };
    }
    return { class: 'stock-ok', text: t('admin.products.stock.inStock') };
};

// Get product image
const getProductImage = (product) => {
    if (!product.image) return '/img/Products/placeholder-product.png';
    if (product.image.startsWith('http')) return product.image;
    return product.image;
};
</script>

<template>
    <Head :title="t('admin.products.index.title')" />

    <AdminLayout>
        <template #title>{{ t('admin.products.index.title') }}</template>

        <!-- Toast Notification -->
        <ToastNotification
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">{{ t('admin.products.index.subtitle') }}</p>
            </div>
            <Link href="/admin/products/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>{{ t('admin.products.index.newProduct') }}</span>
            </Link>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input v-model="search" type="text" :placeholder="t('admin.products.index.searchPlaceholder')" class="search-input">
                    <button v-if="search" @click="search = ''" class="clear-search"><i class="fas fa-times"></i></button>
                </div>

                <select v-model="categoryFilter" @change="applyFilters" class="filter-select">
                    <option value="">{{ t('admin.products.index.allCategories') }}</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ getCategoryName(cat) }}
                    </option>
                </select>

                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">{{ t('admin.products.status.allStatuses') }}</option>
                    <option value="active">{{ t('admin.products.status.active') }}</option>
                    <option value="inactive">{{ t('admin.products.status.inactive') }}</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <span>{{ t('admin.common.clear') }}</span>
                </button>
            </div>
        </div>

        <!-- Products Table (Desktop) -->
        <div class="table-card">
            <table class="data-table">
                <thead>
                <tr>
                    <th>{{ t('admin.products.table.image') }}</th>
                    <th>{{ t('admin.products.table.name') }}</th>
                    <th>{{ t('admin.products.table.sku') }}</th>
                    <th>{{ t('admin.products.table.category') }}</th>
                    <th>{{ t('admin.products.table.price') }}</th>
                    <th>{{ t('admin.products.table.stock') }}</th>
                    <th>{{ t('admin.products.table.status') }}</th>
                    <th>{{ t('admin.products.table.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="product in products.data" :key="product.id">
                    <td>
                        <img :src="getProductImage(product)" :alt="getProductName(product)" class="product-thumb">
                    </td>
                    <td>
                        <div class="product-name">
                            <span class="name-primary">{{ getProductName(product) }}</span>
                            <span class="name-secondary">{{ locale === 'lv' ? product.name_en : product.name_lv }}</span>
                        </div>
                    </td>
                    <td><code class="sku">{{ product.sku }}</code></td>
                    <td>
                        <span class="category-badge" v-if="product.category">{{ getCategoryName(product.category) }}</span>
                        <span v-else class="no-category">—</span>
                    </td>
                    <td>
                        <div class="price-cell">
                            <span class="price">{{ formatPrice(product.price) }}</span>
                            <span v-if="product.compare_price" class="compare-price">{{ formatPrice(product.compare_price) }}</span>
                        </div>
                    </td>
                    <td>
                            <span :class="['stock-badge', getStockStatus(product).class]">
                                {{ product.stock_quantity }} {{ t('admin.products.units') }}
                            </span>
                    </td>
                    <td>
                        <button @click="toggleStatus(product)" :class="['status-toggle', product.is_active ? 'active' : 'inactive']">
                            <i :class="product.is_active ? 'fas fa-check' : 'fas fa-times'"></i>
                            {{ product.is_active ? t('admin.products.status.active') : t('admin.products.status.inactive') }}
                        </button>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <Link :href="`/shop/product/${product.slug}`" class="btn-icon btn-view" :title="t('admin.common.view')" target="_blank">
                                <i class="fas fa-eye"></i>
                            </Link>
                            <Link :href="`/admin/products/${product.id}/edit`" class="btn-icon btn-edit" :title="t('admin.common.edit')">
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button @click="openDeleteModal(product)" class="btn-icon btn-delete" :title="t('admin.common.delete')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="products.data.length === 0">
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>{{ t('admin.products.index.noProducts') }}</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination (Desktop) -->
            <div v-if="products.links && products.links.length > 3" class="pagination desktop-pagination">
                <Link v-for="link in products.links" :key="link.label" :href="link.url" :class="['page-link', { active: link.active, disabled: !link.url }]" v-html="link.label" preserve-scroll />
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="mobile-cards">
            <div v-if="products.data.length === 0" class="empty-state-mobile">
                <i class="fas fa-box-open"></i>
                <p>{{ t('admin.products.index.noProducts') }}</p>
            </div>

            <div v-for="product in products.data" :key="product.id" class="product-card">
                <div class="card-header">
                    <img :src="getProductImage(product)" :alt="getProductName(product)" class="card-image">
                    <div class="card-info">
                        <h3 class="card-title">{{ getProductName(product) }}</h3>
                        <code class="card-sku">{{ product.sku }}</code>
                        <span v-if="product.category" class="card-category">{{ getCategoryName(product.category) }}</span>
                    </div>
                    <button @click="toggleStatus(product)" :class="['status-badge-mobile', product.is_active ? 'active' : 'inactive']">
                        <i :class="product.is_active ? 'fas fa-check' : 'fas fa-times'"></i>
                    </button>
                </div>

                <div class="card-body">
                    <div class="card-row">
                        <span class="card-label">{{ t('admin.products.table.price') }}</span>
                        <div class="card-price">
                            <span class="price">{{ formatPrice(product.price) }}</span>
                            <span v-if="product.compare_price" class="compare-price">{{ formatPrice(product.compare_price) }}</span>
                        </div>
                    </div>
                    <div class="card-row">
                        <span class="card-label">{{ t('admin.products.table.stock') }}</span>
                        <span :class="['stock-badge', getStockStatus(product).class]">
                            {{ product.stock_quantity }} {{ t('admin.products.units') }}
                        </span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">{{ t('admin.products.table.status') }}</span>
                        <span :class="['status-text', product.is_active ? 'active' : 'inactive']">
                            {{ product.is_active ? t('admin.products.status.active') : t('admin.products.status.inactive') }}
                        </span>
                    </div>
                </div>

                <div class="card-actions">
                    <Link :href="`/shop/product/${product.slug}`" class="btn btn-action btn-view" target="_blank">
                        <i class="fas fa-eye"></i>
                        <span>{{ t('admin.common.view') }}</span>
                    </Link>
                    <Link :href="`/admin/products/${product.id}/edit`" class="btn btn-action btn-edit">
                        <i class="fas fa-edit"></i>
                        <span>{{ t('admin.common.edit') }}</span>
                    </Link>
                    <button @click="openDeleteModal(product)" class="btn btn-action btn-delete">
                        <i class="fas fa-trash"></i>
                        <span>{{ t('admin.common.delete') }}</span>
                    </button>
                </div>
            </div>

            <!-- Pagination (Mobile) -->
            <div v-if="products.links && products.links.length > 3" class="pagination mobile-pagination">
                <Link v-for="link in products.links" :key="link.label" :href="link.url" :class="['page-link', { active: link.active, disabled: !link.url }]" v-html="link.label" preserve-scroll />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3 class="modal-title">
                                <i class="fas fa-trash"></i>
                                {{ t('admin.products.deleteTitle') }}
                            </h3>
                            <button @click="closeDeleteModal" class="modal-close"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="delete-preview">
                                <img :src="getProductImage(productToDelete)" :alt="getProductName(productToDelete)" class="delete-image">
                                <div class="delete-info">
                                    <span class="delete-name">{{ getProductName(productToDelete) }}</span>
                                    <code class="delete-sku">{{ productToDelete?.sku }}</code>
                                </div>
                            </div>
                            <div class="danger-box">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>{{ t('admin.products.deleteWarningTitle') }}</strong>
                                    <p>{{ t('admin.products.deleteWarningText') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="closeDeleteModal" class="btn btn-secondary">{{ t('admin.common.cancel') }}</button>
                            <button @click="confirmDelete" class="btn btn-danger" :disabled="isDeleting">
                                <i v-if="isDeleting" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-trash"></i>
                                {{ t('admin.products.confirmDelete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
/* Page Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-subtitle { color: #6b7280; margin: 0; }

/* Filters */
.filters-card { background: white; border-radius: 0.75rem; padding: 1rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.filters-row { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
.search-box { flex: 1; min-width: 250px; position: relative; }
.search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.search-input { width: 100%; padding: 0.625rem 2.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; }
.search-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
.clear-search { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; }
.clear-search:hover { color: #dc2626; }
.filter-select { padding: 0.625rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; background: white; min-width: 150px; }
.filter-select:focus { outline: none; border-color: #dc2626; }

/* Table Card */
.table-card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.data-table th { background: #f9fafb; font-weight: 600; color: #374151; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
.data-table tr:hover { background: #f9fafb; }

/* Product Thumbnail */
.product-thumb { width: 3rem; height: 3rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid #e5e7eb; }

/* Product Name */
.product-name { display: flex; flex-direction: column; gap: 0.125rem; }
.name-primary { font-weight: 600; color: #111827; }
.name-secondary { font-size: 0.75rem; color: #6b7280; }

/* SKU */
.sku { background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; color: #6b7280; }

/* Category Badge */
.category-badge { background: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 500; }
.no-category { color: #9ca3af; }

/* Price Cell */
.price-cell { display: flex; flex-direction: column; gap: 0.125rem; }
.price { font-weight: 600; color: #111827; }
.compare-price { font-size: 0.75rem; color: #9ca3af; text-decoration: line-through; }

/* Stock Badge */
.stock-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500; }
.stock-ok { background: #d1fae5; color: #065f46; }
.stock-low { background: #fef3c7; color: #92400e; }
.stock-out { background: #fee2e2; color: #991b1b; }

/* Status Toggle */
.status-toggle { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border: none; border-radius: 1rem; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.status-toggle.active { background: #d1fae5; color: #065f46; }
.status-toggle.inactive { background: #f3f4f6; color: #6b7280; }
.status-toggle:hover { transform: scale(1.05); }

/* Action Buttons */
.action-buttons { display: flex; gap: 0.5rem; }
.btn-icon { width: 2rem; height: 2rem; border: none; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; text-decoration: none; font-size: 0.8rem; }
.btn-view { background: #dbeafe; color: #2563eb; }
.btn-view:hover { background: #2563eb; color: white; }
.btn-edit { background: #fef3c7; color: #d97706; }
.btn-edit:hover { background: #d97706; color: white; }
.btn-delete { background: #fee2e2; color: #dc2626; }
.btn-delete:hover { background: #dc2626; color: white; }

/* Empty State */
.empty-state { text-align: center; padding: 3rem !important; color: #6b7280; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; display: block; }
.empty-state p { margin: 0; }

/* Pagination */
.pagination { display: flex; justify-content: center; gap: 0.25rem; padding: 1rem; border-top: 1px solid #e5e7eb; flex-wrap: wrap; }
.page-link { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; color: #374151; text-decoration: none; font-size: 0.875rem; transition: all 0.2s; }
.page-link:hover:not(.disabled):not(.active) { background: #f3f4f6; }
.page-link.active { background: #dc2626; border-color: #dc2626; color: white; }
.page-link.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; font-size: 0.875rem; }
.btn-primary { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
.btn-primary:hover { box-shadow: 0 4px 12px rgba(220,38,38,0.3); transform: translateY(-1px); }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-danger { background: #dc2626; color: white; }
.btn-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }

/* Mobile Cards - Hidden by default */
.mobile-cards { display: none; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal-container { background: white; border-radius: 1rem; width: 100%; max-width: 450px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.modal-title i { color: #dc2626; }
.modal-close { background: none; border: none; color: #6b7280; font-size: 1.25rem; cursor: pointer; }
.modal-close:hover { color: #111827; }
.modal-body { padding: 1.5rem; overflow-y: auto; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; }

/* Delete Preview */
.delete-preview { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f9fafb; border-radius: 0.75rem; margin-bottom: 1rem; }
.delete-image { width: 4rem; height: 4rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid #e5e7eb; }
.delete-info { display: flex; flex-direction: column; gap: 0.25rem; }
.delete-name { font-weight: 600; color: #111827; }
.delete-sku { font-size: 0.75rem; }

/* Danger Box */
.danger-box { display: flex; gap: 1rem; padding: 1rem; background: #fee2e2; border-radius: 0.75rem; color: #991b1b; }
.danger-box i { font-size: 1.5rem; flex-shrink: 0; margin-top: 0.25rem; }
.danger-box strong { display: block; margin-bottom: 0.25rem; }
.danger-box p { margin: 0; font-size: 0.875rem; opacity: 0.9; }

/* Transitions */
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-container, .modal-leave-to .modal-container { transform: scale(0.9); }

/* ========================================
   RESPONSIVE STYLES
   ======================================== */

@media (max-width: 1024px) {
    .filters-row { flex-direction: column; align-items: stretch; }
    .search-box { min-width: 100%; }
    .filter-select { width: 100%; }
    .data-table { display: block; overflow-x: auto; }
}

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .btn-primary { width: 100%; justify-content: center; }

    /* Hide table, show mobile cards */
    .table-card { display: none; }
    .mobile-cards { display: flex; flex-direction: column; gap: 1rem; }

    .product-card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
    .card-header { display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid #e5e7eb; align-items: flex-start; }
    .card-image { width: 4rem; height: 4rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid #e5e7eb; flex-shrink: 0; }
    .card-info { flex: 1; min-width: 0; }
    .card-title { font-size: 1rem; font-weight: 600; color: #111827; margin: 0 0 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .card-sku { font-size: 0.7rem; display: block; margin-bottom: 0.375rem; }
    .card-category { background: #dbeafe; color: #1e40af; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 500; }
    .status-badge-mobile { width: 2rem; height: 2rem; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .status-badge-mobile.active { background: #d1fae5; color: #065f46; }
    .status-badge-mobile.inactive { background: #f3f4f6; color: #6b7280; }

    .card-body { padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
    .card-row { display: flex; justify-content: space-between; align-items: center; }
    .card-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }
    .card-price { display: flex; align-items: center; gap: 0.5rem; }
    .card-price .compare-price { font-size: 0.7rem; }
    .status-text { font-size: 0.75rem; font-weight: 500; }
    .status-text.active { color: #065f46; }
    .status-text.inactive { color: #6b7280; }

    .card-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; padding: 1rem; border-top: 1px solid #e5e7eb; background: #f9fafb; }
    .btn-action { padding: 0.5rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.375rem; text-decoration: none; border: none; cursor: pointer; }
    .btn-action.btn-view { background: #dbeafe; color: #1e40af; }
    .btn-action.btn-edit { background: #fef3c7; color: #92400e; }
    .btn-action.btn-delete { background: #fee2e2; color: #dc2626; }

    .empty-state-mobile { text-align: center; padding: 3rem 1.5rem; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .empty-state-mobile i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
    .empty-state-mobile p { color: #6b7280; margin: 0; }

    .mobile-pagination { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 1rem; border-top: none; }
    .desktop-pagination { display: none; }

    .modal-footer { flex-direction: column; gap: 0.5rem; }
    .modal-footer .btn { width: 100%; justify-content: center; }
}

@media (max-width: 480px) {
    .filters-card { padding: 1rem; }
    .card-header { padding: 0.875rem; }
    .card-image { width: 3.5rem; height: 3.5rem; }
    .card-title { font-size: 0.9rem; }
    .card-body { padding: 0.875rem; }
    .card-actions { padding: 0.875rem; }
    .page-link { padding: 0.375rem 0.5rem; font-size: 0.75rem; }
}

@media (max-width: 360px) {
    .card-actions { grid-template-columns: 1fr; }
}
</style>
