<?php
/**
 * View: Latest Past View - Single Event Date Tag
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/latest-past/event/date-tag.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.1.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

use Tribe__Date_Utils as Dates;

$event_month     = $event->dates->start_display->format_i18n( 'M' );
$event_day_num   = $event->dates->start_display->format_i18n( 'j' );
$event_year      = $event->dates->start_display->format_i18n( 'Y' );
$event_date_attr = $event->dates->start_display->format( Dates::DBDATEFORMAT );
?>
<div class="tribe-events-calendar-latest-past__event-date-tag tribe-common-g-col">
	<time class="tribe-events-calendar-latest-past__event-date-tag-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<span class="tribe-events-calendar-latest-past__event-date-tag-month">
			<?php echo esc_html( $event_month ); ?>
		</span>
		<span class="tribe-events-calendar-latest-past__event-date-tag-daynum tribe-common-h5 tribe-common-h4--min-medium">
			<?php echo esc_html( $event_day_num ); ?>
		</span>
		<span class="tribe-events-calendar-latest-past__event-date-tag-year">
			<?php echo esc_html( $event_year ); ?>
		</span>
	</time>
</div>
