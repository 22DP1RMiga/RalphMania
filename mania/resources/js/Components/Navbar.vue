<script>
import { ref } from 'vue';
const isDropdownOpen = ref(false); // Track whether the dropdown is open
</script>

<template>
    <nav class="upper_menu">   
        <p>j</p>
        <!-- LEFT SIDE ICON -->
        <img src="C:/xampp/htdocs/mania/mania/public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
        <img src="C:/xampp/htdocs/mania/mania/public/img/name_logo.png" class="namelogo">

        <!-- HAMBURGER MENU ICON (Visible on small screens) -->
        <div class="hamburger-menu" @click="isDropdownOpen = !isDropdownOpen">
            <i class="fa-solid fa-bars"></i> <!-- Hamburger icon -->
        </div>

        <!-- BUTTONS IN THE MIDDLE -->
        <div class="button-container">
            <a href="/"><button class="centered_button"><i class="fa-solid fa-house"></i> Home</button></a>
            <a href="/about"><button><i class="fa-regular fa-user"></i> About</button></a>
            <a href="/contacts"><button><i class="fa-solid fa-phone"></i> Contacts</button></a>
            <a href="/shop" target="_blank" rel="noopener noreferrer"><button><i class="fa-solid fa-shop"></i> Shop</button></a>
        </div>

        <!-- RIGHT SIDE ICON -->
        <img src="C:/xampp/htdocs/mania/mania/public/img/YT_logo.png" class="YT_logo">
    </nav>
</template>

<style scoped>
body {
    background-image: url('C:/xampp/htdocs/mania/mania/public/img/Coder_RoltonsLV.png');
    margin: 0;
}

.apraksts {
    font-size: 15px;
    text-align: center;
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
    z-index: 10; /* Higher z-index to render on top */
    top: 0;
    left: 0;
    width: 100%;

    button {
        font-family: cursive;
        font-weight: bold;
    }

    .namelogo {
        height: 35px;
        width: max;
    }
}

.RoltonsLV_Icon, .YT_logo {
    max-width: 50px; /* Set the size for the images */
    padding-right: 10px;
    padding-left: 5px;
    height: auto;
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
    top: 50px;
    left: 0;
    right: 0;
    background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 0;
    width: 100%;
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
    body { background-image: url('C:/xampp/htdocs/mania/mania/public/img/Coder_RoltonsLV.png'); }
}

</style>
