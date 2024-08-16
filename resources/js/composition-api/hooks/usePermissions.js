import { ref } from "vue";
import { useRoute } from "vue-router";
import { useAuth } from "@/composition-api/hooks/useAuth";
const routes = ref({});

export function usePermissions() {
  const hasPermissionTo = (permission) => {
    const route = useRoute();
    const { loggedUser } = useAuth();
    if (route && loggedUser.value) {
      const user = loggedUser.value;
      const routePathString = routes[route.name] || "";
      const permissions = user?.role?.permissions;
      if (!user?.role_id) {
        return true;
      }
      const access = routePathString
        .split(".")
        .reduce((a, b) => (a && a[b]) || null, permissions);
      if (access) {
        if (Array.isArray(access)) return access.includes(permission);
        else return access.access.includes(permission);
      }
      return access && access.includes(permission);
    }
    return false;
  };

  const hasSpecialPermission = (permission) => {
    const { loggedUser } = useAuth();
    if (loggedUser.value) {
      const user = loggedUser.value;
      if (!user?.role_id) {
        return true;
      }
      const permissions = user?.role?.permissions;
      return (
        permissions["specials"] && permissions["specials"].includes(permission)
      );
    }
    return false;
  };

  const canAccess = (menu, submenu = null) => {
    const { loggedUser } = useAuth();
    if (loggedUser.value) {
      const user = loggedUser.value;
      if (!user?.role_id) {
        return true;
      }
      const permissions = user?.role?.permissions;
      if (permissions) {
        if (!submenu) {
          const result =
            permissions[menu] && permissions[menu].accessible == true;
          return result;
        } else {
          const result =
            permissions[menu][submenu] &&
            permissions[menu][submenu].includes("R");
          return result;
        }
      }
    }
    return false;
  };

  const isMenuEnabled = (item) => {
    if ("enabled" in item) {
      return item.enabled();
    }
    return true;
  };

  return {
    routes,
    hasPermissionTo,
    canAccess,
    isMenuEnabled,
    hasSpecialPermission,
  };
}
