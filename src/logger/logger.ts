import winston from "winston";
import * as Transport from "winston-transport";
import ecsFormat from "@elastic/ecs-winston-format";
import config from "../config.js";
// import { redact } from "./redact";
import { AppLogger } from "./types.js";
import { DeveloperConsoleFormat } from "./developerConsole.js";

// Define logging levels
export const levels: winston.config.AbstractConfigSetLevels = {
  critical: 0,
  error: 1,
  warn: 2,
  http: 3,
  verbose: 4,
  info: 5,
  debug: 6
};

// Define different colors for each level.
// https://www.npmjs.com/package/colors
const loggingColors = {
  critical: "red",
  error: "red",
  warn: "yellow",
  http: "magenta",
  verbose: "white",
  info: "green",
  debug: "blue"
};
winston.addColors(loggingColors);

// Create redaction formatter from local redactor
// TODO: Fix redaction format to return regular string instead of escaped string
// const redactionFormat = winston.format((info) => {
//   if (config.environment === "development") return info;
//   // Redact secrets from message and metadata
//   info.message = redact(info.message);
//   // info.metadata = redact(info.metadata);
//   return info;
// })();

// Create developer log formatter
const devFormat = winston.format.combine(
  // Timestamp with the preferred format
  winston.format.timestamp(),
  // Metadata
  winston.format.metadata(),
  // Milliseconds
  winston.format.ms(),
  // Logs must have stack traces
  winston.format.errors({ stack: true }),
  // Colorize logs
  winston.format.colorize({ all: true }),
  // Format pad levels to be the same length
  winston.format.padLevels(),
  // Developer console formatting
  DeveloperConsoleFormat
);

// Create production log formatter
const prodFormat = winston.format.combine(winston.format.timestamp(), winston.format.metadata(), ecsFormat({ convertReqRes: true, convertErr: true }));

// Set log level based on environment
const level = config.logging.level || (config.environment === "production" ? "warn" : "debug");

// Setup logging transports
const transports: Transport[] | Transport = [];

// Enable console transport
transports.push(new winston.transports.Console({ level }));

// Build logger format (and export for other use cases)
export const loggerConfig: winston.LoggerOptions = {
  level,
  levels,
  format: config.environment !== "development" ? prodFormat : devFormat,
  transports,
  exitOnError: false,
  defaultMeta: {
    service: config.application.name
  }
};

/**
 * @description logging instance for winston
 * @param {string} message the message to log
 * @param {object} meta optional additional metadata in object form
 * @export
 * @example
 * Logging Levels depend on .env.APP_ENV
 * APP_ENV = local -- will log all levels
 * APP_ENV = development -- will log all levels
 * APP_ENV = production -- (or !APP_ENV or APP_ENV !== development) will log only warn and above
 */
const logger: AppLogger = <AppLogger>winston.createLogger(loggerConfig);

// Deprecated logging levels for backwards-compatability
// eslint-disable-next-line deprecation/deprecation
logger.notice = logger.verbose;
// eslint-disable-next-line deprecation/deprecation
logger.emergency = logger.critical;
// eslint-disable-next-line deprecation/deprecation
logger.alert = logger.critical;

// Export logger instance
export { logger };
