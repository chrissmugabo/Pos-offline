<script>
import EmptyResults from "@/components/EmptyResults.vue";
import OrderModal from "@/components/OrderModal.vue";
export default {
  name: "orders",
  components: { EmptyResults, OrderModal },
  data: () => ({
    dataUrl: "orders",
    selectedOrder: null,
    rows: [],
    client: null,
    printedOrder: {},
  }),
  created() {
    this.$http.get(this.dataUrl).then((response) => {
      this.rows = response.data.rows;
      if (!this.rows.length) {
        this.createNewDay();
      }
    });
  },
  methods: {
    handlePrintedOrder() {
      const index = this.rows.findIndex(
        (item) => item.id == this.selectedOrder.id
      );
      this.rows[index].status = "DELIVERED";
      this.rows[index].printed = 1;
      this.printedOrder = this.rows[index];
      setTimeout(() => {
        this.storePrintableOrder({
          category: "INVOICE",
          order_id: this.printedOrder.id,
        });
      }, 1000);
    },
    createNewDay() {
      this.$http.post("settings/close-day").then((response) => {
        if (response.data.status) {
          this.$notify({
            type: "success",
            message: "END OF DAY created successfully",
          });
          this.$store.state.system_date = response.data.system_date;
          this.$store.state.invalid_date = false;
          this.$router.go(-1);
        }
      });
    },
    handlePaidOrder() {
      const index = this.rows.findIndex(
        (item) => item.id == this.selectedOrder.id
      );
      this.rows.splice(index, 1);
      this.closeSidebar();
    },
    selectOrder(order) {
      if (order) {
        document.querySelector("#root").classList.add("sb--show");
      } else {
        document.querySelector("#root").classList.remove("sb--show");
      }
      this.selectedOrder = order;
    },
    closeSidebar() {
      document.querySelector("#root").classList.remove("sb--show");
      this.selectedOrder = null;
    },
  },
};
</script>
<template>
  <div class="container-fluid mt-2">
    <div class="row">
      <div class="pt-1 col-lg-12">
        <div
          class="d-flex justify-content-center"
          style="min-height: 65vh"
          v-if="!rows.length"
        >
          <empty-results>
            <template #description
              ><span
                >No orders found. Please wait for new orders</span
              ></template
            >
          </empty-results>
        </div>
        <div class="main-side scrollable-content w-100 pt-3" v-else>
          <div class="order-wraper d-grid">
            <a
              href="javascript:void(0)"
              class="rounded-2 box-shadow bg-white border p-2 order-list-item mb-2 position-relative"
              v-for="(order, i) in rows"
              :key="'order' + i"
              @click.prevent="selectOrder(order)"
              :class="{ active: selectedOrder && selectedOrder.id == order.id }"
            >
              <span
                class="badge bg-success rounded-circle completed-order p-1"
                v-if="order.printed == 1"
              >
                <i class="demo-pli-yes fs-5"></i>
              </span>
              <div class="d-flex align-items-center flex-nowrap">
                <div class="order-left">
                  <h6 class="mb-0 fw-bolder">
                    Order: #{{ $helper.padNumber(order.id) }}
                  </h6>
                  <a
                    href="javascript:void(0)"
                    class="m-0 h6"
                    v-if="order.table"
                    >{{ order.table?.name }}</a
                  >
                  <h6 class="m-0 text-muted">
                    By:
                    {{ order.waiter ? order.waiter.name : "Administrator" }}
                  </h6>
                </div>
                <div class="ms-auto text-end">
                  <p class="m-0 text-dark">
                    {{ $helper.formatOrderDate(order.order_date) }}
                  </p>
                  <p class="mb-0 text-muted">
                    {{ $helper.formatOrderTime(order.order_date) }}
                  </p>
                  <div class="d-flex flex-nowrap">
                    <h6 class="m-0">
                      <span class="fw-bolder text-dark">{{
                        formatMoney(order.grand_total)
                      }}</span>
                    </h6>
                  </div>
                </div>
              </div>
            </a>
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
    @deleted="handlePaidOrder"
    @printed="handlePrintedOrder"
  />
  <div
    class="w-100 fixed-bottom shadow border-top p-2 main-content bg-white"
    style="z-index: 999"
  >
    <div class="d-flex w-100 align-items-center">
      <wolf-button
        type="button"
        @clicked="createNewDay"
        :disabler="rows.length > 0"
        activator="CREATING_EDO"
        class="btn btn-primary"
        >Confirm End Of {{ $store.state.system_date }}</wolf-button
      >
    </div>
  </div>
</template>
