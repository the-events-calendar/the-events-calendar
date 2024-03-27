<?php
/**
 * View: Elementor Single Event Venue widget phone number.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/phone/phone.php
 *
 * @since TBD
 *
 * @var string $venue_id The venue ID.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

$phone = tribe_get_phone( $venue_id );
?>
<p <?php tribe_classes( $widget->get_phone_base_class() . '-number' ); ?> >
	<?php if ( $link_venue_phone ) : ?>
		<?php // For a dial link we remove spaces, and replace 'ext' or 'x' with 'p' to pause before dialing the extension. ?>
		<a <?php tribe_classes( $widget->get_phone_base_class() . '-link' ); ?> href="<?php echo esc_url( 'tel:' . str_ireplace( [ 'ext', 'x', ' ' ], [ 'p', 'p', '' ], $phone ) ); ?>">
	<?php endif; ?>
		<?php echo wp_kses_post( $phone ); ?>
	<?php if ( $link_venue_phone ) : ?>
		</a>
	<?php endif; ?>
</p>
