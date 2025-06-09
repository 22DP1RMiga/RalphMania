<script>

</script>

<template>
    <div class="flex-container">

        <!-- FIRST PRODUCT -->
        <a href="">
            <button>
                <img src="../../../public/img/Shirt.png">
            </button>
            <p class="title">T-shirt, White, Limited Edition!</p>
            <p class="price">15.00€</p>
        </a>

        <!-- SECOND PRODUCT -->
        <a href="">
            <button>
                <img src="../../../public/img/Bags.png">
            </button>
            <p class="title">Leather Bag, White, Limited Edition!</p>
            <p class="price">5.00€</p>
        </a>

        <!-- THIRD PRODUCT -->
        <a href="">
            <button>
                <img src="../../../public/img/Stationery.png">
            </button>
            <p class="title">Personal Stationery, White, Limited Edition!</p>
            <p class="price">12.46€</p>
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
    height: 500px; /* Fixed height */
    color: black;
    cursor: pointer;
    font-size: 18px;
    border-radius: 10px;
    /* outline: 2px solid black; */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between; /* Pushes content apart */
    padding: 15px;
    text-align: center;
    overflow: hidden;   /* Prevents content overflow */
}

.title, .price {
    color: white;
    text-align: center;
}

button:hover {
    transform: scale(1.05);
    transition: 0.2s;
}

img {
    width: 100%; /* Set image width */
    max-height: 100%;
    /*object-fit: cover;  Ensures images are cropped properly */
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
