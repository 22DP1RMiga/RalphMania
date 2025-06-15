<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const user = computed(() => usePage().props.auth.user)
</script>

<template>
    <nav class="improvision">
        <div class="moved_text">
            <h1 style="font-weight: bold;">
                Welcome<span v-if="user">, {{ user.name }}</span>!
            </h1>
            <br>
            <p>A place to see more of RoltonsLV the latvian YouTuber of Geometry Dash and more!</p>
            <a href="https://www.youtube.com/@RoltonsLV" style="text-decoration: underline;" class="yt_link">Click here to visit the channel</a>


            <p></p> <!-- FOR SPACES -->
        </div>
    </nav>
</template>

<style scoped>
    .improvision {
        background: linear-gradient(to bottom, rgb(229, 112, 112), rgb(143, 69, 69));
        margin-top: calc(5em);
        outline: 2px solid darkred;
        left: 0;
        font-family: cursive;
        padding: 20px;
        display: flex;
        justify-content: center; /* Centers content horizontally */
        align-items: center; /* Centers content vertically */
        text-align: center; /* Centers text */
        flex-direction: column; /* Stacks elements inside */
    }



    .moved_text h1 {
        font-size: 24px;
    }

    .yt_link:hover {
        color: #741616;
    }
</style>
