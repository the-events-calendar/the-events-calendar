<?php

use TEC\Events\Custom_Tables\V1\Migration\State;

$state = tribe( State::class );

if ( $state->is_completed() ) {
	$report_meta = $state->get( 'migrate' );
} else {
	$report_meta = $state->get( 'preview' );
}
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php echo $logo; ?>
			<?php esc_html_e( 'Migration preview in progress', 'the-events-calendar' ); ?>
		</h3>

		<p><?php esc_html_e( 'We\'re scanning your existing events so you’ll know what to expect from the migration process. You can keep using your site and managing events. Check back later for a full preview report and the next steps for migration.', 'the-events-calendar' ); ?></p>
		<div class="tribe-update-bar tec-ct1-upgrade-bar">
			<div class="progress" title="Checking...">
				<div class="bar"></div>
			</div>
			<div class="tribe-update-bar__summary">
				<div class="tribe-update-bar__summary-progress-text">
					<?php
					echo sprintf(
						_x(
							'%1$s...%2$s events previewed',
							'Number of events previewed',
							'the-events-calendar'
						),
						'<strong data-migration="total_events_migrated">',
						'</strong>'
					);
					?>
				</div>
				<div class="tribe-update-bar__summary-remaining-text">
					<?php
					echo sprintf(
						_x(
							'%1$s...%2$s remaining',
							'Number of events awaiting preview',
							'the-events-calendar'
						),
						'<strong data-migration="total_events_remaining">',
						'</strong>'
					);
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
