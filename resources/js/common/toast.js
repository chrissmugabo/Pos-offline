export default {
  install(app) {
    app.config.globalProperties.$toast = (text) => {
      const toast = `<div
        class="error_msg sucess_msg"
        id="new_notification"
        style="display: block; top: 100px"
      >
        <div style="display: table; width: 100%">
          <div class="err_icon_aligner">
            <div class="error_msg_cross"><span class="tick"></span></div>
          </div>
          <div class="error_msg_text">
            <span id="succ_or_err"></span>
            <span id="succ_or_err_msg">${text}</span>
          </div>
        </div>
      </div>`;
      var div = document.createElement("div");
      div.innerHTML  = toast;
      document.body.prepend(div);
      setTimeout(() => {
        div.remove();
      }, 3000);
    };
  },
};
