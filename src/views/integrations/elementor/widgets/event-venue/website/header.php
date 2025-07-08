<?php
/**
 * View: Elementor Single Event Venue widget website header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/website/header.php
 *
 * @since 6.4.0
 *
 * @var bool   $show_website_header Whether to show the website header.
 * @var string $website_header_tag  The HTML tag to use for the website header.
 * @var string $website_header_text The website header text.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( ! $show_website_header ) {
	return;
}
?>
<<?php echo tag_escape( $website_header_tag ); ?> <?php tec_classes( $widget->get_website_base_class() . '-header' ); ?>>
	<?php echo wp_kses_post( $website_header_text ); ?>
<?php echo '</' . tag_escape( $website_header_tag ) . '>'; ?>
