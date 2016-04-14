<?php
/**
 * Embed Venue Meta Template
 *
 * The venue template for the embed view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/embed/venue.php
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$event_id = get_the_ID();

// Setup an array of venue details for use later in the template
$venue_post_type = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE );
$do_venue_link = empty( $venue_post_type->exclude_from_search );

$venue = tribe_get_venue_single_line_address( $event_id, $do_venue_link );

if ( ! $venue ) {
	return;
}
?>
<!-- Venue Display Info -->
<div class="tribe-events-venue-details">
	<?php echo $venue; ?>
</div> <!-- .tribe-events-venue-details -->
