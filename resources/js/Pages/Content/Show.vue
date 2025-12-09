<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t, locale } = useI18n();

const props = defineProps({
    content: Object,
    auth: Object,
});

// State
const userRating = ref(0);
const hoverRating = ref(0);
const userLiked = ref(false);
const likeCount = ref(props.content.like_count || 0);
const comments = ref([]);
const isLoadingComments = ref(false);

// Success messages
const showSuccessMessage = ref(false);
const successMessage = ref('');

// Comment form
const commentForm = useForm({
    content_id: props.content.id,
    comment_text: '',
});

// Review form
const reviewForm = useForm({
    content_id: props.content.id,
    rating: 0,
    review_text: '',
});

const showReviewForm = ref(false);

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

const embedUrl = computed(() => {
    if (!isVideo.value || !props.content.video_url) return null;

    const url = props.content.video_url;
    const platform = props.content.video_platform;

    // YouTube
    if (platform === 'YouTube') {
        const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (match) {
            return `https://www.youtube.com/embed/${match[1]}`;
        }
    }

    // Instagram
    if (platform === 'Instagram') {
        // Instagram embeds need special handling
        return null; // Will show thumbnail instead
    }

    // TikTok
    if (platform === 'TikTok') {
        // TikTok embeds need special handling
        return null; // Will show thumbnail instead
    }

    // X/Twitter
    if (platform === 'X' || platform === 'Twitter') {
        // Twitter embeds need special handling
        return null; // Will show thumbnail instead
    }

    // Facebook
    if (platform === 'Facebook') {
        // Facebook embeds need special handling
        return null; // Will show thumbnail instead
    }

    return null;
});

const getThumbnail = computed(() => {
    if (props.content.thumbnail && !props.content.thumbnail.includes('img.thumbnails')) {
        return props.content.thumbnail;
    }

    if (isVideo.value && props.content.video_url && props.content.video_platform === 'YouTube') {
        const match = props.content.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (match) {
            return `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`;
        }
    }

    return '/img/default-content.jpg';
});

const formatDate = computed(() => {
    return new Date(props.content.published_at).toLocaleDateString(
        locale.value === 'lv' ? 'lv-LV' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
});

// Methods
const showSuccess = (message) => {
    successMessage.value = message;
    showSuccessMessage.value = true;
    setTimeout(() => {
        showSuccessMessage.value = false;
    }, 3000);
};

const toggleLike = async () => {
    if (!props.auth.user) {
        alert(t('content.login_required'));
        return;
    }

    const previousLiked = userLiked.value;
    const previousCount = likeCount.value;

    userLiked.value = !userLiked.value;
    likeCount.value += userLiked.value ? 1 : -1;

    try {
        // ✅ FIX: Use web route instead of API route
        await axios.post(`/content/${props.content.id}/like`, {}, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            }
        });

        // ✅ Show success message
        showSuccess(userLiked.value ? t('content.liked_success') : t('content.unliked_success'));
    } catch (error) {
        // Revert on error
        userLiked.value = previousLiked;
        likeCount.value = previousCount;
        console.error('Error toggling like:', error);

        if (error.response?.status === 401) {
            alert(t('content.login_required'));
        } else {
            alert(t('content.error_occurred'));
        }
    }
};

const setRating = (rating) => {
    if (!props.auth.user) {
        alert(t('content.login_required'));
        return;
    }
    userRating.value = rating;
    reviewForm.rating = rating;
};

const submitComment = () => {
    if (!props.auth.user) {
        alert(t('content.login_required'));
        return;
    }

    commentForm.post('/comments', {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset('comment_text');
            // ✅ Show success message
            showSuccess(t('content.comment_submitted'));
            loadComments();
        },
        onError: (errors) => {
            console.error('Comment error:', errors);
        }
    });
};

const submitReview = () => {
    if (!props.auth.user) {
        alert(t('content.login_required'));
        return;
    }

    if (reviewForm.rating === 0) {
        alert(t('content.select_rating'));
        return;
    }

    reviewForm.post('/reviews', {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.reset();
            showReviewForm.value = false;
            userRating.value = reviewForm.rating;
            // ✅ Show success message
            showSuccess(t('content.review_submitted'));
        },
        onError: (errors) => {
            console.error('Review error:', errors);
        }
    });
};

const loadComments = async () => {
    isLoadingComments.value = true;
    try {
        const response = await axios.get(`/api/v1/comments/content/${props.content.id}`);
        comments.value = response.data || [];
    } catch (error) {
        console.error('Error loading comments:', error);
        comments.value = [];
    } finally {
        isLoadingComments.value = false;
    }
};

const openSource = () => {
    if (props.content.video_url) {
        window.open(props.content.video_url, '_blank');
    }
};

const shareContent = () => {
    if (navigator.share) {
        navigator.share({
            title: getTitle.value,
            text: getDescription.value,
            url: window.location.href,
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert(t('content.link_copied'));
    }
};

onMounted(() => {
    loadComments();
});
</script>

<template>
    <Head :title="getTitle" />

    <MainLayout>
        <div class="content-show">
            <!-- Success Message Notification -->
            <Transition name="notification">
                <div v-if="showSuccessMessage" class="success-notification">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ successMessage }}</span>
                </div>
            </Transition>

            <!-- Breadcrumbs -->
            <div class="breadcrumbs">
                <Link href="/content" class="breadcrumb-item">
                    <i class="fas fa-arrow-left"></i>
                    {{ t('content.back_to_list') }}
                </Link>
            </div>

            <!-- Video Player (if video) -->
            <div v-if="isVideo && embedUrl" class="video-container">
                <iframe
                    :src="embedUrl"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="video-iframe"
                ></iframe>
            </div>

            <!-- Thumbnail with Platform Badge (if no embed) -->
            <div v-else-if="isVideo" class="content-hero">
                <div class="video-preview" @click="openSource">
                    <img :src="getThumbnail" :alt="getTitle" class="hero-image">
                    <div class="play-overlay">
                        <i class="fab" :class="`fa-${content.video_platform?.toLowerCase()}`"></i>
                        <span>{{ t('content.watch_on') }} {{ content.video_platform }}</span>
                    </div>
                </div>
            </div>

            <!-- Thumbnail (if blog) -->
            <div v-else class="content-hero">
                <img :src="getThumbnail" :alt="getTitle" class="hero-image">
            </div>

            <!-- Content Header -->
            <div class="content-container">
                <div class="content-header-section">
                    <!-- Title & Meta -->
                    <div class="header-left">
                        <h1 class="content-main-title">{{ getTitle }}</h1>

                        <div class="content-meta-bar">
                            <span class="meta-badge" :class="`meta-badge-${content.type}`">
                                <i :class="isVideo ? 'fas fa-play-circle' : 'fas fa-newspaper'"></i>
                                {{ isVideo ? t('content.video') : t('content.blog') }}
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
                        <!-- Like Button -->
                        <button
                            @click="toggleLike"
                            class="action-btn"
                            :class="{ 'action-btn-liked': userLiked }"
                        >
                            <i class="fas fa-heart"></i>
                            <span>{{ likeCount }}</span>
                        </button>

                        <!-- Share Button -->
                        <button @click="shareContent" class="action-btn">
                            <i class="fas fa-share-alt"></i>
                            <span>{{ t('content.share') }}</span>
                        </button>

                        <!-- Watch on Platform (if video) -->
                        <button
                            v-if="isVideo && content.video_url"
                            @click="openSource"
                            class="action-btn action-btn-primary"
                        >
                            <i class="fab" :class="`fa-${content.video_platform?.toLowerCase()}`"></i>
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

                <!-- Body Content (if blog) -->
                <div v-if="getBody" class="content-body-section">
                    <div class="content-body" v-html="getBody"></div>
                </div>

                <!-- Rating Section -->
                <div class="rating-section">
                    <h2 class="section-title">
                        <i class="fas fa-star"></i>
                        {{ t('content.rate_this') }}
                    </h2>

                    <div class="rating-stars">
                        <button
                            v-for="star in 5"
                            :key="star"
                            @click="setRating(star)"
                            @mouseenter="hoverRating = star"
                            @mouseleave="hoverRating = 0"
                            class="star-btn"
                            :class="{
                                'star-filled': star <= (hoverRating || userRating)
                            }"
                        >
                            <i class="fas fa-star"></i>
                        </button>
                    </div>

                    <!-- Review Form -->
                    <Transition name="fade">
                        <div v-if="userRating > 0 && !showReviewForm" class="review-prompt">
                            <button @click="showReviewForm = true" class="btn-write-review">
                                <i class="fas fa-pen"></i>
                                {{ t('content.write_review') }}
                            </button>
                        </div>
                    </Transition>

                    <Transition name="fade">
                        <form v-if="showReviewForm" @submit.prevent="submitReview" class="review-form">
                            <textarea
                                v-model="reviewForm.review_text"
                                :placeholder="t('content.review_placeholder')"
                                class="review-textarea"
                                rows="4"
                            ></textarea>

                            <div class="form-actions">
                                <button type="submit" class="btn-submit" :disabled="reviewForm.processing">
                                    <i v-if="!reviewForm.processing" class="fas fa-paper-plane"></i>
                                    <i v-else class="fas fa-spinner fa-spin"></i>
                                    {{ reviewForm.processing ? t('content.submitting') : t('content.submit_review') }}
                                </button>
                                <button type="button" @click="showReviewForm = false" class="btn-cancel">
                                    {{ t('content.cancel') }}
                                </button>
                            </div>
                        </form>
                    </Transition>
                </div>

                <!-- Comments Section -->
                <div class="comments-section">
                    <h2 class="section-title">
                        <i class="fas fa-comments"></i>
                        {{ t('content.comments') }}
                        <span class="comments-count">({{ comments.length }})</span>
                    </h2>

                    <!-- Comment Form -->
                    <form v-if="auth.user" @submit.prevent="submitComment" class="comment-form">
                        <div class="comment-form-header">
                            <img
                                :src="auth.user.profile_picture ? `/storage/${auth.user.profile_picture}` : '/img/default-avatar.png'"
                                :alt="auth.user.username"
                                class="comment-avatar"
                            >
                            <textarea
                                v-model="commentForm.comment_text"
                                :placeholder="t('content.comment_placeholder')"
                                class="comment-textarea"
                                rows="3"
                            ></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit" :disabled="commentForm.processing || !commentForm.comment_text.trim()">
                                <i v-if="!commentForm.processing" class="fas fa-paper-plane"></i>
                                <i v-else class="fas fa-spinner fa-spin"></i>
                                {{ commentForm.processing ? t('content.posting') : t('content.post_comment') }}
                            </button>
                        </div>
                    </form>

                    <!-- Login Prompt -->
                    <div v-else class="login-prompt">
                        <i class="fas fa-lock"></i>
                        <p>{{ t('content.login_to_comment') }}</p>
                        <Link href="/login" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            {{ t('auth.login') }}
                        </Link>
                    </div>

                    <!-- Comments List -->
                    <div v-if="isLoadingComments" class="loading-comments">
                        <i class="fas fa-spinner fa-spin"></i>
                        {{ t('content.loading_comments') }}
                    </div>

                    <div v-else-if="comments.length > 0" class="comments-list">
                        <div v-for="comment in comments" :key="comment.id" class="comment-item">
                            <img
                                :src="comment.user?.profile_picture ? `/storage/${comment.user.profile_picture}` : '/img/default-avatar.png'"
                                :alt="comment.user?.username"
                                class="comment-avatar"
                            >
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author">{{ comment.user?.username }}</span>
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
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
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
