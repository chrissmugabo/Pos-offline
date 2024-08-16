<template>
  <button @click="handleClick" :disabled="isRequesting || disabler">
    <p v-if="isRequesting" id="loading">
      <span></span><span></span><span></span>
    </p>
    <slot v-else></slot>
  </button>
</template>
<script>
export default {
  name: "WolfButton",
  props: {
    activator: {
      type: String,
      required: true,
    },
    disabler: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    isRequesting() {
      return this.isProcessing(this.activator);
    },
  },
  methods: {
    handleClick() {
      this.$store.commit("SET_REQUEST_FLAG", this.activator);
      this.$emit("clicked");
    },
  },
};
</script>
<style lang="css" scoped>
@-webkit-keyframes opacity {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}
@-moz-keyframes opacity {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

#loading {
  text-align: center;
  margin: 0;
}

#loading span {
  display: inline-block;
  width: 0.6125rem;
  height: 0.6125rem;
  margin: 0 0.3125rem;
  border-radius: 50%;
  background-color: #fff;
  opacity: 0.5;
  vertical-align: middle;
  -webkit-animation-name: opacity;
  -webkit-animation-duration: 1s;
  -webkit-animation-iteration-count: infinite;

  -moz-animation-name: opacity;
  -moz-animation-duration: 1s;
  -moz-animation-iteration-count: infinite;
}

#loading span:nth-child(2) {
  -webkit-animation-delay: 100ms;
  -moz-animation-delay: 100ms;
}

#loading span:nth-child(3) {
  -webkit-animation-delay: 300ms;
  -moz-animation-delay: 300ms;
}
</style>
