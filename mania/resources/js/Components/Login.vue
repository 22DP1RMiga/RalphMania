
<script setup>
    import { ref } from 'vue';

    const userInput = ref('');
    const password = ref('');

    const emit = defineEmits(['login']);

    const submitLogin = () => {
        emit('login', {
            userInput: userInput.value,
            password: password.value
        });
    };
</script>

<template>
    <main>
        <div class="form-container">
            <div class="auth-box">
                <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
                <h2>Log In</h2>

                <input v-model="userInput" type="text" placeholder="Username or Email" />
                <input v-model="password" type="password" placeholder="Password" />

                <button @click="submitLogin">Log In</button>
                <a href="/register"><p class="switch-link">Don't have an account? Register</p></a>
<!--                <p v-if="errorMessage" class="error">{{ errorMessage }}</p>-->
            </div>
        </div>
    </main>
</template>


<style scoped>
    .form-container {
        height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #420d65;
        column-gap: 30px;
    }

    .link:hover {
        cursor: pointer;
    }
    .form{
        position: absolute;
        max-width: 430px;
        width: 100%;
        padding: 30px;
        border-radius: 6px;
        background: #FFF;
    }
    .form.signup{
        opacity: 0;
        pointer-events: none;
    }
    .forms.show-signup .form.signup{
        opacity: 1;
        pointer-events: auto;
    }
    .forms.show-signup .form.login{
        opacity: 0;
        pointer-events: none;
    }
    header{
        font-size: 28px;
        font-weight: 600;
        color: #232836;
        text-align: center;
    }
    form{
        margin-top: 30px;
    }
    .form .field{
        position: relative;
        height: 50px;
        width: 100%;
        margin-top: 20px;
        border-radius: 6px;
    }
    .field input,
    .field button{
        height: 100%;
        width: 100%;
        border: none;
        font-size: 16px;
        font-weight: 400;
        border-radius: 6px;
    }
    .field input{
        outline: none;
        padding: 0 15px;
        border: 1px solid#CACACA;
    }
    .field input:focus{
        border-bottom-width: 2px;
    }
    .eye-icon{
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        font-size: 18px;
        color: #8b8b8b;
        cursor: pointer;
        padding: 5px;
    }
    .field button{
        color: #fff;
        background-color: #420d65;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .field button:hover{
        background-color: #420d65;
    }
    .form-link{
        text-align: center;
        margin-top: 10px;
    }
    .form-link span,
    .form-link a{
        font-size: 14px;
        font-weight: 400;
        color: #232836;
    }
    .form a{
        color: #420d65;
        text-decoration: none;
    }
    .form-content a:hover{
        text-decoration: underline;
    }
    .line{
        position: relative;
        height: 1px;
        width: 100%;
        margin: 36px 0;
        background-color: #d4d4d4;
    }
    .line::before{
        content: 'Or';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #FFF;
        color: #8b8b8b;
        padding: 0 15px;
    }
    .media-options a{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    a.facebook{
        color: #fff;
        background-color: #420d65;
    }
    a.facebook .facebook-icon{
        height: 28px;
        width: 28px;
        color: #420d65;
        font-size: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
    }
    .facebook-icon,
    img.google-img{
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
    }
    img.google-img{
        height: 20px;
        width: 20px;
        object-fit: cover;
    }
    a.google{
        border: 1px solid #CACACA;
    }
    a.google span{
        font-weight: 500;
        opacity: 0.6;
        color: #232836;
    }

    @media screen and (max-width: 400px) {
        .form{
            padding: 20px 10px;
        }

    }
</style>
