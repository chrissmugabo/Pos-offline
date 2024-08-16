<template>
  <div
    class="modal fade show"
    :style="`display: block; ${isCompact ? 'z-index: 9999' : ''}`"
    tabindex="-1"
    role="dialog"
  >
    <div class="modal-dialog modal-dialog-centered" :class="[modalSize]">
      <div class="modal-content">
        <progress-bar
          :bg-color="'#003a60'"
          v-if="modalRequesting"
        ></progress-bar>
        <div class="modal-header">
          <h4 class="modal-title w-100">
            <slot name="head"></slot>
          </h4>
          <a
            href="javascript:void(0)"
            class="close"
            @click.prevent="$emit('close')"
          >
            <em class="demo-pli-cross fs-5 fw-bolder text-danger"></em
          ></a>
        </div>

        <div class="modal-body">
          <slot name="body"></slot>
        </div>
        <div class="modal-footer bg-light" v-if="showFooter">
          <slot name="foot"></slot>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade show" @click.self="$emit('close')"></div>
</template>
<script>
import ProgressBar from "./ProgressBar.vue";
export default {
  name: "BootstrapModal",
  components: { ProgressBar },
  emits: ["close"],
  props: {
    modalSize: {
      type: String,
      default: "modal-default",
    },
    showFooter: {
      type: Boolean,
      default: false,
    },
    isCompact: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    modalRequesting() {
      return this.isProcessing("MODAL_REQUEST");
    },
  },
};
</script>
<style scoped>
.modal-xg {
  max-width: 1000px !important;
}
.modal-header {
  border-top-left-radius: unset;
  border-top-right-radius: unset;
}
</style>
