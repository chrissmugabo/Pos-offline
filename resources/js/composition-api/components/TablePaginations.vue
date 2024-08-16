<script setup>
import { handlePrint } from "@/composition-api/utils/pagePrint";
import { baseURL } from "@/common/utils";
import { useHttp } from "@/composition-api/hooks/useHttp";
import { usePaginations } from "@/composition-api/hooks/usePaginations";
import { computed, onBeforeMount, ref } from "vue";
const props = defineProps({
  url: String,
  columns: Array,
  excelFilename: { type: String, default: "" },
  printTitle: { type: String, default: "" },
});

const { onGoingRequestFlag, isProcessing, requireProgress } = useHttp();

const {
  currentPage,
  rowCountPage,
  totalRows,
  pageRange,
  recordsFrom,
  recordsTo,
  rows,
  newFilter,
  getPaginationData,
  searchKeyword,
  handleSearch,
} = usePaginations();

const isRequesting = computed(() => {
  return isProcessing("PAGINATION_SEARCH");
});

const prev = computed(() => {
  return currentPage.value - 1;
});
const next = computed(() => {
  return currentPage.value + 1;
});
const rangeStart = computed(() => {
  var start = currentPage.value - pageRange.value;
  return start > 0 ? start : 0;
});
const rangeEnd = computed(() => {
  var end = currentPage.value + pageRange.value;
  return end < totalPages.value ? end : totalPages.value;
});

const pages = computed(() => {
  var pages = [];
  for (var i = rangeStart.value; i < rangeEnd.value; i++) {
    pages.push(i);
  }
  return pages;
});

const totalPages = computed(() => {
  return Math.ceil(totalRows.value / rowCountPage.value);
});

const firstPage = computed(() => {
  return rangeStart.value !== 0;
});

const lastPage = computed(() => {
  return rangeEnd.value < totalPages.value;
});

const showPrevLink = computed(() => {
  return currentPage.value == 0 ? false : true;
});

const showNextLink = computed(() => {
  return currentPage.value == totalPages.value - 1 ? false : true;
});

const perPage = ref(10);

const sortableColumns = props.columns.filter(
  (column) => column.sortable === true
);

onBeforeMount(() => {
  getPaginationData(props.url, true);
});

function handlePagination(obj) {
  rowCountPage.value = Number(obj.per_page);
  let url = `${props.url}?page=${obj.page_number}&per_page=${obj.per_page}&`;
  let data = newFilter;
  for (let d in data) {
    if (data[d])
      url += encodeURIComponent(d) + "=" + encodeURIComponent(data[d]) + "&";
  }
  onGoingRequestFlag.value = "PAGINATION_SEARCH";
  requireProgress.value = true;
  getPaginationData(url);

  /*http.get(url).then((response) => {
    rows.value = response.data.rows;
    currentPage.value = response.data.current_page;
    totalRows.value = response.data.total;
    recordsFrom.value = response.data.from;
    recordsTo.value = response.data.to;
  });
  */
}

function updatePage(pageNumber) {
  handlePagination({
    page_number: pageNumber,
    per_page: perPage.value,
  });
}

function handleChange(e) {
  handlePagination({
    page_number: 1,
    per_page: e.target ? e.target.value : 0,
  });
}
function handleExcelExport() {
  const container = document.querySelector("#pagination-table");
  if (container) {
    //HTMLTableSectionElement
    const head = container.querySelector("thead");
    const headers = [
      ...head.querySelectorAll("th:not(.action):not(.check):not(:last-child)"),
    ].map((item) => item.textContent);
    const dataset = [];
    [...container.querySelectorAll("tbody tr")].forEach((row) => {
      const arr = [
        ...row.querySelectorAll(
          "td:not(.action):not(.check):not(.text-end.text-nowrap):not(:last-child)"
        ),
      ].map((item) => item.textContent);
      dataset.push(arr);
    });
    /**
     * Creating a temporary form
     */
    const dataForm = document.createElement("form");
    dataForm.target = "_blank";
    dataForm.method = "POST";
    dataForm.action = `${baseURL}api/shared/export/excel`;
    const records = document.createElement("input");
    const file_name = document.createElement("input");
    const headings = document.createElement("input");
    records.type = "hidden";
    file_name.type = "hidden";
    headings.type = "hidden";
    records.name = "dataset";
    records.value = JSON.stringify(dataset);
    file_name.name = "filename";
    file_name.value =
      props.excelFilename || document.title.replaceAll(" ", "-");
    headings.name = "columns";
    headings.value = JSON.stringify(headers);
    dataForm.appendChild(records);
    dataForm.appendChild(file_name);
    dataForm.appendChild(headings);
    dataForm.style.display = "none";
    document.body.appendChild(dataForm);
    dataForm.submit();
  }
}
</script>
<template>
  <div class="row gy-3">
    <div class="col-sm-4">
      <slot name="action"></slot>
    </div>

    <div class="col-sm-auto ms-auto">
      <div class="d-flex gap-3">
        <div class="search-box">
          <input
            type="text"
            class="form-control"
            placeholder="Search here..."
            v-model="searchKeyword"
            @keyup="handleSearch"
          />
          <i class="ri-search-line search-icon fs-16"></i>
        </div>
        <div class="d-flex flex-nowrap css-792liz">
          <button
            class="css-1ihdyg6"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#paginationFilters"
          >
            Filter
          </button>
          <hr />
          <div class="">
            <button
              type="button"
              id="dropdownMenuLink1"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              class="css-1ihdyg6"
            >
              <span class="me-1"> Sort </span><i class="ri-sort-desc fs-18"></i>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
              <li v-for="(column, i) in sortableColumns" :key="i">
                <a class="dropdown-item" href="javascript:void(0)">{{
                  column.title
                }}</a>
              </li>
            </ul>
          </div>
          <hr />
          <button
            class="css-1ihdyg6"
            type="button"
            @click="handleExcelExport()"
          >
            <i class="ri-file-excel-line fs-4"></i>
          </button>
          <hr />
          <button
            class="css-1ihdyg6"
            type="button"
            @click="handlePrint(props.printTitle, '#pagination-content')"
          >
            <i class="ri-printer-line fs-4"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="row pb-2">
    <div class="col-12">
      <div id="paginationFilters" class="collapse">
        <slot name="filters"></slot>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive" id="pagination-content">
            <table
              class="table table-hover table-nowrap align-middle mb-0"
              id="pagination-table"
            >
              <thead class="table-danger">
                <tr class="text-capitalize">
                  <th
                    scope="col"
                    v-for="(column, i) in columns"
                    :key="'column' + i"
                    :style="column.styles"
                    class="fw-medium"
                  >
                    <span>{{ column.title }}</span>
                  </th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <slot v-if="rows.length > 0" name="rows"></slot>
                <template v-if="!rows.length">
                  <tr>
                    <td :colspan="props.columns.length + 1" class="text-center">
                      <svg
                        v-if="isRequesting"
                        viewBox="0 0 16 16"
                        fill="none"
                        width="32"
                        height="32"
                        class="_2deea771"
                        style="
                          box-sizing: content-box;
                          color: black;
                          vertical-align: top;
                        "
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
                      <div v-else class="text-center w-100 cursor-pointer">
                        <h6 class="mb-0 text-muted">No data to display</h6>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <template v-if="rows.length > 0">
    <div class="mt-0">
      <div class="d-grid d-md-inline-block">
        <label class="d-flex flex-nowrap align-items-center"
          ><span class="d-none d-sm-inline-block mb-0 fw-bold">Show</span>
          <div class="form-control-select mx-2">
            <select
              class="tabulator-page-size"
              v-model="perPage"
              @change="handleChange($event)"
            >
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
              <option value="25">25</option>
              <option value="30">30</option>
              <option value="35">35</option>
              <option value="45">45</option>
              <option value="90">90</option>
              <option value="150">150</option>
              <option value="10000000000000000000">All Records</option>
            </select>
          </div>
          <div
            class="dataTables_info"
            style="padding: 0.5rem 0; white-space: nowrap; font-size: 0.75rem"
          >
            {{ recordsFrom }} - {{ recordsTo }} of {{ totalRows }}
          </div>
        </label>
      </div>
      <nav class="text-center float-md-end mt-0 mt-md-0">
        <ul class="nav nav-sm nav-invert">
          <li class="nav-item" :class="{ disabled: prev == 0 }">
            <a class="nav-link px-3" href="#" @click.prevent="updatePage(prev)"
              ><span aria-hidden="true">&laquo;</span></a
            >
          </li>

          <li class="nav-item" v-if="firstPage">
            <a class="nav-link px-3" href="javascript:void(0)">...</a>
          </li>
          <li class="nav-item" v-for="(page, index) in pages" :key="index">
            <a
              class="nav-link px-3"
              :class="{ active: currentPage == page + 1 }"
              @click="updatePage(page + 1)"
              href="javascript:void(0)"
              >{{ page + 1 }}</a
            >
          </li>
          <li class="nav-item" v-if="lastPage">
            <a class="nav-link px-3" href="javascript:void(0)">...</a>
          </li>
          <li class="nav-item" :class="{ disabled: next == totalPages + 1 }">
            <a
              class="nav-link px-3"
              @click="updatePage(next)"
              href="javascript:void(0)"
              ><span aria-hidden="true">&raquo;</span></a
            >
          </li>
        </ul>
      </nav>
    </div>
  </template>
</template>
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
.css-1ihdyg6 {
  cursor: pointer;
}
.css-1ihdyg6 {
  margin: 0;
  padding: 0;
}
.css-1ihdyg6:focus {
  outline: 0;
}
.css-1ihdyg6 {
  background: none;
  border: 0;
  font: inherit;
}

.css-1ihdyg6 {
  border: 0px;
  border-radius: 4px;
  box-sizing: border-box;
  cursor: pointer;
  display: flex;
  font-size: 16px;
  font-weight: 600;
  height: 42px;
  line-height: 24px;
  outline: none;
  padding: 0px;
  position: relative;
  text-align: center;
  -webkit-box-align: center;
  align-items: center;
  -webkit-box-pack: center;
  justify-content: center;
  transition: width 0.2s ease-in-out 0s;
  user-select: none;
}
.css-1ihdyg6 {
  color: rgb(149, 151, 157);
  background-color: rgb(250, 250, 250);
  border-radius: 2px;
  display: flex;
  font-weight: 600;
  height: 38px;
  letter-spacing: 0.2px;
  line-height: 24px;
  padding: 0px 16px;
  width: auto !important;
}
.css-1ihdyg6:focus {
  outline: rgba(34, 35, 37, 0.4) solid 1px;
  outline-offset: -1px;
}
.css-1ihdyg6:hover {
  box-shadow: none;
  background-color: rgb(245, 245, 245);
  color: rgb(34, 35, 37);
}
.css-1ihdyg6:active {
  box-shadow: rgba(197, 198, 201, 0.4) 0px 1px 2px;
}
.css-1ihdyg6:disabled,
.css-1ihdyg6:disabled:hover {
  color: rgba(149, 151, 157, 0.4);
  cursor: not-allowed;
  box-shadow: none;
  outline-color: transparent;
  background-color: rgb(255, 255, 255);
}
hr {
  margin: 0;
  padding: 0;
  outline: 0;
}
.css-792liz > hr {
  background-color: rgb(228, 229, 231);
  border: 0px;
  height: 16px;
  width: 1px;
}
.css-792liz:hover > hr {
  background-color: transparent;
}
.nav-invert,
.nav-invert .nav-link {
  border-radius: 0.35rem;
}
.nav-invert {
  background-color: rgb(255, 255, 255);
  display: inline-flex;
  padding: 0.25rem;
}
.nav-sm .nav-link {
  font-size: 0.875rem;
  padding: 0.344rem 0.65rem;
}
.nav-invert,
.nav-invert .nav-link {
  border-radius: 0.35rem;
}
.nav-item > a {
  position: relative;
}
.nav-invert .nav-link.active {
  background-color: rgb(241, 244, 248);
  box-shadow: rgba(50, 50, 93, 0.08) 0px 1px 3px 0px,
    rgba(112, 157, 199, 0.08) 0px 4px 6px 0px;
  color: var(--bs-primary) !important;
}
</style>
