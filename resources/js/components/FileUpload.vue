<template>
  <div>
    <input
      type="file"
      class="d-none"
      :id="inputId"
      @change="delegateUploadAction"
      ref="FileInput"
    />
    <div v-if="uploading && $store.state.currentUploader == inputId">
      <transition enter-active-class leave-active-class="animated rollOut">
        <div
          class="modal fade show"
          style="
            z-index: 999999999;
            display: block;
            background: rgba(0, 0, 0, 0.7);
          "
        >
          <div class="modal-dialog modal-dialog-centered">
            <div
              class="modal-content rounded-0"
              style="border-color: transparent"
            >
              <div class="modal-body" :class="{ 'p-0': percentageCount >= 100 }">
                <div class="row" v-if="percentageCount < 100">
                  <div class="col-lg-12">
                    <div class="mt-3">
                      <span
                        ><b>{{ selectedFile }}</b></span
                      >
                      <div class="progress">
                        <div
                          class="progress-bar"
                          :class="{
                            'progress-bar-striped progress-bar-animated':
                              percentageCount < 100,
                            'bg-success': percentageCount >= 100,
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
                        <span class="float-right">
                          {{ percentageCount }}%
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-primary text-white d-flex align-items-center" v-else>
                  <svg
                    version="1.1"
                    id="L6"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    x="0px"
                    y="0px"
                    viewBox="0 0 100 100"
                    enable-background="new 0 0 100 100"
                    xml:space="preserve"
                  >
                    <rect
                      fill="none"
                      stroke="#fff"
                      stroke-width="4"
                      x="25"
                      y="25"
                      width="50"
                      height="50"
                    >
                      <animateTransform
                        attributeName="transform"
                        dur="0.5s"
                        from="0 50 50"
                        to="180 50 50"
                        type="rotate"
                        id="strokeBox"
                        attributeType="XML"
                        begin="rectBox.end"
                      ></animateTransform>
                    </rect>
                    <rect x="27" y="27" fill="#fff" width="46" height="50">
                      <animate
                        attributeName="height"
                        dur="1.3s"
                        attributeType="XML"
                        from="50"
                        to="0"
                        id="rectBox"
                        fill="freeze"
                        begin="0s;strokeBox.end"
                      ></animate>
                    </rect>
                  </svg>
                  <div>
                    <h6>Please wait while processing your request.</h6>
                    <p>This can take a while base on size of the uploaded media.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>
<script>
export default {
  name: "FileUpload",
  props: {
    uploadURL: {
      type: String,
      default: "media/store",
    },
    needImagePreview: {
      type: Boolean,
      default: true,
    },
    extentions: {
      type: Array,
      default: function () {
        return ["png", "jpg", "gif", "jpeg", "webp", "ico"];
      },
    },
    inputId: {
      type: String,
      default: "wolf-file",
    },
    postData: {
      type: Object,
      default: function () {
        return {};
      },
      required: false,
    },
  },
  data: () => ({
    selectedFile: null,
    uploading: false,
    progressBarWidth: 0,
    percentageCount: null,
    fileUploaded: false,
    invalidFile: false,
  }),
  methods: {
    handleFilePickUp() {
      this.$refs.FileInput.click();
    },
    delegateUploadAction() {
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
        this.uploading = true;
        this.$store.state.requestFlag = "uploading-button";
        this.$store.state.currentUploader = this.inputId;
        this.$http
          .post(this.uploadURL, fd, {
            onUploadProgress: (uploadEvent) => {
              let percentage = Math.round(
                (uploadEvent.loaded / uploadEvent.total) * 100
              );
              this.progressBarWidth = percentage + "%";
              this.percentageCount = percentage;
            },
          })
          .then((response) => {
            this.fileUploaded = true;
            this.$emit("after-uploading", response.data);
            this.$store.state.currentUploader = null;
            this.uploading = false;
          })
      } else this.invalidFile = true;
    },
  },
};
</script>
<style lang="scss" scoped>
svg {
  width: 100px;
  height: 100px;
  margin: 20px;
  display: inline-block;
}
</style>
