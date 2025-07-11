<?php
/**
 * View: Messages Error Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/messages/error-icon.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 5.3.0
 * @since 6.14.0 Added $icon_description parameter and updated the template to use it for the accessible label.
 *
 * @version 6.14.0
 *
 * @var string $icon_description The description of the icon. Used for the accessible label. (optional)
 */

if ( empty( $icon_description ) ) {
	$icon_description = __( 'Error', 'the-events-calendar' );
}

 $this->template( 'components/icons/error', [ 'classes' => [ 'tribe-events-c-messages__message-icon-svg' ] ] );
?>
<span class="tribe-common-a11y-visual-hide">
	<?php echo esc_html( $icon_description ); ?>
</span>
