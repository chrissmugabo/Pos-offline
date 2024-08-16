<script setup>
import { onMounted, ref, toRefs, watch } from "vue";

const props = defineProps({
  type: {
    type: String,
    default: "image",
  },
  url: {
    type: String,
    required: false,
  },
});

const fileUrlPath = ref(null);
const uploadedFile = ref(null);

onMounted(() => {
  if (props.url) {
    fileUrlPath.value = props.url;
  }
});

const urlRef = toRefs(props).url;
watch(
  urlRef,
  () => {
    /** When v-model is change */
    if (urlRef.value) {
      fileUrlPath.value = urlRef.value;
    }
  },
  { deep: true }
);

function onFileSelected(event) {
  if (event && event.target) {
    const file = event.target.files[0];
    const reader = new FileReader();
    reader.onload = () => {
      fileUrlPath.value = reader.result;
    };
    reader.readAsDataURL(file);
  }
}

function resetUploadedFile() {
  fileUrlPath.value = null;
  if (uploadedFile.value) {
    uploadedFile.value.value = "";
  }
}
function selectDefaultImage() {
  uploadedFile.value?.click();
}

defineExpose({ uploadedFile });
</script>
<template>
  <div class="image-box">
    <input
      type="file"
      name="image"
      accept=".png, .jpg, .jpeg"
      @change="onFileSelected"
      class="image-data d-none"
      ref="uploadedFile"
    />
    <div class="preview-image-wrapper">
      <img
        :src="`${fileUrlPath || '/img/placeholder.png'}`"
        alt="Preview image"
        width="150"
        class="preview_image"
      />
      <a
        title="Remove image"
        class="btn_remove_image"
        href="#"
        @click.prevent="resetUploadedFile"
        ><svg
          height="18px"
          viewBox="0 0 16 16"
          xmlns="http://www.w3.org/2000/svg"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
          ></path></svg
      ></a>
    </div>
    <div class="image-box-actions">
      <a href="#" class="btn_gallery" @click.prevent="selectDefaultImage">
        Choose image
      </a>
    </div>
  </div>
</template>
