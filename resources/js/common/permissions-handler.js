import { CASHIER_EXTRAS_PERMISSIONS, PLACE_ORDER_EXTRAS } from "@/common/utils";
export const permissionsHandler = {
  computed: {
    loggedUser() {
      return this.$store.state.user || {};
    },
    isReady() {
      return this.loggedUser && Object.keys(this.loggedUser).length > 0;
    },
    permissions() {
      return (
        this.isReady &&
        (this.loggedUser.role.pos_permissions ||
          this.loggedUser.role.permissions)
      );
    },
    routePathString() {
      return this.$store.state.routes[this.$route.name] || "";
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
    isCashierOnly() {
      const userPermissions = this.permissions && this.permissions.front_office;
      if (userPermissions) {
        const obj = userPermissions?.waiter_orders?.access?.others;
        if (obj) {
          return CASHIER_EXTRAS_PERMISSIONS.every((x) => {
            return obj[x] === true;
          });
        }
      }
      return false;
    },
    canChangePrice() {
      if (this.userRole == "ADMINISTRATOR" || !this.loggedUser.role_id) {
        return true;
      }
      const userPermissions = this.permissions && this.permissions.front_office;
      if (userPermissions) {
        const obj = userPermissions.place_orders.access.others;
        return PLACE_ORDER_EXTRAS.every((x) => {
          return obj[x] === true;
        });
      }
      return false;
    },
  },
  methods: {
    isWaiter(user) {
      const permissions = user?.role?.permissions?.front_office;
      if (permissions) {
        return (
          permissions?.place_orders?.accessible &&
          permissions?.waiter_orders?.accessible
        );
      }
      return false;
    },
    hasPermissionTo(permission, str = null) {
      /**
       * permission ==> CRUD
       */
      if (!this.loggedUser.role_id || this.loggedUser.role_id == 8) {
        return true;
      }
      const group =
        location.pathname.indexOf("/front-office/") != -1
          ? "front_office"
          : "back_office";
      if (group == "front_office") {
        const access = this.routePathString
          .split(".")
          .reduce((a, b) => (a && a[b]) || null, this.permissions[group]);
        const priviledges = access?.access?.priviledges;
        return priviledges && priviledges.includes(permission);
      } else {
        const permissions = this.permissions[group];
        let access = {};
        if (str) {
          access = str
            .split(".")
            .reduce((a, b) => (a && a[b]) || null, permissions);
        } else {
          access = this.routePathString
            .split(".")
            .reduce((a, b) => (a && a[b]) || null, permissions);
        }
        const priviledges = access?.access || access;
        return priviledges && priviledges.includes(permission);
      }
    },
  },
};
