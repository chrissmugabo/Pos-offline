import { ref } from "vue";
const hasFlashMessage = ref(false);
const message = ref();

const toggleFlashMessage = (msg) => {
  hasFlashMessage.value = !hasFlashMessage.value;
  message.value = msg;
  setTimeout(() => (hasFlashMessage.value = !hasFlashMessage.value), 3000);
};

export { hasFlashMessage, toggleFlashMessage, message };
