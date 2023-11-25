import type winston from "winston";

export interface AppLogger extends winston.Logger {
  /** @deprecated alert log level is deprecated. Please use critical instead. */
  alert: winston.LeveledLogMethod;

  critical: winston.LeveledLogMethod;
  debug: winston.LeveledLogMethod;

  /** @deprecated emergency log level is deprecated. Please use critical instead. */
  emergency: winston.LeveledLogMethod;

  error: winston.LeveledLogMethod;
  http: winston.LeveledLogMethod;
  info: winston.LeveledLogMethod;

  /** @deprecated notice log level is deprecated. Please use verbose instead. */
  notice: winston.LeveledLogMethod;

  verbose: winston.LeveledLogMethod;
  warn: winston.LeveledLogMethod;
}
