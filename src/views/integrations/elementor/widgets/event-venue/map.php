<?php
/**
 * View: Elementor Single Event Venue widget amp.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/map.php
 *
 * @since TBD
 *
 * @var bool   $show Whether to show the venue map.
 * @var string $venue_id The venue ID.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show ) ) {
	return;
}

if ( empty( $venue_id ) ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_map_base_class() ); ?>>
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped -- cannot escape Google map HTML
	echo tribe_get_embedded_map( $venue_id, '100%', '200px' );
	?>
</div>