<?php
/**
 * View: List View Month separator
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/month-separator.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
$month = $this->get( 'month' );
?>
<div class="tribe-events-calendar-list__separator-month">
	<span class="tribe-events-calendar-list__separator-month-text"><?php echo esc_html( $month ); ?></span>
</div>
