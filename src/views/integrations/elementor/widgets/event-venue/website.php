<?php
/**
 * View: Elementor Single Event Venue widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/website.php
 *
 * @since 6.4.0
 * @var bool   $show         Whether to show the website section.
 * @var bool   $show_header  Whether to show the website header.
 * @var string $header_tag   The HTML tag to use for the website header.
 * @var string $header_text  The website header text.
 * @var string $venue_id     The venue ID.
 * @var array  $settings     The widget settings.
 * @var int    $event_id     The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show_website ) ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_website_base_class() ); ?> >
	<?php
	$this->template( 'views/integrations/elementor/widgets/event-venue/website/header' );

	$this->template( 'views/integrations/elementor/widgets/event-venue/website/website' );
	?>
</div>
