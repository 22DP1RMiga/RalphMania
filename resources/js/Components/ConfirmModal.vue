<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    title: String,
    message: String,
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    type: {
        type: String,
        default: 'danger', // danger, warning, info
    },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const isVisible = ref(props.show);

watch(() => props.show, (newVal) => {
    isVisible.value = newVal;
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

const confirm = () => {
    emit('confirm');
    close();
};

const cancel = () => {
    emit('cancel');
    close();
};

const close = () => {
    isVisible.value = false;
    document.body.style.overflow = '';
    emit('close');
};
</script>

<template>
    <Transition name="modal">
        <div v-if="isVisible" class="modal-overlay" @click="cancel">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <div :class="['modal-icon', `modal-icon-${type}`]">
                        <i v-if="type === 'danger'" class="fas fa-exclamation-triangle"></i>
                        <i v-else-if="type === 'warning'" class="fas fa-exclamation-circle"></i>
                        <i v-else class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="modal-title">{{ title }}</h3>
                </div>

                <div class="modal-body">
                    <p class="modal-message">{{ message }}</p>
                </div>

                <div class="modal-footer">
                    <button @click="cancel" class="modal-btn modal-btn-cancel">
                        {{ cancelText }}
                    </button>
                    <button @click="confirm" :class="['modal-btn', 'modal-btn-confirm', `modal-btn-${type}`]">
                        {{ confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 20px;
}

.modal-container {
    background: white;
    border-radius: 16px;
    max-width: 450px;
    width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.modal-header {
    padding: 24px 24px 16px;
    text-align: center;
}

.modal-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.modal-icon-danger {
    background: #fee2e2;
    color: #dc2626;
}

.modal-icon-warning {
    background: #fef3c7;
    color: #f59e0b;
}

.modal-icon-info {
    background: #dbeafe;
    color: #3b82f6;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.modal-body {
    padding: 0 24px 24px;
}

.modal-message {
    font-size: 15px;
    color: #6b7280;
    text-align: center;
    margin: 0;
    line-height: 1.6;
}

.modal-footer {
    padding: 16px 24px 24px;
    display: flex;
    gap: 12px;
    justify-content: center;
}

.modal-btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    min-width: 120px;
}

.modal-btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.modal-btn-cancel:hover {
    background: #e5e7eb;
}

.modal-btn-confirm {
    color: white;
}

.modal-btn-danger {
    background: #dc2626;
}

.modal-btn-danger:hover {
    background: #b91c1c;
}

.modal-btn-warning {
    background: #f59e0b;
}

.modal-btn-warning:hover {
    background: #d97706;
}

.modal-btn-info {
    background: #3b82f6;
}

.modal-btn-info:hover {
    background: #2563eb;
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
    transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-container {
    transform: scale(0.9) translateY(-20px);
}

.modal-leave-to .modal-container {
    transform: scale(0.9) translateY(-20px);
}

/* Mobile responsive */
@media (max-width: 640px) {
    .modal-container {
        max-width: 100%;
        margin: 0 10px;
    }

    .modal-footer {
        flex-direction: column-reverse;
    }

    .modal-btn {
        width: 100%;
    }
}
</style>
