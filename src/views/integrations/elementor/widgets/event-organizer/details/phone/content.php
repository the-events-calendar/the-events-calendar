<?php
/**
 * View: Elementor Event Organizer widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details/phone/content.php
 *
 * @since TBD
 *
 * @var array  $organizer The organizer ID.
 * @var array  $settings  The widget settings.
 * @var int    $event_id  The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

$phone = tribe_get_organizer_phone( $organizer );
?>
<p <?php tribe_classes( $widget->get_phone_base_class() ); ?>>
	<?php if ( $link_organizer_phone ) : ?>
		<?php // For a dial link we remove spaces, and replace 'ext' or 'x' with 'p' to pause before dialing the extension. ?>
		<a <?php tribe_classes( $widget->get_phone_base_class() . '-link' ); ?> href="<?php echo esc_url( 'tel:' . str_ireplace( [ 'ext', 'x', ' ' ], [ 'p', 'p', '' ], $phone ) ); ?>">
	<?php endif; ?>
		<?php echo esc_html( $phone ); ?>
	<?php if ( $link_organizer_phone ) : ?>
		</a>
	<?php endif; ?>
</p>
