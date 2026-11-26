<?php
/**
 * Single Event Meta (Map) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/map.php
 *
 * @since 4.4
 * @since 6.15.3 Added post password protection.
 * @since 6.18.4.1 Hides the map from users who may not see the Venue.
 *
 * @package TribeEventsCalendar
 * @version 6.18.4.1
 */

// Checked before the map is built, because building it hands the address to the map script.
if ( ! Tribe__Events__Linked_Posts::instance()->is_linked_post_visible( absint( tribe_get_venue_id() ) ) ) {
	return;
}

$map = tribe_get_embedded_map(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- The template is included in a function scope.

if ( empty( $map ) ) {
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
