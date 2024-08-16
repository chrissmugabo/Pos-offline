<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  onBeforeMount,
  toRefs,
  onUpdated,
  nextTick,
} from "vue";
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
    default: "MULTSELECT_REQUEST",
  },
});

const rows = ref([]);
const searchKey = ref(null);
const selectedRows = ref([]);
const searchActive = ref(false);

const isRequesting = computed(() => {
  return isProcessing(props.fetchFlag);
});
const selectedRowsRefs = computed(() =>
  selectedRows.value.map((item) => item[props.primaryKey])
);

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

function getDefaultData(getUrl) {
  const fetchUrl = getUrl || props.url;
  onGoingRequestFlag.value = props.fetchFlag;
  http.get(fetchUrl).then((response) => {
    if (response.data.rows) {
      rows.value = [...rows.value, ...response.data.rows];
    } else {
      rows.value = [...rows.value, ...response.data];
    }
  });
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

  if (query.trim().length == 0) {
    getDefaultData(null);
  }
}

function handleChange(option) {
  if (!selectedRowsRefs.value.includes(option[props.primaryKey])) {
    selectedRows.value.push(option);
  } else {
    const index = selectedRowsRefs.value.findIndex(
      (item) => item == option[props.primaryKey]
    );
    if (index !== -1) {
      selectedRows.value.splice(index, 1);
    }
  }
  emits("selected", option);
  setTimeout(() => {
    emits("update:modelValue", selectedRowsRefs.value);
  }, 1);
  searchActive.value = false;
}

function clearAll() {
  selectedRows.value = [];
  rows.value = [];
}

function removeDuplicates(array) {
  const uniqueIds = new Set(); // Using Set to keep track of unique ids
  return array.filter((obj) => {
    if (uniqueIds.has(obj[props.primaryKey])) {
      return false; // If id is already in the set, filter out the duplicate
    } else {
      uniqueIds.add(obj[props.primaryKey]); // Add the id to the set
      return true; // Keep the object
    }
  });
}

onBeforeMount(() => {
  if (props.records.length) {
    rows.value = [...props.records];
  }
});

onMounted(() => {
  if (props.loadDefaults) {
    getDefaultData(null);
  }
});

const modelValueRef = toRefs(props).modelValue;
const urlRef = toRefs(props).url;
const recordsRef = toRefs(props).records;

watch(
  [modelValueRef, urlRef, recordsRef],
  () => {
    if (recordsRef.value.length) {
      rows.value = removeDuplicates([...rows.value, ...recordsRef.value]);
    }
  },
  { deep: true }
);

function getDefaultValue(array) {
  const ids = array.map((id) => Number(id));
  const options = rows.value.filter((row) =>
    ids.includes(row[props.primaryKey])
  );
  options.forEach((option) => {
    selectedRows.value.push(option);
  });
}

defineExpose({ clearAll, getDefaultValue });
</script>
<template>
  <div class="dropdown w-100">
    <div
      class="dropdown-toggle ui-select-wrapper form-control"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      :class="{
        'is-loading': isRequesting,
      }"
      style="
        transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
        position: relative;
      "
    >
      <ul
        class="list-inline mb-0"
        style="
          position: relative;
          display: flex;
          flex: auto;
          flex-wrap: wrap;
          max-width: 100%;
        "
      >
        <li
          class="list-inline-item badge bg-primary me-1 mb-1 option"
          v-for="row in selectedRows"
          :key="row[props.primaryKey]"
        >
          <a
            href="#!"
            @click.prevent="handleChange(row)"
            class="text-white text-capitalize text-decoration-none d-flex align-items-center"
          >
            <span class="text-white me-1">{{ row[props.label] }}</span>
            <svg
              width="18px"
              height="18px"
              viewBox="0 0 16 16"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
              ></path>
            </svg>
          </a>
        </li>
      </ul>
      <span v-if="!selectedRows.length">{{ props.placeholder }}</span>
      <svg
        class="svg-next-icon svg-next-icon-size-16"
        viewBox="0 0 16 16"
        xmlns="http://www.w3.org/2000/svg"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M3.646 9.146a.5.5 0 0 1 .708 0L8 12.793l3.646-3.647a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 0-.708zm0-2.292a.5.5 0 0 0 .708 0L8 3.207l3.646 3.647a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 0 0 0 .708z"
        ></path>
      </svg>
    </div>
    <div class="dropdown-menu dropdown-mega-lg shadow-lg p-4 mt-1 w-100">
      <input
        type="search"
        class="form-control mb-2"
        placeholder="Search..."
        @focus="searchActive = true"
        v-model="searchKey"
        @input="handleSearch($event)"
      />
      <ul
        class="custom__options"
        :class="{
          'spinner-border spinner-border-lg text-primary': isRequesting,
        }"
      >
        <li
          class="d-flex align-items-center border border-light"
          @click="handleChange(row)"
          v-for="(row, i) in filteredRows"
          :key="'item' + i"
          :class="{
            selected: selectedRowsRefs.includes(row[props.primaryKey]),
          }"
        >
          <span> {{ row[props.label] }}</span>
          <span class="ms-auto">
            <lord-icon
              src="https://cdn.lordicon.com/oqdmuxru.json"
              trigger="loop"
              delay="500"
              colors="primary:#fffff"
              style="width: 30px; height: 30px"
              v-if="selectedRowsRefs.includes(row[props.primaryKey])"
            >
            </lord-icon>
          </span>
        </li>
      </ul>
      <span class="py-2" v-if="!isRequesting && !rows.length"
        >No data to display</span
      >
    </div>
  </div>
</template>
<style scoped>
.is-loading {
  background-position: right calc(0.375em + 0.1875rem) center;
  background-repeat: no-repeat;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
  background-image: url("./loading_48px.gif");
}
.is-loading .svg-next-icon {
  display: none !important;
}
svg {
  vertical-align: baseline;
}
.option {
  flex: none;
  align-self: center;
  max-width: 100%;
  display: inline-flex;
}
</style>
