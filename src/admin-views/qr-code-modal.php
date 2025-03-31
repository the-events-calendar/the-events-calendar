<?php
/**
 * QR Code Modal Template
 *
 * @since TBD
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="tec-events-qr-modal">
	<div class="tec-events-qr-modal__container">
		<div class="tec-events-qr-modal__left">
			<img src="<?php echo wp_kses_data( $qr_img ); ?>" <?php echo esc_attr( $atts ); ?> class="tec-events-qr-modal__image">
		</div>
		<div class="tec-events-qr-modal__right">
		<div><?php esc_html_e( 'EVENT', 'the-events-calendar' ); ?></div>
			<div class="tec-events-qr-modal__title"><?php echo esc_html( $title ); ?></div>
		<div><?php esc_html_e( 'SIZE', 'the-events-calendar' ); ?></div>
			<div class="tec-events-qr-modal__select">
				<label for="tec-events-qr-code-size" class="screen-reader-text">
					<?php esc_html_e( 'QR Code Size', 'the-events-calendar' ); ?>
				</label>
				<div class="tec-events-qr-modal__select-wrapper">
					<select id="tec-events-qr-code-size" class="tec-events-qr-modal__select-input">
						<option value="4" selected><?php esc_html_e( '125 x 125', 'the-events-calendar' ); ?></option>
						<option value="8"><?php esc_html_e( '250 x 250', 'the-events-calendar' ); ?></option>
						<option value="12"><?php esc_html_e( '420 x 420', 'the-events-calendar' ); ?></option>
						<option value="21"><?php esc_html_e( '650 x 650', 'the-events-calendar' ); ?></option>
						<option value="32"><?php esc_html_e( '1000 x 1000', 'the-events-calendar' ); ?></option>
					</select>
					<span class="tec-events-qr-modal__select-unit">px</span>
				</div>
			</div>
			<div><?php esc_html_e( 'The value corresponds to the width and height of the QR code.', 'the-events-calendar' ); ?></div>
		</div>
	</div>
	<div class="tec-events-qr-modal__buttons">
		<button type="button" class="button js-tec-close-modal"><?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?></button>
		<button type="button" class="button button-primary" download="<?php echo esc_url( $qr_url ); ?>"><?php esc_html_e( 'Download', 'the-events-calendar' ); ?></button>
	</div>
</div>
