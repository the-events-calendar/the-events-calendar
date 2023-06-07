<?php
/**
 * Widget: Events List Event Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/widget-events-list/event/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.2.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

use Tribe__Date_Utils as Dates;

$display_date    = $event->dates->start_display;

$event_month     = $display_date->format_i18n( 'M' );
$event_day_num   = $display_date->format_i18n( 'j' );
$event_date_attr = $display_date->format( Dates::DBDATEFORMAT );
?>
<div class="tribe-events-widget-events-list__event-date-tag tribe-common-g-col">
	<time class="tribe-events-widget-events-list__event-date-tag-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<span class="tribe-events-widget-events-list__event-date-tag-month">
			<?php echo esc_html( $event_month ); ?>
		</span>
		<span class="tribe-events-widget-events-list__event-date-tag-daynum tribe-common-h2 tribe-common-h4--min-medium">
			<?php echo esc_html( $event_day_num ); ?>
		</span>
	</time>
</div>
