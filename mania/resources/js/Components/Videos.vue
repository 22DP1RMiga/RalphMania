<script>

</script>

<template>
    <!-- FOR THE LATEST VIDEO -->
    <div class="flex-container">
        <a href="https://www.youtube.com/watch?v=GIRJISwkB_w">
            <button>
                <div class="content">
                    <div class="spacing">
                        a funny color spam solution - THE PART
                    </div>
                    <img src="../../../public/img/Tran La Lout.png" alt="latest">
                </div>
            </button>
        </a>

        <!-- FOR THE 2ND LATEST VIDEO -->
        <a href="https://www.youtube.com/watch?v=RCu-FQoDTUo">
            <button>
                <div class="content">
                    <div class="spacing">
                        When Magpipe joins Australind.. and cooks gud (GP by Me and @Kcool.101)
                    </div>
                    <img src="../../../public/img/Magpipe Australind.png" alt="2nd latest">
                </div>
            </button>
        </a>

        <!-- FOR THE 3RD LATEST VIDEO -->
        <a href="https://www.youtube.com/watch?v=JOa6WAF8PVM">
            <button>
                <div class="content">
                    <div class="spacing">
                        "COME UP" by Team Epil // (NCS Gauntlet Contest)
                    </div>
                    <img src="../../../public/img/COME UP.png" alt="3rd latest">
                </div>
            </button>
        </a>

        <!-- END OF THE FLEX CONTAINER -->
    </div>
</template>

<style scoped>
.flex-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
}

button {
    width: 500px; /* Fixed width */
    height: 350px; /* Fixed height */
    background: linear-gradient(to bottom, rgb(234, 231, 231), rgb(137, 137, 137));
    color: black;
    cursor: pointer;
    font-size: 18px;
    border-radius: 10px;
    outline: 2px solid black;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between; /* Pushes content apart */
    padding: 15px;
    text-align: center;
    overflow: hidden;   /* Prevents content overflow */
}

button:hover {
    transform: scale(1.05);
    transition: 0.2s;
}

.content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 100%;
}

.spacing {
    font-size: 16px;
    font-family: cursive;
    font-weight: bold;
    margin-bottom: 10px;
    max-height: 3.5em; /* Roughly 2 lines */
    /*word-wrap: break-word;
    /white-space: normal;*/
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Limits to 2 lines */
    -webkit-box-orient: vertical;
}

img {
    width: 100%; /* Set image width */
    max-height: 100%;
    object-fit: cover; /* Ensures images are cropped properly */
    border-radius: 10px;
    outline: 2px solid black;
    margin-bottom: 10px;
}

/* RESPONSIVE DESIGN */
@media (max-width: 800px) {
    .flex-container {
        flex-direction: column;
    }
    button {
        width: 300px;
        height: 150px;
    }
}

@media (max-width: 600px) {
    button {
        width: 260px;
        height: 130px;
    }
}
</style>
