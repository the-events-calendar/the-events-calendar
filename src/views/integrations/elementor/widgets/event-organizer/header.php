<?php
/**
 * View: Elementor Event Organizer widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/event-organizer/header.php
 *
 * @since TBD
 *
 * @var bool  $show          Whether to show the organizer heading.
 * @var bool  $multiple      Whether there are multiple organizers.
 * @var array $settings      The widget settings.
 * @var int   $event_id      The event ID.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_widget_header_classes() ); ?>>
	<?php echo esc_html( tribe_get_organizer_label( ! $multiple ) ); ?>
</<?php echo tag_escape( $header_tag ); ?>>
