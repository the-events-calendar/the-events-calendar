<?php
/**
 * Renders the event organizer block
 *
 * @version 0.3.0-alpha
 *
 */

$organizer = $this->attr( 'organizer' );

if ( ! $organizer ) {
	return;
}

$phone   = tribe_get_organizer_phone( $organizer );
$website = tribe_get_organizer_website_link( $organizer );
$email   = tribe_get_organizer_email( $organizer );

?>
<div class="tribe-block tribe-block__organizer__details tribe-clearfix">
	<div class="tribe-block__organizer__title">
		<h3><?php echo tribe_get_organizer( $organizer ); ?></h3>
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
