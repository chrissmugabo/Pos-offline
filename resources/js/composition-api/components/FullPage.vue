<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

function handleClose() {
  router.go(-1);
}
</script>
<template>
  <div
    class="BaseModal v-modal modal__backdrop ModalAccountCreate TheModal is--full base-modal"
  >
    <div
      aria-labelledby="modal-headline"
      aria-modal="true"
      role="dialog"
      class="modal__container"
    >
      <div class="modal__header-solid modal__header">
        <h4 class="modal__header-title">
          <slot name="title"></slot>
        </h4>
        <button type="button" class="modal__close-button border-0 bg-transparent" @click="handleClose">
          <svg
            width="16"
            height="16"
            viewBox="0 0 16 16"
            xmlns="http://www.w3.org/2000/svg"
            class="modal__close-button-icon svg-icon close-icon"
          >
            <g id="X mark">
              <path
                id="Vector"
                d="M13.134 4.59L14 3.728 12.272 2l-.862.862L8 6.272 4.59 2.866 3.728 2 2 3.728l.862.862L6.272 8l-3.406 3.41-.866.862L3.728 14l.862-.862L8 9.728l3.41 3.41.862.862L14 12.272l-.862-.862L9.728 8l3.41-3.41h-.004z"
                fill="inherit"
              ></path>
            </g>
          </svg>
        </button>
      </div>
      <div class="modal__content pt-3">
        <div class="modal-account-create__container">
          <slot name="body"></slot>
          <div class="modal-account-create__footer-row"></div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.v-modal {
  --modal-content-small-padding: 24px;
  position: relative;
  width: 50%;
  height: 50%;
  background-color: #fff;
  overflow: hidden;
  text-align: left;
  pointer-events: auto;
}
.v-modal .modal__content {
  padding: 24px;
  padding-top: 0;
}
.v-modal.is--full {
  width: 100%;
  height: 100%;
  padding: 0;
}
.v-modal.is--full .modal__container {
  width: 100%;
  max-width: 100%;
  height: 100%;
  padding-top: 0;
  animation: fade-in-up-13da0c94 0.3s;
  background-color: #f2f4f5;
}
.v-modal.is--full .modal__container .modal__content {
  padding: 60px 40px 110px;
}
@media screen and (max-width: 767px) {
  .v-modal.is--full .modal__container .modal__content {
    padding: 45px 12px 100px;
  }
}
.modal__backdrop {
  position: fixed;
  z-index: 100;
  overflow-y: auto;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(18, 23, 28, 0.5);
  -webkit-backdrop-filter: blur(6.7957px);
  backdrop-filter: blur(6.7957px);
}
.modal__container {
  flex-direction: column;
  height: auto;
  overflow: hidden;
  position: relative;
  z-index: 999;
  background-color: #fff;
  max-width: 946px;
  width: 100%;
}
.modal__header {
  padding: 24px 24px 16px;
  text-align: center;
  position: relative;
  top: 0;
  left: 0;
  right: 0;
  z-index: 2;
  width: 100%;
  display: flex;
  align-items: center;
}
.modal__header-title {
  margin-bottom: 0;
  flex: 1;
}
.modal__header-title + .modal__close-button {
  position: absolute;
  right: 30px;
}
.modal__header-solid {
  background-color: var(--bs-primary);
  box-shadow: 0 1px 15px 0 rgba(0, 0, 0, 0.05);
  padding: 14px 30px;
}
@media screen and (max-width: 767px) {
  .modal__header-solid {
    padding: 14px 12px;
  }
}
.modal__header-solid .modal__close-button {
  align-self: center;
  fill: #fff;
}
@media screen and (max-width: 767px) {
  .modal__header-solid .modal__close-button {
    right: 12px;
  }
}
.modal__header-solid .modal__header-title {
  color: #fff;
  margin: 0 30px;
}
.modal__content {
  overflow-y: scroll;
  overflow-x: hidden;
  height: 100%;
  margin: 0 auto;
}
.modal__close-button {
  cursor: pointer;
  margin-left: auto;
  align-self: flex-start;
}
.modal__close-button-icon {
  width: 20px;
  height: 20px;
}

.modal-account-create__container[data-v-92bcd4e4] {
  width: 100%;
  max-width: 950px;
  margin: 0 auto;
  flex-direction: column;
  min-height: 100vh;
}
@media screen and (max-width: 767px) {
  .modal-account-create__container[data-v-92bcd4e4] {
    margin: 6px auto;
  }
}
.modal-account-create__cancel-row[data-v-92bcd4e4] {
  display: flex;
  flex-direction: row;
  justify-content: center;
  margin-top: 29px;
  margin-bottom: 82px;
}
@media screen and (max-width: 767px) {
  .modal-account-create__cancel-row[data-v-92bcd4e4] {
    margin-bottom: 75px;
    margin-top: 33px;
  }
}

@keyframes fade-in-up-13da0c94 {
  0% {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}
</style>
