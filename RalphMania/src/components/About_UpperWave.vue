<script>
</script>

<template>
    <!-- UPPER STYLED PICTURE -->
    <nav class="upperwave">
        <img src="../../public/img/AboutPage12.png" class="aboutpage">
    </nav>
</template>

<style scoped>
    /* Ensure there are no gaps */
    html, body {
        margin: 0;
        padding: 0;
        box-sizing: border-box; /* Includes padding and border in element size calculations */
    }

    .upperwave {
        position: fixed; /* Ensure it stays at the top and doesn't move with scroll */
        top: 0;
        left: 0;
        width: 100%; /* Span the entire width of the screen */
        height: auto; /* Allow the height to adjust proportionally */
        z-index: 5; /* Ensure it's behind higher z-index elements */
    }

    .aboutpage {
        width: 100%; /* Image spans the full width */
        height: auto; /* Maintain aspect ratio */
    }
</style>