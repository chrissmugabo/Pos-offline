<template>
  <div style="min-width: 180px">
    <multiselect
      v-model="selectedRecords"
      id="ajax"
      :label="label"
      :track-by="tracker"
      :placeholder="placeholder"
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
    </multiselect>
  </div>
</template>
<script>
import { useHttp } from "@/composition-api/composition-api/hooks/useHttp";
import Multiselect from "@vueform/multiselect";
import { encodeQuery } from "@/composition-api/composition-api/utils/helpers";
const { http, onGoingRequestFlag, isProcessing, requireProgress } = useHttp();
export default {
  name: "TypeAhead",
  emits: ["changed", "update:modelValue"],
  modelValue: { required: false },
  props: {
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
  },
  components: {
    Multiselect,
  },
  data: () => ({
    selectedRecords: [],
    records: [],
    isLoading: false,
  }),
  created() {
    if (this.loadDefaults) {
      this.getDefaultData();
    }
  },
  methods: {
    getDefaultData(url = null) {
      let fetchUrl = url || this.url;
      //this.$store.commit("SET_REQUEST_FLAG", "AUTOCOMPLETE_REQUEST");
      http.get(fetchUrl).then((response) => {
        if (response.data.rows) this.records = response.data.rows;
        else this.records = response.data || [];
        this.isLoading = false;
      });
    },
    limitText(count) {
      return `and ${count} other records`;
    },
    handleSearch(query) {
      if (query.trim().length > 0) {
        this.isLoading = true;
        //this.$store.commit("SET_REQUEST_FLAG", "AUTOCOMPLETE_REQUEST");
        http
          .get(encodeQuery(this.url, { query: query }))
          .then((response) => {
            if (response.data.rows) this.records = response.data.rows;
            else this.records = response.data || [];
            this.isLoading = false;
          });
      }
    },
    handleChange(event) {
      this.$emit("changed", event);
    },
    clearAll() {
      this.selectedRecords = [];
      this.records = [];
    },
  },
  watch: {
    url: {
      handler: function () {
        this.clearAll();
        if (this.loadDefaults) {
          this.getDefaultData();
        }
      },
      deep: true,
    },
  },
  // mounted() {
  //   this.clearAll();
  // },
};
</script>
<style src="@vueform/multiselect/themes/default.css"></style>
