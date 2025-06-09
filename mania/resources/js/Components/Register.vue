<script setup>
import { ref } from 'vue'
import {route} from "ziggy-js";
import {post} from "axios";
import {useForm} from "@inertiajs/vue3";

const props = defineProps(['username', 'email', 'password', 'confirmPassword', 'errorMessage'])
const emit = defineEmits(['update:username', 'update:email', 'update:password', 'update:confirmPassword', 'register'])


const form = useForm({
    username: '',
    email: '',
    password: '',
    confirmPassword: '',
});
const updateField = (field, event) => {
    emit(`update:${field}`, event.target.value)
}

const submitForm = () => {
    post(route('registration'), {
        onError: (errors) => console.log(errors),
        onFinish: () => form.reset('password', 'confirmPassword'),
    });
}
</script>

<template>
    <section>
        <div class="form-container">
            <div class="form login">
                <div class="form-content">
                    <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon" />
                    <h2>Register</h2>
                    <form @submit.prevent="submitForm">
                        <div class="field input-field">
                            <input :value="username" v-model="form.username"  @input="e => updateField('username', e)" type="text" placeholder="Username" />
                        </div>
                        <div class="field input-field">
                            <input :value="email" v-model="form.email"  @input="e => updateField('email', e)" type="email" placeholder="Email" />
                        </div>
                        <div class="field input-field">
                            <input :value="password"  v-model="form.password"  @input="e => updateField('password', e)" type="password" placeholder="Password" />
                        </div>
                        <div class="field input-field">
                            <input :value="confirmPassword" v-model="form.confirmPassword"  @input="e => updateField('confirmPassword', e)" type="password" placeholder="Confirm Password" />
                        </div>
                        <div class="field input-field">
                            <button @click="submitForm">Register</button>
                        </div>
                    </form>
                    <div class="form-link">
                        <a href="/login"><p class="switch-link">Already have an account? Log in</p></a>
                    </div>
                    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
                </div>
            </div>
        </div>
    </section>
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
