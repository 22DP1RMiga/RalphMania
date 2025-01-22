<template>
    <nav class="footer">
        <!-- text -->
        <div class="text-container">
            <p class="moved-text">© 2025 by RalphMania</p>
        </div>
        
        <!-- BUTTONS IN THE MIDDLE -->
        <div class="button-container">
            <a href="/"><button class="centered_button"><i class="fa-solid fa-house"></i> Home</button></a>
            <a href="/about"><button><i class="fa-regular fa-user"></i> About</button></a>
            <a href="/contacts"><button><i class="fa-solid fa-phone"></i> Contacts</button></a>
            <a href="/shop"><button><i class="fa-solid fa-shop"></i> Shop</button></a>
        </div>
    </nav>
</template>

<style scoped>
    .footer {
        background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
        padding: 5px;
        outline: 2px solid black;
        display: flex;
        flex-direction: column;     /* Stack the items vertically */
        justify-content: center;    /* Center content horizontally */
        align-items: center;        /* Center content vertically */
        border-top: 2px solid #000000;
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
    }

    .text-container {
        margin-bottom: 10px;  /* Space between text and buttons */
    }

    .moved-text {
        text-align: center;   /* Center the text */
        font-size: 16px;       /* Adjust text size */
    }

    .button-container {
        display: flex;
        justify-content: center; /* Center the buttons horizontally */
        align-items: center;     /* Center the buttons vertically */
    }

    button {
        color: black;
        padding: 5px 20px;
        cursor: pointer;
        margin: 2px;
        font-size: 20px;
        border-radius: 10px;
    }
</style>
