<template>
  <bootstrap-modal
    @close="$emit('closed')"
    :modalSize="'default'"
    :showFooter="false"
    :is-compact="true"
  >
    <template #head>
      <span>
        {{ newClient.id ? "Update Client" : "Add a new Client" }}
      </span>
    </template>
    <template #body>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">First Name</label>
            <input
              type="text"
              v-model="newClient.first_name"
              name="First Name"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">Last Name</label>
            <input
              type="text"
              v-model="newClient.last_name"
              name="Last Name"
              class="form-control"
            />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" v-model="newClient.phone" class="form-control" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">Email</label>
            <input type="text" v-model="newClient.email" class="form-control" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">TIN number</label>
            <input
              type="number"
              v-model="newClient.tin_number"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">Address</label>
            <input
              type="text"
              v-model="newClient.address"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">Discount(%)</label>
            <input
              type="number"
              v-model="newClient.discount"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-md-6" v-if="$helper.empty(loggedUser.branch)">
          <label for="">Branch</label>
          <zoho-search
            url="branches/search"
            label="name"
            placeholder="Select Branch"
            :load-defaults="true"
            v-model="newClient.branch_id"
            primary-key="id"
            fetch-flag="SEARCHING_FOR_BRANCHES"
          ></zoho-search>
        </div>
      </div>
      <div class="my-1">
        <wolf-button
          class="btn btn-primary"
          activator="SAVING_CLIENT"
          :disabler="!newClient.first_name"
          @clicked="handleSubmit"
        >
          Save and closed
        </wolf-button>
      </div>
    </template>
  </bootstrap-modal>
</template>
<script>
import BootstrapModal from "@/components/BootstrapModal.vue";
import ZohoSearch from "@/components/ZohoSearch.vue";
export default {
  name: "ClientCreator",
  props: ["client"],
  emits: ["saved", "closed"],
  components: {
    BootstrapModal,
    ZohoSearch,
  },
  data: () => ({
    newClient: {
      first_name: null,
      last_name: null,
      email: null,
      phone: null,
      address: null,
      discount: 0,
      tin_number: null,
      receive: 0,
      store: 0,
      status: 1,
      type: "SIGNED",
    },
  }),
  created() {
    if (!this.$helper.empty(this.client)) {
      this.newClient = this.client;
    }
  },
  methods: {
    handleSubmit() {
      let url = this.newClient.id ? "clients/update" : "clients/create";
      this.$http
        .post(url, this.$helper.generateFormData(this.newClient))
        .then((response) => {
          if (response.data.status) {
            this.$emit("saved", response.data.client);
            this.$helper.resetObjectValues(this.newClient);
          }
        });
    },
  },
};
</script>
