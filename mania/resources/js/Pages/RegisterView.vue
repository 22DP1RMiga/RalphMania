<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {route} from "ziggy-js";
import Navbar from "../Components/Navbar.vue";

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onError: (errors) => console.log(errors),
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <main class="page-wrapper">
        <Navbar/>
        <div class="form-box">
            <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
            <h2>Register</h2>
            <div v-if="errors">
                <ul>
                    <li v-for="(msg, field) in errors" :key="field" style="color: red">{{ msg[0] }}</li>
                </ul>
            </div>
            <input v-model="form.username" placeholder=" Username" />
            <input v-model="form.email" placeholder=" Email" />
            <input v-model="form.password" type="password" placeholder=" Password" />
            <input v-model="form.password_confirmation" type="password" placeholder=" Confirm Password" />
            <button class="signin-button" @click="submit">Sign Up</button>
            <p>
                Have an account?
                <a href="/login" class="text-blue-500 hover:underline">Sign In</a>
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

    .moved_text h1 {
        font-size: 24px;
    }

    .RoltonsLV_Icon {
        max-width: 200px; /* Set the size for the images */
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
        color: white;
        padding: 5px 15px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        margin-left: 10px;
        outline: 1px solid black;
    }
</style>
