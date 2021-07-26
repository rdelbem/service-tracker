<div class="st-container">

	<?php foreach ( $user_cases_and_statuses as $case ) : ?>
		<div class="st-headers">
			<div class="st-case-title">
				<small>
					<?php echo $case['created_at']; ?>
				</small>
				<p>	
					<?php echo $case['case_title']; ?>
					- 
					<?php echo __( $case['case_status'], 'service-tracker' ); ?> 
				</p>
			</div>
		</div>
	
		<div class="st-progress-container">

			<ul class="st-ul-progress">
				<?php	foreach ( $case['progress'] as $status ) : ?>
					<li class="st-li-progress"> 

						<small>
							<?php echo $status->{'created_at'}; ?>
						</small>

						<p>
							<?php echo $status->{'text'}; ?>
						</p>
							
					</li>
				<?php	endforeach; ?>
			</ul>

		</div>
	<?php endforeach; ?>
</div>
