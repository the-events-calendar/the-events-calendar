<?php
/**
 * View: Elementor Event Export widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-export/link.php
 *
 * @since 6.4.0
 *
 * @var array        $link     The link and label. In the format:
 *                             [
 *                                 'label' => string,
 *                                 'link'  => string,
 *                                 'class' => array,
 *                             ]
 * @var int          $event_id The event ID.
 * @var Event_Export $widget   The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Export;

// No url, no render.
if ( empty( $link ) ) {
	return;
}
?>
<a
	<?php tribe_classes( $widget->get_link_class() ); ?>
	href="<?php echo esc_url( $link['link'] ); ?>"
	target="_blank"
	rel="noopener noreferrer"
><?php echo esc_html( $link['label'] ); ?></a>
