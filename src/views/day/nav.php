<?php
/**
 * Day View Nav
 * This file contains the day view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/day/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<nav class="tribe-events-nav-pagination" aria-label="<?php esc_html_e( 'Day Navigation', 'the-events-calendar' ) ?>">
	<ul class="tribe-events-sub-nav">

		<!-- Previous Page Navigation -->
		<li class="tribe-events-nav-previous"><?php tribe_the_day_link( 'previous day' ) ?></li>

		<!-- Next Page Navigation -->
		<li class="tribe-events-nav-next"><?php tribe_the_day_link( 'next day' ) ?></li>

	</ul>
</nav>