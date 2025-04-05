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
 * @version 6.2.0
 * @since 6.2.0 Defer to the block attributes to dictate whether maps should show.
 *
 * @var bool $show_map Whether to show the map or not.
 * @var ?int $venue_id The ID of the venue to display.
 *
 */

$attributes = $this->get( 'attributes', [] );

if ( ! $show_map ) {
	return;
}

$map = tribe_get_embedded_map( $venue_id, 310, 256 );

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
