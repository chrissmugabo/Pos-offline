<template>
  <button
    type="submit"
    @click="handleClick"
    :class="{ disabled: isRequesting }"
  >
    <template v-if="isRequesting">
      <span
        class="spinner-border spinner-border-sm"
        role="status"
        aria-hidden="true"
      ></span>
      <span class="visually-hidden">Loading...</span>
    </template>
    <slot name="caption" v-else></slot>
  </button>
</template>
<script setup>
import { computed } from "vue";
import { useHttp } from "@/composition-api/hooks/useHttp";
const { onGoingRequestFlag, isProcessing } = useHttp();

const props = defineProps({ task: { type: String } });
const emits = defineEmits(["clicked"]);
const isRequesting = computed(() => {
  return isProcessing(props.task);
});
function handleClick() {
  if (isRequesting.value) {
    return false;
  }
  onGoingRequestFlag.value = props.task;
  setTimeout(() => {
    emits("clicked");
  }, 2);
}
</script>
