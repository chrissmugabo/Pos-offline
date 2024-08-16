import { Printd } from "printd";
import { baseURL } from "./helpers";
const styles = [
  `${location.origin}/css/bootstrap.min.css`,
  `${location.origin}/css/app.min.css`,
  `${location.origin}/css/custom.css`,
];

export const printWithoutHeader = (elementId) => {
  const d = new Printd();
  const elt = document.querySelector(elementId);
  d.print(elt, [...styles]);
};

export const handlePrint = (title, elementId, css = []) => {
  const pageTitle = title ? title : document.title;
  const d = new Printd();
  const header = `<table class="table table-borderless mb-3">
    <tr>
      
    </tr>
    <tr>
      <td colspan="2" style="text-align: center">
        <h2 class="mb-0">${pageTitle}</h2>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="text-align: center;">
        <div style="border-bottom: 2px solid #444;width: 100%; margin: auto;text-align: center;"></div>
      </td>
    </tr>
  </table>`;
  const printEl = document.querySelector(elementId);
  const div = document.createElement("div");
  div.innerHTML = header.trim();
  if (printEl && printEl.parentElement) {
    printEl.parentElement.prepend(div);
    d.print(printEl.parentElement, [...styles, ...css]);
    setTimeout(() => {
      div.remove();
    }, 250);
  }
};

export const handleExcelExport = (excelFilename, tableId) => {
  if (!tableId) {
    tableId = "table.table";
  } else {
    tableId = "#" + tableId;
  }
  const container = document.querySelector(tableId);
  const head = container.querySelector("thead");
  const headers = [...head.querySelectorAll("th:not(.action):not(.check)")].map(
    (item) => item.textContent
  );
  const dataset = [];
  [...container.querySelectorAll("tbody tr")].forEach((row) => {
    const arr = [...row.querySelectorAll("td:not(.action):not(.check)")].map(
      (item) => item.textContent
    );
    dataset.push(arr);
  });
  /**
   * Creating a temporary form
   */
  const dataForm = document.createElement("form");
  dataForm.target = "_blank";
  dataForm.method = "POST";
  dataForm.action = `${baseURL}api/shared/export/excel`;
  const records = document.createElement("input");
  const file_name = document.createElement("input");
  const headings = document.createElement("input");
  records.type = "hidden";
  file_name.type = "hidden";
  headings.type = "hidden";
  records.name = "dataset";
  records.value = JSON.stringify(dataset);
  file_name.name = "filename";
  file_name.value = excelFilename || document.title.replaceAll(" ", "-");
  headings.name = "columns";
  headings.value = JSON.stringify(headers);
  dataForm.appendChild(records);
  dataForm.appendChild(file_name);
  dataForm.appendChild(headings);
  dataForm.style.display = "none";
  document.body.appendChild(dataForm);
  dataForm.submit();
};
