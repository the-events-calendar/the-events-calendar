<?php
/**
 * View: Elementor Event Organizer widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/header.php
 *
 * @since 6.4.0
 *
 * @var bool  $show          Whether to show the organizer header.
 * @var bool  $multiple      Whether there are multiple organizers.
 * @var array $settings      The widget settings.
 * @var int   $event_id      The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show_organizer_header ) {
	return;
}
?>
<<?php echo tag_escape( $organizer_header_tag ); ?> <?php tec_classes( $widget->get_header_class() ); ?>>
	<?php echo esc_html( tribe_get_organizer_label( ! $multiple ) ); ?>
<?php echo '</' . tag_escape( $organizer_header_tag ) . '>'; ?>
