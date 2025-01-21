<script setup>
import Navbar from '../components/Navbar.vue'
import Improvision from '../components/Improvision.vue'
import Videos from '../components/Videos.vue'
</script>

<template>
  <main>
    <Navbar/>
    <Improvision/>
    <div class="about">
      <h1>This is an about page</h1>
    </div>
    <Videos/>
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
