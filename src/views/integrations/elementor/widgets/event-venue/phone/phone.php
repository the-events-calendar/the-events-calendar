<?php
/**
 * View: Elementor Single Event Venue widget phone number.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/phone/phone.php
 *
 * @since 6.4.0
 *
 * @var string $venue_id         The venue ID.
 * @var bool   $link_venue_phone Whether to link the phone number.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show_phone ) ) {
	return;
}
?>
<p <?php tec_classes( $widget->get_phone_base_class() . '-number' ); ?> >
	<?php if ( $link_venue_phone ) : ?>
		<?php // For a dial link we remove spaces, and replace 'ext' or 'x' with 'p' to pause before dialing the extension. ?>
		<a <?php tec_classes( $widget->get_phone_base_class() . '-link' ); ?>
			href="<?php echo esc_url( $venue['phone_link'] ); ?>"
		>
	<?php endif; ?>
		<?php echo wp_kses_post( $venue['phone'] ); ?>
	<?php if ( $link_venue_phone ) : ?>
		</a>
	<?php endif; ?>
</p>
