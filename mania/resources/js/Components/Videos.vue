-- OLD VIDEOS.VUE

<script setup>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const user = usePage().props.auth.user

const videos = [
  { id: 1, youtubeId: 'GIRJISwkB_w', title: 'a funny color spam solution - THE PART' },
  { id: 2, youtubeId: 'RCu-FQoDTUo', title: 'When Magpipe joins Australind.. and cooks gud (GP by Me and @Kcool.101)' },
  { id: 3, youtubeId: 'JOa6WAF8PVM', title: '"COME UP" by Team Epil // (NCS Gauntlet Contest)' },
  // Duplicated for demo
  { id: 4, youtubeId: 'k_hNSKwB9nA', title: '[NCS DAILY] "Placid Ivy Grove" by @chutruongwaifu' },
  { id: 5, youtubeId: 'Yo-p0cY23sM', title: '"hostage adventure" by truongwf // SPEEDRUN SECOND BEST 🥈 (0:12:762)' },
  { id: 6, youtubeId: 'a0NmZw_XFGE', title: '[PREVIEW] "COME UP" by Team Epil // (NCS Gauntlet Contest)' },
]

const displayedVideos = user ? videos : videos.slice(0, 3)

const userReviews = ref({})

onMounted(async () => {
  if (user) {
    // Optionally, fetch user reviews from API
    const res = await axios.get('/api/reviews');
    userReviews.value = res.data;
  }
})

async function rate(videoId, stars) {
  if (!user) return
  userReviews.value[videoId] = stars
  await axios.post('/api/reviews', { video_id: videoId, stars })
}
</script>

<template>
    <div class="flex-container">
        <div v-for="video in displayedVideos" :key="video.id" class="video-card">
            <!-- Videos -->
            <iframe
                class="video-iframe"
                width="480"
                height="240"
                :src="`https://www.youtube.com/embed/${video.youtubeId}`"
                frameborder="0"
                allowfullscreen
            ></iframe>

            <!-- Video titles -->
            <div class="video-title">{{ video.title }}</div>

            <!-- Star rates -->
            <div v-if="user" class="stars">
                <span v-for="star in 5" :key="star" @click="rate(video.id, star)">
                <i
                    class="fa-star"
                    :class="star <= (userReviews[video.id] || 0) ? 'fas' : 'far'"
                    style="color: gold; cursor: pointer;"
                ></i>
                </span>
            </div>
        </div>
  </div>


    <!-- FOR THE LATEST VIDEO -->
    <!-- <div class="flex-container">
        <a href="https://www.youtube.com/watch?v=GIRJISwkB_w">
            <button>
                <div class="content">
                    <div class="spacing">
                        a funny color spam solution - THE PART
                    </div>
                    <img src="../../../public/img/Tran La Lout.png" alt="latest">
                </div>
            </button>
        </a> -->

        <!-- FOR THE 2ND LATEST VIDEO -->
        <!-- <a href="https://www.youtube.com/watch?v=RCu-FQoDTUo">
            <button>
                <div class="content">
                    <div class="spacing">
                        When Magpipe joins Australind.. and cooks gud (GP by Me and @Kcool.101)
                    </div>
                    <img src="../../../public/img/Magpipe Australind.png" alt="2nd latest">
                </div>
            </button>
        </a> -->

        <!-- FOR THE 3RD LATEST VIDEO -->
        <!-- <a href="https://www.youtube.com/watch?v=JOa6WAF8PVM">
            <button>
                <div class="content">
                    <div class="spacing">
                        "COME UP" by Team Epil // (NCS Gauntlet Contest)
                    </div>
                    <img src="../../../public/img/COME UP.png" alt="3rd latest">
                </div>
            </button>
        </a>
    </div> -->
    <!-- END OF THE FLEX CONTAINER -->
</template>

<style scoped>
.flex-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 16px;
    margin-top: 20px;
    color: white;
}

.video-card {
    flex: 1 1 30%;
    max-width: 32%;
    box-sizing: border-box;
    z-index: 100;
}

.video-iframe {
    /*width: 100%;
    height: 180px;*/
    border: none;
    border-radius: 10px;
    outline: 2px solid black;
    justify-self: center;
}

.video-title {
    font-size: 16px;
    font-weight: bold;
    margin-top: 8px;
    text-align: center;
}

.video-title:hover {
    text-decoration: underline;
    cursor: pointer;
}

.fa-star {
    font-size: 20px;
    margin-right: 5px;
}

.stars {
    display: flex;
    justify-content: center;
    margin-top: 8px;
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
    /*-webkit-line-clamp: 2;  Limits to 2 lines */
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
