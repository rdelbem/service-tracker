/**
 * Global data object injected by WordPress wp_localize_script.
 *
 * Contains both runtime config (root_url, nonce, api_url, …) and all
 * translatable UI strings keyed by their Text enum values.
 *
 * Prefer using {@link stolmc_text} instead of accessing this directly.
 *
 * @see react-app/i18n/Text.ts   — enum of text keys
 * @see react-app/i18n/index.ts  — stolmec_text() helper
 * @see admin/translation/ui_copy.php — canonical source of strings
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
declare const data: Record<string, any>;
