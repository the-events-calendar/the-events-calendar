<?php
/**
 * View: Elementor Event Website widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-website/header.php
 *
 * @since 6.4.0
 *
 * @var bool   $show_website_header  Whether to show the header.
 * @var string $header_tag           The HTML tag for the header.
 * @var string $header_class         The class for the link header.
 */

if ( ! $show_website_header ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?> <?php tec_classes( $header_class ); ?>>
	<?php printf( esc_html__( 'Website:', 'the-events-calendar' ) ); ?>
<?php echo '</' . tag_escape( $header_tag ) . '>'; ?>
