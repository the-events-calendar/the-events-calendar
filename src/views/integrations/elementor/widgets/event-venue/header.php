<?php
/**
 * View: Elementor Single Event Venue widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/venue/event-venue/header.php
 *
 * @since TBD
 *
 * @var bool   $show        Whether to show the venue header.
 * @var string $header_text The venue header.
 * @var string $header_tag  The HTML tag for the section header.
 * @var array  $settings    The widget settings.
 * @var int    $event_id    The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show ) ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_header_classes() ); ?>>
	<?php echo esc_html( $header_text ); ?>
</<?php echo tag_escape( $header_tag ); ?>>
