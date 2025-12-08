<script setup>
defineProps({
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
    },
    fullscreen: {
        type: Boolean,
        default: false,
    },
    text: {
        type: String,
        default: '',
    }
});

const sizeClasses = {
    sm: 'spinner-sm',
    md: 'spinner-md',
    lg: 'spinner-lg',
    xl: 'spinner-xl'
};
</script>

<template>
    <div
        class="loading-spinner-wrapper"
        :class="{ 'loading-fullscreen': fullscreen }"
    >
        <div class="loading-spinner-container">
            <!-- Outer Ring -->
            <div
                class="spinner-ring"
                :class="sizeClasses[size]"
            ></div>

            <!-- Logo in Center -->
            <div
                class="spinner-logo"
                :class="sizeClasses[size]"
            >
                <img src="/img/RoltonsLV_Icon.png" alt="Loading..." class="logo-image">
            </div>

            <!-- Loading Text -->
            <p v-if="text" class="loading-text">{{ text }}</p>
        </div>
    </div>
</template>

<style scoped>
/* ========== LOADING SPINNER WRAPPER ========== */
.loading-spinner-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.loading-fullscreen {
    position: fixed;
    inset: 0;
    background-color: rgba(255, 255, 255, 0.95);
    z-index: 9999;
    backdrop-filter: blur(4px);
}

.loading-spinner-container {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

/* ========== SPINNER RING ========== */
.spinner-ring {
    position: relative;
    border-radius: 50%;
    border: 4px solid #fecaca;
    border-top-color: #dc2626;
    animation: spin 1s linear infinite;
}

.spinner-sm .spinner-ring,
.spinner-ring.spinner-sm {
    width: 3rem;
    height: 3rem;
    border-width: 3px;
}

.spinner-md .spinner-ring,
.spinner-ring.spinner-md {
    width: 4rem;
    height: 4rem;
    border-width: 4px;
}

.spinner-lg .spinner-ring,
.spinner-ring.spinner-lg {
    width: 6rem;
    height: 6rem;
    border-width: 5px;
}

.spinner-xl .spinner-ring,
.spinner-ring.spinner-xl {
    width: 8rem;
    height: 8rem;
    border-width: 6px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ========== SPINNER LOGO ========== */
.spinner-logo {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s ease-in-out infinite;
}

.spinner-sm .spinner-logo,
.spinner-logo.spinner-sm {
    width: 2rem;
    height: 2rem;
}

.spinner-md .spinner-logo,
.spinner-logo.spinner-md {
    width: 2.5rem;
    height: 2.5rem;
}

.spinner-lg .spinner-logo,
.spinner-logo.spinner-lg {
    width: 4rem;
    height: 4rem;
}

.spinner-xl .spinner-logo,
.spinner-logo.spinner-xl {
    width: 5.5rem;
    height: 5.5rem;
}

.logo-image {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        opacity: 0.7;
        transform: translate(-50%, -50%) scale(0.95);
    }
}

/* ========== LOADING TEXT ========== */
.loading-text {
    font-size: 1rem;
    font-weight: 500;
    color: #6b7280;
    text-align: center;
    animation: fadeInOut 2s ease-in-out infinite;
}

@keyframes fadeInOut {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
