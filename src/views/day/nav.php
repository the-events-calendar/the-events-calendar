<?php
/**
 * Day View Nav
 * This file contains the day view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/day/nav.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<h3 class="tribe-events-visuallyhidden"><?php esc_html_e( 'Day Navigation', 'tribe-events-calendar' ) ?></h3>
<ul class="tribe-events-sub-nav">

	<!-- Previous Page Navigation -->
	<li class="tribe-events-nav-previous"><?php tribe_the_day_link( 'previous day' ) ?></li>

	<!-- Next Page Navigation -->
	<li class="tribe-events-nav-next"><?php tribe_the_day_link( 'next day' ) ?></li>

</ul>
