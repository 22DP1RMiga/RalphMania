<script setup>
import { ref, onMounted } from 'vue';
import Navbar from '../Components/Navbar.vue';
import Footer from '../Components/Footer.vue';
// import router from '../router';

const username = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const isLoggedIn = ref(false);
const currentUser = ref(null);
const errorMessage = ref('');
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Simple email regex

const saveUser = async () => {
    // if none of the spots are filled
    if (!username.value || !email.value || !password.value || !confirmPassword.value) {
        errorMessage.value = "All fields are required!";
        return;
    }

    // checks to see if the email is entered correctly by format
    if (!emailRegex.test(email.value)) {
        errorMessage.value = "Invalid email format!";
        return;
    }

    // if passwords don't match
    if (password.value !== confirmPassword.value) {
        errorMessage.value = "Passwords do not match!";
        return;
    }

    let users = JSON.parse(localStorage.getItem('users') || '[]');
    if (users.find(user => user.username === username.value)) {
        errorMessage.value = "Username already exists!";
        return;
    }

    users.push({ username: username.value, email: email.value, password: password.value });
    localStorage.setItem('users', JSON.stringify(users));
    loginUser(username.value, password.value);
};

const loginUser = (user, pass) => {
    // if nothing has been entered
    if (!user || !pass) {
        errorMessage.value = "Please enter both username and password!";
        return;
    }

    let users = JSON.parse(localStorage.getItem('users') || '[]');
    let foundUser = users.find(u => u.username === user && u.password === pass);

    // login validation process
    if (foundUser) {
        isLoggedIn.value = true;
        currentUser.value = foundUser;
        localStorage.setItem('currentUser', JSON.stringify(foundUser));
        errorMessage.value = ""; // Clear error message
    } else {
        errorMessage.value = "Invalid username or password!";
    }
};

const logoutUser = () => {
    isLoggedIn.value = false;
    currentUser.value = null;
    localStorage.removeItem('currentUser');
};

onMounted(() => {
    let storedUser = JSON.parse(localStorage.getItem('currentUser'));
    if (storedUser) {
        isLoggedIn.value = true;
        currentUser.value = storedUser;
    }
});
</script>

<template>
    <Navbar :currentUser="currentUser" />
    <main v-if="isLoggedIn">
        <div class="welcome-container">
            <h2>Welcome, {{ currentUser.username }}!</h2>
            <p>Contact the head of the company, RoltonsLV:</p>
            <p>Email: example@roltonslv.com</p>
            <p>Phone: +123456789</p>
            <button @click="logoutUser">Log Out</button>
        </div>
    </main>

    <main v-else>
        <br><br><br>
        <div v-if="!isLoggedIn" class="form-container">
            <div class="login-box">
                <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
                <h2 style="font-weight: bold;">Create an Account / Log In</h2>
                <input type="text" v-model="username" placeholder="Username" required>
                <input type="email" v-model="email" placeholder="Email" required>
                <input type="password" v-model="password" placeholder="Password" required>
                <input type="password" v-model="confirmPassword" placeholder="Confirm Password" required>
            </div>
            <button @click="saveUser">Register</button>
            <button @click="loginUser(username, password)">Log In</button>
            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        </div>

<!--        <div v-else class="welcome-container">-->
<!--            <h2>Welcome, {{ currentUser.username }}!</h2>-->
<!--            <button @click="logoutUser">Log Out</button>-->
<!--        </div>-->
    </main>
    <Footer />
</template>

<style scoped>

.login-box {
    background: linear-gradient(to bottom, rgb(229, 112, 112), rgb(143, 69, 69));
    border: #000000;
    border-radius: 10px;
    border-style: groove;
    height: 450px;
    width: 500px;
    justify-items: center;
}

.login-box input {
    border-radius: 10px;
    outline: 1px solid black;
}

.form-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.RoltonsLV_Icon {
    max-width: 150px; /* Set the size for the images */
    padding-right: 10px;
    padding-left: 5px;
    height: auto;
}
.welcome-container {
    text-align: center;
    margin-top: 20px;
}

input {
    display: block;
    margin: 10px 0;
    padding: 8px;
}

button {
    margin: 10px;
    padding: 8px 15px;
    background-color: firebrick;
    color: white;
    border: none;
    cursor: pointer;
}

.error {
    color: red;
    margin-top: 10px;
}
</style>
