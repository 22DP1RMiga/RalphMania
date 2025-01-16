<script>
</script>

<template>
    <nav class="upper_menu">
        <!-- LEFT SIDE ICON -->
        <img src="../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
        <img src="../../public/img/name_logo.png" class="namelogo">

        <!-- BUTTONS IN THE MIDDLE -->
        <div class="button-container">
<<<<<<< HEAD
            <a href="/"><button class="centered_button"><i class="fa-solid fa-house"></i> Home</button></a>
            <a href="/about"><button><i class="fa-regular fa-user"></i> About</button></a>
            <a href="/contacts"><button><i class="fa-solid fa-phone"></i> Contacts</button></a>
            <a href="/shop"><button><i class="fa-solid fa-shop"></i> Shop</button></a>
=======
            <a href="../views/HomeView.vue"><button class="centered_button"><i class="fa-solid fa-house"></i> Home</button></a>
            <a href="../views/AboutView.vue"><button><i class="fa-regular fa-user"></i> About</button></a>
            <a href="../Contacts/Contacts.html"><button><i class="fa-solid fa-phone"></i> Contacts</button></a>
            <a href="../Shop/Shop.html"><button><i class="fa-solid fa-shop"></i> Shop</button></a>
>>>>>>> 1add96311b2faf7fd55d0ae072b5ec8c241b9165
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

<<<<<<< HEAD
    .upper_menu {
        background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
        border-radius: 10px;
        padding: 5px;
        outline: 2px solid black;
        display: flex;
        justify-content: space-between;
        align-items: center;
        
        button {
            font-family: cursive;
            font-weight: bold;
        }

        .namelogo {
            height: 35px;
            width: max;
        }
    }
=======
.upper_menu {
    background: linear-gradient(to bottom, firebrick, rgb(116, 22, 22));
    border-radius: 10px;
    padding: 5px;
    outline: 2px solid black;
    display: flex;
    justify-content: space-between;
    align-items: center;
    
    button {
        font-family: cursive;
        font-weight: bold;
    }
}
>>>>>>> 1add96311b2faf7fd55d0ae072b5ec8c241b9165

    .RoltonsLV_Icon, .YT_logo {
        max-width: 50px; /* Set the size for the images */
        padding-right: 10px;
        padding-left: 5px;
        height: auto;
    }


<<<<<<< HEAD
    .button-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-grow: 1; /* Allows the button container to take up space in the middle */
        padding-right: 200px;
    }
=======
.button-container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-grow: 1; /* Allows the button container to take up space in the middle */
}
>>>>>>> 1add96311b2faf7fd55d0ae072b5ec8c241b9165

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
