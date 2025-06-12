<script setup>
import Navbar from '../Components/Navbar.vue';
import Improvision from '../Components/Improvision.vue';
import Videos from '../Components/Videos.vue';
import Footer from '../Components/Footer.vue';
import {Head} from "@inertiajs/vue3";

</script>

<template>
    <Head title="RalphMania || HOME" />
    <main>
        <Navbar/>
        <Improvision/>
        <Videos/>
        <Footer/>
    </main>
</template>

<style>
    body {
        background-image: url('../../../public/img/Coder_RoltonsLV.png');
    }
</style>
