<script>
import { dataTableService } from "@/common/datatable-mixin.js";
import ZohoSelect from "@/composition-api/components/ZohoSelect.vue";
import ReceiptOne from "@/shared/ReceiptOne.vue";
import { permissionsHandler } from "@/common/permissions-handler";
import BootstrapModal from "@/components/BootstrapModal.vue";
import PrintHeader from "@/components/PrintHeader.vue";
export default {
  name: "SalesReport",
  mixins: [dataTableService, permissionsHandler],
  components: {
    ZohoSelect,
    ReceiptOne,
    BootstrapModal,
    PrintHeader,
  },
  data: () => ({
    selectedRow: null,
    paymentModes: [],
    colours: [
      "#8e44ad",
      "#f1c40f",
      "#e67e22",
      "#e74c3c",
      "#95a5a6",
      "#f39c12",
      "#d35400",
      "#c0392b",
      "#bdc3c7",
      "#7f8c8d",
    ],
    comment: null,
    deletedOrder: null,
  }),
  computed: {
    payments() {
      return this.rows.reduce((a, b) => [...a, ...b.payments], []);
    },
    paymentPercentages() {
      const result = [];
      this.paymentModes.forEach((mode) => {
        const payments = this.payments.filter(
          (payment) => payment.payment_type == mode.payment_type
        );
        result.push({
          mode: mode.name,
          amount: payments.reduce((a, b) => a + b.amount_paid, 0),
        });
      });
      return result;
    },
    grandTotal() {
      return this.rows.reduce((a, b) => a + b.grand_total, 0);
    },
    totalPaidAmount() {
      return this.rows.reduce((a, b) => a + b.paid_amount, 0);
    },
  },
  created() {
    this.fetchUrl = `reports/sales?is-front=1&is-cashier=1`;
    this.handlePaginationSearch();
  },
  methods: {
    handleDatesFilter(dates = []) {
      if (Array.isArray(dates) && dates.length > 1) {
        this.newFilter.from = dates[0];
        this.newFilter.to = dates[1];
        this.handlePaginationFilter();
      }
    },
    handleDestroy() {
      this.$http
        .post(
          `orders/delete`,
          this.$helper.generateFormData({
            order_id: this.deletedOrder.id,
            comment: this.comment,
          })
        )
        .then((response) => {
          if (response.data.status) {
            let index = this.rows.findIndex(
              (item) => item.id == this.deletedOrder.id
            );
            this.rows.splice(index, 1);
            this.deletedOrder = null;
            this.comment = null;
            this.toggleModal();
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "success",
              message: "Sales deleted successfully",
            });
          }
        });
    },
    selectRow(row) {
      this.selectedRow = row;
      //this.toggleModal();
    },
  },
  mounted() {
    this.$http.get("shared/used-payment-modes").then((response) => {
      this.paymentModes = response.data.rows;
    });
  },
};
</script>
<template>
  <div class="content__boxed">
    <div class="content__wrap">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-8 d-flex align-items-center mb-1">
              <div class="ms-1">
                <zoho-select
                  url="clients/search"
                  label="name"
                  primary-key="id"
                  placeholder="select a client"
                  v-model="newFilter.client"
                  :load-defaults="true"
                  :records="[{ id: 'walk-in', name: 'Walk-in Customer' }]"
                  fetch-flag="SEARCHING_FOR_CLIENTS"
                ></zoho-select>
              </div>
              <div class="ms-1" v-if="isCashier">
                <zoho-select
                  url="users/search?role=waiters"
                  label="name"
                  primary-key="id"
                  placeholder="select a waiter"
                  v-model="newFilter.waiter"
                  :load-defaults="true"
                  fetch-flag="SEARCHING_FOR_WAITERS"
                ></zoho-select>
              </div>
            </div>
            <div
              class="col-md-4 d-flex gap-1 align-items-center justify-content-md-end mb-1"
            >
              <button class="btn btn-primary" @click="handlePaginationFilter">
                <span>Apply</span>
              </button>
              <button
                class="btn btn-icon btn-outline-primary"
                @click="resetFilters"
              >
                <i class="demo-pli-refresh fs-5"></i>
              </button>
              <template v-if="rows.length">
                <button
                  class="btn btn-icon btn-outline-light"
                  @click="handlePrint('#PaginationTable')"
                >
                  <i class="demo-pli-printer fs-5"></i>
                </button>
                <button
                  class="btn btn-icon btn-outline-light"
                  @click="handleExcelExport('PaginationTable')"
                >
                  <i class="demo-pli-file-excel fs-5"></i>
                </button>
              </template>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="w-100 p-3 mb-4 rounded border shadow">
            <div class="d-flex align-items-center justify-content-between">
              <div class="ms-0">
                <h4 class="mb-1">{{ formatMoney(grandTotal) }}</h4>
                <h6 class="fw-normal mb-0">Total Amount</h6>
              </div>
              <div class="ms-0">
                <h4 class="mb-1">{{ formatMoney(totalPaidAmount) }}</h4>
                <h6 class="fw-normal mb-0">Paid Amount</h6>
              </div>
              <div class="ms-0">
                <h4 class="mb-1">
                  {{ formatMoney(grandTotal - totalPaidAmount) }}
                </h4>
                <h6 class="fw-normal mb-0">Unpaid Amount</h6>
              </div>
            </div>
            <hr />
            <div class="d-flex align-items-center justify-content-between pt-0">
              <div
                class="d-flex align-items-center"
                v-for="(item, i) in paymentPercentages"
                :key="i"
              >
                <div
                  class="dot"
                  :style="`width: 18px; height: 18px; background-color:${colours[i]}`"
                ></div>
                <h4 class="mb-0 ms-1">
                  {{ formatMoney(item.amount) }}
                  <small>{{ item.mode }}</small>
                </h4>
              </div>
            </div>
          </div>
          <div class="table-responsive" id="PaginationTable">
            <print-header>
              <template #title>
                <div class="text-center mb-1">
                  <h5 class="text-center">Sales Report</h5>
                </div>
              </template>
            </print-header>
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Table</th>
                  <th>Waiter</th>
                  <th>Client</th>
                  <th>Amount</th>
                  <th>Paid</th>
                  <th>Balance</th>
                  <th>Status</th>
                  <th>Created at</th>
                  <th class="action"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in rows" :key="order.reference">
                  <td>
                    <span>#{{ $helper.generateVoucherNo(order.id) }}</span>
                  </td>
                  <td>
                    <a class="btn-link" href="javascript:void(0)">{{
                      order?.table?.name
                    }}</a>
                  </td>
                  <td>
                    <span class="fw-bolder">
                      {{ order.waiter ? order.waiter.name : "Admin" }}</span
                    >
                  </td>
                  <td>
                    <span>{{
                      order.client ? order.client.name : "Walk-in"
                    }}</span>
                  </td>
                  <td>
                    <span class="fw-bolder">{{
                      formatMoney(order.grand_total)
                    }}</span>
                  </td>
                  <td>
                    <span class="fw-bolder">{{
                      formatMoney(order.paid_amount)
                    }}</span>
                  </td>
                  <td>
                    <span class="fw-bolder">{{
                      formatMoney(order.grand_total - order.paid_amount)
                    }}</span>
                  </td>

                  <td>
                    <span class="badge bg-success" v-if="order.paid == 1"
                      >Paid</span
                    >
                    <span class="badge bg-warning" v-else>Due</span>
                  </td>
                  <td>
                    <span>{{
                      `${$helper.formatDate(
                        order.order_date
                      )} ${$helper.formatOrderTime(order.order_date)}`
                    }}</span>
                  </td>
                  <td class="text-end text-nowrap action">
                    <div class="">
                      <button
                        class="btn btn-icon btn-xs btn-light"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                      >
                        <i class="demo-pli-dot-horizontal"></i>
                        <span class="visually-hidden">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end" style="">
                        <li v-if="hasPermissionTo('R', 'reports.sales_report')">
                          <a
                            class="dropdown-item"
                            href="javascript:void(0)"
                            @click.prevent="selectRow(order)"
                            ><svg
                              height="16px"
                              xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 24 24"
                              fill="none"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              class="me-1"
                            >
                              <path
                                d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                              ></path>
                              <circle cx="12" cy="12" r="3"></circle></svg
                            >View Items</a
                          >
                        </li>
                        <li v-if="hasPermissionTo('D', 'reports.sales_report')">
                          <a
                            class="dropdown-item"
                            href="javascript:void(0)"
                            @click.prevent="
                              () => {
                                deletedOrder = order;
                                toggleModal();
                              }
                            "
                            ><i class="demo-pli-trash fs-5 me-1"></i>Delete</a
                          >
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <pagination
            :from="recordsFrom"
            :to="recordsTo"
            :current_page="currentPage"
            :row_count_page="rowCountPage"
            @page-update="handlePagination"
            :total_records="totalRows"
            :page_range="pageRange"
          ></pagination>
        </div>
      </div>
    </div>
  </div>
  <div
    class="receipt receipt-end border-0 shadow"
    tabindex="-1"
    style="visibility: visible"
    aria-modal="true"
    role="dialog"
    :class="{ 'show-receipt': selectedRow != null }"
  >
    <div class="receipt-header bg-dark">
      <h4 class="receipt-title">RECEIPT</h4>
      <div class="text-center text-nowrap">
        <a
          class="btn btn-icon btn-xs btn-success mx-2"
          href="javascript:void(0)"
          @click.prevent="handleInvoicePrinting(selectedRow)"
        >
          <svg
            height="18px"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
            ></path></svg
        ></a>
        <a
          class="btn btn-icon btn-xs btn-danger"
          href="javascript:void(0)"
          @click.prevent="selectRow(null)"
          ><i class="demo-pli-cross fs-5 fw-bolder"></i
        ></a>
      </div>
    </div>
    <div class="receipt-body scrollable-content px-1">
      <receipt-one
        v-if="selectedRow != null"
        :order="selectedRow"
        :invoiced="true"
      ></receipt-one>
    </div>
  </div>

  <bootstrap-modal
    v-if="modalOpen"
    @close="
      () => {
        deletedOrder = null;
        toggleModal();
      }
    "
    modal-size="modal-default"
  >
    <template #head>
      <span>Delete Sale #{{ $helper.padNumber(deletedOrder.id) }}</span>
    </template>
    <template #body>
      <div class="alert alert-warning" role="alert">
        If you confirm, there is way to restore deleted sales.
      </div>
      <div class="row mb-2">
        <label class="col-lg-3 col-md-5 fw-bold required">Comment</label>
        <div class="col-lg-9 col-md-7">
          <textarea
            v-model="comment"
            rows="3"
            class="form-control"
            placeholder="Optional comment"
          ></textarea>
        </div>
      </div>
      <div class="row">
        <label class="col-lg-3 col-md-5"></label>
        <div class="col-lg-6 col-md-7">
          <wolf-button
            class="bnt-block btn btn-danger"
            activator="REJECTING_ORDER"
            :disabler="!comment"
            @clicked="handleDestroy"
          >
            Confirm
          </wolf-button>
        </div>
      </div>
    </template>
  </bootstrap-modal>
</template>
