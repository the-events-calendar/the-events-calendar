<?php
/**
 * Single Event Meta (Map) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/map.php
 *
 * @since 4.4
 * @since 6.15.3 Added post password protection.
 *
 * @package TribeEventsCalendar
 * @version 6.15.3
 */

$map = tribe_get_embedded_map();

if ( empty( $map ) ) {
	return;
}

if ( post_password_required( tribe_get_venue_id() ) ) {
	return;
}

?>

<div class="tribe-events-venue-map">
	<?php
	// Display the map.
	do_action( 'tribe_events_single_meta_map_section_start' );
	echo $map;
	do_action( 'tribe_events_single_meta_map_section_end' );
	?>
</div>
