<?php
/**
 * Widget: Events QR Code
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/widget-events-qr-code.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 6.12.0
 * @since 6.17.5.1 Rendered the QR code through the shortcode manager.
 *
 * @version 6.17.5.1
 *
 * @var array<string>        $compatibility_classes      Classes used for the compatibility container.
 * @var array<string>        $container_classes          Classes used for the container of the view.
 * @var array<string,mixed>  $container_data             An additional set of container `data` attributes.
 * @var array<string,string> $messages                   An array of user-facing messages, managed by the View.
 * @var string               $widget_title               The title of the widget.
 * @var string               $qr_code_size               The size of the QR code.
 * @var string               $redirection                The redirection behavior.
 * @var string               $event_id                   The specific event ID if redirection is set to specific.
 * @var string               $series_id                  The series ID if redirection is set to next.
 */

use TEC\Events\QR\Controller as QR_Controller;
use Tribe\Shortcode\Manager as Shortcode_Manager;

$qr_id = 'next' === $redirection ? $series_id : $event_id;

?>
<div <?php tec_classes( $compatibility_classes ); ?>>
	<div
		<?php tec_classes( $container_classes ); ?>
		data-js="tribe-events-view"
		<?php foreach ( $container_data as $key => $value ) : ?>
			data-view-<?php echo esc_attr( $key ); ?>="<?php echo esc_attr( $value ); ?>"
		<?php endforeach; ?>
	>
		<div class="tribe-events-widget-events-qr-code">

			<?php $this->template( 'components/json-ld-data' ); ?>

			<?php $this->template( 'components/data' ); ?>

			<?php if ( ! empty( $widget_title ) ) : ?>
				<header class="tribe-events-widget-events-qr-code__header">
					<h2 class="tribe-events-widget-events-qr-code__header-title tribe-common-h6 tribe-common-h--alt">
						<?php echo esc_html( $widget_title ); ?>
					</h2>
				</header>
			<?php endif; ?>

			<div class="tribe-events-widget-events-qr-code__content">
				<?php
				// Pass the arguments as an array so request-supplied values are never parsed as shortcode markup.
				// phpcs:disable StellarWP.XSS.EscapeOutput.OutputNotEscaped -- Shortcode output is safe to be rendered.
				echo tribe( Shortcode_Manager::class )->render_shortcode(
					[
						'mode' => $redirection,
						'id'   => $qr_id,
						'size' => $qr_code_size,
					],
					'',
					QR_Controller::QR_SLUG
				);
				// phpcs:enable StellarWP.XSS.EscapeOutput.OutputNotEscaped
				?>
			</div>
		</div>
	</div>
</div>
