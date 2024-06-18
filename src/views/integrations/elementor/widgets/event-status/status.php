<?php
/**
 * View: Elementor Event Header widget - Status.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-status/status.php
 *
 * @since 6.4.0
 *
 * @var bool   $show_status       Whether the status should be shown.
 * @var string $description_class The CSS classes for the description.
 * @var string $label_class       The CSS classes for the label.
 * @var string $status            The status.
 * @var string $status_label      The status label text.
 * @var string $status_reason     The status reason.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Status $widget The widget instance.
 */

if ( ! $show_status || empty( $status ) ) {
	return;
}

?>
<div <?php tribe_classes( $widget->get_element_classes() ); ?>>
	<div <?php tribe_classes( $label_class, $widget->get_status_class( $status ) ); ?>><?php echo esc_html( $status_label ); ?></div>
	<?php if ( ! empty( $status_reason ) ) : ?>
		<div <?php tribe_classes( $description_class ); ?>><?php echo wp_kses_post( $status_reason ); ?></div>
	<?php endif; ?>
</div>
