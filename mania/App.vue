<script setup>
import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import updateBackground from 'resources/js/background.js';

const page = usePage();

onMounted(() => {
    updateBackground(window.location.pathname);
});

watch(() => page.url.value, (newRoute) => {
    updateBackground(newRoute);
});
</script>

<template>
    <div>
        <div id="background-dark-overlay" style="
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 0; /* Lower than your app's root element */
            pointer-events: none;">
        </div>
        <slot />
    </div>
</template>

<style>
/*#background-dark-overlay {
    display: none;
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0; left: 0;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: -1;
}*/
</style>
