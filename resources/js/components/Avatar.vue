<template>
  <div
    class="user-avatar"
    :style="`background:${
      colours[getIndex(name)]
    };width:${width};height:${height}`"
  >
    <span :style="`${isLarge ? 'font-size: 2rem' : ''}`">
      {{ getInitials(name) }}</span
    >
  </div>
</template>
<script>
export default {
  name: "Avatar",
  props: {
    name: {
      type: String,
      required: true,
    },
    width: {
      type: String,
      default: "40px",
    },
    height: {
      type: String,
      default: "40px",
    },
    isLarge: {
      type: Boolean,
      default: false,
    },
  },
  data: () => ({
    colours: [
      "#1abc9c",
      "#2ecc71",
      "#3498db",
      "#9b59b6",
      "#34495e",
      "#16a085",
      "#27ae60",
      "#2980b9",
      "#8e44ad",
      "#2c3e50",
      "#f1c40f",
      "#e67e22",
      "#e74c3c",
      "#95a5a6",
      "#f39c12",
      "#d35400",
      "#c0392b",
      "#bdc3c7",
      "#7f8c8d",
    ],
  }),
  methods: {
    getInitials(name, numChars = 2) {
      var initials = name.charAt(0).toUpperCase();
      if (name.indexOf(" ") > -1 && numChars > 1) {
        var nameSplit = name.split(" ");
        initials =
          nameSplit[0].charAt(0).toUpperCase() +
          nameSplit[1].charAt(0).toUpperCase();
      }
      return initials;
    },
    getIndex(name) {
      var myindex = 0;
      if (name.indexOf(" ") > -1) {
        var nameSplit = name.split(" ");
        myindex =
          nameSplit[0].toUpperCase().charCodeAt(0) +
          nameSplit[1].toUpperCase().charCodeAt(nameSplit[1].length - 1);
      } else {
        myindex =
          name.toUpperCase().charCodeAt(0) +
          name.toUpperCase().charCodeAt(name.length - 1);
      }
      myindex = myindex % 19;
      return myindex;
    },
  },
};
</script>
<style scoped>
.user-avatar,
[class^="user-avatar"]:not([class*="-group"]) {
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #fff;
  background: #9d72ff;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.06em;
  flex-shrink: 0;
  position: relative;
}
</style>
