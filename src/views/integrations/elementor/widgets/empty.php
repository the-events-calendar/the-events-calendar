<?php
/**
 * View: Elementor empty widget - for display in the editor when a widget is empty.
 * It displays hte widget icon and a message to the user.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/empty.php
 *
 * @since 6.4.0
 *
 * @var Template_Engine $this The template engine.
 */

$widget = $this->get_widget();

$classes = [
	'elementor-widget-empty-icon',
	$widget->get_icon_class(),
];
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Template_Engine;
?>
<div class="tec-events-elementor-widget-empty">
	<i <?php tec_classes( $classes ); ?>></i>
	<p><?php echo esc_html( $widget->get_empty_message() ); ?></p>
</div>
