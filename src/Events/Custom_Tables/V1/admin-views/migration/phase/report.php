<?php

use TEC\Events\Custom_Tables\V1\Migration\CSV_Report\File_Download;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary $text               The text dictionary.
 * @var string            $datetime_heading   The heading for the date of completion.
 * @var string            $total_heading      The heading for the total events.
 * @var string            $heading_action     The action bar relevant for this phase.
 * @var Site_Report       $report             The site report data.
 * @var array<mixed>      $event_categories   A list of the event report data inside of each category.
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
			<?php if ( ! $report->has_changes ) : ?>
				<p>
					<strong><?php echo esc_html( $text->get( 'migration-prompt-no-changes-to-events' ) ); ?></strong>
				</p>
			<?php endif; ?>
			<?php
			if ( $report->has_errors ) {
				include( $template_directory . '/partials/failure-event-loop.php' );
			} else {
				foreach ( $event_categories as $category ) {
					/**
					 * @var string $key
					 * @var string $label ;
					 */
					extract( $category );
					$event_category_key   = $key;
					$event_category_label = $label;
					include( $template_directory . '/partials/event-loop.php' );
				}
			}
			?>
		</div>
		<footer class="tec-ct1-upgrade__report-body-footer">
			<a
				href="<?php echo $text->get( 'learn-more-button-url' ); ?>"
				target="_blank"
				rel="noopener"
			><?php echo esc_html( $text->get( 'migration-prompt-learn-about-report-button' ) ); ?></a>
			|
			<a href="<?php echo File_Download::get_download_url(); ?>"><?php echo $text->get( 'migration-download-report-button' ); ?></a>
		</footer>
	</div>
</div>
