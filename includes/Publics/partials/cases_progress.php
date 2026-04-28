<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Variable passed from STOLMC_Service_Tracker_Public_User_Content::use_partial().
$stolmc_user_cases_and_statuses = $user_cases_and_statuses ?? [];
?>

<div class="stolmc-container">

		<?php foreach ( $stolmc_user_cases_and_statuses as $stolmc_case ) : ?>
		<div class="stolmc-headers">
			<div class="stolmc-case-title">
				<small class="stolmc-title-small">
						<?php echo esc_html( $stolmc_case['created_at'] ); ?>
				</small>
				<p>
						<?php echo esc_html( $stolmc_case['case_title'] ); ?>
					-
						<?php echo esc_html( $stolmc_case['case_status'] ); ?>
				</p>
			</div>
		</div>

		<div class="stolmc-progress-container">

			<ul class="stolmc-ul-progress">
					<?php foreach ( $stolmc_case['progress'] as $stolmc_progress_item ) : ?>
					<li class="stolmc-li-progress">
						<small class="stolmc-progress-small">
								<?php echo esc_html( $stolmc_progress_item['created_at'] ); ?>
						</small>

						<div class="stolmc-text-container">
							<p>
									<?php echo esc_html( $stolmc_progress_item['text'] ); ?>
							</p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

		</div>
	<?php endforeach; ?>
</div>
