import { reactive, ref } from "vue";
import { useHttp } from "@/composition-api/hooks/useHttp";
import { encodeQuery } from "@/common/utils";
import { useLayout } from "@/composition-api/hooks/useLayout";

const { appSettings } = useLayout();
const currentPage = ref(0);
const rowCountPage = ref(45);
const totalRows = ref(0);
const pageRange = ref(10);
const recordsFrom = ref(0);
const recordsTo = ref(0);
const rows = ref([]);
const searchKeyword = ref("");

const { http, onGoingRequestFlag, isProcessing, requireProgress } = useHttp();
const newFilter = reactive({
  from: new Date(appSettings.today)
    .toLocaleString("fr-CA", { timeZone: appSettings.timezone })
    .slice(0, 10),
  to: new Date(appSettings.today)
    .toLocaleString("fr-CA", { timeZone: appSettings.timezone })
    .slice(0, 10),
});
const fetchUrl = ref(null);

export function usePaginations() {
  const getPaginationData = (url, initialFetch = false) => {
    if(initialFetch) {
      fetchUrl.value = url;
    }
    rows.value = [];
    onGoingRequestFlag.value = "PAGINATION_SEARCH";
    requireProgress.value = true;
    http.get(url).then((response) => {
      rows.value = response.data.rows.data;
      currentPage.value = response.data.rows.current_page;
      totalRows.value = response.data.rows.total;
      recordsFrom.value = response.data.rows.from;
      recordsTo.value = response.data.rows.to;
    });
  };

  const displayPicker = (e) => {
    e.target.showPicker();
  };

  const filterRecords = () => {
    onGoingRequestFlag.value = "PAGINATION_SEARCH";
    requireProgress.value = true;
    getPaginationData(encodeQuery(fetchUrl.value, newFilter));
  };

  const handleSearch = () => {
    if (isProcessing("PAGINATION_SEARCH")) {
      return;
    }
    setTimeout(() => {
      if (searchKeyword.value) {
        if (searchKeyword.value.trim().length >= 3) {
          newFilter.q = searchKeyword.value;
          filterRecords();
        }
      } else {
        delete newFilter.q;
        filterRecords();
      }
    }, 250);
  };

  const resetFilters = (url) => {
    Object.assign(newFilter, {
      from: new Date(appSettings.today)
        .toLocaleString("fr-CA", { timeZone: appSettings.timezone })
        .slice(0, 10),
      to: new Date(appSettings.today)
        .toLocaleString("fr-CA", { timeZone: appSettings.timezone })
        .slice(0, 10),
    });
    getPaginationData(url);
  };

  return {
    currentPage,
    rowCountPage,
    totalRows,
    pageRange,
    recordsFrom,
    recordsTo,
    rows,
    newFilter,
    getPaginationData,
    fetchUrl,
    displayPicker,
    filterRecords,
    resetFilters,
    searchKeyword,
    handleSearch,
  };
}
