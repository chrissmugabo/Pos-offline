<template>
  <button
    class="btn btn-primary"
    type="button"
    tabindex="2"
    @click="
      isProcessing(activator);
      $emit('clicked');
    "
    :class="{
      changeloadbtn: isProcessing(activator),
    }"
    :disabled="$store.state.isLoading || disabler"
  >
    <slot v-if="isProcessing(activator)"></slot>
  </button>
</template>
<script>
export default {
  name: "ZohoButton",
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
};
</script>
<style lang="css" scoped>
.zoho-btn {
  cursor: pointer;
  display: block;
  width: 100%;
  height: 44px;
  border-radius: 4px;
  letter-spacing: 0.5;
  font-size: 14px;
  font-weight: 600;
  outline: none;
  border: none;
  margin: auto;
  text-transform: uppercase;
  margin-bottom: 30px;
  transition: all 0.2s ease-in-out;
}

.changeloadbtn {
  display: block;
  height: 32px;
  width: 32px;
  border-radius: 15px;
  padding: 0px;
}

.blue {
  box-shadow: 0px 2px 2px #fff;
  background-color: rgb(30, 39, 85);
  color: #fff;
}
.changeloadbtn:before {
  content: "";
  height: 15px;
  width: 15px;
  display: inline-block;
  margin: 7px;
  border: 3px solid #fff;
  border-top: 3px solid rgb(30, 39, 85);
  border-radius: 50%;
  animation: spin 0.5s linear infinite;
}
.secodanry {
  color: 244682;
}
.zoho-btn:hover,
.zoho-btn:focus {
  background-color: rgb(45, 53, 97);
}
.zoho-btn:disabled {
  background-color: rgb(96, 103, 135);
}
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
