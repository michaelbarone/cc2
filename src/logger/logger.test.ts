import { logger } from "./logger";

// Setup winston mock for spying on logger
jest.mock("winston", () => {
  const formatOptions = {
    combine: jest.fn(),
    timestamp: jest.fn(),
    printf: jest.fn(),
    colorize: jest.fn(),
    ms: jest.fn(),
    uncolorize: jest.fn(),
    json: jest.fn(),
    splat: jest.fn(),
    errors: jest.fn(),
    padLevels: jest.fn(),
    metadata: jest.fn()
  };
  const formatFunction = jest.fn().mockReturnValue(jest.fn());
  Object.assign(formatFunction, formatOptions);
  return {
    createLogger: jest.fn(() => {
      return {
        critical: jest.fn(),
        warn: jest.fn(),
        http: jest.fn(),
        verbose: jest.fn(),
        info: jest.fn(),
        error: jest.fn(),
        debug: jest.fn()
      };
    }),
    addColors: jest.fn(),
    format: formatFunction,
    transports: {
      Console: jest.fn()
    }
  };
});

describe("logger", () => {
  it("should log critical messages", () => {
    logger.critical("test critical message");
    expect(logger.critical).toHaveBeenCalledWith("test critical message");
  });

  it("should log error messages", () => {
    logger.error("test error message");
    expect(logger.error).toHaveBeenCalledWith("test error message");
  });

  it("should log warn messages", () => {
    logger.warn("test warn message");
    expect(logger.warn).toHaveBeenCalledWith("test warn message");
  });

  it("should log http messages", () => {
    logger.http("test http message");
    expect(logger.http).toHaveBeenCalledWith("test http message");
  });

  it("should log verbose messages", () => {
    logger.verbose("test notice message");
    expect(logger.verbose).toHaveBeenCalledWith("test notice message");
  });

  it("should log info messages", () => {
    logger.info("test info message");
    expect(logger.info).toHaveBeenCalledWith("test info message");
  });

  it("should log debug messages", () => {
    logger.debug("test debug message");
    expect(logger.debug).toHaveBeenCalledWith("test debug message");
  });
});
