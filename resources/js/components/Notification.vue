<template>
  <div class="notification-container">
    <template v-for="(item, key) in list" :key="key + item.id">
      <transition
        enter-active-class="animated quick fadeInRight"
        leave-active-class="animated quick fadeOutRight"
      >
        <div
          :class="[container, item.status, 'notify-me']"
          :style="{ width: item.width }"
        >
          <div>{{ item.content }}</div>
          <button class="delete" @click="hideChild(key)"></button>
        </div>
      </transition>
    </template>
  </div>
</template>
<script>
export default {
  name: "Notification",
  props: {
    container: {
      type: String,
      default: "notification",
    },
  },
  computed: {},
  data: () => ({}),
  methods: {
    hideChild(key) {
      this.$store.dispatch("notifyRemove", key);
    },
  },
};
</script>
<style scoped>
.notify-me {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  bottom: 2rem;
  right: 2rem;
  z-index: 9999;
  margin-bottom: 1.5rem;
}

.notify-me i {
  cursor: pointer;
  align-self: flex-start;
}

.notify-me:first-child {
  margin-bottom: 0;
}
.notification-container {
  display: flex;
  flex-direction: column-reverse;
  align-items: flex-end;
  position: fixed;
  right: 0;
  bottom: 0;
  width: auto;
  height: auto;
  z-index: 9999;
}
.notification {
  background-color: #f5f5f5;
  border-radius: 3px;
  padding: 1.25rem 2.5rem 1.25rem 1.5rem;
  position: relative;
}
.notify-me {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  bottom: 2rem;
  right: 2rem;
  z-index: 9999;
  margin-bottom: 1.5rem;
}
.notification.is-success {
  background-color: #23d160;
  color: #fff;
}
.notify-me:first-child {
  margin-bottom: 0;
}
.delete {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -moz-appearance: none;
  -webkit-appearance: none;
  background-color: rgba(10, 10, 10, 0.2);
  border: none;
  border-radius: 290486px;
  cursor: pointer;
  display: inline-block;
  -webkit-box-flex: 0;
  -ms-flex-positive: 0;
  flex-grow: 0;
  -ms-flex-negative: 0;
  flex-shrink: 0;
  font-size: 1rem;
  height: 20px;
  max-height: 20px;
  max-width: 20px;
  min-height: 20px;
  min-width: 20px;
  outline: 0;
  position: relative;
  vertical-align: top;
  width: 20px;
}
.notification > .delete {
  position: absolute;
  right: 0.5em;
  top: 0.5em;
}

.delete {
  background: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBkPSJNMTYgMHYxNkgwVjB6Ii8+PHBhdGggZmlsbD0iIzAwMCIgZmlsbC1ydWxlPSJub256ZXJvIiBkPSJNMTQuMzU0IDIuMzUzbC0uNzA4LS43MDdMOCA3LjI5MyAyLjM1MyAxLjY0NmwtLjcwNy43MDdMNy4yOTMgOGwtNS42NDcgNS42NDYuNzA3LjcwOEw4IDguNzA3bDUuNjQ2IDUuNjQ3LjcwOC0uNzA4TDguNzA3IDh6Ii8+PC9nPjwvc3ZnPg==)
    no-repeat;
  background-position: center;
  background-color: #fff;
  border: none;
  cursor: pointer;
  padding: 8px;
  height: 24px;
  width: 24px;
  top: 24px;
  right: 23px;
  position: absolute;
  background-size: 12px;
}
</style>
