<?php
/**
 * View: Elementor Event Organizer widget names list.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/names.php
 *
 * @since 6.4.0
 *
 * @var bool   $show          Whether to show the organizer names.
 * @var bool   $link_name     Whether to link the organizer name.
 * @var bool   $multiple      Whether there are multiple organizers.
 * @var string $organizer     The organizer ID.
 * @var array  $settings      The widget settings.
 * @var int    $event_id      The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

if ( ! $show_organizer_name ) {
	return;
}

if ( empty( $organizer ) ) {
	return;
}
?>
<<?php echo tag_escape( $organizer_name_tag ); ?> <?php tec_classes( $widget->get_name_base_class() ); ?>>
	<?php echo esc_html( $organizer['name'] ); ?>
<?php echo '</' . tag_escape( $organizer_name_tag ) . '>'; ?>
