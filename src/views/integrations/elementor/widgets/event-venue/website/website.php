<?php
/**
 * View: Elementor Single Event Venue widget website link.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/website/website.php
 *
 * @since 6.4.0
 *
 * @var string $venue_id The venue ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

?>
<p <?php tec_classes( $widget->get_website_base_class() . '-url' ); ?>>
	<?php echo wp_kses_post( $venue['website'] ); ?>
</p>
