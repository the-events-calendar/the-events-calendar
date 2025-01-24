<?php
/**
 * View: Elementor Event Export widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-export/list-item.php
 *
 * @since 6.4.0
 *
 * @var array        $link           The link and label. In the format:
 *                                   [
 *                                       'label' => string,
 *                                       'link'  => string,
 *                                       'class' => array,
 *                                   ]
 * @var bool         $should_display Whether to show the widget.
 * @var int          $event_id       The event ID.
 * @var Event_Export $widget         The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Export;

// No url, no render.
if ( empty( $link ) || ! $should_display ) {
	return;
}
?>
<li <?php tribe_classes( $link['class'] ); ?>>
	<?php
	$this->template( 'widgets/event-export/link' );
	?>
</li>
