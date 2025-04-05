<?php
/**
 * Block: Event Price
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-price.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */

$cost             = $this->attr( 'cost' );
$cost_description = $this->attr( 'costDescription' );

$default_classes = [ 'tribe-block', 'tribe-block__event-price' ];

// Add the custom classes from the block attributes.
$classes = isset( $attributes['className'] ) ? array_merge( $default_classes, [ $attributes['className'] ] ) : $default_classes;
?>
<div <?php tribe_classes( $classes ); ?>>
	<?php if ( $cost ) : ?>
		<span class="tribe-block__event-price__cost"> <?php echo esc_html( $cost ); ?> </span>
	<?php endif; ?>
	<?php if ( $cost_description ) : ?>
		<span class="tribe-block__event-price__description"><?php echo esc_html( $cost_description ); ?></span>
	<?php endif ?>
</div>
