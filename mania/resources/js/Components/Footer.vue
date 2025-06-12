<template>
    <footer class="footer">
        <!-- text -->
        <div class="text-container">
            <p class="moved-text">© 2025 by RalphMania</p>
        </div>

        <!-- BUTTONS IN THE MIDDLE -->
        <div class="button-container">
            <a href="https://www.youtube.com/@RoltonsLV"><button class="centered_button"><i class="fa-brands fa-youtube"></i></button></a>
            <a href="https://www.instagram.com/ralfsmigals/"><button><i class="fa-brands fa-instagram"></i></button></a>
            <a href="https://github.com/22DP1RMiga"><button class="centered_button"><i class="fa-brands fa-github"></i></button></a>
            <a href="https://discord.gg/4Hzsp42ugg"><button class="centered_button"><i class="fa-brands fa-discord"></i></button></a>
        </div>
    </footer>
</template>

<style scoped>
.footer {
    background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
    padding: 5px;
    outline: 1px solid black;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border-top: 2px solid #000000;
    width: 100%;
    margin-top: auto; /* This is key for sticky footer */
}

.text-container {
    margin-bottom: 10px;  /* Space between text and buttons */
}

.moved-text {
    text-align: center; /* Center the text */
    font-size: 16px; /* Adjust text size */
    color: white;
}

.button-container {
    display: flex;
    justify-content: center; /* Center the buttons horizontally */
    align-items: center; /* Center the buttons vertically */
}

button {
    background: none; /* Remove background */
    color: white; /* Text color */
    padding: 0; /* Remove padding */
    border: none; /* Remove border */
    cursor: pointer; /* Keep pointer on hover */
    margin: 2px;
    font-size: 20px; /* Text size */
    text-decoration: underline; /* Optional: underline the text for a link effect */
}

button:hover {
    color: lightgray; /* Change color on hover */
}
</style>
