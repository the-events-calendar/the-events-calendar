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
 * @version 4.9.9
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

use Tribe__Date_Utils as Dates;

$event_week_day  = $event->dates->start->format( 'D' );
$event_day_num   = $event->dates->start->format( 'j' );
$event_date_attr = $event->dates->start->format( Dates::DBDATEFORMAT );
?>
<div class="tribe-events-calendar-list__event-date-tag tribe-common-g-col">
	<time class="tribe-events-calendar-list__event-date-tag-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<span class="tribe-events-calendar-list__event-date-tag-weekday">
			<?php echo esc_html( $event_week_day ); ?>
		</span>
		<span class="tribe-events-calendar-list__event-date-tag-daynum tribe-common-h5 tribe-common-h4--min-medium">
			<?php echo esc_html( $event_day_num ); ?>
		</span>
	</time>
</div>
