import { reactive, ref } from "vue";
const hasModal = ref(false);
const appSettings = reactive({
  today: new Date().toLocaleString("fr-CA").slice(0, 10),
});

const confirm = (options) => {
  const div = document.createElement("div");
  div.setAttribute("id", "alert-container");
  const alert = ` <div
      class="modal fade show"
      id="modal-default"
      tabindex="-1"
      role="dialog"
      style="display: block"
    >
      <div
        class="modal-dialog modal-dialog-scrollable modal-dialog-centered"
        role="document"
      >
        <div class="modal-content" style="border-color: transparent">
          <div class="modal-header bg-primary">
            <h5 class="modal-title text-white">
              ${options.title}
            </h5>
            <a
              href="javascript:void(0)"
              class="close text-white fs-20"
            >
                <svg height="30px" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
                    <path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                </svg>
              </a>
          </div>

          <div class="modal-body text-center h6 mb-0">
          <lord-icon
              src="${options.icon}"
              trigger="loop"
              delay="500"
              colors="primary:#c71f16"
              style="width:100px;height:100px;">
          </lord-icon>
          <br>
              ${options.content}
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="mr-auto right btn-label ${options.classes}"
              id="action-btn"
            >
            <i
                class="ri-checkbox-circle-line label-icon align-middle fs-20 ms-2"
              ></i
              >
              <span id="action-text">${options.actionText}</span>
              <p id="alert-loading"><span></span><span></span><span></span></p>
            </button>
            <button
              type="button"
              class="btn btn-light ms-auto"
              id="close-btn"
            >
            <span>${options.closeText}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>`;
  div.innerHTML = alert;
  div.querySelector(".close").addEventListener("click", () => {
    document.querySelector("#alert-container").remove();
  });
  div.querySelector("#close-btn").addEventListener("click", () => {
    document.querySelector("#alert-container").remove();
  });
  div.querySelector("#action-btn").addEventListener("click", () => {
    hasModal.value = true;
    div.querySelector("#action-text").remove();
    div.querySelector("#alert-loading").classList.add("d-block");
    options.action();
  });
  document.body.prepend(div);
};

const triageEnabled = () => {
  return appSettings.triage_enabled && appSettings.triage_enabled == "1";
};

export const menus = [
  {
    title: "dashboard",
    icon: "ri-dashboard-2-line",
    url: { name: "Dashboard" },
    notifications: [],
  },
  {
    title: "receiption",
    icon: "ri-user-4-line",
    notifications: [],
    submenus: {
      patients: { path: { name: "PatientsList" } },
      triage_requests: {
        path: { name: "TriagePatients" },
        enabled: triageEnabled,
      },
      consultations: { path: { name: "Consultations" } },
    },
  },
  {
    title: "caisse",
    icon: "ri-hand-coin-line",
    notifications: [],
    submenus: {
      unpaid_bills: { path: { name: "UnpaidConsultations" } },
      Invoices: { path: { name: "OutPatientBills" } },
    },
  },
  {
    title: "settings",
    icon: "ri-settings-2-line",
    notifications: [],
    submenus: {
      general: { path: { name: "GeneralSettings" } },
      shared: { path: { name: "SharedSettings" } },
      prestations: { path: { name: "PrestationsList" } },
      users: { path: { name: "UsersList" } },
      rooms_management: { path: { name: "RoomsController" } },
    },
  },
  {
    title: "reports",
    icon: "mdi mdi-file-document-multiple-outline",
    url: { name: "Dashboard" },
    notifications: [],
  },
  {
    title: "appointments",
    icon: "ri-calendar-check-line",
    url: { name: "Dashboard" },
    notifications: [],
  },
];

export const SPECIAL_PERMISSIONS = ['EDIT_PATIENT_INSURANCE', 'ADD_CUSTOM_DISCOUNTING'];

export function useLayout() {
  return { appSettings, confirm, hasModal, triageEnabled };
}
