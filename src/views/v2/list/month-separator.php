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
 * @version 4.9.3
 *
 */
$month = $this->get( 'month' );
?>
<div class="tribe-events-calendar-list__separator-month">
	<time class="tribe-events-calendar-list__separator-month-text tribe-common-b1 tribe-common-b1--bold" datetime="1970-01-01T00:00:00+00:00"><?php echo esc_html( $month ); ?></time>
</div>
