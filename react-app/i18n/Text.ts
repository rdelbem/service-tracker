/**
 * Enum of all UI text keys exposed by the PHP backend via wp_localize_script.
 *
 * Each value corresponds to a key in `window.data` populated from
 * `admin/translation/ui_copy.php`. Use with {@link stolmec_text}.
 *
 * When adding a new string, add it to BOTH this enum AND `ui_copy.php`.
 *
 * @see stolmec_text
 */
export enum Text {
  // Search & navigation
  SearchBar = "search_bar",
  HomeScreen = "home_screen",

  // Sidebar
  NavDashboard = "nav_dashboard",
  NavClients = "nav_clients",
  NavCases = "nav_cases",
  NavCalendar = "nav_calendar",
  NavAnalytics = "nav_analytics",
  NavSettings = "nav_settings",
  BrandName = "brand_name",
  RoleAdmin = "role_admin",
  RoleMaster = "role_master",
  FallbackAdminUser = "fallback_admin_user",

  // Cases – list view
  BtnAddCase = "btn_add_case",
  NoCasesYet = "no_cases_yet",
  CaseName = "case_name",
  CasesHeading = "cases_heading",
  CasesSearchPlaceholder = "cases_search_placeholder",
  CasesEmptySearch = "cases_empty_search",
  CasesRetry = "cases_retry",
  CasesCreateFirst = "cases_create_first",
  CasesAddNew = "cases_add_new",

  // Cases – single case
  TipEditCase = "tip_edit_case",
  TipToggleCaseOpen = "tip_toggle_case_open",
  TipToggleCaseClose = "tip_toggle_case_close",
  TipDeleteCase = "tip_delete_case",
  BtnSaveCase = "btn_save_case",
  BtnDismissEdit = "btn_dismiss_edit",
  StatusActive = "status_active",
  StatusClosed = "status_closed",
  StatusUnknown = "status_unknown",
  CaseCreatedPrefix = "case_created_prefix",
  TipViewProgress = "tip_view_progress",
  CaseEditPlaceholder = "case_edit_placeholder",
  CaseNotFound = "case_not_found",

  // Cases – details view
  LabelStatus = "label_status",
  LabelCreated = "label_created",
  LabelStartDate = "label_start_date",
  LabelDueDate = "label_due_date",
  LabelDescription = "label_description",
  NotSet = "not_set",
  NoDescription = "no_description",
  BtnBackToCases = "btn_back_to_cases",
  CaseOwnerLabel = "case_owner_label",
  CaseOwnerUnassigned = "case_owner_unassigned",
  CaseOwnerAdminSuffix = "case_owner_admin_suffix",

  // Cases – confirmations
  ConfirmDeleteCase = "confirm_delete_case",
  ConfirmDeleteCaseTitle = "confirm_delete_case_title",
  ConfirmDeleteCaseMsg = "confirm_delete_case_msg",
  ConfirmCloseCaseTitle = "confirm_close_case_title",
  ConfirmReopenCaseTitle = "confirm_reopen_case_title",
  ConfirmCloseCaseMsg = "confirm_close_case_msg",
  ConfirmReopenCaseMsg = "confirm_reopen_case_msg",

  // Progress / status updates
  TitleProgressPage = "title_progress_page",
  NewStatusBtn = "new_status_btn",
  CloseBoxBtn = "close_box_btn",
  AddStatusBtn = "add_status_btn",
  TipEditStatus = "tip_edit_status",
  TipDeleteStatus = "tip_delete_status",
  BtnSaveChangesStatus = "btn_save_changes_status",
  NoProgressYet = "no_progress_yet",
  ProgressHeading = "progress_heading",
  ProgressPlaceholder = "progress_placeholder",
  ProgressFilesLabel = "progress_files_label",
  ProgressAttachFiles = "progress_attach_files",
  ProgressAddImage = "progress_add_image",
  ProgressUploading = "progress_uploading",
  ProgressPostBtn = "progress_post_btn",
  ProgressNotifyBadge = "progress_notify_badge",
  ActivityLogHeading = "activity_log_heading",
  ActivityLogEmptyDesc = "activity_log_empty_desc",
  ClientAttachmentsHeading = "client_attachments_heading",
  ProgressTab = "progress_tab",
  AttachmentsTab = "attachments_tab",

  // Progress – confirmations
  ConfirmDeleteStatus = "confirm_delete_status",
  ConfirmDeleteStatusTitle = "confirm_delete_status_title",
  ConfirmDeleteStatusMsg = "confirm_delete_status_msg",

  // Toast messages
  ToastCaseAdded = "toast_case_added",
  ToastCaseDeleted = "toast_case_deleted",
  ToastCaseEdited = "toast_case_edited",
  ToastCaseToggled = "toast_case_toggled",
  ToastToggleBaseMsg = "toast_toggle_base_msg",
  ToastToggleStateOpenMsg = "toast_toggle_state_open_msg",
  ToastToggleStateCloseMsg = "toast_toggle_state_close_msg",
  ToastStatusAdded = "toast_status_added",
  ToastStatusDeleted = "toast_status_deleted",
  ToastStatusEdited = "toast_status_edited",
  ToastCaseDeletedSuccess = "toast_case_deleted_success",
  ToastCaseTitleUpdated = "toast_case_title_updated",
  ToastOwnerChanged = "toast_owner_changed",

  // Alerts & validation
  AlertBlankCaseTitle = "alert_blank_case_title",
  AlertBlankStatusTitle = "alert_blank_status_title",
  AlertErrorBase = "alert_error_base",

  // Clients view
  ClientsHeading = "clients_heading",
  ClientsSearchPlaceholder = "clients_search_placeholder",
  ClientsEmpty = "clients_empty",
  ClientsEmptySearch = "clients_empty_search",
  ClientsAddFirst = "clients_add_first",
  ClientsAddBtn = "clients_add_btn",
  ClientsCreating = "clients_creating",
  ClientsCreateBtn = "clients_create_btn",
  LabelName = "label_name",
  LabelEmail = "label_email",
  LabelPhone = "label_phone",
  LabelCellphone = "label_cellphone",
  PlaceholderName = "placeholder_name",
  PlaceholderEmail = "placeholder_email",
  PlaceholderPhone = "placeholder_phone",
  ClientsNameEmailRequired = "clients_name_email_required",
  ClientsResultsFor = "clients_results_for",
  ClientsTotal = "clients_total",

  // Client details
  ClientLabel = "client_label",
  ClientSince = "client_since",
  ClientContactHeading = "client_contact_heading",
  ClientCasesHeading = "client_cases_heading",
  ClientNoCases = "client_no_cases",
  ClientBackToList = "client_back_to_list",
  ClientUpdateSuccess = "client_update_success",
  ClientUpdateError = "client_update_error",
  ClientInvalidEmail = "client_invalid_email",

  // Add case form
  AddCaseHeading = "add_case_heading",
  AddCaseDescription = "add_case_description",
  AddCaseClientLabel = "add_case_client_label",
  AddCaseClientPlaceholder = "add_case_client_placeholder",
  AddCaseTitleLabel = "add_case_title_label",
  AddCaseTitlePlaceholder = "add_case_title_placeholder",
  AddCaseStatusLabel = "add_case_status_label",
  AddCaseDescriptionLabel = "add_case_description_label",
  AddCaseDescriptionPlaceholder = "add_case_description_placeholder",
  AddCaseDateRangeLabel = "add_case_date_range_label",
  AddCaseStartDateLabel = "add_case_start_date_label",
  AddCaseDueDateLabel = "add_case_due_date_label",
  AddCaseDateHelp = "add_case_date_help",
  AddCaseCreating = "add_case_creating",
  AddCaseCreateBtn = "add_case_create_btn",
  AddCaseSelectClient = "add_case_select_client",
  AddCaseEnterTitle = "add_case_enter_title",
  AddCaseError = "add_case_error",

  // Calendar
  CalendarHeading = "calendar_heading",
  CalendarDescription = "calendar_description",
  CalendarAllClients = "calendar_all_clients",
  CalendarAllStatuses = "calendar_all_statuses",
  CalendarCaseStarts = "calendar_case_starts",
  CalendarCaseEnds = "calendar_case_ends",
  CalendarMore = "calendar_more",
  CalendarWeekdays = "calendar_weekdays",
  CalendarMonths = "calendar_months",
  CalendarCasesEnding = "calendar_cases_ending",
  CalendarCasesStarting = "calendar_cases_starting",

  // Analytics
  AnalyticsHeading = "analytics_heading",
  AnalyticsDescription = "analytics_description",
  AnalyticsTimePeriod = "analytics_time_period",
  AnalyticsTabSummary = "analytics_tab_summary",
  AnalyticsTabCustomers = "analytics_tab_customers",
  AnalyticsTabAdmins = "analytics_tab_admins",
  AnalyticsTabTrends = "analytics_tab_trends",
  AnalyticsTotalCustomers = "analytics_total_customers",
  AnalyticsTotalCases = "analytics_total_cases",
  AnalyticsOpenCases = "analytics_open_cases",
  AnalyticsClosedCases = "analytics_closed_cases",
  AnalyticsProgressUpdates = "analytics_progress_updates",
  AnalyticsEmailsSent = "analytics_emails_sent",
  AnalyticsEmailsAttempted = "analytics_emails_attempted",
  AnalyticsFailedEmails = "analytics_failed_emails",
  AnalyticsActiveAdmins = "analytics_active_admins",
  AnalyticsNoData = "analytics_no_data",
  AnalyticsNever = "analytics_never",

  // Analytics – table headers
  AnalyticsColCustomer = "analytics_col_customer",
  AnalyticsColTotalCases = "analytics_col_total_cases",
  AnalyticsColOpen = "analytics_col_open",
  AnalyticsColClosed = "analytics_col_closed",
  AnalyticsColProgress = "analytics_col_progress",
  AnalyticsColEmails = "analytics_col_emails",
  AnalyticsColLastActivity = "analytics_col_last_activity",
  AnalyticsColAdmin = "analytics_col_admin",
  AnalyticsColCasesCreated = "analytics_col_cases_created",
  AnalyticsColCasesUpdated = "analytics_col_cases_updated",
  AnalyticsColCasesDeleted = "analytics_col_cases_deleted",
  AnalyticsColProgressAdded = "analytics_col_progress_added",
  AnalyticsColEmailsTriggered = "analytics_col_emails_triggered",
  AnalyticsNoCustomerData = "analytics_no_customer_data",
  AnalyticsNoAdminData = "analytics_no_admin_data",
  AnalyticsNoTrendsData = "analytics_no_trends_data",

  // Settings
  SettingsHeading = "settings_heading",
  SettingsDescription = "settings_description",
  SettingsAppearance = "settings_appearance",
  SettingsDarkMode = "settings_dark_mode",
  SettingsDarkModeDesc = "settings_dark_mode_desc",
  SettingsThemePreview = "settings_theme_preview",
  SettingsThemeDark = "settings_theme_dark",
  SettingsThemeLight = "settings_theme_light",
  SettingsAccount = "settings_account",
  SettingsUserInfo = "settings_user_info",

  // How to use / instructions
  InstructionsPageTitle = "instructions_page_title",
  AccordionFirstTitle = "accordion_first_title",
  FirstAccordionFirstLiItem = "first_accordion_first_li_item",
  FirstAccordionSecondLiItem = "first_accordion_second_li_item",
  FirstAccordionThirdLiItem = "first_accordion_third_li_item",
  FirstAccordionForthLiItem = "first_accordion_forth_li_item",
  AccordionSecondTitle = "accordion_second_title",
  SecondAccordionFirtLiItem = "second_accordion_firt_li_item",
  SecondAccordionSecondLiItem = "second_accordion_second_li_item",

  // User attachments
  AttachmentsLoading = "attachments_loading",
  AttachmentsEmpty = "attachments_empty",
  AttachmentsFilterLabel = "attachments_filter_label",
  AttachmentsAllCases = "attachments_all_cases",

  // Generic UI
  BtnSave = "btn_save",
  BtnCancel = "btn_cancel",
  BtnDelete = "btn_delete",
  BtnConfirm = "btn_confirm",
  BtnOk = "btn_ok",
  BtnClose = "btn_close",
  BtnEdit = "btn_edit",
  BtnBack = "btn_back",
  BtnPrev = "btn_prev",
  BtnNext = "btn_next",
  BtnRetry = "btn_retry",
  ModalConfirmTitle = "modal_confirm_title",
  ModalNoticeTitle = "modal_notice_title",
  Na = "na",
  CustomerCaseStateClose = "customer_case_state_close",

  // Pluralization helpers
  CaseSingular = "case_singular",
  CasePlural = "case_plural",
  Found = "found",
  Page = "page",
  Of = "of",
  AttachmentSingular = "attachment_singular",
  AttachmentPlural = "attachment_plural",
  AnalyticsDays = "analytics_days",

  // Progress – client fallback
  ProgressClientPrefix = "progress_client_prefix",

  // Client details – fallback
  ClientActiveSince = "client_active_since",

  // Toast messages – error feedback
  ToastDateUpdateFailed = "toast_date_update_failed",
  ToastOwnerUpdateFailed = "toast_owner_update_failed",
  ToastTitleUpdateFailed = "toast_title_update_failed",
  ToastStatusPostFailed = "toast_status_post_failed",
  ToastUploadFailed = "toast_upload_failed",
  ToastUserCreated = "toast_user_created",
  ToastUserCreateFailed = "toast_user_create_failed",
  ToastUserCreateError = "toast_user_create_error",

  // Analytics – trend block titles
  TrendCasesCreated = "trend_cases_created",
  TrendProgressUpdates = "trend_progress_updates",
  TrendEmailNotifications = "trend_email_notifications",
  TrendAdminActions = "trend_admin_actions",

  // Cases – count subtitle
  CasesCountSubtitle = "cases_count_subtitle",
}
