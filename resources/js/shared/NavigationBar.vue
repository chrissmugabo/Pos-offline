<template>
  <nav id="mainnav-container" class="mainnav">
    <div class="mainnav__inner rounded-top">
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
            <template v-for="menu in menus" :key="menu.title">
              <li class="nav-item py-2" v-if="canAccess(menu.title)">
                <router-link
                  :to="menu.url"
                  class="text-center fw-bolder menu-text d-flex flex-column"
                  :class="{ active: menu.url.name == currentRoute }"
                  ><h4 class="mb-0 w-100">
                    <span v-html="menu.icon"></span>
                  </h4>

                  <span class="text-capitalize fw-normal fw-bold">{{
                    menu.alias ?? ""
                  }}</span>
                </router-link>
              </li>
            </template>
          </ul>
        </div>
      </div>
      <div class="mainnav__bottom-content border-top pb-2">
        <ul id="mainnav" class="mainnav__menu nav flex-column">
          <li class="nav-item py-2 border-bottom" v-if="!isCashier">
            <a
              class="text-center fw-bolder menu-text d-flex flex-column"
              @click.prevent="handleShiftClosing"
              href="javascript:void(0)"
              aria-expanded="false"
              ><h6 class="mb-0 w-100">
                <svg
                  height="24px"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="text-info"
                >
                  <circle cx="12" cy="12" r="10"></circle>
                  <rect x="9" y="9" width="6" height="6"></rect>
                </svg>
              </h6>
              <span class="text-capitalize fw-bold" style="font-size: 13px"
                >End Shift</span
              >
            </a>
          </li>
          <li class="nav-item">
            <a
              class="text-center fw-bolder menu-text d-flex flex-column"
              @click.prevent="signout"
              href="javascript:void(0)"
              aria-expanded="false"
              ><h4 class="mb-0 w-100">
                <i class="demo-pli-unlock fs-4 fw-bold text-danger"></i>
              </h4>
              <span class="text-capitalize fw-bold">Logout</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
<script>
import { CASHIER_EXTRAS_PERMISSIONS } from "@/common/utils";
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
    user() {
      return this.$store.state.user || {};
    },
    isReady() {
      return this.user && Object.keys(this.user).length > 0;
    },
    permissions() {
      return (
        this.isReady &&
        (this.user?.role?.pos_permissions || this.user?.role?.permissions)
      );
    },
    isCashier() {
      if (this.userRole == "ADMINISTRATOR" || !this.loggedUser.role_id) {
        return true;
      }
      const userPermissions = this.permissions && this.permissions.front_office;
      if (userPermissions) {
        const obj = userPermissions.waiter_orders.access.others;
        return CASHIER_EXTRAS_PERMISSIONS.every((x) => {
          return obj[x] === true;
        });
      }
      return false;
    },
  },
  mounted() {
    const routes = {};
    menus.forEach((menu) => {
      if (!menu.submenus) {
        routes[menu.url.name] = menu.title;
      } else {
        for (let item in menu.submenus) {
          routes[menu.submenus[item].name] = `${menu.title}.${item}`;
        }
      }
    });
    this.$store.state.routes = routes;
  },
  methods: {
    handleShiftClosing() {
      this.$confirm({
        title: "Ending Shift",
        content: `Are you sure you to end your shift?`,
        classes: "btn btn-danger",
        actionText: "Confirm",
        closeText: "Cancel",
        action: () => {
          this.$http.get(`auth/close-shift`).then((response) => {
            const { status } = response.data;
            if (status) {
              const user = this.loggedUser;
              user.working = false;
              this.$store.commit("SET_CURRENT_USER", user);
              this.$notify({
                type: "success",
                message: "Shift ended successfully",
              });
              this.signout();
            }
          });
        },
      });
    },
    canAccess(menu, submenu = null) {
      if (!this.user.role_id || this.userRole == "ADMINISTRATOR") {
        return true;
      } else {
        if (!submenu) {
          return (
            this.permissions.front_office[menu] &&
            this.permissions.front_office[menu].accessible == true
          );
        } else {
          return (
            this.permissions.front_office[menu] &&
            this.permissions.front_office[menu][submenu] &&
            this.permissions.front_office[menu][submenu].includes("R")
          );
        }
      }
    },
    signout() {
      if (this.userRole == "ADMINISTRATOR" || this.userRole == null)
        this.handleLogout();
      else
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
  },
};
</script>
<style scoped>
@media (min-width: 1200px) {
  .mainnav__bottom-content,
  .mainnav__top-content {
    padding-inline: Max(0.3rem, 0.25rem);
  }
}
</style>
