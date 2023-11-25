import { dashboardSettings } from "./app.js";

export function getDay(date, index = 1) {
  const inputDay = new Date(date).toLocaleString("en-US", { timeZone: dashboardSettings.timezone, weekday: "short" });
  const currentDay = new Date().toLocaleString("en-US", { timeZone: dashboardSettings.timezone, weekday: "short" });
  if (index == 0 && currentDay == inputDay) {
    return "TODAY";
  }
  return inputDay;
}

export class HTTPResponseError extends Error {
  response: any;

  constructor(response) {
    super(`HTTP Error Response: ${response.status} ${response.statusText}`);
    this.response = response;
  }
}

export default getDay;

// module.exports = { getDay };
