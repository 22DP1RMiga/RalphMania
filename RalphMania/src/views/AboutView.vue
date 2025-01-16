<script setup>
import About_Navbar from '../components/About_Navbar.vue'
import About_UpperWave from '../components/About_UpperWave.vue'
</script>

<template>
  <main>
    <About_Navbar/>
    <About_UpperWave/>
  </main>
</template>

<style>
  @media (min-width: 1024px) {
    .about {
      min-height: 100vh;
      display: flex;
      align-items: center;
    }
  }
</style>
