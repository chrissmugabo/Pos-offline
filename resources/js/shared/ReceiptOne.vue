<script>
import { ordersMixin } from "@/common/global-mixin";
export default {
  name: "ReceiptOne",
  mixins: [ordersMixin],
  props: {
    order: {
      type: Object,
      required: true,
    },
    invoiced: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  data: () => ({
    items: [],
  }),
  created() {
    this.getOrderItems(this.order.id).then((items) => {
      this.items = items;
    });
  },
};
</script>
<template>
  <div id="print-container">
    <template v-if="invoiced">
      <div class="fs-5 text-center py-1 border-bottom border-dashed">
        <div class="site-log">
          <img
            :src="getSiteLogo()"
            style="width: 150px"
            :alt="appSettings?.site_name"
          />
        </div>
        <p class="h5 mb-1">
          <b>{{ appSettings.site_name }} </b>
        </p>
        <p class="mb-0 h6">TIN: {{ appSettings.app_tin }}</p>
        <p class="mb-0 h6">
          Tel: {{ appSettings.app_phone }}
          <span v-if="appSettings?.contact_one"
            >/{{ appSettings?.contact_one }}</span
          >
        </p>
        <p class="mb-0 h6">Email:{{ appSettings?.app_email }}</p>
        <p class="mb-0 h6">Address: {{ appSettings?.site_address }}</p>
      </div>
      <div
        class="py-1 border-bottom border-dashed"
        v-if="!$helper.empty(order)"
      >
        <table class="table table-sm table-borderless mb-1">
          <tr>
            <td colspan="2">
              <span class=""
                >INVOICE NO:
                <b>#{{ $helper.generateVoucherNo(order.id) }}</b></span
              >
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <span class=""
                >Customer: <b>{{ order?.client?.name || "Walk-In" }}</b></span
              >
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <span class=""
                >Served By:
                <b>{{
                  order.waiter ? order.waiter.name : "Administrator"
                }}</b></span
              >
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <span class=""
                >Table: <b>{{ order?.table?.name }}</b></span
              >
            </td>
          </tr>
          <tr>
            <td>
              <span
                >Date: <b>{{ $helper.formatDate(order.order_date) }}</b></span
              >
            </td>
            <td class="text-end text-nowrap">
              <span
                ><b>{{ $helper.formatTime(order.order_date) }}</b></span
              >
            </td>
          </tr>
        </table>
      </div>
    </template>
    <div class="py-1 border-bottom border-dashed">
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
      <table class="table table-sm table-borderless mb-1">
        <tr v-for="(item, i) in items" :key="'order_items' + i">
          <td>
            <span>{{ item.name }}</span>
          </td>
          <td>
            <span
              >{{ item.quantity }} x
              {{ $helper.formatNumber(item.price) }}</span
            >
          </td>
          <td class="text-end text-nowrap">
            <span>{{ $helper.formatNumber(item.amount) }}</span>
          </td>
        </tr>
      </table>
    </div>
    <template v-if="invoiced">
      <div class="py-1 border-bottom border-dashed">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td><span class="fw-bolder">Grand Total</span></td>
            <td class="text-end text-nowrap">
              <span class="h6 mb-0">{{ formatMoney(order.grand_total) }}</span>
            </td>
          </tr>
        </table>
      </div>
      <div class="py-1 text-center">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td colspan="2">
              <span class="h5 mb-0" v-if="appSettings?.momo_code"
                >Dial {{ appSettings?.momo_code }} to pay with MOMO</span
              >
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div class="text-center" style="font-size: 12px">
                This is not a legal receipt. Please ask your legal receipt.
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <span class="h5 mb-0"> <em>Thank you!</em> </span>
            </td>
          </tr>
        </table>
      </div>
    </template>
  </div>
</template>
