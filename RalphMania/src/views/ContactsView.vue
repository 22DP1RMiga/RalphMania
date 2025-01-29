<script setup>
import { ref, onMounted } from 'vue';
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
import router from '../router';

const username = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const isLoggedIn = ref(false);
const currentUser = ref(null);
const errorMessage = ref('');

const saveUser = async () => {
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
    let users = JSON.parse(localStorage.getItem('users') || '[]');
    let foundUser = users.find(u => u.username === user && u.password === pass);
    if (foundUser) {
        isLoggedIn.value = true;
        currentUser.value = foundUser;
        localStorage.setItem('currentUser', JSON.stringify(foundUser));
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
    <main>
        <div v-if="!isLoggedIn" class="form-container">
            <h2>Create an Account / Log In</h2>
            <input type="text" v-model="username" placeholder="Username" required>
            <input type="email" v-model="email" placeholder="Email" required>
            <input type="password" v-model="password" placeholder="Password" required>
            <input type="password" v-model="confirmPassword" placeholder="Confirm Password" required>
            <button @click="saveUser">Register</button>
            <button @click="loginUser(username, password)">Log In</button>
            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        </div>
        <div v-else class="welcome-container">
            <h2>Welcome, {{ currentUser.username }}!</h2>
            <button @click="logoutUser">Log Out</button>
        </div>
    </main>
    <Footer />
</template>

<style scoped>
.form-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
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