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
$venue = tribe_get_venue_single_line_address( $event_id );

if ( $venue ) :
	?>
	<!-- Venue Display Info -->
	<div class="tribe-events-venue-details">
		<?php echo $venue; ?>
	</div> <!-- .tribe-events-venue-details -->
	<?php
endif;
