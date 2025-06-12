<script setup>
import Navbar from '../Components/Navbar.vue'
import Footer from '../Components/Footer.vue'
import ShopCatalog from '../Components/ShopCatalog.vue'
import {Head} from "@inertiajs/vue3";
</script>

<template>
    <Head title="RalphMania || SHOP" />
    <main>
        <Navbar/>
        <ShopCatalog/>
        <Footer/>
    </main>
</template>

<style>
    /*body {
        background-image: url('../../../public/img/Shop1.jpg');
    }*/
</style>
