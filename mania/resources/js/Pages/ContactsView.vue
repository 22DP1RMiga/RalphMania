<script setup>
import Navbar from '../Components/Navbar.vue'
import Footer from '../Components/Footer.vue'
import { ref, onMounted } from 'vue'

const user = ref(null)
const title = ref('')
const message = ref('')
const success = ref(false)

onMounted(() => {
    const storedUser = localStorage.getItem('currentUser')
    if (storedUser) {
        user.value = JSON.parse(storedUser)
    }
})

const sendMessage = async () => {
    if (!title.value || !message.value) return

    try {
        // Example API call — replace with actual endpoint
        await fetch('http://localhost:8000/api/contact', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: user.value.email,
                title: title.value,
                message: message.value,
            })
        })
        success.value = true
        title.value = ''
        message.value = ''
    } catch (err) {
        console.error(err)
    }
}
</script>

<template>
    <Navbar />

    <div class="contact-wrapper">
        <h1>Contact RoltonsLV</h1>

        <div v-if="user" class="contact-box">
            <input v-model="title" type="text" placeholder="Message Title" />
            <input type="email" :value="user.email" disabled />
            <textarea v-model="message" placeholder="Your message here..."></textarea>
            <button @click="sendMessage">Send</button>
            <p v-if="success">Message sent successfully!</p>
        </div>

        <p v-else>Please log in to contact RoltonsLV.</p>
    </div>

    <Footer />
</template>

<style scoped>
.contact-wrapper {
    padding: 30px;
    text-align: center;
}
.contact-box {
    max-width: 500px;
    margin: auto;
    display: flex;
    flex-direction: column;
}
.contact-box input,
.contact-box textarea {
    margin: 10px 0;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
.contact-box button {
    background-color: firebrick;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
}
</style>
