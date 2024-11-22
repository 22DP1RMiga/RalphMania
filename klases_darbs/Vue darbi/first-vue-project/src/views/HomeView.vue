<template>
  <main>

    <h1 class="gametitle"> Welcome to Cookie Clicker!</h1>

    <h1>{{ score }}</h1>

    <div class="cookie">
      <img class="cookie"
      :class="{ bounce: isBouncing }" 
      src="https://img.freepik.com/free-photo/chocolate-chip-cookies-isolated-white-background-ai-generative_123827-24072.jpg?semt=ais_hybrid"
      @click="handleClickEvent"
      >
    </div>
  </main>
</template>

<script>
export default {
  data () {
    return {
      score: 0,
    }
  },

  methods: {
    handleClickEvent() {
      this.score += 1;
    }
  }
}
</script>

<!-- stils tikai šim failam. Citos vārdos: private style -->
<style scoped>
* {
  font-family: cursive;
}

main {
  width: 100vw;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

img {
  width: 400px;
  object-fit: cover;
}

.gametitle {
  font-size: 50px;
}

</style>