<?php
defined( 'ABSPATH' ) or die( 'You do not have permission to access this file on its own.' );

require_once plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

use ServiceTracker\Sql\Activate;
use ServiceTracker\Sql\Uninstall;
use ServiceTracker\Api\Api;

if ( ! class_exists( 'ServiceTracker' ) ) {

	class ServiceTracker {

		public $base_path;

		public $base_plugin_uri;


		function __construct() {
			$this->base_path = plugin_dir_path( __DIR__ );

			$this->base_plugin_uri = plugin_dir_url( __DIR__ );
		}


		function register() {
			if ( isset( $_GET['page'] ) && $_GET['page'] === 'service_tracker' ) {

				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			}

			if ( ! class_exists( 'WooCommerce' ) ) {
				$this->add_client_role();
			}

			add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		}

		function add_client_role() {
			add_role( 'client', 'Client', get_role( 'subscriber' )->capabilities );
		}

		function add_admin_pages() {
			add_menu_page( 'Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', array( $this, 'admin_index' ), 'dashicons-universal-access-alt', 10 );
		}

		function admin_index() {
			require_once $this->base_path . '/src/templates/admin_view.php';
		}

		function enqueue() {
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
			$api_cases = new Api( 'cases', 'user' );
			$api_cases->register_api();

			$api_progress = new Api( 'progress', 'case' );
			$api_progress->register_api();
		}

		/**
		 * It will create all the necessary tables
		 *
		 * @return void
		 */
		function activate() {
			Activate::activate();
		}

		/**
		 * It will drop tables with uninstall
		 *
		 * @return void
		 */
		function uninstall() {
			Uninstall::uninstall();
		}
	}

	$serviceTracker = new ServiceTracker();

	$serviceTracker->register();

	$serviceTracker->api();

	// On activation
	register_activation_hook( $serviceTracker->base_path . 'Service_Tracker_init.php', array( $serviceTracker, 'activate' ) );

	// On uninstall
	register_uninstall_hook( $serviceTracker->base_path . 'Service_Tracker_init.php', array( $serviceTracker, 'uninstall' ) );
}
