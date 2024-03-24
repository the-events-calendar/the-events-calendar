<?php
/**
 * View: Elementor Single Event Venue widget phone number.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/venue/event-venue/phone/phone.php
 *
 * @since TBD
 *
 * @var string $venue_id The venue ID.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

?>
<p <?php tribe_classes( $widget->get_phone_base_class() . '-number' ); ?> >
	<?php echo wp_kses_post( tribe_get_phone( $venue_id ) ); ?>
</p>
