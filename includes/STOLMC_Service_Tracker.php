<?php
namespace STOLMC_Service_Tracker\includes;

defined( 'ABSPATH' ) || exit;

use STOLMC_Service_Tracker\admin\STOLMC_Service_Tracker_Admin;
use STOLMC_Service_Tracker\includes\Analytics\STOLMC_Analytics_Logger;
use STOLMC_Service_Tracker\includes\Analytics\STOLMC_Analytics_Hooks;
use STOLMC_Service_Tracker\includes\CLI\STOLMC_Service_Tracker_Commands;
use STOLMC_Service_Tracker\includes\DB\STOLMC_Calendar_Index;
use STOLMC_Service_Tracker\includes\DB\STOLMC_Schema_Manager;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Loader;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Permalink_Validator;
use STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public;
use STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public_User_Content;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Service_Factory;

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    STOLMC_Service_Tracker
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
 * @package    STOLMC_Service_Tracker
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
		$this->define_schema_hooks();
		$this->define_analytics_hooks();
		$this->define_cli_commands();
		$this->define_calendar_index_hooks();
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
		$api_controllers = STOLMC_Service_Tracker_Service_Factory::create_api_controllers();
		foreach ( $api_controllers as $api_controller ) {
			$this->loader->add_action( 'rest_api_init', $api_controller, 'run' );
		}
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * WordPress.org loads plugin translations automatically based on
	 * the plugin headers (`Text Domain` and `Domain Path`), so no
	 * explicit runtime hook registration is needed here.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function set_locale(): void {
		add_action( 'init', function () {
			load_plugin_textdomain(
				'service-tracker-stolmc',
				false,
				dirname( plugin_basename( STOLMC_SERVICE_TRACKER_ROOT_FILE ) ) . '/languages'
			);
		} );
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
		add_action( 'init', [ $this, 'register_staff_role' ] );
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
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress hook name.
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
			add_role( 'stolmc_customer', __( 'Service Tracker Client', 'service-tracker-stolmc' ), $capabilities );
		}

		/**
		 * Fires after the customer role has been registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stolmc_service_tracker_role_registered' );
	}

	/**
	 * Register the staff role on init hook.
	 *
	 * Staff users can manage cases and progress. Administrators
	 * are implicitly treated as staff for ownership and assignment purposes.
	 *
	 * @since    1.2.0
	 * @access   public
	 *
	 * @return void
	 */
	public function register_staff_role(): void {
		// Only create the role if it doesn't already exist.
		if ( null === get_role( 'stolmc_staff' ) ) {
			$editor_role = get_role( 'editor' );
			$capabilities = $editor_role ? $editor_role->capabilities : [];

			/**
			 * Filters the capabilities assigned to the staff role.
			 *
			 * @since 1.2.0
			 *
			 * @param array $capabilities The capabilities (defaults to editor).
			 */
			$capabilities = apply_filters( 'stolmc_service_tracker_staff_role_capabilities', $capabilities );

			add_role( 'stolmc_staff', __( 'Staff', 'service-tracker-stolmc' ), $capabilities );
		}

		/**
		 * Fires after the staff role has been registered.
		 *
		 * @since 1.2.0
		 */
		do_action( 'stolmc_service_tracker_staff_role_registered' );
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
	 * Register database schema synchronization hooks.
	 *
	 * The STOLMC_Schema_Manager runs on every `init` to compare the actual
	 * database schema against the declarative definition and apply
	 * any pending ALTER TABLE migrations.  The fast-path version
	 * check costs a single option read and returns immediately when
	 * the schema is current.
	 *
	 * @since    1.1.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_schema_hooks(): void {
		$schema_manager = new STOLMC_Schema_Manager();
		$this->loader->add_action( 'init', $schema_manager, 'sync' );
	}

	/**
	 * Register analytics hooks for activity and notification logging.
	 *
	 * Sets up the STOLMC_Analytics_Logger and STOLMC_Analytics_Hooks classes to
	 * listen to domain events and persist activity/notification records.
	 *
	 * @since    1.2.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_analytics_hooks(): void {
		$analytics = STOLMC_Service_Tracker_Service_Factory::create_analytics_hooks_with_logger();
		$analytics->register_hooks();
	}

	/**
	 * Register WP-CLI commands.
	 *
	 * @since    1.1.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_cli_commands(): void {
		// WP_CLI is only defined when running via WP-CLI.
		// phpcs:ignore Generic.PHP.NoSilencedErrors -- Intentional for WP-CLI check.
		$wp_cli = defined( 'WP_CLI' ) && constant( 'WP_CLI' );
		if ( ! $wp_cli ) {
			return;
		}

		// phpcs:ignore Generic.PHP.NoSilencedErrors -- WP-CLI class only exists when running WP-CLI.
		if ( class_exists( '\WP_CLI' ) ) {
			\WP_CLI::add_command( 'stolmc-service-tracker', STOLMC_Service_Tracker_Commands::class );
		}
	}

	/**
	 * Register hooks to keep the calendar date index in sync.
	 *
	 * The index is rebuilt on every case create, update, or delete
	 * event so the frontend calendar can render accurate start/end
	 * indicators without scanning every case.
	 *
	 * @since    1.1.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_calendar_index_hooks(): void {
		$this->loader->add_action( 'stolmc_service_tracker_case_created', $this, 'rebuild_calendar_index' );
		$this->loader->add_action( 'stolmc_service_tracker_case_updated', $this, 'rebuild_calendar_index' );
		$this->loader->add_action( 'stolmc_service_tracker_case_before_delete', $this, 'rebuild_calendar_index' );
	}

	/**
	 * Rebuild the calendar date index (proxy method for hook registration).
	 *
	 * @since    1.1.0
	 * @access   public
	 *
	 * @return void
	 */
	public function rebuild_calendar_index(): void {
		STOLMC_Calendar_Index::rebuild();
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

		global $wpdb;

			// Create SQL services using factory.
		$cases_sql = STOLMC_Service_Tracker_Service_Factory::create_sql_service( $wpdb->prefix . 'servicetracker_cases' );
		$progress_sql = STOLMC_Service_Tracker_Service_Factory::create_sql_service( $wpdb->prefix . 'servicetracker_progress' );

		$public_user_content = new STOLMC_Service_Tracker_Public_User_Content( $cases_sql, $progress_sql );
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
