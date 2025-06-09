<script setup>
import Navbar from '../Components/Navbar.vue'
import About_UpperWave from '../Components/About_UpperWave.vue'
import About_Improvision from '../Components/About_Improvision.vue'
import Footer from '../Components/Footer.vue'
</script>

<template>
    <main>
        <Navbar :currentUser="currentUser" @logout="logoutUser" />
        <About_UpperWave/>
        <About_Improvision/>
        <Footer/>
    </main>
</template>

<style>

@media screen and (min-width: 1024px) {
    .about {
        min-height: 100vh;
        display: flex;
        align-items: center;
    }
}
</style>
