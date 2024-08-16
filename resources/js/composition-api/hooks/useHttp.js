import { reactive, ref } from "vue";
import axios from "axios";
import { baseURL, encodeQuery } from "@/common/utils";
import { toggleFlashMessage } from "@/composition-api/hooks/useToast";
import { useRoute } from "vue-router";
import { useLayout } from "./useLayout";

const router = useRoute();
const { hasModal } = useLayout();
const pendingRequests = reactive({});
const onGoingRequestFlag = ref(null);
const requestPercentage = ref(0);
const requireProgress = ref(false);

export function useHttp(prefix = "pos/") {
  const apiUrl =
    process.env.NODE_ENV == "production"
      ? `${baseURL}${prefix}`
      : `${baseURL}api/${prefix}`;
  const authToken = localStorage.getItem("token");
  const http = axios.create({
    baseURL: apiUrl,
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
      ...(authToken ? { Authorization: `Bearer ${authToken}` } : {}),
    },
  });
  if (requireProgress.value) {
    // Set onDownloadProgress globally
    http.defaults.onDownloadProgress = (progressEvent) => {
      if (progressEvent.total) {
        //const percentage = (progressEvent.loaded / progressEvent?.total || 1) * 100;
        const percentage = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
        requestPercentage.value = percentage;
        if (percentage === 100) {
          setTimeout(() => {
            requestPercentage.value = 0;
          }, 400);
        }
      }
    };

    // Set onUploadProgress globally
    http.defaults.onUploadProgress = (progressEvent) => {
      if (progressEvent.total) {
        //const percentage = (progressEvent.loaded / progressEvent?.total || 1) * 100;
        const percentage = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
        requestPercentage.value = percentage;
        if (percentage === 100) {
          setTimeout(() => {
            requestPercentage.value = 0;
          }, 400);
        }
      }
    };
  }

  const resolveRequest = (url) => {
    for (let key in pendingRequests) {
      if (pendingRequests[key] === url) {
        delete pendingRequests[key];
        break;
      }
    }
    requireProgress.value = false;
    if (hasModal.value) {
      const el = document.querySelector("#alert-container");
      if (el) {
        el.remove();
      }
    }
  };

  http.interceptors.request.use(
    async (config) => {
      const branch = localStorage.getItem("__branch");
      if (branch) {
        config.url = encodeQuery(config.url, {
          current_branch: JSON.parse(branch).id,
        });
      }
      if (onGoingRequestFlag.value) {
        pendingRequests[onGoingRequestFlag.value] = config.url;
        onGoingRequestFlag.value = null;
      }
      return config;
    },
    (error) => {
      resolveRequest(error.config.url);
      return Promise.reject(error);
    }
  );

  http.interceptors.response.use(
    (response) => {
      resolveRequest(response.config.url);
      return response;
    },
    async (error) => {
      resolveRequest(error.config.url);
      const { status } = error.response;
      if (status === 401 || status === 419) {
        localStorage.clear();
        if (location.pathname !== "/") {
          try {
            await axios.post(`${baseURL}logout`);
            await router.push({ name: "Login" });
          } catch (error) {
            window.location.pathname = "/";
          }
        }
      }
      if (error.response.status == 500) {
        toggleFlashMessage({
          type: "danger",
          text: "Error. Contact system administrator",
        });
      }
      return Promise.reject(error);
    }
  );

  const isProcessing = (task) => Object.keys(pendingRequests).includes(task);
  return {
    http,
    onGoingRequestFlag,
    isProcessing,
    requestPercentage,
    requireProgress,
  };
}
