<script setup>
import Navbar from '../components/Navbar.vue'
import About_UpperWave from '../components/About_UpperWave.vue'
import About_Improvision from '../components/About_Improvision.vue'
import Footer from '../components/Footer.vue'
</script>

<template>
  <main>
    <Navbar/>
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
