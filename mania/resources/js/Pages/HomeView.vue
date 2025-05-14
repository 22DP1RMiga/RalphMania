<script setup>
import Navbar from '../Components/Navbar.vue';
import Improvision from '../Components/Improvision.vue';
import Videos from '../Components/Videos.vue';
import Footer from '../Components/Footer.vue';
</script>

<template>
    <main>
        <Navbar :currentUser="currentUser" @logout="logoutUser" />
        <Improvision/>
        <Videos/>
        <Footer/>
    </main>
</template>

<style>
    body {
        background-image: url('{{ asset('img/Coder_RoltonsLV.png') }}');
    }
</style>
