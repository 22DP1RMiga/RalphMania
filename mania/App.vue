<script setup>
    import Navbar from './resources/js/Components/Navbar.vue';
    import Footer from './resources/js/Components/Footer.vue'
    import { ref, onMounted } from 'vue'
    // import { useUserStore } from './stores/user.js'

    // const userStore = useUserStore();
    const currentUser = ref(null)

    onMounted(() => {
        const user = JSON.parse(localStorage.getItem('currentUser'))
        if (user) {
            currentUser.value = user
        }
    })

    const logout = () => {
        localStorage.removeItem('currentUser')
        currentUser.value = null
    }
</script>

<template>
<!--    <Navbar :currentUser="userStore.user" @logout="userStore.clearUser" />-->
    <Navbar :currentUser="currentUser" @logout="logout" />
    <router-view />
    <Footer />
</template>

