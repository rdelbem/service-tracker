<?php
/**
 * Aggregated UI copy for the Service Tracker React application.
 *
 * Every user-facing string displayed by the React app MUST live here.
 * Keys are grouped by feature area and prefixed to avoid collisions.
 *
 * To add a new string:
 *   1. Add it to the appropriate section below.
 *   2. Reference it in React via `data.your_key`.
 *
 * @package    STOLMC_Service_Tracker
 * @since      2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return [

	// -------------------------------------------------------------------------
	// Search & navigation
	// -------------------------------------------------------------------------
	'search_bar'                       => __( 'Search for a client', 'service-tracker-stolmc' ),
	'home_screen'                      => __( 'Click on a client name, to se hers/his cases!', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Sidebar
	// -------------------------------------------------------------------------
	'nav_dashboard'                    => __( 'Dashboard', 'service-tracker-stolmc' ),
	'nav_clients'                      => __( 'Clients', 'service-tracker-stolmc' ),
	'nav_cases'                        => __( 'Cases', 'service-tracker-stolmc' ),
	'nav_calendar'                     => __( 'Calendar', 'service-tracker-stolmc' ),
	'nav_analytics'                    => __( 'Analytics', 'service-tracker-stolmc' ),
	'nav_settings'                     => __( 'Settings', 'service-tracker-stolmc' ),
	'brand_name'                       => __( 'Service Tracker', 'service-tracker-stolmc' ),
	'role_admin'                       => __( 'Administrator', 'service-tracker-stolmc' ),
	'role_master'                      => __( 'Master Admin', 'service-tracker-stolmc' ),
	'fallback_admin_user'              => __( 'Admin User', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Cases – list view
	// -------------------------------------------------------------------------
	'btn_add_case'                     => __( 'Add case', 'service-tracker-stolmc' ),
	'no_cases_yet'                     => __( 'No cases yet! Include a new one!', 'service-tracker-stolmc' ),
	'case_name'                        => __( 'Case name', 'service-tracker-stolmc' ),
	'cases_heading'                    => __( 'Cases', 'service-tracker-stolmc' ),
	'cases_search_placeholder'         => __( 'Search cases by title or client name...', 'service-tracker-stolmc' ),
	'cases_empty_search'               => __( 'No cases found', 'service-tracker-stolmc' ),
	'cases_retry'                      => __( 'Retry', 'service-tracker-stolmc' ),
	'cases_create_first'               => __( 'Create your first case', 'service-tracker-stolmc' ),
	'cases_add_new'                    => __( 'Add new case', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Cases – single case
	// -------------------------------------------------------------------------
	'tip_edit_case'                    => __( 'Edit the name of this case', 'service-tracker-stolmc' ),
	'tip_toggle_case_open'             => __( 'This case is open! Click to close it.', 'service-tracker-stolmc' ),
	'tip_toggle_case_close'            => __( 'This case is closed! Click to open it.', 'service-tracker-stolmc' ),
	'tip_delete_case'                  => __( 'Delete this case', 'service-tracker-stolmc' ),
	'btn_save_case'                    => __( 'Save', 'service-tracker-stolmc' ),
	'btn_dismiss_edit'                 => __( 'Dismiss', 'service-tracker-stolmc' ),
	'status_active'                    => __( 'Active', 'service-tracker-stolmc' ),
	'status_closed'                    => __( 'Closed', 'service-tracker-stolmc' ),
	'status_unknown'                   => __( 'Unknown', 'service-tracker-stolmc' ),
	'case_created_prefix'              => __( 'Created:', 'service-tracker-stolmc' ),
	'tip_view_progress'                => __( 'View Progress', 'service-tracker-stolmc' ),
	'case_edit_placeholder'            => __( 'Enter new title...', 'service-tracker-stolmc' ),
	'case_not_found'                   => __( 'Case not found', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Cases – details view
	// -------------------------------------------------------------------------
	'label_status'                     => __( 'Status', 'service-tracker-stolmc' ),
	'label_created'                    => __( 'Created', 'service-tracker-stolmc' ),
	'label_start_date'                 => __( 'Start Date', 'service-tracker-stolmc' ),
	'label_due_date'                   => __( 'Due Date', 'service-tracker-stolmc' ),
	'label_description'                => __( 'Description', 'service-tracker-stolmc' ),
	'not_set'                          => __( 'Not set', 'service-tracker-stolmc' ),
	'no_description'                   => __( 'No description provided.', 'service-tracker-stolmc' ),
	'btn_back_to_cases'                => __( 'Back to Cases List', 'service-tracker-stolmc' ),
	'case_owner_label'                 => __( 'Case Owner', 'service-tracker-stolmc' ),
	'case_owner_unassigned'            => __( 'Unassigned', 'service-tracker-stolmc' ),
	'case_owner_admin_suffix'          => __( '(Admin)', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Cases – confirmations
	// -------------------------------------------------------------------------
	'confirm_delete_case'              => __( 'Do you want to delete the case under the name', 'service-tracker-stolmc' ),
	'confirm_delete_case_title'        => __( 'Delete Case', 'service-tracker-stolmc' ),
	'confirm_delete_case_msg'          => __( 'Are you sure you want to delete this case? This will also delete all associated progress updates.', 'service-tracker-stolmc' ),
	'confirm_close_case_title'         => __( 'Close Case', 'service-tracker-stolmc' ),
	'confirm_reopen_case_title'        => __( 'Reopen Case', 'service-tracker-stolmc' ),
	'confirm_close_case_msg'           => __( 'Are you sure you want to close this case?', 'service-tracker-stolmc' ),
	'confirm_reopen_case_msg'          => __( 'Are you sure you want to reopen this case?', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Progress / status updates
	// -------------------------------------------------------------------------
	'title_progress_page'              => __( 'Progress for case', 'service-tracker-stolmc' ),
	'new_status_btn'                   => __( 'New Status', 'service-tracker-stolmc' ),
	'close_box_btn'                    => __( 'Close box', 'service-tracker-stolmc' ),
	'add_status_btn'                   => __( 'Add this status', 'service-tracker-stolmc' ),
	'tip_edit_status'                  => __( 'Edit this status', 'service-tracker-stolmc' ),
	'tip_delete_status'                => __( 'Delete this status', 'service-tracker-stolmc' ),
	'btn_save_changes_status'          => __( 'Save changes', 'service-tracker-stolmc' ),
	'no_progress_yet'                  => __( 'No progress is registered for this case.', 'service-tracker-stolmc' ),
	'progress_heading'                 => __( 'Progress Update', 'service-tracker-stolmc' ),
	'progress_placeholder'             => __( 'Type progress details here...', 'service-tracker-stolmc' ),
	'progress_files_label'             => __( 'Files to Attach:', 'service-tracker-stolmc' ),
	'progress_attach_files'            => __( 'Attach Files', 'service-tracker-stolmc' ),
	'progress_add_image'               => __( 'Add Image', 'service-tracker-stolmc' ),
	'progress_uploading'               => __( 'Uploading...', 'service-tracker-stolmc' ),
	'progress_post_btn'                => __( 'Post Update', 'service-tracker-stolmc' ),
	'progress_notify_badge'            => __( 'Client will be notified', 'service-tracker-stolmc' ),
	'activity_log_heading'             => __( 'Activity Log', 'service-tracker-stolmc' ),
	'activity_log_empty_desc'          => __( 'Add your first status update to get started', 'service-tracker-stolmc' ),
	'client_attachments_heading'       => __( 'Client Attachments', 'service-tracker-stolmc' ),
	'progress_tab'                     => __( 'Progress', 'service-tracker-stolmc' ),
	'attachments_tab'                  => __( 'All Attachments', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Progress – confirmations
	// -------------------------------------------------------------------------
	'confirm_delete_status'            => __( 'Do you want to delete the status created in', 'service-tracker-stolmc' ),
	'confirm_delete_status_title'      => __( 'Delete Progress Update', 'service-tracker-stolmc' ),
	'confirm_delete_status_msg'        => __( 'Are you sure you want to delete this progress update?', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Toast messages
	// -------------------------------------------------------------------------
	'toast_case_added'                 => __( 'Case added!', 'service-tracker-stolmc' ),
	'toast_case_deleted'               => __( 'Case deleted!', 'service-tracker-stolmc' ),
	'toast_case_edited'                => __( 'Case edited!', 'service-tracker-stolmc' ),
	'toast_case_toggled'               => __( 'Case status updated!', 'service-tracker-stolmc' ),
	'toast_toggle_base_msg'            => __( 'Case is now', 'service-tracker-stolmc' ),
	'toast_toggle_state_open_msg'      => __( 'open', 'service-tracker-stolmc' ),
	'toast_toggle_state_close_msg'     => __( 'closed', 'service-tracker-stolmc' ),
	'toast_status_added'               => __( 'Status added!', 'service-tracker-stolmc' ),
	'toast_status_deleted'             => __( 'Status deleted!', 'service-tracker-stolmc' ),
	'toast_status_edited'              => __( 'Status edited!', 'service-tracker-stolmc' ),
	'toast_case_deleted_success'       => __( 'Case deleted successfully', 'service-tracker-stolmc' ),
	'toast_case_title_updated'         => __( 'Case title updated successfully', 'service-tracker-stolmc' ),
	'toast_owner_changed'              => __( 'Case owner changed', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Alerts & validation
	// -------------------------------------------------------------------------
	'alert_blank_case_title'           => __( 'Case title can not be blank', 'service-tracker-stolmc' ),
	'alert_blank_status_title'         => __( 'Status text can not be blank', 'service-tracker-stolmc' ),
	'alert_error_base'                 => __( 'It was impossible to complete this task. We had an error', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Clients view
	// -------------------------------------------------------------------------
	'clients_heading'                  => __( 'Clients', 'service-tracker-stolmc' ),
	'clients_search_placeholder'       => __( 'Search clients...', 'service-tracker-stolmc' ),
	'clients_empty'                    => __( 'No clients found', 'service-tracker-stolmc' ),
	'clients_empty_search'             => __( 'No clients found for this search', 'service-tracker-stolmc' ),
	'clients_add_first'                => __( 'Add your first client to get started', 'service-tracker-stolmc' ),
	'clients_add_btn'                  => __( 'Add Client', 'service-tracker-stolmc' ),
	'clients_creating'                 => __( 'Creating...', 'service-tracker-stolmc' ),
	'clients_create_btn'               => __( 'Create Client', 'service-tracker-stolmc' ),
	'label_name'                       => __( 'Name', 'service-tracker-stolmc' ),
	'label_email'                      => __( 'Email', 'service-tracker-stolmc' ),
	'label_phone'                      => __( 'Phone', 'service-tracker-stolmc' ),
	'label_cellphone'                  => __( 'Cellphone', 'service-tracker-stolmc' ),
	'placeholder_name'                 => __( 'Client name...', 'service-tracker-stolmc' ),
	'placeholder_email'                => __( 'client@example.com', 'service-tracker-stolmc' ),
	'placeholder_phone'                => __( '(123) 456-7890', 'service-tracker-stolmc' ),
	'clients_name_email_required'      => __( 'Name and email are required', 'service-tracker-stolmc' ),
	// translators: %1$d: number of results, %2$s: search term.
	'clients_results_for'              => __( '%1$d result(s) for "%2$s"', 'service-tracker-stolmc' ),
	// translators: %d: total number of clients.
	'clients_total'                    => __( '%d client(s) total', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Client details
	// -------------------------------------------------------------------------
	'client_label'                     => __( 'Client', 'service-tracker-stolmc' ),
	'client_since'                     => __( 'Client Since', 'service-tracker-stolmc' ),
	'client_contact_heading'           => __( 'Contact Information', 'service-tracker-stolmc' ),
	'client_cases_heading'             => __( 'Client Cases', 'service-tracker-stolmc' ),
	'client_no_cases'                  => __( 'No cases found for this client', 'service-tracker-stolmc' ),
	'client_back_to_list'              => __( 'Back to Clients List', 'service-tracker-stolmc' ),
	'client_update_success'            => __( 'Client information updated successfully', 'service-tracker-stolmc' ),
	'client_update_error'              => __( 'Failed to update client information', 'service-tracker-stolmc' ),
	'client_invalid_email'             => __( 'Please enter a valid email address', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Add case form
	// -------------------------------------------------------------------------
	'add_case_heading'                 => __( 'Add New Case', 'service-tracker-stolmc' ),
	'add_case_description'             => __( 'Create a new service case for a client', 'service-tracker-stolmc' ),
	'add_case_client_label'            => __( 'Client', 'service-tracker-stolmc' ),
	'add_case_client_placeholder'      => __( 'Search for a client...', 'service-tracker-stolmc' ),
	'add_case_title_label'             => __( 'Case Title', 'service-tracker-stolmc' ),
	'add_case_title_placeholder'       => __( 'Enter case title...', 'service-tracker-stolmc' ),
	'add_case_status_label'            => __( 'Status', 'service-tracker-stolmc' ),
	'add_case_description_label'       => __( 'Description', 'service-tracker-stolmc' ),
	'add_case_description_placeholder' => __( 'Enter case description...', 'service-tracker-stolmc' ),
	'add_case_date_range_label'        => __( 'Date Range (Optional)', 'service-tracker-stolmc' ),
	'add_case_start_date_label'        => __( 'Start Date', 'service-tracker-stolmc' ),
	'add_case_due_date_label'          => __( 'Due Date', 'service-tracker-stolmc' ),
	'add_case_date_help'               => __( 'Leave blank if no specific dates are needed.', 'service-tracker-stolmc' ),
	'add_case_creating'                => __( 'Creating...', 'service-tracker-stolmc' ),
	'add_case_create_btn'              => __( 'Create Case', 'service-tracker-stolmc' ),
	'add_case_select_client'           => __( 'Please select a client', 'service-tracker-stolmc' ),
	'add_case_enter_title'             => __( 'Please enter a case title', 'service-tracker-stolmc' ),
	'add_case_error'                   => __( 'Failed to create case. Please try again.', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Calendar
	// -------------------------------------------------------------------------
	'calendar_heading'                 => __( 'Calendar', 'service-tracker-stolmc' ),
	'calendar_description'             => __( 'View cases and progress updates across all clients', 'service-tracker-stolmc' ),
	'calendar_all_clients'             => __( 'All Clients', 'service-tracker-stolmc' ),
	'calendar_all_statuses'            => __( 'All Statuses', 'service-tracker-stolmc' ),
	'calendar_case_starts'             => __( 'Case Starts', 'service-tracker-stolmc' ),
	'calendar_case_ends'               => __( 'Case Ends', 'service-tracker-stolmc' ),
	'calendar_more'                    => __( 'more', 'service-tracker-stolmc' ),
	'calendar_weekdays'                => [
		__( 'Sun', 'service-tracker-stolmc' ),
		__( 'Mon', 'service-tracker-stolmc' ),
		__( 'Tue', 'service-tracker-stolmc' ),
		__( 'Wed', 'service-tracker-stolmc' ),
		__( 'Thu', 'service-tracker-stolmc' ),
		__( 'Fri', 'service-tracker-stolmc' ),
		__( 'Sat', 'service-tracker-stolmc' ),
	],
	'calendar_months'                  => [
		__( 'January', 'service-tracker-stolmc' ),
		__( 'February', 'service-tracker-stolmc' ),
		__( 'March', 'service-tracker-stolmc' ),
		__( 'April', 'service-tracker-stolmc' ),
		__( 'May', 'service-tracker-stolmc' ),
		__( 'June', 'service-tracker-stolmc' ),
		__( 'July', 'service-tracker-stolmc' ),
		__( 'August', 'service-tracker-stolmc' ),
		__( 'September', 'service-tracker-stolmc' ),
		__( 'October', 'service-tracker-stolmc' ),
		__( 'November', 'service-tracker-stolmc' ),
		__( 'December', 'service-tracker-stolmc' ),
	],

	// -------------------------------------------------------------------------
	// Analytics
	// -------------------------------------------------------------------------
	'analytics_heading'                => __( 'Analytics', 'service-tracker-stolmc' ),
	'analytics_description'            => __( 'Operational metrics and activity insights', 'service-tracker-stolmc' ),
	'analytics_time_period'            => __( 'Time Period:', 'service-tracker-stolmc' ),
	'analytics_tab_summary'            => __( 'Summary', 'service-tracker-stolmc' ),
	'analytics_tab_customers'          => __( 'Customers', 'service-tracker-stolmc' ),
	'analytics_tab_admins'             => __( 'Admins', 'service-tracker-stolmc' ),
	'analytics_tab_trends'             => __( 'Trends', 'service-tracker-stolmc' ),
	'analytics_total_customers'        => __( 'Total Customers', 'service-tracker-stolmc' ),
	'analytics_total_cases'            => __( 'Total Cases', 'service-tracker-stolmc' ),
	'analytics_open_cases'             => __( 'Open Cases', 'service-tracker-stolmc' ),
	'analytics_closed_cases'           => __( 'Closed Cases', 'service-tracker-stolmc' ),
	'analytics_progress_updates'       => __( 'Progress Updates', 'service-tracker-stolmc' ),
	'analytics_emails_sent'            => __( 'Emails Sent', 'service-tracker-stolmc' ),
	'analytics_emails_attempted'       => __( 'attempted', 'service-tracker-stolmc' ),
	'analytics_failed_emails'          => __( 'Failed Emails', 'service-tracker-stolmc' ),
	'analytics_active_admins'          => __( 'Active Admins (30d)', 'service-tracker-stolmc' ),
	'analytics_no_data'                => __( 'No analytics data available', 'service-tracker-stolmc' ),
	'analytics_never'                  => __( 'Never', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Analytics – table headers
	// -------------------------------------------------------------------------
	'analytics_col_customer'           => __( 'Customer', 'service-tracker-stolmc' ),
	'analytics_col_total_cases'        => __( 'Total Cases', 'service-tracker-stolmc' ),
	'analytics_col_open'               => __( 'Open', 'service-tracker-stolmc' ),
	'analytics_col_closed'             => __( 'Closed', 'service-tracker-stolmc' ),
	'analytics_col_progress'           => __( 'Progress', 'service-tracker-stolmc' ),
	'analytics_col_emails'             => __( 'Emails', 'service-tracker-stolmc' ),
	'analytics_col_last_activity'      => __( 'Last Activity', 'service-tracker-stolmc' ),
	'analytics_col_admin'              => __( 'Admin/Staff', 'service-tracker-stolmc' ),
	'analytics_col_cases_created'      => __( 'Cases Created', 'service-tracker-stolmc' ),
	'analytics_col_cases_updated'      => __( 'Cases Updated', 'service-tracker-stolmc' ),
	'analytics_col_cases_deleted'      => __( 'Cases Deleted', 'service-tracker-stolmc' ),
	'analytics_col_progress_added'     => __( 'Progress Added', 'service-tracker-stolmc' ),
	'analytics_col_emails_triggered'   => __( 'Emails Triggered', 'service-tracker-stolmc' ),
	'analytics_no_customer_data'       => __( 'No customer data available', 'service-tracker-stolmc' ),
	'analytics_no_admin_data'          => __( 'No admin activity recorded', 'service-tracker-stolmc' ),
	'analytics_no_trends_data'         => __( 'No data available', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Settings
	// -------------------------------------------------------------------------
	'settings_heading'                 => __( 'Settings', 'service-tracker-stolmc' ),
	'settings_description'             => __( 'Manage your preferences and application settings', 'service-tracker-stolmc' ),
	'settings_appearance'              => __( 'Appearance', 'service-tracker-stolmc' ),
	'settings_dark_mode'               => __( 'Dark Mode', 'service-tracker-stolmc' ),
	'settings_dark_mode_desc'          => __( 'Toggle between light and dark themes', 'service-tracker-stolmc' ),
	'settings_theme_preview'           => __( 'Theme Preview', 'service-tracker-stolmc' ),
	'settings_theme_dark'              => __( 'Dark', 'service-tracker-stolmc' ),
	'settings_theme_light'             => __( 'Light', 'service-tracker-stolmc' ),
	'settings_account'                 => __( 'Account', 'service-tracker-stolmc' ),
	'settings_user_info'               => __( 'User Information', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// How to use / instructions
	// -------------------------------------------------------------------------
	'instructions_page_title'          => __( 'How to use this plugin', 'service-tracker-stolmc' ),
	'accordion_first_title'            => __( 'Display info for customers access', 'service-tracker-stolmc' ),
	'first_accordion_first_li_item'    => __(
		'Create a secured page, one that is only available after login. (there are some approaches in order to achieve this result, find one that suits you website better)',
		'service-tracker-stolmc'
	),
	'first_accordion_second_li_item'   => __( 'Copy and paste the following short code to the restricted page, [service-tracker-stolmc-cases-progress]', 'service-tracker-stolmc' ),
	'first_accordion_third_li_item'    => __( 'Now, every new status registered in a case/service will be displayed for that respective customer.', 'service-tracker-stolmc' ),
	'first_accordion_forth_li_item'    => __(
		'If you do not want to have a restricted customer page that is perfectly fine. Every new status triggers a email send which contains such status.',
		'service-tracker-stolmc'
	),
	'accordion_second_title'           => __( 'Customers\' notifications', 'service-tracker-stolmc' ),
	'second_accordion_firt_li_item'    => __( 'Everytime a new status is registered for a case, an email is sent to its respective customer.', 'service-tracker-stolmc' ),
	'second_accordion_second_li_item'  => __( 'This plugin uses the default wp_mail function to send its emails. So, it is highly recomended to use WP Mail SMTP OR other smtp plugin alongside Service Tracker, in order to avoid lost emails.', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// User attachments
	// -------------------------------------------------------------------------
	'attachments_loading'              => __( 'Loading attachments...', 'service-tracker-stolmc' ),
	'attachments_empty'                => __( 'No attachments found for this client', 'service-tracker-stolmc' ),
	'attachments_filter_label'         => __( 'Filter by case:', 'service-tracker-stolmc' ),
	'attachments_all_cases'            => __( 'All Cases', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Generic UI
	// -------------------------------------------------------------------------
	'btn_save'                         => __( 'Save', 'service-tracker-stolmc' ),
	'btn_cancel'                       => __( 'Cancel', 'service-tracker-stolmc' ),
	'btn_delete'                       => __( 'Delete', 'service-tracker-stolmc' ),
	'btn_confirm'                      => __( 'Confirm', 'service-tracker-stolmc' ),
	'btn_ok'                           => __( 'OK', 'service-tracker-stolmc' ),
	'btn_close'                        => __( 'Close', 'service-tracker-stolmc' ),
	'btn_edit'                         => __( 'Edit', 'service-tracker-stolmc' ),
	'btn_back'                         => __( 'Back', 'service-tracker-stolmc' ),
	'btn_prev'                         => __( 'Prev', 'service-tracker-stolmc' ),
	'btn_next'                         => __( 'Next', 'service-tracker-stolmc' ),
	'btn_retry'                        => __( 'Retry', 'service-tracker-stolmc' ),
	'modal_confirm_title'              => __( 'Confirm Action', 'service-tracker-stolmc' ),
	'modal_notice_title'               => __( 'Notice', 'service-tracker-stolmc' ),
	'na'                               => __( 'N/A', 'service-tracker-stolmc' ),
	'customer_case_state_close'        => __( 'close', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Pluralization helpers
	// -------------------------------------------------------------------------
	'case_singular'                    => __( 'case', 'service-tracker-stolmc' ),
	'case_plural'                      => __( 'cases', 'service-tracker-stolmc' ),
	'found'                            => __( 'found', 'service-tracker-stolmc' ),
	'page'                             => __( 'Page', 'service-tracker-stolmc' ),
	'of'                               => __( 'of', 'service-tracker-stolmc' ),
	'attachment_singular'              => __( 'attachment', 'service-tracker-stolmc' ),
	'attachment_plural'                => __( 'attachments', 'service-tracker-stolmc' ),
	'analytics_days'                   => __( 'Days', 'service-tracker-stolmc' ),
	'calendar_cases_ending'            => __( 'case(s) ending', 'service-tracker-stolmc' ),
	'calendar_cases_starting'          => __( 'case(s) starting', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Progress – client fallback
	// -------------------------------------------------------------------------
	'progress_client_prefix'           => __( 'Client #', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Client details – fallback
	// -------------------------------------------------------------------------
	// translators: %d: year the client became active.
	'client_active_since'              => __( 'Active Since %d', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Toast messages – error feedback
	// -------------------------------------------------------------------------
	'toast_date_update_failed'         => __( 'Failed to update date', 'service-tracker-stolmc' ),
	'toast_owner_update_failed'        => __( 'Failed to update case owner', 'service-tracker-stolmc' ),
	'toast_title_update_failed'        => __( 'Failed to update case title', 'service-tracker-stolmc' ),
	'toast_status_post_failed'         => __( 'Failed to post status update', 'service-tracker-stolmc' ),
	'toast_upload_failed'              => __( 'Upload failed', 'service-tracker-stolmc' ),
	'toast_user_created'               => __( 'User created successfully.', 'service-tracker-stolmc' ),
	'toast_user_create_failed'         => __( 'Failed to create user.', 'service-tracker-stolmc' ),
	'toast_user_create_error'          => __( 'Failed to create user. Please try again.', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Analytics – trend block titles
	// -------------------------------------------------------------------------
	'trend_cases_created'              => __( 'Cases Created', 'service-tracker-stolmc' ),
	'trend_progress_updates'           => __( 'Progress Updates', 'service-tracker-stolmc' ),
	'trend_email_notifications'        => __( 'Email Notifications', 'service-tracker-stolmc' ),
	'trend_admin_actions'              => __( 'Admin Actions', 'service-tracker-stolmc' ),

	// -------------------------------------------------------------------------
	// Cases – count subtitle
	// -------------------------------------------------------------------------
	// translators: %d: number of cases.
	'cases_count_subtitle'             => __( '%d case(s) across all clients', 'service-tracker-stolmc' ),
];
