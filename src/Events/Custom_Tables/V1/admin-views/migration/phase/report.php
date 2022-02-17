<div class="tec-ct1-upgrade__report">
	<header class="tec-ct1-upgrade__report-header">
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--timestamp">
			<?php echo $datetime_heading; ?>
			<strong data-migration="date_completed">...</strong>
		</div>
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--total">
			<?php echo $total_heading; ?>
			<strong data-migration="event_total">...</strong>
		</div>
		<div class="tec-ct1-upgrade__report-header-section tec-ct1-upgrade__report-header-section--rerun">
			<?php echo $heading_action; ?>
		</div>
	</header>
	<div class="tec-ct1-upgrade__report-body">
		<div class="tec-ct1-upgrade__report-body-content">
			<div class="tec-ct1-upgrade__report-pre-message">
				<p>Checking...</p>
			</div>
			<ul class="tec-ct1-upgrade__report-events-list">
			</ul>
		</div>
		<footer class="tec-ct1-upgrade__report-body-footer">
			<a href="http://evnt.is/recurrence-2-0-report" target="_blank" rel="noopener">
				<?php esc_html_e( 'Learn more about your migration preview report', 'ical-tec' ); ?>
			</a>
		</footer>
	</div>
</div>
