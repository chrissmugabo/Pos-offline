<template>
  <div
    :class="`offcanvas offcanvas-${position} show`"
    tabindex="-1"
    style="visibility: visible"
    aria-modal="true"
    role="dialog"
  >
    <div class="offcanvas-header">
      <h4 class="offcanvas-title">
        <slot name="head"></slot>
      </h4>
      <button
        type="button"
        class="btn-close text-reset"
        data-bs-dismiss="offcanvas"
        aria-label="Close"
        @click="$emit('close')"
      ></button>
    </div>
    <div class="offcanvas-body scrollable-content" :class="{ 'overflow-hidden': noScroll }">
      <slot name="body"></slot>
    </div>
  </div>
  <div class="modal-backdrop fade show" @click.self="$emit('close')"></div>
</template>
<script>
export default {
  name: "OffCanvas",
  emits: ["close"],
  props: {
    modalSize: String,
    headerColor: {
      type: String,
      default: "#000",
    },
    noScroll: {
      type: Boolean,
      default: false,
    },
    position: {
      type: String,
      default: "start",
    },
  },
};
</script>
<style scoped>
.offcanvas {
  position: fixed;
  bottom: 0;
  z-index: 1050;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -ms-flex-direction: column;
  flex-direction: column;
  max-width: 100%;
  visibility: hidden;
  background-color: #fff;
  background-clip: padding-box;
  outline: 0;
  -webkit-transition: -webkit-transform 0.3s ease-in-out;
  transition: -webkit-transform 0.3s ease-in-out;
  transition: transform 0.3s ease-in-out;
  transition: transform 0.3s ease-in-out, -webkit-transform 0.3s ease-in-out;
}
@media (prefers-reduced-motion: reduce) {
  .offcanvas {
    -webkit-transition: none;
    transition: none;
  }
}
.offcanvas-header {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  -webkit-box-pack: justify;
  -ms-flex-pack: justify;
  justify-content: space-between;
  padding: 1rem 1rem;
}
.offcanvas-header .btn-close {
  padding: 0.5rem 0.5rem;
  margin-top: -0.5rem;
  margin-right: -0.5rem;
  margin-bottom: -0.5rem;
}
.offcanvas-title {
  margin-bottom: 0;
  line-height: 1.5;
}
.offcanvas-body {
  -webkit-box-flex: 1;
  -ms-flex-positive: 1;
  flex-grow: 1;
  padding: 1rem 1rem;
  overflow-y: auto;
}
.offcanvas-start {
  top: 0;
  left: 0;
  width: 736px;
  border-right: 1px solid #f1f5f7;
  -webkit-transform: translateX(-100%);
  transform: translateX(-100%);
}
.offcanvas-end {
  top: 0;
  right: 0;
  width: 736px;
  border-left: 1px solid #f1f5f7;
  -webkit-transform: translateX(100%);
  transform: translateX(100%);
}
.offcanvas-top {
  top: 0;
  right: 0;
  left: 0;
  height: 30vh;
  max-height: 100%;
  border-bottom: 1px solid #f1f5f7;
  -webkit-transform: translateY(-100%);
  transform: translateY(-100%);
}
.offcanvas-bottom {
  right: 0;
  left: 0;
  height: 30vh;
  max-height: 100%;
  border-top: 1px solid #f1f5f7;
  -webkit-transform: translateY(100%);
  transform: translateY(100%);
}
.offcanvas.show {
  -webkit-transform: none;
  transform: none;
}
.clearfix::after {
  display: block;
  clear: both;
  content: "";
}
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1040;
  width: 100vw;
  height: 100vh;
  background-color: #000;
}
.modal-backdrop.fade {
  opacity: 0;
}
.modal-backdrop.show {
  opacity: 0.5;
}
</style>
