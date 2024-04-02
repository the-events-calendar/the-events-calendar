<?php
/**
 * View: Elementor Event Status widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-title.php
 *
 * @since TBD
 *
 * @var int         $event_id   The event ID.
 */

// No event, no render.
if ( empty( $event_id ) ) {
	return;
}


$this->template( 'event-status/passed' );

$this->template( 'event-status/status' );
