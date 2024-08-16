<script setup>
import { ref, computed, watch, onMounted, onBeforeMount, toRefs } from "vue";
import { useHttp } from "@/composition-api/hooks/useHttp";
import { encodeQuery } from "@/common/utils";

const { http, onGoingRequestFlag, isProcessing } = useHttp();
const emits = defineEmits(["selected", "update:modelValue"]);
const props = defineProps({
  modelValue: {
    required: false,
  },
  primaryKey: {
    default: "id",
  },
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
  records: {
    type: Array,
    default: function () {
      return [];
    },
  },
  fetchFlag: {
    type: String,
    default: "AUTOCOMPLETE_REQUEST",
  },
});

const rows = ref([]);
const searchKey = ref(null);
const selectedRow = ref({});
const searchActive = ref(false);

const isRequesting = computed(() => {
  return isProcessing(props.fetchFlag);
});
const filteredRows = computed(() => {
  let data = rows.value;
  const keyword = searchKey.value && searchKey.value.toLowerCase();
  if (keyword) {
    data = data.filter(
      (row) => String(row[props.label]).toLowerCase().indexOf(keyword) > -1
    );
  }
  return data;
});

/** handle focus in */
function handleFocusIn(e) {
  const el = e.target;
  if (el.nodeName == "BUTTON") {
    el.click();
  } else {
    if (el.nodeName == "INPUT") {
      searchKey.value = null;
    }
  }
}

/** Handle focus out */
function handleFocusOut(e) {
  const el = e.target;
  if (el.nodeName == "INPUT" || el.classList.contains("search-item")) {
    setTimeout(() => {
      searchActive.value = false;
    }, 1);
  }
}

function getDefaultData(getUrl) {
  const fetchUrl = getUrl || props.url;
  onGoingRequestFlag.value = props.fetchFlag;
  http.get(fetchUrl).then((response) => {
    if (response.data.rows) {
      rows.value = [...rows.value, ...response.data.rows];
    } else {
      rows.value = [...rows.value, ...response.data];
    }
    setDefaultOption();
  });
}

function setDefaultOption() {
  if (props.modelValue) {
    const data = rows.value.find(
      (item) => item[props.primaryKey] == props.modelValue
    );
    if (data) {
      searchKey.value = data[props.label];
      selectedRow.value = data;
    }
  }
}

function handleSearch(e) {
  const query = e.target.value;
  if (query.trim().length > 2) {
    if (!filteredRows.value.length) {
      onGoingRequestFlag.value = props.fetchFlag;
      http.get(encodeQuery(props.url, { query: query })).then((response) => {
        if (response.data.rows) rows.value = response.data.rows;
        else rows.value = response.data || [];
      });
    }
  }
}

function handleChange(option) {
  searchKey.value = option[props.label];
  if (option[props.primaryKey] != selectedRow.value[props.primaryKey]) {
    selectedRow.value = option;
    emits("selected", option);
    emits("update:modelValue", option[props.primaryKey]);
  }
  searchActive.value = false;
}

function clearAll() {
  selectedRow.value = {};
  rows.value = [];
}

onBeforeMount(() => {
  if (props.records.length) {
    rows.value = [...props.records];
  }
  if (props.modelValue) {
    const row = rows.value.find(
      (row) => row[props.primaryKey] == props.modelValue
    );
    if (row) {
      searchKey.value = row[props.label];
      selectedRow.value = row;
    }
  }
});
onMounted(() => {
  if (props.loadDefaults) {
    getDefaultData(null);
  }
});

defineExpose({ clearAll });
</script>
<template>
  <div
    class="search-field ember-view"
    @focusout="handleFocusOut"
    @focusin="handleFocusIn"
  >
    <div class="quick-search">
      <div class="dropdown show ac-dropdown ember-view">
        <div class="input-group btn-group">
          <div class="dropdown ember-view input-group-prepend w-100">
            <div class="auto-select ac-selected w-100">
              <input
                :placeholder="props.placeholder"
                class="form-control cursor-pointer search-key-input"
                :class="{
                  'rounded-bottom-0': searchActive,
                  'is-loading': isRequesting,
                }"
                type="text"
                @focus="searchActive = true"
                v-model="searchKey"
                @input="handleSearch($event)"
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
                :class="{
                  active:
                    selectedRow[props.primaryKey] == row[props.primaryKey],
                }"
              >
                <svg
                  version="1.1"
                  xmlns="http://www.w3.org/2000/svg"
                  x="0"
                  y="0"
                  viewBox="0 0 512 512"
                  xml:space="preserve"
                  class="icon icon-ok icon-xs"
                  v-if="selectedRow[props.primaryKey] == row[props.primaryKey]"
                >
                  <path
                    d="M503.4 141.1c-5.6-5.6-12.9-8.6-20.8-8.6s-15.3 3.1-20.8 8.6L162.1 440.7 50.2 328.8c-11.5-11.5-30.2-11.5-41.6 0-11.5 11.5-11.5 30.2 0 41.6l132.8 132.9c5.6 5.5 13 8.5 20.8 8.5 7.9 0 15.5-3.2 20.9-8.7l320.4-320.4c11.4-11.5 11.4-30.2-.1-41.6z"
                  ></path>
                </svg>
                {{ row[props.label] }}
              </button>

              <span class="py-2" v-if="!isRequesting && !rows.length"
                >No data to display</span
              >
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
  z-index: 10000;
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
