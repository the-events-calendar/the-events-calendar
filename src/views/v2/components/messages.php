<?php
/**
 * View: Messages
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/components/messaging.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var array $messges An array of user-facing messages, managed by the View.
 *
 * @package the-events-calendar/views/v2
 */

if ( empty( $messages ) ) {
	return;
}

$classes = [ 'tribe-events-c-messages', 'tribe-common-b2' ];

?>

<div <?php tribe_classes( $classes ); ?>>
	<?php foreach ( $messages as $message_type => $message_group ) : ?>
		<?php foreach ( $message_group as $message ) : ?>
			<div class="tribe-events-c-messages__inner tribe-events-c-messages__inner--<?php echo esc_attr( $message_type ); ?>">
				<span><?php echo wp_kses_post( $message ); ?></span>
			</div>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>

