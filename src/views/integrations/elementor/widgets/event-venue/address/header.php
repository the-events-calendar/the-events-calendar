<?php
/**
 * View: Elementor Single Event Venue widget address header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/event-venue/address/header.php
 *
 * @since 6.4.0
 *
 * @var bool   $show_address_header Whether to show the website header.
 * @var string $address_header_tag  The HTML tag to use for the website header.
 * @var string $address_header_text The website header text.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( ! $show_address_header ) {
	return;
}
?>
<<?php echo tag_escape( $address_header_tag ); ?> <?php tribe_classes( $widget->get_address_base_class() . '-header' ); ?>>
	<?php echo wp_kses_post( $address_header_text ); ?>
<?php echo '</' . tag_escape( $address_header_tag ) . '>'; ?>
