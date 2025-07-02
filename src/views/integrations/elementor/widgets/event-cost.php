<?php
/**
 * View: Elementor Event Cost widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-cost.php
 *
 * @since 6.4.0
 *
 * @var string     $html_tag The HTML tag for the event cost.
 * @var int        $event_id   The event ID.
 * @var string     $cost       The event cost.
 * @var Event_Cost $widget     The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Cost;

if ( empty( $cost ) ) {
	return;
}
?>
<?php $this->template( 'views/integrations/elementor/widgets/event-cost/header' ); ?>
<<?php echo tag_escape( $html_tag ); ?><?php tec_classes( $widget->get_element_classes() ); ?>>
<?php echo esc_html( $cost ); ?>
<?php echo '</' . tag_escape( $html_tag ) . '>'; ?>
