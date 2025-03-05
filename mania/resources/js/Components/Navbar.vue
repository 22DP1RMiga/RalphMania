<script setup>
import { ref, defineProps, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    currentUser: Object
});

// States for dropdowns
const userDropdownOpen = ref(false);
const isDropdownOpen = ref(false);

// Toggle User Dropdown
const toggleUserDropdown = () => {
    userDropdownOpen.value = !userDropdownOpen.value;
};

// Toggle Hamburger Menu Dropdown
const toggleHamburgerMenu = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
    const userDropdown = document.querySelector('.user-dropdown');
    const userIcon = document.querySelector('.fa-circle-user');
    const dropdownMenu = document.querySelector('.dropdown');
    const hamburgerIcon = document.querySelector('.hamburger-menu');

    if (
        userDropdownOpen.value &&
        userDropdown &&
        !userDropdown.contains(event.target) &&
        !userIcon.contains(event.target)
    ) {
        userDropdownOpen.value = false;
    }

    if (
        isDropdownOpen.value &&
        dropdownMenu &&
        !dropdownMenu.contains(event.target) &&
        !hamburgerIcon.contains(event.target)
    ) {
        isDropdownOpen.value = false;
    }
};

// Add event listener when component is mounted
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

// Remove event listener when component is unmounted
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<!--WEB BUILD (TEMPLATE)-->
<template>
    <nav class="upper_menu">
        <!-- LEFT SIDE ICON -->
        <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
        <img src="../../../public/img/name_logo.png" class="namelogo">

        <!-- HAMBURGER MENU ICON -->
        <div class="hamburger-menu" @click="isDropdownOpen = !isDropdownOpen">
            <i class="fa-solid fa-bars"></i>
        </div>

        <!-- DROPDOWN MENU (Visible when the hamburger is clicked) -->
        <span v-if="isDropdownOpen" class="dropdown">
            <ul>
                <li>
                    <a href="/"><i class="fa-solid fa-house"></i> Home</a>
                    <a href="/about"><i class="fa-regular fa-user"></i> About</a>
                    <a href="/contacts"><i class="fa-solid fa-phone"></i> Contacts</a>
                    <a href="/shop" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-shop"></i> Shop</a>
                </li>
            </ul>
        </span>

        <!-- BUTTONS IN THE MIDDLE (Visible on larger screens) -->
        <div class="button-container">
            <a href="/"><button class="centered_button"><i class="fa-solid fa-house"></i> Home</button></a>
            <a href="/about"><button><i class="fa-regular fa-user"></i> About</button></a>
            <a href="/contacts"><button><i class="fa-solid fa-phone"></i> Contacts</button></a>
            <a href="/shop" target="_blank" rel="noopener noreferrer"><button><i class="fa-solid fa-shop"></i> Shop</button></a>
        </div>

        <!-- RIGHT SIDE ICONS (YouTube + User Icon if logged in) -->
        <div class="right-side-icons">
            <img src="../../../public/img/YT_logo.png" class="YT_logo">

            <!-- USER ICON WITH DROPDOWN -->
            <div v-if="currentUser" class="user-icon-container">
                <!-- USER ICON (CLICK TO TOGGLE DROPDOWN) -->
                <i class="fa-solid fa-circle-user" @click="toggleUserDropdown"></i>

                <!-- DROPDOWN MENU -->
                <div v-if="userDropdownOpen" ref="dropdownRef" class="user-dropdown">
                    <p><strong>{{ currentUser.username }}</strong></p>
                    <p>{{ currentUser.email }}</p>
                    <button @click="$emit('logout')">Log Out</button>
                </div>
            </div>
        </div>
    </nav>
</template>

<!--STYLE-->
<style scoped>
body {
    background-image: url('../../../public/img/Coder_RoltonsLV.png');
    margin: 0;
}

.upper_menu {
    background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
    padding: 5px;
    outline: 1px solid black;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #000000;
    position: fixed;
    z-index: 1000; /* Higher z-index to render on top */
    top: 0;
    left: 0;
    width: 100%;
}

.namelogo {
    height: 35px;
    width: max;
}

.RoltonsLV_Icon, .YT_logo {
    max-width: 50px; /* Set the size for the images */
    padding-right: 10px;
    padding-left: 5px;
    height: auto;
}

/* Right-side icons container */
.right-side-icons {
    display: flex;
    align-items: center;
}

.right-side-icons i {
    font-size: 35px;
    //cursor: pointer;
}

.right-side-icons i:hover {
    color: rgb(53, 53, 53);
}

.user-icon-container {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.user-dropdown {
    position: absolute;
    top: 60px; /* Adjust based on icon size */
    right: 0;
    background: white;
    color: black;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    min-width: 150px;
    text-align: center;
    z-index: 1000;
}

.user-dropdown p {
    margin: 5px 0;
}

.user-dropdown button {
    background: firebrick;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    margin-top: 5px;
    border-radius: 5px;
    width: 100%;
}

.user-dropdown button:hover {
    background: darkred;
}

.button-container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-grow: 1; /* Allows the button container to take up space in the middle */
    padding-right: 200px;
}

button {
    background: linear-gradient(to bottom, rgb(229, 112, 112), rgb(143, 69, 69));
    color: black;
    padding: 5px 20px;
    cursor: pointer;
    margin: 2px;
    font-size: 20px;
    border-radius: 10px;
    outline: 1px solid black;
}

button:hover {
    transform: scale(1.1);
    transition: transform 0.1s ease;
    animation: hoverUp 0.5s forwards;
    box-shadow: 10px 10px 15px rgba(0, 0 , 0, 0.6);
}

@keyframes hoverUp {
    0% {
        transform: translateY(0);
    } 100% {
          transform: translateY(-10px) scale(1.1)  ;
      }
}

/* Hamburger menu for small screens */
.hamburger-menu {
    display: none;
    cursor: pointer;
    font-size: 30px;
    color: white;
}

/* Dropdown menu for small screens */
.dropdown {
    position: absolute;
    top: 40px;
    left: 0;
    background: white;
    color: black;
    padding: 10px;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.5);
    min-width: 150px;
    z-index: 1000;
    //right: 0;
    //background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
    //display: flex;
    //flex-direction: column;
    //align-items: center;
    //width: 100%;
}

.dropdown ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.dropdown ul li {
    padding: 10px;
}

.dropdown li a {
    color: black;
    text-decoration: none;
    font-size: 16px;
    //font-weight: bold;
    //display: block;
    //padding: 10px;
}

.dropdown li:hover {
    //background: rgba(255, 255, 255, 0.2);
    background: rgba(0, 0, 0, 0.1);
}

/* Show the hamburger menu on small screens */
@media screen and (max-width: 800px) {
    .button-container {
        display: none; /* Hide the default button container */
    }

    .hamburger-menu {
        display: block; /* Show the hamburger icon */
    }

    .dropdown a {
        margin: 10px 0;
    }

    /* Style the buttons inside the dropdown */
    .dropdown button {
        padding: 10px 20px;
        font-size: 18px;
    }
}

@media screen and (max-width: 600px) {
    body { background-image: url('../../../public/img/Coder_RoltonsLV.png'); }
}
</style>
