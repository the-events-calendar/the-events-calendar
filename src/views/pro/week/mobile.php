<?php

/**
 *
 * Please see single-event-hourly.php in this directory for detailed instructions on how to use and modify these templates.
 *
 */

?>

<script type="text/html" id="tribe_tmpl_week_mobile">
	<div class="tribe-events-mobile hentry vevent tribe-clearfix tribe-events-mobile-event-[[=eventId]][[ if(categoryClasses.length) { ]] [[= categoryClasses]][[ } ]]">
		<h4 class="summary">
			<a class="url" href="[[=permalink]]" title="[[=title]]" rel="bookmark">[[=title]]</a>
		</h4>

		<div class="tribe-events-event-body">
			<div class="updated published time-details">
				<span class="date-start dtstart">[[=dateDisplay]] </span>
			</div>
			<a href="[[=permalink]]" class="tribe-events-read-more" rel="bookmark">Find out more »</a>
		</div>
	</div>
</script>
