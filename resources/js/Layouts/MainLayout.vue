<script setup>
import MainNavbar from '@/Components/MainNavbar.vue';
import MainFooter from '@/Components/MainFooter.vue';
</script>

<template>
    <div class="main-layout">
        <!-- Navbar -->
        <MainNavbar />

        <!-- Page Content -->
        <main class="main-content">
            <slot />
        </main>

        <!-- Footer -->
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
