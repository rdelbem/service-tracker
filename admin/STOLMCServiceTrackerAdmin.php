<?php
namespace STOLMCServiceTracker\admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
class STOLMCServiceTrackerAdmin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pluginName    The ID of this plugin.
	 */
	private $pluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $blockEnqueueBadConfig;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct($pluginName, $version, $blockEnqueueBadConfig)
	{
		$this->pluginName = $pluginName;
		$this->version = $version;
		$this->blockEnqueueBadConfig = $blockEnqueueBadConfig;
		
		// Add hooks to remove WordPress admin elements on our page
		add_action('admin_init', array($this, 'removeWpAdminElements'));
	}

	/**
	 * Remove WordPress admin elements from our plugin page
	 */
	public function removeWpAdminElements()
	{
		if (isset($_GET['page']) && $_GET['page'] === 'service_tracker') {
			// Remove footer text
			add_filter('admin_footer_text', '__return_empty_string', 9999);
			add_filter('update_footer', '__return_empty_string', 9999);
			
			// Remove help tabs
			add_action('admin_head', array($this, 'removeHelpTabs'));
			
			// Add custom CSS to hide remaining elements
			add_action('admin_head', array($this, 'hideWpElements'));
		}
	}

	public function removeHelpTabs()
	{
		$screen = get_current_screen();
		$screen->remove_help_tabs();
	}

	public function hideWpElements()
	{
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
	 */
	public function enqueueStyles($hook)
	{
		if ('toplevel_page_service_tracker' !== $hook) {
			return;
		}

		// Load Google Fonts (Inter, Manrope, Material Symbols)
		wp_enqueue_style(
			$this->pluginName . '-google-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800;900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap',
			array(),
			null
		);

		// Load the new Tailwind CSS file from the build
		$css_file = plugin_dir_path(__FILE__) . 'js/prod/style.css';
		if (file_exists($css_file)) {
			wp_enqueue_style($this->pluginName . '-tailwind', plugin_dir_url(__FILE__) . 'js/prod/style.css', array(), $this->version, 'all');
		} else {
			// Fallback to old CSS if new one doesn't exist
			wp_enqueue_style($this->pluginName, plugin_dir_url(__FILE__) . 'css/style.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts($hook)
	{
		if ('toplevel_page_service_tracker' !== $hook) {
			return;
		}

		wp_enqueue_script($this->pluginName, plugin_dir_url(__FILE__) . 'js/prod/App.js', array(), $this->version, true);

		// Add type="module" to enable ES module syntax (dynamic imports for code splitting)
		add_filter('script_loader_tag', function($tag, $handle) {
			if ($handle === $this->pluginName) {
				return str_replace('<script ', '<script type="module" ', $tag);
			}
			return $tag;
		}, 10, 2);
	}

	public function localizeScripts($hook)
	{
		if ('toplevel_page_service_tracker' !== $hook) {
			return;
		}

		// This file has all the texts inside a variable $texts_array
		include wp_normalize_path(plugin_dir_path(__FILE__) . 'translation/texts_array.php');

		wp_localize_script(
			$this->pluginName,
			'data',
			$texts_array
		);
	}

	public function adminPage()
	{
		add_menu_page('Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', array($this, 'adminIndex'), 'dashicons-money', 10);
	}

	public function adminIndex()
	{
		if ($this->blockEnqueueBadConfig) {
			include wp_normalize_path(plugin_dir_path(__FILE__) . 'partials/admin_page_bad_config.php');
			return;
		}
		include wp_normalize_path(plugin_dir_path(__FILE__) . 'partials/admin_page.php');
	}
}