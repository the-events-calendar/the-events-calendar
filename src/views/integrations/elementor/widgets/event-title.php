<?php
/**
 * View: Elementor Event Title widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-title.php
 *
 * @since 6.4.0
 *
 * @var string      $header_tag The HTML tag for the event title.
 * @var int         $event_id   The event ID.
 * @var string      $title      The event title.
 * @var Event_Title $widget     The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Title;

// No title, no render.
if ( empty( $title ) ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?> <?php tec_classes( $widget->get_widget_class() ); ?>>
	<?php echo wp_kses_post( $title ); ?>
<?php echo '</' . tag_escape( $header_tag ) . '>'; ?>
