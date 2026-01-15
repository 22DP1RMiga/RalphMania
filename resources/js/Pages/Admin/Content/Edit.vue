<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n({ useScope: 'global' });

const props = defineProps({
    content: {
        type: Object,
        required: true,
    },
});

// Format datetime for input
function formatDateTimeLocal(dateString) {
    if (!dateString) return null;
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16);
}

// Form data - pre-populated with existing content
const form = useForm({
    type: props.content.type || 'blog',
    title_lv: props.content.title_lv || '',
    title_en: props.content.title_en || '',
    slug: props.content.slug || '',
    category: props.content.category || '',
    description_lv: props.content.description_lv || '',
    description_en: props.content.description_en || '',
    content_body_lv: props.content.content_body_lv || '',
    content_body_en: props.content.content_body_en || '',
    video_url: props.content.video_url || '',
    video_platform: props.content.video_platform || 'youtube',
    duration: props.content.duration || null,
    thumbnail: null,
    featured_image: null,
    blog_images: [],
    is_published: props.content.is_published || false,
    is_featured: props.content.is_featured || false,
    published_at: props.content.published_at ? formatDateTimeLocal(props.content.published_at) : null,
    _existing_thumbnail: props.content.thumbnail,
    _existing_featured_image: props.content.featured_image,
    _existing_blog_images: props.content.blog_images || [],
    _remove_thumbnail: false,
    _remove_featured_image: false,
    _remove_blog_images: [],
});

// File handling
const thumbnailPreview = ref(null);
const featuredImagePreview = ref(null);
const blogImagesPreview = ref([]);
const isDraggingThumbnail = ref(false);
const isDraggingFeatured = ref(false);
const isDraggingBlogImages = ref(false);

// Existing images computed
const existingThumbnail = computed(() => {
    if (form._remove_thumbnail || !props.content.thumbnail) return null;
    const img = props.content.thumbnail;
    if (img.startsWith('http') || img.startsWith('/')) return img;
    return `/img/thumbnails/${img}`;
});

const existingFeaturedImage = computed(() => {
    if (form._remove_featured_image || !props.content.featured_image) return null;
    const img = props.content.featured_image;
    if (img.startsWith('http') || img.startsWith('/')) return img;
    return `/img/Blogs/${img}`;
});

// Content types
const contentTypes = computed(() => [
    { value: 'video', labelKey: 'admin.content.types.video', icon: 'fas fa-video' },
    { value: 'blog', labelKey: 'admin.content.types.blog', icon: 'fas fa-blog' },
    { value: 'news', labelKey: 'admin.content.types.news', icon: 'fas fa-newspaper' },
    { value: 'announcement', labelKey: 'admin.content.types.announcement', icon: 'fas fa-bullhorn' },
]);

// Video platforms
const videoPlatforms = [
    { value: 'youtube', label: 'YouTube' },
    { value: 'vimeo', label: 'Vimeo' },
    { value: 'other', label: 'Cits' },
];

// Generate slug
const generateSlug = (text) => {
    const latvianMap = {
        'ā': 'a', 'č': 'c', 'ē': 'e', 'ģ': 'g', 'ī': 'i', 'ķ': 'k',
        'ļ': 'l', 'ņ': 'n', 'š': 's', 'ū': 'u', 'ž': 'z',
        'Ā': 'a', 'Č': 'c', 'Ē': 'e', 'Ģ': 'g', 'Ī': 'i', 'Ķ': 'k',
        'Ļ': 'l', 'Ņ': 'n', 'Š': 's', 'Ū': 'u', 'Ž': 'z'
    };
    return text.toLowerCase().split('').map(char => latvianMap[char] || char).join('')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 100);
};

const regenerateSlug = () => {
    if (form.title_lv) form.slug = generateSlug(form.title_lv);
};

// File upload handlers
const handleThumbnailUpload = (event) => {
    const file = event.target.files[0];
    if (file) processImageFile(file, 'thumbnail');
};

const handleFeaturedImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) processImageFile(file, 'featured');
};

const handleBlogImagesUpload = (event) => {
    Array.from(event.target.files).forEach(file => processImageFile(file, 'blog'));
};

const processImageFile = (file, type) => {
    if (!file.type.startsWith('image/')) {
        alert(t('admin.content.form.invalidImageType') || 'Nederīgs attēla formāts');
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        if (type === 'thumbnail') {
            thumbnailPreview.value = e.target.result;
            form.thumbnail = file;
            form._remove_thumbnail = false;
        } else if (type === 'featured') {
            featuredImagePreview.value = e.target.result;
            form.featured_image = file;
            form._remove_featured_image = false;
        } else if (type === 'blog') {
            blogImagesPreview.value.push(e.target.result);
            form.blog_images.push(file);
        }
    };
    reader.readAsDataURL(file);
};

// Drag & drop handlers
const handleDragOver = (event, type) => {
    event.preventDefault();
    if (type === 'thumbnail') isDraggingThumbnail.value = true;
    else if (type === 'featured') isDraggingFeatured.value = true;
    else if (type === 'blog') isDraggingBlogImages.value = true;
};

const handleDragLeave = (type) => {
    if (type === 'thumbnail') isDraggingThumbnail.value = false;
    else if (type === 'featured') isDraggingFeatured.value = false;
    else if (type === 'blog') isDraggingBlogImages.value = false;
};

const handleDrop = (event, type) => {
    event.preventDefault();
    handleDragLeave(type);
    const files = event.dataTransfer.files;
    if (type === 'blog') {
        Array.from(files).forEach(file => processImageFile(file, 'blog'));
    } else if (files[0]) {
        processImageFile(files[0], type);
    }
};

// Remove images
const removeThumbnail = () => {
    thumbnailPreview.value = null;
    form.thumbnail = null;
    form._remove_thumbnail = true;
};

const removeFeaturedImage = () => {
    featuredImagePreview.value = null;
    form.featured_image = null;
    form._remove_featured_image = true;
};

const removeNewBlogImage = (index) => {
    blogImagesPreview.value.splice(index, 1);
    form.blog_images.splice(index, 1);
};

const removeExistingBlogImage = (index) => {
    form._remove_blog_images.push(index);
};

// Active tab for language content
const activeTab = ref('lv');

// Submit form
const submit = () => {
    form.post(`/admin/content/${props.content.id}`, {
        preserveScroll: true,
        forceFormData: true,
        headers: { 'X-HTTP-Method-Override': 'PUT' },
    });
};

// Computed
const isVideoType = computed(() => form.type === 'video');
const isBlogType = computed(() => form.type === 'blog');

const displayTitle = computed(() => {
    return locale.value === 'lv'
        ? (props.content.title_lv || props.content.title_en)
        : (props.content.title_en || props.content.title_lv);
});

// Delete content
const deleteContent = () => {
    if (confirm(t('admin.content.deleteConfirm', { name: displayTitle.value }))) {
        router.delete(`/admin/content/${props.content.id}`);
    }
};

// Get blog image URL
const getBlogImageUrl = (img) => {
    if (img.startsWith('http') || img.startsWith('/')) return img;
    return `/img/Blogs/${img}`;
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleString(locale.value === 'lv' ? 'lv-LV' : 'en-US');
};
</script>

<template>
    <Head :title="`${t('admin.content.edit.title')} - ${displayTitle}`" />

    <AdminLayout>
        <template #title>{{ t('admin.content.edit.title') }}</template>

        <!-- Header -->
        <div class="page-header">
            <div class="header-left">
                <Link href="/admin/content" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i>
                    {{ t('admin.common.back') }}
                </Link>
                <div class="header-info">
                    <h2 class="content-name">{{ displayTitle }}</h2>
                    <p class="header-subtitle">{{ t('admin.content.edit.subtitle') }}</p>
                </div>
            </div>
            <div class="header-actions">
                <Link :href="`/content/${content.slug}`" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="btn-text">{{ t('admin.common.view') }}</span>
                </Link>
                <button @click="deleteContent" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    <span class="btn-text">{{ t('admin.common.delete') }}</span>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="content-form">
            <div class="form-layout">
                <!-- Main Content -->
                <div class="form-main">
                    <!-- Content Type -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group"></i>
                                {{ t('admin.content.form.type') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="type-selector">
                                <label v-for="type in contentTypes" :key="type.value"
                                       :class="['type-option', { active: form.type === type.value }]">
                                    <input type="radio" v-model="form.type" :value="type.value" class="sr-only">
                                    <i :class="type.icon"></i>
                                    <span>{{ t(type.labelKey) }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Titles & Slug -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-heading"></i>
                                {{ t('admin.content.form.titles') || 'Virsraksti' }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        {{ t('admin.content.form.titleLv') }}
                                        <span class="required">*</span>
                                    </label>
                                    <input v-model="form.title_lv" type="text" class="form-input"
                                           :class="{ 'is-invalid': form.errors.title_lv }"
                                           :placeholder="t('admin.content.form.titleLvPlaceholder')" required>
                                    <span v-if="form.errors.title_lv" class="error-text">{{ form.errors.title_lv }}</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">{{ t('admin.content.form.titleEn') }}</label>
                                    <input v-model="form.title_en" type="text" class="form-input"
                                           :placeholder="t('admin.content.form.titleEnPlaceholder')">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    {{ t('admin.content.form.slug') }}
                                    <span class="required">*</span>
                                </label>
                                <div class="slug-input-wrapper">
                                    <span class="slug-prefix">/content/</span>
                                    <input v-model="form.slug" type="text" class="form-input slug-input"
                                           :class="{ 'is-invalid': form.errors.slug }"
                                           :placeholder="t('admin.content.form.slugPlaceholder')" required>
                                    <button type="button" @click="regenerateSlug" class="btn-regenerate"
                                            :title="t('admin.content.form.regenerateSlug') || 'Ģenerēt no virsraksta'">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <span v-if="form.errors.slug" class="error-text">{{ form.errors.slug }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ t('admin.content.form.category') }}</label>
                                <input v-model="form.category" type="text" class="form-input"
                                       :placeholder="t('admin.content.form.categoryPlaceholder')">
                            </div>
                        </div>
                    </div>

                    <!-- Video Settings -->
                    <div v-if="isVideoType" class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-video"></i>
                                {{ t('admin.content.form.videoSettings') || 'Video iestatījumi' }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        {{ t('admin.content.form.videoUrl') }}
                                        <span class="required">*</span>
                                    </label>
                                    <input v-model="form.video_url" type="url" class="form-input"
                                           :class="{ 'is-invalid': form.errors.video_url }"
                                           :placeholder="t('admin.content.form.videoUrlPlaceholder')">
                                    <span v-if="form.errors.video_url" class="error-text">{{ form.errors.video_url }}</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">{{ t('admin.content.form.videoPlatform') }}</label>
                                    <select v-model="form.video_platform" class="form-select">
                                        <option v-for="platform in videoPlatforms" :key="platform.value" :value="platform.value">
                                            {{ platform.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ t('admin.content.form.duration') }}</label>
                                <div class="duration-input">
                                    <input v-model.number="form.duration" type="number" class="form-input" min="0" placeholder="0">
                                    <span class="duration-unit">{{ t('admin.content.form.seconds') || 'sekundes' }}</span>
                                </div>
                                <p class="form-hint">{{ t('admin.content.form.durationHint') || 'Video ilgums sekundēs' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Content Body -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-alt"></i>
                                {{ t('admin.content.form.content') || 'Saturs' }}
                            </h3>
                            <div class="lang-tabs">
                                <button type="button" :class="['lang-tab', { active: activeTab === 'lv' }]" @click="activeTab = 'lv'">
                                    <span class="flag">🇱🇻</span> LV
                                </button>
                                <button type="button" :class="['lang-tab', { active: activeTab === 'en' }]" @click="activeTab = 'en'">
                                    <span class="flag">🇬🇧</span> EN
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- LV Content -->
                            <div v-show="activeTab === 'lv'" class="tab-content">
                                <div class="form-group">
                                    <label class="form-label">{{ t('admin.content.form.descriptionLv') }}</label>
                                    <textarea v-model="form.description_lv" class="form-textarea" rows="3"
                                              :placeholder="t('admin.content.form.descriptionPlaceholder')"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ t('admin.content.form.contentLv') }}</label>
                                    <textarea v-model="form.content_body_lv" class="form-textarea content-editor" rows="12"
                                              :placeholder="t('admin.content.form.contentPlaceholder')"></textarea>
                                </div>
                            </div>

                            <!-- EN Content -->
                            <div v-show="activeTab === 'en'" class="tab-content">
                                <div class="form-group">
                                    <label class="form-label">{{ t('admin.content.form.descriptionEn') }}</label>
                                    <textarea v-model="form.description_en" class="form-textarea" rows="3"
                                              :placeholder="t('admin.content.form.descriptionPlaceholder')"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ t('admin.content.form.contentEn') }}</label>
                                    <textarea v-model="form.content_body_en" class="form-textarea content-editor" rows="12"
                                              :placeholder="t('admin.content.form.contentPlaceholder')"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Blog Images -->
                    <div v-if="isBlogType" class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-images"></i>
                                {{ t('admin.content.form.blogImages') || 'Bloga attēli' }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Existing Images -->
                            <div v-if="content.blog_images && content.blog_images.length > 0" class="existing-images">
                                <p class="section-label">{{ t('admin.content.form.existingImages') || 'Esošie attēli' }}</p>
                                <div class="image-gallery">
                                    <div v-for="(img, index) in content.blog_images" :key="'existing-' + index"
                                         class="gallery-item" :class="{ 'removed': form._remove_blog_images.includes(index) }">
                                        <img :src="getBlogImageUrl(img)" :alt="`Image ${index + 1}`">
                                        <button v-if="!form._remove_blog_images.includes(index)" type="button"
                                                @click="removeExistingBlogImage(index)" class="remove-image">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div v-else class="removed-overlay">
                                            <i class="fas fa-trash"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload New -->
                            <div class="dropzone" :class="{ 'dragging': isDraggingBlogImages }"
                                 @dragover="handleDragOver($event, 'blog')" @dragleave="handleDragLeave('blog')"
                                 @drop="handleDrop($event, 'blog')">
                                <input type="file" @change="handleBlogImagesUpload" accept="image/*" multiple
                                       class="dropzone-input" id="blog-images-input">
                                <label for="blog-images-input" class="dropzone-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>{{ t('admin.content.form.addMoreImages') || 'Pievienot attēlus' }}</span>
                                </label>
                            </div>

                            <!-- New Images Preview -->
                            <div v-if="blogImagesPreview.length > 0" class="new-images">
                                <p class="section-label">{{ t('admin.content.form.newImages') || 'Jaunie attēli' }}</p>
                                <div class="image-gallery">
                                    <div v-for="(preview, index) in blogImagesPreview" :key="'new-' + index" class="gallery-item">
                                        <img :src="preview" :alt="`New ${index + 1}`">
                                        <button type="button" @click="removeNewBlogImage(index)" class="remove-image">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="form-sidebar">
                    <!-- Publish Settings -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cog"></i>
                                {{ t('admin.content.form.publishSettings') || 'Iestatījumi' }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="toggle-group">
                                <label class="toggle-label">
                                    <input type="checkbox" v-model="form.is_published" class="toggle-input">
                                    <span class="toggle-switch"></span>
                                    <span class="toggle-text">
                                        <strong>{{ t('admin.content.form.isPublished') }}</strong>
                                        <small>{{ t('admin.content.form.isPublishedHint') }}</small>
                                    </span>
                                </label>
                            </div>

                            <div class="toggle-group">
                                <label class="toggle-label">
                                    <input type="checkbox" v-model="form.is_featured" class="toggle-input">
                                    <span class="toggle-switch"></span>
                                    <span class="toggle-text">
                                        <strong>{{ t('admin.content.form.isFeatured') }}</strong>
                                        <small>{{ t('admin.content.form.isFeaturedHint') }}</small>
                                    </span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ t('admin.content.form.publishedAt') }}</label>
                                <input v-model="form.published_at" type="datetime-local" class="form-input">
                            </div>
                        </div>
                    </div>

                    <!-- Thumbnail (Video) -->
                    <div v-if="isVideoType" class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-image"></i>
                                {{ t('admin.content.form.thumbnail') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div v-if="existingThumbnail && !thumbnailPreview" class="image-preview existing">
                                <img :src="existingThumbnail" alt="Current thumbnail">
                                <button type="button" @click="removeThumbnail" class="remove-image">
                                    <i class="fas fa-times"></i>
                                </button>
                                <span class="image-label">{{ t('admin.content.form.currentImage') || 'Pašreizējais' }}</span>
                            </div>
                            <div v-else-if="thumbnailPreview" class="image-preview">
                                <img :src="thumbnailPreview" alt="New thumbnail">
                                <button type="button" @click="removeThumbnail" class="remove-image">
                                    <i class="fas fa-times"></i>
                                </button>
                                <span class="image-label new">{{ t('admin.content.form.newImage') || 'Jauns' }}</span>
                            </div>
                            <div v-else class="dropzone" :class="{ 'dragging': isDraggingThumbnail }"
                                 @dragover="handleDragOver($event, 'thumbnail')" @dragleave="handleDragLeave('thumbnail')"
                                 @drop="handleDrop($event, 'thumbnail')">
                                <input type="file" @change="handleThumbnailUpload" accept="image/*" class="dropzone-input" id="thumbnail-input">
                                <label for="thumbnail-input" class="dropzone-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>{{ t('admin.content.form.uploadImage') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image (Blog/News) -->
                    <div v-if="!isVideoType" class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-image"></i>
                                {{ t('admin.content.form.featuredImage') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div v-if="existingFeaturedImage && !featuredImagePreview" class="image-preview existing">
                                <img :src="existingFeaturedImage" alt="Current featured">
                                <button type="button" @click="removeFeaturedImage" class="remove-image">
                                    <i class="fas fa-times"></i>
                                </button>
                                <span class="image-label">{{ t('admin.content.form.currentImage') || 'Pašreizējais' }}</span>
                            </div>
                            <div v-else-if="featuredImagePreview" class="image-preview">
                                <img :src="featuredImagePreview" alt="New featured">
                                <button type="button" @click="removeFeaturedImage" class="remove-image">
                                    <i class="fas fa-times"></i>
                                </button>
                                <span class="image-label new">{{ t('admin.content.form.newImage') || 'Jauns' }}</span>
                            </div>
                            <div v-else class="dropzone" :class="{ 'dragging': isDraggingFeatured }"
                                 @dragover="handleDragOver($event, 'featured')" @dragleave="handleDragLeave('featured')"
                                 @drop="handleDrop($event, 'featured')">
                                <input type="file" @change="handleFeaturedImageUpload" accept="image/*" class="dropzone-input" id="featured-input">
                                <label for="featured-input" class="dropzone-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>{{ t('admin.content.form.uploadImage') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar"></i>
                                {{ t('admin.content.form.stats') || 'Statistika' }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <i class="fas fa-eye"></i>
                                    <span class="stat-value">{{ content.view_count || 0 }}</span>
                                    <span class="stat-label">{{ t('admin.content.views') }}</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-heart"></i>
                                    <span class="stat-value">{{ content.like_count || 0 }}</span>
                                    <span class="stat-label">{{ t('admin.content.likes') }}</span>
                                </div>
                            </div>
                            <div class="meta-info">
                                <p><strong>{{ t('admin.content.form.created') || 'Izveidots' }}:</strong> {{ formatDate(content.created_at) }}</p>
                                <p><strong>{{ t('admin.content.form.updated') || 'Atjaunināts' }}:</strong> {{ formatDate(content.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                                <i :class="form.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                                {{ form.processing ? t('admin.common.saving') : t('admin.content.form.update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
/* Layout */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #f9fafb;
}

.header-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.content-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.header-subtitle {
    color: #6b7280;
    margin: 0;
    font-size: 0.875rem;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.form-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 1.5rem;
    align-items: start;
}

.form-main, .form-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Cards */
.card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-title i { color: #dc2626; }

.card-body { padding: 1.25rem; }

/* Type Selector */
.type-selector {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.type-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
}

.type-option:hover {
    border-color: #fca5a5;
    background: #fef2f2;
}

.type-option.active {
    border-color: #dc2626;
    background: #fef2f2;
}

.type-option i {
    font-size: 1.5rem;
    color: #6b7280;
}

.type-option.active i { color: #dc2626; }

.type-option span {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

/* Form Elements */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group { margin-bottom: 1rem; }
.form-group:last-child { margin-bottom: 0; }

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.required { color: #dc2626; }

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 0.625rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.is-invalid { border-color: #dc2626; }

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.content-editor {
    font-family: 'JetBrains Mono', monospace;
    min-height: 300px;
}

.form-hint {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.375rem;
}

.error-text {
    font-size: 0.75rem;
    color: #dc2626;
    margin-top: 0.25rem;
    display: block;
}

/* Slug Input */
.slug-input-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    overflow: hidden;
}

.slug-input-wrapper:focus-within {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.slug-prefix {
    padding: 0.625rem 0.75rem;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 0.875rem;
    border-right: 1px solid #d1d5db;
}

.slug-input {
    border: none;
    border-radius: 0;
}

.slug-input:focus { box-shadow: none; }

.btn-regenerate {
    padding: 0.625rem 0.75rem;
    background: #f3f4f6;
    border: none;
    border-left: 1px solid #d1d5db;
    color: #6b7280;
    cursor: pointer;
}

.btn-regenerate:hover {
    background: #e5e7eb;
    color: #374151;
}

/* Duration Input */
.duration-input {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.duration-input .form-input { max-width: 150px; }

.duration-unit {
    color: #6b7280;
    font-size: 0.875rem;
}

/* Language Tabs */
.lang-tabs {
    display: flex;
    gap: 0.5rem;
}

.lang-tab {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    background: white;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
}

.lang-tab.active {
    border-color: #dc2626;
    background: #fef2f2;
    color: #dc2626;
}

.flag { font-size: 1rem; }

/* Toggle Groups */
.toggle-group { margin-bottom: 1rem; }
.toggle-group:last-child { margin-bottom: 0; }

.toggle-label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
}

.toggle-input { display: none; }

.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    background: #d1d5db;
    border-radius: 12px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.toggle-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-input:checked + .toggle-switch { background: #dc2626; }
.toggle-input:checked + .toggle-switch::after { transform: translateX(20px); }

.toggle-text {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.toggle-text strong {
    font-size: 0.875rem;
    color: #111827;
}

.toggle-text small {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Dropzone */
.dropzone {
    border: 2px dashed #d1d5db;
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: center;
    transition: all 0.2s;
    cursor: pointer;
}

.dropzone:hover, .dropzone.dragging {
    border-color: #dc2626;
    background: #fef2f2;
}

.dropzone-input { display: none; }

.dropzone-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.dropzone-label i {
    font-size: 2rem;
    color: #9ca3af;
}

.dropzone:hover .dropzone-label i { color: #dc2626; }

.dropzone-label span {
    font-size: 0.875rem;
    color: #374151;
    font-weight: 500;
}

/* Image Preview */
.image-preview {
    position: relative;
    border-radius: 0.75rem;
    overflow: hidden;
}

.image-preview img {
    width: 100%;
    height: auto;
    display: block;
}

.image-preview.existing {
    border: 2px solid #d1d5db;
}

.image-label {
    position: absolute;
    bottom: 0.5rem;
    left: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 0.625rem;
    border-radius: 0.25rem;
    font-weight: 600;
}

.image-label.new { background: #dc2626; }

.remove-image {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 2rem;
    height: 2rem;
    background: rgba(220, 38, 38, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-image:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Image Gallery */
.section-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}

.existing-images, .new-images { margin-bottom: 1rem; }

.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
}

.gallery-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 0.5rem;
    overflow: hidden;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-item .remove-image {
    width: 1.5rem;
    height: 1.5rem;
    font-size: 0.625rem;
}

.gallery-item.removed {
    opacity: 0.5;
}

.removed-overlay {
    position: absolute;
    inset: 0;
    background: rgba(220, 38, 38, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}

.stat-item i {
    font-size: 1.25rem;
    color: #dc2626;
    margin-bottom: 0.25rem;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
}

.meta-info {
    font-size: 0.75rem;
    color: #6b7280;
}

.meta-info p { margin: 0.25rem 0; }

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover { background: #e5e7eb; }

.btn-danger {
    background: #fee2e2;
    color: #dc2626;
}

.btn-danger:hover {
    background: #dc2626;
    color: white;
}

.btn-block { width: 100%; }

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .form-layout { grid-template-columns: 1fr; }
    .form-sidebar { order: -1; }
    .type-selector { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .header-actions { justify-content: flex-end; }
    .form-grid { grid-template-columns: 1fr; }
    .card-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
    .btn-text { display: none; }
}

@media (max-width: 480px) {
    .type-selector { grid-template-columns: 1fr 1fr; }
    .type-option { padding: 0.75rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>
