<template>
  <nav id="mainnav-container" class="mainnav border-end">
    <div class="mainnav__inner">
      <div class="mainnav__top-content scrollable-content pb-5">
        <div class="mainnav__profile mt-3 d-flex3">
          <div class="mt-2 d-mn-max"></div>
          <div
            class="mininav-toggle text-center d-flex justify-content-center collapsed"
          >
            <avatar height="2rem" width="2rem" :name="loggedUser.name"></avatar>
          </div>
        </div>
        <div class="mainnav__categoriy pb-2 pt-5">
          <ul class="mainnav__menu nav flex-column">
            <template v-if="!['CHEF', 'WAITER', 'BARMAN'].includes(userRole)">
              <li class="nav-item py-1">
                <router-link
                  :to="{
                    name: 'OrdersCreator',
                    params: { action: 'new' },
                  }"
                  class="router-link-active router-link-exact-active text-center fw-bolder menu-text d-flex flex-column"
                  ><h4 class="mb-0 w-100">
                    <i class="demo-pli-mine fs-4"></i>
                  </h4>
                  <span class="text-capitalize fw-normal"
                    >Menu</span
                  ></router-link
                >
              </li>
              <li class="nav-item py-1">
                <router-link
                  :to="{
                    name: 'KitchenOrders',
                  }"
                  class="router-link-active router-link-exact-active text-center fw-bolder menu-text d-flex flex-column"
                  ><h4 class="mb-0 w-100">
                    <svg
                      height="15px"
                      class="fs-4"
                      viewBox="0 0 16 16"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022zM6 8.694L1 10.36V15h5V8.694zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5V15z"
                      ></path>
                      <path
                        d="M2 11h1v1H2v-1zm2 0h1v1H4v-1zm-2 2h1v1H2v-1zm2 0h1v1H4v-1zm4-4h1v1H8V9zm2 0h1v1h-1V9zm-2 2h1v1H8v-1zm2 0h1v1h-1v-1zm2-2h1v1h-1V9zm0 2h1v1h-1v-1zM8 7h1v1H8V7zm2 0h1v1h-1V7zm2 0h1v1h-1V7zM8 5h1v1H8V5zm2 0h1v1h-1V5zm2 0h1v1h-1V5zm0-2h1v1h-1V3z"
                      ></path>
                    </svg>
                  </h4>
                  <span class="text-capitalize fw-normal"
                    >KITCHEN</span
                  ></router-link
                >
              </li>
              <li class="nav-item py-1">
                <router-link
                  :to="{
                    name: 'BarOrders',
                  }"
                  class="router-link-active router-link-exact-active text-center fw-bolder menu-text d-flex flex-column"
                  ><h4 class="mb-0 w-100">
                    <svg
                      height="17px"
                      class="fs-4"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                      ></path>
                    </svg>
                  </h4>
                  <span class="text-capitalize fw-normal"
                    >BAR</span
                  ></router-link
                >
              </li>
            </template>
            <template v-else>
              <li class="nav-item py-1" v-if="userRole == 'WAITER'">
                <router-link
                  :to="{
                    name: 'OrdersCreator',
                    params: { action: 'new' },
                  }"
                  class="router-link-active router-link-exact-active text-center fw-bolder menu-text d-flex flex-column"
                  ><h4 class="mb-0 w-100">
                    <i class="demo-pli-mine fs-4"></i>
                  </h4>
                  <span class="text-capitalize fw-normal"
                    >Menu</span
                  ></router-link
                >
              </li>
              <li class="nav-item py-1" v-if="userRole == 'WAITER'">
                <router-link
                  :to="{
                    name: 'WaiterOrders',
                    params: {},
                  }"
                  class="router-link-active router-link-exact-active text-center fw-bolder menu-text d-flex flex-column"
                  ><h4 class="mb-0 w-100">
                    <svg
                      height="18px"
                      class="fs-4"
                      viewBox="0 0 16 16"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M0 2.5A1.5 1.5 0 0 1 1.5 1h11A1.5 1.5 0 0 1 14 2.5v10.528c0 .3-.05.654-.238.972h.738a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 1 1 0v9a1.5 1.5 0 0 1-1.5 1.5H1.497A1.497 1.497 0 0 1 0 13.5v-11zM12 14c.37 0 .654-.211.853-.441.092-.106.147-.279.147-.531V2.5a.5.5 0 0 0-.5-.5h-11a.5.5 0 0 0-.5.5v11c0 .278.223.5.497.5H12z"
                      ></path>
                      <path
                        d="M2 3h10v2H2V3zm0 3h4v3H2V6zm0 4h4v1H2v-1zm0 2h4v1H2v-1zm5-6h2v1H7V6zm3 0h2v1h-2V6zM7 8h2v1H7V8zm3 0h2v1h-2V8zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1z"
                      ></path>
                    </svg>
                  </h4>
                  <span class="text-capitalize fw-normal"
                    >Orders</span
                  ></router-link
                >
              </li>
              <li class="nav-item py-1" v-else>
                <router-link
                  :to="{
                    name: userRole == 'CHEF' ? 'KitchenOrders' : 'BarOrders',
                    params: {},
                  }"
                  class="router-link-active router-link-exact-active text-center fw-bolder menu-text d-flex flex-column"
                  ><h4 class="mb-0 w-100">
                    <i class="demo-pli-home fs-4"></i>
                  </h4>
                  <span class="text-capitalize fw-normal"
                    >Home</span
                  ></router-link
                >
              </li>
              <li class="nav-item py-1" v-for="menu in menus" :key="menu.title">
                <router-link
                  :to="menu.url"
                  class="text-center fw-bolder menu-text d-flex flex-column"
                  :class="{ active: menu.url.name == currentRoute }"
                  ><h4 class="mb-0 w-100">
                    <i :class="`${menu.icon} fs-4`"></i>
                  </h4>

                  <span class="text-capitalize fw-normal">{{
                    menu.title
                  }}</span>
                </router-link>
              </li>
            </template>
          </ul>
        </div>
      </div>
      <div class="mainnav__bottom-content border-top pb-2">
        <ul id="mainnav" class="mainnav__menu nav flex-column">
          <li class="nav-item">
            <a
              class="text-center fw-bolder menu-text d-flex flex-column"
              @click.prevent="signout"
              href="javascript:void(0)"
              aria-expanded="false"
              ><h4 class="mb-0 w-100">
                <i class="demo-pli-unlock fs-4"></i>
              </h4>
              <span class="text-capitalize fw-normal">Logout</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
<script>
import { menus } from "./menus.js";
import Avatar from "@/components/Avatar.vue";
export default {
  name: "NavigationBar",
  components: {
    Avatar,
  },
  data: () => ({
    menus,
  }),
  computed: {
    currentRoute() {
      //return this.$route.fullPath
      return this.$route.name;
    },
  },
  methods: {
    signout() {
      if (this.userRole == "ADMINISTRATOR") this.handleLogout();
      else
        this.$http
          .post("auth/logout", this.$helper.generateFormData({}))
          .then((response) => {
            if (response.data.status) {
              this.$store.dispatch("logout").then(() => {
                window.location.replace("/front-office/");
              });
            }
          });
    },
  },
};
</script>
