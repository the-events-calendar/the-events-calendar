<?php
/**
 * View: Elementor Event Header widget - All Events backlink.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/events-header/backlink.php
 *
 * @since TBD
 *
 * @var string esc_attr( $html_tag ) HTML tag for the backlink.
 * @var string $label                Label for the link.
 * @var string $link                 URL to the all events page.
 */

?>
<<?php echo esc_attr( $html_tag ); ?> <?php tribe_classes( $widget->get_element_classes() ); ?>>
<a
	href="<?php echo esc_url( $link ); ?>"
	<?php if ( ! empty( $align ) ) : ?>
		style="text-align: <?php echo esc_attr( $align ); ?>;"
	<?php endif; ?>
>&laquo; <?php echo wp_kses_post( $label ); ?></a>
</<?php echo esc_attr( $html_tag ); ?>>
