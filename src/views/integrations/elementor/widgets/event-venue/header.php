<?php
/**
 * View: Elementor Single Event Venue widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/header.php
 *
 * @since 6.4.0
 *
 * @var bool   $show_widget_header Whether to show the venue header.
 * @var string $header_text        The venue header.
 * @var string $header_tag         The HTML tag for the section header.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $show_widget_header ) ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_header_class() ); ?>>
	<?php echo esc_html( $header_text ); ?>
<?php echo '</' . tag_escape( $header_tag ) . '>'; ?>
