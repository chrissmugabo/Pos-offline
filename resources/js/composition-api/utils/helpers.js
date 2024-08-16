export const generateFormData = (obj) => {
  const fd = new FormData();
  for (const key in obj) {
    if (
      obj[key] !== null &&
      obj[key] !== false &&
      typeof obj[key] !== "undefined"
    ) {
      if (typeof obj[key] === "object")
        fd.append(key, JSON.stringify(obj[key]));
      else fd.append(key, obj[key]);
    }
  }
  return fd;
};

export const baseURL =
  process.env.NODE_ENV == "production"
    ? "https://localhost:8000/"
    : "http://localhost:8000/";

export const serverUrl =
  process.env.NODE_ENV == "production"
    ? "https://localhost:8000/"
    : "http://localhost:8000/";

export const getImageFromServer = (img) => {
  return `${serverUrl}uploads/${img}`;
};

export const formatNumber = (num) => {
  if (num.toString().indexOf(".") > -1) num = Number(num).toFixed(3);
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};
export const empty = (mixedVar) => {
  let undef, key, i, len;
  const emptyValues = [undef, null, false, 0, "", "0"];
  for (i = 0, len = emptyValues.length; i < len; i++) {
    if (mixedVar === emptyValues[i]) {
      return true;
    }
  }
  if (typeof mixedVar === "object") {
    for (key in mixedVar) {
      if (Object.prototype.hasOwnProperty.call(mixedVar, key)) {
        return false;
      }
    }
    return true;
  }
  return false;
};
export const encodeQuery = (url, data) => {
  let query = "";
  for (let d in data) {
    if (!empty(data[d]) && url.indexOf(`?${d}`) < 0 && url.indexOf(`&${d}`) < 0)
      query += encodeURIComponent(d) + "=" + encodeURIComponent(data[d]) + "&";
  }
  return url.indexOf("?") > -1
    ? `${url}&${query.slice(0, -1)}`
    : `${url}?${query.slice(0, -1)}`;
};

export const padNumber = (num, targetedLength = 3) => {
  const strNumber = num.toString();
  if (strNumber.length < targetedLength) {
    const padding = new Array(targetedLength - strNumber.length + 1).join("0");
    return padding + strNumber;
  }
};

export const titleFromSlug = (text) => {
  return text.split("_").join(" ");
};

export const slugFromTitle = (text) => {
  return text.toLowerCase().split(" ").join("_");
};
export const getFileIcon = (filename) => {
  if (!filename) {
    return "/img/files/file.svg";
  }
  const extension = filename.split(".")[1];
  return `/img/files/${extension}.svg`;
};

export const isObject = (item) => {
  return item && typeof item === "object" && !Array.isArray(item);
};

export const mergeDeep = (target, ...sources) => {
  if (!sources.length) return target;
  const source = sources.shift();
  if (isObject(target) && isObject(source)) {
    for (const key in source) {
      if (isObject(source[key])) {
        if (!target[key]) Object.assign(target, { [key]: {} });
        mergeDeep(target[key], source[key]);
      } else {
        Object.assign(target, { [key]: source[key] });
      }
    }
  }
  return mergeDeep(target, ...sources);
};
