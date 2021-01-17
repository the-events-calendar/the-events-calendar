<?php
/**
 * Widget: Events List Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/widget-events-list/event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
use Tribe__Date_Utils as Dates;

$event_date_attr = $event->dates->start->format( Dates::DBDATEFORMAT );

if ( $event->multiday ) {
	// The date returned back contains HTML and is already escaped.
	$event_date = $event->schedule_details->value();
} elseif ( $event->all_day ) {
	$event_date = esc_html_x( 'All day', 'All day label for event', 'the-events-calendar' );
} else {
	// The date returned back contains HTML and is already escaped.
	$event_date = $event->short_schedule_details->value();
}
?>
<div class="tribe-events-widget-events-list__event-datetime-wrapper tribe-common-b2 tribe-common-b3--min-medium">
	<?php $this->template( 'widgets/widget-events-list/event/date/featured', [ 'event' => $event ] ); ?>
	<time class="tribe-events-widget-events-list__event-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<?php echo $event_date; // phpcs:ignore. ?>
	</time>
	<?php $this->do_entry_point( 'after_event_datetime' ); ?>
</div>
