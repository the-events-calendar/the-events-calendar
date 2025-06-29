<?php
/**
 * View: Elementor Single Event Venue widget address Google Maps link.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/address/map-link.php
 *
 * @since 6.4.0
 *
 * @var string $venue_id               The venue ID.
 * @var bool   $show_address_map_link  Whether to show the venue map link.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show_address_map_link ) || empty( $venue['map_link'] ) ) {
	return;
}
?>
<p <?php tec_classes( $widget->get_address_base_class() . '-map-link' ); ?>>
	<?php echo wp_kses_post( $venue['map_link'] ); ?>
</p>
