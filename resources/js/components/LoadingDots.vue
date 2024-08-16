<template>
  <div class="loading text-center">
    <div class="load-circle1"></div>
    <div class="load-circle2"></div>
    <div class="load-circle3"></div>
    <div class="load-circle4"></div>
    <div class="load-circle5"></div>
  </div>
</template>
<script>
export default {
  name: "LoadingDots",
};
</script>
<style scoped>
.loading {
  /* margin-top: 20px; */
}
.load-circle1,
.load-circle2,
.load-circle3,
.load-circle4,
.load-circle5 {
  width: 8px;
  height: 8px;
  background: grey;
  display: inline-block;
  border-radius: 20px;
  -webkit-animation: loader 1.5s infinite;
  animation: loader 1.5s infinite;
}
.load-circle2 {
  -webkit-animation-delay: 0.25s;
  animation-delay: 0.25s;
}
.load-circle3 {
  -webkit-animation-delay: 0.5s;
  animation-delay: 0.5s;
}
.load-circle4 {
  -webkit-animation-delay: 0.75s;
  animation-delay: 0.75s;
}
.load-circle5 {
  -webkit-animation-delay: 1s;
  animation-delay: 1s;
}
@-webkit-keyframes loader {
  from {
    opacity: 1;
    scale: 1;
  }
  to {
    opacity: 0.25;
    scale: 0.3;
  }
}
@keyframes loader {
  from {
    opacity: 1;
    scale: 1;
  }
  to {
    opacity: 0.25;
    scale: 0.3;
  }
}
</style>
