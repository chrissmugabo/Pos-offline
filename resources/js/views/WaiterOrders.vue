<script>
import { dataTableService } from "@/common/datatable-mixin.js";
import { permissionsHandler } from "@/common/permissions-handler";
import EmptyResults from "@/components/EmptyResults.vue";
import OrderModal from "@/components/OrderModal.vue";
import OrderReceipts from "@/components/OrderReceipts.vue";

export default {
  name: "WaiterOrders",
  mixins: [dataTableService, permissionsHandler],
  components: {
    EmptyResults,
    OrderModal,
    OrderReceipts,
  },
  data: () => ({
    selectedOrder: null,
    printedOrder: {},
    splitingMode: false,
    splittedOrder: null,
    latestOrderInterval: null,
    latest: null,
    tableRef: 1,
  }),
  mounted() {
    this.fetchUrl = `orders/show-waiter-orders?paid=0&is-cashier=${this.isCashier}`;
    this.$http.get(this.fetchUrl).then((response) => {
      this.rows = response.data.rows.data;
      const rows = response.data.rows.data;
      if (rows.length) {
        this.latest = rows[0].id;
        //this.rows = [...rows, ...this.rows];
      }
      this.currentPage = response.data.rows.current_page;
      this.totalRows = response.data.rows.total;
      this.recordsFrom = response.data.rows.from;
      this.recordsTo = response.data.rows.to;
      this.fetchedData = response.data;
      if (this.isCashier) {
        this.latestOrderInterval = setInterval(() => {
          this.getComponentData();
        }, 5000);
      }
    });
  },
  beforeUnmount() {
    clearInterval(this.latestOrderInterval);
  },
  methods: {
    getComponentData() {
      let url = this.fetchUrl;
      if (this.latest) {
        url += `&latest=${this.latest}`;
      }
      this.$store.commit("SET_REQUEST_FLAG", "GETTING_LATEST_ORDERS");
      this.$http.get(url).then((response) => {
        const rows = response.data.rows.data;
        if (rows.length) {
          this.latest = rows[0].id;
          this.rows = [...rows, ...this.rows];
          this.currentPage = response.data.rows.current_page;
          this.totalRows = response.data.rows.total;
          this.recordsFrom = response.data.rows.from;
          this.recordsTo = response.data.rows.to;
          this.fetchedData = response.data;
        }
      });
    },
    handleSplitEvent() {
      this.openSplitModal(this.selectedOrder);
      this.closeSidebar();
    },
    openSplitModal(order) {
      this.splittedOrder = { ...order };
      this.splitingMode = true;
    },
    closeSplitingOrder() {
      this.splittedOrder = null;
      this.splitingMode = false;
    },
    handlePaidOrder(meta) {
      const index = this.rows.findIndex(
        (item) => item.id == this.selectedOrder.id
      );
      if (meta.amount_remain == 0 || meta.credited) {
        this.rows.splice(index, 1);
      } else {
        this.rows[index].paid_amount += meta.amount_paid;
        if (meta.credited) {
          this.rows[index].resolved = 1;
        }
      }
      this.closeSidebar();
    },
    removeOrderItem() {
      const index = this.rows.findIndex(
        (item) => item.id == this.selectedOrder.id
      );
      this.rows.splice(index, 1);
      this.closeSidebar();
    },
    closeSidebar() {
      document.querySelector("#root").classList.remove("sb--show");
      this.selectedOrder = null;
    },
    selectOrder(order) {
      if (order) {
        document.querySelector("#root").classList.add("sb--show");
      } else {
        document.querySelector("#root").classList.remove("sb--show");
      }
      this.selectedOrder = order;
    },
    handleOrderPrint(order, index) {
      this.$confirm({
        title: "Printing order invoice",
        content: `Are you sure you want to print order #${this.$helper.generateVoucherNo(
          order.id
        )} and make it as completed?`,
        classes: "btn btn-primary",
        actionText: "Complete",
        closeText: "Cancel",
        action: () => {
          this.$http
            .get(`orders/invoice/${order.id}/print`)
            .then((response) => {
              if (response.data.status) {
                this.$store.commit("SET_FLASH_MESSAGE", {
                  type: "success",
                  message: "Order sent to print command successfully",
                });
                this.rows[index].status = "DELIVERED";
                this.rows[index].printed = 1;
                //this.rows[index].resolved = 1;
                this.printedOrder = order;
                /*if (
                  !Array.isArray(order.receipts) &&
                  !order?.receipts?.length
                ) { */
                setTimeout(() => {
                  this.storePrintableOrder({
                    category: "INVOICE",
                    order_id: this.printedOrder.id,
                  });
                  if (!this.isCashier) {
                    this.signOutWaiter();
                  }
                }, 1000);
                //}
              }
            });
        },
      });
    },
    handleSplittedOrder(order) {
      const index = this.rows.findIndex(
        (item) => item.id == this.splittedOrder.id
      );
      this.rows[index] = order;
      //this.rows.unshift(orders[1]);
      this.closeSplitingOrder();
    },
  },
};
</script>
<template>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="pt-1 col-lg-11 col-md-12 col-sm-12">
        <div
          class="d-flex justify-content-center"
          style="min-height: 95vh"
          v-if="!rows.length"
        >
          <empty-results>
            <template #description
              ><span>No orders found. Please place new orders</span></template
            >
          </empty-results>
        </div>
        <div class="card" v-else>
          <div class="card-header">
            <div class="row align-items-center">
              <div class="col-md-6 d-flex gap-1 align-items-center">
                <h4 class="card-title mb-0">
                  {{ isCashier ? "Orders" : "Your Orders" }}
                </h4>
              </div>
              <div
                class="col-md-6 d-flex gap-1 align-items-center justify-content-md-end"
              >
                <div class="form-group" v-if="false">
                  <input
                    type="text"
                    placeholder="Search..."
                    class="form-control"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="card-body pt-1">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Tot. Amount</th>
                    <th>Paid Amount</th>
                    <th>Table</th>
                    <th>Time</th>
                    <th v-if="isCashier">Waiter</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody :ref="tableRef">
                  <tr v-for="(order, i) in rows" :key="order.reference">
                    <td>
                      <a class="btn-link" href="javascript:void(0)">
                        #{{ $helper.generateVoucherNo(order.id) }}</a
                      >
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
                      <span>{{ order?.table?.name }}</span>
                    </td>
                    <td>
                      <span>{{
                        $helper.formatOrderTime(order.order_date)
                      }}</span>
                    </td>
                    <td v-if="isCashier">
                      <span>{{ order.waiter.name }}</span>
                    </td>
                    <td>
                      <span
                        >{{ order?.client?.name || "Walk-in" }}
                        <span v-if="order?.room"
                          >Room: [{{ order.room.room_name }}]</span
                        >
                      </span>
                    </td>
                    <td class="fs-6">
                      <div
                        class="badge d-block bg-success"
                        v-if="order.status == 'DELIVERED'"
                      >
                        Completed
                      </div>
                      <div
                        class="badge d-block bg-warning"
                        v-else-if="order.status == 'PENDING'"
                      >
                        Pending
                      </div>
                      <div
                        class="badge d-block bg-danger"
                        v-else-if="order.status == 'DENIED'"
                      >
                        Cancelled
                      </div>
                    </td>
                    <td class="text-center text-nowrap">
                      <a
                        class="btn btn-icon btn-sm btn-info btn-hover"
                        href="javascript:void(0)"
                        @click.prevent="selectOrder(order)"
                      >
                        <svg
                          height="18px"
                          viewBox="0 0 16 16"
                          xmlns="http://www.w3.org/2000/svg"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M15 13V4a1 1 0 0 0-1-1h-3.586A2 2 0 0 1 9 2.414l-1-1-1 1A2 2 0 0 1 5.586 3H2a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM2 2a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2h-3.586a1 1 0 0 1-.707-.293L8.354.354a.5.5 0 0 0-.708 0L6.293 1.707A1 1 0 0 1 5.586 2H2z"
                          ></path>
                          <path
                            fill-rule="evenodd"
                            d="M15 11H1v-1h14v1zm0-4H1V6h14v1zM2 12.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5z"
                          ></path>
                        </svg>
                      </a>
                      <template v-if="order.printed == 0">
                        <a
                          class="btn btn-icon btn-sm btn-light btn-hover"
                          href="javascript:void(0)"
                          v-if="isCashier"
                          @click.prevent="openSplitModal(order)"
                        >
                          <i class="uil uil-file-share-alt fs-3"></i>
                        </a>
                        <router-link
                          v-if="hasPermissionTo('U')"
                          class="btn btn-icon btn-sm btn-primary btn-hover"
                          :to="{
                            name: 'OrdersCreator',
                            params: {
                              action: 'edit',
                              reference: order.reference,
                            },
                          }"
                          ><i class="demo-pli-add fs-5"></i
                        ></router-link>
                        <a
                          v-if="hasPermissionTo('R') || hasPermissionTo('U')"
                          class="btn btn-icon btn-sm btn-success btn-hover"
                          href="javascript:void(0)"
                          @click.prevent="handleOrderPrint(order, i)"
                          ><i class="demo-pli-printer fs-5"></i
                        ></a>
                      </template>
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
  </div>

  <OrderModal
    :order="selectedOrder"
    v-if="!$helper.empty(selectedOrder)"
    @closed="closeSidebar"
    @paid="handlePaidOrder"
    @deleted="removeOrderItem"
    @request-split="handleSplitEvent"
  />

  <OrderReceipts
    :order="splittedOrder"
    v-if="!$helper.empty(splittedOrder) && splitingMode"
    @closed="closeSplitingOrder"
    @splitted="handleSplittedOrder"
  />
</template>
