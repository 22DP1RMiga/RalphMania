<script setup>
import Navbar from '../Components/Navbar.vue'
import Footer from '../Components/Footer.vue'
import ContactBox from '../Components/ContactBox.vue'
import { ref } from 'vue';
import { Link, usePage } from "@inertiajs/vue3";
import {Head} from "@inertiajs/vue3";

const page = usePage();
const user = page.props.auth.user;
const isDropdownOpen = ref(false);

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const handleSent = () => {
    console.log('Message sent!')
}
</script>

<template>
    <Head title="RalphMania || CONTACTS" />
    <Navbar />

    <div class="contact-box">
        <div class="contact-wrapper">
            <img src="../../../public/img/RoltonsLV_Icon.png" class="RoltonsLV_Icon">
            <img src="../../../public/img/name_logo.png" class="Name_Logo">
            <!-- <h1>Contact RoltonsLV</h1> -->

            <div v-if="user">
                <ContactBox :userEmail="user.email" @message-sent="handleSent" />
            </div>

            <p v-else class="note-for-login">Please log in to contact RoltonsLV.</p>
        </div>
    </div>
    
    <!-- <div class="contact-wrapper">
        <h1>Contact RoltonsLV</h1>

        <div v-if="user">
            <ContactBox :userEmail="user.email" @message-sent="handleSent" />
        </div>

        <p v-else class="note-for-login">Please log in to contact RoltonsLV.</p>
    </div> -->

    <Footer />
</template>

<style scoped>
/*body {
    background-image:
        url('../../../public/img/Hostage_Adventure.png'),
        linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
}*/

.contact-box {
    max-width: 500px;
    margin: auto;
    display: flex;
    flex-direction: column;
    padding: 70px 0;
}

.contact-wrapper {
    padding: 30px;
    text-align: center;
    background: linear-gradient(to bottom, rgb(229, 112, 112), rgb(143, 69, 69));
    border-radius: 8px;
    outline: solid 2px darkred;
}

.note-for-login {
    color: white;
}

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
    justify-self: center;
}
</style>
