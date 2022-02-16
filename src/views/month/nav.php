<?php
/**
 * Month View Nav Template
 * This file loads the month view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( tec_events_views_v1_should_display_deprecated_notice() ) {
	_deprecated_file( __FILE__, '5.13.0', null, 'On version 6.0.0 this file will be removed. Please refer to <a href="https://evnt.is/v1-removal">https://evnt.is/v1-removal</a> for template customization assistance.' );
}

 ?>

<?php do_action( 'tribe_events_before_nav' ) ?>

<nav class="tribe-events-nav-pagination" aria-label="<?php esc_html_e( 'Calendar Month Navigation', 'the-events-calendar' ) ?>">
	<ul class="tribe-events-sub-nav">
		<li class="tribe-events-nav-previous">
			<?php tribe_events_the_previous_month_link(); ?>
		</li>
		<!-- .tribe-events-nav-previous -->
		<li class="tribe-events-nav-next">
			<?php tribe_events_the_next_month_link(); ?>
		</li>
		<!-- .tribe-events-nav-next -->
	</ul><!-- .tribe-events-sub-nav -->
</nav>
<?php
do_action( 'tribe_events_after_nav' );
