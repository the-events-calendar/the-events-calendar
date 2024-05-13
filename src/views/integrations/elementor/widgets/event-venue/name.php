<?php
/**
 * View: Elementor Single Event Venue widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/single-venue.php
 *
 * @since 6.4.0
 *
 * @var bool   $show     Whether to show the venue name.
 * @var string $venue_id The venue ID.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show_name ) ) {
	return;
}
?>
<<?php echo tag_escape( $name_tag ); ?> <?php tribe_classes( $widget->get_name_base_class() ); ?>>
	<?php echo wp_kses_post( $venue['name'] ); ?>
<?php echo '</' . tag_escape( $name_tag ) . '>'; ?>
