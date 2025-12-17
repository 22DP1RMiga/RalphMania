<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    message: String,
    type: {
        type: String,
        default: 'success', // success, error, info
    },
    duration: {
        type: Number,
        default: 3000,
    },
});

const emit = defineEmits(['close']);

const isVisible = ref(props.show);

watch(() => props.show, (newVal) => {
    isVisible.value = newVal;
    if (newVal) {
        setTimeout(() => {
            isVisible.value = false;
            emit('close');
        }, props.duration);
    }
});

const close = () => {
    isVisible.value = false;
    emit('close');
};
</script>

<template>
    <Transition name="toast">
        <div v-if="isVisible" :class="['toast-notification', `toast-${type}`]" @click="close">
            <div class="toast-icon">
                <i v-if="type === 'success'" class="fas fa-check-circle"></i>
                <i v-else-if="type === 'error'" class="fas fa-exclamation-circle"></i>
                <i v-else class="fas fa-info-circle"></i>
            </div>
            <div class="toast-message">{{ message }}</div>
            <button class="toast-close" @click.stop="close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </Transition>
</template>

<style scoped>
.toast-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    min-width: 300px;
    max-width: 500px;
    padding: 16px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 9999;
    cursor: pointer;
    border-left: 4px solid;
}

.toast-success {
    border-left-color: #10b981;
    background: linear-gradient(to right, #ecfdf5, white);
}

.toast-error {
    border-left-color: #ef4444;
    background: linear-gradient(to right, #fef2f2, white);
}

.toast-info {
    border-left-color: #3b82f6;
    background: linear-gradient(to right, #eff6ff, white);
}

.toast-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.toast-success .toast-icon {
    color: #10b981;
}

.toast-error .toast-icon {
    color: #ef4444;
}

.toast-info .toast-icon {
    color: #3b82f6;
}

.toast-message {
    flex: 1;
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.toast-close {
    background: none;
    border: none;
    cursor: pointer;
    color: #6b7280;
    font-size: 16px;
    padding: 4px;
    transition: color 0.2s;
    flex-shrink: 0;
}

.toast-close:hover {
    color: #1f2937;
}

/* Transition animations */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100px);
}

/* Mobile responsive */
@media (max-width: 640px) {
    .toast-notification {
        top: 70px;
        right: 10px;
        left: 10px;
        min-width: auto;
        max-width: none;
    }
}
</style>
