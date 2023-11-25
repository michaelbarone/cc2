import fastRedact, { RedactOptions } from "fast-redact";

// Configure redaction options
const redactOptions: RedactOptions = {
  paths: ["headers.authorization", "headers.partner_key", "config.data", "config.auth"],
  serialize: true,
  strict: false
};

// Export redact function for use
export const redact = fastRedact(redactOptions);
