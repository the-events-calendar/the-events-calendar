<?php
/**
 * View: List View - Single Event Date Tag
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/event/date-tag.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$event = $this->get( 'event' );
?>
<div class="tribe-events-calendar-list__event-date-tag tribe-common-g-col">
	<time class="tribe-events-calendar-list__event-date-tag-datetime" datetime="1970-01-01T00:00:00+00:00">
		<span class="tribe-events-calendar-list__event-date-tag-weekday">Wed</span>
		<span class="tribe-events-calendar-list__event-date-tag-daynum tribe-common-h5 tribe-common-h4--min-medium">05</span>
	</time>
</div>
