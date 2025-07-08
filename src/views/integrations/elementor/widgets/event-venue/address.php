<?php
/**
 * View: Elementor Single Event Venue widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/address.php
 *
 * @since 6.4.0
 *
 * @var bool   $show          Whether to show the venue address section.
 * @var bool   $show_map_link Whether to show the map link.
 * @var bool   $show_header   Whether to show the address header.
 * @var string $header_tag    The HTML tag to use for the address header.
 * @var string $header_text   The address header text.
 * @var string $venue_id      The venue ID.
 * @var array  $settings      The widget settings.
 * @var int    $event_id      The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show_address ) ) {
	return;
}
?>
<div <?php tec_classes( $widget->get_address_base_class() ); ?> >
	<?php
	// Display address header.
	$this->template( 'views/integrations/elementor/widgets/event-venue/address/header' );

	// Display the address.
	$this->template( 'views/integrations/elementor/widgets/event-venue/address/address' );

	// Display map link.
	$this->template( 'views/integrations/elementor/widgets/event-venue/address/map_link' );
	?>
</div>
