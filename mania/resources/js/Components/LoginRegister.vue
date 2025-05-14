<script setup>
import {onMounted, ref} from "vue";
import Navbar from "./Navbar.vue";

const username = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const isLoggedIn = ref(false);
const currentUser = ref(null);
const errorMessage = ref('');
const showRegister = ref(true); // Toggle between register and login box
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

// New input field for username OR email
const userInput = ref('');

const loginUser = () => {
    if (!userInput.value || !password.value) {
        errorMessage.value = "Please enter your username/email and password!";
        return;
    }

    let users = JSON.parse(localStorage.getItem('users') || '[]');
    let foundUser = users.find(u =>
        (u.username === userInput.value || u.email === userInput.value) &&
        u.password === password.value
    );

    if (foundUser) {
        isLoggedIn.value = true;
        currentUser.value = foundUser;
        localStorage.setItem('currentUser', JSON.stringify(foundUser));
        errorMessage.value = "";
    } else {
        errorMessage.value = "Invalid username/email or password!";
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
    <Navbar :currentUser="currentUser" @logout="logoutUser" />

    <main v-if="isLoggedIn">
        <div class="welcome-container">
            <h2 style="font-weight: bold;">Welcome, {{ currentUser.username }}!</h2>
            <br>
            <p>Contact the head of the company, RoltonsLV:</p>
            <p>Email: head@roltonslv.com</p>
            <p>Phone: +371 29203658</p>
            <!--            <button @click="logoutUser">Log Out</button>-->
        </div>
    </main>

    <main v-else>
        <br><br><br>
        <div class="form-container">
            <!-- REGISTER BOX -->
            <div v-if="showRegister" class="auth-box">
                <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
                <h2>Register</h2>
                <input type="text" v-model="username" placeholder="Username" required>
                <input type="email" v-model="email" placeholder="Email" required>
                <input type="password" v-model="password" placeholder="Password" required>
                <input type="password" v-model="confirmPassword" placeholder="Confirm Password" required>
                <button @click="saveUser">Register</button>
                <p @click="showRegister = false" class="switch-link">Own an account? Log in</p>
            </div>

            <!-- LOGIN BOX -->
            <div v-else class="auth-box">
                <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
                <h2>Log In</h2>
                <input type="text" v-model="userInput" placeholder="Username or Email" required>
                <input type="password" v-model="password" placeholder="Password" required>
                <button @click="loginUser(username, password)">Log In</button>
                <p @click="showRegister = true" class="switch-link">Don't have an account? Register</p>
            </div>

            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        </div>
    </main>
</template>

<style>
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
    justify-self: center;
}
.welcome-container {
    text-align: center;
    margin-top: 20px;
    margin-top: calc(5em);

    h2 {
        color: white;
        font-size: 24px;
    }

    p {
        color: white;
    }
}

input {
    display: block;
    margin: 10px 0;
    padding: 8px;
}

/* AUTH BOX (Register & Login) */
.auth-box {
    background: linear-gradient(to bottom, rgb(229, 112, 112), rgb(143, 69, 69));
    border-radius: 10px;
    border: 2px solid black;
    width: 400px;
    padding: 20px;
    text-align: center;
    box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.5);
    align-items: center;
    align-content: center;
    justify-content: center;
    justify-items: center;
}

.auth-box input {
    width: 90%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 10px;
    border: 1px solid black;
    align-self: center;
    justify-self: center;
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
    font-size: 16px;
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
