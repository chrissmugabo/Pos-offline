
import axios, { CancelToken } from "axios";

const pendingRequests = {};

const makeCancellable = (headers, requestId) => {
  if (!requestId) {
    return headers;
  }

  if (pendingRequests[requestId]) {
    // cancel an existing request
    pendingRequests[requestId].cancel();
  }
  const source = CancelToken.source();
  const newHeaders = {
    ...headers,
    cancelToken: source.token,
  };
  pendingRequests[requestId] = source;
  return newHeaders;
};
const generateId = () => {
  // generate a random string of 8 characters
  return Math.random().toString(36).substr(2, 8);
};

const request = ({ url, method = "GET", headers }) => {
  const requestId = generateId(); 
  const requestConfig = {
    url,
    method,
    headers: makeCancellable(headers || {}, requestId),
  };

  return axios
    .request(requestConfig)
    .then((res) => {
      delete pendingRequests[requestId];
      return { data: res.data };
    })
    .catch(() => {
      delete pendingRequests[requestId];
      /*if (axios.isCancel(error)) {
        console.log(`A request to url $ {url} was cancelled`);
      } else {
        return handleReject(error);
      } */
    });
};

export default request;
