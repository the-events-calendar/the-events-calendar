<?php
/**
 * View: Day Single Event Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day/event/venue.php
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
 *
 */

// Setup an array of venue details for use later in the template
$venue_details = tribe_get_venue_details( $event->ID );

if ( ! $venue_details ) {
	return;
}
?>
<address class="tribe-events-calendar-day__event-venue tribe-common-b2">
	<span class="tribe-events-calendar-day__event-venue-title tribe-common-b2--bold">
		<?php echo isset( $venue_details['linked_name'] ) ? $venue_details['linked_name'] : esc_html__( 'Venue Name', 'the-events-calendar' ); ?>
	</span>
	<span class="tribe-events-calendar-day__event-venue-address">
		<?php echo isset( $venue_details['address'] ) ? $venue_details['address'] : ''; ?>
	</span>
</address>
