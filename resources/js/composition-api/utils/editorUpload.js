import { baseURL } from "./helpers";

const token = localStorage.getItem("token");
export const uploadImage = {
  server: `${baseURL}`,
  timeout: 5 * 1000, // 5s

  fieldName: "custom-fileName",
  meta: { token: `Bear ${token}`, a: 100 },
  metaWithUrl: true, // join params to url
  headers: {
    Accept: "application/json",
    "Content-Type": "multipart/form-data",
    Authorization: `Bearer ${token}`,
  },

  maxFileSize: 10 * 1024 * 1024, // 10M

  base64LimitSize: 5 * 1024,

  onBeforeUpload(file) {
    console.log("onBeforeUpload", file);

    return file; // will upload this file
    // return false // prevent upload
  },
  onProgress(progress) {
    console.log("onProgress", progress);
  },
  onSuccess(file, res) {
    console.log("onSuccess", file, res);
  },
  onFailed(file, res) {
    alert(res.message);
    console.log("onFailed", file, res);
  },
  onError(file, err, res) {
    alert(err.message);
    console.error("onError", file, err, res);
  },
};
