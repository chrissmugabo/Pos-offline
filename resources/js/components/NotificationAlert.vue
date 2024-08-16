<template>
  <div class="m-toast" :class="{ active: hasAlert }">
    <div class="toast-content">
      <svg
        height="14px"
        class="check bg-sucess"
        v-if="alert.type == 'success'"
        viewBox="0 0 16 16"
        xmlns="http://www.w3.org/2000/svg"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"
        ></path>
        <path
          fill-rule="evenodd"
          d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.236.236 0 0 1 .02-.022z"
        ></path>
      </svg>

      <svg
        xmlns="http://www.w3.org/2000/svg"
        height="14px"
        class="check bg-danger"
        v-if="alert.type == 'error' || alert.type == 'danger'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <polygon
          points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"
        ></polygon>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>

      <div class="message">
        <span class="text text-1 text-capitalize">{{ alert.type }}</span>
        <span class="text text-2">{{ alert.message }}</span>
      </div>
    </div>

    <svg
      class="close"
      @click.prevent="handleClose"
      height="24px"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M6 18L18 6M6 6l12 12"
      ></path>
    </svg>
    <div
      :class="`progress progress-${alert.type} ${hasAlert ? 'active' : ''}`"
    ></div>
  </div>
</template>
<script>
export default {
  name: "NotificationAlert",
  computed: {
    alert() {
      return this.$store.state.flashMessage || {};
    },
    hasAlert() {
      return Object.keys(this.alert).length > 0;
    },
  },
  watch: {
    alert() {
      if (this.hasAlert) {
        setTimeout(() => {
          this.$store.commit("SET_FLASH_MESSAGE", null);
        }, 5300);
      }
    },
  },
  methods: {
    handleClose() {
      this.$store.commit("SET_FLASH_MESSAGE", null);
    },
  },
};
</script>
<style scoped>
.m-toast {
  position: absolute;
  top: 25px;
  right: 30px;
  border-radius: 0.25rem;
  background: #fff;
  padding: 10px 25px 10px 15px;
  box-shadow: 0 6px 20px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transform: translateX(calc(100% + 30px));
  transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.35);
  z-index: 9999999;
}

.m-toast.active {
  transform: translateX(0%);
}

.m-toast .toast-content {
  display: flex;
  align-items: center;
}

.toast-content .check {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 25px;
  min-width: 25px;
  color: #fff;
  font-size: 14px;
  border-radius: 50%;
}

.toast-content .message {
  display: flex;
  flex-direction: column;
  margin: 0 10px;
}

.message .text {
  font-size: 16px;
  font-weight: 400;
  color: #666666;
}

.message .text.text-1 {
  font-weight: 600;
  color: #333;
}

.m-toast .close {
  position: absolute;
  top: 10px;
  right: 15px;
  padding: 5px;
  cursor: pointer;
  opacity: 0.7;
}

.m-toast .close:hover {
  opacity: 1;
}

.m-toast .progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: 100%;
}

.m-toast .progress:before {
  content: "";
  position: absolute;
  bottom: 0;
  right: 0;
  height: 100%;
  width: 100%;
}

.progress.active:before {
  animation: progress 5s linear forwards;
}

@keyframes progress {
  100% {
    right: 100%;
  }
}

.m-toast.active ~ button {
  pointer-events: none;
}
.progress-success::before {
  background-color: var(--success);
}
.progress-danger::before,
.progress-error::before {
  background-color: var(--error);
}
.progress-warning::before {
  background-color: var(--warning);
}
</style>
