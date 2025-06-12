<script setup>
import Navbar from '../Components/Navbar.vue'
import Footer from '../Components/Footer.vue'
import ContactBox from '../Components/ContactBox.vue'
import { ref, onMounted } from 'vue'
import {Head} from "@inertiajs/vue3";

const user = ref(null)

onMounted(() => {
    const storedUser = localStorage.getItem('currentUser')
    if (storedUser) {
        user.value = JSON.parse(storedUser)
    }
})

const handleSent = () => {
    console.log('Message sent!')
}
</script>

<template>
    <Head title="CONTACTS" />
    <Navbar />

    <div class="contact-wrapper">
        <h1>Contact RoltonsLV</h1>

        <div v-if="user">
            <ContactBox :userEmail="user.email" @message-sent="handleSent" />
        </div>

        <p v-else class="note-for-login">Please log in to contact RoltonsLV.</p>
    </div>

    <Footer />
</template>

<style scoped>
body {
    background-image:
        url('../../../public/img/Hostage_Adventure.png'),
        linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
}

.contact-wrapper {
    padding: 30px;
    text-align: center;
}

.note-for-login {
    color: white;
}
</style>
