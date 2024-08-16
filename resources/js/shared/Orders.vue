<script>
import EmptyResults from "@/components/EmptyResults.vue";
import OrderModal from "@/components/OrderModal.vue";
import LoadingDots from "@/components/LoadingDots.vue";
export default {
  name: "Orders",
  components: { EmptyResults, OrderModal, LoadingDots },
  props: ["newDay"],
  data: () => ({
    fetchUrl: "orders",
    selectedOrder: null,
    rows: [],
    client: null,
    days: {},
    printedOrder: {},
    hasFetched: false,
    isFetching: false,
  }),
  created() {
    this.isFetching = true;
    this.$http
      .get(this.fetchUrl)
      .then((response) => {
        this.rows = response.data.rows;
        this.hasFetched = true;
        this.isFetching = false;
        if (!this.rows.length) {
          this.createNewDay();
        }
      })
      .catch((e) => {
        this.isFetching = false;
        console.log(e);
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
      this.$http
        .post("settings/close-day", this.$helper.generateFormData(this.days))
        .then((response) => {
          if (response.data.status) {
            this.$store.state.isCorrectSystemDay = true;
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "success",
              message: "END OF DAY created successfully",
            });
            location.reload();
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
  mounted() {
    this.$nextTick(() => {
      this.days = { ...this.newDay };
    });
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
            <template #description>
              <loading-dots v-if="isFetching" />
              <span v-else
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
      <div class="form-group" style="width: 200px">
        <label class="mb-0" for="from-date">System Date</label>
        <input
          type="date"
          class="form-control form-control-lg"
          v-model="days.from"
          id="from-date"
        />
      </div>
      <div class="form-group mx-2" style="width: 200px">
        <label class="mb-0" for="to-date">Current Date</label>
        <input
          type="date"
          class="form-control form-control-lg"
          v-model="days.to"
          id="to-date"
        />
      </div>
      <div class="form-group">
        <label for="from-date" class="invisible d-block mb-0">Confirm</label>
        <wolf-button
          type="button"
          @clicked="createNewDay"
          :disabler="!hasFetched || rows.length > 0"
          activator="CREATING_EDO"
          class="btn btn-primary"
          >Confirm</wolf-button
        >
      </div>
    </div>
  </div>
</template>
