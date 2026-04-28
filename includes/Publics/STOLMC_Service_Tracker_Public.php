<?php
namespace STOLMC_Service_Tracker\includes\Publics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    STOLMC_Service_Tracker
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    STOLMC_Service_Tracker
 * @author     Rodrigo Del Bem <rodrigodelbem@gmail.com>
 */
class STOLMC_Service_Tracker_Public {


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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( string $plugin_name, string $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		if ( ! has_shortcode( get_the_content(), 'stolmc-service-tracker-cases-progress' ) ) {
			return;
		}

		/**
		 * Filters whether to enqueue public-facing styles.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $enqueue     Whether to enqueue styles.
		 * @param string $plugin_name The plugin name.
		 */
		if ( apply_filters( 'stolmc_service_tracker_public_enqueue_styles', true, $this->plugin_name ) ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/service-tracker-public.css', [], $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * NOTE: This is yet not used, since we do not have any JS in the user
	 * facing part of the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		if ( ! has_shortcode( get_the_content(), 'stolmc-service-tracker-cases-progress' ) ) {
			return;
		}

		/**
		 * Filters whether to enqueue public-facing scripts.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $enqueue     Whether to enqueue scripts.
		 * @param string $plugin_name The plugin name.
		 */
		$enqueue = apply_filters( 'stolmc_service_tracker_public_enqueue_scripts', false, $this->plugin_name );
		if ( ! $enqueue ) {
			return;
		}

		// Intentionally empty: JavaScript is not currently used on the public-facing side.
	}
}
