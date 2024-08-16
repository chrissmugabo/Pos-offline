<script>
import { permissionsHandler } from "@/common/permissions-handler";
export default {
  name: "IndexView",
  mixins: [permissionsHandler],
  methods: {
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
  <div class="nk-block">
    <div class="d-flex align-items-center justify-content-between">
      <div class="w-100 d-flex justify-content-center" style="min-height: 80vh">
        <div class="ant-empty m-auto">
          <div class="ant-empty-image" v-if="!$helper.empty(permissions)">
            <svg
              class="ant-empty-img-default"
              width="184"
              height="152"
              viewBox="0 0 184 152"
            >
              <g fill="none" fill-rule="evenodd">
                <g transform="translate(24 31.67)">
                  <ellipse
                    class="ant-empty-img-default-ellipse"
                    cx="67.797"
                    cy="106.89"
                    rx="67.797"
                    ry="12.668"
                  ></ellipse>
                  <path
                    class="ant-empty-img-default-path-1"
                    d="M122.034 69.674L98.109 40.229c-1.148-1.386-2.826-2.225-4.593-2.225h-51.44c-1.766 0-3.444.839-4.592 2.225L13.56 69.674v15.383h108.475V69.674z"
                  ></path>
                  <path
                    class="ant-empty-img-default-path-2"
                    d="M101.537 86.214L80.63 61.102c-1.001-1.207-2.507-1.867-4.048-1.867H31.724c-1.54 0-3.047.66-4.048 1.867L6.769 86.214v13.792h94.768V86.214z"
                    transform="translate(13.56)"
                  ></path>
                  <path
                    class="ant-empty-img-default-path-3"
                    d="M33.83 0h67.933a4 4 0 0 1 4 4v93.344a4 4 0 0 1-4 4H33.83a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z"
                  ></path>
                  <path
                    class="ant-empty-img-default-path-4"
                    d="M42.678 9.953h50.237a2 2 0 0 1 2 2V36.91a2 2 0 0 1-2 2H42.678a2 2 0 0 1-2-2V11.953a2 2 0 0 1 2-2zM42.94 49.767h49.713a2.262 2.262 0 1 1 0 4.524H42.94a2.262 2.262 0 0 1 0-4.524zM42.94 61.53h49.713a2.262 2.262 0 1 1 0 4.525H42.94a2.262 2.262 0 0 1 0-4.525zM121.813 105.032c-.775 3.071-3.497 5.36-6.735 5.36H20.515c-3.238 0-5.96-2.29-6.734-5.36a7.309 7.309 0 0 1-.222-1.79V69.675h26.318c2.907 0 5.25 2.448 5.25 5.42v.04c0 2.971 2.37 5.37 5.277 5.37h34.785c2.907 0 5.277-2.421 5.277-5.393V75.1c0-2.972 2.343-5.426 5.25-5.426h26.318v33.569c0 .617-.077 1.216-.221 1.789z"
                  ></path>
                </g>
                <path
                  class="ant-empty-img-default-path-5"
                  d="M149.121 33.292l-6.83 2.65a1 1 0 0 1-1.317-1.23l1.937-6.207c-2.589-2.944-4.109-6.534-4.109-10.408C138.802 8.102 148.92 0 161.402 0 173.881 0 184 8.102 184 18.097c0 9.995-10.118 18.097-22.599 18.097-4.528 0-8.744-1.066-12.28-2.902z"
                ></path>
                <g
                  class="ant-empty-img-default-g"
                  transform="translate(149.65 15.383)"
                >
                  <ellipse
                    cx="20.654"
                    cy="3.167"
                    rx="2.849"
                    ry="2.815"
                  ></ellipse>
                  <path
                    d="M5.698 5.63H0L2.898.704zM9.259.704h4.985V5.63H9.259z"
                  ></path>
                </g>
              </g>
            </svg>
            <p class="ant-empty-description h5 mt-2">
              Welcome, <a href="javascript:void(0)">{{ loggedUser.name }}</a
              >! Use the left panel to started working.
            </p>
          </div>
          <div class="" v-else>
            <svg
              class="ant-empty-img-default"
              width="184"
              height="152"
              viewBox="0 0 16 16"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M5.443 1.991a60.17 60.17 0 0 0-2.725.802.454.454 0 0 0-.315.366C1.87 7.056 3.1 9.9 4.567 11.773c.736.94 1.533 1.636 2.197 2.093.333.228.626.394.857.5.116.053.21.089.282.11A.73.73 0 0 0 8 14.5c.007-.001.038-.005.097-.023.072-.022.166-.058.282-.111.23-.106.525-.272.857-.5a10.197 10.197 0 0 0 2.197-2.093C12.9 9.9 14.13 7.056 13.597 3.159a.454.454 0 0 0-.315-.366c-.626-.2-1.682-.526-2.725-.802C9.491 1.71 8.51 1.5 8 1.5c-.51 0-1.49.21-2.557.491zm-.256-.966C6.23.749 7.337.5 8 .5c.662 0 1.77.249 2.813.525a61.09 61.09 0 0 1 2.772.815c.528.168.926.623 1.003 1.184.573 4.197-.756 7.307-2.367 9.365a11.191 11.191 0 0 1-2.418 2.3 6.942 6.942 0 0 1-1.007.586c-.27.124-.558.225-.796.225s-.526-.101-.796-.225a6.908 6.908 0 0 1-1.007-.586 11.192 11.192 0 0 1-2.417-2.3C2.167 10.331.839 7.221 1.412 3.024A1.454 1.454 0 0 1 2.415 1.84a61.11 61.11 0 0 1 2.772-.815z"
              ></path>
              <path
                d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z"
              ></path>
            </svg>
            <p class="ant-empty-description h6 my-2">
              Welcome,
              <a href="javascript:void(0)">{{ loggedUser.name }}</a> <br />
              <span class="text-warning"
                >You do not have permissions to access this system</span
              >
            </p>
            <button class="btn btn-danger" type="button" @click="signout">
              Sign out
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.ant-empty {
  margin: 0 8px;
  font-size: 14px;
  line-height: 1.5715;
  text-align: center;
}
.ant-empty-image {
  height: 100px;
  margin-bottom: 8px;
}
.ant-empty-image svg {
  height: 100%;
  margin: auto;
}
.ant-empty-img-default-ellipse {
  fill: #f5f5f5;
  fill-opacity: 0.8;
}
.ant-empty-img-default-path-1 {
  fill: #aeb8c2;
}
.ant-empty-img-default-path-3 {
  fill: #f5f5f7;
}
.ant-empty-img-default-path-4,
.ant-empty-img-default-path-5 {
  fill: #dce0e6;
}
.ant-empty-img-default-g {
  fill: #fff;
}
</style>
