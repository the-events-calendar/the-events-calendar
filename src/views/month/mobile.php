<?php

/**
 *
 * Please see single-event.php in this directory for detailed instructions on how to use and modify these templates.
 *
 * @version 4.3.3
 *
 */

?>

<script type="text/html" id="tribe_tmpl_month_mobile_day_header">
	<div class="tribe-mobile-day" data-day="[[=date]]">[[ if(has_events) { ]]
		<h3 class="tribe-mobile-day-heading">[[=i18n.for_date]] <span>[[=raw date_name]]</span></h3>[[ } ]]
	</div>
</script>

<script type="text/html" id="tribe_tmpl_month_mobile">
	<div class="tribe-events-mobile tribe-clearfix tribe-events-mobile-event-[[=eventId]][[ if(categoryClasses.length) { ]] [[= categoryClasses]][[ } ]]">
		<h4 class="summary">
			<a class="url" href="[[=permalink]]" title="[[=title]]" rel="bookmark">[[=raw title]]</a>
		</h4>

		<div class="tribe-events-event-body">
			<div class="tribe-events-event-schedule-details">
				<span class="tribe-event-date-start">[[=dateDisplay]] </span>
			</div>
			[[ if(imageSrc.length) { ]]
			<div class="tribe-events-event-image">
				<a href="[[=permalink]]" title="[[=title]]">
					<img src="[[=imageSrc]]" alt="[[=title]]" title="[[=title]]">
				</a>
			</div>
			[[ } ]]
			[[ if(excerpt.length) { ]]
			<div class="tribe-event-description"> [[=raw excerpt]] </div>
			[[ } ]]
			<a href="[[=permalink]]" class="tribe-events-read-more" rel="bookmark">[[=i18n.find_out_more]]</a>
		</div>
	</div>
</script>
