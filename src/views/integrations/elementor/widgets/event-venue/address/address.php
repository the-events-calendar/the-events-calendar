<?php
/**
 * View: Elementor Single Event Venue widget address address content.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/address/address.php
 *
 * @since 6.4.0
 *
 * @var string $venue_id        The venue ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $venue['address'] ) ) {
	return;
}
?>
<address <?php tec_classes( $widget->get_address_base_class() . '-address' ); ?>>
	<?php echo wp_kses_post( $venue['address'] ); ?>
</address>
