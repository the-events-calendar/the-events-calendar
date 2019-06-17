<?php
/**
 * View: Month View - Mobile Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
?>
<div class="tribe-events-calendar-month__mobile-event-datetime tribe-common-b2">
	<time datetime="14:00">2pm</time>
	<span class="tribe-events-calendar-month__mobile-event-datetime-separator"> - </span>
	<time datetime="18:00">6pm</time>
	<em
		class="tribe-events-calendar-month__mobile-event-datetime-featured tribe-common-svgicon tribe-common-svgicon--featured"
		aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
		title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
	>
	</em>
	<em
		class="tribe-events-calendar-month__mobile-event-datetime-recurring tribe-common-svgicon tribe-common-svgicon--recurring"
		aria-label="<?php esc_attr_e( 'Recurring', 'the-events-calendar' ) ?>"
		title="<?php esc_attr_e( 'Recurring', 'the-events-calendar' ) ?>"
	>
	</em>
</div>
