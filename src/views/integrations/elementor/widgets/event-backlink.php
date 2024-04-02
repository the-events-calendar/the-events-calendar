<?php
/**
 * View: Elementor Event Header widget - All Events backlink.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/events-header/backlink.php
 *
 * @since TBD
 *
 * @var string $backlink_tag   HTML tag for the backlink.
 * @var string $backlink_label Label for the link.
 * @var string $backlink_class CSS classes.
 * @var string $backlink       URL to the events page.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Header $widget The widget instance.
 */

?>
<<?php echo esc_attr( $backlink_tag ); ?> <?php tribe_classes( $backlink_class ); ?>>
	<a href="<?php echo esc_url( $backlink ); ?>" >&laquo; <?php echo wp_kses_post( $backlink_label ); ?></a>
</<?php echo esc_attr( $backlink_tag ); ?>>
