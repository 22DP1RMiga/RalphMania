<script setup>
import { ref } from 'vue'

const props = defineProps(['username', 'email', 'password', 'confirmPassword', 'errorMessage'])
const emit = defineEmits(['update:username', 'update:email', 'update:password', 'update:confirmPassword', 'register'])

const updateField = (field, event) => {
    emit(`update:${field}`, event.target.value)
}

const submitForm = () => {
    emit('register')
}
</script>

<template>
    <main>
        <div class="form-container">
            <div class="auth-box">
                <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon" />
                <h2>Register</h2>
                <input :value="username" @input="e => updateField('username', e)" type="text" placeholder="Username" />
                <input :value="email" @input="e => updateField('email', e)" type="email" placeholder="Email" />
                <input :value="password" @input="e => updateField('password', e)" type="password" placeholder="Password" />
                <input :value="confirmPassword" @input="e => updateField('confirmPassword', e)" type="password" placeholder="Confirm Password" />
                <button @click="submitForm">Register</button>
                <a href="/login"><p class="switch-link">Already have an account? Log in</p></a>
                <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
            </div>
        </div>
    </main>
</template>


<style scoped>
    .form-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .RoltonsLV_Icon {
        max-width: 150px;
        padding: 10px;
        justify-self: center;
    }
    .auth-box {
        background: linear-gradient(to bottom, rgb(229, 112, 112), rgb(143, 69, 69));
        border-radius: 10px;
        border: 2px solid black;
        width: 400px;
        padding: 20px;
        text-align: center;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.5);
    }
    .auth-box input {
        width: 90%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 10px;
        border: 1px solid black;
    }
    .auth-box h2 {
        font-weight: bold;
        color: white;
    }
    button {
        margin: 10px;
        padding: 8px 15px;
        background-color: firebrick;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    button:hover {
        background-color: darkred;
    }
    .switch-link {
        margin-top: 10px;
        color: white;
        text-decoration: underline;
        cursor: pointer;
    }
    .switch-link:hover {
        color: lightgray;
    }
    .error {
        color: red;
        margin-top: 10px;
    }
</style>
