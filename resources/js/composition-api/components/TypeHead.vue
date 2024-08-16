

<template>
  <div style="min-width: 180px">
    <Multiselect
      v-model="selectedRecords"
      id="ajax"
      :label="props.label"
      :track-by="props.tracker"
      :placeholder="props.placeholder"
      open-direction="bottom"
      :options="records"
      :multiple="false"
      :searchable="true"
      :loading="isLoading"
      :internal-search="false"
      :clear-on-select="true"
      :close-on-select="true"
      :options-limit="300"
      :show-no-results="false"
      :hide-selected="false"
      @search-change="handleSearch"
      @select="$emit('update:modelValue', $event)"
    >
    </Multiselect>
  </div>
</template>
<script setup>
import Multiselect from "@vueform/multiselect";
import { encodeQuery } from "@/common/utils";
import { useHttp } from "@/composition-api/hooks/useHttp";
import { onBeforeMount, ref, watch, toRefs } from "vue";

const emits = defineEmits(["changed", "update:modelValue"]);
const props = defineProps({
  modelValue: { required: false },
  url: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  tracker: {
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
});
const { http, onGoingRequestFlag, isProcessing, requireProgress } = useHttp();
const selectedRecords = ref(null);
const records = ref([]);
const isLoading = ref(false);
onBeforeMount(() => {
  if (props.loadDefaults) {
    getDefaultData();
  }
});

function getDefaultData(url = null) {
  const fetchUrl = url || props.url;
  onGoingRequestFlag.value = "AUTOCOMPLETE_REQUEST";
  http.get(fetchUrl).then((response) => {
    if (response.data.rows) records.value = response.data.rows;
    else records.value = response.data || [];
    isLoading.value = false;
  });
}
function limitText(count) {
  return `and ${count} other records`;
}
function handleSearch(query) {
  if (query.trim().length > 0) {
    isLoading.value = true;
    onGoingRequestFlag.value = "AUTOCOMPLETE_REQUEST";
    http.get(encodeQuery(props.url, { query: query })).then((response) => {
      if (response.data.rows) records.value = response.data.rows;
      else records.value = response.data || [];
      isLoading.value = false;
    });
  }
}
function handleChange(event) {
  emits("changed", event);
}
function clearAll() {
  selectedRecords.value = null;
  records.value = [];
}

const urlRef = toRefs(props).url;
watch(
  [toRefs],
  () => {
    if (urlRef.value) {
      clearAll();
      getDefaultData(null);
    }
  },
  { deep: true }
);
</script>

<style src="@vueform/multiselect/themes/default.css"></style>
