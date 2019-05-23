<?php
/**
 * View: Month View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$events = $this->get( 'events' );

$this->template( 'events-bar' );

$this->template( 'top-bar' );

?>

<div class="tribe-events-calendar-month">
	<h1>Welcome to the month view</h1>
</div>