<script setup>
import MainNavbar from '@/Components/MainNavbar.vue';
import MainFooter from '@/Components/MainFooter.vue';
</script>

<template>
    <div class="main-layout">
        <!-- Navigācijas josla -->
        <MainNavbar />

        <!-- Lapas saturs -->
        <main class="main-content">
            <slot />
        </main>

        <!-- Kājene -->
        <MainFooter />
    </div>
</template>

<style scoped>
.main-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.main-content {
    flex: 1;
}
</style>
