<?php
namespace STOLMCServiceTracker\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
class STOLMCServiceTrackerAdmin {

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
	public function __construct( $plugin_name, $version, $block_enqueue_bad_config ) {
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
	public function remove_wp_admin_elements() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Non-admin data check for page identification.
		if ( isset( $_GET['page'] ) && 'service_tracker' === $_GET['page'] ) {

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
	public function remove_help_tabs() {
		$screen = get_current_screen();
		$screen->remove_help_tabs();
	}

	/**
	 * Hide WordPress admin elements with custom CSS.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function hide_wp_elements() {
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
	public function enqueue_styles( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
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
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/prod/App.js', [], $this->version, true );

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
	 * Localize scripts with plugin data.
	 *
	 * @since    1.0.0
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function localize_scripts( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		// This file has all the texts inside a variable $texts_array.
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'translation/texts_array.php' );

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
	public function admin_page() {
		add_menu_page( 'Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', [ $this, 'admin_index' ], 'dashicons-money', 10 );
	}

	/**
	 * Render the admin index page.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function admin_index() {
		if ( $this->block_enqueue_bad_config ) {
			include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/admin_page_bad_config.php' );
			return;
		}
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/admin_page.php' );
	}
}
