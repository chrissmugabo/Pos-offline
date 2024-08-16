<script>
import "@/assets/ant-transfer.css";
import { ordersMixin } from "@/common/global-mixin";
export default {
  name: "OrderReceipts",
  props: ["order"],
  emits: ["closed", "splitted"],
  mixins: [ordersMixin],
  data: () => ({
    leftItems: [],
    rightItems: [],
    transferedItems: [],
    items: [],
    offCanvas: null,
    receipts: [],
    displayMode: true,
  }),
  computed: {
    leftItemsTotal() {
      return this.items.reduce((a, b) => a + Number(b.amount), 0);
    },
    rightItemsTotal() {
      return this.transferedItems.reduce(
        (a, b) => a + Number(b.quantity) * (b.price || b.cost),
        0
      );
    },
    cartItems() {
      return this.transferedItems.map((item) => item.id);
    },
  },
  methods: {
    addToCart(sign = "+", index = null) {
      if (index != null) {
        const item = this.transferedItems[index];
        if (sign == "-") {
          if (item.quantity > 1) {
            item.quantity = Number(item.quantity) - 1;
          }
        } else {
          if (item.quantity < item.max_qty) {
            item.quantity = Number(item.quantity) + 1;
          }
        }
      }
    },
    handleRightToLeftTransfer() {
      /*const sentItems = [
        ...this.transferedItems.filter((item) =>
          this.rightItems.includes(item.id)
        ),
      ];
      this.items = [...this.items, ...sentItems]; */
      this.rightItems.forEach((id) => {
        const index = this.transferedItems.findIndex((obj) => obj.id === id);
        if (index !== -1) {
          this.transferedItems.splice(index, 1);
        }
      });
      this.rightItems = [];
    },
    handleLeftToRightTransfer() {
      const sentItems = [
        ...this.items.filter((item) => this.leftItems.includes(item.id)),
      ];
      sentItems.map((obj) => {
        obj.max_qty = obj.quantity;
        return obj;
      });
      this.transferedItems = JSON.parse(
        JSON.stringify([...this.transferedItems, ...sentItems])
      );
      /*this.leftItems.forEach((id) => {
        const index = this.items.findIndex((obj) => obj.id === id);
        if (index !== -1) {
          this.items.splice(index, 1);
        }
      });*/
      this.leftItems = [];
    },
    toggleCrudMode() {
      this.displayMode = !this.displayMode;
    },
    handleSubmit() {
      this.$http
        .post(
          `orders/receipts/${this.order.id}/split`,
          this.$helper.generateFormData({
            grand_total: this.rightItemsTotal,
            amount: this.rightItemsTotal,
            items: this.transferedItems,
          })
        )
        .then((response) => {
          this.$store.commit("SET_FLASH_MESSAGE", {
            type: "success",
            message: "Receipt Splitted successfully",
          });
          const row = response.data.row;
          //const splitted = response.data.splitted;
          this.$emit("splitted", row);
        });
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.offCanvas = new window.bootstrap.Offcanvas(
        document.querySelector("#order-split-modal")
      );
      this.offCanvas.show();
      this.getOrderItems(this.order.id).then((items) => {
        this.items = items;
      });
    });
  },
  unmounted() {
    this.offCanvas.hide();
  },
};
</script>
<template>
  <div
    class="offcanvas offcanvas-end"
    id="order-split-modal"
    style="width: 750px"
  >
    <div class="offcanvas-header">
      <h4 class="offcanvas-title">
        Split Order No #{{ $helper.generateVoucherNo(order.id) }}
      </h4>
      <h5>Total: {{ formatMoney(order?.grand_total) }}</h5>
      <button
        type="button"
        class="btn-close text-reset"
        data-bs-dismiss="offcanvas"
        @click="$emit('closed')"
      ></button>
    </div>
    <div class="offcanvas-body" :class="{ 'p-0': !displayMode }">
      <div class="crud-container position-relative h-100">
        <div class="crud-display" :class="{ 'empty d-none': !displayMode }">
          <template v-if="displayMode">
            <div class="d-flex align-items-center mb-2">
              <h5>Splitted receipts ({{ order?.receipts?.length || 0 }})</h5>
              <button
                type="button"
                @click="toggleCrudMode"
                class="btn btn-primary btn-sm ms-auto"
              >
                <i class="uil uil-plus"></i> Add New
              </button>
            </div>
            <div class="orders-container" v-if="order?.receipts">
              <div
                class="order-item border p-2"
                v-for="(receipt, i) in order?.receipts"
                :key="receipt.id"
              >
                <div class="order-card">
                  <h6 class="text-primary">
                    Total {{ formatMoney(receipt.total) }}
                  </h6>
                  <div
                    class="d-flex w-100 py-2 border-bottom border-dashed"
                    v-for="item in receipt.items"
                    :key="'receipt-items' + i + item.id"
                  >
                    <span>{{ `${item.quantity} x ${item.name}` }}</span>
                    <span class="fw-bold ms-auto">{{
                      formatMoney(item.amount)
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
        <div class="crud-create" :class="{ 'd-none': displayMode }">
          <div
            class="d-flex w-100 justify-content-center"
            v-if="isProcessing(itemsLoader)"
          >
            <div class="loading text-center">
              <div class="load-circle1"></div>
              <div class="load-circle2"></div>
              <div class="load-circle3"></div>
              <div class="load-circle4"></div>
              <div class="load-circle5"></div>
            </div>
          </div>
          <template v-else>
            <div class="ant-transfer">
              <div
                class="ant-transfer-list ant-transfer-list-with-footer"
                style="width: 300px; height: 400px"
              >
                <div class="ant-transfer-list-header">
                  <span class="ant-transfer-list-header-selected"
                    ><span
                      >{{ items.length }} items -
                      {{
                        formatMoney(order?.grand_total - rightItemsTotal)
                      }}</span
                    ><span class="ant-transfer-list-header-title"></span
                  ></span>
                </div>
                <div
                  class="ant-transfer-list-body pt-0 ant-transfer-list-body-with-search"
                >
                  <ul class="ant-transfer-list-content">
                    <li
                      class="ant-transfer-list-content-item border-bottom"
                      v-for="row in items"
                      :key="row.id"
                    >
                      <label
                        class="ant-checkbox-wrapper ant-transfer-list-checkbox"
                      >
                        <div
                          class="form-check form-check-sm form-check-custom form-check-solid"
                        >
                          <input
                            class="form-check-input"
                            type="checkbox"
                            :value="row.id"
                            v-model="leftItems"
                            :disabled="cartItems.includes(row.id)"
                          />
                        </div>
                      </label>
                      <div
                        class="symbol symbol-circle symbol-20px overflow-hidden"
                      >
                        <div class="symbol-label"></div>
                      </div>
                      <span class="ant-transfer-list-content-item-text">{{
                        `${row.quantity} x ${row.name}`
                      }}</span>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="ant-transfer-operation">
                <button
                  class="ant-btn ant-btn-primary ant-btn-sm ant-btn-icon-only"
                  type="button"
                  @click="handleLeftToRightTransfer"
                  :disabled="!leftItems.length"
                >
                  <span
                    role="img"
                    aria-label="right"
                    class="anticon anticon-right"
                    ><svg
                      focusable="false"
                      class=""
                      data-icon="right"
                      width="1em"
                      height="1em"
                      fill="currentColor"
                      aria-hidden="true"
                      viewBox="64 64 896 896"
                    >
                      <path
                        d="M765.7 486.8L314.9 134.7A7.97 7.97 0 00302 141v77.3c0 4.9 2.3 9.6 6.1 12.6l360 281.1-360 281.1c-3.9 3-6.1 7.7-6.1 12.6V883c0 6.7 7.7 10.4 12.9 6.3l450.8-352.1a31.96 31.96 0 000-50.4z"
                      ></path></svg></span></button
                ><button
                  class="ant-btn ant-btn-primary ant-btn-sm ant-btn-icon-only"
                  type="button"
                  @click="handleRightToLeftTransfer"
                  :disabled="!rightItems.length"
                >
                  <span
                    role="img"
                    aria-label="left"
                    class="anticon anticon-left"
                    ><svg
                      focusable="false"
                      class=""
                      data-icon="left"
                      width="1em"
                      height="1em"
                      fill="currentColor"
                      aria-hidden="true"
                      viewBox="64 64 896 896"
                    >
                      <path
                        d="M724 218.3V141c0-6.7-7.7-10.4-12.9-6.3L260.3 486.8a31.86 31.86 0 000 50.3l450.8 352.1c5.3 4.1 12.9.4 12.9-6.3v-77.3c0-4.9-2.3-9.6-6.1-12.6l-360-281 360-281.1c3.8-3 6.1-7.7 6.1-12.6z"
                      ></path></svg></span>
                </button>
              </div>
              <div
                class="ant-transfer-list ant-transfer-list-with-footer"
                style="width: 365px; height: 400px"
              >
                <div class="ant-transfer-list-header">
                  <span class="ant-transfer-list-header-selected"
                    ><span
                      >{{ transferedItems.length }} items -
                      {{ formatMoney(rightItemsTotal) }}</span
                    ><span class="ant-transfer-list-header-title"></span
                  ></span>
                </div>
                <div
                  class="ant-transfer-list-body pt-0 ant-transfer-list-body-with-search"
                >
                  <ul class="ant-transfer-list-content">
                    <li
                      class="ant-transfer-list-content-item border-bottom align-items-center"
                      v-for="(row, i) in transferedItems"
                      :key="row.id"
                    >
                      <label
                        class="ant-checkbox-wrapper ant-transfer-list-checkbox"
                      >
                        <div
                          class="form-check form-check-sm form-check-custom form-check-solid"
                        >
                          <input
                            class="form-check-input"
                            type="checkbox"
                            :value="row.id"
                            v-model="rightItems"
                          />
                        </div>
                      </label>
                      <div
                        class="symbol symbol-circle symbol-20px overflow-hidden"
                      >
                        <div class="symbol-label"></div>
                      </div>
                      <span class="ant-transfer-list-content-item-text">{{
                        `${row.name}`
                      }}</span>
                      <div class="ml-auto">
                        <div class="dz-stepper small-stepper border-2">
                          <div
                            class="input-group bootstrap-touchspin bootstrap-touchspin-injected"
                          >
                            <span class="input-group-btn input-group-prepend">
                              <button
                                class="btn btn-info bootstrap-touchspin-down"
                                type="button"
                                @click="addToCart('-', i)"
                                :disabled="!row.quantity"
                              >
                                -
                              </button> </span
                            ><input
                              class="stepper form-control"
                              type="text"
                              v-model="row.quantity"
                            /><span class="input-group-btn input-group-append">
                              <button
                                class="btn btn-primary bootstrap-touchspin-up"
                                type="button"
                                @click="addToCart('+', i)"
                              >
                                +
                              </button>
                            </span>
                          </div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div
              class="position-absolute bottom-0 w-100 border-top py-2 ps-3 end-0"
            >
              <wolf-button
                class="btn btn-primary"
                activator="HANDLE_ORDER_SPLIT"
                :disabler="!transferedItems.length"
                @clicked="handleSubmit"
              >
                Confirm and Save
              </wolf-button>
              <button
                type="button"
                class="btn btn-dark ms-2"
                data-bs-dismiss="offcanvas"
              >
                Cancel
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
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
  column-count: 2;
  column-gap: 10px;
}
.form-check-input:disabled {
  cursor: not-allowed;
  border-color: #d9d9d9;
  background: #f5f5f5;
}
</style>
