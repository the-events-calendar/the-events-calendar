<?php
/**
 * Events Pro Mini Calendar Widget
 * This is the template for the output of the mini calendar widget. 
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/mini-calendar-widget.php
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<div class="tribe-mini-calendar-wrapper">

	<!-- Grid -->
	<?php tribe_get_template_part('widgets/mini-calendar/grid') ?>

	<!-- List -->
	<?php tribe_get_template_part('widgets/mini-calendar/list') ?>

</div>