<script setup>
import { ref, computed, onMounted } from "vue";
const status = ref(false);
const props = defineProps({
  src: { type: String },
  placeholder: { type: String, default: "/img/blank-image.svg" },
});
const imageFound = computed(() => {
  return status.value === true;
});
function testImageUrl(url) {
  return new Promise(function (resolve, reject) {
    var image = new Image();
    image.addEventListener("load", () => resolve(true));
    image.addEventListener("error", () => reject(false));
    image.src = url;
  });
}
function checkFoundImages(imageUrl) {
  return testImageUrl(imageUrl)
    .then(() => {
      status.value = true;
    })
    .catch(() => {
      status.value = false;
    });
}
onMounted(() => {
  checkFoundImages(props.src);
});
</script>
<template>
  <img :src="props.src" v-if="imageFound" />
  <img :src="props.placeholder" class="default-bg-icon" v-else />
</template>
