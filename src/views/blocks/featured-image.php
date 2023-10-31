<?php
/**
 * Block: Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/featured-image.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */

$event_id = $this->get( 'post_id' );

// Get the custom class from the block attributes.
$class = isset( $attributes['className'] ) ? $attributes['className'] : '';

// Generate the featured image HTML.
$featured_image = tribe_event_featured_image( $event_id, 'full', false );

// If a featured image and custom class are present, append the custom class to the parent container.
if ( $featured_image && $class ) {
	$search_pattern  = 'class="tribe-events-event-image"';
	$replace_pattern = 'class="tribe-events-event-image ' . esc_attr( $class ) . '"';
	$featured_image  = str_replace( $search_pattern, $replace_pattern, $featured_image );
}

echo $featured_image;
