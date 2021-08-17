<?php
/**
 * View: Messages
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/messages.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.7.0
 *
 * @var array<string,array<string>> $messages   An array of user-facing messages, managed by the View.
 * @var array<string,mixed>         $attributes A optional map of attributes that should be applied to the wrapper div element.
 * @var string                      $wp_version Global WP version.
 *
 * @package the-events-calendar/views/v2
 */

if ( empty( $messages ) ) {
	return;
}

global $wp_version;

$default_classes = [ 'tribe-events-header__messages', 'tribe-events-c-messages', 'tribe-common-b2' ];
$classes         = isset( $classes ) ? array_merge( $default_classes, $classes ) : $default_classes;
$attributes = isset( $attributes ) ? (array) $attributes : [];

?>
<div <?php tribe_classes( $classes ); ?> <?php tribe_attributes( $attributes ); ?>>
	<?php foreach ( $messages as $message_type => $message_group ) : ?>
		<div class="tribe-events-c-messages__message tribe-events-c-messages__message--<?php echo esc_attr( $message_type ); ?>" role="alert">
			<?php $this->template( 'components/messages/' . esc_attr( $message_type ) . '-icon' ); ?>
			<ul class="tribe-events-c-messages__message-list">
				<?php foreach ( $message_group as $key => $message ) : ?>
					<li
						class="tribe-events-c-messages__message-list-item"
						<?php tribe_attributes( [ 'data-key' => (string)$key ] ); ?>
					>
					<?php echo version_compare( $wp_version, '5.0', '>=' ) ? wp_kses_post( $message ) : $message; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endforeach; ?>
</div>
