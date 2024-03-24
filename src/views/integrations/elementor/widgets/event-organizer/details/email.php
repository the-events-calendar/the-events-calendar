<?php
/**
 * View: Elementor Event Organizer widget details section - email.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/event-organizer/details/email.php
 *
 * @since TBD
 *
 * @var bool   $show        Whether to show the organizer email.
 * @var bool   $show_header Whether to show the organizer email heading.
 * @var string $header_tag  The organizer email header tag.
 * @var int    $organizer   The organizer ID.
 * @var array  $settings    The widget settings.
 * @var int    $event_id    The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_email_base_class() ); ?>>
	<?php
	$this->template(
		'integrations/elementor/widgets/event-organizer/details/email/header',
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
		'integrations/elementor/widgets/event-organizer/details/email/content',
		[
			'organizer' => $organizer,
			'settings'  => $settings,
			'event_id'  => $event_id,
			'widget'    => $widget,
		]
	);
	?>
</div>
