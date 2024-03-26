<?php
/**
 * View: Elementor Event Organizer widget details section - website.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details/website.php
 *
 * @since TBD
 *
 * @var bool   $show        Whether to show the organizer website.
 * @var bool   $show_header Whether to show the organizer website heading.
 * @var string $header_tag  The organizer website header tag.
 * @var string $header_text The organizer website header text.
 * @var string $website     The organizer website.
 * @var int    $organizer   The organizer ID.
 * @var array  $settings    The widget settings.
 * @var int    $event_id    The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show ) {
	return;
}

?>
<div <?php tribe_classes( $widget->get_website_base_class() ); ?>>
	<?php
	$this->template(
		'integrations/elementor/widgets/event-organizer/details/website/header',
		[
			'show'        => $show_header,
			'header_tag'  => $header_tag,
			'header_text' => $header_text,
			'organizer'   => $organizer,
			'settings'    => $settings,
			'event_id'    => $event_id,
			'widget'      => $widget,
		]
	);

	$this->template(
		'integrations/elementor/widgets/event-organizer/details/website/content',
		[
			'organizer' => $organizer,
			'settings'  => $settings,
			'event_id'  => $event_id,
			'widget'    => $widget,
		]
	);
	?>
</div>
