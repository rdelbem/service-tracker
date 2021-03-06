<?php
// TODO: in the future move nonce to its own place

	$texts_array = array(
		'root_url'                        => get_site_url(),
		'api_url'                         => 'service-tracker/v1',
		'nonce'                           => wp_create_nonce( 'wp_rest' ),
		'search_bar'                      => __( 'Search for a client', 'service-tracker' ),
		'home_screen'                     => __( 'Click on a client name, to se hers/his cases!', 'service-tracker' ),
		'btn_add_case'                    => __( 'Add case', 'service-tracker' ),
		'no_cases_yet'                    => __( 'No cases yet! Include a new one!', 'service-tracker' ),
		'case_name'                       => __( 'Case name', 'service-tracker' ),
		'tip_edit_case'                   => __( 'Edit the name of this case', 'service-tracker' ),
		'tip_toggle_case_open'            => __( 'This case is open! Click to close it.', 'service-tracker' ),
		'tip_toggle_case_close'           => __( 'This case is closed! Click to open it.', 'service-tracker' ),
		'tip_delete_case'                 => __( 'Delete this case', 'service-tracker' ),
		'btn_save_case'                   => __( 'Save', 'service-tracker' ),
		'btn_dismiss_edit'                => __( 'Dismiss', 'service-tracker' ),
		'title_progress_page'             => __( 'Progress for case', 'service-tracker' ),
		'new_status_btn'                  => __( 'New Status', 'service-tracker' ),
		'close_box_btn'                   => __( 'Close box', 'service-tracker' ),
		'add_status_btn'                  => __( 'Add this status', 'service-tracker' ),
		'tip_edit_status'                 => __( 'Edit this status', 'service-tracker' ),
		'tip_delete_status'               => __( 'Delete this status', 'service-tracker' ),
		'btn_save_changes_status'         => __( 'Save changes', 'service-tracker' ),
		'toast_case_added'                => __( 'Case added!', 'service-tracker' ),
		'toast_toggle_base_msg'           => __( 'Case is now', 'service-tracker' ),
		'toast_toggle_state_open_msg'     => __( 'open', 'service-tracker' ),
		'toast_toggle_state_close_msg'    => __( 'closed', 'service-tracker' ),
		'toast_case_deleted'              => __( 'Case deleted!', 'service-tracker' ),
		'toast_case_edited'               => __( 'Case edited!', 'service-tracker' ),
		'toast_status_added'              => __( 'Status added!', 'service-tracker' ),
		'toast_status_deleted'            => __( 'Status deleted!', 'service-tracker' ),
		'toast_status_edited'             => __( 'Status edited!', 'service-tracker' ),
		'confirm_delete_case'             => __( 'Do you want to delete the case under the name', 'service-tracker' ),
		'confirm_delete_status'           => __( 'Do you want to delete the status created in', 'service-tracker' ),
		'alert_blank_case_title'          => __( 'Case title can not be blank', 'service-tracker' ),
		'alert_blank_status_title'        => __( 'Status text can not be blank', 'service-tracker' ),
		'alert_error_base'                => __( 'It was impossible to complete this task. We had an error', 'service-tracker' ),
		'no_progress_yet'                 => __( 'No progress is registered for this case.', 'service-tracker' ),
		'customer_case_state_close'       => __( 'close', 'service-tracker' ),
		'instructions_page_title'         => __( 'How to use this plugin', 'service-tracker' ),
		'accordion_first_title'           => __( 'Display info for customers access', 'service-tracker' ),
		'first_accordion_first_li_item'   => __(
			'Create a secured page, one that is only available after login.
    (there are some approaches in order to achieve this result, find one that suits you website better)',
			'service-tracker'
		),
		'first_accordion_second_li_item'  => __( 'Copy and paste the following short code to the restricted page, [service-tracker-cases-progress]', 'service-tracker' ),
		'first_accordion_third_li_item'   => __( 'Now, every new status registered in a case/service will be displayed for that respective customer.', 'service-tracker' ),
		'first_accordion_forth_li_item'   => __(
			'If you do not want to have a restricted customer page that is perfectly fine.
    Every new status triggers a email send which contains such status.',
			'service-tracker'
		),
		'accordion_second_title'          => __( 'Customers?? notifications', 'service-tracker' ),
		'second_accordion_firt_li_item'   => __( 'Everytime a new status is registered for a case, an email is sent to its respective customer.', 'service-tracker' ),
		'second_accordion_second_li_item' => __( 'This plugin uses the default wp_mail function to send its emails. So, it is highly recomended to use WP Mail SMTP OR other smtp plugin alongside Service Tracker, in order to avoid lost emails. (The standard wp_mail from WordPress is notorius for sending emails straight to spam box. However, with the third party smtp plugins this can be easily avoided, as wp_mail is overwritten by them.)', 'service-tracker' ),
		'accordion_third_title'           => __( 'Service Tracker plugin updates, support and warranty', 'service-tracker' ),
		'third_accordion_first_li_item'   => __( 'Official support will ALWAYS be under the email of servicetracker@delbem.net.', 'service-tracker' ),
		'third_accordion_second_li_item'  => __( 'ANY modification of the source code of this plugin will cause loss of warranty, which implies no refound and loss of support.', 'service-tracker' ),
		'third_accordion_third_li_item'   => __( 'A refound order MUST be made within seven business days.', 'service-tracker' ),
		'third_accordion_forth_li_item'   => __( 'A license is valid for one site only.', 'service-tracker' ),
		'third_accordion_fifth_li_item'   => __( 'Updates must be on point. If not, Service Tracker may not work properly, as any WordPress plugin.', 'service-tracker' ),
		'instructions_footer_info'        => __(
			'This plugin was coded and is maintained by Rodrigo Vieira Del Bem.
    Do you need any help? Contact me at servicetracker@delbem.net, or, alternatively, at rodrigo@delbem.net',
			'service-tracker'
		),
	);

