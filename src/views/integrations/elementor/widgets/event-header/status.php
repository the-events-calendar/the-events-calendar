<?php
/**
 * View: Elementor Event Header widget - Status.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-header/status.php
 *
 * @since TBD
 *
 * @var bool   $show          If the widget should be shown.
 * @var string $status        The status.
 * @var string $status_label  The status label.
 * @var string $status_reason The status reason.
 * @var int    $event_id      The event ID.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Status $widget The widget instance.
 */

if ( ! $show || empty( $status_label ) || empty( $status ) ) {
	return;
}

?>
<div <?php tribe_classes( $widget->get_element_classes() ); ?>>
	<div <?php tribe_classes( $widget->get_label_class(), $widget->get_status_class( $status ) ); ?>><?php echo esc_html( $status_label ); ?></div>
	<?php if ( ! empty( $status_reason ) ) : ?>
		<div <?php tribe_classes( $widget->get_description_class() ); ?>><?php echo wp_kses_post( $status_reason ); ?></div>
	<?php endif; ?>
</div>
