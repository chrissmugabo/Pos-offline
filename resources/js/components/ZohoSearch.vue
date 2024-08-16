<template>
  <div
    class="search-field ember-view"
    @focusout="handleFocusOut"
    @focusin="handleFocusIn"
  >
    <div class="quick-search">
      <div class="dropdown show ac-dropdown ember-view">
        <div class="input-group btn-group">
          <div
            class="dropdown ember-view input-group-prepend w-100"
            :class="{ 'v-dropdown': isVertical }"
          >
            <div
              class="auto-select ac-selected w-100"
              data-bs-toggle="dropdown"
              data-bs-target="#search-dropdown"
            >
              <input
                :placeholder="placeholder"
                class="form-control cursor-pointer search-key-input"
                :class="{
                  'rounded-bottom-0': searchActive,
                  'is-loading': isRequesting,
                }"
                type="text"
                @focus="searchActive = true"
                v-model="searchKey"
              />
            </div>
            <!---->
            <div
              class="dropdown-menu ember-view scrollmenu w-100"
              :class="{ show: searchActive }"
            >
              <button
                class="dropdown-item search-item pl-7"
                type="button"
                @click="handleChange(row)"
                v-for="(row, i) in filteredRows"
                :key="'item' + i"
                :class="{ active: selectedRow[primaryKey] == row[primaryKey] }"
              >
                <svg
                  version="1.1"
                  xmlns="http://www.w3.org/2000/svg"
                  x="0"
                  y="0"
                  viewBox="0 0 512 512"
                  xml:space="preserve"
                  class="icon icon-ok icon-xs"
                  v-if="selectedRow[primaryKey] == row[primaryKey]"
                >
                  <path
                    d="M503.4 141.1c-5.6-5.6-12.9-8.6-20.8-8.6s-15.3 3.1-20.8 8.6L162.1 440.7 50.2 328.8c-11.5-11.5-30.2-11.5-41.6 0-11.5 11.5-11.5 30.2 0 41.6l132.8 132.9c5.6 5.5 13 8.5 20.8 8.5 7.9 0 15.5-3.2 20.9-8.7l320.4-320.4c11.4-11.5 11.4-30.2-.1-41.6z"
                  ></path>
                </svg>
                <span v-html="row?.alias || row[label]"></span>
              </button>

              <span class="py-2" v-if="!isRequesting && !rows.length"
                >No data to display</span
              >
              <slot name="footer"></slot>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: "ZohoSearch",
  emits: ["selected", "update:modelValue"],
  props: {
    modelValue: Number,
    url: {
      type: String,
      required: false,
    },
    label: {
      type: String,
      required: true,
    },
    placeholder: {
      type: String,
      required: true,
    },
    loadDefaults: {
      type: Boolean,
      default: false,
      required: false,
    },
    isVertical: {
      type: Boolean,
      default: false,
      required: false,
    },
    records: {
      type: Array,
      default: function () {
        return [];
      },
    },
    primaryKey: {
      type: String,
      default: "id",
    },
    fetchFlag: {
      type: String,
      default: "AUTOCOMPLETE_REQUEST",
    },
    defaultRow: {
      type: Object,
      default: function () {
        return {};
      },
    },
  },
  data: () => ({
    rows: [],
    searchKey: null,
    selectedRow: {},
    searchActive: false,
  }),
  computed: {
    isRequesting() {
      return this.isProcessing(this.fetchFlag);
    },
    filteredRows() {
      let rows = this.rows;
      let searchKey = this.searchKey && this.searchKey.toLowerCase();
      if (searchKey) {
        rows = rows.filter(
          (row) => String(row[this.label]).toLowerCase().indexOf(searchKey) > -1
        );
        if (!rows.length || false) {
          if (searchKey.trim().length > 2) {
            this.$store.commit("SET_REQUEST_FLAG", this.fetchFlag);
            (async (searchKey) => {
              const url =
                this.url.indexOf("?") === -1
                  ? `${this.url}?query=${searchKey}`
                  : `${this.url}&query=${searchKey}`;
              const response = await this.$http.get(url);
              rows = response.data.rows || response.data || [];
            })(searchKey);
          }
        }
      }
      return rows;
    },
  },
  created() {
    if (this.records.length) {
      this.rows = [...this.records];
    }
    if (Object.keys(this.defaultRow).length) {
      this.searchKey = this.defaultRow[this.label];
      this.selectedRow = this.defaultRow;
    }
  },
  updated() {
    if (this.modelValue) {
      const row = this.rows.find(
        (item) => item[this.primaryKey] == this.modelValue
      );

      if (row) {
        this.searchKey = row[this.label];
        this.selectedRow = row;
      }
    }
  },
  watch: {
    $props: {
      handler() {
        //defaultRow
        if (Object.keys(this.defaultRow).length) {
          this.searchKey = this.defaultRow[this.label];
          this.selectedRow = this.defaultRow;
        } else {
          this.searchKey = null;
          this.selectedRow = {};
        }
        // Records
        if (this.records.length) {
          this.rows = [...this.rows, ...this.records];
        }

        // modelValue
        if (!this.modelValue) {
          this.searchKey = null;
          this.selectedRow = {};
        } else {
          const row = this.rows.find(
            (row) => row[this.primaryKey] == this.modelValue
          );
          if (row) {
            this.searchKey = row[this.label];
            this.selectedRow = row;
          }
        }
      },
      deep: true,
    },
  },
  mounted() {
    if (this.loadDefaults) {
      this.getDefaultData();
    }
  },
  methods: {
    setDefaultOption() {
      if (this.modelValue) {
        const data = this.rows.find(
          (item) => item[this.primaryKey] == this.modelValue
        );
        if (data) {
          this.searchKey = data[this.label];
          this.selectedRow = data;
        }
      }
    },
    handleFocusIn(e) {
      if (e.target.nodeName == "BUTTON" || e.target.nodeName == "A") {
        e.target.click();
      } else {
        if (e.target.nodeName == "INPUT") {
          this.searchKey = null;
        }
      }
    },
    handleFocusOut(e) {
      if (
        e.target.nodeName == "INPUT" ||
        e.target.classList.contains("search-item")
      ) {
        setTimeout(() => {
          this.searchActive = false;
        }, 1);
      }
    },
    getDefaultData(url = null) {
      let fetchUrl = url || this.url;
      this.$store.commit("SET_REQUEST_FLAG", this.fetchFlag);
      this.$http.get(fetchUrl).then((response) => {
        if (response.data.rows) {
          this.rows = [...this.rows, ...response.data.rows];
        } else {
          this.rows = [...this.rows, ...response.data];
        }
        this.setDefaultOption();
      });
    },
    handleSearch(query) {
      if (query.trim().length > 0) {
        this.$store.commit("SET_REQUEST_FLAG", this.fetchFlag);
        this.$http.get(`${this.url}?query=${query}`).then((response) => {
          if (response.data.rows) this.rows = response.data.rows;
          else this.rows = response.data || [];
        });
      }
    },
    handleChange(option) {
      this.searchKey = option[this.label];
      if (option[this.primaryKey] != this.selectedRow[this.primaryKey]) {
        this.selectedRow = option;
        this.$emit("selected", option);
        this.$emit("update:modelValue", option[this.primaryKey]);
      }
      this.searchActive = false;
    },
    clearAll() {
      this.selectedRow = {};
      this.rows = [];
    },
  },
};
</script>
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
  font-size: 0.875rem;
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

.dropdown-item {
  display: block;
}
.input-group-text {
  text-align: center;
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
.dropdown-toggle {
  white-space: nowrap;
}
.dropdown-toggle::after {
  display: inline-block;
  margin-left: 0.255em;
  vertical-align: 0.255em;
  content: "";
  border-top: 0.3em solid;
  border-right: 0.3em solid transparent;
  border-bottom: 0;
  border-left: 0.3em solid transparent;
}
.dropdown-toggle:empty::after {
  margin-left: 0;
}
.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 9999;
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
  border-radius: 0.25rem;
  border-top-left-radius: unset;
  border-top-right-radius: unset;
  min-width: 100%;
  border-top-color: #fff;
  border-top: transparent !important;
  box-shadow: unset !important;
}
.dropdown-item {
  width: 100%;
  padding: 0.25rem 1rem;
  clear: both;
  color: #212529;
  text-align: inherit;
  white-space: nowrap;
  background-color: transparent;
  border: 0;
}
.dropdown-item:active,
.dropdown-item.active {
  color: #fff;
  background-color: var(--bs-primary);
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
  background-color: #dadce0;
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
  background-color: var(--bs-primary) !important;
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
  transform: scale(2);
}
.search-field .input-group .input-group-text {
  border-color: #dadce0;
  transition: 0.3s;
}
.search-field .input-group .input-group-text svg {
  color: #999;
}
.search-field .input-group input {
  border-color: #dadce0;
}
.search-field .input-group input:placeholder {
  color: #999;
}
.search-field .input-group:hover .input-group-text,
.search-field .input-group:hover input,
.search-field .input-group:hover input:focus {
  border-color: #dadce0;
}

.search-field .dropdown-menu {
  right: 0;
  padding-bottom: 0;
  border: 1px solid #dadce0;
}
.search-field .dropdown-menu .icon-ok {
  margin: 3px;
}
.search-field .dropdown-menu .dropdown-item .icon-ok {
  position: absolute;
  left: 0;
}
.search-field .dropdown-menu .bottom-0 {
  bottom: 0;
}
.search-field .dropdown-menu .adv-search,
.search-field .dropdown-menu .zia-search {
  padding: 10px 5px;
  border-top: 1px solid #dadce0;
  background-color: #fff;
  cursor: pointer;
}
.search-field .dropdown-menu .adv-search:hover,
.search-field .dropdown-menu .zia-search:hover {
  background-color: #f8f8f8 !important;
}
.search-field [class*="icon-"] {
  margin: 0 3px;
}
@media (max-width: 1024px) {
  .search-field {
    width: 200px;
  }
}
.top-band .dropdown .dropdown-toggle {
  color: #777;
}
.bottom-0 {
  bottom: 0;
}
.cursor-pointer {
  cursor: pointer;
}
.scrollmenu {
  max-height: 300px;
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
  overflow-x: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.bg-faded {
  background-color: #dadce0;
}
.search-key-input {
  border-radius: 0.25rem;
  outline: 0;
  box-shadow: unset;
}
.search-key-input:hover {
  border-color: #dadce0 !important;
}
.search-key-input {
  background-position: right calc(0.375em + 0.1875rem) center;
  background-repeat: no-repeat;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
  background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAMCAYAAABSgIzaAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDZFNDEwNjlGNzFEMTFFMkJEQ0VDRTM1N0RCMzMyMkIiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDZFNDEwNkFGNzFEMTFFMkJEQ0VDRTM1N0RCMzMyMkIiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0NkU0MTA2N0Y3MUQxMUUyQkRDRUNFMzU3REIzMzIyQiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0NkU0MTA2OEY3MUQxMUUyQkRDRUNFMzU3REIzMzIyQiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PuGsgwQAAAA5SURBVHjaYvz//z8DOYCJgUxAf42MQIzTk0D/M+KzkRGPoQSdykiKJrBGpOhgJFYTWNEIiEeAAAMAzNENEOH+do8AAAAASUVORK5CYII=);
}
.search-key-input.is-loading {
  background-image: url("./loading_48px.gif");
}
.show {
  transform: scale(1);
  display: block;
}
.rounded-bottom-0 {
  border-bottom-left-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
}
.scrollmenu::-webkit-scrollbar {
  width: 0.1875rem;
}
.scrollmenu::-webkit-scrollbar-thumb {
  background: rgba(135, 139, 144, 0.5);
  border-radius: 2rem;
}
.scrollmenu::-webkit-scrollbar-track {
  background-color: transparent;
}
.search-item {
  overflow-x: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.search-field {
  position: relative;
  z-index: 9;
}
</style>
