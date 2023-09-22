<?php
namespace ServiceTracker\includes;

defined('WPINC') or die();

use ServiceTracker\includes\STOServiceTrackerLoader;
use ServiceTracker\includes\STOServiceTrackerI18n;
use ServiceTracker\includes\STOServiceTrackerApiCases;
use ServiceTracker\includes\STOServiceTrackerApiProgress;
use ServiceTracker\includes\STOServiceTrackerApiToggle;
use ServiceTracker\admin\STOServiceTrackerAdmin;
use ServiceTracker\publics\STOServiceTrackerPublic;
use ServiceTracker\publics\STOServiceTrackerPublicUserContent;
use ServiceTracker\includes\STOServiceTrackerPermalinkValidator;

// This must be here, since PSR4 determines that define should not be used in an output file
define('SERVICE_TRACKER_VERSION', '1.0.0');

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Service Tracker
 * @subpackage Service Tracker/includes
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
 * @package    Service Tracker
 * @subpackage Service Tracker/includes
 * @author     Rodrigo Del Bem <rodrigodelbem@gmail.com>
 */


class STOServiceTracker
{
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      STOServiceTrackerLoader	$loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $pluginName    The string used to uniquely identify this plugin.
	 */
	protected $pluginName;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected $blockEnqueueBadConfig = false;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('SERVICE_TRACKER_VERSION')) {
			$this->version = SERVICE_TRACKER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->pluginName = 'service-tracker';

		$serviceTrackerPermalinkValidator = new STOServiceTrackerPermalinkValidator();
		if (!$serviceTrackerPermalinkValidator->isPermalinkStructureValid()) {
			$this->blockEnqueueBadConfig = true;
		}

		$this->addCustomerRole();
		$this->loadDependencies();
		$this->setLocale();
		$this->defineAdminHooks();
		$this->api();
		$this->definePublicHooks();
		$this->publicUserContent();
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
	private function loadDependencies()
	{
		$this->loader = new STOServiceTrackerLoader();
	}

	private function api()
	{
		$serviceTrackerApiCases = new STOServiceTrackerApiCases();
		$this->loader->addAction('rest_api_init', $serviceTrackerApiCases, 'run');

		$serviceTrackerApiProgress = new STOServiceTrackerApiProgress();
		$this->loader->addAction('rest_api_init', $serviceTrackerApiProgress, 'run');

		$serviceTrackerApiToggle = new STOServiceTrackerApiToggle();
		$this->loader->addAction('rest_api_init', $serviceTrackerApiToggle, 'run');
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the STOServiceTrackerI18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function setLocale()
	{
		$pluginI18n = new STOServiceTrackerI18n();
		$this->loader->addAction('plugins_loaded', $pluginI18n, 'loadPluginTextdomain');
	}

	private function addCustomerRole()
	{
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			return;
		}
		add_role('customer', __('Customer', 'service-tracker'), get_role('subscriber')->capabilities);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function defineAdminHooks()
	{
		$pluginAdmin = new STOServiceTrackerAdmin($this->getPluginName(), $this->getVersion(), $this->blockEnqueueBadConfig);
		if (!$this->blockEnqueueBadConfig) {
			$this->loader->addAction('admin_enqueue_scripts', $pluginAdmin, 'enqueueStyles');
			$this->loader->addAction('admin_enqueue_scripts', $pluginAdmin, 'enqueueScripts');
			$this->loader->addAction('admin_enqueue_scripts', $pluginAdmin, 'localizeScripts');
		}
		$this->loader->addAction('admin_menu', $pluginAdmin, 'adminPage');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function definePublicHooks()
	{
		if (is_admin()) {
			return;
		}

		$pluginPublic = new STOServiceTrackerPublic($this->getPluginName(), $this->getVersion());

		$this->loader->addAction('wp_enqueue_scripts', $pluginPublic, 'enqueueStyles');
		$this->loader->addAction('wp_enqueue_scripts', $pluginPublic, 'enqueueScripts');

	}

	private function publicUserContent()
	{
		if (is_admin()) {
			return;
		}

		$publicUserContent = new STOServiceTrackerPublicUserContent();
		$this->loader->addAction('init', $publicUserContent, 'getUserId');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function getPluginName()
	{
		return $this->pluginName;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    STOServiceTrackerLoader    Orchestrates the hooks of the plugin.
	 */
	public function getLoader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function getVersion()
	{
		return $this->version;
	}

}