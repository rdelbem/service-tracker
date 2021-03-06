<?php
namespace ServiceTracker\includes;

use ServiceTracker\includes\Service_Tracker_Loader;
use ServiceTracker\includes\Service_Tracker_i18n;
use ServiceTracker\includes\Service_Tracker_Api;
use ServiceTracker\includes\Service_Tracker_Api_Cases;
use ServiceTracker\includes\Service_Tracker_Api_Progress;
use ServiceTracker\includes\Service_Tracker_Api_Toggle;
use ServiceTracker\admin\Service_Tracker_Admin;
use ServiceTracker\publics\Service_Tracker_Public; // public is a reserved word in php, it had to be changed to plural
use ServiceTracker\publics\Service_Tracker_Public_User_Content;

// This must be here, since PSR4 determines that define should not be used in an output file
define( 'SERVICE_TRACKER_VERSION', '1.0.0' );

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */


class Service_Tracker {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'SERVICE_TRACKER_VERSION' ) ) {
			$this->version = SERVICE_TRACKER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'service-tracker';

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->add_client_role();
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->api();
		$this->define_public_hooks();
		$this->public_user_content();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the admin area.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		$this->loader = new Service_Tracker_Loader();

	}

	private function api() {
		$serviceTracker_api_cases = new Service_Tracker_Api_Cases();

		$this->loader->add_action( 'rest_api_init', $serviceTracker_api_cases, 'run' );

		$serviceTracker_api_progress = new Service_Tracker_Api_Progress();

		$this->loader->add_action( 'rest_api_init', $serviceTracker_api_progress, 'run' );

		$serviceTracker_api_toggle = new Service_Tracker_Api_Toggle();

		$this->loader->add_action( 'rest_api_init', $serviceTracker_api_toggle, 'run' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Service_Tracker_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Service_Tracker_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function add_client_role() {
		add_role( 'client', __( 'Client', 'service-tracker' ), get_role( 'subscriber' )->capabilities );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Service_Tracker_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'localize_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_page' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		if ( is_admin() ) {
			return;
		}

		$plugin_public = new Service_Tracker_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	private function public_user_content() {
		if ( is_admin() ) {
			return;
		}

		$public_user_content = new Service_Tracker_Public_User_Content();
		$this->loader->add_action( 'init', $public_user_content, 'get_user_id' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
