<?php
/**
 * Widget: Events QR Code
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/widget-events-qr-code.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @since TBD
 *
 * @version TBD
 *
 * @var array<string>        $compatibility_classes      Classes used for the compatibility container.
 * @var array<string>        $container_classes          Classes used for the container of the view.
 * @var array<string,mixed>  $container_data             An additional set of container `data` attributes.
 * @var array<string,string> $messages                   An array of user-facing messages, managed by the View.
 * @var string               $widget_title               The title of the widget.
 * @var string               $qr_code_size               The size of the QR code.
 * @var string               $redirection                The redirection behavior.
 * @var string               $specific_event_id          The specific event ID if redirection is set to specific.
 */

?>
<div <?php tribe_classes( $compatibility_classes ); ?>>
	<div
		<?php tribe_classes( $container_classes ); ?>
		data-js="tribe-events-view"
		<?php foreach ( $container_data as $key => $value ) : ?>
			data-view-<?php echo esc_attr( $key ); ?>="<?php echo esc_attr( $value ); ?>"
		<?php endforeach; ?>
	>
		<div class="tribe-events-widget-events-qr-code">

			<?php if ( ! empty( $widget_title ) ) : ?>
				<header class="tribe-events-widget-events-qr-code__header">
					<h2 class="tribe-events-widget-events-qr-code__header-title tribe-common-h6 tribe-common-h--alt">
						<?php echo esc_html( $widget_title ); ?>
					</h2>
				</header>
			<?php endif; ?>

			<div class="tribe-events-widget-events-qr-code__content">
				<?php
				echo do_shortcode( '[tec_event_qr mode="' . esc_attr( $redirection ) . '" id="' . esc_attr( $specific_event_id ) . '" size="' . esc_attr( $qr_code_size ) . '"]' );
				?>
			</div>
		</div>
	</div>
</div>
