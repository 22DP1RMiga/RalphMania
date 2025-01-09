<script>
</script>

<template>
    <nav class="upper_menu">
        <!-- LEFT SIDE ICON -->
        <img src="..\..\public\img\RoltonsLV_Icon.png" class="RoltonsLV_Icon">
        <p class="upper_title">RALPHMANIA</p>
        <!-- BUTTONS IN THE MIDDLE -->
        <div class="button-container">
            <a href="../views/HomeView.vue"><button class="centered_button"><i class="fa-solid fa-house"></i> Home</button></a>
            <a href="../views/AboutView.vue"><button><i class="fa-regular fa-user"></i> About</button></a>
            <a href="../Contacts/Contacts.html"><button><i class="fa-solid fa-phone"></i> Contacts</button></a>
        </div>

        <!-- RIGHT SIDE ICON -->
        <img src="..\..\public\img\YT_logo.png" class="YT_logo">
    </nav>
</template>

<style scoped>
    body {
        background-image: url('../RalphMania/public/img/Coder_RoltonsLV.png');
    }

    .apraksts {
        font-size: 15px;
        text-align: center;
    }

    .upper_menu {
        background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
        padding: 10px 0;
        outline: 2px solid black;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed; /* Keeps it at the top */
        top: 0;
        left: 0;
        z-index: 1000; /* Keeps it above other elements */
        width: 100%;
        
        button {
            font-family: cursive;
            font-weight: bold;
        }

        .upper_title {
            font-family: Arial, Helvetica, sans-serif;
            color: rgb(255, 196, 196);
            font-weight: bold;
            letter-spacing: 2px;
            /* font-size: 24px; */

            text-shadow:
                -1px -1px 0 black, /* Top-left */
                1px -1px 0 black,  /* Top-right */
                -1px 1px 0 black,  /* Bottom-left */
                -1px 1px 0 black,  /* Bottom-left */
                1px 1px 0 black;   /* Bottom-right */
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

    @media (max-width: 800px) {
        body { background-image: url('Coder_RoltonsLV.png'); }
        .column {
            flex: 1 1 calc(50% - 5px);
            max-width: 100%;
        }
        .flex-container {
            flex: 1 1 calc(50% - 5px);
            max-width: 100%;
        }

    }

    @media (max-width: 600px) {
        body { background-image: url('Coder_RoltonsLV.png'); }
        .column {
            flex: 1 1 calc(100% - 5px);
            max-width: 100%;
        }
        .flex-container {
            flex: 1 1 calc(100% - 5px);
            max-width: 100%;
        }
    }
</style>