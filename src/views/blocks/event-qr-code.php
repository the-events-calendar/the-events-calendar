<?php
/**
 * Block: Event QR Code
 *
 * @since TBD
 *
 * @package TEC\Events\Blocks\QR_Code
 */

$qr_code_size = $this->attr( 'qr_code_size' );
$redirection  = $this->attr( 'redirection' );
$event_id     = $this->attr( 'specific_event_id' );
$align        = $this->attr( 'align' );

?>

<div class="tribe-block tec-block__event-qr-code <?php echo esc_attr( $align ); ?>">
	<?php echo do_shortcode( '[tribe_qr_code id="' . $event_id . '" mode="' . $redirection . '" size="' . $qr_code_size . '"]' ); ?>
</div>
