<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t, locale } = useI18n();
const page = usePage();

const props = defineProps({
    content: Object,
    userLiked: { type: Boolean, default: false },
    relatedContent: { type: Array, default: () => [] },
    auth: Object,
});

// Auth — no props vai page
const authUser = computed(() => props.auth?.user ?? page.props.auth?.user ?? null);

// State
const userRating   = ref(0);
const hoverRating  = ref(0);
const userLiked    = ref(props.userLiked);  // ← ielādē no servera
const likeCount    = ref(props.content.like_count || 0);
const comments     = ref([]);
const isLoadingComments = ref(false);

// Atsauksmes
const reviews             = ref([]);
const isLoadingReviews    = ref(false);
const reviewForm          = ref({ rating: 0, review_text: '' });
const hoverStar           = ref(0);
const isSubmitting        = ref(false);
const showReviewForm      = ref(false);
const justSubmittedReview = ref(false);

const reviewStats = computed(() => {
    if (!reviews.value.length) return { avg: 0, count: 0, dist: { 5:0,4:0,3:0,2:0,1:0 } };
    const total = reviews.value.length;
    const sum   = reviews.value.reduce((s, r) => s + r.rating, 0);
    const dist  = { 5:0, 4:0, 3:0, 2:0, 1:0 };
    reviews.value.forEach(r => { if (dist[r.rating] !== undefined) dist[r.rating]++; });
    return { avg: (sum / total).toFixed(1), count: total, dist };
});

const userReview = computed(() => {
    if (!authUser.value) return null;
    return reviews.value.find(r =>
        Number(r.user_id) === Number(authUser.value.id) ||
        Number(r.user?.id) === Number(authUser.value.id)
    ) || (justSubmittedReview.value ? { is_approved: false, _pending: true } : null);
});

const fetchReviews = async () => {
    isLoadingReviews.value = true;
    try {
        const res = await axios.get(`/api/v1/reviews/content/${props.content.id}`);
        reviews.value = res.data;
    } catch { reviews.value = []; }
    finally { isLoadingReviews.value = false; }
};

const submitReview = async () => {
    if (!reviewForm.value.rating) {
        showMsg(locale.value === 'lv' ? 'Lūdzu izvēlies vērtējumu!' : 'Please select a rating!', 'error');
        return;
    }
    isSubmitting.value = true;
    try {
        await axios.post('/reviews', {
            content_id:  props.content.id,
            rating:      reviewForm.value.rating,
            review_text: reviewForm.value.review_text,
        });
        showMsg(locale.value === 'lv'
            ? 'Atsauksme pievienota! Tā būs redzama pēc apstiprinājuma.'
            : 'Review submitted! Visible after approval.', 'success');
        reviewForm.value = { rating: 0, review_text: '' };
        showReviewForm.value = false;
        justSubmittedReview.value = true;
        await fetchReviews();
    } catch (err) {
        showMsg(err.response?.data?.message ||
            (locale.value === 'lv' ? 'Kļūda!' : 'Error!'), 'error');
    } finally { isSubmitting.value = false; }
};

const deleteReview = async (reviewId) => {
    if (!confirm(locale.value === 'lv' ? 'Dzēst atsauksmi?' : 'Delete review?')) return;
    try {
        await axios.delete(`/reviews/${reviewId}`);
        reviews.value = reviews.value.filter(r => r.id !== reviewId);
        justSubmittedReview.value = false;
        showMsg(locale.value === 'lv' ? 'Atsauksme dzēsta' : 'Review deleted', 'success');
    } catch { showMsg(locale.value === 'lv' ? 'Kļūda!' : 'Error!', 'error'); }
};

const getReviewText = (r) => {
    if (locale.value === 'lv') return r.review_text_lv || r.review_text_en || r.review_text || '';
    return r.review_text_en || r.review_text_lv || r.review_text || '';
};

const formatRevDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString(
        locale.value === 'lv' ? 'lv-LV' : 'en-GB',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
};

// Lomas badge — parāda lomu nosaukumu
const getRoleBadge = (user) => {
    if (!user?.role) return null;
    const name = user.role.name;
    if (name === 'administrator') return { label: locale.value === 'lv' ? 'Admins' : 'Admin', cls: 'role-admin' };
    if (name === 'courier')       return { label: locale.value === 'lv' ? 'Kurjers' : 'Courier', cls: 'role-courier' };
    return null; // parasts lietotājs — nerāda badge
};

// Toast / success message
const toastMsg     = ref('');
const toastType    = ref('success');
const showToast    = ref(false);
const showMsg = (msg, type = 'success') => {
    toastMsg.value = msg; toastType.value = type; showToast.value = true;
    setTimeout(() => { showToast.value = false; }, 3500);
};

// Computed
const getTitle = computed(() => {
    return locale.value === 'lv' ? props.content.title_lv : (props.content.title_en || props.content.title_lv);
});

const getDescription = computed(() => {
    return locale.value === 'lv' ? props.content.description_lv : (props.content.description_en || props.content.description_lv);
});

const getBody = computed(() => {
    return locale.value === 'lv' ? props.content.content_body_lv : (props.content.content_body_en || props.content.content_body_lv);
});

const isVideo = computed(() => props.content.type === 'video');

const contentTypeLabel = computed(() => {
    const map = {
        video:        { lv: 'Video',       en: 'Video' },
        blog:         { lv: 'Blogs',        en: 'Blog' },
        post:         { lv: 'Ziņa',         en: 'Post' },
        announcement: { lv: 'Paziņojums',   en: 'Announcement' },
    };
    const entry = map[props.content.type] || { lv: props.content.type, en: props.content.type };
    return locale.value === 'lv' ? entry.lv : entry.en;
});

const contentTypeIcon = computed(() => {
    const map = {
        video: 'fas fa-play-circle',
        blog: 'fas fa-pen-nib',
        news: 'fas fa-bullhorn',
        post: 'fas fa-bullhorn',
        announcement: 'fas fa-bell',
    };
    return map[props.content.type] || 'fas fa-file';
});

const embedUrl = computed(() => {
    if (!isVideo.value || !props.content.video_url) return null;
    const url      = props.content.video_url;
    const platform = props.content.video_platform;

    if (platform === 'YouTube') {
        const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (match) return `https://www.youtube.com/embed/${match[1]}`;
    }
    // TikTok, Instagram, Facebook, X — nav tiešas embed iespējas, atveras jaunā cilnē
    return null;
});

// Universāls attēla URL aprēķins
const resolveImg = (path, fallbackDir = '/img/thumbnails') => {
    if (!path) return null;
    if (path.startsWith('http') || path.startsWith('/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return `${fallbackDir}/${path}`;
};

// Iegūst attēla mapi pēc satura tipa
const getTypeDir = (type) => {
    const map = { video: '/img/thumbnails', blog: '/img/Blogs', post: '/img/Posts', announcement: '/img/Announcements' };
    return map[type] || '/img/thumbnails';
};

const getThumbnail = computed(() => {
    const typeDir = getTypeDir(props.content.type);
    if (props.content.thumbnail) {
        const url = resolveImg(props.content.thumbnail, typeDir);
        if (url) return url;
    }
    if (isVideo.value && props.content.video_url && props.content.video_platform === 'YouTube') {
        const match = props.content.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (match) return `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`;
    }
    if (props.content.featured_image) {
        const url = resolveImg(props.content.featured_image, typeDir);
        if (url) return url;
    }
    return '/img/no-content-placeholder.png';
});

const formatDate = computed(() => {
    return new Date(props.content.published_at).toLocaleDateString(
        locale.value === 'lv' ? 'lv-LV' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
});

const platformIcon = computed(() => {
    const map = {
        YouTube:   'fab fa-youtube',
        TikTok:    'fab fa-tiktok',
        Instagram: 'fab fa-instagram',
        Facebook:  'fab fa-facebook',
        X:         'fab fa-x-twitter',
        Twitter:   'fab fa-x-twitter',
    };
    return map[props.content.video_platform] || 'fas fa-video';
});

// Methods
const toggleLike = async () => {
    if (!authUser.value) {
        showMsg(locale.value === 'lv' ? 'Lūdzu piesakies, lai patiktu!' : 'Please log in to like!', 'error');
        return;
    }
    const prev = userLiked.value;
    const prevCount = likeCount.value;
    userLiked.value  = !userLiked.value;
    likeCount.value += userLiked.value ? 1 : -1;
    try {
        const res = await axios.post(`/content/${props.content.id}/like`, {}, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }
        });
        likeCount.value = res.data.like_count;
        userLiked.value = res.data.liked;
        showMsg(userLiked.value
            ? (locale.value === 'lv' ? 'Patīk!' : 'Liked!')
            : (locale.value === 'lv' ? 'Noņemts' : 'Removed'), 'success');
    } catch {
        userLiked.value = prev;
        likeCount.value = prevCount;
        showMsg(locale.value === 'lv' ? 'Kļūda!' : 'Error!', 'error');
    }
};

// Comment form
const commentForm = useForm({
    content_id: props.content.id,
    comment_text: '',
});

const submitComment = () => {
    if (!authUser.value) { showMsg(t('content.login_required'), 'error'); return; }
    commentForm.post('/comments', {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset('comment_text');
            showMsg(t('content.comment_submitted'), 'success');
            loadComments();
        },
    });
};

const loadComments = async () => {
    isLoadingComments.value = true;
    try {
        const res = await axios.get(`/api/v1/comments/content/${props.content.id}`);
        comments.value = res.data || [];
    } catch { comments.value = []; }
    finally { isLoadingComments.value = false; }
};

const openSource = () => {
    if (props.content.video_url) window.open(props.content.video_url, '_blank');
};

const shareContent = () => {
    if (navigator.share) {
        navigator.share({ title: getTitle.value, text: getDescription.value, url: window.location.href });
    } else {
        navigator.clipboard.writeText(window.location.href);
        showMsg(locale.value === 'lv' ? 'Saite nokopēta!' : 'Link copied!', 'success');
    }
};

const relatedTitle = (item) => locale.value === 'lv' ? item.title_lv : (item.title_en || item.title_lv);
const relatedThumb = (item) => {
    const typeDir = getTypeDir(item.type);
    if (item.thumbnail) {
        const url = resolveImg(item.thumbnail, typeDir);
        if (url) return url;
    }
    if (item.type === 'video' && item.video_url) {
        const m = item.video_url?.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (m) return `https://img.youtube.com/vi/${m[1]}/mqdefault.jpg`;
    }
    if (item.featured_image) {
        return resolveImg(item.featured_image, typeDir) || '/img/no-content-placeholder.png';
    }
    return '/img/no-content-placeholder.png';
};

// ── ATTĒLU RITINĀTĀJS (post tips) ────────────────────────────
const isPost = computed(() => props.content.type === 'post');

// blog_images var būt JSON masīvs vai kā string — normalizē
const postImages = computed(() => {
    const raw = props.content.blog_images;
    if (!raw) return [];
    const arr = Array.isArray(raw) ? raw : (typeof raw === 'string' ? (() => { try { return JSON.parse(raw); } catch { return []; } })() : []);
    return arr.map(img => resolveImg(img, '/img/Posts')).filter(Boolean);
});

const sliderIndex   = ref(0);
const sliderPrev    = () => { sliderIndex.value = (sliderIndex.value - 1 + postImages.value.length) % postImages.value.length; };
const sliderNext    = () => { sliderIndex.value = (sliderIndex.value + 1) % postImages.value.length; };
const sliderGoTo    = (i) => { sliderIndex.value = i; };

onMounted(() => {
    loadComments();
    fetchReviews();
});
</script>

<template>
    <Head :title="getTitle" />

    <MainLayout>
        <div class="content-show">
            <!-- Toast Notification -->
            <Transition name="notification">
                <div v-if="showToast" class="toast-notif" :class="`toast-notif--${toastType}`">
                    <i :class="toastType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
                    <span>{{ toastMsg }}</span>
                </div>
            </Transition>

            <!-- Breadcrumbs -->
            <div class="breadcrumbs">
                <Link href="/content" class="breadcrumb-item">
                    <i class="fas fa-arrow-left"></i>
                    {{ t('content.back_to_list') }}
                </Link>
            </div>

            <!-- Video Player (YouTube embed) -->
            <div v-if="isVideo && embedUrl" class="video-container">
                <iframe
                    :src="embedUrl"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="video-iframe"
                ></iframe>
            </div>

            <!-- Non-embeddable video (TikTok, Instagram, etc.) — klikšķināms uz platformas -->
            <div v-else-if="isVideo" class="content-hero">
                <div class="video-preview" @click="openSource">
                    <img :src="getThumbnail" :alt="getTitle" class="hero-image"
                         @error="$event.target.src='/img/no-content-placeholder.png'">
                    <div class="play-overlay">
                        <i :class="platformIcon" style="font-size:3rem"></i>
                        <span>{{ locale === 'lv' ? 'Skatīties' : 'Watch' }} {{ content.video_platform }}</span>
                    </div>
                </div>
            </div>

            <!-- Blog / news / announcement hero image -->
            <div v-else class="content-hero">
                <img :src="getThumbnail" :alt="getTitle" class="hero-image"
                     @error="$event.target.src='/img/no-content-placeholder.png'">
            </div>

            <!-- ── Main Content ── -->
            <div class="content-container">

                <!-- Header card -->
                <div class="content-header-section">
                    <div class="header-left">
                        <h1 class="content-main-title">{{ getTitle }}</h1>

                        <div class="content-meta-bar">
                            <span class="meta-badge" :class="`meta-badge-${content.type}`">
                                <i :class="contentTypeIcon"></i>
                                {{ contentTypeLabel }}
                            </span>
                            <span v-if="content.category" class="meta-badge meta-badge-category">
                                <i class="fas fa-tag"></i>
                                {{ content.category }}
                            </span>
                            <span class="meta-text">
                                <i class="fas fa-calendar"></i>
                                {{ formatDate }}
                            </span>
                            <span class="meta-text">
                                <i class="fas fa-eye"></i>
                                {{ content.view_count }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="header-actions">
                        <button @click="toggleLike" class="action-btn" :class="{ 'action-btn-liked': userLiked }">
                            <i class="fas fa-heart"></i>
                            <span>{{ likeCount }}</span>
                        </button>
                        <button @click="shareContent" class="action-btn">
                            <i class="fas fa-share-alt"></i>
                            <span>{{ t('content.share') }}</span>
                        </button>
                        <button v-if="isVideo && content.video_url" @click="openSource" class="action-btn action-btn-primary">
                            <i :class="platformIcon"></i>
                            <span>{{ content.video_platform }}</span>
                        </button>
                    </div>
                </div>

                <!-- Description -->
                <div class="content-description-section">
                    <h2 class="section-subtitle">
                        <i class="fas fa-align-left"></i>
                        {{ t('content.description') }}
                    </h2>
                    <p class="content-description-text">{{ getDescription }}</p>
                </div>

                <!-- Body Content (blog/news/announcement) -->
                <div v-if="getBody" class="content-body-section">
                    <div class="content-body" v-html="getBody"></div>
                </div>

                <!-- ── ATTĒLU RITINĀTĀJS (tikai post tipam) ── -->
                <div v-if="isPost && postImages.length > 0" class="post-slider">
                    <!-- Viena bilde -->
                    <div v-if="postImages.length === 1" class="slider-single">
                        <img :src="postImages[0]" alt="Post image"
                             @error="$event.target.src='/img/no-content-placeholder.png'">
                    </div>

                    <!-- Vairākas bildes — ritinātājs -->
                    <div v-else class="slider-wrap">
                        <!-- Galvenais attēls -->
                        <div class="slider-stage">
                            <Transition name="slide" mode="out-in">
                                <img :key="sliderIndex"
                                     :src="postImages[sliderIndex]"
                                     :alt="`Post image ${sliderIndex + 1}`"
                                     class="slider-img"
                                     @error="$event.target.src='/img/no-content-placeholder.png'">
                            </Transition>

                            <!-- Kreisā bulta -->
                            <button @click="sliderPrev" class="slider-btn slider-btn--prev" aria-label="Iepriekšējais">
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            <!-- Labā bulta -->
                            <button @click="sliderNext" class="slider-btn slider-btn--next" aria-label="Nākamais">
                                <i class="fas fa-chevron-right"></i>
                            </button>

                            <!-- Skaits -->
                            <div class="slider-count">
                                {{ sliderIndex + 1 }} / {{ postImages.length }}
                            </div>
                        </div>

                        <!-- Punktu navigācija -->
                        <div class="slider-dots">
                            <button
                                v-for="(_, i) in postImages"
                                :key="i"
                                @click="sliderGoTo(i)"
                                :class="['slider-dot', { 'slider-dot--active': i === sliderIndex }]"
                                :aria-label="`Attēls ${i + 1}`"
                            ></button>
                        </div>

                        <!-- Sīktēlu josla (maks 8) -->
                        <div v-if="postImages.length <= 8" class="slider-thumbs">
                            <button
                                v-for="(img, i) in postImages"
                                :key="i"
                                @click="sliderGoTo(i)"
                                :class="['thumb-btn', { 'thumb-btn--active': i === sliderIndex }]"
                            >
                                <img :src="img" :alt="`Thumb ${i + 1}`"
                                     @error="$event.target.src='/img/no-content-placeholder.png'">
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ════ ATSAUKSMES (tāpat kā ProductDetail) ════ -->
                <section class="reviews-sec">
                    <!-- Virsraksts + "Rakstīt" poga -->
                    <div class="reviews-head">
                        <h2 class="reviews-title">
                            {{ locale === 'lv' ? 'Atsauksmes' : 'Reviews' }}
                            <span v-if="reviewStats.count > 0" class="rev-badge">{{ reviewStats.count }}</span>
                        </h2>
                        <button v-if="authUser && !userReview" @click="showReviewForm = !showReviewForm" class="write-btn">
                            <i class="fas fa-pen"></i>
                            {{ locale === 'lv' ? 'Rakstīt atsauksmi' : 'Write a review' }}
                        </button>
                        <p v-else-if="!authUser" class="login-hint">
                            <Link href="/login">{{ locale === 'lv' ? 'Piesakies' : 'Log in' }}</Link>
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
                                    <div class="bar-fill" :style="{ width: reviewStats.count ? (reviewStats.dist[star] / reviewStats.count * 100) + '%' : '0%' }"></div>
                                </div>
                                <span class="bar-cnt">{{ reviewStats.dist[star] || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Atsauksmes forma -->
                    <Transition name="rev-form">
                        <div v-if="showReviewForm && authUser" class="rev-form">
                            <h3 class="rev-form-title">{{ locale === 'lv' ? 'Jūsu atsauksme' : 'Your Review' }}</h3>
                            <div class="star-pick">
                                <span class="star-pick-lbl">{{ locale === 'lv' ? 'Vērtējums:' : 'Rating:' }}</span>
                                <div class="star-pick-row">
                                    <button v-for="star in 5" :key="star" type="button"
                                            @click="reviewForm.rating = star"
                                            @mouseenter="hoverStar = star"
                                            @mouseleave="hoverStar = 0"
                                            class="star-btn">
                                        <i class="fas fa-star" :class="star <= (hoverStar || reviewForm.rating) ? 'star-on' : 'star-off'"></i>
                                    </button>
                                    <span v-if="reviewForm.rating" class="star-label">
                                        {{ ['','😕 Ļoti slikts','😐 Slikts','🙂 Vidējs','😊 Labs','🤩 Izcils'][reviewForm.rating] }}
                                    </span>
                                </div>
                            </div>
                            <div class="rev-field">
                                <label class="rev-field-lbl">{{ locale === 'lv' ? 'Komentārs (pēc izvēles):' : 'Comment (optional):' }}</label>
                                <textarea v-model="reviewForm.review_text"
                                          :placeholder="locale === 'lv' ? 'Pastāstiet par savu pieredzi...' : 'Share your experience...'"
                                          class="rev-ta" rows="4" maxlength="1000"></textarea>
                                <span class="char-cnt">{{ reviewForm.review_text.length }}/1000</span>
                            </div>
                            <div class="rev-actions">
                                <button @click="showReviewForm = false" class="btn-cancel-rev">
                                    {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                                </button>
                                <button @click="submitReview" :disabled="!reviewForm.rating || isSubmitting" class="btn-submit-rev">
                                    <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
                                    <i v-else class="fas fa-paper-plane"></i>
                                    {{ isSubmitting ? (locale === 'lv' ? 'Iesniedz...' : 'Submitting...') : (locale === 'lv' ? 'Iesniegt' : 'Submit') }}
                                </button>
                            </div>
                        </div>
                    </Transition>

                    <!-- Saraksts -->
                    <div v-if="isLoadingReviews" class="rev-loading"><i class="fas fa-spinner fa-spin"></i></div>
                    <div v-else-if="reviews.length === 0 && !justSubmittedReview" class="rev-empty">
                        <i class="fas fa-comment-slash"></i>
                        <p>{{ locale === 'lv' ? 'Vēl nav atsauksmju. Esiet pirmais!' : 'No reviews yet. Be the first!' }}</p>
                    </div>
                    <div v-else class="rev-list">
                        <div v-for="review in reviews" :key="review.id"
                             :class="['rev-card', { 'rev-card--pending': !review.is_approved }]">
                            <div v-if="!review.is_approved" class="pending-banner">
                                <i class="fas fa-clock"></i>
                                {{ locale === 'lv' ? 'Gaida apstiprinājumu (redzama tikai jums)' : 'Pending approval (visible only to you)' }}
                            </div>
                            <div class="rev-author">
                                <img :src="review.user?.profile_picture
                                    ? (review.user.profile_picture.startsWith('/') ? review.user.profile_picture : '/storage/' + review.user.profile_picture)
                                    : '/img/default-avatar.png'"
                                     :alt="review.user?.username" class="rev-avatar"
                                     @error="$event.target.src='/img/default-avatar.png'">
                                <div class="rev-meta">
                                    <span class="rev-name-row">
                                        <strong class="rev-name">{{ review.user?.username || 'Lietotājs' }}</strong>
                                        <span v-if="getRoleBadge(review.user)" :class="['role-badge', getRoleBadge(review.user).cls]">
                                            {{ getRoleBadge(review.user).label }}
                                        </span>
                                    </span>
                                    <time class="rev-date">{{ formatRevDate(review.created_at) }}</time>
                                </div>
                                <button v-if="authUser && (Number(authUser.id) === Number(review.user_id) || Number(authUser.id) === Number(review.user?.id))"
                                        @click="deleteReview(review.id)" class="rev-del"
                                        :title="locale === 'lv' ? 'Dzēst' : 'Delete'">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="rev-stars">
                                <i v-for="s in 5" :key="s" class="fas fa-star" :class="s <= review.rating ? 'star-on' : 'star-off'"></i>
                            </div>
                            <p v-if="getReviewText(review)" class="rev-text">{{ getReviewText(review) }}</p>
                        </div>
                    </div>
                </section>

                <!-- ════ KOMENTĀRI ════ -->
                <div class="comments-section">
                    <h2 class="section-title">
                        <i class="fas fa-comments"></i>
                        {{ t('content.comments') }}
                        <span class="comments-count">({{ comments.length }})</span>
                    </h2>

                    <form v-if="authUser" @submit.prevent="submitComment" class="comment-form">
                        <div class="comment-form-header">
                            <img :src="authUser.profile_picture ? `/storage/${authUser.profile_picture}` : '/img/default-avatar.png'"
                                 :alt="authUser.username" class="comment-avatar">
                            <textarea v-model="commentForm.comment_text"
                                      :placeholder="t('content.comment_placeholder')"
                                      class="comment-textarea" rows="3"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-submit" :disabled="commentForm.processing || !commentForm.comment_text.trim()">
                                <i v-if="!commentForm.processing" class="fas fa-paper-plane"></i>
                                <i v-else class="fas fa-spinner fa-spin"></i>
                                {{ commentForm.processing ? t('content.posting') : t('content.post_comment') }}
                            </button>
                        </div>
                    </form>
                    <div v-else class="login-prompt">
                        <i class="fas fa-lock"></i>
                        <p>{{ t('content.login_to_comment') }}</p>
                        <Link href="/login" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            {{ t('auth.login') }}
                        </Link>
                    </div>

                    <div v-if="isLoadingComments" class="loading-comments">
                        <i class="fas fa-spinner fa-spin"></i>
                        {{ t('content.loading_comments') }}
                    </div>
                    <div v-else-if="comments.length > 0" class="comments-list">
                        <div v-for="comment in comments" :key="comment.id" class="comment-item">
                            <img :src="comment.user?.profile_picture ? comment.user.profile_picture : '/img/default-avatar.png'"
                                 :alt="comment.user?.username" class="comment-avatar">
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author-wrap">
                                        <span class="comment-author">{{ comment.user?.username }}</span>
                                        <span v-if="getRoleBadge(comment.user)" :class="['role-badge', getRoleBadge(comment.user).cls]">
                                            {{ getRoleBadge(comment.user).label }}
                                        </span>
                                    </span>
                                    <span class="comment-date">
                                        {{ new Date(comment.created_at).toLocaleDateString(locale === 'lv' ? 'lv-LV' : 'en-US') }}
                                    </span>
                                </div>
                                <p class="comment-text">{{ comment.comment_text }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="no-comments">
                        <i class="fas fa-comment-slash"></i>
                        <p>{{ t('content.no_comments') }}</p>
                    </div>
                </div>

                <!-- Saistītais saturs -->
                <div v-if="relatedContent && relatedContent.length > 0" class="related-section">
                    <h2 class="section-title">
                        <i class="fas fa-layer-group"></i>
                        {{ locale === 'lv' ? 'Saistītais saturs' : 'Related content' }}
                    </h2>
                    <div class="related-grid">
                        <Link v-for="item in relatedContent" :key="item.id"
                              :href="`/content/${item.slug}`" class="related-card">
                            <div class="related-thumb">
                                <img :src="relatedThumb(item)" :alt="relatedTitle(item)"
                                     @error="$event.target.src='/img/no-content-placeholder.png'">
                            </div>
                            <div class="related-info">
                                <p class="related-title">{{ relatedTitle(item) }}</p>
                            </div>
                        </Link>
                    </div>
                </div>

            </div><!-- /content-container -->
        </div>
    </MainLayout>
</template>

<style scoped>
.content-show {
    min-height: 100vh;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

/* ── Toast ── */
.toast-notif {
    position: fixed;
    top: 2rem;
    right: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    font-weight: 600;
    z-index: 9999;
    color: white;
}
.toast-notif--success { background: linear-gradient(135deg, #10b981, #059669); }
.toast-notif--error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
.toast-notif i        { font-size: 1.25rem; }

.notification-enter-active, .notification-leave-active { transition: all 0.3s; }
.notification-enter-from { opacity: 0; transform: translateX(2rem); }
.notification-leave-to   { opacity: 0; transform: translateY(-2rem); }

/* ── Breadcrumbs ── */
.breadcrumbs { max-width: 1400px; margin: 0 auto; padding: 1.5rem 2rem; }
.breadcrumb-item {
    display: inline-flex; align-items: center; gap: 0.5rem;
    color: #6b7280; font-weight: 600; transition: color 0.2s;
}
.breadcrumb-item:hover { color: #dc2626; }

/* ── Video / Hero ── */
.video-container { max-width: 1400px; margin: 0 auto 2rem; padding: 0 2rem; }
.video-iframe    { width: 100%; aspect-ratio: 16/9; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); }

.content-hero    { max-width: 1400px; margin: 0 auto 2rem; padding: 0 2rem; }
.video-preview   { position: relative; cursor: pointer; overflow: hidden; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
.hero-image      { width: 100%; max-height: 600px; object-fit: cover; border-radius: 1rem; transition: transform 0.3s; display: block; }
.video-preview:hover .hero-image { transform: scale(1.05); }

.play-overlay {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
    padding: 2rem 3rem; background: rgba(220,38,38,0.95);
    border-radius: 1rem; color: white; font-weight: 700;
    font-size: 1.125rem; transition: all 0.3s;
}
.video-preview:hover .play-overlay { background: rgba(220,38,38,1); transform: translate(-50%,-50%) scale(1.1); }

/* ── Content Container ── */
.content-container { max-width: 1000px; margin: 0 auto; padding: 0 2rem 4rem; }

.content-header-section {
    background: white; padding: 2rem; border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;
}
.content-main-title { font-size: 2rem; font-weight: 800; color: #111827; margin: 0 0 1rem 0; }

.content-meta-bar { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; }
.meta-badge {
    display: inline-flex; align-items: center; gap: 0.375rem;
    padding: 0.375rem 0.875rem; border-radius: 9999px;
    font-weight: 600; font-size: 0.875rem;
}
.meta-badge-video        { background: linear-gradient(135deg,#dc2626,#b91c1c); color:white; }
.meta-badge-blog         { background: linear-gradient(135deg,#3b82f6,#2563eb); color:white; }
.meta-badge-news,
.meta-badge-post         { background: linear-gradient(135deg,#059669,#047857); color:white; }
.meta-badge-announcement { background: linear-gradient(135deg,#f59e0b,#d97706); color:white; }
.meta-badge-category     { background: #f3f4f6; color: #374151; }
.meta-text { display: inline-flex; align-items: center; gap: 0.375rem; color: #6b7280; font-size: 0.875rem; }
.meta-text i { color: #dc2626; }

.header-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6; }
.action-btn {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.75rem 1.25rem; background: #f3f4f6;
    border: 2px solid transparent; border-radius: 0.75rem;
    color: #374151; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.action-btn:hover      { background: #e5e7eb; transform: translateY(-2px); }
.action-btn-liked      { background: #fee2e2; border-color: #dc2626; color: #dc2626; }
.action-btn-primary    { background: linear-gradient(135deg,#dc2626,#b91c1c); color: white; border: none; }
.action-btn-primary:hover { box-shadow: 0 4px 12px rgba(220,38,38,0.3); }

/* ── Description & Body ── */
.content-description-section {
    background: white; padding: 2rem; border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;
}
.section-subtitle { display: flex; align-items: center; gap: 0.75rem; font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0 0 1rem 0; }
.section-subtitle i { color: #dc2626; }
.content-description-text { font-size: 1.125rem; line-height: 1.75; color: #374151; margin: 0; }

.content-body-section { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.content-body { font-size: 1rem; line-height: 1.75; color: #374151; }

/* ── ATSAUKSMES (identiskas ProductDetail stilam) ── */
.reviews-sec { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.reviews-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; }
.reviews-title { font-size: 1.5rem; font-weight: 800; color: #111827; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.rev-badge { background: #dc2626; color: white; font-size: 0.85rem; font-weight: 700; padding: 0.15rem 0.6rem; border-radius: 9999px; }
.write-btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: linear-gradient(135deg,#dc2626,#b91c1c); border: none; border-radius: 0.625rem; color: white; font-weight: 700; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; }
.write-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.login-hint { font-size: 0.875rem; color: #6b7280; margin: 0; }
.login-hint a { color: #dc2626; font-weight: 600; }
.pending-hint { font-size: 0.875rem; color: #92400e; background: #fef3c7; padding: 0.5rem 0.875rem; border-radius: 0.5rem; margin: 0; display: flex; align-items: center; gap: 0.5rem; }

/* Stats */
.stats-box { display: flex; gap: 2rem; flex-wrap: wrap; padding: 1.25rem; background: #f9fafb; border-radius: 0.75rem; margin-bottom: 1.5rem; }
.stats-avg { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; min-width: 100px; }
.avg-num { font-size: 2.5rem; font-weight: 800; color: #111827; line-height: 1; }
.avg-stars { display: flex; gap: 2px; }
.avg-cnt { font-size: 0.8rem; color: #6b7280; }
.stats-bars { flex: 1; display: flex; flex-direction: column; gap: 0.4rem; justify-content: center; }
.bar-row { display: flex; align-items: center; gap: 0.5rem; }
.bar-lbl { font-size: 0.75rem; color: #6b7280; width: 2rem; text-align: right; flex-shrink: 0; }
.bar-track { flex: 1; height: 8px; background: #e5e7eb; border-radius: 9999px; overflow: hidden; }
.bar-fill { height: 100%; background: linear-gradient(90deg,#fbbf24,#f59e0b); border-radius: 9999px; transition: width 0.4s ease; }
.bar-cnt { font-size: 0.75rem; color: #6b7280; width: 1.25rem; }

/* Review form */
.rev-form { background: #f9fafb; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem; }
.rev-form-title { font-size: 1rem; font-weight: 700; color: #111827; margin: 0 0 1rem 0; }
.star-pick { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.star-pick-lbl { font-size: 0.875rem; font-weight: 600; color: #374151; }
.star-pick-row { display: flex; align-items: center; gap: 0.375rem; }
.star-btn { background: none; border: none; cursor: pointer; padding: 0.25rem; font-size: 1.375rem; transition: transform 0.15s; }
.star-btn:hover { transform: scale(1.2); }
.star-label { font-size: 0.875rem; color: #6b7280; margin-left: 0.5rem; }
.rev-field { display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 1rem; }
.rev-field-lbl { font-size: 0.8rem; font-weight: 600; color: #6b7280; }
.rev-ta { width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.9rem; font-family: inherit; resize: vertical; transition: border-color 0.2s; box-sizing: border-box; }
.rev-ta:focus { outline: none; border-color: #dc2626; }
.char-cnt { font-size: 0.75rem; color: #9ca3af; text-align: right; }
.rev-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
.btn-cancel-rev { padding: 0.6rem 1.25rem; background: #e5e7eb; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; cursor: pointer; color: #374151; transition: all 0.2s; }
.btn-cancel-rev:hover { background: #d1d5db; }
.btn-submit-rev { display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: linear-gradient(135deg,#dc2626,#b91c1c); border: none; border-radius: 0.5rem; color: white; font-weight: 700; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; }
.btn-submit-rev:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-submit-rev:not(:disabled):hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.rev-form-enter-active, .rev-form-leave-active { transition: all 0.3s ease; }
.rev-form-enter-from, .rev-form-leave-to { opacity: 0; transform: translateY(-10px); }

/* Review list */
.rev-loading { text-align: center; padding: 2rem; color: #9ca3af; }
.rev-empty   { text-align: center; padding: 2.5rem 2rem; }
.rev-empty i { font-size: 2.5rem; color: #d1d5db; display: block; margin-bottom: 0.75rem; }
.rev-empty p { color: #9ca3af; margin: 0; }
.rev-list    { display: flex; flex-direction: column; gap: 1rem; }
.rev-card    { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.25rem; }
.rev-card--pending { border-color: #fcd34d; background: #fffbeb; }
.pending-banner { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #92400e; margin-bottom: 0.75rem; }
.rev-author  { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.rev-avatar  { width: 2.5rem; height: 2.5rem; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.rev-meta    { flex: 1; }
.rev-name    { display: block; font-weight: 700; font-size: 0.9rem; color: #111827; }
.rev-date    { font-size: 0.8rem; color: #9ca3af; }
.rev-del     { margin-left: auto; background: none; border: none; cursor: pointer; color: #ef4444; padding: 0.25rem 0.5rem; opacity: 0.6; transition: opacity 0.2s; }
.rev-del:hover { opacity: 1; }
.rev-stars   { display: flex; gap: 3px; margin-bottom: 0.5rem; }
.rev-text    { font-size: 0.9rem; color: #374151; margin: 0; line-height: 1.6; }
.star-on  { color: #fbbf24; }
.star-off { color: #d1d5db; }

/* ── Komentāri ── */
.section-title { display: flex; align-items: center; gap: 0.75rem; font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0 0 1.5rem 0; }
.section-title i { color: #dc2626; }
.comments-count { font-size: 1.125rem; color: #6b7280; font-weight: 500; }

.comments-section { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.comment-form { margin-bottom: 2rem; }
.comment-form-header { display: flex; gap: 1rem; margin-bottom: 1rem; }
.comment-avatar  { width: 3rem; height: 3rem; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.comment-textarea { flex: 1; padding: 0.875rem 1rem; border: 2px solid #e5e7eb; border-radius: 0.75rem; font-size: 1rem; font-family: inherit; resize: vertical; transition: all 0.2s; }
.comment-textarea:focus { outline: none; border-color: #dc2626; }
.form-actions { display: flex; gap: 0.75rem; }
.btn-submit { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg,#dc2626,#b91c1c); border: none; border-radius: 0.75rem; color: white; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
.login-prompt { text-align: center; padding: 2rem; background: #f9fafb; border-radius: 0.75rem; margin-bottom: 2rem; }
.login-prompt i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.login-prompt p { color: #6b7280; margin: 0 0 1.5rem 0; }
.btn-login { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg,#dc2626,#b91c1c); border-radius: 0.75rem; color: white; font-weight: 600; transition: all 0.2s; }
.btn-login:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.loading-comments { text-align: center; padding: 2rem; color: #6b7280; }
.comments-list { display: flex; flex-direction: column; gap: 1.5rem; }
.comment-item { display: flex; gap: 1rem; padding: 1.25rem; background: #f9fafb; border-radius: 0.75rem; }
.comment-content { flex: 1; }
.comment-header  { display: flex; justify-content: space-between; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.25rem; }
.comment-author  { font-weight: 600; color: #111827; }
.comment-date    { font-size: 0.875rem; color: #9ca3af; }
.comment-text    { color: #374151; line-height: 1.6; margin: 0; }
.no-comments     { text-align: center; padding: 3rem 2rem; }
.no-comments i   { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.no-comments p   { color: #6b7280; margin: 0; }

/* ── Saistītais saturs ── */
.related-section { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; }
.related-card { display: flex; flex-direction: column; border-radius: 0.5rem; overflow: hidden; border: 1px solid #e5e7eb; transition: all 0.2s; text-decoration: none; }
.related-card:hover { transform: translateY(-4px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
.related-thumb { width: 100%; aspect-ratio: 16/9; overflow: hidden; }
.related-thumb img { width: 100%; height: 100%; object-fit: cover; }
.related-info { padding: 0.625rem; }
.related-title { font-size: 0.8rem; font-weight: 600; color: #374151; margin: 0; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }

/* ── POST ATTĒLU RITINĀTĀJS ── */
.post-slider { background: white; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; overflow: hidden; }

.slider-single img { width: 100%; max-height: 600px; object-fit: contain; display: block; background: #000; }

.slider-wrap { display: flex; flex-direction: column; }

.slider-stage {
    position: relative;
    overflow: hidden;
    background: #000;
    aspect-ratio: 4/3;
    max-height: 560px;
}

.slider-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

/* Slide transition */
.slide-enter-active, .slide-leave-active { transition: opacity 0.25s ease; }
.slide-enter-from, .slide-leave-to       { opacity: 0; }

/* Bultas */
.slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 3rem;
    height: 3rem;
    background: rgba(0,0,0,0.55);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    color: white;
    font-size: 1.125rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    z-index: 10;
    backdrop-filter: blur(4px);
}
.slider-btn:hover          { background: rgba(220,38,38,0.85); border-color: transparent; transform: translateY(-50%) scale(1.1); }
.slider-btn--prev          { left: 0.875rem; }
.slider-btn--next          { right: 0.875rem; }

/* Skaits */
.slider-count {
    position: absolute;
    bottom: 0.75rem;
    right: 0.875rem;
    padding: 0.25rem 0.75rem;
    background: rgba(0,0,0,0.6);
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 9999px;
    backdrop-filter: blur(4px);
}

/* Punkti */
.slider-dots  { display: flex; justify-content: center; gap: 0.5rem; padding: 0.875rem 1rem 0.5rem; }
.slider-dot   { width: 8px; height: 8px; border-radius: 50%; background: #d1d5db; border: none; cursor: pointer; transition: all 0.2s; padding: 0; }
.slider-dot--active { background: #dc2626; transform: scale(1.3); }

/* Sīktēlu josla */
.slider-thumbs { display: flex; gap: 0.5rem; padding: 0.5rem 1rem 1rem; overflow-x: auto; scrollbar-width: thin; }
.thumb-btn {
    flex-shrink: 0;
    width: 70px;
    height: 52px;
    border-radius: 0.375rem;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
    padding: 0;
    transition: border-color 0.2s;
    background: #f3f4f6;
}
.thumb-btn img       { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb-btn--active   { border-color: #dc2626; }
.thumb-btn:hover:not(.thumb-btn--active) { border-color: #9ca3af; }

/* ── Lomas badges ── */
.rev-name-row { display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap; }
.comment-author-wrap { display: inline-flex; align-items: center; gap: 0.375rem; flex-wrap: wrap; }
.role-badge {
    display: inline-flex; align-items: center;
    padding: 0.1rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.role-admin   { background: rgba(220,38,38,0.12); color: #b91c1c; border: 1px solid rgba(220,38,38,0.25); }
.role-courier { background: rgba(37,99,235,0.12);  color: #1d4ed8; border: 1px solid rgba(37,99,235,0.25); }

/* ── Responsive ── */
@media (max-width: 768px) {
    .breadcrumbs, .video-container, .content-hero, .content-container { padding-left: 1rem; padding-right: 1rem; }
    .content-main-title { font-size: 1.5rem; }
    .header-actions { flex-wrap: wrap; }
    .action-btn { flex: 1; min-width: calc(50% - 0.375rem); }
    .comment-form-header { flex-direction: column; }
    .toast-notif { top: 1rem; right: 1rem; left: 1rem; }
    .stats-box { flex-direction: column; }
    .related-grid { grid-template-columns: repeat(2, 1fr); }
}

.content-show {
min-height: 100vh;
background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

/* Success Notification */
.success-notification {
position: fixed;
top: 2rem;
right: 2rem;
display: flex;
align-items: center;
gap: 0.75rem;
padding: 1rem 1.5rem;
background: linear-gradient(135deg, #10b981 0%, #059669 100%);
color: white;
border-radius: 0.75rem;
box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
font-weight: 600;
z-index: 9999;
}

.success-notification i {
font-size: 1.25rem;
}

.notification-enter-active,
.notification-leave-active {
transition: all 0.3s;
}

.notification-enter-from {
opacity: 0;
transform: translateX(2rem);
}

.notification-leave-to {
opacity: 0;
transform: translateY(-2rem);
}

/* Breadcrumbs */
.breadcrumbs {
max-width: 1400px;
margin: 0 auto;
padding: 1.5rem 2rem;
}

.breadcrumb-item {
display: inline-flex;
align-items: center;
gap: 0.5rem;
color: #6b7280;
font-weight: 600;
transition: color 0.2s;
}

.breadcrumb-item:hover {
color: #dc2626;
}

/* Video Container */
.video-container {
max-width: 1400px;
margin: 0 auto 2rem;
padding: 0 2rem;
}

.video-iframe {
width: 100%;
aspect-ratio: 16 / 9;
border-radius: 1rem;
box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

/* Hero Image */
.content-hero {
max-width: 1400px;
margin: 0 auto 2rem;
padding: 0 2rem;
}

.video-preview {
position: relative;
cursor: pointer;
overflow: hidden;
border-radius: 1rem;
box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.hero-image {
width: 100%;
max-height: 600px;
object-fit: cover;
border-radius: 1rem;
transition: transform 0.3s;
}

.video-preview:hover .hero-image {
transform: scale(1.05);
}

.play-overlay {
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
display: flex;
flex-direction: column;
align-items: center;
gap: 0.75rem;
padding: 2rem 3rem;
background: rgba(220, 38, 38, 0.95);
border-radius: 1rem;
color: white;
font-weight: 700;
font-size: 1.125rem;
transition: all 0.3s;
}

.video-preview:hover .play-overlay {
background: rgba(220, 38, 38, 1);
transform: translate(-50%, -50%) scale(1.1);
}

.play-overlay i {
font-size: 3rem;
}

/* Content Container */
.content-container {
max-width: 1000px;
margin: 0 auto;
padding: 0 2rem 4rem;
}

/* Header Section */
.content-header-section {
background: white;
padding: 2rem;
border-radius: 1rem;
box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
margin-bottom: 2rem;
}

.content-main-title {
font-size: 2rem;
font-weight: 800;
color: #111827;
margin: 0 0 1rem 0;
}

.content-meta-bar {
display: flex;
flex-wrap: wrap;
gap: 0.75rem;
margin-bottom: 1.5rem;
}

.meta-badge {
display: inline-flex;
align-items: center;
gap: 0.375rem;
padding: 0.375rem 0.875rem;
border-radius: 9999px;
font-weight: 600;
font-size: 0.875rem;
}

.meta-badge-video {
background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
color: white;
}

.meta-badge-blog {
background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
color: white;
}

.meta-badge-category {
background: #f3f4f6;
color: #374151;
}

.meta-text {
display: inline-flex;
align-items: center;
gap: 0.375rem;
color: #6b7280;
font-size: 0.875rem;
}

.meta-text i {
color: #dc2626;
}

.header-actions {
display: flex;
gap: 0.75rem;
margin-top: 1.5rem;
padding-top: 1.5rem;
border-top: 1px solid #f3f4f6;
}

.action-btn {
display: flex;
align-items: center;
gap: 0.5rem;
padding: 0.75rem 1.25rem;
background: #f3f4f6;
border: 2px solid transparent;
border-radius: 0.75rem;
color: #374151;
font-weight: 600;
cursor: pointer;
transition: all 0.2s;
}

.action-btn:hover {
background: #e5e7eb;
transform: translateY(-2px);
}

.action-btn-liked {
background: #fee2e2;
border-color: #dc2626;
color: #dc2626;
}

.action-btn-primary {
background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
color: white;
}

.action-btn-primary:hover {
box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Description */
.content-description-section {
background: white;
padding: 2rem;
border-radius: 1rem;
box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
margin-bottom: 2rem;
}

.section-subtitle {
display: flex;
align-items: center;
gap: 0.75rem;
font-size: 1.25rem;
font-weight: 700;
color: #111827;
margin: 0 0 1rem 0;
}

.section-subtitle i {
color: #dc2626;
}

.content-description-text {
font-size: 1.125rem;
line-height: 1.75;
color: #374151;
margin: 0;
}

/* Body */
.content-body-section {
background: white;
padding: 2rem;
border-radius: 1rem;
box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
margin-bottom: 2rem;
}

.content-body {
font-size: 1rem;
line-height: 1.75;
color: #374151;
}

/* Rating Section */
.rating-section {
background: white;
padding: 2rem;
border-radius: 1rem;
box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
margin-bottom: 2rem;
}

.section-title {
display: flex;
align-items: center;
gap: 0.75rem;
font-size: 1.5rem;
font-weight: 700;
color: #111827;
margin: 0 0 1.5rem 0;
}

.section-title i {
color: #dc2626;
}

.comments-count {
font-size: 1.125rem;
color: #6b7280;
font-weight: 500;
}

.rating-stars {
display: flex;
gap: 0.5rem;
margin-bottom: 1.5rem;
}

.star-btn {
width: 3rem;
height: 3rem;
background: #f3f4f6;
border: none;
border-radius: 0.5rem;
color: #d1d5db;
font-size: 1.5rem;
cursor: pointer;
transition: all 0.2s;
}

.star-btn:hover {
transform: scale(1.1);
}

.star-filled {
background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
color: white;
}

/* Forms */
.review-prompt {
text-align: center;
}

.btn-write-review {
display: inline-flex;
align-items: center;
gap: 0.5rem;
padding: 0.875rem 1.75rem;
background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
border: none;
border-radius: 0.75rem;
color: white;
font-weight: 600;
cursor: pointer;
transition: all 0.2s;
}

.btn-write-review:hover {
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.review-form,
.comment-form {
margin-top: 1.5rem;
}

.comment-form-header {
display: flex;
gap: 1rem;
margin-bottom: 1rem;
}

.comment-avatar {
width: 3rem;
height: 3rem;
border-radius: 50%;
object-fit: cover;
flex-shrink: 0;
}

.review-textarea,
.comment-textarea {
width: 100%;
padding: 1rem;
border: 2px solid #e5e7eb;
border-radius: 0.75rem;
font-size: 1rem;
font-family: inherit;
resize: vertical;
transition: all 0.2s;
}

.review-textarea:focus,
.comment-textarea:focus {
outline: none;
border-color: #dc2626;
}

.form-actions {
display: flex;
gap: 0.75rem;
margin-top: 1rem;
}

.btn-submit {
display: flex;
align-items: center;
gap: 0.5rem;
padding: 0.75rem 1.5rem;
background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
border: none;
border-radius: 0.75rem;
color: white;
font-weight: 600;
cursor: pointer;
transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-submit:disabled {
opacity: 0.5;
cursor: not-allowed;
}

.btn-cancel {
padding: 0.75rem 1.5rem;
background: #f3f4f6;
border: none;
border-radius: 0.75rem;
color: #374151;
font-weight: 600;
cursor: pointer;
transition: all 0.2s;
}

.btn-cancel:hover {
background: #e5e7eb;
}

/* Comments Section */
.comments-section {
background: white;
padding: 2rem;
border-radius: 1rem;
box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.login-prompt {
text-align: center;
padding: 2rem;
background: #f9fafb;
border-radius: 0.75rem;
margin-bottom: 2rem;
}

.login-prompt i {
font-size: 3rem;
color: #d1d5db;
margin-bottom: 1rem;
}

.login-prompt p {
color: #6b7280;
margin: 0 0 1.5rem 0;
}

.btn-login {
display: inline-flex;
align-items: center;
gap: 0.5rem;
padding: 0.75rem 1.5rem;
background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
border-radius: 0.75rem;
color: white;
font-weight: 600;
transition: all 0.2s;
}

.btn-login:hover {
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.loading-comments {
text-align: center;
padding: 2rem;
color: #6b7280;
}

.comments-list {
display: flex;
flex-direction: column;
gap: 1.5rem;
}

.comment-item {
display: flex;
gap: 1rem;
padding: 1.5rem;
background: #f9fafb;
border-radius: 0.75rem;
}

.comment-content {
flex: 1;
}

.comment-header {
display: flex;
justify-content: space-between;
margin-bottom: 0.5rem;
}

.comment-author {
font-weight: 600;
color: #111827;
}

.comment-date {
font-size: 0.875rem;
color: #9ca3af;
}

.comment-text {
color: #374151;
line-height: 1.6;
margin: 0;
}

.no-comments {
text-align: center;
padding: 3rem 2rem;
}

.no-comments i {
font-size: 3rem;
color: #d1d5db;
margin-bottom: 1rem;
}

.no-comments p {
color: #6b7280;
margin: 0;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
transition: all 0.3s;
}

.fade-enter-from,
.fade-leave-to {
opacity: 0;
transform: translateY(-1rem);
}

/* Responsive */
@media (max-width: 768px) {
.breadcrumbs,
.video-container,
.content-hero,
.content-container {
padding-left: 1rem;
padding-right: 1rem;
}

.content-main-title {
font-size: 1.5rem;
}

.header-actions {
flex-wrap: wrap;
}

.action-btn {
flex: 1;
min-width: calc(50% - 0.375rem);
}

.comment-form-header {
flex-direction: column;
}

.success-notification {
top: 1rem;
right: 1rem;
left: 1rem;
}
}
</style>
