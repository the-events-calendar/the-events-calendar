<?php

use TEC\Events\Custom_Tables\V1\Migration\State;

$state = tribe( State::class );

if ( $state->is_completed() ) {
	$report_meta = $state->get( 'migrate' );
} else {
	$report_meta = $state->get( 'preview' );
}

$remaining_events       = 138;
$total_previewed_events = $state->get( 'events', 'total' ) - $remaining_events;
$percent                = '30%';
$progress               = 30;
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
							'the-events-calendar'
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
							'the-events-calendar'
					),
					'<strong>',
					$remaining_events,
					'</strong>'
			);
			?>
		</div>
	</div>
</div>

