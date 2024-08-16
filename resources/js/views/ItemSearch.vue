<script>
export default {
  name: "ItemSearch",
  props: {
    items: {
      type: Array,
      default: [],
    },
  },
  data: () => ({
    dropdownOpen: false,
  }),
  methods: {
    handleSelectedItem() {},
  },
};
</script>
<template>
  <div class="search-field global-search ember-view">
    <div id="quick-search">
      <div class="dropdown show ac-dropdown ember-view">
        <div class="input-group btn-group">
          <div class="dropdown ember-view input-group-prepend w-100">
            <span
              class="ember-view input-group-text cursor-pointer"
            >
              <svg
                version="1.1"
                id="Layer_1"
                xmlns="http://www.w3.org/2000/svg"
                x="0"
                y="0"
                viewBox="0 0 512 512"
                xml:space="preserve"
                class="icon icon-xs align-text-bottom icon-zoomed"
              >
                <path
                  d="M500.7 431.3l-94.2-94.1c21.4-33.9 33.9-74.1 33.9-117.2 0-121.5-98.6-220-220.2-220S0 98.5 0 220s98.6 220 220.2 220c42.9 0 82.9-12.3 116.7-33.5l94.3 94.2c15.1 15 39.5 15 54.5 0l15-15c15.1-15 15.1-39.4 0-54.4zm-280.5-52.2c-88 0-159.3-71.2-159.3-159.1 0-87.9 71.3-159.1 159.3-159.1 88 0 159.2 71.2 159.2 159.1 0 87.9-71.3 159.1-159.2 159.1z"
                ></path>
              </svg>
              <!---->
            </span>
            <div class="auto-select ac-selected w-100" tabindex="-1">
              <input
                placeholder="Search for item"
                class="ember-text-field ember-view form-control app-quick-search-field"
                type="text"
              />
            </div>
            <!---->
            <div class="dropdown-menu ember-view scrollmenu">
              <button
                class="dropdown-item pl-7"
                type="button"
                v-for="(item, i) in items"
                :key="'item' + i"
                @click="handleSelectedItem(item)"
              >
                {{ item.name }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
[tabindex="-1"]:focus {
  outline: 0 !important;
}
img {
  border-style: none;
}
button {
  border-radius: 0;
}
button:focus {
  outline: dotted 1px;
  outline: -webkit-focus-ring-color auto 5px;
}
button,
input {
  margin: 0;
  font-family: inherit;
  font-size: inherit;
  line-height: inherit;
}
[type="button"],
button {
  -webkit-appearance: button;
}
[type="button"]::-moz-focus-inner,
button::-moz-focus-inner {
  padding: 0;
  border-style: none;
}
.dropdown-item,
.form-control,
.input-group-text {
  font-weight: 400;
}
.dropdown-item {
  display: block;
}
.input-group-text {
  text-align: center;
}
input::-ms-clear {
  display: none;
}
.dropdown-item:active {
  text-decoration: none;
}
button.dropdown-item {
  outline: 0;
}
.dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1000;
  display: none;
  float: left;
  padding: 0.5rem 0;
  margin: 0.125rem 0 0;
  font-size: 1rem;
  color: #212529;
  text-align: left;
  list-style: none;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid rgba(0, 0, 0, 0.15);
  border-radius: 0.25rem;
  min-width: 100%;
}
.dropdown-item {
  width: 100%;
  padding: 0.25rem 1.5rem;
  clear: both;
  color: #212529;
  text-align: inherit;
  white-space: nowrap;
  background-color: transparent;
  border: 0;
}
.dropdown-item:active {
  color: #fff;
  background-color: #2485e8;
}
.dropdown-item:disabled {
  color: #999;
  pointer-events: none;
  background-color: transparent;
}
.dropdown-menu .dropdown-item:disabled {
  cursor: not-allowed;
  pointer-events: all;
}
.btn-group {
  position: relative;
  display: inline-flex;
  vertical-align: middle;
}
.input-group {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  width: 100%;
}
.input-group-prepend {
  display: flex;
}
.input-group-prepend {
  margin-right: -1px;
}
.input-group-text {
  display: flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  margin-bottom: 0;
  font-size: 1rem;
  line-height: 1.6;
  color: #495057;
  white-space: nowrap;
  background-color: #eee;
  border: 1px solid #ced4da;
  border-radius: 2px;
}
.input-group > .input-group-prepend > .input-group-text {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
svg {
  vertical-align: baseline;
}

.dropdown-item:focus,
.dropdown-item:hover {
  color: #fff !important;
  text-decoration: none !important;
  background-color: #007bff !important;
}
svg.icon {
  width: 16px;
  height: 16px;
  fill: currentColor;
}
svg.icon.icon-xs {
  height: 12px;
  width: 12px;
}
svg.icon.icon-zoomed {
  transform: scale(1.5);
}
.search-field .input-group .input-group-text {
  border-color: #eee;
  transition: 0.3s;
}
.search-field .input-group .input-group-text svg {
  color: #999;
}
.search-field .input-group input {
  border-color: #eee;
}
.search-field .input-group input:placeholder {
  color: #999;
}
.search-field .input-group:hover .input-group-text,
.search-field .input-group:hover input,
.search-field .input-group:hover input:focus {
  border-color: #2098ff;
}
.global-search {
  float: left;
  width: 300px;
}
.search-field {
  margin-top: 2px;
}
.search-field .form-control {
  border-left: 0;
  padding: 0 2px;
  border-radius: 0 20px 20px 0 !important;
  height: 33px;
  box-shadow: none;
}
.search-field .form-control:focus {
  border-color: #ccc;
}
.search-field .input-group-text {
  background-color: #fff;
  padding: 9px 7px;
}
.search-field .input-group-prepend .input-group-text {
  border-top-left-radius: 20px;
  border-bottom-left-radius: 20px;
  border-right: 0;
}
.search-field .input-group-prepend {
  font-size: 12px;
  position: relative;
  z-index: 5;
}
.search-field .dropdown-menu {
  right: 0;
  padding-bottom: 0;
  border: 1px solid #eaeaea;
}
.search-field .dropdown-menu .icon-ok {
  margin: 3px;
}
.search-field .dropdown-menu .dropdown-item .icon-ok {
  position: absolute;
  left: 5px;
}
.search-field .dropdown-menu .bottom-0 {
  bottom: 0;
}
.search-field [class*="icon-"] {
  margin: 0 3px;
}
@media (max-width: 1024px) {
  .search-field {
    width: 200px;
  }
}
.form-control {
  padding: 5px 8px;
}
.bottom-0 {
  bottom: 0;
}
.cursor-pointer {
  cursor: pointer;
}
.scrollmenu {
  max-height: 400px;
}
:focus::-webkit-input-placeholder {
  opacity: 0.5;
}
:focus:-moz-placeholder {
  opacity: 0.5;
}
:focus::-moz-placeholder {
  opacity: 0.5;
}
:focus:-ms-input-placeholder {
  opacity: 0.5;
}
.scrollmenu {
  min-width: 200px;
  overflow-y: auto;
}
.bg-faded {
  background-color: #eee;
}
</style>
