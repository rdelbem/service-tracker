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
class STOServiceTrackerAdmin
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

		wp_enqueue_style($this->pluginName, plugin_dir_url(__FILE__) . 'css/style.css', array(), $this->version, 'all');
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

		wp_enqueue_script($this->pluginName, plugin_dir_url(__FILE__) . 'js/prod/App.js', array('wp-element'), $this->version, false);
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