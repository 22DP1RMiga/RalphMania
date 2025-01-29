<script>
</script>

<template>
    <nav class="improvision">

        <!--ABOUT TITLE-->
        <div class="about-title-box">
            <img src="../../public/img/about_title.png" class="about-title">
        </div>

        <div class="main-part">
            <p>Founded in 2024 by RoltonsLV. He is a Latvian YouTuber with more than 200 subscribers</p>
            <p>who decided to create a platform to help fans discover something more about his content creation.</p>
            <br>
            <p>This will be the official site</p>
        </div>
    </nav>
</template>

<style scoped>
    .main-part {
        color: white;
    }

    .about-title {
        display: block;
        margin: 0 auto;
        height: 70px;
        width: max;
    }

    .about-title-box {
        display: inline-block; /* Ensures the box wraps around the content only */
        background: linear-gradient(to bottom, rgba(191, 21, 21), rgb(224, 62, 62));
        padding: 10px;
        border-radius: 5px;
        outline: 3px solid black;
    }

    .improvision {
        text-align: center; /* Centers the entire box horizontally on the page */
        margin-top: 300px;
        position: relative;
        z-index: 10;
    }
</style>