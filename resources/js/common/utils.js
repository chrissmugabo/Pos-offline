export const encodeQuery = (url, data) => {
  let query = "";
  for (let d in data) {
    if (data[d] && url.indexOf(`?${d}`) < 0 && url.indexOf(`&${d}`) < 0)
      query += encodeURIComponent(d) + "=" + encodeURIComponent(data[d]) + "&";
  }
  return url.indexOf("?") > -1
    ? `${url}&${query.slice(0, -1)}`
    : `${url}?${query.slice(0, -1)}`;
};

export const CASHIER_EXTRAS_PERMISSIONS = ["Process Payment", "Credit Post"];

export const PLACE_ORDER_EXTRAS = ["Change Price"];

export const colours = [
  "#1abc9c",
  "#2ecc71",
  "#3498db",
  "#9b59b6",
  "#34495e",
  "#16a085",
  "#27ae60",
  "#2980b9",
  "#8e44ad",
  "#2c3e50",
  "#f1c40f",
  "#e67e22",
  "#e74c3c",
  "#95a5a6",
  "#f39c12",
  "#d35400",
  "#c0392b",
  "#bdc3c7",
  "#7f8c8d",
];

export const baseURL =
  process.env.NODE_ENV == "production" ? "/api/" : "http://127.0.0.1:8000/";

export const getUploadedImage = (url) => {
  return process.env.NODE_ENV == "production"
    ? `${baseURL}uploads/${url}`
    : `${baseURL}uploads/${url}`;
};

export const isObject = (item) => {
  return item && typeof item === "object" && !Array.isArray(item);
};