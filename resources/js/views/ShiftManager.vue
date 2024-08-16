<script>
export default {
  name: "ShiftManager",
  data: () => ({
    today: window.TODAY,
    now: () => {
      const currentTimeInMilliseconds = Date.now();
      const date = new Date(currentTimeInMilliseconds);
      const hours = date.getHours();
      const minutes = date.getMinutes();
      return `${hours}:${minutes}`;
    },
  }),
  methods: {
    handleShiftStart() {
      this.$confirm({
        title: "Starting Shift",
        content: `Are you sure you start your shift?`,
        classes: "btn btn-primary",
        actionText: "Confirm",
        closeText: "Cancel",
        action: () => {
          this.$http.get(`auth/start-shift`).then((response) => {
            const { status } = response.data;
            if (status) {
              const user = this.loggedUser;
              user.working = true;
              this.$store.commit("SET_CURRENT_USER", user);
              this.$notify({
                type: "success",
                message: "Shift started successfully",
              });
            }
          });
        },
      });
    },
  },
};
</script>
<template>
  <div
    class="modal fade show"
    id="shift_manager_modal"
    role="dialog"
    tabindex="-1"
    aria-modal="true"
    style="padding-right: 15px; display: block"
  >
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <template v-if="!loggedUser?.working">
            <div
              class="d-flex border shadow p-2 align-items-center rounded border-start-yellow"
            >
              <span class="me-2">
                <svg
                  height="48px"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  ></path>
                </svg>
              </span>
              <div class="">
                <h4>
                  You can not start working until yo start your shift. Please
                  start your shift.
                </h4>
              </div>
            </div>
            <hr />
            <div class="row mb-2 align-items-center">
              <label class="col-lg-3">Work Day</label>
              <div class="col-lg-6">
                <input
                  type="text"
                  class="form-control"
                  readonly
                  :value="today"
                />
              </div>
            </div>
            <div class="row mb-2 align-items-center">
              <label class="col-lg-3">Start Time</label>
              <div class="col-lg-6">
                <input
                  type="text"
                  class="form-control"
                  :value="now()"
                  readonly
                />
              </div>
            </div>
            <div class="row mb-2 align-items-center">
              <label class="col-lg-3"></label>
              <div class="col-lg-6">
                <wolf-button
                  class="btn btn-primary btn-lg"
                  activator="STARTING_SHIFT"
                  @clicked="handleShiftStart"
                >
                  Start Your Shift
                </wolf-button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade show"></div>
</template>
