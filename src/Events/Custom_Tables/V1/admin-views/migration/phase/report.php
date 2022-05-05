<?php

use TEC\Events\Custom_Tables\V1\Migration\Download_Report_Provider;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;

/**
 * @var string              $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary   $text               The text dictionary.
 * @var string              $datetime_heading   The heading for the date of completion.
 * @var string              $total_heading      The heading for the total events.
 * @var string              $heading_action     The action bar relevant for this phase.
 * @var Site_Report         $report             The site report data.
 * @var array<Event_Report> $event_reports      A list of the event report data.
 *
 */
?>
<div class="tec-ct1-upgrade__report">
	<header class="tec-ct1-upgrade__report-header">
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--timestamp">
			<?php echo $datetime_heading; ?>
			<strong><?php echo esc_html( $report->date_completed ); ?></strong>
		</div>
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--total">
			<?php echo $total_heading; ?>
			<strong><?php echo esc_html( $report->total_events ); ?></strong>
		</div>
		<?php if ( $heading_action ) { ?>
			<div class="tec-ct1-action-container tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--rerun">
				<?php echo $heading_action; ?>
			</div>
		<?php } ?>
	</header>
	<div class="tec-ct1-upgrade__report-body">
		<div class="tec-ct1-upgrade__report-body-content">
			<?php if ( $report->has_changes ) : ?>
				<strong>
					<?php
					// @todo We could potentially use the phase as a key for the different text definition, i.e. $text->get($phase.'-changes-to-events')
					echo esc_html( $text->get( 'migration-prompt-changes-to-events' ) );
					?>
				</strong>
				<?php echo esc_html( $text->get( 'migration-prompt-events-modified' ) ); ?>
			<?php else: ?>
				<p>
					<strong><?php echo esc_html( $text->get( 'migration-prompt-no-changes-to-events' ) ); ?></strong>
				</p>
			<?php endif; ?>
			<?php include( $template_directory . '/partials/event-loop.php' ); ?>
		</div>
		<footer class="tec-ct1-upgrade__report-body-footer">
			<a href="http://evnt.is/recurrence-2-0-report" target="_blank"
			   rel="noopener"><?php echo esc_html( $text->get( 'migration-prompt-learn-about-report-button' ) ); ?></a>
			|
			<a href="<?php echo Download_Report_Provider::get_download_url(); ?>"><?php echo $text->get( 'migration-download-report-button' ); ?></a>
		</footer>
	</div>
</div>
