<template>
  <div
    class="content__boxed w-100 min-vh-100 d-flex flex-column align-items-center justify-content-center"
  >
    <div class="content__wrap">
      <div class="card shadow-lg">
        <div class="card-body">
          <div class="text-center">
            <img
              :src="`${publicPath}img/logo.png`"
              class="w-50"
              alt="Tame POS"
              style="transform: scale(1.5)"
            />
            <!-- <h1 class="h3">Front Office Login</h1> -->
          </div>
          <form class="mt-4" @submit.prevent="authenticateUser">
            <div class="my-4">
              <input
                type="password"
                class="form-control use-keyboard-input"
                placeholder="Passcode"
                v-model="credentials.password"
                name="password"
                style="
                  -webkit-text-security: circle;
                  padding: 0.75rem 1rem;
                  border-color: #141543;
                  -webkit-text-stroke-width: 0.1em;
                  letter-spacing: 0.2em;
                "
                autofocus
              />
            </div>
            <div id="keypad-container"></div>
            <div class="d-gridy d-none mt-2">
              <wolf-button
                type="submit"
                @clicked="authenticateUser"
                :disabler="!credentials.password"
                activator="SIGNING_REQUEST"
                class="btn btn-primary btn-lg"
                >Sign In</wolf-button
              >
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { Keyboard } from "./keyboard";
export default {
  name: "login",
  data: () => ({
    credentials: {
      password: null,
    },
    invalidLogin: false,
    loginSucceed: false,
    passwordType: "password",
  }),
  created() {
    //this.$store.commit("SET_LOCKED_BLANCH", null);
    this.$store.dispatch("logout");
  },
  mounted() {
    this.$nextTick(() => {
      Keyboard.init(this);
    });
  },
  methods: {
    handleVisibility() {
      if (this.passwordType == "password") this.passwordType = "text";
      else this.passwordType = "password";
    },
    authenticateUser() {
      this.$http
        .post(
          "auth/authenticate",
          this.$helper.generateFormData(this.credentials)
        )
        .then((response) => {
          if (response.data.status) {
            const token = response.data.access_token || response.data.token;
            if (token) {
              localStorage.setItem("token", token);
            }
            /*this.invalidLogin = false;
            this.loginSucceed = true; */
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "success",
              message: "Login Succeed. Redirecting...",
            });
            let redirect = { name: "FrontOfficeHome" };
            const { user } = response.data;
            if (user) {
              const permissions = user?.role?.permissions?.front_office;
              if (permissions) {
                if (
                  permissions?.place_orders?.accessible &&
                  permissions?.waiter_orders?.accessible
                ) {
                  redirect = {
                    name: "OrdersCreator",
                    params: { action: "new" },
                  };
                }
              }
            }
            if (navigator.userAgent.indexOf("Firefox") > 0)
              setTimeout(() => {
                location.replace(this.$router.resolve(redirect).href);
              }, 2);
            else {
              location.replace(this.$router.resolve(redirect).href);
            }
          }
        })
        .catch((err) => {
          if (err.response.status == 401) {
            /*this.invalidLogin = true;
            this.loginSucceed = false; */
            this.$store.commit("SET_FLASH_MESSAGE", {
              type: "danger",
              message: "Invalid Passcode. Try Again",
            });
          }
        });
    },
  },
};
</script>
<style>
@import "https://fonts.googleapis.com/icon?family=Material+Icons";
.keyboard {
  /* position: fixed; */
  left: 0;
  bottom: 0;
  width: 100%;
  padding: 5px 0;
  background: #141543;
  box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
  user-select: none;
  transition: bottom 0.4s;
  border-radius: 0.35rem;
}

.keyboard--hidden {
  bottom: -100%;
}

.keyboard__keys {
  text-align: center;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
}

.keyboard__key {
  height: 40px;
  /* width: 6%;
  max-width: 55px; */
  margin: 3px;
  border-radius: 4px;
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  font-size: 1.05rem;
  outline: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  vertical-align: top;
  padding: 0;
  -webkit-tap-highlight-color: transparent;
  position: relative;
}

.keyboard__key:active {
  background: rgba(255, 255, 255, 0.12);
}

.keyboard__key--wide {
  width: 12%;
}

.keyboard__key--extra-wide {
  width: 36%;
  max-width: 500px;
}

.keyboard__key--activatable::after {
  content: "";
  top: 10px;
  right: 10px;
  position: absolute;
  width: 8px;
  height: 8px;
  background: rgba(0, 0, 0, 0.4);
  border-radius: 50%;
}

.keyboard__key--active::after {
  background: #08ff00;
}

.keyboard__key--dark {
  background: rgba(0, 0, 0, 0.25);
}
</style>
