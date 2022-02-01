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

if ( tec_events_views_v1_should_display_deprecated_notice() ) {
	_deprecated_file( __FILE__, '5.13.0', null, 'On version 6.0.0 this file will be removed. Please refer to <a href="https://evnt.is/v1-removal">https://evnt.is/v1-removal</a> for template customization assistance.' );
}

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