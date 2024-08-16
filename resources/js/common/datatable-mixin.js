import Paginations from "@/components/Paginations.vue";
import { Printd } from "printd";
export const dataTableService = {
  components: {
    pagination: Paginations,
  },
  data: () => ({
    rows: [],
    currentPage: 0,
    rowCountPage: 45,
    totalRows: 0,
    pageRange: 10,
    recordsFrom: 0,
    recordsTo: 0,
    fetchUrl: null,
    fetchedData: {},
    editIndex: null,
    newFilter: {
      from: new Date(TODAY)
        .toLocaleString("fr-CA", { timeZone: TIMEZONE })
        .slice(0, 10),
      to: new Date(TODAY)
        .toLocaleString("fr-CA", { timeZone: TIMEZONE })
        .slice(0, 10),
    },
    checkedRows: [],
    checkAll: false,
  }),
  computed: {
    hasKeyword() {
      return this.filterKey != null && this.filterKey.trim().length >= 2;
    },
    reportLabel() {
      let from = !this.newFilter.from
        ? new Date(TODAY)
            .toLocaleString("fr-CA", { timeZone: TIMEZONE })
            .slice(0, 10)
        : this.newFilter.from;
      if (!this.newFilter.to) return this.$helper.formatDate(from);
      else
        return `${this.$helper.formatDate(from)} - ${this.$helper.formatDate(
          this.newFilter.to
        )}`;
    },
  },
  created() {
    if (this.fetchUrl) this.handlePaginationSearch();
  },
  watch: {
    checkAll() {
      var checkbox = document.querySelectorAll(".row-checker");
      if (this.checkAll)
        checkbox.forEach((elt) => {
          if (!elt.checked) elt.click();
        });
      else
        checkbox.forEach((elt) => {
          if (elt.checked) elt.click();
        });
    },
  },
  methods: {
    displayPicker(e) {
      e.target.showPicker();
    },
    resetFilters() {
      this.newFilter = {
        from: new Date(TODAY)
          .toLocaleString("fr-CA", { timeZone: TIMEZONE })
          .slice(0, 10),
        to: new Date(TODAY)
          .toLocaleString("fr-CA", { timeZone: TIMEZONE })
          .slice(0, 10),
      };
      this.handlePaginationSearch();
    },
    handlePaginationSearch(fetchUrl = null) {
      let url = this.fetchUrl;
      if (typeof fetchUrl == "string") {
        url = fetchUrl;
      }
      if (this.hasKeyword) {
        if (url.indexOf("?") > -1)
          url += `&q=${this.filterKey}&per_page=${this.rowCountPage}`;
        else url += `?q=${this.filterKey}&per_page=${this.rowCountPage}`;
      }
      this.$store.commit("SET_REQUEST_FLAG", "PAGINATION_SEARCH");
      this.$http.get(url).then((response) => {
        this.rows = response.data.rows.data;
        this.currentPage = response.data.rows.current_page;
        this.totalRows = response.data.rows.total;
        this.recordsFrom = response.data.rows.from;
        this.recordsTo = response.data.rows.to;
        this.fetchedData = response.data;
      });
    },
    handlePagination(obj) {
      this.rowCountPage = Number(obj.per_page);
      var url = `${this.fetchUrl}?page=${obj.page_number}&per_page=${obj.per_page}&`;
      if (this.filterKey) {
        url = `${this.fetchUrl}?page=${obj.page_number}&per_page=${obj.per_page}&q=${this.filterKey}&`;
      }
      let data = this.newFilter;
      for (let d in data) {
        if (!this.$helper.empty(data[d]))
          url +=
            encodeURIComponent(d) + "=" + encodeURIComponent(data[d]) + "&";
      }
      this.$http.get(url).then((response) => {
        this.rows = response.data.rows.data;
        this.currentPage = response.data.rows.current_page;
        this.recordsFrom = response.data.rows.from;
        this.recordsTo = response.data.rows.to;
        this.totalRows = response.data.rows.total;
      });
    },
    computeUserAvatar(user) {
      var firstname = user.first_name[0];
      var lastname = user.last_name ? user.last_name[0] : "";
      return `${firstname} ${lastname}`;
    },
    /*getDateDifference(date, otherDate) {
      const diffDays = (date, otherDate) =>
        Math.ceil(Math.abs(date - otherDate) / (1000 * 60 * 60 * 24));
      return diffDays;
    }, */
    encodeQuery(url, data) {
      let query = "";
      for (let d in data) {
        if (
          !this.$helper.empty(data[d]) &&
          url.indexOf(`?${d}`) < 0 &&
          url.indexOf(`&${d}`) < 0
        )
          query +=
            encodeURIComponent(d) + "=" + encodeURIComponent(data[d]) + "&";
      }
      return url.indexOf("?") > -1
        ? `${url}&${query.slice(0, -1)}`
        : `${url}?${query.slice(0, -1)}`;
    },
    handlePaginationFilter() {
      let fetchUrl = this.encodeQuery(this.fetchUrl, this.newFilter);
      this.$store.commit("SET_REQUEST_FLAG", "PAGINATION_SEARCH");
      this.handlePaginationSearch(fetchUrl);
    },
    handlePrint(elementId, css = []) {
      const styles = [
        `${location.origin}/css/custom.css`,
        `${location.origin}/css/bootstrap.min.css`,
      ];
      const d = new Printd();
      d.print(document.querySelector(elementId), [...styles, ...css]);
    },
    handleExcelExport(tableId = null, excelFilename) {
      if (!tableId) {
        tableId = "table.table";
      } else {
        tableId = "#" + tableId;
      }
      const container = document.querySelector(tableId);
      const head = container.querySelector("thead");
      const headers = [
        ...head.querySelectorAll("th:not(.action):not(.check)"),
      ].map((item) => item.textContent);
      const dataset = [];
      [...container.querySelectorAll("tbody tr")].forEach((row) => {
        let arr = [...row.querySelectorAll("td:not(.action):not(.check)")].map(
          (item) => item.textContent
        );
        dataset.push(arr);
      });
      /**
       * Creating a temporary form
       */
      const dataForm = document.createElement("form");
      dataForm.target = "_blank";
      dataForm.method = "POST";
      dataForm.action = "https://tameaps.com/export.php";
      const records = document.createElement("input");
      const file_name = document.createElement("input");
      const headings = document.createElement("input");
      records.type = "hidden";
      file_name.type = "hidden";
      headings.type = "hidden";
      records.name = "dataset";
      records.value = JSON.stringify(dataset);
      file_name.name = "filename";
      file_name.value = excelFilename || document.title.replaceAll(" ", "-");
      headings.name = "columns";
      headings.value = JSON.stringify(headers);
      dataForm.appendChild(records);
      dataForm.appendChild(file_name);
      dataForm.appendChild(headings);
      dataForm.style.display = "none";
      document.body.appendChild(dataForm);
      dataForm.submit();
    },
  },
};

/**
 * 'en-CA'
  'fr-CA'
  'lt-LT'
  'sv-FI'
  'sv-SE'
 */
