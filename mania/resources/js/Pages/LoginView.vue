<script setup>
import {route} from "ziggy-js";
import { Head, Link, useForm } from '@inertiajs/vue3';
import Navbar from "../Components/Navbar.vue";
import login from "../Components/Login.vue";

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <main class="page-wrapper">
        <Navbar/>

        <div class="form-box">
            <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon" />
            <h2>Login</h2>

            <p v-if="error" style="color: red">{{ error }}</p>

            <input v-model="form.email" placeholder=" Email (or Username)" />
            <input v-model="form.password" type="password" placeholder=" Password" />
            <button class="signin-button" @click="login">Sign In</button>

            <p>
                Don't have an account?
                <a href="/register" class="text-blue-500 hover:underline">Sign Up</a>
            </p>
        </div>

        <Footer />
    </main>
</template>

<style scoped>
h2 {
    font-size: 40px;
}

.page-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
}

.form-box {
    width: 300px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: linear-gradient(to bottom, rgb(229, 112, 112), rgb(143, 69, 69));
    outline: 2px solid darkred;
    font-family: cursive;
    padding: 20px;
    text-align: center;
    align-items: center;
}

.RoltonsLV_Icon {
    max-width: 200px;
    padding-right: 10px;
    padding-left: 5px;
    height: auto;
}

.signin-button {
    background: linear-gradient(to bottom, #af352b, #8e2921);
    color: white;
    padding: 5px 15px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    margin-left: 10px;
    outline: 1px solid black;
}

.signin-button:hover {
    background: linear-gradient(to bottom, #e74c3c, #c0392b);
}
</style>
