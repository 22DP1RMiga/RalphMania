<script setup>
import ShopNavbar from '@/Components/ShopNavbar.vue';
import ShopFooter from '@/Components/ShopFooter.vue';
</script>

<template>
    <div class="shop-layout">
        <!-- Veikala navigācijas josla -->
        <ShopNavbar />

        <!-- Lapas saturs -->
        <main class="shop-content">
            <slot />
        </main>

        <!-- Veikala kājene -->
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
