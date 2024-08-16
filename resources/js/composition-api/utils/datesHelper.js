import { useLayout } from "@/composition-api/hooks/useLayout";

const { appSettings } = useLayout();

export const formatDate = (date) => {
  try {
    const d = new Date(date);
    const ye = new Intl.DateTimeFormat("en", { year: "numeric" }).format(d);
    const mo = new Intl.DateTimeFormat("en", { month: "short" }).format(d);
    const da = new Intl.DateTimeFormat("en", { day: "2-digit" }).format(d);
    return `${mo} ${da}, ${ye}`;
  } catch (error) {
    //console.log(error);
  }
};

export const dateDiffInDays = (a, b) => {
  const _MS_PER_DAY = 1000 * 60 * 60 * 24;
  // Discard the time and time-zone information.
  const utc1 = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
  const utc2 = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
  return Math.floor((utc2 - utc1) / _MS_PER_DAY);
};
export const timeDifference = (target, current = new Date()) => {
  const msPerMinute = 60 * 1000;
  const msPerHour = msPerMinute * 60;
  const msPerDay = msPerHour * 24;
  const msPerMonth = msPerDay * 30;
  const msPerYear = msPerDay * 365;
  const elapsed = target - current;
  let nume;
  let unit;
  if (elapsed > 0) {
    if (elapsed < msPerMinute) {
      nume = Math.round(elapsed / 1000);
      unit = "seconds";
    } else if (elapsed < msPerHour) {
      nume = Math.round(elapsed / msPerMinute);
      unit = "minutes";
    } else if (elapsed < msPerDay) {
      nume = Math.round(elapsed / msPerHour);
      unit = "hours";
    } else if (elapsed < msPerMonth) {
      nume = Math.round(elapsed / msPerDay);
      unit = "days";
    } else if (elapsed < msPerYear) {
      nume = Math.round(elapsed / msPerMonth);
      unit = "months";
    } else {
      nume = Math.round(elapsed / msPerYear);
      unit = "years";
    }
  } else {
    if (elapsed > -1 * msPerMinute) {
      nume = Math.round(elapsed / 1000);
      unit = "seconds";
    } else if (elapsed > -1 * msPerHour) {
      nume = Math.round(elapsed / msPerMinute);
      unit = "minutes";
    } else if (elapsed > -1 * msPerDay) {
      nume = Math.round(elapsed / msPerHour);
      unit = "hours";
    } else if (elapsed > -1 * msPerMonth) {
      nume = Math.round(elapsed / msPerDay);
      unit = "days";
    } else if (elapsed > -1 * msPerYear) {
      nume = Math.round(elapsed / msPerMonth);
      unit = "months";
    } else {
      nume = Math.round(elapsed / msPerYear);
      unit = "years";
    }
  }
  /*const rtf = new Intl.RelativeTimeFormat();
  return rtf.format(nume, unit);*/
  return `${-nume} ${unit}`;
};

export const formatSeasonDate = (date) => {
  const d = new Date(date);
  const ye = new Intl.DateTimeFormat("en", { year: "numeric" }).format(d);
  const mo = new Intl.DateTimeFormat("en", { month: "long" }).format(d);
  return `${mo} ${ye}`;
};

export const isMinor = (dob) => {
  const dobDate = new Date(dob);
  const today = new Date(appSettings.today);
  // Calculate the difference in years
  let age = today.getFullYear() - dobDate.getFullYear();
  // Adjust the age if the current month or day is before the birth month or day
  if (
    today.getMonth() < dobDate.getMonth() ||
    (today.getMonth() == dobDate.getMonth() &&
      today.getDate() < dobDate.getDate())
  ) {
    age--;
  }

  // Check if the age is less than 18
  if (age < 18) {
    return true;
  } /*else {
  if the current date matches the birth date
  if (today.getMonth() == dobDate.getMonth() && today.getDate() == dobDate.getDate()) {
    alert("Happy birthday!");
  }
} */
};
