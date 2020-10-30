<?php
/**
 * Widget: Events List Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/widget-events-list/event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version 5.2.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
use Tribe__Date_Utils as Dates;

$event_date_attr = $event->dates->start->format( Dates::DBDATEFORMAT );

?>
<div class="tribe-events-widget-events-list__event-datetime-wrapper tribe-common-b2 tribe-common-b3--min-medium">
	<?php $this->template( 'widgets/widget-events-list/event/date/featured', [ 'event' => $event ] ); ?>
	<time class="tribe-events-widget-events-list__event-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<?php
		// The date returned back contains HTML and is already escaped.
		echo $event->schedule_details->value();
		?>
	</time>
	<?php $this->do_entry_point( 'after_event_datetime' ); ?>
</div>
