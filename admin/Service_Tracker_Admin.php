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
class Service_Tracker_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/style.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/app.js', array( 'wp-element' ), $this->version, false );

	}

	public function localize_scripts( $hook ) {
		if ( 'toplevel_page_service_tracker' !== $hook ) {
			return;
		}

		wp_localize_script(
			$this->plugin_name,
			'data',
			array(
				'root_url'                     => get_site_url(),
				'api_url'                      => 'service-tracker/v1',
				'nonce'                        => wp_create_nonce( 'wp_rest' ),
				'search_bar'                   => __( 'Search for a client', 'service-tracker' ),
				'home_screen'                  => __( 'Click on a client name, to se hers/his cases!', 'service-tracker' ),
				'btn_add_case'                 => __( 'Add case', 'service-tracker' ),
				'no_cases_yet'                 => __( 'No cases yet! Include a new one!', 'service-tracker' ),
				'case_name'                    => __( 'Case name', 'service-tracker' ),
				'tip_edit_case'                => __( 'Edit the name of this case', 'service-tracker' ),
				'tip_toggle_case_open'         => __( 'This case is open! Click to close it.', 'service-tracker' ),
				'tip_toggle_case_close'        => __( 'This case is closed! Click to open it.', 'service-tracker' ),
				'tip_delete_case'              => __( 'Delete this case', 'service-tracker' ),
				'btn_save_case'                => __( 'Save', 'service-tracker' ),
				'btn_dismiss_edit'             => __( 'Dismiss', 'service-tracker' ),
				'title_progress_page'          => __( 'Progress for case', 'service-tracker' ),
				'new_status_btn'               => __( 'New Status', 'service-tracker' ),
				'close_box_btn'                => __( 'Close box', 'service-tracker' ),
				'add_status_btn'               => __( 'Add this status', 'service-tracker' ),
				'tip_edit_status'              => __( 'Edit this status', 'service-tracker' ),
				'tip_delete_status'            => __( 'Delete this status', 'service-tracker' ),
				'btn_save_changes_status'      => __( 'Save changes', 'service-tracker' ),
				'toast_case_added'             => __( 'Case added!', 'service-tracker' ),
				'toast_toggle_base_msg'        => __( 'Case is now', 'service-tracker' ),
				'toast_toggle_state_open_msg'  => __( 'open', 'service-tracker' ),
				'toast_toggle_state_close_msg' => __( 'closed', 'service-tracker' ),
				'toast_case_deleted'           => __( 'Case deleted!', 'service-tracker' ),
				'toast_case_edited'            => __( 'Case edited!', 'service-tracker' ),
				'toast_status_added'           => __( 'Status added!', 'service-tracker' ),
				'toast_status_deleted'         => __( 'Status deleted!', 'service-tracker' ),
				'toast_status_edited'          => __( 'Status edited!', 'service-tracker' ),
				'confirm_delete_case'          => __( 'Do you want to delete the case under the name', 'service-tracker' ),
				'confirm_delete_status'        => __( 'Do you want to delete the status created in', 'service-tracker' ),
				'alert_blank_case_title'       => __( 'Case title can not be blank', 'service-tracker' ),
				'alert_blank_status_title'     => __( 'Status text can not be blank', 'service-tracker' ),
				'alert_error_base'             => __( 'It was impossible to complete this task. We had an error', 'service-tracker' ),
			)
		);
	}

	public function admin_page() {
		add_menu_page( 'Service Tracker', 'Service Tracker', 'manage_options', 'service_tracker', array( $this, 'admin_index' ), 'dashicons-money', 10 );
	}

	public function admin_index() {
		echo '<div id="root"></div>';
	}

}
