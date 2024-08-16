<template>
  <div>
    <input
      type="file"
      class="d-none"
      :id="inputId"
      @change="wolfUpload"
      ref="wolfInput"
    />
    <div class="mt-1" v-if="wolfUploading">
      <span
        ><b>{{ selectedFile }}</b></span
      >
      <div class="progress">
        <div
          class="progress-bar"
          :class="{
            'progress-bar-striped progress-bar-animated':
              progressBarWidth < 100,
            'bg-success': progressBarWidth >= 100
          }"
          role="progressbar"
          :aria-valuenow="progressBarWidth"
          aria-valuemin="0"
          aria-valuemax="100"
          :style="{ width: progressBarWidth }"
        ></div>
      </div>
      <div class="w-100">
        Uploading file. Please wait...
        <span class="float-right"> {{ percentageCount }}% </span>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: "WolfFilesUploader",
  props: {
    uploadURL: {
      type: String,
      default: "media/store"
    },
    needImagePreview: {
      type: Boolean,
      default: true
    },
    extentions: {
      type: Array,
      default: function() {
        return ["png", "jpg", "gif", "jpeg", "webp", "ico"];
      }
    },
    inputId: {
      type: String,
      default: "wolf-file"
    },
    postData: {
      type: Object,
      default: function() {
        return {};
      },
      required: false
    }
  },
  data: () => ({
    selectedFile: null,
    wolfUploading: false,
    progressBarWidth: 60,
    percentageCount: null,
    wolfDocument: null,
    fileUploaded: false,
    uploading: true,
    invalidFile: false
  }),
  methods: {
    handleFilePickUp() {
      this.$refs.wolfInput.click();
    },
    wolfUpload() {
      this.invalidFile = false;
      let uploadFile = document.getElementById(this.inputId);
      let FileUploadPath = uploadFile.value;
      let Extension = FileUploadPath.substring(
        FileUploadPath.lastIndexOf(".") + 1
      ).toLowerCase();
      this.selectedFile = uploadFile.files[0].name;
      if (this.extentions.includes(Extension)) {
        let fd = new FormData();
        fd.append("file", uploadFile.files[0], uploadFile.files[0].name);
        if (!this.$helper.isEmpty(this.postData)) {
          for (let key in this.postData) {
            if (this.postData[key] !== null) {
              if (typeof this.postData[key] === "object")
                fd.append(key, JSON.stringify(this.postData[key]));
              else fd.append(key, this.postData[key]);
            }
          }
          if (this.postData.url) {
            this.uploadURL = this.postData.url;
          }
        }
        let imgDataUrl = URL.createObjectURL(uploadFile.files[0]);
        if (this.needImagePreview) {
          this.$emit("preview", imgDataUrl);
        }
        this.wolfUploading = true;
        this.$store.state.requestFlag = "wolf-button";
        this.$http
          .post(this.uploadURL, fd, {
            onUploadProgress: uploadEvent => {
              let percentage = Math.round(
                (uploadEvent.loaded / uploadEvent.total) * 100
              );
              this.progressBarWidth = percentage + "%";
              this.percentageCount = percentage;
            }
          })
          .then(response => {
            this.fileUploaded = true;
            this.wolfDocument = response.data.filename;
            this.$emit("after-uploading", response.data);
            this.wolfUploading = false;
          })
      } else this.invalidFile = true;
    }
  }
};
</script>
