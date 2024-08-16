import { baseURL } from "@/common/utils.js";
export const globalMixin = {
  data: () => ({
    modalOpen: false,
    filterKey: null,
    publicPath: process.env.BASE_URL,
    permissionsPath: null,
  }),
  computed: {
    isUserLoggedIn: function () {
      return this.$store.getters.isLoggedIn;
    },
    userPermissions() {
      if (this.$store.getters.user) {
        let role = this.$store.getters.user.role;
        if (role) return role.permissions;
      }
      return {};
    },
    appSettings() {
      const settings = this.$store.state.settings || {};
      return settings.result || {};
    },
    loggedUser() {
      return this.$store.getters.user || {};
    },
    userRole() {
      if (this.loggedUser.role) return this.loggedUser.role.name;
      else return null;
    },
    currentBranch() {
      return this.$store.getters.branch;
    },
    isOffline() {
      return this.$store?.state?.settings?.pos_env === "OFFLINE";
    },
  },
  methods: {
    getSiteLogo() {
      const siteLogo = this.appSettings?.site_logo;
      if (siteLogo) {
        return `${this.uploadsUrl + "/" + siteLogo}`;
      }
      return `${this.publicPath}img/logo.jpg`;
    },
    handleLogout() {
      this.$http
        .post("auth/logout", this.$helper.generateFormData({}))
        .then((response) => {
          if (response.data.status) {
            this.$store.dispatch("logout").then(() => {
              window.location.replace(
                this.$router.resolve({ name: "Login" }).href
              );
            });
          }
        });
    },
    toggleModal() {
      this.$helper.toggleModalOpen();
      this.modalOpen = !this.modalOpen;
    },
    formatMoney(num) {
      return `${this.appSettings.currency} ${this.$helper.formatNumber(num)}`;
    },
    getPaymentsMeta() {
      this.$store.commit("SET_REQUEST_FLAG", "GET_PAYMENTS_META");
      this.$http.get("shared/payments-meta").then((response) => {
        this.$store.commit("SET_PAYMENT_META", response.data);
      });
    },
    storePrintableOrder(fd) {
      this.$store.commit("SET_REQUEST_FLAG", "STORE_PRINTABLE_ORDER");
      this.$http
        .post("orders/store-printable", this.$helper.generateFormData(fd))
        .then((response) => {
          console.log(response.data);
        });
    },
    handleInvoicePrinting(invoice) {
      this.storePrintableOrder({
        category: "INVOICE",
        order_id: invoice.id,
      });
      this.$store.commit("SET_FLASH_MESSAGE", {
        type: "success",
        message: "Print command sent successfully",
      });
    },
    signOutWaiter() {
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
    isProcessing(task) {
      return Object.keys(this.$store.getters.pendingRequests).includes(task);
    },
  },
};

export const layoutMixin = {
  data: () => ({
    isOnline: navigator.onLine,
    syncInterval: null,
  }),
  created() {
    window.addEventListener("online", () => {
      this.updateOnlineStatus();
      this.handleSyncAction();
    });
    window.addEventListener("offline", () => {
      this.updateOnlineStatus();
      clearInterval(this.syncInterval);
      this.syncInterval = null;
    });
  },
  beforeDestroy() {
    clearInterval(this.syncInterval);
    this.syncInterval = null;
    window.removeEventListener("online", this.updateOnlineStatus);
    window.removeEventListener("offline", this.updateOnlineStatus());
  },
  methods: {
    updateOnlineStatus() {
      this.isOnline = navigator.onLine;
    },
    handleSyncAction() {
      this.syncInterval = setInterval(() => {
        const isOffline = this.$store?.state?.settings?.pos_env === "OFFLINE";
        if (isOffline) {
          fetch(`${baseURL}run-db-sync`, {
            mode: "no-cors",
            timeout: 5000,
          });
        }
      }, 30000);
    },
  },
  mounted() {
    this.$nextTick(() => {
      if (this.isOnline) {
        this.handleSyncAction();
      }
    });
  },
};

export const ordersMixin = {
  data: () => ({
    itemsLoader: "GETTING_ORDER_ITEMS",
  }),
  methods: {
    async getOrderItems(id) {
      this.$store.commit("SET_REQUEST_FLAG", this.loader);
      const result = await this.$http.get(`orders/items-by-id/${id}`);
      return result.data;
    },
  },
};
