<?php
/**
 * View: Elementor Single Event Venue widget address Google Maps link.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/venue/event-venue/address/map-link.php
 *
 * @since TBD
 *
 * @var string $venue_id The venue ID.
 * @var bool   $show     Whether to show the venue map link.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show ) || empty( $venue_id ) ) {
	return;
}

?>

<p <?php tribe_classes( $widget->get_address_base_class() . '-map-link' ); ?>>
	<?php echo wp_kses_post( tribe_get_map_link_html( $venue_id ) ); ?>
</p>
