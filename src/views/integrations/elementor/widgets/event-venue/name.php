<?php
/**
 * View: Elementor Single Event Venue widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/single-venue.php
 *
 * @since TBD
 *
 * @var bool   $show     Whether to show the venue name.
 * @var bool   $link     Whether to link the venue name.
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
	<?php if ( $venue['name_link'] ) : ?>
		<a <?php tribe_classes( $widget->get_name_base_class() . '-link' ); ?> href="<?php echo esc_url( $venue['name_link'] ); ?>">
	<?php endif; ?>
	<?php echo wp_kses_post( $venue['name'] ); ?>
	<?php if ( $venue['name_link'] ) : ?>
		</a>
	<?php endif; ?>
</<?php echo tag_escape( $name_tag ); ?>>
