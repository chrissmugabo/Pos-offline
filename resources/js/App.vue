<template>
  <transition name="bounce">
    <div
      v-if="!$helper.empty(flashMessage)"
      id="ember201"
      class="notification-message text-center zf-notification-container ember-view"
      style="
        transform: translateY(0px);
        opacity: 1;
        transition: transform 0.6s ease 0s;
      "
    >
      <div align="center">
        <div :class="`message-container ${flashMessage.type}`">
          <div>
            <svg
              class="svg-icon"
              v-if="flashMessage.type == 'warning'"
              viewBox="0 0 17 16"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 5zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"
              ></path>
            </svg>

            <svg
              v-if="flashMessage.type == 'info'"
              class="svg-icon"
              viewBox="0 0 16 16"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM8 5.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"
              ></path>
            </svg>
            <svg
              v-if="flashMessage.type == 'success'"
              class="svg-icon"
              viewBox="0 0 16 16"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
              ></path>
            </svg>

            <svg
              class="svg-icon"
              v-if="flashMessage.type == 'danger'"
              viewBox="0 0 16 16"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zm-6.106 4.5a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"
              ></path>
            </svg>
          </div>
          <div class="msg">
            <span>{{ flashMessage.message }}</span>
          </div>
          <div @click="$store.commit('SET_FLASH_MESSAGE', {})">
            <svg
              version="1.1"
              id="Layer_1"
              xmlns="http://www.w3.org/2000/svg"
              x="0"
              y="0"
              viewBox="0 0 512 512"
              xml:space="preserve"
              class="icon-remove"
            >
              <path
                d="M455.2 9.2L256 208.4 56.8 9.2C44.5-3.1 24.6-3.1 12.2 9.2l-2.9 2.9C-3 24.4-3 44.4 9.3 56.7L208.4 256 9.2 455.2c-12.3 12.3-12.3 32.3 0 44.6l2.9 2.9c12.3 12.3 32.3 12.3 44.6 0L256 303.6l199.2 199.2c12.3 12.3 32.3 12.3 44.6 0l2.9-2.9c12.3-12.3 12.3-32.3 0-44.6L303.6 256 502.8 56.8c12.3-12.3 12.3-32.3 0-44.6l-2.9-2.9c-12.5-12.4-32.4-12.4-44.7-.1z"
              ></path>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </transition>
  <div id="root" class="root hd--expanded mn--min">
    <section
      id="content"
      class="content pt-5"
      :class="{ 'bg-white': requiredWhiteBg }"
    >
      <div class="content__boxed">
        <div
          class="content__wrap"
          :class="{ 'p-0': currentRoute == 'OrdersCreator' }"
        >
          <loader v-if="globalAjaxRequest"></loader>
          <router-view />
        </div>
      </div>
    </section>
    <template v-if="appReady">
      <navigation-bar></navigation-bar>
      <app-header></app-header>
      <ShiftManager v-if="!loggedUser?.working && !isCashier" />
      <BottomNavigation></BottomNavigation>
    </template>
  </div>
</template>
<script>
import AppHeader from "./shared/AppHeader.vue";
import NavigationBar from "./shared/NavigationBar.vue";
import BottomNavigation from "./shared/BottomNavigation.vue";
import Loader from "@/components/Loader.vue";
import { permissionsHandler } from "@/common/permissions-handler";
import ShiftManager from "./views/ShiftManager.vue";
export default {
  mixins: [permissionsHandler],
  components: {
    AppHeader,
    NavigationBar,
    Loader,
    ShiftManager,
    BottomNavigation,
  },
  data: () => ({}),
  computed: {
    flashMessage() {
      return this.$store.state.flashMessage;
    },
    appSettings() {
      return this.$store.state.settings;
    },
    appReady() {
      return !this.$helper.empty(this.loggedUser);
    },
    globalAjaxRequest() {
      return (
        this.$store.state.isLoading &&
        !Object.keys(this.$store.getters.pendingRequests).length
      );
    },
    currentRoute() {
      return this.$route.name;
    },
    requiredWhiteBg() {
      return ["FrontOfficeSalesReport"].includes(this.$route.name);
    },
  },
  created() {
    if (this.isUserLoggedIn) {
      this.$http.get("auth/myself").then((response) => {
        this.$store.commit("SET_CURRENT_USER", { ...response.data.user });
      });
      this.$http.get("shared/payments-meta").then((response) => {
        this.$store.commit("SET_PAYMENT_META", response.data);
      });
    }
    this.$http
      .post(
        "frontend/preloaders",
        this.$helper.generateFormData({ keys: Object.keys(this.appSettings) })
      )
      .then((response) => {
        this.$store.commit("SET_APP_SETTINGS", response.data);
      });
  },
  watch: {
    $route(to) {
      if (to.name == "OrdersCreator") {
        document.body.classList.add("overflow-hidden");
      } else {
        document.body.classList.remove("overflow-hidden");
      }
    },
  },
};
</script>
