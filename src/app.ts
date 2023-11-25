import express, { Request } from "express";
import cors from "cors";
import serveStatic from "serve-static";
import bodyParser from "body-parser";
import fetch from "node-fetch";
import sqlite3 from "sqlite3";
import { getDay, HTTPResponseError } from "./utils.js";
import { logger as log, morganMiddleware } from "./logger/index.js";

const app = express();

app.use(cors());

app.use(function appUse(err, Request, Response, NextFunction) {
  Response.status(200).json(err);
});
app.use(bodyParser.urlencoded({ extended: true }));

app.use(morganMiddleware);

const port = process.env.APP_PORT || 9876;

app.use(serveStatic("http", { index: ["index.html"] }));

export const dashboardSettings = {
  checkWeatherCurrentInterval: 600000,
  checkWeatherCurrentMinimumElapsedTime: 1800000,
  checkWeatherDailyMinimumElapsedTime: 21600000,
  timezone: process.env.TIMEZONE,
  weatherGovZone: process.env.weatherGovZone,

  googleMapsApi: process.env.googleMapsApi,
  city: process.env.CITY,
  state: process.env.STATE,
  photoChange: process.env.photoChange,
  flickrFeedID: process.env.flickrFeedID,
  mapsLat: process.env.mapsLat,
  mapsLong: process.env.mapsLong
};

export const backendSettings = {
  weatherBitKey: process.env.weatherBitKey
};

const temp_pass = true;

// app.post("/data/saveToJSON", bodyParser.json(), async (req, res) => {
//   // not used anymore, all weather data should be coming from nodejs now
//   if (temp_pass && req.body) {
//     weather = req.body;
//     console.log(JSON.stringify(weather));
//     return res.status(200).json({ status: "success" });
//   }
//   return res.status(500).json({ status: "error" });
// });

// app.get("/data/weather", async (req, res) => {
//   if (temp_pass) {
//     if (getDay(weather.daily[0].datetime, 0) != "TODAY") {
//       while (weather.daily[0]?.datetime && getDay(weather.daily[0].datetime, 0) != "TODAY") {
//         weather.daily.shift();
//       }
//     }
//     return res.status(200).json(weather);
//   }
//   return res.status(500).json({ status: "error" });
// });

app.get("/dashboardSettings", async (req, res) => {
  if (temp_pass) {
    return res.status(200).json(dashboardSettings);
  }
  // return defaults?
  return res.status(500).json({ status: "error" });
});

const initApp = async () => {
  // in memory db for current status?
  // use env for persistent config?
  // const db = new sqlite3.Database("./data/db.sqlite");

  app.listen({ port, host: "0.0.0.0" }, () => log.warn(`> Listening on port ${port}`));
};

initApp();
