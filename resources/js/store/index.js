import { createStore } from "vuex";
export default createStore({
  state: {
    navBarOpen: false,
    timeZone: localStorage.getItem("_tz") || "Africa/Kigali",
    today:
      localStorage.getItem("today") ||
      new Date()
        .toLocaleString("fr-CA", { timeZone: "Africa/Kigali" })
        .slice(0, 10),
    token: localStorage.getItem("token") || "",
    branch: JSON.parse(localStorage.getItem("_branch")) || {},
    user: {},
    system_date: "",
    invalid_date: false,
    isLoading: false,
    requestFlag: [],
    pendingRequests: {},
    isCorrectSystemDay: false,
    hasActiveModal: false,
    flashMessage: {},
    branches: [],
    settings: {
      site_address: null,
      site_name: null,
      currency: null,
      site_logo: null,
      contact_one: null,
      app_phone: null,
      app_email: null,
      app_tin: null,
      momo_code: null,
      airtel_code: null,
      disabled_direct_print: 0,
    },
    routes: {},
    accounts: [],
    paymentModes: [],
    orderedItems: [],
  },

  mutations: {
    SET_PAYMENT_META(state, data) {
      state.accounts = data.accounts;
      state.paymentModes = data.modes;
    },
    REQUEST_RESOLVED(state, url) {
      for (let key in state.pendingRequests) {
        if (state.pendingRequests[key] === url) {
          delete state.pendingRequests[key];
          break;
        }
      }
      if (!Object.keys(state.pendingRequests).length) {
        state.isLoading = false;
      }
      if (state.hasActiveModal) {
        state.hasActiveModal = false;
        document.querySelector("#alert-container").remove();
      }
    },
    REGISTER_CONFIRM_MODAL(state) {
      state.requestFlag = "HANDLING_ALERT_ACTION";
      state.hasActiveModal = true;
    },
    SET_APP_SETTINGS(state, settings) {
      state.settings = settings;
      localStorage.setItem("_tz", settings.timezone);
      localStorage.setItem("today", settings.today);
      state.system_date = settings.system_date;
      state.invalid_date = settings.invalid_date;
    },
    SET_LOCKED_BLANCH(state, branch) {
      if (branch != null)
        localStorage.setItem("_branch", JSON.stringify(branch));
      else localStorage.removeItem("_branch");
      state.branch = branch;
    },
    SET_REQUEST_FLAG(state, flag) {
      state.requestFlag = flag;
    },
    SET_FLASH_MESSAGE(state, message) {
      state.flashMessage = message;
      setTimeout(() => {
        state.flashMessage = {};
      }, 5000);
    },
    logout(state) {
      state.status = "";
      state.token = "";
    },
    SET_CURRENT_USER(state, user) {
      state.user = user;
    },
    SET_BRANCHES(state, branches) {
      state.branches = branches;
    },
  },
  actions: {
    logout({ commit }) {
      return new Promise((resolve) => {
        commit("logout");
        localStorage.removeItem("_branch");
        localStorage.removeItem("token");
        resolve();
      });
    },
  },
  getters: {
    isLoggedIn: (state) => !!state.token,
    authStatus: (state) => state.status,
    user: (state) => state.user,
    branch: (state) => state.branch,
    requestFlag: (state) => state.requestFlag,
    pendingRequests: (state) => state.pendingRequests,
    orderedItemsLen: (state) => state.orderedItems.length,
  },
});
