import { Text } from "./Text";

/**
 * Return a translated UI string from the backend-localised `window.stolmcData`.
 *
 * @example
 *   stolmec_text(Text.AccordionFirstTitle)
 *   // → "Display info for customers access"
 *
 * @param key - A {@link Text} enum value (the raw `window.stolmcData` key).
 * @returns The translated string, or the key itself as a fallback.
 */
export function stolmc_text(key: Text): string {
  return stolmcData[key] ?? key;
}

/**
 * Convenience accessor for array-valued copy (e.g. weekday/month names).
 *
 * @example
 *   stolmec_text_array(Text.CalendarWeekdays)
 *   // → ["Sun", "Mon", …]
 *
 * @param key - A {@link Text} enum value whose value is an array.
 * @returns The array, or an empty array as a fallback.
 */
export function stolmc_text_array(key: Text): string[] {
  const value = stolmcData[key];
  return Array.isArray(value) ? value : [];
}

export { Text };
