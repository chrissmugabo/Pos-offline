export default {
  install(app, store) {
    app.config.globalProperties.$confirm = (options) => {
      var div = document.createElement("div");
      div.setAttribute("id", "alert-container");
      const alert = ` <div
          class="modal fade show"
          id="modal-default"
          tabindex="-1"
          role="dialog"
          style="display: block"
        >
          <div
            class="modal-dialog modal-sm modal-dialog-centered"
            role="document"
          >
            <div class="modal-content" style="border-color: transparent">
              <div class="modal-header py-2">
                <h5 class="modal-title">
                  ${options.title}
                </h5>
                <a
                  href="javascript:void(0)"
                  class="close text-dark"
                >
                <svg height="18px" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
                    <path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                </svg>
                  </a>
              </div>
    
              <div class="modal-body text-left h6 mb-0">
                  ${options.content}
              </div>
              <div class="modal-footer">
                <button
                  type="button"
                  class="mr-auto ${options.classes}"
                  id="action-btn"
                >
                  <span id="action-text">${options.actionText}</span>
                  <p id="alert-loading"><span></span><span></span><span></span></p>
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
        store.commit("REGISTER_CONFIRM_MODAL");
        div.querySelector("#action-text").remove();
        div.querySelector("#alert-loading").classList.add("d-block");
        options.action();
      });
      document.body.prepend(div);
    };
  },
};
