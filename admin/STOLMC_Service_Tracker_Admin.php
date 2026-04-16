<?php
namespace STOLMC_Service_Tracker\admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://delbem.net/portfolio/service-tracker-sto/
 * @since      1.0.0
 *
 * @package    Service_Tracker
 * @subpackage Service_Tracker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Service_Tracker
 * @subpackage Service_Tracker/admin
 * @author     Rodrigo Del Bem <rodrigodelbem@gmail.com>
 */
class STOLMC_Service_Tracker_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Whether to block enqueueing scripts due to bad permalink configuration.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $block_enqueue_bad_config Flag to block script enqueue.
	 */
	private $block_enqueue_bad_config;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param string $plugin_name            The name of this plugin.
	 * @param string $version                The version of this plugin.
	 * @param bool   $block_enqueue_bad_config Whether to block script enqueue.
	 */
	public function __construct( string $plugin_name, string $version, bool $block_enqueue_bad_config ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->block_enqueue_bad_config = $block_enqueue_bad_config;

		// Add hooks to remove WordPress admin elements on our page.
		add_action( 'admin_init', [ $this, 'remove_wp_admin_elements' ] );
	}

	/**
	 * Remove WordPress admin elements from our plugin page.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function remove_wp_admin_elements(): void {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Non-admin data check for page identification.
		if ( isset( $_GET['page'] ) && 'service_tracker' === $_GET['page'] ) {

			/**
			 * Fires when the admin page is initialized.
			 *
			 * @since 1.0.0
			 */
			do_action( 'stolmc_service_tracker_admin_page_init' );

			// Remove footer text.
			add_filter( 'admin_footer_text', '__return_empty_string', 9999 );
			add_filter( 'update_footer', '__return_empty_string', 9999 );

			// Remove help tabs.
			add_action( 'admin_head', [ $this, 'remove_help_tabs' ] );

			// Add custom CSS to hide remaining elements.
			add_action( 'admin_head', [ $this, 'hide_wp_elements' ] );
		}
	}

	/**
	 * Remove help tabs from the current screen.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function remove_help_tabs(): void {
		$screen = get_current_screen();
		if ( null !== $screen ) {
			$screen->remove_help_tabs();
		}
	}

	/**
	 * Hide WordPress admin elements with custom CSS.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function hide_wp_elements(): void {
		echo '<style>
			#wpfooter,
			#screen-meta-links,
			#contextual-help-link-wrap,
			#help-link-wrap {
				display: none !important;
			}
			#wpbody-content {
				padding-bottom: 0 !important;
			}
		</style>';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_styles( string $hook ): void {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		/**
		 * Filters whether to enqueue admin styles.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $enqueue Whether to enqueue styles.
		 * @param string $hook    The current admin page.
		 */
		if ( ! apply_filters( 'stolmc_service_tracker_admin_enqueue_styles', true, $hook ) ) {
			return;
		}

		// Load Google Fonts (Inter, Manrope, Material Symbols).
		wp_enqueue_style(
			$this->plugin_name . '-google-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800;900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap',
			[],
			$this->version
		);

		// Load the new Tailwind CSS file from the build.
		$css_file = plugin_dir_path( __FILE__ ) . 'js/prod/style.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style( $this->plugin_name . '-tailwind', plugin_dir_url( __FILE__ ) . 'js/prod/style.css', [], $this->version, 'all' );
		} else {
			// Fallback to old CSS if new one doesn't exist.
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/style.css', [], $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( string $hook ): void {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		/**
		 * Filters whether to enqueue admin scripts.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $enqueue Whether to enqueue scripts.
		 * @param string $hook    The current admin page.
		 */
		if ( ! apply_filters( 'stolmc_service_tracker_admin_enqueue_scripts', true, $hook ) ) {
			return;
		}

		$entrypoint_file = $this->resolve_entrypoint_file();
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/prod/' . $entrypoint_file, [], $this->version, true );

		// Add type="module" to enable ES module syntax (dynamic imports for code splitting).
		add_filter(
			'script_loader_tag',
			function ( $tag, $handle ) {
				if ( $handle === $this->plugin_name ) {
					return str_replace( '<script ', '<script type="module" ', $tag );
				}
				return $tag;
			},
			10,
			2
		);
	}

	/**
	 * Resolve the hashed app entrypoint file from build mapping.
	 *
	 * Falls back to the latest App-*.js file when mapping is missing,
	 * then to App.js for backward compatibility.
	 *
	 * @return string
	 */
	private function resolve_entrypoint_file(): string {
		$default_file = 'App.js';
		$prod_dir = plugin_dir_path( __FILE__ ) . 'js/prod/';
		$map_file = $prod_dir . 'entrypoints.json';

		if ( file_exists( $map_file ) && is_readable( $map_file ) ) {
			$raw_map = file_get_contents( $map_file );
			if ( false !== $raw_map ) {
				$decoded_map = json_decode( $raw_map, true );

				if ( is_array( $decoded_map ) && isset( $decoded_map['entrypoint'] ) && is_string( $decoded_map['entrypoint'] ) ) {
					$entrypoint = basename( $decoded_map['entrypoint'] );
					$is_valid_entrypoint = 1 === preg_match( '/^App-[A-Za-z0-9_-]+\.js$/', $entrypoint );

					if ( $is_valid_entrypoint && file_exists( $prod_dir . $entrypoint ) ) {
						return $entrypoint;
					}
				}
			}
		}

		$hashed_matches = glob( $prod_dir . 'App-*.js' );
		if ( is_array( $hashed_matches ) && ! empty( $hashed_matches ) ) {
			usort(
				$hashed_matches,
				static function ( string $a, string $b ): int {
					return filemtime( $b ) <=> filemtime( $a );
				}
			);

			return basename( $hashed_matches[0] );
		}

		return $default_file;
	}

	/**
	 * Localize scripts with plugin data.
	 *
	 * @since    1.0.0
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function localize_scripts( string $hook ): void {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		// This file has all the texts inside a variable $texts_array.
		$texts_array = [];
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'translation/texts_array.php' );

		/**
		 * Filters the data passed to the admin JavaScript.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $texts_array The texts data array.
		 * @param string $hook        The current admin page.
		 */
		$texts_array = apply_filters( 'stolmc_service_tracker_admin_localize_script_data', $texts_array, $hook );

		wp_localize_script(
			$this->plugin_name,
			'data',
			$texts_array
		);
	}

	/**
	 * Register the admin menu page.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function admin_page(): void {
		add_menu_page( 'Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', [ $this, 'admin_index' ], 'dashicons-money', 10 );
	}

	/**
	 * Render the admin index page.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function admin_index(): void {
		if ( $this->block_enqueue_bad_config ) {
			/**
			 * Fires before the admin page renders (error state).
			 *
			 * @since 1.0.0
			 */
			do_action( 'stolmc_service_tracker_admin_page_before_render' );

			include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/admin_page_bad_config.php' );

			/**
			 * Fires after the admin page renders.
			 *
			 * @since 1.0.0
			 */
			do_action( 'stolmc_service_tracker_admin_page_after_render' );
			return;
		}

		/**
		 * Fires before the admin page renders.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stolmc_service_tracker_admin_page_before_render' );

		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/admin_page.php' );

		/**
		 * Fires after the admin page renders.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stolmc_service_tracker_admin_page_after_render' );
	}
}
