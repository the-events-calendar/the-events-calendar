<?php
/**
 * View: Month View - Single Event Tooltip Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 4.9.13
 * @since 5.1.1 Move icons into separate templates.
 *
 * @var WP_Post $event        The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

use Tribe__Date_Utils as Dates;
$event_date_attr = $event->dates->start->format( Dates::DBDATEFORMAT );
?>
<div class="tribe-events-calendar-month__calendar-event-tooltip-datetime">
	<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip/date/featured' ); ?>
	<time datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<?php echo $event->schedule_details->value(); ?>
	</time>
	<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip/date/meta', [ 'event' => $event ] ); ?>
</div>
