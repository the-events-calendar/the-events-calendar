<?php
/**
 * View: Elementor Event Header widget - Passed.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-header/passed.php
 *
 * @since TBD
 *
 * @var string $label     The "passed" message.
 * @var bool   $is_passed Whether the event has passed.
 * @var int    $event_id  The event ID.
 * @var string $title     The event title.
 */

if ( ! $is_passed ) {
	return;
}
?>
<p <?php tribe_classes( $widget->get_element_classes() ); ?>><?php echo wp_kses_post( $label ); ?></p>
