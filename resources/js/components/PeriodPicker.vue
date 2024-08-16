<script>
export default {
  name: "PeriodPicker",
  emits: ["selected"],
  props: {
    period: {
      type: String,
      required: false,
    },
  },
  data: () => ({
    newFilter: {
      from: new Date(TODAY)
        .toLocaleString("fr-CA", { timeZone: TIMEZONE })
        .slice(0, 10),
      to: new Date(TODAY)
        .toLocaleString("fr-CA", { timeZone: TIMEZONE })
        .slice(0, 10),
    },
    dateRanges: null,
    currentPeriod: "TODAY",
  }),
  beforeCreate() {
    this.$http.get("dates-range").then((response) => {
      this.dateRanges = response.data;
    });
  },
  mounted() {
    if (this.period) {
      this.currentPeriod = this.period;
    }
  },
  methods: {
    handleFilter(period) {
      this.currentPeriod = period;
      this.$emit("selected", this.dateRanges[period]);
    },
    toggleCustomFilter() {
      this.currentPeriod = "CUSTOM";
      setTimeout(() => {
        document.querySelector("#data-filter-toggler").click();
      }, 2);
    },
    handleCustomFilter() {
      this.$emit("selected", [this.newFilter.from, this.newFilter.to]);
    },
  },
};
</script>
<template>
  <div class="dropdown filter-dropdown date-filter dropdown-menu-end">
    <a
      href="javascript:void(0)"
      class="dropdown-toggle zf-daterange-picker d-flex align-items-center ember-view form-control cursor-pointer"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      id="data-filter-toggler"
    >
      <em class="icon uil uil-calender fs-4"></em>
      <span class="range-text ms-2 text-capitalize">{{
        $helper.inputTitle(
          $helper.capitalizeFirstLetter(currentPeriod.toLowerCase())
        )
      }}</span>

      <em class="icon uil uil-caret-down-fill ms-auto"></em>
    </a>

    <div class="daterangepicker dropdown-menu dropdown-menu-left" style="">
      <div
        class="ranges"
        :class="{
          'd-flex': currentPeriod == 'CUSTOM',
        }"
        v-if="dateRanges"
      >
        <ul>
          <li
            :class="{
              active: currentPeriod == 'TODAY',
            }"
            @click="handleFilter('TODAY')"
          >
            Today
          </li>
          <li
            :class="{
              active: currentPeriod == 'THIS_WEEK',
            }"
            @click="handleFilter('THIS_WEEK')"
          >
            This Week
          </li>
          <li
            :class="{
              active: currentPeriod == 'CURRENT_MONTH',
            }"
            @click="handleFilter('CURRENT_MONTH')"
          >
            This Month
          </li>
          <li
            :class="{
              active: currentPeriod == 'THIS_YEAR',
            }"
            @click="handleFilter('THIS_YEAR')"
          >
            This Year
          </li>
          <li
            :class="{
              active: currentPeriod == 'YEAR_TO_DATE',
            }"
            @click="handleFilter('YEAR_TO_DATE')"
          >
            Year To Date
          </li>
          <li
            :class="{
              active: currentPeriod == 'YESTERDAY',
            }"
            @click="handleFilter('YESTERDAY')"
          >
            Yesterday
          </li>
          <li
            :class="{
              active: currentPeriod == 'LAST_WEEK',
            }"
            @click="handleFilter('LAST_WEEK')"
          >
            Previous Week
          </li>
          <li
            :class="{
              active: currentPeriod == 'PREVIOUS_MONTH',
            }"
            @click="handleFilter('PREVIOUS_MONTH')"
          >
            Previous Month
          </li>
          <li
            :class="{
              active: currentPeriod == 'LAST_YEAR',
            }"
            @click="handleFilter('LAST_YEAR')"
          >
            Previous Year
          </li>
          <li
            :class="{
              active: currentPeriod == 'CUSTOM',
            }"
            @click="toggleCustomFilter()"
          >
            Custom
          </li>
        </ul>
        <div
          class="custom-ranges border-left d-block px-2 ms-2"
          v-if="currentPeriod == 'CUSTOM'"
        >
          <div class="custom-ranger-wraper">
            <div class="date-picker">
              <div class="form-group mb-2">
                <label for="">From:</label>
                <input
                  type="date"
                  class="form-control"
                  v-model="newFilter.from"
                />
              </div>
              <div class="form-group">
                <label for="">To:</label>
                <input
                  type="date"
                  class="form-control"
                  v-model="newFilter.to"
                />
              </div>
            </div>
            <div class="bottom-0 d-flex justify-content-between">
              <button
                class="btn btn-sm btn-primary"
                type="button"
                @click="handleCustomFilter"
              >
                Apply
              </button>
              <button
                class="btn btn-sm btn-secondary ms-1"
                type="button"
                @click="currentPeriod = ''"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.date-filter {
  min-width: 160px;
  display: inline-block;
}
.daterangepicker .ranges {
  font-size: 11px;
  float: none;
  margin: 4px;
  text-align: left;
}
.daterangepicker .ranges ul {
  list-style: none;
  margin: 0 auto;
  padding: 0;
  width: 100%;
}
.daterangepicker .ranges li {
  font-size: 13px;
  background: #f5f5f5;
  border: 1px solid #f5f5f5;
  color: #141543;
  padding: 3px 12px;
  margin-bottom: 8px;
  border-radius: 5px;
  cursor: pointer;
}
.daterangepicker .ranges li:hover {
  background: #141543;
  border: 1px solid #141543;
  color: #fff;
}
@media (min-width: 564px) {
  .daterangepicker .ranges ul {
    width: 160px;
  }
  .daterangepicker .ranges {
    float: left;
  }
}
@media (min-width: 730px) {
  .daterangepicker .ranges {
    width: auto;
    float: left;
  }
}
.daterangepicker .ranges li {
  border-radius: 0;
  border: 0;
  background: 0 0;
  margin-bottom: 2px;
  padding: 6px 12px;
  color: #333;
}
.daterangepicker .ranges li:hover,
.daterangepicker .ranges li.active {
  border-radius: 0;
  border: 0;
  margin-left: -8px;
  margin-right: -14px;
  padding: 6px 20px;
  background-color: #141543;
  color: #fff;
}

.daterangepicker .ranges li:hover {
  background-color: #f5f5f5;
  color: #000;
}
.daterangepicker:before {
  position: absolute;
  top: -7px;
  left: 9px;
  display: inline-block;
  border-right: 7px solid transparent;
  border-bottom: 7px solid #ccc;
  border-left: 7px solid transparent;
  border-bottom-color: rgba(0, 0, 0, 0.2);
  content: "";
}
</style>
