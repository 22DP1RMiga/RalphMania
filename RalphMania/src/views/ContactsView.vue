<script setup>
import Navbar from '../components/Navbar.vue'
import Improvision from '../components/Improvision.vue'
import Footer from '../components/Footer.vue'
</script>

<template>
  <main>
    <Navbar/>
    
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

.registration-form {
  display: flex;
  flex-direction: column;
  max-width: 300px;
  margin: auto;
  gap: 10px;
}
.error { color: red; }
.success { color: green; }

</style>