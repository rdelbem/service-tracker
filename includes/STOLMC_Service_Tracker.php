<?php
namespace STOLMC_Service_Tracker\includes;

use STOLMC_Service_Tracker\admin\STOLMC_Service_Tracker_Admin;
use STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Cases;
use STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Progress;
use STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Toggle;
use STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Users;
use STOLMC_Service_Tracker\includes\I18n\STOLMC_Service_Tracker_I18n;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Loader;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Permalink_Validator;
use STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public;
use STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public_User_Content;

// This must be here, since PSR4 determines that define should not be used in an output file.
define( 'STOLMC_SERVICE_TRACKER_VERSION', '1.0.0' );

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Service_Tracker
 * @subpackage Service_Tracker/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * It is also used to load our API end points.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Service_Tracker
 * @subpackage Service_Tracker/includes
 * @author     Rodrigo Del Bem <rodrigodelbem@gmail.com>
 */
class STOLMC_Service_Tracker {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      STOLMC_Service_Tracker_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Whether to block enqueueing scripts due to bad permalink configuration.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      bool $block_enqueue_bad_config Flag to block script enqueue.
	 */
	protected $block_enqueue_bad_config = false;

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
		if ( defined( 'STOLMC_SERVICE_TRACKER_VERSION' ) ) {
			$this->version = STOLMC_SERVICE_TRACKER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'service-tracker-stolmc';

		$service_tracker_permalink_validator = new STOLMC_Service_Tracker_Permalink_Validator();
		if ( ! $service_tracker_permalink_validator->is_permalink_structure_valid() ) {
			$this->block_enqueue_bad_config = true;
		}

		$this->add_customer_role();

		/**
		 * Fires before the plugin initializes its dependencies and hooks.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker $instance The plugin instance.
		 */
		do_action( 'stolmc_service_tracker_before_init', $this );

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
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		$this->loader = new STOLMC_Service_Tracker_Loader();

		/**
		 * Fires after the plugin dependencies have been loaded.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker_Loader $loader The plugin loader instance.
		 */
		do_action( 'stolmc_service_tracker_dependencies_loaded', $this->loader );
	}

	/**
	 * Initialize and register API endpoints.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function api(): void {
		$service_tracker_api_cases = new STOLMC_Service_Tracker_Api_Cases();
		$this->loader->add_action( 'rest_api_init', $service_tracker_api_cases, 'run' );

		$service_tracker_api_progress = new STOLMC_Service_Tracker_Api_Progress();
		$this->loader->add_action( 'rest_api_init', $service_tracker_api_progress, 'run' );

		$service_tracker_api_toggle = new STOLMC_Service_Tracker_Api_Toggle();
		$this->loader->add_action( 'rest_api_init', $service_tracker_api_toggle, 'run' );

		$service_tracker_api_users = new STOLMC_Service_Tracker_Api_Users();
		$this->loader->add_action( 'rest_api_init', $service_tracker_api_users, 'run' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the STOLMC_Service_Tracker_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function set_locale(): void {
		$plugin_i18n = new STOLMC_Service_Tracker_I18n();
		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Add the customer role if WooCommerce is not active.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function add_customer_role(): void {
		add_action( 'init', [ $this, 'register_customer_role' ] );
	}

	/**
	 * Register the customer role on init hook.
	 *
	 * @since    1.0.1
	 * @access   public
	 *
	 * @return void
	 */
	public function register_customer_role(): void {
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			return;
		}
		$subscriber_role = get_role( 'subscriber' );
		if ( null !== $subscriber_role ) {
			/**
			 * Filters the capabilities assigned to the customer role.
			 *
			 * @since 1.0.0
			 *
			 * @param array $capabilities The capabilities from the subscriber role.
			 */
			$capabilities = apply_filters( 'stolmc_service_tracker_customer_role_capabilities', $subscriber_role->capabilities );
			add_role( 'customer', __( 'Customer', 'service-tracker-stolmc' ), $capabilities );
		}

		/**
		 * Fires after the customer role has been registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stolmc_service_tracker_role_registered' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_admin_hooks(): void {
		$plugin_admin = new STOLMC_Service_Tracker_Admin( $this->get_plugin_name(), $this->get_version(), $this->block_enqueue_bad_config );
		if ( ! $this->block_enqueue_bad_config ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'localize_scripts' );
		}
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_page' );

		/**
		 * Fires after admin hooks have been defined.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker_Admin $plugin_admin The admin instance.
		 */
		do_action( 'stolmc_service_tracker_admin_hooks_defined', $plugin_admin );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_public_hooks(): void {
		if ( is_admin() ) {
			return;
		}

		$plugin_public = new STOLMC_Service_Tracker_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		/**
		 * Fires after public-facing hooks have been defined.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker_Public $plugin_public The public instance.
		 */
		do_action( 'stolmc_service_tracker_public_hooks_defined', $plugin_public );
	}

	/**
	 * Register public-facing user content hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function public_user_content(): void {
		if ( is_admin() ) {
			return;
		}

		$public_user_content = new STOLMC_Service_Tracker_Public_User_Content();
		$this->loader->add_action( 'init', $public_user_content, 'get_user_id' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function run(): void {
		/**
		 * Fires before the plugin loader runs.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker $instance The plugin instance.
		 */
		do_action( 'stolmc_service_tracker_running', $this );

		$this->loader->run();

		/**
		 * Fires after the plugin has been fully initialized.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker $instance The plugin instance.
		 */
		do_action( 'stolmc_service_tracker_initialized', $this );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    STOLMC_Service_Tracker_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader(): STOLMC_Service_Tracker_Loader {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version(): string {
		return $this->version;
	}
}
