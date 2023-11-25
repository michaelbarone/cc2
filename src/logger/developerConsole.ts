import { inspect } from "util";
import colors from "colors/safe.js";
import { Format, TransformableInfo } from "logform";
import { LEVEL, MESSAGE, SPLAT } from "triple-beam";

// This developer console class is based on winston-console-format
// with custom changes to fit EyeRate's use case
export class DeveloperConsole {
  // Regular Expression for finding spaces
  private static readonly reSpaces = /^\s+/;

  // Regular expression for finding spaces (or empty strings)
  private static readonly reSpacesOrEmpty = /^(\s*)/;

  // Ignore rule to catch regex mistakes, this is intentional
  // eslint-disable-next-line no-control-regex
  private static readonly reColor = /\x1B\[\d+m/;

  // Log attributes to strip out
  private static readonly strip = [LEVEL, MESSAGE, SPLAT, "level", "message", "ms", "stack", "service"];

  // Pretty characters to display
  private static readonly chars = {
    singleLine: "▪",
    startLine: "┏",
    line: "┃",
    endLine: "┗"
  };

  // Ignore explicit any, the util inspect takes type "any"
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  private inspector(value: any, messages: string[]): void {
    // Pass options to util inspector
    const inspector = inspect(value, {
      depth: Infinity,
      colors: true,
      maxArrayLength: Infinity,
      breakLength: 120,
      compact: Infinity
    });

    // Inspect lines and print
    inspector.split("\n").forEach((line) => {
      messages.push(line);
    });
  }

  private message(info: TransformableInfo, chr: string, color: string): string {
    // Main message builder
    const message = info.message.replace(DeveloperConsole.reSpacesOrEmpty, `$1${color}${colors.dim(chr)}${colors.reset(" ")}`);
    return `${info.level}:${message}`;
  }

  private pad(message?: string): string {
    // Pad if match found, otherwise return nothing
    const matches = message && message.match(DeveloperConsole.reSpaces);
    if (matches && matches.length > 0) {
      return matches[0];
    }
    return "";
  }

  private ms(info: TransformableInfo): string {
    // If log contains millisecond timestamp, color otherwise return nothing
    if (info.ms) {
      return colors.italic(colors.dim(` ${info.ms}`));
    }
    return "";
  }

  private stack(info: TransformableInfo): string[] {
    const messages: string[] = [];

    // If log contains a stack, create error stack and inspect
    if (info.stack) {
      const error = new Error();
      error.stack = info.stack;
      this.inspector(error, messages);
    }

    // Return new messages
    return messages;
  }

  private meta(info: TransformableInfo): string[] {
    const messages: string[] = [];
    const stripped = { ...info };

    // Strip out specific attributes from metadata
    DeveloperConsole.strip.forEach((e) => delete stripped[e]);

    // Inspect metadata we want to print
    if (Object.keys(stripped).length > 0) {
      this.inspector(stripped, messages);
    }

    // Return new messages
    return messages;
  }

  private getColor(info: TransformableInfo): string {
    // Find color, with default empty string (no color)
    const colorMatch = info.level.match(DeveloperConsole.reColor);
    if (colorMatch) {
      return colorMatch[0];
    }
    return "";
  }

  private write(info: TransformableInfo, messages: string[], color: string): void {
    // Create padded message
    const pad = this.pad(info.message);

    // For each line, set colors, add padding, and inspection (with line numberbing)
    messages.forEach((line, index, arr) => {
      const lineNumber = colors.dim(`[${(index + 1).toString().padStart(arr.length.toString().length, " ")}]`);
      let chr = DeveloperConsole.chars.line;
      if (index === arr.length - 1) {
        chr = DeveloperConsole.chars.endLine;
      }
      info[MESSAGE] += `\n${colors.dim(info.level)}:${pad}${color}${colors.dim(chr)}${colors.reset(" ")}`;
      info[MESSAGE] += `${lineNumber} ${line}`;
    });
  }

  public transform(info: TransformableInfo): TransformableInfo {
    // Track individual lines for log message
    const messages: string[] = [];

    // Show meta and error stacks
    messages.push(...this.stack(info));
    messages.push(...this.meta(info));

    // Get color for message
    const color = this.getColor(info);

    // Transform message
    info[MESSAGE] = this.message(info, DeveloperConsole.chars[messages.length > 0 ? "startLine" : "singleLine"], color);
    // Transform timestamp
    info[MESSAGE] += this.ms(info);

    // Write message and color into info
    this.write(info, messages, color);

    // Return transformed message object
    return info;
  }
}

// Return instance of DeveloperConsole
export const DeveloperConsoleFormat: Format = new DeveloperConsole();
