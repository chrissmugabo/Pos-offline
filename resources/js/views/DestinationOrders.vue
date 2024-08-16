<script>
import EmptyResults from "@/components/EmptyResults.vue";
import { permissionsHandler } from "@/common/permissions-handler";
export default {
  name: "DestinationOrders",
  components: { EmptyResults },
  mixins: [permissionsHandler],
  data: () => ({
    orders: [],
    fetchUrl: "orders/show-destination-orders?destination=",
    latestOrderInterval: null,
    latestOrder: null,
    willUpdateKey: 0,
  }),
  computed: {
    destination() {
      return this.$route.name == "KitchenOrders" ? "KITCHEN" : "BAR";
    },
  },
  watch: {
    $route() {
      location.reload();
    },
  },
  created() {
    this.fetchUrl += this.destination;
    this.$http.get(this.fetchUrl).then((response) => {
      this.orders = response.data;
      /* if(this.orders.length) {
        const lastItem = this.orders [this.orders .length - 1];
        this.latestOrder = lastObject.id
      } */
      //this.getLatestOrder();
    });
  },
  mounted() {
    // Update Order status (Badge - danger, success, ...)
    setInterval(() => {
      this.willUpdateKey += 1;
    }, 60000);
  },
  methods: {
    handleDeliver(order) {
      this.$confirm({
        title: "Delivering Order",
        content: `Are you sure you want to deliver order #${this.$helper.generateVoucherNo(
          order.id
        )}?`,
        classes: "btn btn-primary",
        actionText: "Yes, Continue",
        closeText: "Cancel",
        action: () => {
          this.$http
            .post(
              "orders/deliver",
              this.$helper.generateFormData({
                order_id: order.id,
                destination: this.destination,
              })
            )
            .then((response) => {
              if (response.data.status) {
                let index = this.orders.findIndex(
                  (item) => item.id == order.id
                );
                this.orders.splice(index, 1);
                this.$store.commit("SET_FLASH_MESSAGE", {
                  type: "success",
                  message: `Order with reference #${order.reference} has been delivered sucessfully`,
                });
              }
            });
        },
      });
    },
    getLatestOrder() {
      this.latestOrderInterval = setInterval(() => {
        this.$store.commit("SET_REQUEST_FLAG", "GETTING_LATEST_ORDERS");
        this.$http
          .get(`${this.fetchUrl}&latest=${this.latestOrder}`)
          .then((response) => {
            if (response.data.orders.length) {
              this.orders = [...response.data.orders, ...this.orders];
              this.latestOrder = response.data.latest;
            }
          });
      }, 3000);
    },
    orderStatus(order_date) {
      const result = this.$helper.timeDifference(new Date(order_date));
      const minutes = result.split(" ")[0];
      const res =
        result.indexOf("second") > -1
          ? "success "
          : result.indexOf("minute") == -1
          ? "danger"
          : isNaN(minutes) || Number(minutes) > 45
          ? "warning"
          : "success";
      return res;
    },
    callback() {
      // v-for-callback="{ key: i, array: orders, callback: callback }"
      [...document.querySelectorAll(".order-card")].forEach((item) => {
        let width = (item.offsetWidth * 300) / item.offsetHeight;
        item.style.width = `${width}px`;
        item.style.flexGrow = width;
      });
    },
    getPastOrders() {},
  },
  beforeUnmount() {
    clearInterval(this.latestOrderInterval);
  },
  directives: {
    forCallback(el, binding, vnode) {
      let element = binding.value;
      var key = element.key;
      var len = 0;

      if (Array.isArray(element.array)) {
        len = element.array.length;
      } else if (typeof element.array === "object") {
        var keys = Object.keys(element.array);
        key = keys.indexOf(key);
        len = keys.length;
      }

      if (key == len - 1) {
        if (typeof element.callback === "function") {
          element.callback.bind(vnode.context)();
        }
      }
    },
  },
};
</script>
<template>
  <h3 class="mb-0">
    <span class="text-primary text-uppercase">
      {{ destination }}
    </span>
    ORDERS
  </h3>
  <div class="orders-grid mt-3" v-if="orders.length">
    <div class="pb-2">
      <div class="orders-container" :key="'orders' + willUpdateKey">
        <div
          class="order-item"
          v-for="(order, i) in orders"
          :key="order.reference + i"
        >
          <div class="order-card cursor-pointer">
            <div class="w-100 p-0 m-0 bg-white rounded-2 border">
              <div
                :class="`px-2 py-2 bg-${orderStatus(
                  order?.system_date + ' ' + order?.order_time
                )}`"
              >
                <div class="d-flex align-items-center flex-nowrap">
                  <div class="order-left">
                    <h4 class="mb-0 text-white">{{ order?.table?.name }}</h4>
                    <a href="javascript:void(0)" class="m-0 h6 text-white"
                      >Order: #{{ $helper.generateVoucherNo(order.id) }}</a
                    >
                    <h6 class="m-0 text-white">
                      By:
                      {{ order.waiter ? order.waiter.name : "Administrator" }}
                    </h6>
                  </div>
                  <div class="order-right ms-auto text-end">
                    <h6 class="text-white mb-0">{{ order.category }}</h6>
                    <h4 class="mb-0 text-white">
                      {{
                        $helper.formatOrderTime(
                          `${order?.system_date + " " + order?.order_time}`
                        )
                      }}
                    </h4>
                    <h6 class="text-end mb-0 text-white">
                      {{ $helper.formatDate(order?.system_date) }}
                    </h6>
                  </div>
                </div>
              </div>
              <div class="px-2 py-2">
                <div
                  class="border-bottom py-2"
                  v-for="(item, j) in order.items"
                  :key="'order_items' + i + j"
                >
                  <div class="d-flex align-items-center item">
                    <div>{{ item.quantity }}</div>
                    <div class="ms-2">
                      <span>{{ item.name }}</span>
                    </div>
                  </div>
                  <div
                    class="mb-1"
                    v-if="item.addons && item.addons.length > 0"
                  >
                    <span class="fw-normal text-dark"><em>With:</em></span>
                    <div class="addons-container ps-1">
                      <div class="widget widget-categories">
                        <ul>
                          <li class="has-children">
                            <ul class="collapse show py-0">
                              <li
                                v-for="(row, k) in item.addons"
                                :key="i + 'addon' + k"
                              >
                                <a
                                  href="javascript:void(0)"
                                  class="d-flex align-items-center fw-normal"
                                >
                                  <span>{{ row.name }}</span>
                                </a>
                              </li>
                            </ul>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div class="mb-1" v-if="item.comment">
                    <span class="fw-normal text-dark"><em>Notes</em>:</span>
                    <p class="mb-0">{{ item.comment }}</p>
                  </div>
                </div>

                <button
                  v-if="hasPermissionTo('U')"
                  type="button"
                  :class="`btn badge-soft-${orderStatus(
                    order?.system_date + ' ' + order?.order_time
                  )} w-100 mt-2`"
                  @click="handleDeliver(order)"
                >
                  Deliver
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex justify-content-center" style="min-height: 93vh" v-else>
    <empty-results>
      <template #description
        ><span>No orders found. Please wait for new orders</span></template
      >
    </empty-results>
  </div>
</template>
<style scoped>
.order-item {
  margin: 0;
  display: grid;
  grid-template-rows: 1fr auto;
  margin-bottom: 10px;
  break-inside: avoid;
}

.order-item > .order-card {
  grid-row: 1 / -1;
  grid-column: 1;
}

.orders-container {
  /* column-count: 5; */
  column-gap: 10px;
}
@media (min-width: 576px) {
  .orders-container {
    column-count: 3;
  }
}
@media (min-width: 768px) {
  .orders-container {
    column-count: 4;
  }
}
@media (min-width: 992px) {
  .orders-container {
    column-count: 5;
  }
}
</style>
