<?php
/**
 * Block: Event Organizer
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-organizer.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.1
 *
 */

$organizer = $this->attr( 'organizer' );

if ( ! $organizer ) {
	return;
}

$phone   = tribe_get_organizer_phone( $organizer );
$website = tribe_get_organizer_website_link( $organizer );
$email   = tribe_get_organizer_email( $organizer );

$default_classes = [ 'tribe-block', 'tribe-block__organizer__details', 'tribe-clearfix' ];

// Add the custom classes from the block attributes.
$classes = isset( $attributes['className'] ) ? array_merge( $default_classes, [ $attributes['className'] ] ) : $default_classes;
?>
<div <?php tec_classes( $classes ); ?>>
	<div class="tribe-block__organizer__title">
		<h3><?php echo tribe_get_organizer_link( $organizer ); ?></h3>
	</div>
	<?php if ( ! empty( $phone ) ) : ?>
		<p class="tribe-block__organizer__phone"><?php echo esc_html( $phone ); ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $website ) ) : ?>
		<p class="tribe-block__organizer__website"><?php echo $website; ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $email ) ) : ?>
		<p class="tribe-block__organizer__email"><?php echo esc_html( $email ); ?></p>
	<?php endif; ?>
</div>
