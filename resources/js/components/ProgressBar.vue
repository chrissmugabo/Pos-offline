<template>
  <div class="p-container" :style="`background-color: ${bgColor}`">
    <div class="progress-bar"></div>
  </div>
</template>
<script>
export default {
  name: "ProgressBar",
  props: {
    bgColor: {
      type: String,
      default: "#f77c29",
    },
  },
};
</script>
<style scoped>
.p-container {
  width: 100%;
  height: 5px;
  position: relative;
  overflow: hidden;
  border-radius: 0.25rem;
  opacity: 0.8;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}
.progress-bar {
  animation: indeterminate 2s linear 1s infinite;
  position: absolute;
  width: 10%;
  height: 100%;
  background: linear-gradient(
    to right,
    #aaa 0%,
    #bbb 25%,
    #ccc 50%,
    #ddd 75%,
    #eee 100%
  );
}
@keyframes indeterminate {
  0% {
    left: -100%;
    width: 20%;
  }
  25% {
    width: 40%;
  }
  50% {
    width: 60%;
  }
  75% {
    width: 40%;
  }
  100% {
    left: 100%;
    width: 30%;
  }
}
</style>
