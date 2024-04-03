<?php
/**
 * View: Elementor Event Header widget - Passed.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-header/passed.php
 *
 * @since TBD
 *
 * @var string $passed_label The "passed" message.
 * @var bool   $is_passed    Whether the event has passed.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Status $widget The widget instance.
 */

if ( ! $is_passed || ! $show_passed ) {
	return;
}
?>
<p <?php tribe_classes( $widget->get_passed_label_class() ); ?>><?php echo wp_kses_post( $passed_label ); ?></p>
