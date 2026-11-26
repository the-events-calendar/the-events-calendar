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
 * @version 6.18.4.1
 *
 * @since 6.18.4.1 Hides the Organizer details from users who may not see the Organizer.
 *
 */

$organizer = $this->attr( 'organizer' );

if ( ! $organizer ) {
	return;
}

// The attribute holds the Organizer ID, or the Organizer post itself when the block is rendered from code.
$organizer_post = get_post( $organizer );
$is_visible     = $organizer_post instanceof WP_Post
	&& Tribe__Events__Linked_Posts::instance()->is_linked_post_visible( $organizer_post->ID );

$phone   = $is_visible ? tribe_get_organizer_phone( $organizer ) : '';
$website = $is_visible ? tribe_get_organizer_website_link( $organizer ) : '';
$email   = $is_visible ? tribe_get_organizer_email( $organizer ) : '';

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
