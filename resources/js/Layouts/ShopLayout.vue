<script setup>
import ShopNavbar from '@/Components/ShopNavbar.vue';
import ShopFooter from '@/Components/ShopFooter.vue';
</script>

<template>
    <div class="shop-layout">
        <!-- Shop Navbar -->
        <ShopNavbar />

        <!-- Page Content -->
        <main class="shop-content">
            <slot />
        </main>

        <!-- Shop Footer -->
        <ShopFooter />
    </div>
</template>

<style scoped>
.shop-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: #f9fafb;
}

.shop-content {
    flex: 1;
}
</style>
