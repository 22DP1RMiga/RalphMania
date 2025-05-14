<script setup>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import axios from 'axios'

    import Navbar from '../Components/Navbar.vue'
    import Footer from '../Components/Footer.vue'
    import Register from '../Components/Register.vue'

    const router = useRouter()

    const username = ref('')
    const email = ref('')
    const password = ref('')
    const confirmPassword = ref('')
    const errorMessage = ref('')

    const registerUser = async () => {
        errorMessage.value = ''

        if (!username.value || !email.value || !password.value || !confirmPassword.value) {
            errorMessage.value = 'All fields are required!'
            return
        }

        if (password.value !== confirmPassword.value) {
            errorMessage.value = 'Passwords do not match!'
            return
        }

        try {
            await axios.post('http://localhost:8000/api/register', {
                username: username.value,
                email: email.value,
                password: password.value
            })
            router.push('/login')
        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Registration failed!'
        }
    }
</script>

<template>
    <Navbar />
    <Register
        :username="username"
        :email="email"
        :password="password"
        :confirmPassword="confirmPassword"
        :errorMessage="errorMessage"
        @update:username="val => username = val"
        @update:email="val => email = val"
        @update:password="val => password = val"
        @update:confirmPassword="val => confirmPassword = val"
        @register="registerUser"
    />
    <Footer />
</template>

<style scoped>
</style>

