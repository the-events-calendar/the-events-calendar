<?php


use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;
use TEC\Events\Custom_Tables\V1\Migration\State;

$state = tribe( State::class );

if ( $state->is_completed() ) {
	$report_meta = $state->get( 'migration' );
} else {
	$report_meta = $state->get( 'preview' );
}
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php echo $logo; ?>
			<?php esc_html_e( 'Migration in progress', 'the-events-calendar' ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
				esc_html__( 'Your events are being migrated to the new system. During this migration, %1$syou cannot make changes to your calendar or events.%2$s Your calendar is still visible on your site. ', 'the-events-calendar' ),
				'<strong>',
				'</strong>'
			);

			if ( $addendum = tribe( Upgrade_Tab::class )->get_migration_prompt_addendum() ) {
				?>
				<strong><?php echo esc_html( $addendum ); ?></strong>
				<?php
			}

			echo sprintf(
				esc_html__( '%1$s%3$sLearn more about the migration%4$s.%2$s', 'the-events-calendar' ),
				'<strong>',
				'</strong>',
				'<a href="https://evnt.is/recurrence-2-0" target="_blank" rel="noopener">',
				'</a>'
			);
			?>
		</p>
		<?php
		$remaining_events = 138;
		$total_previewed_events = $state->get( 'events', 'total' ) - $remaining_events;
		$percent = '30%';
		$progress = 30;
		?>
		<div class="tribe-update-bar">
			<div class="progress" title="<?php echo esc_attr( $percent ); ?>"> <div class="bar" style="width: <?php echo esc_attr( $progress ); ?>%"></div> </div>
			<div class="tribe-update-bar__summary">
				<div class="tribe-update-bar__summary-progress-text">
					<?php
					echo sprintf(
						_x(
							'%1$s%2$d%3$s events migrated',
							'Number of events migrated',
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
							'Number of events awaiting migration',
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
	</div>
	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>
