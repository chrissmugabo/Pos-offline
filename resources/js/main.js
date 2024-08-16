import { createApp } from "vue";
import App from "./App.vue";
import "@/registerServiceWorker";
import router from "@/router";
import store from "@/store";
import axios from "axios";
import { globalMixin } from "@/common/global-mixin.js";
import helper from "@/common/index.js";
import WolfButton from "@/components/WolfButton.vue";
import confirm from "@/common/confirm";
import notify from "@/common/notify";
import { encodeQuery, baseURL } from "@/common/utils.js";
const app = createApp(App);
axios.withCredentials = true;
axios.interceptors.request.use(
  (config) => {
    const branch = localStorage.getItem("_branch");
    if (branch) {
      config.url = encodeQuery(config.url, {
        current_branch: JSON.parse(branch).id,
      });
    }
    store.state.isLoading = true;
    if (store.state.requestFlag) {
      store.state.pendingRequests[store.state.requestFlag] = config.url;
      store.state.requestFlag = null;
    }
    return config;
  },
  (error) => {
    store.commit("REQUEST_RESOLVED", error.config.url);
    return Promise.reject(error);
  }
);

axios.interceptors.response.use(
  (response) => {
    store.commit("REQUEST_RESOLVED", response.config.url);
    return response;
  },
  (error) => {
    store.commit("REQUEST_RESOLVED", error.config.url);
    if (error.response.status === 401) {
      localStorage.removeItem("token");
      //location.replace(router.resolve({ name: "Login" }).href);
    }
    if (error.response.status == 500) {
      store.commit("SET_FLASH_MESSAGE", {
        type: "danger",
        message: "Error Occured. Please contact system administrator",
      });
    }
    return Promise.reject(error);
  }
);

app.config.globalProperties.$http = axios;
app.config.globalProperties.$http.defaults.baseURL =
  process.env.NODE_ENV == "production"
    ? `${baseURL}pos/`
    : `${baseURL}api/pos/`;
const token = localStorage.getItem("token");
if (token) {
  app.config.globalProperties.$http.defaults.headers.common["Authorization"] =
    "Bearer " + token;
}
app.component("wolf-button", WolfButton);
app.mixin(globalMixin);
app
  .use(confirm, store)
  .use(notify, store)
  .use(helper)
  .use(store)
  .use(router)
  .mount("#app");
