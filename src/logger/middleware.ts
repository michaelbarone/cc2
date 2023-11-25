import morgan, { StreamOptions } from "morgan";
import { logger } from "./logger.js";

// Override the stream method by telling
// Morgan to use our custom logger instead of the console.log
class MorganStream implements StreamOptions {
  write(message: string): void {
    logger.http(message.trim());
  }
}

/**
 * @description middleware for morgan - ONLY for server side
 * @example
 * import leyebrary-backend then add morganMiddleware to express app
 * @example
 * import { func } from "@eyerate/leyebrary-backend";
 * app.use(func.morganMiddleware);
 */
export const morganMiddleware = morgan(
  // Define message format string
  ":method :url HTTP/:http-version :status  :res[content-length] :response-time ms - :remote-user :remote-addr :referrer :user-agent",
  // Use custom stream from above
  { stream: new MorganStream() }
);
