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
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show ) ) {
	return;
}

$url = $link ? tribe_get_venue_link( $venue_id, false ) : false;
?>
<<?php echo tag_escape( $tag ); ?> <?php tribe_classes( $widget->get_name_base_class() ); ?>>
	<?php if ( $url ) : ?>
		<a <?php tribe_classes( $widget->get_name_base_class() . '-link' ); ?> href="<?php echo esc_url( $url ); ?>">
	<?php endif; ?>
	<?php echo wp_kses_post( tribe_get_venue( $venue_id ) ); ?>
	<?php if ( $url ) : ?>
		</a>
	<?php endif; ?>
</<?php echo tag_escape( $tag ); ?>>
