<?php
/**
 * Renders the event price block
 *
 * @version 0.3.0-alpha
 *
 */
$cost             = $this->attr( 'cost' );
$cost_description = $this->attr( 'costDescription' );
?>
<div class="tribe-block tribe-block__event-price">
	<?php if ( $cost ) : ?>
		<span class="tribe-block__event-price__cost"> <?php echo esc_html( $cost ); ?> </span>
	<?php endif; ?>
	<?php if ( $cost_description ) : ?>
		<span class="tribe-block__event-price__description"><?php echo esc_html( $cost_description ); ?></span>
	<?php endif ?>
</div>
