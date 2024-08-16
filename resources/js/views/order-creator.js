import Avatar from "@/components/Avatar.vue";
import LazyImage from "@/components/LazyImage.vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import EmptyResults from "@/components/EmptyResults.vue";
import ClientCreator from "@/shared/ClientCreator.vue";
import { Navigation } from "swiper";
import {
  cartItemAddedSound,
  cartItemEditedSound,
  placeOrderSound,
} from "@/common/beeps";
import "swiper/css";
import "swiper/css/navigation";
import ZohoSearch from "@/components/ZohoSearch.vue";
import { permissionsHandler } from "@/common/permissions-handler";
import BootstrapModal from "@/components/BootstrapModal.vue";
import { getUploadedImage } from "@/common/utils.js";

export default {
  name: "OrderCreator",
  mixins: [permissionsHandler],
  components: {
    Avatar,
    Swiper,
    SwiperSlide,
    LazyImage,
    EmptyResults,
    ZohoSearch,
    BootstrapModal,
    ClientCreator,
  },
  data: () => ({
    types: [],
    items: [],
    itemsFilter: {
      group: "all",
      category: null,
    },
    modules: [Navigation],
    searchKey: null,
    orderItems: [],
    tables: [],
    selectedTable: {},
    tableKeyword: null,
    orderCategory: "DINE IN",
    client: null,
    comment: null,
    room_id: null,
    searchBarOpen: false,
    record: null,
    lockedTables: [],
    checkTableInterval: null,
    customer: null,
    selectedItemIndex: null,
    waiters: [],
    currentWaiter: {},
    waiter: null,
    addons: [],
    addonsModalOpen: false,
    selectedAddons: [],
    newClientModalOpen: false,
    print_round_slip: false,
  }),
  computed: {
    activeTables() {
      return Array.isArray(this.lockedTables) ? this.lockedTables : [];
    },
    categories() {
      let records = this.types;
      if (this.itemsFilter.group != "all") {
        const filter = this.itemsFilter.group == "drinks" ? "BAR" : "KITCHEN";
        records = records.filter((row) => {
          return row.type == filter;
        });
      }
      this.itemsFilter.category = null;
      return records;
    },
    products() {
      const searchKey = this.searchKey && this.searchKey.toLowerCase();
      let rows = this.items;
      if (searchKey) {
        rows = rows.filter(
          (row) => String(row.name).toLowerCase().indexOf(searchKey) > -1
        );
      } else {
        if (!this.$helper.empty(this.itemsFilter.category)) {
          const id = this.itemsFilter.category.id;
          rows = rows.filter((row) => {
            return row.type_id == id;
          });
        }
        if (this.itemsFilter.group != "all") {
          const filter = this.itemsFilter.group == "drinks" ? "BAR" : "KITCHEN";
          rows = rows.filter((row) => {
            return row.group == filter;
          });
        }
      }
      return rows;
    },
    cartItems() {
      const result = this.orderItems.map((item) => item.id);
      this.$store.state.orderedItems = result;
      return result;
    },
    subTotal() {
      return this.orderItems.reduce(
        (a, b) => a + b.quantity * (b.price || b.cost),
        0
      );
    },
    filteredTables() {
      const keyword = this.tableKeyword && this.tableKeyword.toLowerCase();
      let tables = this.tables;
      if (keyword) {
        tables = tables.filter(
          (item) => String(item.name).toLowerCase().indexOf(keyword) > -1
        );
      }
      return tables;
    },
    existingItems() {
      return this.orderItems.filter(
        (item) => item.old_quantity || item.round_key
      );
    },
    submitDisabled() {
      return (
        (this.record !== null &&
          this.existingItems.every(
            (item) => item.old_quantity == item.quantity
          ) &&
          this.existingItems.length == this.orderItems.length) ||
        (this.orderCategory == "DINE IN" &&
          this.$helper.empty(this.selectedTable)) ||
        !this.cartItems.length
      );
    },
    currentAddons() {
      return this.selectedAddons.map((item) => item.id);
    },
  },
  beforeCreate() {
    this.$http.get("items/types").then((response) => {
      this.types = response.data.rows;
    });
  },
  created() {
    const reference = this.$route.params.reference;
    if (reference) {
      this.$http.get(`orders/items/${reference}`).then((response) => {
        this.record = response.data.record;
        this.orderItems = [...response.data.items, ...this.orderItems];
        this.selectedTable = response.data.record.table;
      });
    }
  },
  mounted() {
    this.$http.get("items/list?nopagination=1").then((response) => {
      this.items = response.data.rows;
    });
    this.$http.get("tables/show").then((response) => {
      this.tables = response.data.tables;
    });
    this.$http.get(`tables/locked`).then((response) => {
      this.lockedTables = response.data;
      this.checkTableInterval = setInterval(() => {
        this.$store.commit("SET_REQUEST_FLAG", "GETTING_LOCKED_TABLES");
        this.$http.get(`tables/locked`).then((response) => {
          this.lockedTables = response.data;
        });
      }, 5000);
    });
    this.$nextTick(() => {
      this.currentWaiter = this.loggedUser;
      if (!this.isCashier) {
        this.waiter = this.loggedUser.id;
      }
      this.$store.commit("SET_REQUEST_FLAG", "GETTING_ADDONS");
      this.$http.get("items/add-ons").then((response) => {
        this.addons = response.data.rows;
      });

      if (this.isCashier) {
        const url = "users/search?role=waiters";
        this.$store.commit("SET_REQUEST_FLAG", "GETTING_WAITERS");
        this.$http.get(url).then((response) => {
          this.waiters = response.data;
        });
      }
    });
  },
  methods: {
    getUploadedImage,
    handleCancel() {
      document.querySelector("#root").classList.remove("sb--show");
    },
    handleAddedClient(client) {
      this.clients.push(client);
      this.newClientModalOpen = false;
      this.$store.commit("SET_FLASH_MESSAGE", {
        type: "success",
        message: "Client added successfully",
      });
    },
    selectAddon(item) {
      if (this.currentAddons.includes(item.id)) {
        const index = this.selectedAddons.findIndex(
          (addon) => addon.id == item.id
        );
        this.selectedAddons.splice(index, 1);
      } else {
        this.selectedAddons.push({
          ...item,
          ...{ quantity: 1, price: 0, cost: 0 },
        });
      }
    },
    toggleAddonsModal() {
      this.selectedAddons = [];
      this.addonsModalOpen = !this.addonsModalOpen;
    },
    setItemAddons() {
      this.orderItems[this.selectedItemIndex].addons = [...this.selectedAddons];
      this.selectedAddons = [];
      this.addonsModalOpen = false;
    },
    selectWaiter(e) {
      this.currentWaiter = this.waiters.find(
        (waiter) => waiter.id == e.target.value
      );
    },
    chooseTable(table) {
      if (this.lockedTables.includes(table.id))
        this.$store.commit("SET_FLASH_MESSAGE", {
          type: "danger",
          message: `Table ${
            table.name || table.id
          } is currently locked on a pending invoice`,
        });
      else this.selectedTable = table;
    },
    removeFromCart(i) {
      this.orderItems.splice(i, 1);
      cartItemEditedSound.play();
    },
    addToCart(item, sign = "+", index = null) {
      if (index != null) {
        if (sign == "-") {
          this.orderItems[index].quantity =
            Number(this.orderItems[index].quantity) - 1;
          if (this.orderItems[index].quantity <= 0)
            this.orderItems.splice(index, 1);
        } else {
          this.orderItems[index].quantity =
            Number(this.orderItems[index].quantity) + 1;
        }
        cartItemEditedSound.play();
      } else {
        item.quantity = 1;
        this.orderItems.push({ ...item, ...{ addons: [] } });
        cartItemAddedSound.play();
      }
    },
    editItemCart(item, sign = "+", index) {
      if (sign == "-") {
        if (item.quantity > item.old_quantity)
          this.orderItems[index].quantity =
            Number(this.orderItems[index].quantity) - 1;
      } else {
        this.orderItems[index].quantity =
          Number(this.orderItems[index].quantity) + 1;
        this.orderItems[index].edited = true;
      }
      cartItemEditedSound.play();
    },
    removeOrderItem(item) {
      this.$confirm({
        title: "Delete Item from orders",
        content: `Are you sure you want to remove ${item.name}`,
        classes: "btn btn-danger",
        actionText: "Continue",
        closeText: "Discard",
        action: () => {
          const index = this.cartItems.indexOf(item.id);
          this.orderItems[index].edited = true;
        },
      });
    },
    getGeneralName(categoryName) {
      const result = categoryName.split("/")[1] || categoryName;
      return result.toLowerCase();
    },
    setCurrentCategory(item) {
      this.itemsFilter.category = item;
    },
    setCustomerFromRoom(room) {
      this.customer = room.client_id;
    },
    handleSubmit() {
      if (!this.currentWaiter || Object.keys(this.currentWaiter).length === 0) {
        this.currentWaiter = this.loggedUser;
      }
      const barItems = [];
      const kitchenItems = [];
      const items = this.orderItems.filter(
        (item) =>
          (item.round_key && item.old_quantity != item.quantity) ||
          !item.round_key
      );
      items.forEach((item) => {
        if (item.group == "BAR") barItems.push(item);
        else kitchenItems.push(item);
      });

      this.barItems = barItems;
      this.kitchenItems = kitchenItems;

      this.$http
        .post(
          "orders/store",
          this.$helper.generateFormData({
            category: this.orderCategory,
            table_id: this.selectedTable.id,
            client_id: this.customer || null,
            comment: this.comment,
            grand_total: this.subTotal,
            discount: 0,
            amount: this.subTotal,
            bar_items: barItems,
            kitchen_items: kitchenItems,
            order_id: this.record != null ? this.record.id : null,
            waiter_id: this.currentWaiter.id,
            disable_print: this.appSettings?.disabled_direct_print ? 1 : 0,
            room_id: this.room_id,
            print_round_slip: this.print_round_slip,
          })
        )
        .then((response) => {
          if (response.data.status) {
            this.orderItems = [];
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "success",
              message: "Order placed successfully",
            });
            placeOrderSound.play();
            this.selectedTable = {};
            setTimeout(() => {
              if (!this.isCashier) {
                this.handleSignoutAction();
              } else {
                this.$router.replace({ name: "WaiterOrders" });
              }
            }, 2000);
          }
        });
    },
    handleSignoutAction() {
      this.$http
        .post("auth/logout", this.$helper.generateFormData({}))
        .then((response) => {
          if (response.data.status) {
            this.$store.dispatch("logout").then(() => {
              window.location.replace(
                this.$router.resolve({ name: "FrontOfficeLogin" }).href
              );
            });
          }
        });
    },
    hideSearchBar() {
      this.searchBarOpen = false;
      this.searchKey = null;
    },
    showSearch() {
      this.searchBarOpen = true;
      this.$nextTick(() => {
        document.querySelector("#search-field").focus();
      });
    },
  },
  beforeUnmount() {
    clearInterval(this.checkTableInterval);
  },
};
