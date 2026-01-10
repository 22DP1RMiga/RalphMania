<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Search and filters
const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');
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
    router.get('/admin/products', {
        search: search.value || undefined,
        category: categoryFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    statusFilter.value = '';
    router.get('/admin/products');
};

// Delete product
const deleteProduct = (id, name) => {
    if (confirm(`Vai tiešām vēlaties dzēst produktu "${name}"?`)) {
        router.delete(`/admin/products/${id}`, {
            preserveScroll: true,
        });
    }
};

// Toggle product status
const toggleStatus = (product) => {
    router.put(`/admin/products/${product.id}`, {
        ...product,
        is_active: !product.is_active,
    }, {
        preserveScroll: true,
    });
};

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('lv-LV', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

// Stock status
const getStockStatus = (product) => {
    if (product.stock_quantity <= 0) {
        return { class: 'stock-out', text: 'Nav noliktavā' };
    }
    if (product.stock_quantity <= product.low_stock_threshold) {
        return { class: 'stock-low', text: 'Zems krājums' };
    }
    return { class: 'stock-ok', text: 'Noliktavā' };
};
</script>

<template>
    <Head title="Produkti - Admin" />

    <AdminLayout>
        <template #title>Produkti</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-info">
                <p class="header-subtitle">Pārvaldiet veikala produktus</p>
            </div>
            <Link href="/admin/products/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Jauns produkts
            </Link>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Meklēt produktus..."
                        class="search-input"
                    >
                </div>

                <select v-model="categoryFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visas kategorijas</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name_lv }}
                    </option>
                </select>

                <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                    <option value="">Visi statusi</option>
                    <option value="active">Aktīvi</option>
                    <option value="inactive">Neaktīvi</option>
                </select>

                <button @click="resetFilters" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Notīrīt
                </button>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-card">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Attēls</th>
                    <th>Nosaukums</th>
                    <th>SKU</th>
                    <th>Kategorija</th>
                    <th>Cena</th>
                    <th>Krājums</th>
                    <th>Statuss</th>
                    <th>Darbības</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="product in products.data" :key="product.id">
                    <td>
                        <img
                            :src="product.image ? product.image : '/img/Products/placeholder.png'"
                            :alt="product.name_lv"
                            class="product-thumb"
                        >
                    </td>
                    <td>
                        <div class="product-name">
                            <span class="name-lv">{{ product.name_lv }}</span>
                            <span class="name-en">{{ product.name_en }}</span>
                        </div>
                    </td>
                    <td>
                        <code class="sku">{{ product.sku }}</code>
                    </td>
                    <td>
                            <span class="category-badge" v-if="product.category">
                                {{ product.category.name_lv }}
                            </span>
                        <span v-else class="no-category">—</span>
                    </td>
                    <td>
                        <div class="price-cell">
                            <span class="price">{{ formatPrice(product.price) }}</span>
                            <span v-if="product.compare_price" class="compare-price">
                                    {{ formatPrice(product.compare_price) }}
                                </span>
                        </div>
                    </td>
                    <td>
                            <span :class="['stock-badge', getStockStatus(product).class]">
                                {{ product.stock_quantity }} vien.
                            </span>
                    </td>
                    <td>
                        <button
                            @click="toggleStatus(product)"
                            :class="['status-toggle', product.is_active ? 'active' : 'inactive']"
                        >
                            <i :class="product.is_active ? 'fas fa-check' : 'fas fa-times'"></i>
                            {{ product.is_active ? 'Aktīvs' : 'Neaktīvs' }}
                        </button>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <Link :href="`/shop/product/${product.slug}`" class="btn-icon btn-icon-view" title="Skatīt" target="_blank">
                                <i class="fas fa-eye"></i>
                            </Link>
                            <Link :href="`/admin/products/${product.id}/edit`" class="btn-icon btn-icon-edit" title="Rediģēt">
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button @click="deleteProduct(product.id, product.name_lv)" class="btn-icon btn-icon-delete" title="Dzēst">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="products.data.length === 0">
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>Nav atrasts neviens produkts</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="products.links && products.links.length > 3" class="pagination">
                <Link
                    v-for="link in products.links"
                    :key="link.label"
                    :href="link.url"
                    :class="['page-link', { active: link.active, disabled: !link.url }]"
                    v-html="link.label"
                />
            </div>
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
    min-width: 250px;
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
    min-width: 150px;
}

/* Table */
.table-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.data-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.data-table tr:hover {
    background: #f9fafb;
}

/* Product Thumbnail */
.product-thumb {
    width: 3rem;
    height: 3rem;
    object-fit: cover;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

/* Product Name */
.product-name {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.name-lv {
    font-weight: 600;
    color: #111827;
}

.name-en {
    font-size: 0.75rem;
    color: #6b7280;
}

/* SKU */
.sku {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
}

/* Category Badge */
.category-badge {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.no-category {
    color: #9ca3af;
}

/* Price Cell */
.price-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.price {
    font-weight: 600;
    color: #111827;
}

.compare-price {
    font-size: 0.75rem;
    color: #9ca3af;
    text-decoration: line-through;
}

/* Stock Badge */
.stock-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.stock-ok {
    background: #d1fae5;
    color: #065f46;
}

.stock-low {
    background: #fef3c7;
    color: #92400e;
}

.stock-out {
    background: #fee2e2;
    color: #991b1b;
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

.status-toggle.active {
    background: #d1fae5;
    color: #065f46;
}

.status-toggle.inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.status-toggle:hover {
    transform: scale(1.05);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem !important;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
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

/* Responsive */
@media (max-width: 1024px) {
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: 100%;
    }

    .data-table {
        display: block;
        overflow-x: auto;
    }
}
</style>
