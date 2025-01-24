<?php
/**
 * View: Elementor Event Organizer widget details section - phone.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details/phone.php
 *
 * @since 6.4.0
 *
 * @var bool   $show        Whether to show the organizer phone.
 * @var bool   $show_header Whether to show the organizer phone header.
 * @var string $header_tag  The organizer phone header tag.
 * @var int    $organizer   The organizer ID.
 * @var array  $settings    The widget settings.
 * @var int    $event_id    The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show_organizer_phone ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_phone_wrapper_class() ); ?>>
	<?php
	$this->template( 'integrations/elementor/widgets/event-organizer/details/phone/header' );

	$this->template( 'integrations/elementor/widgets/event-organizer/details/phone/content' );
	?>
</div>
