<template>
  <nav class="text-align-center" v-if="totalPages > 0">
    <ul class="pagination justify-content-center mb-0">
      <li class="page-item" :class="{ disabled: prev == 0 }">
        <a class="page-link" href="#" @click.prevent="updatePage(prev)">Prev</a>
      </li>

      <li class="page-item" v-if="firstPage">
        <a class="page-link" href="javascript:void(0)">...</a>
      </li>
      <li
        class="page-item"
        :class="{ active: current_page == page + 1 }"
        v-for="(page, index) in pages"
        :key="index"
      >
        <a
          class="page-link"
          @click="updatePage(page + 1)"
          href="javascript:void(0)"
          >{{ page + 1 }}</a
        >
      </li>
      <li class="page-item" v-if="lastPage">
        <a class="page-link" href="javascript:void(0)">...</a>
      </li>
      <li class="page-item" :class="{ disabled: next == totalPages + 1 }">
        <a class="page-link" @click="updatePage(next)" href="javascript:void(0)"
          >Next</a
        >
      </li>
      <li class="ms-auto">
        <label class="d-flex flex-nowrap align-items-center"
          ><span class="d-none d-sm-inline-block mb-0 fw-bold">Show</span>
          <div class="form-control-select mx-2">
            <select
              class="tabulator-page-size"
              v-model="perPage"
              @change="handleChange($event)"
            >
              <option value="45">45</option>
              <option value="90">90</option>
              <option value="150">150</option>
              <option value="10000000000000000000">All Records</option>
            </select>
          </div>
          <div
            class="dataTables_info"
            style="padding: 0.5rem 0; white-space: nowrap; ont-size: 0.75rem"
          >
            {{ from }} - {{ to }} of {{ total_records }}
          </div>
        </label>
      </li>
    </ul>
  </nav>
  <div v-else class="text-center w-100 cursor-pointer">
    <svg
      v-if="isRequesting"
      viewBox="0 0 16 16"
      fill="none"
      width="32"
      height="32"
      class="_2deea771"
      style="box-sizing: content-box; color: black; vertical-align: top"
    >
      <circle
        cx="8"
        cy="8"
        r="7"
        stroke="currentColor"
        stroke-opacity="0.25"
        stroke-width="2"
        vector-effect="non-scaling-stroke"
      ></circle>
      <path
        d="M15 8a7.002 7.002 0 00-7-7"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        vector-effect="non-scaling-stroke"
      ></path>
    </svg>
    <h6 class="mb-0 text-muted" v-else>No data to display</h6>
  </div>
</template>
<script>
export default {
  name: "Paginations",
  props: {
    current_page: Number,
    row_count_page: Number,
    total_records: Number,
    page_range: Number,
    from: Number,
    to: Number,
    dotsIcon: {
      type: String,
      default: "icon ni ni-more-h",
    },
  },
  data: () => ({
    perPage: 45,
  }),
  computed: {
    prev() {
      return this.current_page - 1;
    },
    next() {
      return this.current_page + 1;
    },
    rangeStart() {
      var start = this.current_page - this.page_range;
      return start > 0 ? start : 0;
    },
    rangeEnd() {
      var end = this.current_page + this.page_range;
      return end < this.totalPages ? end : this.totalPages;
    },
    pages() {
      var pages = [];
      for (var i = this.rangeStart; i < this.rangeEnd; i++) {
        pages.push(i);
      }
      return pages;
    },
    totalPages() {
      return Math.ceil(this.total_records / this.row_count_page);
    },
    firstPage() {
      return this.rangeStart !== 0;
    },
    lastPage() {
      return this.rangeEnd < this.totalPages;
    },
    showPrevLink() {
      return this.current_page == 0 ? false : true;
    },
    showNextLink() {
      return this.current_page == this.totalPages - 1 ? false : true;
    },
    isRequesting() {
      return this.isProcessing("PAGINATION_SEARCH");
    },
  },
  methods: {
    updatePage(pageNumber) {
      this.$emit("page-update", {
        page_number: pageNumber,
        per_page: this.perPage,
      });
    },
    handleChange(e) {
      this.$emit("page-update", { page_number: 1, per_page: e.target.value });
    },
  },
};
</script>
<style scoped>
.tabulator-page-size {
  display: inline-block;
  margin: 0 5px;
  color: inherit;
  padding: 0.375rem 1rem 0.375rem 0.75rem;
  border: 1px solid #f0f3f8;
  background-color: #fff;
  background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAxNiAxNic+PHBhdGggZmlsbD0nbm9uZScgc3Ryb2tlPScjNzU4NjhmJyBzdHJva2UtbGluZWNhcD0ncm91bmQnIHN0cm9rZS1saW5lam9pbj0ncm91bmQnIHN0cm9rZS13aWR0aD0nMicgZD0nTTIgNWw2IDYgNi02Jy8+PC9zdmc+);
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 16px 12px;
  border-radius: 0.4375rem;
  appearance: none;
}
</style>
