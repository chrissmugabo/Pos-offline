import { createRouter, createWebHistory } from "vue-router";
import store from "@/store";
const routes = [
  {
    path: "/:pathMatch(.*)*",
    name: "error404",
    component: () =>
      import(/* webpackChunkName: "mainChuck" */ "../views/404.vue"),
    meta: {
      title: "Page Not Found",
    },
  },
  {
    path: "/",
    name: "FrontOfficeLogin",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../views/Login.vue"
      ),
    meta: {
      title: "Login",
    },
  },
  {
    path: "/home",
    name: "FrontOfficeHome",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../views/IndexView.vue"
      ),
    meta: {
      title: "Front Office Home",
    },
  },
  {
    path: "/orders",
    name: "WaiterOrders",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../views/WaiterOrders.vue"
      ),
    meta: {
      title: "Waiter Orders",
      requireFrontOfficeAuth: true,
    },
  },
  {
    path: "/orders/:action/:reference?",
    name: "OrdersCreator",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../views/OrderCreator.vue"
      ),
    meta: {
      title: "Order Creator",
      requireFrontOfficeAuth: true,
    },
  },
  {
    path: "/kitchen/orders",
    name: "KitchenOrders",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../views/DestinationOrders.vue"
      ),
    meta: {
      title: "Kitchen Orders",
      requireFrontOfficeAuth: true,
    },
  },
  {
    path: "/bar/orders",
    name: "BarOrders",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../views/DestinationOrders.vue"
      ),
    meta: {
      title: "Bar Orders",
      requireFrontOfficeAuth: true,
    },
  },
  {
    path: "/reports/sales",
    name: "FrontOfficeSalesReport",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../views/SalesReport.vue"
      ),
    meta: {
      title: "Sales Report",
      requiresAdminAuth: true,
    },
  },
  {
    path: "/orders",
    name: "FrontOrders",
    component: () =>
      import(
        /* webpackChunkName: "mainChuck" */ "../shared/Orders.vue"
      ),
    meta: {
      title: "Orders",
      requireFrontOfficeAuth: true,
    },
  },
  {
    path: "/settings/eod",
    name: "EOD_Handler",
    component: () =>
      import(/* webpackChunkName: "mainChuck" */ "../views/Orders.vue"),
    meta: {
      title: "End Of Day Setttings",
      requireFrontOfficeAuth: true,
    },
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { x: 0, y: 0 };
  },
});
const pathname = process.env.NODE_ENV === "production" ? "/pos/" : "/";
router.beforeEach((to, from, next) => {
  if (to.matched.some((record) => record.meta.requiresAdminAuth)) {
    if (store.getters.isLoggedIn) {
      next();
      return;
    }
    window.location.replace(pathname);
  } else if (to.matched.some((record) => record.meta.requireFrontOfficeAuth)) {
    if (store.getters.isLoggedIn) {
      next();
      return;
    }
    window.location.replace(pathname + "front-office/");
  } else {
    next();
  }
});

const DEFAULT_TITLE = "TAME POS";
router.afterEach((to) => {
  document.title = to.meta.title + " | " + DEFAULT_TITLE || DEFAULT_TITLE;
  store.state.navBarOpen = false;
  [...document.querySelectorAll("div.dropdown-menu")].forEach((elt) => {
    elt.classList.remove("show");
  });
});

export default router;
