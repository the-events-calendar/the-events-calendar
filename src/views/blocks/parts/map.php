<?php
/**
 * Map template part for the Event Venue block
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/parts/map.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.18.4.1
 * @since 6.2.0 Defer to the block attributes to dictate whether maps should show.
 * @since 6.18.4.1 Hides the map from users who may not see the Venue.
 *
 * @var bool $show_map Whether to show the map or not.
 * @var ?int $venue_id The ID of the venue to display.
 *
 */

$attributes = $this->get( 'attributes', [] );

if ( ! $show_map ) {
	return;
}

// Checked before the map is built, because building it hands the address to the map script.
if ( ! Tribe__Events__Linked_Posts::instance()->is_linked_post_visible( absint( tribe_get_venue_id( $venue_id ) ) ) ) {
	return;
}

$map = tribe_get_embedded_map( $venue_id, 310, 256 ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- The template is included in a function scope.

if ( empty( $map ) ) {
	return;
}

?>

<div class="tribe-block__venue__map">
	<?php
	// Display the map.
	do_action( 'tribe_events_single_meta_map_section_start' );
	echo $map;
	do_action( 'tribe_events_single_meta_map_section_end' );
	?>
</div>
