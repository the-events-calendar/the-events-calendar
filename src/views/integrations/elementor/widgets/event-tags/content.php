<?php
/**
 * View: Elementor Event Tags widget content.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-tags/content.php
 *
 * @since 6.4.0
 *
 * @var string $tag_name The tag name.
 * @var string $tag_link The tag url.
 * @var array  $settings The widget settings.
 * @var int    $event_id The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Tags $widget The widget instance.
 */

// Note: inserting a line break after the closing anchor tag will add a visual "space" between the anchor text and the separator.
?>
<a <?php tec_classes( $widget->get_link_class() ); ?> href="<?php echo esc_url( $tag_link ); ?>">
	<?php echo esc_html( $tag_name ); ?>
</a><span class="<?php tec_classes( $widget->get_link_class() . '-separator' ); ?>"><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
if ( ! $last ) {
	$widget->print_tags_separator();
}
?>
</span>
