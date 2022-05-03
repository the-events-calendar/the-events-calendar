<?php

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
			<ul>
				<?php foreach ( $event_reports as $event ) : ?>
					<li>
						<a target="_blank"
						   href="<?php echo get_edit_post_link( $event->source_event_post->ID, false ) ?>"><?php echo esc_html( $event->source_event_post->post_title ); ?></a>
						—
						<?php
						if ( $event->error ) {
							esc_html_e( $event->error, 'the-events-calendar' );
						} else {
							foreach ( $event->strategies_applied as $action ) {
								switch ( $action ) {
									case 'split':
										echo sprintf(
												esc_html( $text->get( "migration-prompt-strategy-$action" ) ),
												'<strong>',
												count( $event->created_events ),
												'</strong>'
										);
										echo sprintf(
												esc_html( $text->get( "migration-prompt-strategy-$action-new-series" ) ),
												$event->series[0]->post_title // @todo This ok?
										);
										break;
									default:
										// Do we have language for this strategy?
										$output = sprintf(
												esc_html( $text->get( "migration-prompt-strategy-$action" ) ),
												'<strong>',
												'</strong>'
										);
										if ( $output ) {
											echo $output;
										} else {
											echo esc_html( $text->get( "migration-prompt-unknown-strategy" ) );
										}
										break;
								}
							}
						}
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<footer class="tec-ct1-upgrade__report-body-footer">
			<a href="http://evnt.is/recurrence-2-0-report" target="_blank" rel="noopener">
				<?php echo esc_html( $text->get( 'migration-prompt-learn-about-report-button' ) ); ?>
			</a>
		</footer>
	</div>
</div>
