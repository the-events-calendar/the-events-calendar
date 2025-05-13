<?php
/**
 * QR Code Meta Box Template
 *
 * @since 6.12.0
 *
 * @var string $url The URL of the QR code.
 * @var string $label The label of the QR code.
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<p>
	<a type="button" class="button thickbox js-tec-qr-code-metabox" href="<?php echo esc_url( $url ); ?>" title="<?php esc_attr_e( 'QR Code', 'the-events-calendar' ); ?>"><?php echo esc_html( $label ); ?></a>
</p>
