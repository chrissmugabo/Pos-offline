<template>
  <img :src="src" v-if="imageFound" />
  <img :src="placeholder" class="default-bg-icon" v-else />
</template>
<script>
export default {
  name: "LazyImage",
  props: ["src", "placeholder"],
  data: () => ({
    status: null,
  }),
  created() {
    this.checkFoundImages(this.src);
  },
  computed: {
    imageFound() {
      return this.status === true;
    },
  },
  methods: {
    testImageUrl(url) {
      return new Promise(function (resolve, reject) {
        var image = new Image();
        image.addEventListener("load", () => resolve(true));
        image.addEventListener("error", () => reject(false));
        image.src = url;
      });
    },
    checkFoundImages(imageUrl) {
      return this.testImageUrl(imageUrl)
        .then(() => {
          this.status = true;
        })
        .catch(() => {
          this.status = false;
        });
    },
  },
};
</script>
