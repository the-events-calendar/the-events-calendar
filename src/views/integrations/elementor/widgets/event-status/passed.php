<?php
/**
 * View: Elementor Event Header widget - Passed.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-status/passed.php
 *
 * @since 6.4.0
 *
 * @var bool   $show_passed        Whether the passed message should be shown.
 * @var string $passed_label       The "passed" message.
 * @var bool   $is_passed          Whether the event has passed.
 * @var string $passed_label_class The CSS classes for the passed label.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Status $widget The widget instance.
 */

if ( ! $is_passed || ! $show_passed ) {
	return;
}
?>
<p <?php tec_classes( $passed_label_class ); ?>><?php echo wp_kses_post( $passed_label ); ?></p>
