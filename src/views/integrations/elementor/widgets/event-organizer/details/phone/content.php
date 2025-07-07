<?php
/**
 * View: Elementor Event Organizer widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details/phone/content.php
 *
 * @since 6.4.0
 *
 * @var array  $organizer The organizer ID.
 * @var array  $settings  The widget settings.
 * @var int    $event_id  The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

?>
<p <?php tec_classes( $widget->get_phone_base_class() ); ?>>
	<?php if ( $link_organizer_phone ) : ?>
		<?php // For a dial link we remove spaces, and replace 'ext' or 'x' with 'p' to pause before dialing the extension. ?>
		<a <?php tec_classes( $widget->get_phone_base_class() . '-link' ); ?> href="<?php echo esc_url( $organizer['phone_link'] ); ?>">
	<?php endif; ?>
		<?php echo esc_html( $organizer['phone'] ); ?>
	<?php if ( $link_organizer_phone ) : ?>
		</a>
	<?php endif; ?>
</p>
