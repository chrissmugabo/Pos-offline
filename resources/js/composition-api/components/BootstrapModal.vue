<script setup>
import { nextTick, onMounted, ref } from "vue";

const modal = ref();
const props = defineProps({
  id: String,
  noBackDrop: { type: Boolean, required: false, default: false },
  dialogClasses: { type: String, required: false, default: "" },
});
onMounted(() => {
  nextTick(() => {
    modal.value = new window.bootstrap.Modal(document.getElementById(props.id));
  });
});
function show() {
  modal.value.show();
  if (props.noBackDrop) {
    const backdrops = document.querySelectorAll(".modal-backdrop");
    if (backdrops && backdrops.length) {
      const el = backdrops[backdrops.length - 1];
      el && el.remove();
    }
  }
}
function hide() {
  modal.value.hide();
}
defineExpose({ show, hide });
</script>
<template>
  <div
    :id="props.id"
    :aria-labelledby="`${props.id}Label`"
    class="modal fade"
    tabindex="-1"
  >
    <div :class="`modal-dialog ${props.dialogClasses}`">
      <div class="modal-content border-0">
        <div
          class="d-flex align-items-center bg-primary bg-gradient p-3 modal-header"
        >
          <h5 class="m-0 me-2 text-white modal-title">
            <slot name="title"></slot>
          </h5>
          <slot name="tools"></slot>
          <button
            type="button"
            class="btn-close btn-close-white ms-auto"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <slot name="body"></slot>
        </div>
        <div class="modal-footer border-top p-3 text-center">
          <slot name="footer"></slot>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
/** Responsive **/
@media (min-width: 1868px) {
  .stock-offcanvas {
    width: 80vw;
  }
}
@media (min-width: 1728px) {
  .stock-offcanvas {
    width: 82vw;
  }
}
@media (min-width: 1560px) {
  .stock-offcanvas {
    width: 84vw;
  }
}
@media (min-width: 1450px) {
  .stock-offcanvas {
    width: 86vw;
  }
}
@media (min-width: 1450px) {
  .stock-offcanvas {
    width: 86vw;
  }
}
@media (min-width: 1312px) {
  .stock-offcanvas {
    width: 88vw;
  }
}
@media (min-width: 1244px) {
  .stock-offcanvas {
    width: 89vw;
  }
}
@media (min-width: 1172px) {
  .stock-offcanvas {
    width: 61vw;
  }
}
@media (min-width: 1104px) {
  .stock-offcanvas {
    width: 62vw;
  }
}
@media (min-width: 1035px) {
  .stock-offcanvas {
    width: 63vw;
  }
}
@media (min-width: 914px) {
  .stock-offcanvas {
    width: 66vw;
  }
}
@media (min-width: 800px) {
  .stock-offcanvas {
    width: 65vw;
  }
}
@media (min-width: 712px) {
  .stock-offcanvas {
    width: 64vw;
  }
}
@media (min-width: 640px) {
  .stock-offcanvas {
    width: 63vw;
  }
}
@media (min-width: 582px) {
  .stock-offcanvas {
    width: 57vw;
  }
}
@media (min-width: 480px) {
  .stock-offcanvas {
    width: 60vw;
  }
}
</style>
