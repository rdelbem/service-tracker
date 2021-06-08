<?php
require_once plugin_dir_path( __DIR__ ) . '/vendor/autoload.php';

use ServiceTracker\Sql\Activate;

if ( ! class_exists( 'ServiceTracker' ) ) {

	class ServiceTracker {

		public $base_path;

		public $base_plugin_uri;

		function __construct() {
			$this->base_path = plugin_dir_path( __DIR__ );

			$this->base_plugin_uri = plugin_dir_url( __DIR__ );
		}

		function register() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

			add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		}

		public function add_admin_pages() {
			add_menu_page( 'Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', array( $this, 'admin_index' ), 'dashicons-universal-access-alt', 10 );
		}

		public function admin_index() {
			require_once $this->base_path . '/src/templates/admin_view.php';
		}

		function enqueue() {
			wp_enqueue_script( 'service-tracker-script', $this->base_plugin_uri . 'assets/js/app.js', array( 'wp-element' ), time(), false );

			wp_enqueue_style( 'service-tracker-style', $this->base_plugin_uri . 'assets/css/style.css', array(), null );
		}

		/**
		 * It will create all the necessary tables
		 *
		 * @return void
		 */
		function activate() {
			Activate::activate();
		}
	}

	$serviceTracker = new ServiceTracker();

	$serviceTracker->register();

	// On activation
	register_activation_hook( __FILE__, array( $serviceTracker, 'activate' ) );

	// On deactivation
}
