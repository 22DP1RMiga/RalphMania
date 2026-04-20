<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const page     = usePage();
const authUser = computed(() => page.props.auth?.user);
const props    = defineProps({ slug: String });

const isLoading       = ref(true);
const productData     = ref(null);
const quantity        = ref(1);
const relatedProducts = ref([]);
const isAddingToCart  = ref(false);

// ── IZMĒRI ────────────────────────────────────────────────────────
const SIZES        = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
const selectedSize = ref(null);
const sizeError    = ref(false);
const hasSizes     = computed(() => productData.value?.has_sizes === true);
const selectSize   = (size) => { selectedSize.value = size; sizeError.value = false; };

// ── HELPERS ───────────────────────────────────────────────────────
const n     = (v) => (v === null || v === undefined) ? 0 : (parseFloat(v) || 0);
const fmt   = (p) => n(p).toFixed(2);
const name  = (obj, lv, en) => !obj ? '' : (locale.value === 'lv' ? obj[lv] : obj[en]);

const getProductName        = computed(() => name(productData.value, 'name_lv', 'name_en'));
const getProductDescription = computed(() => name(productData.value, 'description_lv', 'description_en'));
const getCategoryName       = computed(() => name(productData.value?.category, 'name_lv', 'name_en'));

const discountPercentage = computed(() => {
    const price = n(productData.value?.price), comp = n(productData.value?.compare_price);
    if (!comp || price >= comp) return 0;
    return Math.round((1 - price / comp) * 100);
});
const isInStock    = computed(() => productData.value?.stock_quantity > 0 && productData.value?.is_active);
const productImage = computed(() => productData.value?.image || '/img/Products/placeholder.png');

const relatedDiscountPct = (p) => {
    const price = n(p.price), comp = n(p.compare_price);
    if (!comp || price >= comp) return 0;
    return Math.round((1 - price / comp) * 100);
};
const relatedInStock = (p) => p.stock_quantity > 0;

// ── FETCH PRODUCT ─────────────────────────────────────────────────
const fetchProduct = async () => {
    const slug = props.slug || window.location.pathname.split('/').pop();
    isLoading.value = true;
    try {
        const res = await axios.get(`/api/v1/products/${slug}`);
        productData.value = res.data;
        // Paralēli ielādē saistītos un atsauksmes
        fetchRelatedProducts();
        fetchReviews(res.data.id);
    } catch {
        productData.value = null;
    } finally {
        isLoading.value = false;
    }
};

const fetchRelatedProducts = async () => {
    if (!productData.value?.category_id) return;
    try {
        const res = await axios.get(`/api/v1/products?category=${productData.value.category_id}`);
        const all = res.data.data || res.data;
        relatedProducts.value = all
            .filter(p => p.id !== productData.value.id)
            .slice(0, 4);
    } catch {}
};

// ── DAUDZUMS ──────────────────────────────────────────────────────
const increaseQuantity = () => { if (quantity.value < productData.value.stock_quantity) quantity.value++; };
const decreaseQuantity = () => { if (quantity.value > 1) quantity.value--; };

// ── GROZS ─────────────────────────────────────────────────────────
const addToCart = async () => {
    if (!isInStock.value) return;
    if (hasSizes.value && !selectedSize.value) {
        sizeError.value = true;
        displayToast(locale.value === 'lv' ? 'Lūdzu izvēlies izmēru!' : 'Please select a size!', 'error');
        document.querySelector('.size-selector')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    isAddingToCart.value = true;
    try {
        const res = await axios.post('/cart/add', {
            product_id: productData.value.id,
            quantity:   quantity.value,
            size:       selectedSize.value || null,
        });
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: res.data.cart.total_items } }));
        const sizeText = selectedSize.value ? ` (${selectedSize.value})` : '';
        displayToast(`${quantity.value}x ${getProductName.value}${sizeText} ${locale.value === 'lv' ? 'pievienots grozam!' : 'added to cart!'}`, 'success');
    } catch (error) {
        displayToast(error.response?.data?.message || (locale.value === 'lv' ? 'Kļūda! Lūdzu mēģiniet vēlreiz.' : 'Error! Please try again.'), 'error');
    } finally {
        isAddingToCart.value = false;
    }
};

const addRelatedToCart = async (product) => {
    if (product.has_sizes) {
        window.location.href = `/shop/product/${product.slug}`;
        return;
    }
    try {
        const res = await axios.post('/cart/add', { product_id: product.id, quantity: 1 });
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: res.data.cart.total_items } }));
        const rName = locale.value === 'lv' ? product.name_lv : product.name_en;
        displayToast(`${rName} ${locale.value === 'lv' ? 'pievienots grozam!' : 'added to cart!'}`, 'success');
    } catch (error) {
        displayToast(error.response?.data?.message || 'Kļūda!', 'error');
    }
};

// ════════════════════════════════════════════════════════════════
// ATSAUKSMJU SISTĒMA
// ════════════════════════════════════════════════════════════════
const reviews          = ref([]);
const isLoadingReviews = ref(false);
const reviewForm       = ref({ rating: 0, review_text: '' });
const hoverStar        = ref(0);
const isSubmitting     = ref(false);
const showReviewForm   = ref(false);

// Reģistrē ja tikko iesniegta atsauksme — garantē "gaida apstiprinājumu"
// rādīšanu pat ja API to neatgriež (web guard / session auth API problēmas)
const justSubmittedReview = ref(false);

const reviewStats = computed(() => {
    if (!reviews.value.length) return { avg: 0, count: 0, dist: { 5:0,4:0,3:0,2:0,1:0 } };
    const total = reviews.value.length;
    const sum   = reviews.value.reduce((s, r) => s + r.rating, 0);
    const dist  = { 5:0, 4:0, 3:0, 2:0, 1:0 };
    reviews.value.forEach(r => { if (dist[r.rating] !== undefined) dist[r.rating]++; });
    return { avg: (sum / total).toFixed(1), count: total, dist };
});

// Vai lietotājs jau ir iesniedzis atsauksmi (apstiprinātu VAI neapstiprinātu)
const userReview = computed(() => {
    if (!authUser.value) return null;
    return reviews.value.find(r =>
        Number(r.user_id) === Number(authUser.value.id) ||
        Number(r.user?.id) === Number(authUser.value.id)
    ) || (justSubmittedReview.value ? { is_approved: false, _pending: true } : null);
});

const fetchReviews = async (productId) => {
    isLoadingReviews.value = true;
    try {
        const res = await axios.get(`/api/v1/reviews/product/${productId}`);
        reviews.value = res.data;
    } catch {
        reviews.value = [];
    } finally {
        isLoadingReviews.value = false;
    }
};

const submitReview = async () => {
    if (!reviewForm.value.rating) {
        displayToast(locale.value === 'lv' ? 'Lūdzu izvēlies vērtējumu!' : 'Please select a rating!', 'error');
        return;
    }
    isSubmitting.value = true;
    try {
        await axios.post('/reviews', {
            product_id:  productData.value.id,
            rating:      reviewForm.value.rating,
            review_text: reviewForm.value.review_text,
        });
        displayToast(
            locale.value === 'lv'
                ? 'Atsauksme pievienota! Tā būs redzama pēc administratora apstiprinājuma.'
                : 'Review submitted! It will be visible after admin approval.',
            'success'
        );
        reviewForm.value = { rating: 0, review_text: '' };
        showReviewForm.value = false;
        justSubmittedReview.value = true;  // garantē "gaida apstiprinājumu" rādīšanu
        await fetchReviews(productData.value.id);
    } catch (error) {
        displayToast(
            error.response?.data?.message ||
            (locale.value === 'lv' ? 'Kļūda iesniedzot atsauksmi!' : 'Error submitting review!'),
            'error'
        );
    } finally {
        isSubmitting.value = false;
    }
};

const deleteReview = async (reviewId) => {
    if (!confirm(locale.value === 'lv' ? 'Dzēst atsauksmi?' : 'Delete review?')) return;
    try {
        await axios.delete(`/reviews/${reviewId}`);
        reviews.value = reviews.value.filter(r => r.id !== reviewId);
        justSubmittedReview.value = false;
        displayToast(locale.value === 'lv' ? 'Atsauksme dzēsta' : 'Review deleted', 'success');
    } catch {
        displayToast(locale.value === 'lv' ? 'Kļūda dzēšot!' : 'Delete error!', 'error');
    }
};

const getReviewText = (r) => {
    if (locale.value === 'lv') return r.review_text_lv || r.review_text_en || r.review_text || '';
    return r.review_text_en || r.review_text_lv || r.review_text || '';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString(
        locale.value === 'lv' ? 'lv-LV' : 'en-GB',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
};

const getRelatedName = (p) => locale.value === 'lv' ? p.name_lv : p.name_en;

// ── TOAST ─────────────────────────────────────────────────────────
const showToast    = ref(false);
const toastMessage = ref('');
const toastType    = ref('success');
const displayToast = (message, type = 'success') => {
    toastMessage.value = message; toastType.value = type; showToast.value = true;
};
const closeToast = () => { showToast.value = false; };

onMounted(() => fetchProduct());
</script>

<template>
    <Head :title="getProductName || 'Product'" />
    <ShopLayout>
        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="closeToast" />

        <!-- Ielāde -->
        <div v-if="isLoading" class="pg-loading">
            <LoadingSpinner size="lg" :text="$t('common.loading')" />
        </div>

        <!-- Produkts -->
        <div v-else-if="productData" class="product-detail">

            <!-- Breadcrumb -->
            <nav class="breadcrumb-bar">
                <div class="wrap">
                    <ol class="breadcrumb">
                        <li><a href="/" class="bc-link">{{ $t('nav.home') }}</a></li>
                        <li class="bc-sep">/</li>
                        <li><a href="/shop" class="bc-link">{{ $t('nav.shop') }}</a></li>
                        <template v-if="productData.category">
                            <li class="bc-sep">/</li>
                            <li><a :href="`/shop/category/${productData.category.slug}`" class="bc-link">{{ getCategoryName }}</a></li>
                        </template>
                        <li class="bc-sep">/</li>
                        <li class="bc-current">{{ getProductName }}</li>
                    </ol>
                </div>
            </nav>

            <!-- Produkta saturs -->
            <section class="product-sec">
                <div class="wrap">
                    <div class="product-grid">

                        <!-- Attēls -->
                        <div class="img-col">
                            <div class="main-img">
                                <img :src="productImage" :alt="getProductName"
                                     @error="$event.target.src='/img/Products/placeholder.png'">
                                <span v-if="discountPercentage > 0" class="badge-discount">-{{ discountPercentage }}%</span>
                                <span v-if="!isInStock" class="badge-oos">{{ $t('product.out_of_stock') }}</span>
                                <span v-else-if="productData.stock_quantity <= productData.low_stock_threshold" class="badge-low">{{ $t('product.low_stock') }}</span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="info-col">
                            <h1 class="product-title">{{ getProductName }}</h1>
                            <p v-if="productData.sku" class="product-sku">SKU: {{ productData.sku }}</p>

                            <!-- Ātrs reitings -->
                            <div v-if="reviewStats.count > 0" class="quick-rating">
                                <span class="stars-sm">
                                    <i v-for="s in 5" :key="s" class="fas fa-star"
                                       :class="s <= Math.round(reviewStats.avg) ? 'star-on' : 'star-off'"></i>
                                </span>
                                <span class="rating-avg">{{ reviewStats.avg }}</span>
                                <span class="rating-cnt">({{ reviewStats.count }})</span>
                                <a href="#reviews" class="rating-link">
                                    {{ locale === 'lv' ? 'Skatīt atsauksmes' : 'See reviews' }}
                                </a>
                            </div>

                            <!-- Cenas -->
                            <div class="price-row">
                                <span v-if="productData.compare_price" class="price-old">€{{ fmt(productData.compare_price) }}</span>
                                <span class="price-main">€{{ fmt(productData.price) }}</span>
                                <span v-if="discountPercentage > 0" class="badge-save">{{ locale === 'lv' ? 'Ietaupi' : 'Save' }} -{{ discountPercentage }}%</span>
                            </div>
                            <p v-if="productData.vat_amount" class="price-vat">
                                <template v-if="locale === 'lv'">
                                    t.sk. PVN ({{ productData.vat_rate || 21 }}%):
                                    <strong>€{{ fmt(productData.vat_amount) }}</strong>
                                    &nbsp;·&nbsp; bez PVN:
                                    <strong>€{{ fmt(productData.price_ex_vat) }}</strong>
                                </template>
                                <template v-else>
                                    incl. VAT ({{ productData.vat_rate || 21 }}%):
                                    <strong>€{{ fmt(productData.vat_amount) }}</strong>
                                    &nbsp;·&nbsp; excl. VAT:
                                    <strong>€{{ fmt(productData.price_ex_vat) }}</strong>
                                </template>
                            </p>

                            <!-- Krājumi -->
                            <div :class="['stock-badge', isInStock ? 'stock-in' : 'stock-out']">
                                <i :class="isInStock ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                                <span>
                                    <template v-if="isInStock">
                                        {{ $t('product.in_stock') }} ({{ productData.stock_quantity }} {{ locale === 'lv' ? 'gab.' : 'pcs' }})
                                    </template>
                                    <template v-else>{{ $t('product.out_of_stock') }}</template>
                                </span>
                            </div>

                            <!-- Apraksts -->
                            <p class="product-desc">{{ getProductDescription }}</p>

                            <!-- Izmēri -->
                            <div v-if="hasSizes && isInStock" class="size-wrap" :class="{ 'size-err': sizeError }">
                                <div class="size-head">
                                    <span class="size-label">
                                        {{ locale === 'lv' ? 'Izvēlies izmēru' : 'Select Size' }}
                                        <span class="req">*</span>
                                    </span>
                                    <span v-if="selectedSize" class="size-chosen">
                                        {{ locale === 'lv' ? 'Izvēlēts:' : 'Selected:' }}
                                        <strong>{{ selectedSize }}</strong>
                                    </span>
                                </div>
                                <div class="size-btns">
                                    <button
                                        v-for="size in SIZES" :key="size"
                                        @click="selectSize(size)"
                                        :class="['sz', { 'sz--active': selectedSize === size }]"
                                        type="button"
                                    >{{ size }}</button>
                                </div>
                                <p v-if="sizeError" class="size-err-msg">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ locale === 'lv' ? 'Lūdzu izvēlies izmēru' : 'Please select a size' }}
                                </p>
                            </div>

                            <!-- Daudzums -->
                            <div v-if="isInStock" class="qty-row">
                                <label class="qty-label">{{ $t('product.quantity') }}:</label>
                                <div class="qty-ctrl">
                                    <button @click="decreaseQuantity" :disabled="quantity <= 1" class="qty-btn">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input v-model.number="quantity" type="number" min="1"
                                           :max="productData.stock_quantity" class="qty-inp">
                                    <button @click="increaseQuantity" :disabled="quantity >= productData.stock_quantity" class="qty-btn">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Pievienot grozam -->
                            <button @click="addToCart" :disabled="!isInStock || isAddingToCart" class="atc-btn">
                                <i v-if="!isAddingToCart" class="fas fa-shopping-cart"></i>
                                <i v-else class="fas fa-spinner fa-spin"></i>
                                <template v-if="isAddingToCart">{{ $t('common.adding') }}</template>
                                <template v-else-if="isInStock">{{ $t('common.add_to_cart') }}</template>
                                <template v-else>{{ $t('product.out_of_stock') }}</template>
                            </button>

                            <!-- Detaļas -->
                            <div class="details-box">
                                <h3 class="details-title">{{ $t('product.details') }}</h3>
                                <ul class="details-list">
                                    <li v-if="productData.category">
                                        <span class="dl-key">{{ $t('product.category') }}:</span>
                                        <span>{{ getCategoryName }}</span>
                                    </li>
                                    <li v-if="hasSizes">
                                        <span class="dl-key">{{ locale === 'lv' ? 'Izmēri:' : 'Sizes:' }}</span>
                                        <span>{{ SIZES.join(', ') }}</span>
                                    </li>
                                    <li>
                                        <span class="dl-key">{{ $t('product.availability') }}:</span>
                                        <span>{{ isInStock ? $t('product.in_stock') : $t('product.out_of_stock') }}</span>
                                    </li>
                                    <li v-if="productData.sku">
                                        <span class="dl-key">SKU:</span>
                                        <span>{{ productData.sku }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ════════════════════════════════════════════════
                 ATSAUKSMES
                 ════════════════════════════════════════════════ -->
            <section id="reviews" class="reviews-sec">
                <div class="wrap">
                    <!-- Virsraksts + forma poga -->
                    <div class="reviews-head">
                        <h2 class="reviews-title">
                            {{ locale === 'lv' ? 'Atsauksmes' : 'Reviews' }}
                            <span v-if="reviewStats.count > 0" class="rev-badge">{{ reviewStats.count }}</span>
                        </h2>
                        <button
                            v-if="authUser && !userReview"
                            @click="showReviewForm = !showReviewForm"
                            class="write-btn"
                        >
                            <i class="fas fa-pen"></i>
                            {{ locale === 'lv' ? 'Rakstīt atsauksmi' : 'Write a review' }}
                        </button>
                        <p v-else-if="!authUser" class="login-hint">
                            <a href="/login">{{ locale === 'lv' ? 'Piesakies' : 'Log in' }}</a>
                            {{ locale === 'lv' ? ', lai pievienotu atsauksmi' : ' to leave a review' }}
                        </p>
                        <p v-else-if="userReview && !userReview.is_approved" class="pending-hint">
                            <i class="fas fa-clock"></i>
                            {{ locale === 'lv' ? 'Jūsu atsauksme gaida apstiprinājumu' : 'Your review is awaiting approval' }}
                        </p>
                    </div>

                    <!-- Statistika -->
                    <div v-if="reviewStats.count > 0" class="stats-box">
                        <div class="stats-avg">
                            <span class="avg-num">{{ reviewStats.avg }}</span>
                            <span class="avg-stars">
                                <i v-for="s in 5" :key="s" class="fas fa-star"
                                   :class="s <= Math.round(reviewStats.avg) ? 'star-on' : 'star-off'"></i>
                            </span>
                            <span class="avg-cnt">{{ reviewStats.count }} {{ locale === 'lv' ? 'atsauksmes' : 'reviews' }}</span>
                        </div>
                        <div class="stats-bars">
                            <div v-for="star in [5,4,3,2,1]" :key="star" class="bar-row">
                                <span class="bar-lbl">{{ star }} <i class="fas fa-star star-on" style="font-size:.7rem"></i></span>
                                <div class="bar-track">
                                    <div class="bar-fill"
                                         :style="{ width: reviewStats.count ? (reviewStats.dist[star] / reviewStats.count * 100) + '%' : '0%' }">
                                    </div>
                                </div>
                                <span class="bar-cnt">{{ reviewStats.dist[star] || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Atsauksmes forma -->
                    <Transition name="rev-form">
                        <div v-if="showReviewForm && authUser" class="rev-form">
                            <h3 class="rev-form-title">{{ locale === 'lv' ? 'Jūsu atsauksme' : 'Your Review' }}</h3>

                            <!-- Zvaigznes -->
                            <div class="star-pick">
                                <span class="star-pick-lbl">{{ locale === 'lv' ? 'Vērtējums:' : 'Rating:' }}</span>
                                <div class="star-pick-row">
                                    <button
                                        v-for="star in 5" :key="star"
                                        type="button"
                                        @click="reviewForm.rating = star"
                                        @mouseenter="hoverStar = star"
                                        @mouseleave="hoverStar = 0"
                                        class="star-btn"
                                    >
                                        <i class="fas fa-star" :class="star <= (hoverStar || reviewForm.rating) ? 'star-on' : 'star-off'"></i>
                                    </button>
                                    <span v-if="reviewForm.rating" class="star-label">
                                        {{ ['','😕 Ļoti slikts','😐 Slikts','🙂 Vidējs','😊 Labs','🤩 Izcils'][reviewForm.rating] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Teksts -->
                            <div class="rev-field">
                                <label class="rev-field-lbl">
                                    {{ locale === 'lv' ? 'Komentārs (pēc izvēles):' : 'Comment (optional):' }}
                                </label>
                                <textarea
                                    v-model="reviewForm.review_text"
                                    :placeholder="locale === 'lv' ? 'Pastāstiet par savu pieredzi...' : 'Share your experience...'"
                                    class="rev-ta" rows="4" maxlength="1000"
                                ></textarea>
                                <span class="char-cnt">{{ reviewForm.review_text.length }}/1000</span>
                            </div>

                            <div class="rev-actions">
                                <button @click="showReviewForm = false" class="btn-cancel">
                                    {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                                </button>
                                <button @click="submitReview" :disabled="!reviewForm.rating || isSubmitting" class="btn-submit">
                                    <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
                                    <i v-else class="fas fa-paper-plane"></i>
                                    {{ isSubmitting
                                    ? (locale === 'lv' ? 'Iesniedz...' : 'Submitting...')
                                    : (locale === 'lv' ? 'Iesniegt' : 'Submit') }}
                                </button>
                            </div>
                        </div>
                    </Transition>

                    <!-- Saraksts -->
                    <div v-if="isLoadingReviews" class="rev-loading">
                        <LoadingSpinner size="md" />
                    </div>
                    <div v-else-if="reviews.length === 0" class="rev-empty">
                        <i class="fas fa-comment-slash"></i>
                        <p>{{ locale === 'lv' ? 'Vēl nav atsauksmju. Esiet pirmais!' : 'No reviews yet. Be the first!' }}</p>
                    </div>
                    <div v-else class="rev-list">
                        <div v-for="review in reviews" :key="review.id"
                             :class="['rev-card', { 'rev-card--pending': !review.is_approved }]">

                            <!-- Neapstiprinātas indikators -->
                            <div v-if="!review.is_approved" class="pending-banner">
                                <i class="fas fa-clock"></i>
                                {{ locale === 'lv' ? 'Gaida apstiprinājumu (redzama tikai jums)' : 'Pending approval (visible only to you)' }}
                            </div>

                            <!-- Autors -->
                            <div class="rev-author">
                                <img
                                    :src="review.user?.profile_picture
                                        ? (review.user.profile_picture.startsWith('/') ? review.user.profile_picture : '/storage/' + review.user.profile_picture)
                                        : '/img/default-avatar.png'"
                                    :alt="review.user?.username"
                                    class="rev-avatar"
                                    @error="$event.target.src='/img/default-avatar.png'"
                                >
                                <div class="rev-meta">
                                    <strong class="rev-name">{{ review.user?.username || 'Lietotājs' }}</strong>
                                    <time class="rev-date">{{ formatDate(review.created_at) }}</time>
                                </div>
                                <button
                                    v-if="authUser && (Number(authUser.id) === Number(review.user_id) || Number(authUser.id) === Number(review.user?.id))"
                                    @click="deleteReview(review.id)"
                                    class="rev-del"
                                    :title="locale === 'lv' ? 'Dzēst' : 'Delete'"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <!-- Zvaigznes -->
                            <div class="rev-stars">
                                <i v-for="s in 5" :key="s" class="fas fa-star"
                                   :class="s <= review.rating ? 'star-on' : 'star-off'"></i>
                                <span class="rev-num">{{ review.rating }}/5</span>
                            </div>

                            <!-- Teksts -->
                            <p v-if="getReviewText(review)" class="rev-text">{{ getReviewText(review) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ════════════════════════════════════════════════
                 SAISTĪTIE PRODUKTI — ar cenām, atlaidēm, PVN
                 ════════════════════════════════════════════════ -->
            <section v-if="relatedProducts.length > 0" class="related-sec">
                <div class="wrap">
                    <h2 class="related-title">{{ $t('product.related_products') }}</h2>
                    <div class="related-grid">
                        <div v-for="related in relatedProducts" :key="related.id" class="rel-card">
                            <a :href="`/shop/product/${related.slug}`" class="rel-link">
                                <div class="rel-img">
                                    <img
                                        :src="related.image || '/img/Products/placeholder.png'"
                                        :alt="getRelatedName(related)"
                                        @error="$event.target.src='/img/Products/placeholder.png'"
                                    >
                                    <span v-if="relatedDiscountPct(related) > 0" class="rel-badge-sale">
                                        -{{ relatedDiscountPct(related) }}%
                                    </span>
                                    <span v-if="!relatedInStock(related)" class="rel-badge-oos">
                                        {{ $t('product.out_of_stock') }}
                                    </span>
                                    <span v-if="related.has_sizes" class="rel-badge-sz">
                                        <i class="fas fa-ruler-horizontal"></i>
                                    </span>
                                </div>
                                <div class="rel-body">
                                    <h3 class="rel-name">{{ getRelatedName(related) }}</h3>
                                    <div class="rel-prices">
                                        <span v-if="related.compare_price" class="rel-price-old">
                                            €{{ fmt(related.compare_price) }}
                                        </span>
                                        <span class="rel-price-now">€{{ fmt(related.price) }}</span>
                                    </div>
                                    <p v-if="related.vat_amount" class="rel-vat">
                                        t.sk. PVN: €{{ fmt(related.vat_amount) }}
                                    </p>
                                </div>
                            </a>
                            <button
                                class="rel-atc"
                                :disabled="!relatedInStock(related)"
                                @click.prevent="addRelatedToCart(related)"
                            >
                                <i :class="related.has_sizes ? 'fas fa-ruler-horizontal' : 'fas fa-cart-plus'"></i>
                                {{ relatedInStock(related)
                                ? (related.has_sizes
                                    ? (locale === 'lv' ? 'Izvēlies izmēru' : 'Select size')
                                    : $t('common.add_to_cart'))
                                : $t('product.out_of_stock') }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Kļūda -->
        <div v-else class="err-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>{{ $t('product.not_found') }}</h2>
            <p>{{ locale === 'lv' ? 'Šis produkts neeksistē vai nav pieejams' : 'This product does not exist or is not available' }}</p>
            <a href="/shop" class="back-btn">{{ $t('shop.back_to_shop') }}</a>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* ── PAMATA ── */
.pg-loading { display: flex; justify-content: center; align-items: center; min-height: 60vh; }
.wrap { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

/* ── BREADCRUMB ── */
.breadcrumb-bar { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 1rem 1.5rem; }
.breadcrumb { display: flex; align-items: center; gap: .375rem; font-size: .875rem; list-style: none; padding: 0; margin: 0; flex-wrap: wrap; }
.bc-link { color: #dc2626; text-decoration: none; font-weight: 500; }
.bc-link:hover { text-decoration: underline; }
.bc-sep { color: #9ca3af; }
.bc-current { color: #6b7280; }

/* ── PRODUKTS ── */
.product-sec { padding: 3rem 1.5rem; }
.product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; }

.img-col { position: sticky; top: 80px; height: fit-content; }
.main-img { position: relative; aspect-ratio: 1; background: #f9fafb; border-radius: 1rem; overflow: hidden; }
.main-img img { width: 100%; height: 100%; object-fit: cover; }
.badge-discount { position: absolute; top: 1rem; left: 1rem; background: #dc2626; color: white; padding: .4rem .875rem; border-radius: .375rem; font-size: .9375rem; font-weight: 700; }
.badge-oos  { position: absolute; top: 1rem; right: 1rem; background: #6b7280; color: white; padding: .4rem .875rem; border-radius: .375rem; font-size: .875rem; font-weight: 600; }
.badge-low  { position: absolute; top: 1rem; right: 1rem; background: #f59e0b; color: white; padding: .4rem .875rem; border-radius: .375rem; font-size: .875rem; font-weight: 600; }

.info-col { display: flex; flex-direction: column; gap: 1.375rem; }
.product-title { font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 800; color: #111827; line-height: 1.2; margin: 0; }
.product-sku { font-size: .875rem; color: #9ca3af; margin: 0; }

/* Reitings */
.quick-rating { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.stars-sm { display: flex; gap: .1rem; }
.star-on  { color: #f59e0b; }
.star-off { color: #d1d5db; }
.rating-avg { font-weight: 700; color: #111827; }
.rating-cnt { color: #9ca3af; font-size: .875rem; }
.rating-link { color: #dc2626; font-size: .875rem; text-decoration: none; }
.rating-link:hover { text-decoration: underline; }

/* Cenas */
.price-row { display: flex; align-items: center; gap: .875rem; flex-wrap: wrap; }
.price-old  { font-size: 1.375rem; color: #9ca3af; text-decoration: line-through; }
.price-main { font-size: 2.25rem; font-weight: 800; color: #dc2626; }
.badge-save { background: #fee2e2; color: #dc2626; padding: .375rem .875rem; border-radius: .375rem; font-size: .875rem; font-weight: 700; }
.price-vat  { font-size: .8rem; color: #9ca3af; margin: -.5rem 0 0; }

/* Krājumi */
.stock-badge { display: flex; align-items: center; gap: .5rem; padding: .875rem 1rem; border-radius: .625rem; }
.stock-in  { background: #f0fdf4; }
.stock-out { background: #fef2f2; }
.stock-in i  { color: #16a34a; font-size: 1.125rem; }
.stock-out i { color: #dc2626; font-size: 1.125rem; }
.stock-in span  { font-weight: 600; color: #166534; }
.stock-out span { font-weight: 600; color: #991b1b; }
.product-desc { font-size: 1.0625rem; line-height: 1.75; color: #4b5563; margin: 0; }

/* Izmēri */
.size-wrap { padding: 1.125rem; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: .75rem; transition: border-color .2s; }
.size-err  { border-color: #ef4444 !important; background: #fff5f5; animation: shake .3s; }
@keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }
.size-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem; }
.size-label { font-size: .9375rem; font-weight: 700; color: #111827; }
.req { color: #dc2626; margin-left: 2px; }
.size-chosen { font-size: .875rem; color: #059669; font-weight: 600; }
.size-btns { display: flex; gap: .5rem; flex-wrap: wrap; }
.sz { min-width: 2.75rem; padding: .45rem .8rem; border: 2px solid #d1d5db; border-radius: .5rem; background: white; color: #374151; font-size: .875rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.sz:hover { border-color: #dc2626; color: #dc2626; background: #fff5f5; }
.sz--active { border-color: #dc2626 !important; background: #dc2626 !important; color: white !important; box-shadow: 0 2px 8px rgba(220,38,38,.3); }
.size-err-msg { margin: .5rem 0 0; font-size: .8125rem; color: #dc2626; display: flex; align-items: center; gap: .3rem; }

/* Daudzums */
.qty-row { display: flex; align-items: center; gap: 1rem; }
.qty-label { font-weight: 600; color: #111827; }
.qty-ctrl { display: flex; align-items: center; border: 2px solid #e5e7eb; border-radius: .5rem; overflow: hidden; }
.qty-btn { width: 2.75rem; height: 2.75rem; border: none; background: white; cursor: pointer; font-size: .9375rem; transition: background .2s; }
.qty-btn:hover:not(:disabled) { background: #f9fafb; }
.qty-btn:disabled { opacity: .4; cursor: not-allowed; }
.qty-inp { width: 3.5rem; height: 2.75rem; border: none; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; text-align: center; font-size: 1rem; font-weight: 600; }
.qty-inp:focus { outline: none; }

/* Pievienot grozam */
.atc-btn { width: 100%; padding: 1.125rem; background: #dc2626; color: white; border: none; border-radius: .75rem; font-size: 1.0625rem; font-weight: 700; cursor: pointer; transition: all .3s; display: flex; align-items: center; justify-content: center; gap: .75rem; }
.atc-btn:hover:not(:disabled) { background: #b91c1c; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220,38,38,.3); }
.atc-btn:disabled { background: #9ca3af; cursor: not-allowed; transform: none; }

/* Detaļas */
.details-box { padding: 1.5rem; background: #f9fafb; border-radius: .75rem; }
.details-title { font-size: 1.125rem; font-weight: 700; color: #111827; margin-bottom: .875rem; }
.details-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .625rem; }
.details-list li { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: .625rem; border-bottom: 1px solid #e5e7eb; font-size: .9375rem; }
.details-list li:last-child { border-bottom: none; padding-bottom: 0; }
.dl-key { color: #6b7280; font-weight: 500; flex-shrink: 0; }

/* ════ ATSAUKSMES ════ */
.reviews-sec { padding: 3rem 1.5rem 4rem; background: #f9fafb; border-top: 1px solid #e5e7eb; }
.reviews-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; }
.reviews-title { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: #111827; display: flex; align-items: center; gap: .625rem; margin: 0; }
.rev-badge { background: #dc2626; color: white; font-size: .8125rem; font-weight: 700; padding: .15rem .6rem; border-radius: 999px; }
.write-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .7rem 1.375rem; background: #dc2626; color: white; border: none; border-radius: .625rem; font-weight: 600; cursor: pointer; transition: all .25s; }
.write-btn:hover { background: #b91c1c; transform: translateY(-1px); }
.login-hint { font-size: .9375rem; color: #6b7280; margin: 0; }
.login-hint a { color: #dc2626; font-weight: 600; text-decoration: none; }
.login-hint a:hover { text-decoration: underline; }
.pending-hint { font-size: .875rem; color: #9ca3af; display: flex; align-items: center; gap: .375rem; margin: 0; }

/* Statistika */
.stats-box { display: flex; gap: 2.5rem; background: white; border-radius: 1rem; padding: 1.75rem; box-shadow: 0 1px 4px rgba(0,0,0,.07); margin-bottom: 1.75rem; }
.stats-avg { display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 110px; gap: .375rem; }
.avg-num   { font-size: 3.25rem; font-weight: 900; color: #111827; line-height: 1; }
.avg-stars { display: flex; gap: .125rem; }
.avg-cnt   { font-size: .8125rem; color: #9ca3af; text-align: center; }
.stats-bars { flex: 1; display: flex; flex-direction: column; gap: .5rem; justify-content: center; }
.bar-row { display: flex; align-items: center; gap: .625rem; }
.bar-lbl  { font-size: .8125rem; font-weight: 600; color: #374151; width: 1.75rem; flex-shrink: 0; }
.bar-track { flex: 1; height: .5rem; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
.bar-fill  { height: 100%; background: #f59e0b; border-radius: 999px; transition: width .5s; }
.bar-cnt   { font-size: .8125rem; color: #9ca3af; width: 1rem; text-align: right; flex-shrink: 0; }

/* Forma */
.rev-form { background: white; border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.75rem; box-shadow: 0 4px 16px rgba(0,0,0,.09); border: 2px solid #dc2626; }
.rev-form-title { font-size: 1.125rem; font-weight: 700; color: #111827; margin-bottom: 1.25rem; }
.star-pick { margin-bottom: 1.125rem; }
.star-pick-lbl { font-size: .9375rem; font-weight: 600; color: #374151; display: block; margin-bottom: .5rem; }
.star-pick-row { display: flex; align-items: center; gap: .2rem; flex-wrap: wrap; }
.star-btn { background: none; border: none; cursor: pointer; font-size: 1.875rem; padding: .1rem; transition: transform .15s; }
.star-btn:hover { transform: scale(1.2); }
.star-label { margin-left: .625rem; font-size: .9375rem; font-weight: 600; color: #374151; }
.rev-field { margin-bottom: 1.125rem; position: relative; }
.rev-field-lbl { display: block; font-size: .9375rem; font-weight: 600; color: #374151; margin-bottom: .5rem; }
.rev-ta { width: 100%; padding: .75rem 1rem; border: 2px solid #e5e7eb; border-radius: .625rem; font-size: .9375rem; resize: vertical; font-family: inherit; transition: border-color .25s; box-sizing: border-box; }
.rev-ta:focus { outline: none; border-color: #dc2626; }
.char-cnt { position: absolute; bottom: .625rem; right: .875rem; font-size: .75rem; color: #9ca3af; }
.rev-actions { display: flex; gap: .875rem; justify-content: flex-end; }
.btn-cancel { padding: .7rem 1.375rem; background: white; border: 2px solid #e5e7eb; border-radius: .625rem; color: #6b7280; font-weight: 600; cursor: pointer; transition: all .25s; }
.btn-cancel:hover { border-color: #9ca3af; }
.btn-submit { display: inline-flex; align-items: center; gap: .5rem; padding: .7rem 1.5rem; background: #dc2626; color: white; border: none; border-radius: .625rem; font-weight: 700; cursor: pointer; transition: all .25s; }
.btn-submit:hover:not(:disabled) { background: #b91c1c; }
.btn-submit:disabled { background: #9ca3af; cursor: not-allowed; }

/* Atsauksmju saraksts */
.rev-loading { display: flex; justify-content: center; padding: 2rem; }
.rev-empty { text-align: center; padding: 2.5rem; }
.rev-empty i { font-size: 2.5rem; color: #d1d5db; display: block; margin-bottom: .75rem; }
.rev-empty p { color: #9ca3af; font-size: 1.0625rem; }
.rev-list { display: flex; flex-direction: column; gap: 1.125rem; }

.rev-card { background: white; border-radius: .875rem; padding: 1.375rem; box-shadow: 0 1px 4px rgba(0,0,0,.07); transition: box-shadow .2s; overflow: hidden; }
.rev-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.11); }
.rev-card--pending { border: 1.5px dashed #d1d5db; background: #fafafa; }

.pending-banner { display: flex; align-items: center; gap: .5rem; background: #fef3c7; color: #92400e; font-size: .8125rem; font-weight: 600; padding: .5rem .875rem; border-radius: .5rem; margin-bottom: 1rem; }

.rev-author { display: flex; align-items: center; gap: .875rem; margin-bottom: .75rem; }
.rev-avatar { width: 2.75rem; height: 2.75rem; border-radius: 50%; object-fit: cover; border: 2px solid #f3f4f6; flex-shrink: 0; }
.rev-meta { flex: 1; }
.rev-name { display: block; font-weight: 700; color: #111827; font-size: .9375rem; }
.rev-date { font-size: .8125rem; color: #9ca3af; }
.rev-del { background: none; border: none; color: #9ca3af; cursor: pointer; padding: .25rem; font-size: .9375rem; transition: color .2s; border-radius: .25rem; }
.rev-del:hover { color: #dc2626; background: #fef2f2; }

.rev-stars { display: flex; align-items: center; gap: .2rem; margin-bottom: .625rem; }
.rev-num { margin-left: .5rem; font-size: .8125rem; font-weight: 600; color: #6b7280; }
.rev-text { font-size: .9375rem; color: #374151; line-height: 1.7; margin: 0; }

.rev-form-enter-active, .rev-form-leave-active { transition: all .3s ease; }
.rev-form-enter-from, .rev-form-leave-to { opacity: 0; transform: translateY(-10px); }

/* ════ SAISTĪTIE PRODUKTI ════ */
.related-sec { padding: 3rem 1.5rem 4rem; background: white; }
.related-title { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: #111827; margin-bottom: 1.75rem; }
.related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }

.rel-card { background: white; border-radius: .875rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); transition: all .3s; display: flex; flex-direction: column; border: 1px solid #f3f4f6; }
.rel-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,.12); }
.rel-link { display: flex; flex-direction: column; text-decoration: none; color: inherit; flex: 1; }
.rel-img { position: relative; aspect-ratio: 1; background: #f9fafb; overflow: hidden; }
.rel-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
.rel-card:hover .rel-img img { transform: scale(1.06); }
.rel-badge-sale { position: absolute; top: .625rem; left: .625rem; background: #dc2626; color: white; padding: .2rem .5rem; border-radius: .25rem; font-size: .75rem; font-weight: 700; }
.rel-badge-oos  { position: absolute; top: .625rem; right: .625rem; background: #6b7280; color: white; padding: .2rem .5rem; border-radius: .25rem; font-size: .7rem; font-weight: 600; }
.rel-badge-sz   { position: absolute; bottom: .625rem; right: .625rem; background: rgba(0,0,0,.5); color: white; padding: .15rem .45rem; border-radius: .25rem; font-size: .7rem; }
.rel-body { padding: 1rem 1rem .75rem; flex: 1; }
.rel-name { font-size: .9375rem; font-weight: 600; color: #111827; margin-bottom: .5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.75rem; }
.rel-prices { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.rel-price-old { font-size: .8125rem; color: #9ca3af; text-decoration: line-through; }
.rel-price-now { font-size: 1.125rem; font-weight: 800; color: #dc2626; }
.rel-vat { font-size: .7rem; color: #9ca3af; margin: .2rem 0 0; }
.rel-atc { width: 100%; padding: .65rem; background: #dc2626; color: white; border: none; border-radius: 0 0 .875rem .875rem; font-weight: 600; font-size: .875rem; cursor: pointer; transition: background .2s; display: flex; align-items: center; justify-content: center; gap: .4rem; flex-shrink: 0; }
.rel-atc:hover:not(:disabled) { background: #b91c1c; }
.rel-atc:disabled { background: #9ca3af; cursor: not-allowed; }

/* ── KĻŪDA ── */
.err-state { text-align: center; padding: 5rem 2rem; }
.err-state i { font-size: 4rem; color: #d1d5db; display: block; margin-bottom: 1.25rem; }
.err-state h2 { font-size: 1.75rem; font-weight: 700; color: #111827; margin-bottom: .875rem; }
.err-state p  { font-size: 1.0625rem; color: #6b7280; margin-bottom: 1.75rem; }
.back-btn { display: inline-block; padding: .875rem 2rem; background: #dc2626; color: white; text-decoration: none; border-radius: .5rem; font-weight: 600; transition: all .25s; }
.back-btn:hover { background: #b91c1c; transform: translateY(-2px); }

/* ── RESPONSĪVS ── */
@media (max-width: 1024px) {
    .product-grid  { grid-template-columns: 1fr; gap: 2rem; }
    .img-col       { position: static; }
    .related-grid  { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .related-grid  { grid-template-columns: repeat(2, 1fr); }
    .stats-box     { flex-direction: column; gap: 1.25rem; padding: 1.25rem; }
    .stats-avg     { flex-direction: row; gap: .875rem; }
    .avg-num       { font-size: 2.5rem; }
    .reviews-head  { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 480px) {
    .product-sec   { padding: 1.5rem 1rem; }
    .reviews-sec   { padding: 1.5rem 1rem 3rem; }
    .related-sec   { padding: 1.5rem 1rem 3rem; }
    .related-grid  { grid-template-columns: 1fr; }
    .wrap          { padding: 0 1rem; }
    .rev-actions   { flex-direction: column; }
    .btn-cancel, .btn-submit { width: 100%; justify-content: center; }
}
</style>
