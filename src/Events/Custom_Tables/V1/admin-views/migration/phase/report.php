<div class="tec-ct1-upgrade__report">
	<header class="tec-ct1-upgrade__report-header">
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--timestamp">
			<?php echo $datetime_heading; ?>
			<strong><?php echo esc_html( $report->date_completed->format( 'F j, Y, g:i a' ) ); ?></strong>
		</div>
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--total">
			<?php echo $total_heading; ?>
			<strong><?php echo esc_html( $report->event_total ); ?></strong>
		</div>
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--rerun">
			<?php echo $heading_action; ?>
		</div>
	</header>
	<div class="tec-ct1-upgrade__report-body">
		<div class="tec-ct1-upgrade__report-body-content">
			<?php if ( $report->changes ) : ?>
				<strong><?php esc_html_e( 'Changes to events!', 'ical-tec' ); ?></strong>
				<?php esc_html_e( 'The following events will be modified during the migration process:', 'ical-tec' ); ?>
			<?php else: ?>
				<p>
					<strong><?php esc_html_e( 'Events can migrate with no changes!', 'ical-tec' ); ?></strong>
				</p>
			<?php endif; ?>
			<ul>
				<?php foreach ( $report->events as $event_id => $event ) : ?>
					<?php
					if ( empty( $event->actions_taken ) ) {
						continue;
					}
					?>
					<li>
						<a href=""><?php echo esc_html( $event->events[ $event_id ]->post_title ); ?></a> —
						<?php
						foreach ( $event->actions_taken as $action ) {
							if ( 'split' === $action ) {
								echo sprintf(
										esc_html( 'This event will be %1$ssplit into %2$s recurring events%3$s with identical content.', 'ical-tec' ),
										'<strong>',
										count( $event->events ),
										'</strong>'
								);

								echo sprintf(
										esc_html( 'The events will be part of a new %1$s.', 'ical-tec' ),
										$event->series->post_title
								);
							}

							if ( 'modified-exclusions' === $action ) {
								echo sprintf(
										esc_html( '%1$sOne or more exclusion rules will be modified%2$s, but no occurrences will be added or removed.', 'ical-tec' ),
										'<strong>',
										'</strong>'
								);
							}

							if ( 'modified-rules' === $action ) {
								echo sprintf(
										esc_html( '%1$sOne or more recurrence rules will be modified%2$s, but no occurrences will be added or removed.', 'ical-tec' ),
										'<strong>',
										'</strong>'
								);
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
