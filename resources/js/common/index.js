import { Printd } from "printd";
export default {
  install(app) {
    app.config.globalProperties.$helper = {
      timeZone: localStorage.getItem("_tz") || "Africa/Kigali",
      baseUrl(tail = null) {
        let result = location.origin;
        if (tail) result += `/${tail}`;
        return result;
      },
      formatNumber(number) {
        if (!number) {
          return 0;
        }
        let str = number.toString();
        const decimalIndex = str.indexOf(".");
        const decimalPlaces = 3;
        if (decimalIndex !== -1) {
          // Limit the decimal places without rounding
          const limitedDecimal = str.substr(decimalIndex + 1, decimalPlaces);
          // Construct the resulting string
          str = str.substr(0, decimalIndex + 1) + limitedDecimal;
        }
        return str.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      },
      reloadPage() {
        location.reload();
      },
      showFilePicker(inputFileId) {
        document.getElementById(inputFileId).click();
      },
      isEmpty(obj) {
        const isNULL = Object.values(obj).every((x) => x === null);
        return Object.keys(obj).length === 0 || isNULL === true;
      },
      empty(mixedVar) {
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
      },
      handleArrayPush(array, newItem) {
        return [...[newItem], ...array];
      },
      handlePrint(elt) {
        const d = new Printd();
        const css = [`${location.origin}/pos/css/thermal-print.css`];
        d.print(document.querySelector(elt), css);
      },
      resetObjectValues(obj) {
        for (let key in obj) {
          if (obj[key] === true) obj[key] = false;
          else if (obj[key] === false) continue;
          else obj[key] = null;
        }
        return obj;
      },
      formatDate(str) {
        let options = {
          month: "short",
          day: "numeric",
          year: "numeric",
          timeZone: this.timeZone,
        };
        let today = new Date(str);
        return today.toLocaleDateString("en-US", options);
      },
      formatTime(str) {
        return new Date(str).toLocaleTimeString("en-US", {
          timeZone: this.timeZone,
        });
      },
      formatOrderTime(str) {
        return new Date(str)
          .toTimeString("en-US", { timeZone: this.timeZone })
          .slice(0, 5);
      },
      generateFormData(obj) {
        const formData = new FormData();
        for (let key in obj) {
          if (
            obj[key] !== null &&
            obj[key] !== false &&
            typeof obj[key] !== "undefined"
          ) {
            if (typeof obj[key] === "object")
              formData.append(key, JSON.stringify(obj[key]));
            else formData.append(key, obj[key]);
          }
        }
        return formData;
      },
      toggleModalOpen() {
        const elt = document.documentElement;
        if (elt.classList.contains("modal-open"))
          elt.classList.remove("modal-open");
        else elt.classList.add("modal-open");

        const el = document.body;
        if (el.classList.contains("modal-open"))
          el.classList.remove("modal-open");
        else el.classList.add("modal-open");
      },
      encodeQuery(url, data) {
        let query = "";
        for (let d in data) {
          if (
            !this.empty(data[d]) &&
            url.indexOf(`?${d}`) < 0 &&
            url.indexOf(`&${d}`) < 0
          )
            query +=
              encodeURIComponent(d) + "=" + encodeURIComponent(data[d]) + "&";
        }
        return url.indexOf("?") > -1
          ? `${url}&${query.slice(0, -1)}`
          : `${url}?${query.slice(0, -1)}`;
      },
      generateVoucherNo(no) {
        let len = no.toString().length;
        if (len >= 4) return no;
        if (len == 1) return `000${no}`;
        if (len == 2) return `00${no}`;
        if (len == 3) return `0${no}`;
      },
      capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      },
      inputTitle(text) {
        return text.split("_").join(" ");
      },
      createTitleSlug(text) {
        return text.toLowerCase().split(" ").join("_");
      },
      createKeyPath(keys = []) {
        return keys.join(".");
      },
      getKeyValue(path, obj) {
        return path.split(".").reduce((p, c) => (p && p[c]) || null, obj);
      },
      timeDifference(date1, date2 = new Date(TODAY)) {
        const units = [
          { name: "year", milliseconds: 31536000000 },
          { name: "month", milliseconds: 2592000000 },
          { name: "day", milliseconds: 86400000 },
          { name: "hour", milliseconds: 3600000 },
          { name: "minute", milliseconds: 60000 },
          { name: "second", milliseconds: 1000 },
        ];

        const diff = Math.abs(new Date(date1) - new Date(date2));

        for (const unit of units) {
          const value = Math.floor(diff / unit.milliseconds);
          if (value >= 1) {
            return `${value} ${unit.name}${value > 1 ? "s" : ""} ago`;
          }
        }

        return "just now";
      },
      __timeDifference(target, current = new Date(TODAY)) {
        let msPerMinute = 60 * 1000;
        let msPerHour = msPerMinute * 60;
        let msPerDay = msPerHour * 24;
        let msPerMonth = msPerDay * 30;
        let msPerYear = msPerDay * 365;
        let elapsed = target - current;
        let nume, unit;
        if (elapsed > 0) {
          if (elapsed < msPerMinute) {
            nume = Math.round(elapsed / 1000);
            unit = "second";
          } else if (elapsed < msPerHour) {
            nume = Math.round(elapsed / msPerMinute);
            unit = "minute";
          } else if (elapsed < msPerDay) {
            nume = Math.round(elapsed / msPerHour);
            unit = "hour";
          } else if (elapsed < msPerMonth) {
            nume = Math.round(elapsed / msPerDay);
            unit = "day";
          } else if (elapsed < msPerYear) {
            nume = Math.round(elapsed / msPerMonth);
            unit = "month";
          } else {
            nume = Math.round(elapsed / msPerYear);
            unit = "year";
          }
        } else {
          if (elapsed > -1 * msPerMinute) {
            nume = Math.round(elapsed / 1000);
            unit = "second";
          } else if (elapsed > -1 * msPerHour) {
            nume = Math.round(elapsed / msPerMinute);
            unit = "minute";
          } else if (elapsed > -1 * msPerDay) {
            nume = Math.round(elapsed / msPerHour);
            unit = "hour";
          } else if (elapsed > -1 * msPerMonth) {
            nume = Math.round(elapsed / msPerDay);
            unit = "day";
          } else if (elapsed > -1 * msPerYear) {
            nume = Math.round(elapsed / msPerMonth);
            unit = "month";
          } else {
            nume = Math.round(elapsed / msPerYear);
            unit = "year";
          }
        }
        const rtf = new Intl.RelativeTimeFormat();
        const result = rtf.format(nume, unit);
        return result;
      },
      getRandomElement(array) {
        return array[Math.floor(Math.random() * array.length)];
      },
      randomDate(start, end) {
        return new Date(
          start.getTime() + Math.random() * (end.getTime() - start.getTime())
        );
      },
      generateRandomText(length) {
        let result = "";
        const characters =
          "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (let i = 0; i < length; i++) {
          const randomIndex = Math.floor(Math.random() * characters.length);
          result += characters.charAt(randomIndex);
        }
        return result;
      },
      formatOrderDate(order_date) {
        const result = this.timeDifference(new Date(order_date));
        const orderDate = this.formatDate(order_date);
        const today = this.formatDate(new Date(TODAY));
        if (orderDate == today) return "today";
        else if (
          result.indexOf("yesterday") > -1 ||
          result.indexOf("hour") > -1
        )
          return "yesterday";
        else return orderDate;
      },
      padNumber(number, targetedLength = 5) {
        let strNumber = number.toString();
        if (strNumber.length < targetedLength) {
          let padding = new Array(targetedLength - strNumber.length + 1).join(
            "0"
          );
          return padding + strNumber;
        }
        return number;
      },
      toggleFullscreen(elem) {
        elem = elem || document.documentElement;
        if (
          !document.fullscreenElement &&
          !document.mozFullScreenElement &&
          !document.webkitFullscreenElement &&
          !document.msFullscreenElement
        ) {
          if (elem.requestFullscreen) {
            elem.requestFullscreen();
          } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
          } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
          } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
          }
        } else {
          if (document.exitFullscreen) {
            document.exitFullscreen();
          } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
          } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
          } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
          }
        }
      },
    };
  },
};
