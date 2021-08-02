<?php
namespace ServiceTracker\admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://delbem.net/service-tracker
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
class Service_Tracker_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/style.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		if ( $_SERVER['SERVER_NAME'] === 'aulasplugin.local' ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dev/App.js', array( 'wp-element' ), $this->version, false );
		} else {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/prod/App.js', array( 'wp-element' ), $this->version, false );
		}

	}

	public function localize_scripts( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		// This file has all the texts inside a variable $texts_array
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'translation/texts_array.php' );

		wp_localize_script(
			$this->plugin_name,
			'data',
			$texts_array
		);
	}

	public function admin_page() {
		add_menu_page( 'Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', array( $this, 'admin_index' ), 'dashicons-money', 10 );
	}

	public function admin_index() {
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/admin_page.php' );
	}
}
