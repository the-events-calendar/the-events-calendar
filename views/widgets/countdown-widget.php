<?php
/**
 * Events Pro Countdown Widget
 * This is the template for the output of the event countdown widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/countdown-widget.php
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<div class="tribe-countdown-timer tribe-clearfix">
	<div class="tribe-countdown-days tribe-countdown-number">DD<br />
		<span class="tribe-countdown-under"><?php _e('days', 'tribe-events-calendar-pro'); ?></span>
	</div>
	<div class="tribe-countdown-colon">:</div>
	<div class="tribe-countdown-hours tribe-countdown-number">HH<br />
		<span class="tribe-countdown-under"><?php _e('hours', 'tribe-events-calendar-pro'); ?></span>
	</div>
	<div class="tribe-countdown-colon">:</div>
	<div class="tribe-countdown-minutes tribe-countdown-number">MM<br />
		<span class="tribe-countdown-under"><?php _e('min', 'tribe-events-calendar-pro'); ?></span>
	</div>
	<?php if ($show_seconds) { ?>
	<div class="tribe-countdown-colon">:</div>
	<div class="tribe-countdown-seconds tribe-countdown-number tribe-countdown-right">SS<br />
		<span class="tribe-countdown-under"><?php _e('sec', 'tribe-events-calendar-pro'); ?></span>
	</div>
	<?php } ?>	
</div>


