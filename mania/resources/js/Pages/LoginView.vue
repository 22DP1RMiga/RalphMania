<script setup>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import axios from 'axios'

    import Navbar from '../Components/Navbar.vue'
    import Footer from '../Components/Footer.vue'
    import Login from '../Components/Login.vue'

    const router = useRouter()
    const errorMessage = ref('')
    // const userInput = ref('')
    // const password = ref('')

    const loginUser = async ({ userInput, password }) => {
        errorMessage.value = ''

        try {
            const response = await axios.post('http://localhost:8000/api/login', {
                user_input: userInput.value,
                password: password.value
            })
            localStorage.setItem('currentUser', JSON.stringify(response.data.user))
            router.push('/contacts')
        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Login failed!'
        }
    }
</script>

<template>
    <Navbar />
    <Login @login="loginUser" />
    <Footer />
</template>

<style scoped>
</style>

