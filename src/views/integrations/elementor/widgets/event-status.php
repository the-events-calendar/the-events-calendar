<?php
/**
 * View: Elementor Event Status widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-status.php
 *
 * @since 6.4.0
 *
 * @var bool   $is_passed          Whether the event has passed.
 * @var bool   $show_passed        Whether the passed message should be shown.
 * @var bool   $show_status        Whether the status should be shown.
 * @var string $description_class  The CSS classes for the description.
 * @var string $label_class        The CSS classes for the label.
 * @var string $passed_label       The event passed label.
 * @var string $passed_label_class The CSS classes for the passed label.
 * @var string $status             The status.
 * @var string $status_label       The status label text.
 * @var string $status_reason      The status reason.
 */

// No event, no render.
if ( ! $this->get_widget()->should_show_mock_data() && ! $this->has_event() ) {
	return;
}

$this->template( 'views/integrations/elementor/widgets/event-status/passed' );

$this->template( 'views/integrations/elementor/widgets/event-status/status' );
