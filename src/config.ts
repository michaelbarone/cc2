/* Get environment variable values with type-checking and defaults */
import * as dotenv from "dotenv";
import env from "env-var";

dotenv.config();

// Environment (development, staging, production)
const NODE_ENV = env.get("NODE_ENV").required().asString();
const APP_ENV = env.get("APP_ENV").default(NODE_ENV).asString();

// Application
const APP_NAME = env.get("APP_NAME").default("local").asString();

// Database
const DB_USER = env.get("DB_USER").asString();
const DB_PASS = env.get("DB_PASS").asString();
const DB_HOST = env.get("DB_HOST").asString();

// Logging
const LOG_LEVEL = env.get("LOG_LEVEL").asString();
const LOG_SLACK_WEBHOOK = env.get("LOG_SLACK_WEBHOOK").asString();
const LOG_SLACK_LEVEL = env.get("LOG_SLACK_LEVEL").asString();
const LOG_DAILY_ROLLUP_WEBHOOK = env.get("LOG_DAILY_ROLLUP_WEBHOOK").asString();
const LOG_DAILY_ROLLUP_LEVEL = env.get("LOG_DAILY_ROLLUP_LEVEL").asString();

// Build config from validated variabled
const config = {
  environment: NODE_ENV,
  application: {
    environment: APP_ENV,
    name: APP_NAME
  },
  database: {
    user: DB_USER,
    pass: DB_PASS,
    host: DB_HOST
  },
  logging: {
    level: LOG_LEVEL,
    slack: {
      webhook: LOG_SLACK_WEBHOOK,
      level: LOG_SLACK_LEVEL
    },
    dailyRollup: {
      webhook: LOG_DAILY_ROLLUP_WEBHOOK,
      level: LOG_DAILY_ROLLUP_LEVEL
    }
  }
};

// Export the config object
export default config;
