<?php

namespace STOLMC_Service_Tracker\admin\I18n;

defined( 'ABSPATH' ) || exit;

/**
 * Centralises all UI copy exposed to the React application.
 *
 * Loads translatable strings from a single aggregated PHP file and merges
 * them with runtime configuration (API URLs, nonce) before handing the
 * combined array to {@see wp_localize_script()}.
 *
 * @since    2.1.0
 * @package  STOLMC_Service_Tracker
 */
class STOLMC_UI_Copy {

	/**
	 * Absolute path to the aggregated copy file.
	 *
	 * @since 2.1.0
	 * @var   string
	 */
	private string $copy_file_path;

	/**
	 * Cached copy array (loaded once per request).
	 *
	 * @since 2.1.0
	 * @var   array<string, mixed>|null
	 */
	private ?array $copy = null;

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param string $copy_file_path Absolute path to the aggregated UI copy PHP file.
	 */
	public function __construct( string $copy_file_path ) {
		$this->copy_file_path = $copy_file_path;
	}

	/**
	 * Return all translatable UI strings.
	 *
	 * The array is loaded once and cached for the lifetime of the request.
	 *
	 * @since 2.1.0
	 *
	 * @return array<string, mixed>
	 */
	public function get_texts(): array {
		if ( null !== $this->copy ) {
			return $this->copy;
		}

		if ( ! file_exists( $this->copy_file_path ) || ! is_readable( $this->copy_file_path ) ) {
			$this->copy = [];
			return $this->copy;
		}

		/** @var array<string, mixed> $texts */
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin file include.
		$texts = include $this->copy_file_path;

		$this->copy = is_array( $texts ) ? $texts : [];

		return $this->copy;
	}

	/**
	 * Build the full data payload for wp_localize_script.
	 *
	 * Merges translatable strings with runtime configuration values
	 * (API URLs, nonce, site URL) so the React app receives everything
	 * in a single global `window.data` object.
	 *
	 * @since 2.1.0
	 *
	 * @return array<string, mixed>
	 */
	public function get_localize_data(): array {
		$config = [
			'root_url'      => get_site_url(),
			'users_api_url' => get_rest_url() . 'service-tracker-stolmc/v1/users',
			'api_url'       => 'service-tracker-stolmc/v1',
			'nonce'         => wp_create_nonce( 'wp_rest' ),
		];

		return array_merge( $this->get_texts(), $config );
	}
}
