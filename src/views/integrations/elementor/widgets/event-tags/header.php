<?php
/**
 * View: Elementor Event Tags widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-tags/header.php
 *
 * @since 6.4.0
 *
 * @var string $alignment         The text alignment.
 * @var bool   $show_tags_header  Whether to show the header.
 * @var string $header_tag        The HTML tag for the header.
 * @var string $label_text        The label text.
 * @var array  $settings          The widget settings.
 * @var int    $event_id          The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Tags $widget The widget instance.
 */

if ( ! $show_tags_header ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?> <?php tribe_classes( $widget->get_header_class() ); ?>class="tec-events-event-tags-label">
	<?php echo esc_html( $label_text ); ?>
<?php echo '</' . tag_escape( $header_tag ) . '>'; ?>
