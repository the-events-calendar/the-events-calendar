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
			<?php esc_html_e( 'Migration preview in progress', 'ical-tec' ); ?>
		</h3>

		<p><?php esc_html_e( 'We\'re scanning your existing events so you’ll know what to expect from the migration process. You can keep using your site and managing events. Check back later for a full preview report and the next steps for migration.', 'ical-tec' ); ?></p>
		<?php
		$remaining_events = 138;
		$total_previewed_events = $state->get( 'events', 'total' ) - $remaining_events;
		$percent = '30%';
		$progress = 30;
		?>
		<div class="tribe-update-bar">
			<div class="progress" title="<?php echo esc_attr( $percent ); ?>">
				<div class="bar" style="width: <?php echo esc_attr( $progress ); ?>%"></div>
			</div>
			<div class="tribe-update-bar__summary">
				<div class="tribe-update-bar__summary-progress-text">
					<?php
					echo sprintf(
						_x(
							'%1$s%2$d%3$s events previewed',
							'Number of events previewed',
							'ical-tec'
						),
						'<strong>',
						$total_previewed_events,
						'</strong>'
					);
					?>
				</div>
				<div class="tribe-update-bar__summary-remaining-text">
					<?php
					echo sprintf(
						_x(
							'%1$s%2$d%3$s remaining',
							'Number of events awaiting preview',
							'ical-tec'
						),
						'<strong>',
						$remaining_events,
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
