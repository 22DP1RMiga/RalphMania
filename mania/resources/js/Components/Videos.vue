<script>

</script>

<template>
    <!-- FOR THE LATEST VIDEO -->
    <div class="flex-container">
        <a href="https://www.youtube.com/watch?v=JOa6WAF8PVM">
            <button>
                <div>
                    <div class="spacing">
                        "COME UP" by Team Epil // (NCS Gauntlet Contest)
                    </div>
                    <img src="../../../public/img/COME UP preview.png" alt="latest" width="150px" height="100px">
                </div>
            </button>
        </a>

        <!-- FOR THE 2ND LATEST VIDEO -->
        <a href="https://www.youtube.com/watch?v=k_hNSKwB9nA">
            <button>
                <div>
                    <div class="spacing">
                        [NCS DAILY] "Placid Ivy Grove" by @chutruongwaifu
                    </div>
                    <img src="../../../public/img/Hostage_Adventure.png" alt="2nd latest">
                </div>
            </button>
        </a>

        <!-- FOR THE 3RD LATEST VIDEO -->
        <a href="https://www.youtube.com/watch?v=Yo-p0cY23sM">
            <button>
                <div>
                    <div class="spacing">
                        "hostage adventure" by truongwf // SPEEDRUN SECOND BEST 🥈 (0:12:762)
                    </div>
                    <img src="../../../public/img/PIG.png" alt="3rd latest" width="150px" height="100px">
                </div>
            </button>
        </a>

        <!-- END OF THE FLEX CONTAINER -->
    </div>
</template>

<style scoped>
.flex-container {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;    /* for responsive button wrapping purposes */
    justify-content: center;
    border-radius: 20px;
    margin-top: calc(2em - 10px);
}

.flex-container img {
    max-width: 90%;   /* Make the image scale inside the button */
    max-height: 150px; /* Set a max height for the image */
    height: auto;
    width: auto;
    border-radius: 10px;
    outline: 2px solid black;
}

.spacing {
    padding: 10px;
    font-size: 15px;
    font-family: cursive;
    font-weight: bold;
}

button {
    background: linear-gradient(to bottom, rgb(234, 231, 231), rgb(137, 137, 137));
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
    body { background-image: url('../../../public/img/Coder_RoltonsLV.png'); }
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
    body { background-image: url('../../../public/img/Coder_RoltonsLV.png'); }
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
