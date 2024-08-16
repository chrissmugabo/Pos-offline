<script>
import { permissionsHandler } from "@/common/permissions-handler";
import ZohoSearch from "@/components/ZohoSearch.vue";
import ClientCreator from "@/shared/ClientCreator.vue";
import { ordersMixin } from "@/common/global-mixin";
export default {
  name: "OrderModal",
  props: ["order"],
  emits: [
    "closed",
    "paid",
    "deleted",
    "printed",
    "itemDeleted",
    "requestSplit",
  ],
  mixins: [permissionsHandler, ordersMixin],
  components: { ZohoSearch, ClientCreator },
  data: () => ({
    payment_method: null,
    account_id: null,
    payment_ref: null,
    comment: null,
    amount_paid: 0,
    amount_remain: 0,
    barItems: [],
    kitchenItems: [],
    currentTab: "payment",
    customer: null,
    actionType: "RESOLVE",
    clients: [],
    newClientModalOpen: false,
    items: [],
  }),
  computed: {
    submitDisabled() {
      return (
        !this.payment_method ||
        !this.account_id ||
        !this.amount_paid ||
        this.amount_paid <= 0
      );
    },
    accounts() {
      return this.$store.state.accounts;
    },
    paymentModes() {
      return this.$store.state.paymentModes;
    },
  },
  created() {
    this.amount_paid = this.order.grand_total - this.order.paid_amount;
    this.customer = this.order?.client_id;
    this.getOrderItems(this.order.id).then((items) => {
      this.items = items;
      items.forEach((item) => {
        if (item.destination == "BAR") this.barItems.push(item);
        else this.kitchenItems.push(item);
      });
    });
  },
  methods: {
    handleAddedClient(client) {
      this.clients.push(client);
      this.newClientModalOpen = false;
      this.$store.commit("SET_FLASH_MESSAGE", {
        type: "success",
        message: "Client added successfully",
      });
    },
    handleOrderPrint(order) {
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
                this.$emit("printed", true);
              }
            });
        },
      });
    },
    calculateRemainAmount() {
      this.amount_remain =
        this.order.grand_total - this.order.paid_amount - this.amount_paid;
    },
    togglePaymentWrapper() {
      document
        .querySelector(".payment-wrapper")
        .classList.toggle("payment-hidden");
    },
    toggleAssignWrapper() {
      document
        .querySelector(".assign-wrapper")
        .classList.toggle("assign-hidden");
    },
    handleDestroy() {
      this.$http
        .post(
          `orders/delete`,
          this.$helper.generateFormData({
            order_id: this.order.id,
            comment: this.comment,
          })
        )
        .then((response) => {
          if (response.data.status) {
            this.$emit("deleted", true);
            this.actionType = "RESOLVE";
            this.comment = null;
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "success",
              message: "Order deleted successfully",
            });
          }
        });
    },
    handlePayment() {
      this.$http
        .post(
          "orders/payment",
          this.$helper.generateFormData({
            order: this.order.id,
            payment_method: this.payment_method,
            account_id: this.account_id,
            payment_ref: this.payment_ref,
            comment: this.comment,
            discount: 0,
            amount_paid: this.amount_paid,
            amount_remain: this.amount_remain,
          })
        )
        .then((response) => {
          if (response.data.status) {
            this.$emit("paid", {
              amount_paid: this.amount_paid,
              amount_remain: this.amount_remain,
            });
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "success",
              message: `Payment to order ${this.order.reference} has been made sucessfully`,
            });
          }
        });
    },
    assignClient() {
      this.$http
        .post(
          "orders/unpaid/assign",
          this.$helper.generateFormData({
            client_id: this.customer,
            order_id: this.order.id,
            amount_remain: this.order.grand_total - this.order.paid_amount,
          })
        )
        .then((response) => {
          if (response.data.status) {
            this.$emit("paid", {
              amount_paid: 0,
              credited: 1,
              amount_remain: this.order.grand_total - this.order.paid_amount,
            });
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "success",
              message: "Credit posted successfully",
            });
          }
        });
    },
    handleItemDelete(orderId, item) {
      this.$confirm({
        title: "Removing item",
        content: `Are you sure you want to remove ${
          item.name
        } from invoice #${this.$helper.generateVoucherNo(orderId)}?`,
        classes: "btn btn-primary",
        actionText: "Complete",
        closeText: "Cancel",
        action: () => {
          this.$http
            .get(`orders/invoices/${orderId}/destroy/${item.item_id}`)
            .then((response) => {
              if (response.data.status) {
                this.$store.commit("SET_FLASH_MESSAGE", {
                  type: "success",
                  message: "Item deleted successfully",
                });
                this.$emit("itemDeleted", { orderId, item });
              }
            });
        },
      });
    },
  },
};
</script>
<template>
  <aside class="sidebar tame-drawer">
    <div class="sidebar__inner pt-2 px-2 overflow-hidden">
      <div
        class="sidebar__stuck align-item-center d-flex py-2 border-bottom mb-2"
      >
        <h5 class="fw-bolder mb-0">
          Order No#{{ $helper.generateVoucherNo(order.id) }}
        </h5>
        <div class="ms-auto">
          <a
            v-if="order.printed == 0 && isCashier"
            class="text-primary me-1 border rounded fw-bolder"
            style="padding: 0.1rem 0.25rem"
            href="javascript:void(0)"
            @click.prevent="$emit('requestSplit')"
            ><i class="uil uil-file-share-alt fw-bold icon fs-6"></i
          ></a>

          <a
            v-if="order.printed == 0"
            class="text-primary me-1 border rounded fw-bolder"
            style="padding: 0.1rem 0.25rem"
            href="javascript:void(0)"
            @click.prevent="handleOrderPrint(order, i)"
            ><i class="demo-pli-printer fw-bold icon fs-6"></i
          ></a>

          <a
            href="javascript:void(0)"
            class="text-primary ms-1 border rounded fw-bolder"
            @click.prevent="$emit('closed')"
            style="padding: 0.1rem 0.25rem"
            ><i class="demo-pli-cross fw-bold icon fs-6"></i
          ></a>
        </div>
      </div>

      <div class="sidebar__wrap pos-cart scrollable-content">
        <div class="rounded shadown bg-white p-2 mb-3">
          <div
            class="d-flex align-items-center flex-nowrap justify-content-between"
          >
            <div
              class="d-flex align-items-center flex-nowrap w-100"
              v-if="kitchenItems.length"
            >
              <div class="bg-primary rounded-1 p-2 text-center">
                <h4 class="m-0 text-white fw-bolder">T{{ order.table?.id }}</h4>
              </div>
              <div class="ms-2 w-100">
                <div class="w-100 d-flex align-items-center">
                  <h5 class="mb-1">
                    {{ order.client ? order.client.name : "Walk-in customer" }}
                    <span v-if="order?.room"
                      >Room: [{{ order.room.room_name }}]</span
                    >
                  </h5>
                  <span
                    :class="`dot sq ms-auto h6 mb-0 ${
                      kitchenItems[0].delivered == 1
                        ? 'bg-success'
                        : 'bg-warning'
                    }`"
                    style="width: 14px; height: 14px"
                  >
                  </span>
                </div>
                <span class="text-dark"
                  >{{ kitchenItems.length }} items &nbsp;
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
                      d="M17 8l4 4m0 0l-4 4m4-4H3"
                    ></path>
                  </svg>
                  Kitchen
                </span>
              </div>
            </div>

            <div class="vertical-line mx-2"></div>

            <div
              class="d-flex align-items-center flex-nowrap w-100"
              v-if="barItems.length"
            >
              <div class="bg-primary rounded-1 p-2 text-center">
                <h4 class="m-0 text-white fw-bolder">T{{ order.table?.id }}</h4>
              </div>
              <div class="ms-2 w-100">
                <div class="w-100 d-flex align-items-center">
                  <h5 class="mb-1">
                    {{ order.client ? order.client.name : "Walk-in customer" }}
                    <span v-if="order?.room"
                      >Room: [{{ order.room.room_name }}]</span
                    >
                  </h5>
                  <span
                    :class="`dot sq ms-auto h6 mb-0 ${
                      barItems[0].delivered == 1 ? 'bg-success' : 'bg-warning'
                    }`"
                    style="width: 14px; height: 14px"
                  >
                  </span>
                </div>
                <span class="text-dark"
                  >{{ barItems.length }} item(s) &nbsp;
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
                      d="M17 8l4 4m0 0l-4 4m4-4H3"
                    ></path>
                  </svg>
                  Bar
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="">
          <div class="accordion" id="_dm-summaryAccordion">
            <div class="accordion-item">
              <div class="accordion-header" id="_dm-defAccHeadingTwo">
                <button
                  class="accordion-button"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#_dm-defAccCollapseTwo"
                  aria-expanded="true"
                  aria-controls="_dm-defAccCollapseTwo"
                >
                  <div class="d-flex align-items-center w-100">
                    <span
                      class="bg-gray-900 text-white p-1 rounded-circle me-1"
                    >
                      <svg
                        height="15px"
                        viewBox="0 0 16 16"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l.5 2H5V5H3.14zM6 5v2h2V5H6zm3 0v2h2V5H9zm3 0v2h1.36l.5-2H12zm1.11 3H12v2h.61l.5-2zM11 8H9v2h2V8zM8 8H6v2h2V8zM5 8H3.89l.5 2H5V8zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"
                        ></path>
                      </svg>
                    </span>
                    <span class="me-3">Order Summary</span>
                    <span class="ms-auto fw-bolder m-2 h5 mb-0">{{
                      formatMoney(order.grand_total)
                    }}</span>
                  </div>
                </button>
              </div>
              <div
                id="_dm-defAccCollapseTwo"
                class="accordion-collapse collapse show"
                aria-labelledby="_dm-defAccHeadingTwo"
                data-bs-parent="#_dm-summaryAccordion"
                style=""
              >
                <div class="accordion-body p-2">
                  <div class="bg-white rounded px-3 py-4 mb-2">
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
                    <div
                      class="border-bottom mb-2"
                      v-for="(item, i) in items"
                      :key="'order_items' + i"
                    >
                      <div class="d-flex align-items-center flex-nowrap">
                        <h6 class="text-foggy">
                          <span class="fw-bold d-block">{{ item.name }}</span>
                          <span class="fw-normal d-block"
                            >{{ item.quantity }} x
                            {{ $helper.formatNumber(item.price) }}</span
                          >
                        </h6>
                        <div class="ms-auto">
                          <span class="fw-bolder text-dark h6">{{
                            formatMoney(item.amount)
                          }}</span>
                          <template
                            v-if="order.resolved == 0 && hasPermissionTo('D')"
                          >
                            <a
                              class="text-danger"
                              href="javascript:void(0)"
                              @click.prevent="handleItemDelete(order.id, item)"
                              ><i class="demo-pli-trash fs-5 ms-2"></i
                            ></a>
                          </template>
                        </div>
                      </div>
                      <div
                        class="addons-container ps-1 my-1"
                        v-if="item.addons && item.addons.length > 0"
                      >
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
                                    <span class="ms-auto">{{
                                      formatMoney(0)
                                    }}</span>
                                  </a>
                                </li>
                              </ul>
                            </li>
                          </ul>
                        </div>
                      </div>
                      <div
                        class="py-2 px-3 border mb-2 rounded-1"
                        style="background: #f9fafc"
                        v-if="item.comment"
                      >
                        <span class="fw-bolder mb-2">Notes:</span>
                        <p class="mb-0">{{ item.comment }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        class="sidebar__stuck align-item-center d-flex py-2 border-top mt-2 position-absolute bottom-0 w-100"
        v-if="order.paid == 0 && order.resolved == 0 && isCashier"
      >
        <div class="w-50 px-1 pb-2" v-if="hasPermissionTo('D')">
          <button
            class="btn btn-light border w-100"
            type="button"
            @click="
              () => {
                actionType = 'REJECT';
                togglePaymentWrapper();
              }
            "
          >
            <span class="text-danger">Reject</span>
          </button>
        </div>
        <div class="w-50 px-1 pb-2" v-if="order.printed == 1">
          <button
            class="bnt-block btn btn-primary w-100"
            type="button"
            @click="togglePaymentWrapper"
          >
            Pay {{ formatMoney(order.grand_total - order.paid_amount) }}
          </button>
        </div>
      </div>
      <div
        class="border-top border-3 border-primary shadow position-absolute w-100 p-3 bottom-0 bg-white payment-wrapper payment-hidden"
        style="z-index: 99999; margin-left: -7px"
      >
        <div class="d-flex align-items-center pb-2 border-bottom">
          <ul class="nav nav-pills" v-if="actionType == 'RESOLVE'">
            <li class="nav-item me-2">
              <a
                class="nav-link h5 mb-0 border"
                :class="{ active: currentTab == 'payment' }"
                href="#!"
                @click.prevent="currentTab = 'payment'"
                ><i class="uil uil-money-withdraw me-1"></i>Process Payment</a
              >
            </li>
            <li class="nav-item">
              <a
                class="nav-link h5 mb-0 border"
                href="#!"
                :class="{ active: currentTab == 'assign' }"
                @click.prevent="currentTab = 'assign'"
                ><i class="uil uil-chat-bubble-user me-1"></i>Credit Post</a
              >
            </li>
          </ul>
          <h5 v-else>Delete Order #{{ $helper.padNumber(order.id) }}</h5>
          <div class="ms-auto">
            <a
              href="javascript:void(0)"
              @click.prevent="
                () => {
                  actionType = 'RESOLVE';
                  togglePaymentWrapper();
                }
              "
              class="text-primary ms-1 border rounded fw-bolder"
              style="padding: 0.1rem 0.25rem"
              ><i class="demo-pli-cross fw-bold icon fs-6"></i
            ></a>
          </div>
        </div>
        <template v-if="actionType == 'RESOLVE'">
          <div class="payment-form py-3">
            <template v-if="currentTab == 'payment'">
              <div class="row mb-2">
                <label class="col-lg-3 col-md-5 required">Paid Amount</label>
                <div class="col-lg-6 col-md-7">
                  <input
                    type="number"
                    v-model="amount_paid"
                    class="form-control"
                    placeholder="Amount to pay"
                    @keyup="calculateRemainAmount"
                  />
                </div>
              </div>
              <div class="row mb-2">
                <label class="col-lg-3 col-md-5">Amount Due</label>
                <div class="col-lg-6 col-md-7 text-end">
                  <span class="fw-bold h5">{{
                    formatMoney(amount_remain)
                  }}</span>
                </div>
              </div>
              <template v-if="amount_paid && amount_paid > 0">
                <div class="row mb-2">
                  <label class="col-lg-3 col-md-5 required"
                    >Payment Method</label
                  >
                  <div class="col-lg-6 col-md-7">
                    <select
                      class="form-control form-select"
                      v-model="payment_method"
                    >
                      <option value="null" hidden disabled>
                        Select option
                      </option>
                      <option
                        :value="row.id"
                        :key="row.id"
                        v-for="row in paymentModes"
                      >
                        {{ row.name }}
                      </option>
                    </select>
                  </div>
                </div>
                <div class="row mb-2">
                  <label class="col-lg-3 col-md-5 required">Deposited To</label>
                  <div class="col-lg-6 col-md-7">
                    <select
                      class="form-control form-select"
                      v-model="account_id"
                    >
                      <option value="null" hidden disabled>
                        Select option
                      </option>
                      <option
                        :value="row.id"
                        :key="row.id"
                        v-for="row in accounts"
                      >
                        {{ row.name }}
                      </option>
                    </select>
                  </div>
                </div>
                <div class="row mb-2">
                  <label class="col-lg-3 col-md-5">Payment Ref</label>
                  <div class="col-lg-6 col-md-7">
                    <input
                      type="text"
                      v-model="payment_ref"
                      class="form-control"
                    />
                  </div>
                </div>
              </template>
              <div class="row mb-2">
                <label class="col-lg-3 col-md-5">Comment</label>
                <div class="col-lg-6 col-md-7">
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
                    class="bnt-block btn btn-primary w-100"
                    activator="PAYING_BILL"
                    :disabler="submitDisabled"
                    @clicked="handlePayment"
                  >
                    Confirm
                  </wolf-button>
                </div>
              </div>
            </template>
            <template v-if="currentTab == 'assign'">
              <div class="row mb-3 align-items-center">
                <label class="col-lg-3 col-md-5 fw-bold">Amount Due</label>
                <div class="col-lg-6 col-md-7 text-end">
                  <span class="fw-bold h5">{{
                    formatMoney(order?.grand_total - order?.paid_amount)
                  }}</span>
                </div>
              </div>
              <div class="row mb-3 align-items-center">
                <label class="col-lg-3 col-md-5 fw-bold">Client</label>
                <div class="col-lg-6 col-md-7">
                  <zoho-search
                    url="clients/search"
                    label="name"
                    primary-key="id"
                    placeholder="Select Client"
                    v-model="customer"
                    :load-defaults="true"
                    fetch-flag="SEARCHING_FOR_CLIENTS"
                    :is-vertical="true"
                    :records="clients"
                  >
                    <template #footer>
                      <div class="position-sticky bottom-0 bg-white">
                        <li class="border-top dropdown-item pl-7">
                          <a
                            href="javascript:void(0)"
                            @click.prevent="newClientModalOpen = true"
                          >
                            <span class="text-blue cursor-pointer"
                              ><svg
                                height="18px"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              >
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                              </svg>
                              &nbsp;Add New</span
                            >
                          </a>
                        </li>
                      </div>
                    </template>
                  </zoho-search>
                </div>
              </div>
              <div class="row">
                <label class="col-lg-3 col-md-5"></label>
                <div class="col-lg-6 col-md-7">
                  <wolf-button
                    class="bnt-block btn btn-primary w-100"
                    activator="HANDLE_CREDIT_POST"
                    :disabler="!customer"
                    @clicked="assignClient"
                  >
                    Confirm and Assign
                  </wolf-button>
                </div>
              </div>
            </template>
          </div>
        </template>
        <template v-else>
          <div class="py-3">
            <div class="alert alert-warning" role="alert">
              If you confirm, there is way to restore deleted order.
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
          </div>
        </template>
      </div>
    </div>
  </aside>
  <client-creator
    v-if="newClientModalOpen"
    @closed="newClientModalOpen = false"
    @saved="handleAddedClient"
  />
</template>
<style scoped>
.payment-hidden {
  visibility: hidden;
  padding: 0 !important;
  transform: translateY(100%);
}
.payment-wrapper {
  transition: visibility 0.5s, padding 0.5s ease, transform 0.5s ease;
  transition-timing-function: cubic-bezier(
    0.005,
    1.045,
    0.25,
    0.915
  ) !important;
}

.assign-hidden {
  visibility: hidden;
  padding: 0 !important;
  transform: translateY(100%);
}
.assign-wrapper {
  transition: visibility 0.5s, padding 0.5s ease, transform 0.5s ease;
  transition-timing-function: cubic-bezier(
    0.005,
    1.045,
    0.25,
    0.915
  ) !important;
}
.sidebar__inner {
  height: 97vh;
}
.tame-drawer {
  bottom: unset;
}
</style>
