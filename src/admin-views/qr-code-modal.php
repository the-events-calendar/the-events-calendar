<?php
/**
 * QR Code Modal Template
 *
 * @since 6.12.0
 *
 * @var string $title The title of the event.
 * @var string $placeholder The placeholder QR code image URL.
 * @var array  $qr_images The uploaded QR code images.
 * @var string $qr_url The QR code redirect URL.
 * @var string $alt The alt text for the QR code image.
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="tec-events-qr-modal">
	<div class="tec-events-qr-modal__container">
		<div class="tec-events-qr-modal__left">
			<img src="<?php echo wp_kses_data( $placeholder ); ?>" alt="<?php echo esc_attr( $alt ); ?>" class="tec-events-qr-modal__image" data-url="<?php echo esc_url( $qr_url ); ?>">
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
					<select class="tec-events-qr-code__size-select" id="tec-events-qr-code-size">
						<?php foreach ( $qr_images as $size => $url ) : ?>
							<option value="<?php echo esc_attr( $url ); ?>" <?php selected( $size, 8 ); ?>>
								<?php echo (int) $size * 35; ?> x <?php echo (int) $size * 35; ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="tec-events-qr-modal__select-unit">px</span>
				</div>
			</div>
			<div><?php esc_html_e( 'The value corresponds to the width and height of the QR code image in pixels.', 'the-events-calendar' ); ?></div>
		</div>
	</div>
	<div class="tec-events-qr-modal__buttons">
		<button type="button" class="button js-tec-close-modal"><?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?></button>
		<a type="button" class="button button-primary js-tec-download-qr-code" href="<?php echo esc_url( $qr_images[8] ); ?>" download target="_blank"><?php esc_html_e( 'Download', 'the-events-calendar' ); ?></a>
	</div>
</div>

<?php /* The above template is dynamically injected by Thickbox thus we need to inline the script. */ ?>
<script>
jQuery(document).ready(function($) {
	const responsiveModal = () => {
		$('#TB_window').css('width', ($(window).width()));
		$('#TB_ajaxContent').css('width', ($(window).width()));
	}
	$('.js-tec-close-modal').on('click', function(e) {
		e.preventDefault();
		$('.tb-close-icon').trigger('click');
	});
	$('#tec-events-qr-code-size').on('change', function(e) {
		$('.js-tec-download-qr-code').attr('href', $(this).val());
	});
	$(window).resize(function() {
		responsiveModal();
	});
	responsiveModal();
});
</script>
