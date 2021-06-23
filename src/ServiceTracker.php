<?php
namespace ServiceTracker\src;

use ServiceTracker\src\Api\Api;


class ServiceTracker {

	public $base_path;

	public $base_plugin_uri;

	function __construct() {
		$this->base_path = plugin_dir_path( __DIR__ );

		$this->base_plugin_uri = plugin_dir_url( __DIR__ );
	}


	function run() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );

		$this->add_client_role();

		$this->api();
	}

	function add_client_role() {
		if ( class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_role( 'client', 'Client', get_role( 'subscriber' )->capabilities );
	}

	function add_admin_pages() {
		add_menu_page( 'Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', array( $this, 'admin_index' ), 'dashicons-universal-access-alt', 10 );
	}

	function admin_index() {
		require_once $this->base_path . '/admin/partials/service-tracker-admin-display.php';
	}

	function enqueue( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'service-tracker-script', $this->base_plugin_uri . 'assets/js/app.js', array( 'wp-element' ), time(), false );

		wp_localize_script(
			'service-tracker-script',
			'data',
			array(
				'root_url' => get_site_url(),
				'api_url'  => 'service-tracker/v1',
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_enqueue_style( 'service-tracker-style', $this->base_plugin_uri . 'assets/css/style.css', array(), null );
	}

	/**
	 * It will create the custom api end points
	 * using Api class
	 *
	 * @return void
	 */
	function api() {
		$api_cases = new Api( 'cases', '_user' );
		$api_cases->register_api();

		$api_progress = new Api( 'progress', '_case' );
		$api_progress->register_api();
	}
}

	// $serviceTracker = new ServiceTracker();

	// $serviceTracker->run();

	// On activation
	// register_activation_hook( $serviceTracker->base_path . 'Service_Tracker_init.php', array( $serviceTracker, 'activate' ) );

	// On uninstall
	// register_uninstall_hook( $serviceTracker->base_path . 'Service_Tracker_init.php', array( $serviceTracker, 'uninstall' ) );

