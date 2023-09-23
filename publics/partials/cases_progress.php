<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="st-container">

	<?php foreach ($user_cases_and_statuses as $case): ?>
		<div class="st-headers">
			<div class="st-case-title">
				<small class="st-title-small">
					<?php echo esc_html($case['created_at']); ?>
				</small>
				<p>
					<?php echo esc_html($case['case_title']); ?>
					-
					<?php echo esc_html($case['case_status'], 'service-tracker'); ?>
				</p>
			</div>
		</div>

		<div class="st-progress-container">

			<ul class="st-ul-progress">
				<?php foreach ($case['progress'] as $status): ?>
					<li class="st-li-progress">
						<small class="st-progress-small">
							<?php echo esc_html($status['created_at']); ?>
						</small>

						<div class="st-text-container">
							<p>
								<?php echo esc_html($status['text']); ?>
							</p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

		</div>
	<?php endforeach; ?>
</div>