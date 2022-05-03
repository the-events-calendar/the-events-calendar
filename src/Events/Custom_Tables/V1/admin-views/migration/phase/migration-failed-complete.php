<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * @var array<Event_Report> $event_reports      A list of the event report data.
 * @var string              $template_directory The absolute path to the Migration template root directory.
 * @var Site_Report         $report             The report details.
 * @var String_Dictionary   $text               Our text dictionary.
 */
?>
<div class="tec-ct1-upgrade__row">
	<div class="content-container">
		<h3>
			<?php
			include $template_directory . '/upgrade-logo.php';
			?>
			<?php echo esc_html( $text->get( 'migration-failure-complete' ) ); ?>
		</h3>

		<p>
			<?php
			echo sprintf(
					esc_html( $text->get( 'migration-failure-complete-paragraph' ) ),
					'<a href="https://evnt.is/2n" rel="noopener" target="_blank">',
					'</a>'
			);
			?>
		</p>

		<p class="tec-ct1-upgrade__alert">
			<i class="tec-ct1-upgrade__alert-icon">!</i>
			<?php
			echo sprintf(
					esc_html( $text->get( 'migration-failure-complete-alert' ) ),
					'<a class="tec-ct1-upgrade-start-migration-preview" href="#">',
					'</a>'
			);
			?>
		</p>
	</div>

	<div class="image-container">
		<img class="screenshot"
			 src="<?php echo esc_url( $text->get( 'completed-screenshot-url' ) ); ?>"
			 alt="<?php echo esc_attr( $text->get( 'updated-views-screenshot-alt' ) ); ?>"/>
	</div>
</div>

<div class="tec-ct1-upgrade__row">
	<div class="tec-ct1-upgrade__report">
		<header class="tec-ct1-upgrade__report-header">
			<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--timestamp">
				<?php echo $text->get( 'migration-failure-complete-date-heading' ); ?>
				<strong><?php echo esc_html( $report->date_completed ); ?></strong>
			</div>
			<div class="tec-ct1-action-container tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--rerun">
				<em title="<?php esc_attr( $text->get( 're-run-preview-button' ) ) ?>">
					<?php include TEC_CUSTOM_TABLES_V1_ROOT . '/admin-views/migration/icons/rerun.php'; ?>
				</em>
				<a class="tec-ct1-upgrade-start-migration-preview"
				   href="#"><?php echo esc_html( $text->get( 're-run-preview-button' ) ); ?></a>
			</div>
		</header>
		<div class="tec-ct1-upgrade__report-body">
			<div class="tec-ct1-upgrade__report-body-content">

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
				<?php echo esc_html( $text->get( 'migration-failure-complete-after-report' ) ); ?>
			</footer>

		</div>
	</div>

</div>

<div class="tec-ct1-upgrade__row tec-ct1-action-container">
	<div class="content-container">
		<button class="tec-ct1-upgrade-start-migration-preview"
				type="button">
			<?php echo esc_html( $text->get( 'start-migration-preview-button' ) ); ?>
		</button>
	</div>
</div>
