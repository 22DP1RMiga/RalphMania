<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
                <img src="../../../public/img/name_logo.png" class="Name_Logo">
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>

<style>
    .RoltonsLV_Icon {
        max-width: 200px; /* Set the size for the images */
        padding-right: 10px;
        padding-left: 5px;
        height: auto;
        justify-self: center;
    }

    .Name_Logo {
        max-width: 400px; /* Set the size for the images */
        padding-right: 10px;
        padding-left: 5px;
        height: auto;
    }
</style>
