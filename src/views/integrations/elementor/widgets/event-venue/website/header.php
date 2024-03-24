<?php
/**
 * View: Elementor Single Event Venue widget website header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/venue/event-venue/website/header.php
 *
 * @since TBD
 *
 * @var bool   $show Whether to show the website header.
 * @var string $header_tag The HTML tag to use for the website header.
 * @var string $header_text The website header text.
 * @var string $venue_id The venue ID.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( ! $show ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_website_base_class() . '-header' ); ?>>
	<?php echo wp_kses_post( $header_text ); ?>
</<?php echo tag_escape( $header_tag ); ?>>
