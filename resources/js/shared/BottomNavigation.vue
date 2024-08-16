<script>
import { CASHIER_EXTRAS_PERMISSIONS } from "@/common/utils";
import { menus } from "./menus.js";
export default {
  name: "BottomNavigation",
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
    openCart() {
      if (!this.$store.getters.orderedItemsLen) {
        return;
      }
      document.querySelector("#root").classList.toggle("sb--show");
    },
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
<template>
  <div class="menubar-area footer-fixed style-9" id="bottom-nav">
    <div class="toolbar-inner menubar-nav">
      <template v-for="menu in menus" :key="menu.title">
        <router-link
          v-if="canAccess(menu.title)"
          :to="menu.url"
          :class="{ active: menu.url.name == currentRoute }"
          class="nav-link"
        >
          <span v-html="menu.icon"></span>

          <span>{{ menu.alias ?? "" }}</span>
        </router-link>
      </template>
      <a href="javascript:void(0)" class="icon-box" @click.prevent="openCart()">
        <svg
          height="18px"
          viewBox="0 0 16 16"
          xmlns="http://www.w3.org/2000/svg"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M8 1a2.5 2.5 0 0 0-2.5 2.5V4h5v-.5A2.5 2.5 0 0 0 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V5H2z"
          ></path>
        </svg>
        <span
          style="
            width: 15px;
            height: 15px;
            color: #fff;
            background-color: #17181c !important;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 11px;
            position: absolute;
            top: 14px;
            right: 7px;
          "
          >{{ $store.getters.orderedItemsLen }}</span
        >
      </a>
    </div>
  </div>
</template>
<style scoped>
.menubar-area {
  border-top: 1px solid var(--border-color);
  background-color: rgba(255, 255, 255, 0.72);
  backdrop-filter: blur(10px);
  padding: 5px 0;
  z-index: 999;
}
.menubar-area.footer-fixed {
  position: fixed !important;
  bottom: 0;
  left: 0;
  width: 100%;
}
.menubar-area .toolbar-inner {
  padding-left: 0;
  padding-right: 0;
  display: flex;
  max-width: 1000px;
  margin-right: auto;
  margin-left: auto;
  align-items: center;
  justify-content: space-between;
}
.menubar-area .toolbar-inner .nav-link {
  color: #2f2f2f;
  text-align: center;
  width: 25%;
  padding: 0 0;
  font-size: 18px;
}
.menubar-area .toolbar-inner .nav-link svg {
  width: 24px;
  height: 24px;
}
.menubar-area .toolbar-inner .nav-link svg path {
  fill: #7d8fab;
}
.menubar-area .toolbar-inner .nav-link.active svg path {
  fill: var(--bs-primary);
}
.menubar-area.style-9 {
  border-top: 0;
  padding: 0;
  position: unset;
  background-color: #fff !important;
  border-radius: 15px 15px 0 0;
  box-shadow: 0px -10px 30px 0px rgba(0, 0, 0, 0.1);
  height: 55px;
}
.menubar-area.style-9 .menubar-nav .nav-link {
  padding: 9px 12px;
  position: relative;
  z-index: 1;
}
.menubar-area.style-9 .menubar-nav .nav-link:after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 54px;
  display: none;
  background-color: #141543;
  z-index: -1;
  border-radius: 10px 10px 0 0;
}
.menubar-area.style-9 .menubar-nav .nav-link span {
  font-size: 14px;
  display: block;
  color: #555;
}
.menubar-area.style-9 .menubar-nav .nav-link svg path {
  fill: unset;
}
.menubar-area.style-9 .menubar-nav .nav-link.active {
  background-color: none;
}
.menubar-area.style-9 .menubar-nav .nav-link.active svg path {
  stroke: #fff;
}
.menubar-area.style-9 .menubar-nav .nav-link.active span {
  color: #fff;
  font-weight: 600;
}
.menubar-area.style-9 .menubar-nav .nav-link.active:after {
  display: block;
}
@media (min-width: 576px) {
  #bottom-nav {
    display: none !important;
  }
}
.icon-box {
  position: relative;
  margin-right: 0.5rem;
}
.icon-box svg {
  width: 34px;
  height: 34px;
}
</style>
