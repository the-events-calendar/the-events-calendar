<?php
/**
 * View: Elementor Event Cost widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-cost.php
 *
 * @since TBD
 *
 * @var string     $header_tag The HTML tag for the event cost.
 * @var int        $event_id   The event ID.
 * @var string     $cost       The event cost.
 * @var Event_Cost $widget     The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Cost;

if ( empty( $cost ) ) {
	return;
}
?>
<<?php echo tag_escape( $header_tag ); ?><?php tribe_classes( $widget->get_element_classes() ); ?>>
<?php echo esc_html( $cost ); ?>
</<?php echo tag_escape( $header_tag ); ?>>
