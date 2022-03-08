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
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--rerun">
			<?php echo $heading_action; ?>
		</div>
	</header>
	<div class="tec-ct1-upgrade__report-body">
		<div class="tec-ct1-upgrade__report-body-content">
			<?php if ( $report->has_changes ) : ?>
				<strong><?php esc_html_e( 'Changes to events!', 'the-events-calendar' ); ?></strong>
				<?php esc_html_e( 'The following events will be modified during the migration process:', 'the-events-calendar' ); ?>
			<?php else: ?>
				<p>
					<strong><?php esc_html_e( 'Events can migrate with no changes!', 'the-events-calendar' ); ?></strong>
				</p>
			<?php endif; ?>
			<ul>
				<?php foreach ( $report->event_reports as $event ) : ?>
					<li>
						<a target="_blank" href="<?php echo get_edit_post_link( $event->source_event_post->ID ,false )  ?>"><?php echo esc_html( $event->source_event_post->post_title ); ?></a>
						—
						<?php
						if (  $event->error  ) {
							echo esc_html( $event->error, 'the-events-calendar' );
						}

						foreach ( $event->strategies_applied as $action ) {
							if ( 'split' === $action ) {
								echo sprintf(
										esc_html( 'This event will be %1$ssplit into %2$s recurring events%3$s with identical content.', 'the-events-calendar' ),
										'<strong>',
										count( $event->created_events ),
										'</strong>'
								);

								echo sprintf(
										esc_html( 'The events will be part of a new %1$s.', 'the-events-calendar' ),
										$event->series[0]->post_title // @todo This ok?
								);
							} else if ( 'modified-exclusions' === $action ) {
								echo sprintf(
										esc_html( '%1$sOne or more exclusion rules will be modified%2$s, but no occurrences will be added or removed.', 'the-events-calendar' ),
										'<strong>',
										'</strong>'
								);
							} else if ( 'modified-rules' === $action ) {
								echo sprintf(
										esc_html( '%1$sOne or more recurrence rules will be modified%2$s, but no occurrences will be added or removed.', 'the-events-calendar' ),
										'<strong>',
										'</strong>'
								);
							} else {
								echo esc_html( 'Unknown strategy applied to this event.', 'the-events-calendar' );
							}
						}
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<footer class="tec-ct1-upgrade__report-body-footer">
			<a href="http://evnt.is/recurrence-2-0-report" target="_blank" rel="noopener">
				<?php esc_html_e( 'Learn more about your migration preview report', 'the-events-calendar' ); ?>
			</a>
		</footer>
	</div>
</div>
