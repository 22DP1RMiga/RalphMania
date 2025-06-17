<script setup>
import { ref } from 'vue'

const props = defineProps({
    userEmail: String
})

const emit = defineEmits(['message-sent'])

const title = ref('')
const message = ref('')
const success = ref(false)

const sendMessage = async () => {
    if (!title.value || !message.value) return

    try {
        await fetch('http://localhost:8000/api/contact', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: props.userEmail,
                title: title.value,
                message: message.value,
            })
        })

        success.value = true
        emit('message-sent')
        title.value = ''
        message.value = ''
    } catch (err) {
        console.error(err)
    }
}
</script>

<template>
    <div class="contact-box">
        <input v-model="title" type="text" placeholder="Message Title" />
        <input type="email" :value="userEmail" disabled />
        <textarea v-model="message" placeholder="Your message here..."></textarea>
        <button @click="sendMessage">Send</button>
        <p v-if="success" class="if-success-text">Message sent successfully!</p>
    </div>
</template>

<style scoped>
.contact-box {
    max-width: 500px;
    margin: auto;
    display: flex;
    flex-direction: column;
    padding: 70px 0;
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
    outline: solid 2px darkred;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
}

.contact-box button:hover {
    background-color: rgb(207, 41, 41);
}

.if-success-text {
    color: rgb(81, 193, 81);
    font-weight: bold;
    margin-top: 10px;
}
</style>
