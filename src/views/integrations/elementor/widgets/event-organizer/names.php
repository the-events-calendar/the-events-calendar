<?php
/**
 * View: Elementor Event Organizer widget names list.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/names.php
 *
 * @since TBD
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
<<?php echo tag_escape( $organizer_name_tag ); ?> <?php tribe_classes( $widget->get_name_base_class() ); ?>>
	<?php if ( $link_organizer_name ) : ?>
		<a <?php tribe_classes( $widget->get_name_base_class() . '-link' ); ?> href="<?php echo esc_url( $organizer_['link'] ); ?>">
	<?php endif; ?>
		<?php echo esc_html( $organizer['name'] ); ?>
	<?php if ( $link_organizer_name ) : ?>
		</a>
	<?php endif; ?>
</<?php echo tag_escape( $organizer_name_tag ); ?>>
