<?php
/**
 * View: Elementor Single Event Venue widget phone header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/phone/header.php
 *
 * @since 6.4.0
 *
 * @var bool   $show_phone_header Whether to show the website header.
 * @var string $phone_header_tag  The HTML tag to use for the website header.
 * @var string $phone_header_text The website header text.
 * @var string $venue_id          The venue ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( ! $show_phone_header ) {
	return;
}
?>
<<?php echo tag_escape( $phone_header_tag ); ?> <?php tribe_classes( $widget->get_phone_base_class() . '-header' ); ?>>
	<?php echo wp_kses_post( $phone_header_text ); ?>
<?php echo '</' . tag_escape( $phone_header_tag ) . '>'; ?>
