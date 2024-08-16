export default {
  install: (app, store) => {
    app.config.globalProperties.$notify = (data) => {
      store.commit("SET_FLASH_MESSAGE", data);
    };
  },
};
