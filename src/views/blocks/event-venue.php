<?php
/**
 * Block: Event Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-venue.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.18.4.1
 * @since 6.2.0 Reworked class handling.
 * @since 6.18.4.1 Leaves out the map class for users who may not see the Venue.
 *
 * @var bool $show_map Whether to show the map or not.
 */

$event_id        = $this->get( 'post_id' );
$default_classes = [
	'tribe-block',
	'tribe-block__venue',
	'tribe-clearfix',
	'tribe-block__venue--has-map' => $show_map && Tribe__Events__Linked_Posts::instance()->is_linked_post_visible( absint( tribe_get_venue_id( $venue_id ?? null ) ) ),
];

// Add the custom classes from the block attributes.
$classes = isset( $attributes['className'] ) ? array_merge( $default_classes, [ $attributes['className'] ] ) : $default_classes;
?>
<div <?php tec_classes( $classes ); ?>>
	<?php do_action( 'tribe_events_single_event_meta_secondary_section_start' ); ?>

	<?php $this->template( 'blocks/parts/venue' ); ?>
	<?php $this->template( 'blocks/parts/map' ); ?>

	<?php do_action( 'tribe_events_single_event_meta_secondary_section_end' ); ?>
</div>
