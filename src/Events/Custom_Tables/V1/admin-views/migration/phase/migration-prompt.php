<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;

/**
 * @var string      $template_directory The absolute path to the Migration template root directory.
 * @var Site_Report $report             The report details.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php use TEC\Events_Pro\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;

			include $template_directory . '/upgrade-logo.php';
			?>
			<?php esc_html_e( 'Preview complete', 'the-events-calendar' ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
				esc_html__( 'The migration preview is done and ready for your review. No changes have been made to your events, but this report shows what adjustments will be made during the migration to the new system. If you have any questions, please %1$sreach out to our support team%2$s.', 'the-events-calendar' ),
				'<a href="https://evnt.is/2n" rel="noopener" target="_blank">',
				'</a>'
			);
			?>
		</p>

		<p class="tec-ct1-upgrade__alert">
			<i class="tec-ct1-upgrade__alert-icon">!</i>
			<?php
			echo sprintf(
				esc_html( 'From this preview, we estimate that the full migration process will take approximately %3$s hour(s). During migration, %1$syou cannot make changes to your calendar or events.%2$s Your calendar will still be visible on your site.', 'the-events-calendar' ),
				'<strong>',
				'</strong>',
				$report->estimated_time_in_hours
			);

			if ( $addendum = tribe( \TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab::class )->get_migration_prompt_addendum() ) {
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
	</div>

	<div class="image-container">
		<img class="screenshot" src="<?php echo esc_url( plugins_url( 'src/resources/images/upgrade-views-screenshot.png', TRIBE_EVENTS_FILE ) ); ?>" alt="<?php esc_attr_e( 'screenshot of updated calendar views', 'the-events-calendar' ); ?>" />
	</div>
</div>

<div class="tec-ct1-upgrade__row">
	<?php
	$datetime_heading = __( 'Previewed Date/Time:', 'the-events-calendar' );
	$total_heading    = __( 'Total Events Previewed:', 'the-events-calendar' );
	ob_start();
	?>
		<em
			title="<?php esc_attr_e( 'Re-run preview', 'the-events-calendar' ) ?>"
		>
			<?php include TEC_CUSTOM_TABLES_V1_ROOT . '/admin-views/migration/icons/rerun.php'; ?>
		</em>
		<a href=""><?php esc_html_e( 'Re-run preview', 'the-events-calendar' ); ?></a>
	<?php
	$heading_action = ob_get_clean();
	include_once __DIR__ . '/report.php';
	?>
</div>

<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<button type="button"><?php esc_html_e( 'Start migration', 'the-events-calendar' ); ?></button>
		<i>
			<?php
			if ( 1 === $report->estimated_time_in_hours ) {
				$message = esc_html( '(Estimated time: %1$s hour)', 'ical-tec' );
			} else {
				$message = esc_html( '(Estimated time: %1$s hours)', 'ical-tec' );
			}

			echo sprintf( $message, $report->estimated_time_in_hours );
			?>
		</i>
	</div>
</div>
